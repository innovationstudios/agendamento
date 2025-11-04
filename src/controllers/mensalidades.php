<?php
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../../index.php');
    exit;
}

$conn = getDBConnection();

// Listar mensalidades com filtros
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $filtro = isset($_GET['filtro']) ? $_GET['filtro'] : 'todas';
    
    $query = "SELECT m.*, u.nome as cliente_nome, p.nome as plano_nome, f.nome as fornecedor_nome
              FROM mensalidades m
              JOIN usuarios u ON m.id_usuario = u.id
              JOIN planos p ON m.id_plano = p.id
              LEFT JOIN fornecedores f ON p.id_fornecedor = f.id
              WHERE u.ativo = TRUE AND p.ativo = TRUE";
    
    switch ($filtro) {
        case 'pendentes':
            $query .= " AND m.status = 'pendente' AND m.data_vencimento >= CURDATE()";
            break;
        case 'vencidas':
            $query .= " AND (m.status = 'vencido' OR (m.status = 'pendente' AND m.data_vencimento < CURDATE()))";
            break;
        case 'pagas':
            $query .= " AND m.status = 'pago'";
            break;
    }
    
    $query .= " ORDER BY m.data_vencimento";
    
    $result = $conn->query($query);
    $mensalidades = [];
    
    while ($row = $result->fetch_assoc()) {
        $mensalidades[] = $row;
    }
    
    echo json_encode($mensalidades);
}

// Registrar pagamento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'registrar_pagamento') {
    $id = $_POST['id'];
    $data_pagamento = $_POST['data_pagamento'];
    $observacao = $_POST['observacao'];
    
    $stmt = $conn->prepare("UPDATE mensalidades SET status = 'pago', data_pagamento = ?, observacao = ? WHERE id = ?");
    $stmt->bind_param("ssi", $data_pagamento, $observacao, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Pagamento registrado com sucesso!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao registrar pagamento.']);
    }
}

// Gerar mensalidades (para o próximo ciclo)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'gerar_mensalidades') {
    // Obter todos os usuários com planos ativos
    $query = "SELECT u.id as id_usuario, p.id as id_plano, p.valor, p.ciclo 
              FROM usuarios u
              JOIN mensalidades m ON u.id = m.id_usuario
              JOIN planos p ON m.id_plano = p.id
              WHERE u.ativo = TRUE AND p.ativo = TRUE
              AND m.status = 'pago'
              GROUP BY u.id, p.id";
    
    $result = $conn->query($query);
    
    while ($row = $result->fetch_assoc()) {
        $id_usuario = $row['id_usuario'];
        $id_plano = $row['id_plano'];
        $valor = $row['valor'];
        
        // Calcular data de vencimento baseada no ciclo
        $data_vencimento = date('Y-m-d');
        
        switch ($row['ciclo']) {
            case 'mensal':
                $data_vencimento = date('Y-m-d', strtotime('+1 month'));
                break;
            case 'trimestral':
                $data_vencimento = date('Y-m-d', strtotime('+3 months'));
                break;
            case 'semestral':
                $data_vencimento = date('Y-m-d', strtotime('+6 months'));
                break;
            case 'anual':
                $data_vencimento = date('Y-m-d', strtotime('+1 year'));
                break;
        }
        
        // Verificar se já existe uma mensalidade para este plano e usuário no próximo ciclo
        $check = $conn->prepare("SELECT id FROM mensalidades WHERE id_usuario = ? AND id_plano = ? AND data_vencimento = ?");
        $check->bind_param("iis", $id_usuario, $id_plano, $data_vencimento);
        $check->execute();
        $check->store_result();
        
        if ($check->num_rows === 0) {
            $insert = $conn->prepare("INSERT INTO mensalidades (id_usuario, id_plano, valor, data_vencimento, status) VALUES (?, ?, ?, ?, 'pendente')");
            $insert->bind_param("iids", $id_usuario, $id_plano, $valor, $data_vencimento);
            $insert->execute();
        }
    }
    
    echo json_encode(['success' => true, 'message' => 'Mensalidades geradas com sucesso!']);
}

$conn->close();
?>