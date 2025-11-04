<?php
require_once 'config/database.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$conn = getDBConnection();

// Processar operações de adição/edição/exclusão
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    
    if ($acao === 'adicionar' || $acao === 'editar') {
        $id = $acao === 'editar' ? $_POST['id'] : null;
        $nome = $_POST['nome'] ?? '';
        $descricao = $_POST['descricao'] ?? '';
        $contato = $_POST['contato'] ?? '';
        $telefone = $_POST['telefone'] ?? '';
        $email = $_POST['email'] ?? '';
        
        if (empty($nome) || empty($telefone)) {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Nome e telefone são obrigatórios!'];
        } else {
            if ($acao === 'adicionar') {
                $stmt = $conn->prepare("INSERT INTO fornecedores (nome, descricao, contato, telefone, email) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $nome, $descricao, $contato, $telefone, $email);
            } else {
                $stmt = $conn->prepare("UPDATE fornecedores SET nome = ?, descricao = ?, contato = ?, telefone = ?, email = ? WHERE id = ?");
                $stmt->bind_param("sssssi", $nome, $descricao, $contato, $telefone, $email, $id);
            }
            
            if ($stmt->execute()) {
                $_SESSION['mensagem'] = ['tipo' => 'success', 'texto' => 'Fornecedor ' . ($acao === 'adicionar' ? 'adicionado' : 'atualizado') . ' com sucesso!'];
            } else {
                $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Erro ao salvar fornecedor: ' . $conn->error];
            }
        }
        header('Location: fornecedores.php');
        exit;
    } elseif ($acao === 'excluir' && !empty($_POST['id'])) {
        // Soft delete (marcar como inativo)
        $stmt = $conn->prepare("UPDATE fornecedores SET ativo = FALSE WHERE id = ?");
        $stmt->bind_param("i", $_POST['id']);
        
        if ($stmt->execute()) {
            $_SESSION['mensagem'] = ['tipo' => 'success', 'texto' => 'Fornecedor excluído com sucesso!'];
        } else {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Erro ao excluir fornecedor: ' . $conn->error];
        }
        header('Location: fornecedores.php');
        exit;
    }
}

// Obter lista de fornecedores ativos
$query = "SELECT * FROM fornecedores WHERE ativo = TRUE ORDER BY nome";
$result = $conn->query($query);
$fornecedores = [];

