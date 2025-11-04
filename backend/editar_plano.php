<?php
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

$conn = getDBConnection();

$id = $_POST['id'] ?? null;
$nome = $_POST['nome'] ?? null;
$descricao = $_POST['descricao'] ?? null;
$valor = $_POST['valor'] ?? null;
$ciclo = $_POST['ciclo'] ?? null;
$fornecedor = $_POST['fornecedor'] ?? null;

if (!$id || !$nome || !$valor || !$ciclo) {
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
    exit;
}

try {
    $stmt = $conn->prepare("UPDATE planos SET 
                            nome = ?, 
                            descricao = ?, 
                            valor = ?, 
                            ciclo = ?, 
                            id_fornecedor = ?
                            WHERE id = ?");
    
    // Se fornecedor estiver vazio, definimos como NULL
    $fornecedorValue = empty($fornecedor) ? null : $fornecedor;
    
    $stmt->bind_param("ssdssi", $nome, $descricao, $valor, $ciclo, $fornecedorValue, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Plano atualizado com sucesso']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar plano: ' . $conn->error]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
}

$conn->close();
?> 