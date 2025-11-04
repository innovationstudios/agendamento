<?php
require_once '../config/database.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método inválido']);
    exit;
}

$id = $_POST['id'] ?? null;
$data_pagamento = $_POST['data_pagamento'] ?? null;
$valor_pago = $_POST['valor_pago'] ?? null;
$observacao = $_POST['observacao'] ?? '';

if (!$id || !$data_pagamento || !$valor_pago) {
    echo json_encode(['success' => false, 'message' => 'Campos obrigatórios faltando']);
    exit;
}

$conn = getDBConnection();

$stmt = $conn->prepare("UPDATE mensalidades SET data_pagamento = ?, valor = ?, status = 'pago', observacao = ? WHERE id = ?");
$stmt->bind_param('sdsi', $data_pagamento, $valor_pago, $observacao, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Pagamento registrado com sucesso']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao registrar pagamento: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