while ($row = $result->fetch_assoc()) {
    $fornecedores[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php include 'includes/head.php'; ?>
    <title>Fornecedores - Gestor de Mensalidades</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .table-actions {
            white-space: nowrap;
        }
    </style>
</head>
<body id="page-top">
    <div id="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include 'includes/navbar.php'; ?>
                
                <div class="container-fluid">
                    <h1 class="h3 mb-4 text-gray-800">Gerenciar Fornecedores</h1>
                    
                    <?php if (isset($_SESSION['mensagem'])): ?>
                    <div class="alert alert-<?= $_SESSION['mensagem']['tipo'] ?> alert-dismissible fade show">
                        <?= $_SESSION['mensagem']['texto'] ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php unset($_SESSION['mensagem']); endif; ?>
                    
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Lista de Fornecedores</h6>
                            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#adicionarFornecedorModal">
                                <i class="fas fa-plus"></i> Adicionar Fornecedor
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Nome</th>
                                            <th>Descrição</th>
                                            <th>Contato</th>
                                            <th>Telefone</th>
                                            <th>Email</th>
                                            <th class="table-actions">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($fornecedores as $fornecedor): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($fornecedor['nome']) ?></td>
                                            <td><?= htmlspecialchars($fornecedor['descricao']) ?></td>
                                            <td><?= htmlspecialchars($fornecedor['contato']) ?></td>
                                            <td><?= htmlspecialchars($fornecedor['telefone']) ?></td>
                                            <td><?= htmlspecialchars($fornecedor['email']) ?></td>
                                            <td class="table-actions">
                                                <button class="btn btn-info btn-sm btn-editar" 
                                                        data-id="<?= $fornecedor['id'] ?>"
                                                        data-nome="<?= htmlspecialchars($fornecedor['nome']) ?>"
                                                        data-descricao="<?= htmlspecialchars($fornecedor['descricao']) ?>"
                                                        data-contato="<?= htmlspecialchars($fornecedor['contato']) ?>"
                                                        data-telefone="<?= htmlspecialchars($fornecedor['telefone']) ?>"
                                                        data-email="<?= htmlspecialchars($fornecedor['email']) ?>">
                                                    <i class="fas fa-edit"></i> Editar
                                                </button>
                                                <button class="btn btn-danger btn-sm btn-excluir" 
                                                        data-id="<?= $fornecedor['id'] ?>"
                                                        data-nome="<?= htmlspecialchars($fornecedor['nome']) ?>">
                                                    <i class="fas fa-trash"></i> Excluir
                                                </button>
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

    <!-- Modal Adicionar Fornecedor -->
    <div class="modal fade" id="adicionarFornecedorModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Adicionar Novo Fornecedor</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form method="POST">
                    <input type="hidden" name="acao" value="adicionar">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="fornecedor_nome">Nome *</label>
                            <input type="text" class="form-control" id="fornecedor_nome" name="nome" required>
                        </div>
                        <div class="form-group">
                            <label for="fornecedor_descricao">Descrição</label>
                            <textarea class="form-control" id="fornecedor_descricao" name="descricao" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="fornecedor_contato">Contato</label>
                            <input type="text" class="form-control" id="fornecedor_contato" name="contato">
                        </div>
                        <div class="form-group">
                            <label for="fornecedor_telefone">Telefone *</label>
                            <input type="text" class="form-control" id="fornecedor_telefone" name="telefone" required>
                        </div>
                        <div class="form-group">
                            <label for="fornecedor_email">Email</label>
                            <input type="email" class="form-control" id="fornecedor_email" name="email">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Fornecedor -->
    <div class="modal fade" id="editarFornecedorModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Editar Fornecedor</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form method="POST">
                    <input type="hidden" name="acao" value="editar">
                    <input type="hidden" name="id" id="editar_id">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="editar_nome">Nome *</label>
                            <input type="text" class="form-control" id="editar_nome" name="nome" required>
                        </div>
                        <div class="form-group">
                            <label for="editar_descricao">Descrição</label>
                            <textarea class="form-control" id="editar_descricao" name="descricao" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="editar_contato">Contato</label>
                            <input type="text" class="form-control" id="editar_contato" name="contato">
                        </div>
                        <div class="form-group">
                            <label for="editar_telefone">Telefone *</label>
                            <input type="text" class="form-control" id="editar_telefone" name="telefone" required>
                        </div>
                        <div class="form-group">
                            <label for="editar_email">Email</label>
                            <input type="email" class="form-control" id="editar_email" name="email">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Confirmar Exclusão -->
    <div class="modal fade" id="confirmarExclusaoModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Confirmar Exclusão</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form method="POST">
                    <input type="hidden" name="acao" value="excluir">
                    <input type="hidden" name="id" id="excluir_id">
                    <div class="modal-body">
                        <p>Tem certeza que deseja excluir o fornecedor <strong id="nome_fornecedor_excluir"></strong>?</p>
                        <p class="text-danger">Esta ação não pode ser desfeita!</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Confirmar Exclusão</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <?php include 'includes/scripts.php'; ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    
    <script>
    $(document).ready(function() {
        // Máscara para telefone
        $('#fornecedor_telefone, #editar_telefone').mask('(00) 00000-0000');

        // Configurar DataTable
        $('#dataTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Portuguese-Brasil.json"
            },
            "columnDefs": [
                { "orderable": false, "targets": 5 } // Desabilita ordenação na coluna de ações
            ]
        });

        // Configurar botão de edição
        $('.btn-editar').click(function() {
            const id = $(this).data('id');
            const nome = $(this).data('nome');
            const descricao = $(this).data('descricao');
            const contato = $(this).data('contato');
            const telefone = $(this).data('telefone');
            const email = $(this).data('email');
            
            $('#editar_id').val(id);
            $('#editar_nome').val(nome);
            $('#editar_descricao').val(descricao);
            $('#editar_contato').val(contato);
            $('#editar_telefone').val(telefone);
            $('#editar_email').val(email);
            
            $('#editarFornecedorModal').modal('show');
        });

        // Configurar botão de exclusão
        $('.btn-excluir').click(function() {
            const id = $(this).data('id');
            const nome = $(this).data('nome');
            
            $('#excluir_id').val(id);
            $('#nome_fornecedor_excluir').text(nome);
            $('#confirmarExclusaoModal').modal('show');
        });
    });
    </script>
</body>
</html>