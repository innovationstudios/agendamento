<?php
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(["erro" => "Usuário não autenticado."]);
    exit;
}

// Verifica se os dados vieram via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $descricao = $_POST['descricao'] ?? '';
    $contato = $_POST['contato'] ?? '';
    $telefone = $_POST['telefone'] ?? '';
    $email = $_POST['email'] ?? '';
    $ativo = 1;

    if (empty($nome) || empty($telefone)) {
        http_response_code(400);
        echo json_encode(["erro" => "Campos obrigatórios não preenchidos."]);
        exit;
    }

    $conn = getDBConnection();
    $stmt = $conn->prepare("INSERT INTO fornecedores (nome, descricao, contato, telefone, email, ativo) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssi", $nome, $descricao, $contato, $telefone, $email, $ativo);

    if ($stmt->execute()) {
        echo json_encode(["sucesso" => true]);
    } else {
        http_response_code(500);
        echo json_encode(["erro" => "Erro ao salvar no banco de dados."]);
    }

    $stmt->close();
    $conn->close();
} else {
    http_response_code(405);
    echo json_encode(["erro" => "Método não permitido."]);
}
?>
