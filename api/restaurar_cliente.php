<?php
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['status' => 'erro', 'mensagem' => 'Não autorizado']);
    exit;
}

$conn = getDBConnection();
$id_historico = isset($_POST['id_historico']) ? intval($_POST['id_historico']) : null;
$id_cliente = isset($_POST['id_cliente']) ? intval($_POST['id_cliente']) : null;

if (!$id_historico || !$id_cliente) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'IDs não informados']);
    exit;
}

try {
    $conn->begin_transaction();
    
    // 1. Obter dados do histórico
    $stmt = $conn->prepare("SELECT * FROM clientes_historico WHERE id = ? AND id_cliente_original = ?");
    $stmt->bind_param("ii", $id_historico, $id_cliente);
    $stmt->execute();
    $historico = $stmt->get_result()->fetch_assoc();
    
    if (!$historico) {
        throw new Exception("Registro de histórico não encontrado");
    }
    
    $dados_cliente = json_decode($historico['dados_cliente'], true);
    
    // 2. Inserir o cliente novamente
    $stmt = $conn->prepare("INSERT INTO usuarios 
                          (id, nome, email, telefone, senha, data_cadastro, ativo) 
                          VALUES (?, ?, ?, ?, ?, ?, TRUE)");
    $stmt->bind_param("isssss", 
        $dados_cliente['id'],
        $dados_cliente['nome'],
        $dados_cliente['email'],
        $dados_cliente['telefone'],
        $dados_cliente['senha'],
        $dados_cliente['data_cadastro']
    );
    $stmt->execute();
    
    // 3. Atualizar o histórico como restaurado
    $stmt = $conn->prepare("UPDATE clientes_historico 
                          SET restaurado = TRUE, data_restauracao = NOW(), restaurado_por = ?
                          WHERE id = ?");
    $stmt->bind_param("ii", $_SESSION['usuario_id'], $id_historico);
    $stmt->execute();
    
    $conn->commit();
    
    echo json_encode(['status' => 'sucesso']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'erro', 'mensagem' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>