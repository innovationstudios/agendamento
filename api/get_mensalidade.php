<?php
require_once '../config/database.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID não fornecido']);
    exit;
}

$conn = getDBConnection();

$stmt = $conn->prepare("SELECT * FROM mensalidades WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();

$result = $stmt->get_result();
if ($mensalidade = $result->fetch_assoc()) {
    echo json_encode(['success' => true, 'data' => $mensalidade]);
} else {
    echo json_encode(['success' => false, 'message' => 'Mensalidade não encontrada']);
}

$stmt->close();
$conn->close();
