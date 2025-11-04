<?php
require_once 'config/database.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$conn = getDBConnection();

// Contagem de registros
$stats = [
    'clientes' => $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE ativo = TRUE")->fetch_assoc()['total'],
    'planos' => $conn->query("SELECT COUNT(*) as total FROM planos WHERE ativo = TRUE")->fetch_assoc()['total'],
    'fornecedores' => $conn->query("SELECT COUNT(*) as total FROM fornecedores WHERE ativo = TRUE")->fetch_assoc()['total']
];

// Status das mensalidades
$mensalidades = [
    'pendentes' => $conn->query("SELECT COUNT(*) as total FROM mensalidades WHERE status = 'pendente' AND data_vencimento >= CURDATE()")->fetch_assoc()['total'],
    'vencidas' => $conn->query("SELECT COUNT(*) as total FROM mensalidades WHERE status = 'vencido' OR (status = 'pendente' AND data_vencimento < CURDATE())")->fetch_assoc()['total'],
    'pagas' => $conn->query("SELECT COUNT(*) as total FROM mensalidades WHERE status = 'pago' AND MONTH(data_pagamento) = MONTH(CURDATE())")->fetch_assoc()['total']
];

// Valor total recebido no mês
$receita_mensal = $conn->query("SELECT SUM(valor) as total FROM mensalidades WHERE status = 'pago' AND MONTH(data_pagamento) = MONTH(CURDATE())")->fetch_assoc()['total'] ?? 0;

