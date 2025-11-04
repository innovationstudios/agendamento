<?php
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['status' => 'erro', 'mensagem' => 'Não autorizado']);
    exit;
}

$conn = getDBConnection();
$id = isset($_POST['id']) ? intval($_POST['id']) : null;
$tipo = $_POST['tipo'] ?? 'soft';

if (!$id) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'ID não informado']);
    exit;
}

try {
    if ($tipo === 'soft') {
        // Soft delete - marca como inativo e registra no histórico
        $conn->begin_transaction();
        
        // 1. Obter dados do cliente
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $cliente = $stmt->get_result()->fetch_assoc();
        
        if (!$cliente) {
            throw new Exception("Cliente não encontrado");
        }
        
        // 2. Inserir no histórico
        $stmt = $conn->prepare("INSERT INTO clientes_historico 
                              (id_cliente_original, dados_cliente, data_exclusao, excluido_por) 
                              VALUES (?, ?, NOW(), ?)");
        $dados_cliente = json_encode($cliente);
        $stmt->bind_param("isi", $id, $dados_cliente, $_SESSION['usuario_id']);
        $stmt->execute();
        
        // 3. Marcar como inativo (em vez de deletar)
        $stmt = $conn->prepare("UPDATE usuarios SET ativo = FALSE WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        $conn->commit();
        
        echo json_encode(['status' => 'sucesso']);
    } else {
        // Hard delete - exclusão definitiva do histórico
        // 1. Obter registro do histórico
        $stmt = $conn->prepare("SELECT * FROM clientes_historico WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $historico = $stmt->get_result()->fetch_assoc();
        
        if (!$historico) {
            throw new Exception("Registro de histórico não encontrado");
        }
        
        // 2. Excluir definitivamente
        $stmt = $conn->prepare("DELETE FROM clientes_historico WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        echo json_encode(['status' => 'sucesso']);
    }
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'erro', 'mensagem' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>