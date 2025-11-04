<?php
require_once 'config/database.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$conn = getDBConnection();

// Filtros
$filtro_status = $_GET['status'] ?? 'todos';
$filtro_mes = $_GET['mes'] ?? date('Y-m');

// Consulta base
$query = "SELECT m.*, u.nome as cliente_nome, u.telefone, p.nome as plano_nome, p.valor as plano_valor
          FROM mensalidades m
          JOIN usuarios u ON m.id_usuario = u.id
          JOIN planos p ON m.id_plano = p.id
          WHERE u.ativo = TRUE AND p.ativo = TRUE";

// Aplicar filtros
if ($filtro_status !== 'todos') {
    if ($filtro_status === 'vencidas') {
        $query .= " AND (m.status = 'vencido' OR (m.status = 'pendente' AND m.data_vencimento < CURDATE()))";
    } else {
        $query .= " AND m.status = '$filtro_status'";
        
        if ($filtro_status === 'pendente') {
            $query .= " AND m.data_vencimento >= CURDATE()";
        }
    }
}

if (!empty($filtro_mes)) {
    $query .= " AND (DATE_FORMAT(m.data_vencimento, '%Y-%m') = '$filtro_mes' 
                OR DATE_FORMAT(m.data_pagamento, '%Y-%m') = '$filtro_mes')";
}

$query .= " ORDER BY m.data_vencimento ASC";

$mensalidades = [];
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $mensalidades[] = $row;
}

// Obter meses disponíveis para filtro
$meses_query = "SELECT DISTINCT DATE_FORMAT(data_vencimento, '%Y-%m') as mes 
                FROM mensalidades 
                ORDER BY mes DESC";
$meses_disponiveis = $conn->query($meses_query)->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php include 'includes/head.php'; ?>
    <title>Mensalidades - Gestor de Mensalidades</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>
<body id="page-top">
    <div id="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include 'includes/navbar.php'; ?>
                
                <div class="container-fluid">
                    <h1 class="h3 mb-4 text-gray-800">Gerenciamento de Mensalidades</h1>
                    
                    <!-- Filtros -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Filtros</h6>
                        </div>
                        <div class="card-body">
                            <form method="get" class="form-inline">
                                <div class="form-group mr-3 mb-2">
                                    <label for="status" class="mr-2">Status:</label>
                                    <select name="status" id="status" class="form-control">
                                        <option value="todos" <?= $filtro_status === 'todos' ? 'selected' : '' ?>>Todos</option>
                                        <option value="pendente" <?= $filtro_status === 'pendente' ? 'selected' : '' ?>>Pendentes</option>
                                        <option value="pago" <?= $filtro_status === 'pago' ? 'selected' : '' ?>>Pagas</option>
                                        <option value="vencidas" <?= $filtro_status === 'vencidas' ? 'selected' : '' ?>>Vencidas</option>
                                    </select>
                                </div>
                                <div class="form-group mr-3 mb-2">
                                    <label for="mes" class="mr-2">Mês:</label>
                                    <select name="mes" id="mes" class="form-control">
                                        <?php foreach ($meses_disponiveis as $mes): ?>
                                        <option value="<?= $mes['mes'] ?>" <?= $filtro_mes === $mes['mes'] ? 'selected' : '' ?>>
                                            <?= date('m/Y', strtotime($mes['mes'] . '-01')) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary mb-2">
                                    <i class="fas fa-filter"></i> Filtrar
                                </button>
                                <a href="mensalidades.php" class="btn btn-secondary mb-2 ml-2">
                                    <i class="fas fa-sync-alt"></i> Limpar
                                </a>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Lista de Mensalidades -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Lista de Mensalidades</h6>
                            <div>
                                <a href="#" class="btn btn-success btn-sm" data-toggle="modal" data-target="#gerarMensalidadesModal">
                                    <i class="fas fa-plus"></i> Gerar Mensalidades
                                </a>
                                <a href="#" class="btn btn-primary btn-sm ml-2" data-toggle="modal" data-target="#adicionarMensalidadeModal">
                                    <i class="fas fa-plus"></i> Adicionar Manual
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Cliente</th>
                                            <th>Plano</th>
                                            <th>Valor</th>
                                            <th>Vencimento</th>
                                            <th>Pagamento</th>
                                            <th>Status</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($mensalidades as $mensalidade): ?>
                                        <?php
                                        $status_class = '';
                                        $status_text = ucfirst($mensalidade['status']);
                                        
                                        if ($mensalidade['status'] == 'pago') {
                                            $status_class = 'badge-success';
                                        } elseif ($mensalidade['status'] == 'pendente') {
                                            if (strtotime($mensalidade['data_vencimento']) < time()) {
                                                $status_class = 'badge-danger';
                                                $status_text = 'Vencida';
                                            } else {
                                                $status_class = 'badge-primary';
                                            }
                                        } elseif ($mensalidade['status'] == 'vencido') {
                                            $status_class = 'badge-danger';
                                        }
                                        ?>
                                        <tr>
                                            <td><?= htmlspecialchars($mensalidade['cliente_nome']) ?></td>
                                            <td><?= htmlspecialchars($mensalidade['plano_nome']) ?></td>
                                            <td>R$ <?= number_format($mensalidade['valor'], 2, ',', '.') ?></td>
                                            <td><?= date('d/m/Y', strtotime($mensalidade['data_vencimento'])) ?></td>
                                            <td>
                                                <?= $mensalidade['data_pagamento'] ? date('d/m/Y', strtotime($mensalidade['data_pagamento'])) : '-' ?>
                                            </td>
                                            <td>
                                                <span class="badge <?= $status_class ?>"><?= $status_text ?></span>
                                            </td>
                                            <td>
                                                <?php if ($mensalidade['status'] !== 'pago'): ?>
                                                <a href="#" class="btn btn-success btn-sm btn-pagar" 
                                                   data-id="<?= $mensalidade['id'] ?>" 
                                                   data-toggle="modal" 
                                                   data-target="#pagarMensalidadeModal">
                                                    <i class="fas fa-money-bill-wave"></i> Pagar
                                                </a>
                                                <?php endif; ?>
                                                <a href="#" class="btn btn-info btn-sm btn-editar" 
                                                   data-id="<?= $mensalidade['id'] ?>"
                                                   data-toggle="modal"
                                                   data-target="#editarMensalidadeModal">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="#" class="btn btn-danger btn-sm btn-excluir" 
                                                   data-id="<?= $mensalidade['id'] ?>">
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

    <!-- Modal Adicionar Mensalidade Manual -->
