<?php
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../../index.php');
    exit;
}

$conn = getDBConnection();

// Listar templates
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = "SELECT * FROM mensagem_templates WHERE ativo = TRUE ORDER BY dias_antes";
    $result = $conn->query($query);
    $templates = [];
    
    while ($row = $result->fetch_assoc()) {
        $templates[] = $row;
    }
    
    echo json_encode($templates);
}

// Adicionar template
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'adicionar') {
    $titulo = $_POST['titulo'];
    $mensagem = $_POST['mensagem'];
    $dias_antes = $_POST['dias_antes'];
    
    $stmt = $conn->prepare("INSERT INTO mensagem_templates (titulo, mensagem, dias_antes) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $titulo, $mensagem, $dias_antes);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Template adicionado com sucesso!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao adicionar template.']);
    }
}

// Atualizar template
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'atualizar') {
    $id = $_POST['id'];
    $titulo = $_POST['titulo'];
    $mensagem = $_POST['mensagem'];
    $dias_antes = $_POST['dias_antes'];
    
    $stmt = $conn->prepare("UPDATE mensagem_templates SET titulo = ?, mensagem = ?, dias_antes = ? WHERE id = ?");
    $stmt->bind_param("ssii", $titulo, $mensagem, $dias_antes, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Template atualizado com sucesso!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar template.']);
    }
}

// Remover template (soft delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'remover') {
    $id = $_POST['id'];
    
    $stmt = $conn->prepare("UPDATE mensagem_templates SET ativo = FALSE WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Template removido com sucesso!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao remover template.']);
    }
}

$conn->close();
?>