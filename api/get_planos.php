<?php
require_once '../config/database.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

$conn = getDBConnection();

$query = "SELECT id, nome FROM planos WHERE ativo = TRUE ORDER BY nome";
$result = $conn->query($query);

$planos = [];
while ($row = $result->fetch_assoc()) {
    $planos[] = $row;
}

echo json_encode(['success' => true, 'data' => $planos]);

$conn->close();