<div class="modal fade" id="adicionarMensalidadeModal" tabindex="-1" role="dialog" aria-labelledby="adicionarMensalidadeLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Adicionar Mensalidade Manualmente</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formAdicionarMensalidade">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="cliente_adicionar">Cliente</label>
                                <select class="form-control" id="cliente_adicionar" required></select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="plano_adicionar">Plano</label>
                                <select class="form-control" id="plano_adicionar" required></select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="valor_adicionar">Valor</label>
                                <input type="number" step="0.01" class="form-control" id="valor_adicionar" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="vencimento_adicionar">Data Vencimento</label>
                                <input type="date" class="form-control" id="vencimento_adicionar" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="status_adicionar">Status</label>
                                <select class="form-control" id="status_adicionar" required>
                                    <option value="pendente">Pendente</option>
                                    <option value="pago">Pago</option>
                                    <option value="vencido">Vencido</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="observacao_adicionar">Observação</label>
                        <textarea class="form-control" id="observacao_adicionar" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary" id="btnAdicionarMensalidade">Adicionar</button>
            </div>
        </div>
    </div>
</div>


    <!-- Modal Pagar Mensalidade -->
    <div class="modal fade" id="pagarMensalidadeModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Registrar Pagamento</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formPagarMensalidade">
                        <input type="hidden" id="mensalidade_id_pagar">
                        <div class="form-group">
                            <label for="data_pagamento">Data do Pagamento</label>
                            <input type="date" class="form-control" id="data_pagamento" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="valor_pago">Valor Pago</label>
                            <input type="number" step="0.01" class="form-control" id="valor_pago" required>
                        </div>
                        <div class="form-group">
                            <label for="observacao_pagamento">Observação</label>
                            <textarea class="form-control" id="observacao_pagamento" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                    <button class="btn btn-primary" id="btnConfirmarPagamento">Confirmar Pagamento</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Editar Mensalidade -->
    <div class="modal fade" id="editarMensalidadeModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Editar Mensalidade</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formEditarMensalidade">
                        <input type="hidden" id="mensalidade_id_editar">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="cliente_editar">Cliente</label>
                                    <select class="form-control" id="cliente_editar" required>
                                        <!-- Opções serão carregadas via AJAX -->
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="plano_editar">Plano</label>
                                    <select class="form-control" id="plano_editar" required>
                                        <!-- Opções serão carregadas via AJAX -->
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="valor_editar">Valor</label>
                                    <input type="number" step="0.01" class="form-control" id="valor_editar" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="vencimento_editar">Data Vencimento</label>
                                    <input type="date" class="form-control" id="vencimento_editar" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="status_editar">Status</label>
                                    <select class="form-control" id="status_editar" required>
                                        <option value="pendente">Pendente</option>
                                        <option value="pago">Pago</option>
                                        <option value="vencido">Vencido</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="observacao_editar">Observação</label>
                            <textarea class="form-control" id="observacao_editar" rows="3"></textarea>
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

    <!-- Modal Gerar Mensalidades -->
    <div class="modal fade" id="gerarMensalidadesModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Gerar Mensalidades</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Deseja gerar as mensalidades para o próximo ciclo de todos os clientes com planos ativos?</p>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Esta ação irá criar mensalidades pendentes para o próximo período baseado no ciclo de cada plano.
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                    <button class="btn btn-primary" id="btnGerarMensalidades">Gerar Mensalidades</button>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/scripts.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/pt.js"></script>
    
   <script>
    $(document).ready(function () {
        // Inicializar DataTable
        $('#dataTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Portuguese-Brasil.json"
            },
            "order": [[3, "asc"]]
        });

        // Botão de pagamento
        $('.btn-pagar').click(function () {
            const id = $(this).data('id');
            $('#mensalidade_id_pagar').val(id);
        });

        // Botão de edição
        $('.btn-editar').click(function () {
            const id = $(this).data('id');
            $('#mensalidade_id_editar').val(id);

            // Carregar dados da mensalidade
            $.ajax({
                url: 'api/get_mensalidade.php',
                method: 'GET',
                data: { id: id },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        $('#cliente_editar').val(response.data.id_usuario);
                        $('#plano_editar').val(response.data.id_plano);
                        $('#valor_editar').val(response.data.valor);
                        $('#vencimento_editar').val(response.data.data_vencimento);
                        $('#status_editar').val(response.data.status);
                        $('#observacao_editar').val(response.data.observacao || '');
                    } else {
                        alert('Erro ao carregar mensalidade: ' + response.message);
                    }
                }
            });

            // Carregar clientes
            $.ajax({
                url: 'api/get_clientes.php',
                method: 'GET',
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        const select = $('#cliente_editar');
                        select.empty();
                        response.data.forEach(cliente => {
                            select.append(new Option(cliente.nome, cliente.id));
                        });
                        $('#cliente_editar').val($('#cliente_editar').data('value'));
                    }
                }
            });

            // Carregar planos
            $.ajax({
                url: 'api/get_planos.php',
                method: 'GET',
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        const select = $('#plano_editar');
                        select.empty();
                        response.data.forEach(plano => {
                            select.append(new Option(plano.nome, plano.id));
                        });
                        $('#plano_editar').val($('#plano_editar').data('value'));
                    }
                }
            });
        });

        // Confirmar pagamento
        $('#btnConfirmarPagamento').click(function () {
            const id = $('#mensalidade_id_pagar').val();
            const data_pagamento = $('#data_pagamento').val();
            const valor_pago = $('#valor_pago').val();
            const observacao = $('#observacao_pagamento').val();

            $.ajax({
                url: 'api/pagar_mensalidade.php',
                method: 'POST',
                data: {
                    id: id,
                    data_pagamento: data_pagamento,
                    valor_pago: valor_pago,
                    observacao: observacao
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Erro ao registrar pagamento: ' + response.message);
                    }
                }
            });
        });

        // Gerar mensalidades
        $('#btnGerarMensalidades').click(function () {
            $.ajax({
                url: 'api/gerar_mensalidades.php',
                method: 'POST',
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        alert(response.message);
                        location.reload();
                    } else {
                        alert('Erro ao gerar mensalidades: ' + response.message);
                    }
                }
            });
        });

        // flatpickr para datas
        flatpickr("#data_pagamento", {
            locale: "pt",
            dateFormat: "Y-m-d"
        });

        flatpickr("#vencimento_editar", {
            locale: "pt",
            dateFormat: "Y-m-d"
        });

        // flatpickr para adicionar vencimento (inicialização dentro do modal também)
        flatpickr("#vencimento_adicionar", {
            locale: "pt",
            dateFormat: "Y-m-d"
        });

        // Modal de adicionar mensalidade manual
        $('#adicionarMensalidadeModal').on('show.bs.modal', function () {
            // Carregar clientes
            $.ajax({
                url: 'api/get_clientes.php',
                method: 'GET',
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        const select = $('#cliente_adicionar');
                        select.empty();
                        response.data.forEach(cliente => {
                            select.append(new Option(cliente.nome, cliente.id));
                        });
                    } else {
                        alert('Erro ao carregar clientes: ' + response.message);
                    }
                }
            });

            // Carregar planos
            $.ajax({
                url: 'api/get_planos.php',
                method: 'GET',
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        const select = $('#plano_adicionar');
                        select.empty();
                        response.data.forEach(plano => {
                            select.append(new Option(plano.nome, plano.id));
                        });
                    } else {
                        alert('Erro ao carregar planos: ' + response.message);
                    }
                }
            });
        });

        // Adicionar mensalidade manual
        $('#btnAdicionarMensalidade').click(function () {
            const data = {
                id_usuario: $('#cliente_adicionar').val(),
                id_plano: $('#plano_adicionar').val(),
                valor: $('#valor_adicionar').val(),
                data_vencimento: $('#vencimento_adicionar').val(),
                status: $('#status_adicionar').val(),
                observacao: $('#observacao_adicionar').val()
            };

            $.ajax({
                url: 'backend/adicionar_mensalidade.php',
                method: 'POST',
                data: data,
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        $('#adicionarMensalidadeModal').modal('hide');
                        location.reload();
                    } else {
                        alert('Erro ao adicionar: ' + response.message);
                    }
                }
            });
        });
    });
</script>

</body>
</html>