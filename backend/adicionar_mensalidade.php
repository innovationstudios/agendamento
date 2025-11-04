<?php
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método inválido']);
    exit;
}

$conn = getDBConnection();

$id_usuario = $_POST['id_usuario'] ?? null;
$id_plano = $_POST['id_plano'] ?? null;
$valor = $_POST['valor'] ?? null;
$data_vencimento = $_POST['data_vencimento'] ?? null;
$status = $_POST['status'] ?? 'pendente';
$observacao = $_POST['observacao'] ?? '';

if (!$id_usuario || !$id_plano || !$valor || !$data_vencimento) {
    echo json_encode(['success' => false, 'message' => 'Campos obrigatórios faltando']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO mensalidades (id_usuario, id_plano, valor, data_vencimento, status, observacao, data_cadastro) VALUES (?, ?, ?, ?, ?, ?, NOW())");
$stmt->bind_param('iidsss', $id_usuario, $id_plano, $valor, $data_vencimento, $status, $observacao);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Mensalidade adicionada com sucesso']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao adicionar mensalidade: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
