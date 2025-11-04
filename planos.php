<?php
require_once 'config/database.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$conn = getDBConnection();
$query = "SELECT p.*, f.nome as fornecedor_nome 
          FROM planos p 
          LEFT JOIN fornecedores f ON p.id_fornecedor = f.id 
          WHERE p.ativo = TRUE 
          ORDER BY p.nome";
$result = $conn->query($query);
$planos = [];

while ($row = $result->fetch_assoc()) {
    $planos[] = $row;
}

$query = "SELECT id, nome FROM fornecedores WHERE ativo = TRUE ORDER BY nome";
$fornecedores = $conn->query($query)->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php include 'includes/head.php'; ?>
    <title>Planos - Gestor de Mensalidades</title>
</head>
<body id="page-top">
    <div id="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include 'includes/navbar.php'; ?>
                
                <div class="container-fluid">
                    <h1 class="h3 mb-4 text-gray-800">Gerenciar Planos</h1>
                    
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Lista de Planos</h6>
                            <a href="#" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#adicionarPlanoModal">
                                <i class="fas fa-plus"></i> Adicionar Plano
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Nome</th>
                                            <th>Descrição</th>
                                            <th>Valor</th>
                                            <th>Ciclo</th>
                                            <th>Fornecedor</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($planos as $plano): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($plano['nome']); ?></td>
                                            <td><?php echo htmlspecialchars($plano['descricao']); ?></td>
                                            <td>R$ <?php echo number_format($plano['valor'], 2, ',', '.'); ?></td>
                                            <td><?php echo ucfirst($plano['ciclo']); ?></td>
                                            <td><?php echo $plano['fornecedor_nome'] ?? 'N/A'; ?></td>
                                            <td>
                                                <a href="#" class="btn btn-info btn-sm btn-editar" 
                                                   data-id="<?php echo $plano['id']; ?>"
                                                   data-nome="<?php echo htmlspecialchars($plano['nome']); ?>"
                                                   data-descricao="<?php echo htmlspecialchars($plano['descricao']); ?>"
                                                   data-valor="<?php echo $plano['valor']; ?>"
                                                   data-ciclo="<?php echo $plano['ciclo']; ?>"
                                                   data-fornecedor="<?php echo $plano['id_fornecedor'] ?? ''; ?>">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="#" class="btn btn-danger btn-sm btn-excluir" data-id="<?php echo $plano['id']; ?>">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php include 'includes/footer.php'; ?>
        </div>
    </div>

    <!-- Modal Adicionar Plano -->
    <div class="modal fade" id="adicionarPlanoModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Adicionar Novo Plano</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formPlano">
                        <div class="form-group">
                            <label for="plano_nome">Nome do Plano</label>
                            <input type="text" class="form-control" id="plano_nome" required>
                        </div>
                        <div class="form-group">
                            <label for="plano_descricao">Descrição</label>
                            <textarea class="form-control" id="plano_descricao" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="plano_valor">Valor</label>
                            <input type="number" step="0.01" class="form-control" id="plano_valor" required>
                        </div>
                        <div class="form-group">
                            <label for="plano_ciclo">Ciclo de Pagamento</label>
                            <select class="form-control" id="plano_ciclo" required>
                                <option value="mensal">Mensal</option>
                                <option value="trimestral">Trimestral</option>
                                <option value="semestral">Semestral</option>
                                <option value="anual">Anual</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="plano_fornecedor">Fornecedor</label>
                            <select class="form-control" id="plano_fornecedor">
                                <option value="">Nenhum</option>
                                <?php foreach ($fornecedores as $fornecedor): ?>
                                <option value="<?php echo $fornecedor['id']; ?>"><?php echo htmlspecialchars($fornecedor['nome']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                    <button class="btn btn-primary" id="btnSalvarPlano">Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Editar Plano -->
    <div class="modal fade" id="editarPlanoModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Editar Plano</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formEditarPlano">
                        <input type="hidden" id="plano_id_editar">
                        <div class="form-group">
                            <label for="plano_nome_editar">Nome do Plano</label>
                            <input type="text" class="form-control" id="plano_nome_editar" required>
                        </div>
                        <div class="form-group">
                            <label for="plano_descricao_editar">Descrição</label>
                            <textarea class="form-control" id="plano_descricao_editar" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="plano_valor_editar">Valor</label>
                            <input type="number" step="0.01" class="form-control" id="plano_valor_editar" required>
                        </div>
                        <div class="form-group">
                            <label for="plano_ciclo_editar">Ciclo de Pagamento</label>
                            <select class="form-control" id="plano_ciclo_editar" required>
                                <option value="mensal">Mensal</option>
                                <option value="trimestral">Trimestral</option>
                                <option value="semestral">Semestral</option>
                                <option value="anual">Anual</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="plano_fornecedor_editar">Fornecedor</label>
                            <select class="form-control" id="plano_fornecedor_editar">
                                <option value="">Nenhum</option>
                                <?php foreach ($fornecedores as $fornecedor): ?>
                                <option value="<?php echo $fornecedor['id']; ?>"><?php echo htmlspecialchars($fornecedor['nome']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                    <button class="btn btn-primary" id="btnSalvarEdicao">Salvar Alterações</button>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/scripts.php'; ?>
    
    <script>
    $(document).ready(function() {
        // Inicializar DataTable
        $('#dataTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Portuguese-Brasil.json"
            }
        });

        // Configurar botão de edição
        $('.btn-editar').click(function() {
            const id = $(this).data('id');
            const nome = $(this).data('nome');
            const descricao = $(this).data('descricao');
            const valor = $(this).data('valor');
            const ciclo = $(this).data('ciclo');
            const fornecedor = $(this).data('fornecedor');
            
            // Preencher o modal de edição
            $('#plano_id_editar').val(id);
            $('#plano_nome_editar').val(nome);
            $('#plano_descricao_editar').val(descricao);
            $('#plano_valor_editar').val(valor);
            $('#plano_ciclo_editar').val(ciclo);
            $('#plano_fornecedor_editar').val(fornecedor);
            
            // Abrir o modal
            $('#editarPlanoModal').modal('show');
        });

        // Salvar edição do plano
        $('#btnSalvarEdicao').click(function() {
            const id = $('#plano_id_editar').val();
            const nome = $('#plano_nome_editar').val().trim();
            const descricao = $('#plano_descricao_editar').val().trim();
            const valor = $('#plano_valor_editar').val();
            const ciclo = $('#plano_ciclo_editar').val();
            const fornecedor = $('#plano_fornecedor_editar').val();

            if (!nome || !valor || !ciclo) {
                alert('Preencha os campos obrigatórios!');
                return;
            }

            $.ajax({
                url: 'backend/editar_plano.php',
                method: 'POST',
                data: {
                    id,
                    nome,
                    descricao,
                    valor,
                    ciclo,
                    fornecedor
                },
                success: function(response) {
                    try {
                        const res = JSON.parse(response);
                        if (res.success) {
                            alert('Plano atualizado com sucesso!');
                            location.reload(); // recarrega para atualizar a tabela
                        } else {
                            alert('Erro: ' + res.message);
                        }
                    } catch (err) {
                        console.error('Erro ao interpretar JSON:', err, response);
                        alert('Erro inesperado!');
                    }
                },
                error: function(err) {
                    console.error('Erro AJAX:', err);
                    alert('Erro na requisição.');
                }
            });
        });

        // Enviar dados do novo plano via AJAX
        $('#btnSalvarPlano').click(function(e) {
            e.preventDefault();

            const nome = $('#plano_nome').val().trim();
            const descricao = $('#plano_descricao').val().trim();
            const valor = $('#plano_valor').val();
            const ciclo = $('#plano_ciclo').val();
            const fornecedor = $('#plano_fornecedor').val();

            if (!nome || !valor || !ciclo) {
                alert('Preencha os campos obrigatórios!');
                return;
            }

            $.ajax({
                url: 'backend/adicionar_plano.php',
                method: 'POST',
                data: {
                    nome,
                    descricao,
                    valor,
                    ciclo,
                    fornecedor
                },
                success: function(response) {
                    try {
                        const res = JSON.parse(response);
                        if (res.success) {
                            alert('Plano adicionado com sucesso!');
                            location.reload(); // recarrega para atualizar a tabela
                        } else {
                            alert('Erro: ' + res.message);
                        }
                    } catch (err) {
                        console.error('Erro ao interpretar JSON:', err, response);
                        alert('Erro inesperado!');
                    }
                },
                error: function(err) {
                    console.error('Erro AJAX:', err);
                    alert('Erro na requisição.');
                }
            });
        });
    });
    </script>
</body>
</html>