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
$nome = trim($_POST['nome']);
$email = trim($_POST['email']);
$telefone = trim($_POST['telefone']);
$senha = isset($_POST['senha']) ? trim($_POST['senha']) : null;

// Validações básicas
if (empty($nome) || empty($email) || empty($telefone)) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Preencha todos os campos obrigatórios']);
    exit;
}

try {
    if ($id) {
        // Atualizar cliente existente
        if ($senha) {
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE usuarios SET nome = ?, email = ?, telefone = ?, senha = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $nome, $email, $telefone, $senhaHash, $id);
        } else {
            $stmt = $conn->prepare("UPDATE usuarios SET nome = ?, email = ?, telefone = ? WHERE id = ?");
            $stmt->bind_param("sssi", $nome, $email, $telefone, $id);
        }
    } else {
        // Inserir novo cliente
        if (empty($senha)) {
            echo json_encode(['status' => 'erro', 'mensagem' => 'A senha é obrigatória para novo cliente']);
            exit;
        }
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, telefone, senha, data_cadastro, ativo) VALUES (?, ?, ?, ?, NOW(), TRUE)");
        $stmt->bind_param("ssss", $nome, $email, $telefone, $senhaHash);
    }
    
    $stmt->execute();
    
    echo json_encode(['status' => 'sucesso']);
} catch (mysqli_sql_exception $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao salvar cliente: ' . $e->getMessage()]);
} finally {
    $conn->close();
}
?>