// Mensalidades recentes (últimos 5 dias)
$mensalidades_recentes = [];
$result = $conn->query("SELECT m.*, u.nome as cliente_nome, p.nome as plano_nome 
                        FROM mensalidades m
                        JOIN usuarios u ON m.id_usuario = u.id
                        JOIN planos p ON m.id_plano = p.id
                        WHERE m.data_vencimento BETWEEN DATE_SUB(CURDATE(), INTERVAL 5 DAY) AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                        ORDER BY m.data_vencimento ASC
                        LIMIT 10");
while ($row = $result->fetch_assoc()) {
    $mensalidades_recentes[] = $row;
}

// Gráfico de receita últimos 6 meses
$receita_ultimos_meses = [];
$result = $conn->query("SELECT 
                            DATE_FORMAT(data_pagamento, '%Y-%m') as mes,
                            SUM(valor) as total
                        FROM mensalidades
                        WHERE status = 'pago' AND data_pagamento >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                        GROUP BY DATE_FORMAT(data_pagamento, '%Y-%m')
                        ORDER BY mes ASC");
while ($row = $result->fetch_assoc()) {
    $receita_ultimos_meses[] = $row;
}

// === NOVO: Atividades Recentes (mensalidades + clientes novos) ===
$atividades = [];

// Últimas mensalidades (10 últimas atividades)
$queryMensalidades = "
    SELECT m.*, u.nome as cliente_nome, p.nome as plano_nome
    FROM mensalidades m
    JOIN usuarios u ON m.id_usuario = u.id
    JOIN planos p ON m.id_plano = p.id
    WHERE m.data_vencimento BETWEEN DATE_SUB(CURDATE(), INTERVAL 5 DAY) AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
    ORDER BY m.data_vencimento DESC
    LIMIT 10
";
$result = $conn->query($queryMensalidades);
while ($row = $result->fetch_assoc()) {
    $atividades[] = [
        'tipo' => 'mensalidade',
        'status' => $row['status'],
        'cliente_nome' => $row['cliente_nome'],
        'plano_nome' => $row['plano_nome'],
        'data_vencimento' => $row['data_vencimento'],
        'data_pagamento' => $row['data_pagamento']
    ];
}

// Últimos 5 clientes cadastrados
$queryClientes = "
    SELECT nome, data_cadastro
    FROM usuarios
    WHERE ativo = TRUE
    ORDER BY data_cadastro DESC
    LIMIT 5
";
$resultClientes = $conn->query($queryClientes);
while ($row = $resultClientes->fetch_assoc()) {
    $atividades[] = [
        'tipo' => 'cliente',
        'cliente_nome' => $row['nome'],
        'data_cadastro' => $row['data_cadastro']
    ];
}

// Ordena todas as atividades por data (mais recentes primeiro)
usort($atividades, function($a, $b) {
    $dataA = $a['tipo'] === 'cliente' ? strtotime($a['data_cadastro']) : strtotime($a['data_vencimento']);
    $dataB = $b['tipo'] === 'cliente' ? strtotime($b['data_cadastro']) : strtotime($b['data_vencimento']);
    return $dataB <=> $dataA;
});

// Limita a 10 atividades totais para exibição
$atividades = array_slice($atividades, 0, 10);

$conn->close();
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php include 'includes/head.php'; ?>
    <title>Dashboard - Gestor de Mensalidades</title>
</head>
<body id="page-top">
    <div id="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include 'includes/navbar.php'; ?>
                
                <div class="container-fluid">
                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
                     <!--   <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                            <i class="fas fa-download fa-sm text-white-50"></i> Gerar Relatório
                        </a> -->
                    </div>

                    <!-- Content Row -->
                    <div class="row">
                        <!-- Total de Clientes -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Total de Clientes</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['clientes'] ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-users fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total de Planos -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Planos Ativos</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['planos'] ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-list fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total de Fornecedores -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                Fornecedores</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['fornecedores'] ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-truck fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Receita Mensal -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                Receita Mensal</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">R$ <?= number_format($receita_mensal, 2, ',', '.') ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Content Row -->
                    <div class="row">
                        <!-- Gráfico de Receita -->
                        <div class="col-xl-8 col-lg-7">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Receita Últimos 6 Meses</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-area">
                                        <canvas id="receitaChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Status das Mensalidades -->
                        <div class="col-xl-4 col-lg-5">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Status das Mensalidades</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-pie pt-4 pb-2">
                                        <canvas id="statusMensalidadesChart"></canvas>
                                    </div>
                                    <div class="mt-4 text-center small">
                                        <span class="mr-2">
                                            <i class="fas fa-circle text-success"></i> Pagas
                                        </span>
                                        <span class="mr-2">
                                            <i class="fas fa-circle text-primary"></i> Pendentes
                                        </span>
                                        <span class="mr-2">
                                            <i class="fas fa-circle text-danger"></i> Vencidas
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Content Row -->
                    <div class="row">
                        <!-- Mensalidades Recentes -->
                        <div class="col-lg-6 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Próximas Mensalidades</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered" width="100%" cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>Cliente</th>
                                                    <th>Plano</th>
                                                    <th>Valor</th>
                                                    <th>Vencimento</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($mensalidades_recentes as $mensalidade): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($mensalidade['cliente_nome']) ?></td>
                                                    <td><?= htmlspecialchars($mensalidade['plano_nome']) ?></td>
                                                    <td>R$ <?= number_format($mensalidade['valor'], 2, ',', '.') ?></td>
                                                    <td><?= date('d/m/Y', strtotime($mensalidade['data_vencimento'])) ?></td>
                                                    <td>
                                                        <?php 
                                                        $status_class = '';
                                                        if ($mensalidade['status'] == 'pago') {
                                                            $status_class = 'badge-success';
                                                        } elseif ($mensalidade['status'] == 'pendente' && strtotime($mensalidade['data_vencimento']) >= time()) {
                                                            $status_class = 'badge-primary';
                                                        } else {
                                                            $status_class = 'badge-danger';
                                                        }
                                                        ?>
                                                        <span class="badge <?= $status_class ?>">
                                                            <?= ucfirst($mensalidade['status']) ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                       <!-- Atividades Recentes -->
<div class="col-lg-6 mb-4">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Atividades Recentes</h6>
            <div class="dropdown no-arrow">
                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="timeline">
                <?php foreach ($mensalidades_recentes as $atividade): ?>
                    <?php
                        $status = $atividade['status'];
                        $icone = 'fa-info-circle';
                        $cor = 'bg-secondary';
                        $titulo = '';
                        $descricao = '';
                        $data_venc = strtotime($atividade['data_vencimento']);
                        $data_pag = !empty($atividade['data_pagamento']) ? strtotime($atividade['data_pagamento']) : null;

                        // Calcular "quando"
                        $ref = $data_pag ?? $data_venc;
                        $agora = time();
                        $diff_seg = $agora - $ref;
                        $dias = floor($diff_seg / 86400);
                        $quando = $dias == 0 ? 'Hoje' : ($dias > 0 ? "$dias dias atrás" : abs($dias) . " dias à frente");

                        if ($status === 'pago') {
                            $icone = 'fa-check';
                            $cor = 'bg-success';
                            $titulo = 'Pagamento confirmado';
                            $descricao = $atividade['cliente_nome'] . ' pagou o plano ' . $atividade['plano_nome'];
                        } elseif ($status === 'pendente' && $data_venc < $agora) {
                            $icone = 'fa-exclamation-triangle';
                            $cor = 'bg-warning';
                            $titulo = 'Mensalidade vencida';
                            $descricao = $atividade['cliente_nome'] . ' não pagou o plano ' . $atividade['plano_nome'];
                        } elseif ($status === 'pendente') {
                            $icone = 'fa-clock';
                            $cor = 'bg-info';
                            $titulo = 'Mensalidade pendente';
                            $descricao = $atividade['cliente_nome'] . ' deve pagar o plano ' . $atividade['plano_nome'];
                        } else {
                            $icone = 'fa-info-circle';
                            $cor = 'bg-secondary';
                            $titulo = 'Status indefinido';
                            $descricao = 'Atividade não categorizada';
                        }
                    ?>
                    <div class="timeline-item">
                        <div class="timeline-icon <?= $cor ?>">
                            <i class="fas <?= $icone ?>"></i>
                        </div>
                        <div class="timeline-content">
                            <h6><?= $titulo ?></h6>
                            <p><?= $descricao ?></p>
                            <small class="text-muted"><?= $quando ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

            
            <?php include 'includes/footer.php'; ?>
        </div>
    </div>
    
    <?php include 'includes/scripts.php'; ?>
    
    <script>
    // Gráfico de Receita
    var ctx = document.getElementById('receitaChart');
    var receitaChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [
                <?php foreach ($receita_ultimos_meses as $mes): ?>
                '<?= date('M/Y', strtotime($mes['mes'] . '-01')) ?>',
                <?php endforeach; ?>
            ],
            datasets: [{
                label: "Receita",
                lineTension: 0.3,
                backgroundColor: "rgba(78, 115, 223, 0.05)",
                borderColor: "rgba(78, 115, 223, 1)",
                pointRadius: 3,
                pointBackgroundColor: "rgba(78, 115, 223, 1)",
                pointBorderColor: "rgba(78, 115, 223, 1)",
                pointHoverRadius: 3,
                pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
                pointHoverBorderColor: "rgba(78, 115, 223, 1)",
                pointHitRadius: 10,
                pointBorderWidth: 2,
                data: [
                    <?php foreach ($receita_ultimos_meses as $mes): ?>
                    <?= $mes['total'] ?>,
                    <?php endforeach; ?>
                ],
            }],
        },
        options: {
            maintainAspectRatio: false,
            layout: {
                padding: {
                    left: 10,
                    right: 25,
                    top: 25,
                    bottom: 0
                }
            },
            scales: {
                xAxes: [{
                    gridLines: {
                        display: false,
                        drawBorder: false
                    },
                    ticks: {
                        maxTicksLimit: 7
                    }
                }],
                yAxes: [{
                    ticks: {
                        maxTicksLimit: 5,
                        padding: 10,
                        callback: function(value, index, values) {
                            return 'R$ ' + value.toLocaleString('pt-BR');
                        }
                    },
                    gridLines: {
                        color: "rgb(234, 236, 244)",
                        zeroLineColor: "rgb(234, 236, 244)",
                        drawBorder: false,
                        borderDash: [2],
                        zeroLineBorderDash: [2]
                    }
                }],
            },
            legend: {
                display: false
            },
            tooltips: {
                backgroundColor: "rgb(255,255,255)",
                bodyFontColor: "#858796",
                titleMarginBottom: 10,
                titleFontColor: '#6e707e',
                titleFontSize: 14,
                borderColor: '#dddfeb',
                borderWidth: 1,
                xPadding: 15,
                yPadding: 15,
                displayColors: false,
                intersect: false,
                mode: 'index',
                caretPadding: 10,
                callbacks: {
                    label: function(tooltipItem, chart) {
                        var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
                        return datasetLabel + ': R$ ' + tooltipItem.yLabel.toLocaleString('pt-BR');
                    }
                }
            }
        }
    });

    // Gráfico de Status das Mensalidades
    var ctx2 = document.getElementById('statusMensalidadesChart');
    var statusMensalidadesChart = new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: ["Pagas", "Pendentes", "Vencidas"],
            datasets: [{
                data: [<?= $mensalidades['pagas'] ?>, <?= $mensalidades['pendentes'] ?>, <?= $mensalidades['vencidas'] ?>],
                backgroundColor: ['#1cc88a', '#4e73df', '#e74a3b'],
                hoverBackgroundColor: ['#17a673', '#2e59d9', '#be2617'],
                hoverBorderColor: "rgba(234, 236, 244, 1)",
            }],
        },
        options: {
            maintainAspectRatio: false,
            tooltips: {
                backgroundColor: "rgb(255,255,255)",
                bodyFontColor: "#858796",
                borderColor: '#dddfeb',
                borderWidth: 1,
                xPadding: 15,
                yPadding: 15,
                displayColors: false,
                caretPadding: 10,
            },
            legend: {
                display: false
            },
            cutoutPercentage: 80,
        },
    });
    </script>

    <style>
    .timeline {
        position: relative;
        padding-left: 50px;
    }
    .timeline-item {
        position: relative;
        padding-bottom: 20px;
    }
    .timeline-icon {
        position: absolute;
        left: -40px;
        top: 0;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        text-align: center;
        line-height: 30px;
        color: white;
    }
    .timeline-content {
        padding: 10px 15px;
        background-color: #f8f9fc;
        border-radius: 5px;
    }
    .timeline-content h6 {
        margin-bottom: 5px;
        font-weight: 600;
    }
    .timeline-content p {
        margin-bottom: 5px;
        font-size: 14px;
    }
    .timeline-content small {
        font-size: 12px;
    }
    </style>
</body>
</html>