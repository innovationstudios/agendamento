<?php
require_once 'config/database.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$conn = getDBConnection();

// Consulta para clientes ativos
$query = "SELECT * FROM usuarios WHERE ativo = TRUE ORDER BY nome";
$result = $conn->query($query);
$clientes = [];

while ($row = $result->fetch_assoc()) {
    $clientes[] = $row;
}

// Consulta para histórico de clientes excluídos
$query_historico = "SELECT ch.*, u.nome as excluido_por_nome 
                   FROM clientes_historico ch
                   JOIN usuarios u ON ch.excluido_por = u.id
                   WHERE ch.restaurado = FALSE
                   ORDER BY ch.data_exclusao DESC";
$historico = $conn->query($query_historico)->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php include 'includes/head.php'; ?>
    <title>Clientes - Gestor de Mensalidades</title>
</head>
<body id="page-top">
    <div id="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include 'includes/navbar.php'; ?>
                
                <div class="container-fluid">
                    <h1 class="h3 mb-4 text-gray-800">Gerenciar Clientes</h1>
                    
                    <ul class="nav nav-tabs" id="clientesTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="ativos-tab" data-toggle="tab" href="#ativos" role="tab">
                                Clientes Ativos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="historico-tab" data-toggle="tab" href="#historico" role="tab">
                                Histórico de Exclusões
                            </a>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="clientesTabsContent">
                        <!-- Tab Clientes Ativos -->
                        <div class="tab-pane fade show active" id="ativos" role="tabpanel">
                            <div class="card shadow mb-4 mt-4">
                                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                    <h6 class="m-0 font-weight-bold text-primary">Lista de Clientes Ativos</h6>
                                    <a href="#" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#adicionarClienteModal">
                                        <i class="fas fa-plus"></i> Adicionar Cliente
                                    </a>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>Nome</th>
                                                    <th>Email</th>
                                                    <th>Telefone</th>
                                                    <th>Data Cadastro</th>
                                                    <th>Ações</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($clientes as $cliente): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($cliente['nome']); ?></td>
                                                    <td><?php echo htmlspecialchars($cliente['email']); ?></td>
                                                    <td><?php echo htmlspecialchars($cliente['telefone']); ?></td>
                                                    <td><?php echo date('d/m/Y', strtotime($cliente['data_cadastro'])); ?></td>
                                                    <td>
                                                        <a href="#" class="btn btn-info btn-sm btn-editar" data-id="<?php echo $cliente['id']; ?>" 
                                                           data-nome="<?php echo htmlspecialchars($cliente['nome']); ?>"
                                                           data-email="<?php echo htmlspecialchars($cliente['email']); ?>"
                                                           data-telefone="<?php echo htmlspecialchars($cliente['telefone']); ?>">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="#" class="btn btn-danger btn-sm btn-excluir" data-id="<?php echo $cliente['id']; ?>">
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
                        
                        <!-- Tab Histórico -->
                        <div class="tab-pane fade" id="historico" role="tabpanel">
                            <div class="card shadow mb-4 mt-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Histórico de Clientes Excluídos</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered" id="dataTableHistorico" width="100%" cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>Nome</th>
                                                    <th>Email</th>
                                                    <th>Telefone</th>
                                                    <th>Data Exclusão</th>
                                                    <th>Excluído por</th>
                                                    <th>Ações</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($historico as $item): 
                                                    $dados = json_decode($item['dados_cliente'], true);
                                                ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($dados['nome']); ?></td>
                                                    <td><?php echo htmlspecialchars($dados['email']); ?></td>
                                                    <td><?php echo htmlspecialchars($dados['telefone']); ?></td>
                                                    <td><?php echo date('d/m/Y H:i', strtotime($item['data_exclusao'])); ?></td>
                                                    <td><?php echo htmlspecialchars($item['excluido_por_nome']); ?></td>
                                                    <td>
                                                        <a href="#" class="btn btn-success btn-sm btn-restaurar" 
                                                           data-id="<?php echo $item['id']; ?>"
                                                           data-id-cliente="<?php echo $item['id_cliente_original']; ?>">
                                                            <i class="fas fa-undo"></i> Restaurar
                                                        </a>
                                                        <a href="#" class="btn btn-danger btn-sm btn-excluir-definitivo" 
                                                           data-id="<?php echo $item['id']; ?>">
                                                            <i class="fas fa-trash"></i> Excluir Definitivo
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
                </div>
            </div>
            
            <?php include 'includes/footer.php'; ?>
        </div>
    </div>

        <!-- Modal Adicionar Cliente -->
    <div class="modal fade" id="adicionarClienteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Adicionar Novo Cliente</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formCliente">
                        <input type="hidden" id="clienteId">
                        <div class="form-group">
                            <label for="nome">Nome Completo</label>
                            <input type="text" class="form-control" id="nome" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" required>
                        </div>
                        <div class="form-group">
                            <label for="telefone">Telefone</label>
                            <input type="text" class="form-control" id="telefone" required>
                        </div>
                        <div class="form-group" id="senhaGroup">
                            <label for="senha">Senha</label>
                            <input type="password" class="form-control" id="senha">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                    <button class="btn btn-primary" id="btnSalvarCliente">Salvar</button>
                </div>
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
                <div class="modal-body">
                    <p>Tem certeza que deseja excluir este cliente?</p>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> Esta ação irá mover o cliente para o histórico de exclusões, onde você poderá restaurá-lo se necessário.
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                    <button class="btn btn-danger" id="btnConfirmarExclusao">Excluir</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Confirmar Exclusão Definitiva -->
    <div class="modal fade" id="confirmarExclusaoDefinitivaModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Exclusão Definitiva</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja excluir definitivamente este cliente?</p>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> Esta ação é irreversível e removerá permanentemente todos os dados deste cliente do sistema.
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                    <button class="btn btn-danger" id="btnConfirmarExclusaoDefinitiva">Excluir Definitivamente</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Confirmar Restauração -->
    <div class="modal fade" id="confirmarRestauracaoModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Confirmar Restauração</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja restaurar este cliente?</p>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> O cliente será restaurado com todos os seus dados originais.
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                    <button class="btn btn-success" id="btnConfirmarRestauracao">Restaurar Cliente</button>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/scripts.php'; ?>
    
    <script>
    $(document).ready(function () {
        // Variáveis para armazenar IDs temporários
        let clienteIdParaExcluir = null;
        let historicoIdParaRestaurar = null;
        let historicoIdParaExcluirDefinitivo = null;
        let clienteOriginalIdParaRestaurar = null;
        
        // Configuração do DataTables
        $('#dataTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Portuguese-Brasil.json"
            }
        });
        
        $('#dataTableHistorico').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Portuguese-Brasil.json"
            },
            "order": [[3, "desc"]]
        });
        
