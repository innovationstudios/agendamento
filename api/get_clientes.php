<?php
require_once '../config/database.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

$conn = getDBConnection();

$query = "SELECT id, nome FROM usuarios WHERE ativo = TRUE ORDER BY nome";
$result = $conn->query($query);

$clientes = [];
while ($row = $result->fetch_assoc()) {
    $clientes[] = $row;
}

echo json_encode(['success' => true, 'data' => $clientes]);

$conn->close();
