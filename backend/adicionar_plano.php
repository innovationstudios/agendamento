<?php
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

$nome = $_POST['nome'] ?? '';
$descricao = $_POST['descricao'] ?? '';
$valor = $_POST['valor'] ?? 0;
$ciclo = $_POST['ciclo'] ?? '';
$fornecedor = empty($fornecedor) ? null : intval($fornecedor);

if (!$nome || !$valor || !$ciclo) {
    echo json_encode(['success' => false, 'message' => 'Campos obrigatórios não preenchidos']);
    exit;
}

$conn = getDBConnection();
$stmt = $conn->prepare("INSERT INTO planos (nome, descricao, valor, ciclo, id_fornecedor, ativo, data_cadastro) VALUES (?, ?, ?, ?, ?, TRUE, NOW())");
$stmt->bind_param('ssdsi', $nome, $descricao, $valor, $ciclo, $fornecedor);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
