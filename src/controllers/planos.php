<?php
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../../index.php');
    exit;
}

$conn = getDBConnection();

// Listar planos com informações de fornecedor
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = "SELECT p.*, f.nome as fornecedor_nome 
              FROM planos p 
              LEFT JOIN fornecedores f ON p.id_fornecedor = f.id 
              WHERE p.ativo = TRUE 
              ORDER BY p.nome";
    $result = $conn->query($query);
    $planos = [];
    
    while ($row = $result->fetch_assoc()) {
        $planos[] = $row;
    }
    
    echo json_encode($planos);
}

// Adicionar plano
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'adicionar') {
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $valor = $_POST['valor'];
    $ciclo = $_POST['ciclo'];
    $id_fornecedor = $_POST['id_fornecedor'];
    
    $stmt = $conn->prepare("INSERT INTO planos (nome, descricao, valor, ciclo, id_fornecedor) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdsi", $nome, $descricao, $valor, $ciclo, $id_fornecedor);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Plano adicionado com sucesso!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao adicionar plano.']);
    }
}

// Atualizar plano
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'atualizar') {
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $valor = $_POST['valor'];
    $ciclo = $_POST['ciclo'];
    $id_fornecedor = $_POST['id_fornecedor'];
    
    $stmt = $conn->prepare("UPDATE planos SET nome = ?, descricao = ?, valor = ?, ciclo = ?, id_fornecedor = ? WHERE id = ?");
    $stmt->bind_param("ssdsii", $nome, $descricao, $valor, $ciclo, $id_fornecedor, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Plano atualizado com sucesso!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar plano.']);
    }
}

// Remover plano (soft delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'remover') {
    $id = $_POST['id'];
    
    $stmt = $conn->prepare("UPDATE planos SET ativo = FALSE WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Plano removido com sucesso!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao remover plano.']);
    }
}

$conn->close();
?>