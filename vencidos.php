<?php
require_once 'config/database.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php include 'includes/head.php'; ?>
    <title>Mensalidades Vencidas - Gestor de Mensalidades</title>
</head>
<body id="page-top">
    <div id="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include 'includes/navbar.php'; ?>
                
                <div class="container-fluid">
                    <h1 class="h3 mb-4 text-gray-800">Mensalidades Vencidas</h1>
                    
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Lista de Mensalidades Vencidas</h6>
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
                                            <th>Dias em Atraso</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tabela-mensalidades">
                                        <!-- Dados serão carregados via AJAX -->
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
    
    <!-- Modal para registrar pagamento -->
    <div class="modal fade" id="registrarPagamentoModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Registrar Pagamento</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formPagamento">
                        <input type="hidden" id="mensalidade_id">
                        <div class="form-group">
                            <label for="data_pagamento">Data do Pagamento</label>
                            <input type="date" class="form-control" id="data_pagamento" required value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="observacao">Observação</label>
                            <textarea class="form-control" id="observacao" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                    <button class="btn btn-primary" id="btnRegistrarPagamento">Registrar</button>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/scripts.php'; ?>
    
    <script>
    $(document).ready(function() {
        carregarMensalidadesVencidas();
        
        // Registrar pagamento
        $('#btnRegistrarPagamento').click(function() {
            const id = $('#mensalidade_id').val();
            const data_pagamento = $('#data_pagamento').val();
            const observacao = $('#observacao').val();
            
            $.ajax({
                url: 'src/controllers/mensalidades.php',
                method: 'POST',
                data: {
                    acao: 'registrar_pagamento',
                    id: id,
                    data_pagamento: data_pagamento,
                    observacao: observacao
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#registrarPagamentoModal').modal('hide');
                        carregarMensalidadesVencidas();
                        alert('Pagamento registrado com sucesso!');
                    } else {
                        alert('Erro ao registrar pagamento: ' + response.message);
                    }
                }
            });
        });
    });
    
    function carregarMensalidadesVencidas() {
        $.ajax({
            url: 'src/controllers/mensalidades.php?filtro=vencidas',
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                $('#tabela-mensalidades').empty();
                
                data.forEach(function(mensalidade) {
                    const vencimento = new Date(mensalidade.data_vencimento);
                    const hoje = new Date();
                    const diffTime = Math.abs(hoje - vencimento);
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                    
                    const row = `
                        <tr>
                            <td>${mensalidade.cliente_nome}</td>
                            <td>${mensalidade.plano_nome}</td>
                            <td>R$ ${parseFloat(mensalidade.valor).toFixed(2).replace('.', ',')}</td>
                            <td>${vencimento.toLocaleDateString('pt-BR')}</td>
                            <td>${diffDays}</td>
                            <td>
                                <button class="btn btn-success btn-sm btn-registrar" data-id="${mensalidade.id}">
                                    <i class="fas fa-check"></i> Registrar Pagamento
                                </button>
                            </td>
                        </tr>
                    `;
                    
                    $('#tabela-mensalidades').append(row);
                });
                
                // Configurar eventos dos botões
                $('.btn-registrar').click(function() {
                    const id = $(this).data('id');
                    $('#mensalidade_id').val(id);
                    $('#registrarPagamentoModal').modal('show');
                });
            }
        });
    }
    </script>
</body>
</html>