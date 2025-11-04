<?php
header('Content-Type: application/json');
require_once '../config/database.php';

session_start();
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

$conn = getDBConnection();

try {
    // Buscar usuários ativos
    $usuarios_result = $conn->query("SELECT id FROM usuarios WHERE ativo = TRUE");
    if (!$usuarios_result) throw new Exception($conn->error);

    // Buscar planos ativos
    $planos_result = $conn->query("SELECT id, valor FROM planos WHERE ativo = TRUE");
    if (!$planos_result) throw new Exception($conn->error);

    $usuarios = [];
    while ($u = $usuarios_result->fetch_assoc()) {
        $usuarios[] = $u['id'];
    }

    $planos = [];
    while ($p = $planos_result->fetch_assoc()) {
        $planos[] = ['id' => $p['id'], 'valor' => $p['valor']];
    }

    if (count($usuarios) === 0 || count($planos) === 0) {
        echo json_encode(['success' => false, 'message' => 'Nenhum usuário ou plano ativo encontrado']);
        exit;
    }

    // Data do próximo mês para vencimento
    $data_hoje = new DateTime();
    $data_hoje->modify('first day of next month');
    $data_vencimento = $data_hoje->format('Y-m-d');
    $mes_ano = $data_hoje->format('Y-m');

    $inseridos = 0;

    // Preparar statements
    $check_stmt = $conn->prepare("SELECT COUNT(*) as total FROM mensalidades WHERE id_usuario = ? AND id_plano = ? AND DATE_FORMAT(data_vencimento, '%Y-%m') = ?");
    $insert_stmt = $conn->prepare("INSERT INTO mensalidades (id_usuario, id_plano, valor, data_vencimento, status) VALUES (?, ?, ?, ?, 'pendente')");

    foreach ($usuarios as $usuario_id) {
        foreach ($planos as $plano) {
            // Verifica se mensalidade já existe
            $check_stmt->bind_param('iis', $usuario_id, $plano['id'], $mes_ano);
            $check_stmt->execute();
            $result = $check_stmt->get_result()->fetch_assoc();

            if ($result['total'] == 0) {
                // Insere nova mensalidade
                $insert_stmt->bind_param('iids', $usuario_id, $plano['id'], $plano['valor'], $data_vencimento);
                if ($insert_stmt->execute()) {
                    $inseridos++;
                }
            }
        }
    }

    $check_stmt->close();
    $insert_stmt->close();
    $conn->close();

    echo json_encode(['success' => true, 'message' => "Mensalidades geradas com sucesso. Total: $inseridos"]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
}
