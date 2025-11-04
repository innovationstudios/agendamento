<?php
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../../index.php');
    exit;
}

$conn = getDBConnection();

// Listar fornecedores
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = "SELECT * FROM fornecedores WHERE ativo = TRUE ORDER BY nome";
    $result = $conn->query($query);
    $fornecedores = [];
    
    while ($row = $result->fetch_assoc()) {
        $fornecedores[] = $row;
    }
    
    echo json_encode($fornecedores);
}

// Adicionar fornecedor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'adicionar') {
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $contato = $_POST['contato'];
    $telefone = $_POST['telefone'];
    $email = $_POST['email'];
    
    $stmt = $conn->prepare("INSERT INTO fornecedores (nome, descricao, contato, telefone, email) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $nome, $descricao, $contato, $telefone, $email);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Fornecedor adicionado com sucesso!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao adicionar fornecedor.']);
    }
}

// Atualizar fornecedor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'atualizar') {
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $contato = $_POST['contato'];
    $telefone = $_POST['telefone'];
    $email = $_POST['email'];
    
    $stmt = $conn->prepare("UPDATE fornecedores SET nome = ?, descricao = ?, contato = ?, telefone = ?, email = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $nome, $descricao, $contato, $telefone, $email, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Fornecedor atualizado com sucesso!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar fornecedor.']);
    }
}

// Remover fornecedor (soft delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'remover') {
    $id = $_POST['id'];
    
    $stmt = $conn->prepare("UPDATE fornecedores SET ativo = FALSE WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Fornecedor removido com sucesso!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao remover fornecedor.']);
    }
}

$conn->close();
?>