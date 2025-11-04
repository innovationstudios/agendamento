<?php
require_once 'config/database.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['status' => 'erro', 'mensagem' => 'Não autorizado']);
    exit;
}

$conn = getDBConnection();
$id = isset($_POST['id']) ? intval($_POST['id']) : null;

if (!$id) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'ID do cliente não informado']);
    exit;
}

try {
    // Soft delete (marcar como inativo)
    $stmt = $conn->prepare("UPDATE usuarios SET ativo = FALSE WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    echo json_encode(['status' => 'sucesso']);
} catch (mysqli_sql_exception $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao excluir cliente: ' . $e->getMessage()]);
} finally {
    $conn->close();
}
?>