$(document).on('click', '.btn-editar', function () {
    const id = $(this).data('id');
    const nome = $(this).data('nome');
    const email = $(this).data('email');
    const telefone = $(this).data('telefone');

    $('#clienteId').val(id);
    $('#nome').val(nome);
    $('#email').val(email);
    $('#telefone').val(telefone);
    
    // Esconde campo de senha ao editar
    $('#senhaGroup').hide();
    
    // Atualiza título do modal
    $('#exampleModalLabel').text('Editar Cliente');
    
    // Abre o modal
    $('#adicionarClienteModal').modal('show');
});

        
        // Configuração do modal de exclusão
        $(document).on('click', '.btn-excluir', function() {
            clienteIdParaExcluir = $(this).data('id');
            $('#confirmarExclusaoModal').modal('show');
        });
        
        // Configuração do modal de restauração
        $(document).on('click', '.btn-restaurar', function() {
            historicoIdParaRestaurar = $(this).data('id');
            clienteOriginalIdParaRestaurar = $(this).data('id-cliente');
            $('#confirmarRestauracaoModal').modal('show');
        });
        
        // Configuração do modal de exclusão definitiva
        $(document).on('click', '.btn-excluir-definitivo', function() {
            historicoIdParaExcluirDefinitivo = $(this).data('id');
            $('#confirmarExclusaoDefinitivaModal').modal('show');
        });
        
        // Botão para confirmar exclusão (move para histórico)
        $('#btnConfirmarExclusao').on('click', function() {
            if (clienteIdParaExcluir) {
                $.ajax({
                    url: 'api/excluir_cliente.php',
                    type: 'POST',
                    data: { 
                        id: clienteIdParaExcluir,
                        tipo: 'soft'
                    },
                    success: function(resposta) {
                        const res = JSON.parse(resposta);
                        if (res.status === 'sucesso') {
                            alert('Cliente movido para o histórico de exclusões!');
                            $('#confirmarExclusaoModal').modal('hide');
                            setTimeout(() => location.reload(), 500);
                        } else {
                            alert(res.mensagem || 'Erro ao excluir cliente');
                        }
                    },
                    error: function(xhr) {
                        alert('Erro no servidor: ' + xhr.responseText);
                    }
                });
            }
        });
        
        // Botão para confirmar restauração
        $('#btnConfirmarRestauracao').on('click', function() {
            if (historicoIdParaRestaurar && clienteOriginalIdParaRestaurar) {
                $.ajax({
                    url: 'api/restaurar_cliente.php',
                    type: 'POST',
                    data: { 
                        id_historico: historicoIdParaRestaurar,
                        id_cliente: clienteOriginalIdParaRestaurar
                    },
                    success: function(resposta) {
                        const res = JSON.parse(resposta);
                        if (res.status === 'sucesso') {
                            alert('Cliente restaurado com sucesso!');
                            $('#confirmarRestauracaoModal').modal('hide');
                            setTimeout(() => location.reload(), 500);
                        } else {
                            alert(res.mensagem || 'Erro ao restaurar cliente');
                        }
                    },
                    error: function(xhr) {
                        alert('Erro no servidor: ' + xhr.responseText);
                    }
                });
            }
        });
        
        // Botão para confirmar exclusão definitiva
        $('#btnConfirmarExclusaoDefinitiva').on('click', function() {
            if (historicoIdParaExcluirDefinitivo) {
                $.ajax({
                    url: 'api/excluir_cliente.php',
                    type: 'POST',
                    data: { 
                        id: historicoIdParaExcluirDefinitivo,
                        tipo: 'hard'
                    },
                    success: function(resposta) {
                        const res = JSON.parse(resposta);
                        if (res.status === 'sucesso') {
                            alert('Cliente excluído permanentemente do sistema!');
                            $('#confirmarExclusaoDefinitivaModal').modal('hide');
                            setTimeout(() => location.reload(), 500);
                        } else {
                            alert(res.mensagem || 'Erro ao excluir cliente');
                        }
                    },
                    error: function(xhr) {
                        alert('Erro no servidor: ' + xhr.responseText);
                    }
                });
            }
        });
        
     // Botão para salvar (adicionar/editar) cliente
        $('#btnSalvarCliente').on('click', function (e) {
            e.preventDefault();

            const id = $('#clienteId').val();
            const nome = $('#nome').val().trim();
            const email = $('#email').val().trim();
            const telefone = $('#telefone').val().trim();
            const senha = $('#senha').val().trim();

            if (!nome || !email || !telefone) {
                alert('Preencha todos os campos obrigatórios!');
                return;
            }

            // Se for um novo cliente, verifica a senha
            if (!id && !senha) {
                alert('Para novo cliente, a senha é obrigatória!');
                return;
            }

            const dados = {
                id: id,
                nome: nome,
                email: email,
                telefone: telefone
            };

            // Adiciona a senha apenas se foi informada (para edição)
            if (senha) {
                dados.senha = senha;
            }

            $.ajax({
                url: 'salvar_cliente.php',
                type: 'POST',
                data: dados,
                success: function (resposta) {
                    const res = JSON.parse(resposta);
                    if (res.status === 'sucesso') {
                        alert(id ? 'Cliente atualizado com sucesso!' : 'Cliente adicionado com sucesso!');
                        $('#adicionarClienteModal').modal('hide');
                        setTimeout(() => location.reload(), 500);
                    } else {
                        alert(res.mensagem || 'Erro ao salvar cliente');
                    }
                },
                error: function (xhr) {
                    alert('Erro no servidor: ' + xhr.responseText);
                }
            });
        });
        
        // Quando o modal é fechado, limpa os campos
        $('#adicionarClienteModal').on('hidden.bs.modal', function () {
            $('#formCliente')[0].reset();
            $('#clienteId').val('');
            $('#senhaGroup').show();
            $('#exampleModalLabel').text('Adicionar Novo Cliente');
        });

    });
    </script>
</body>
</html>