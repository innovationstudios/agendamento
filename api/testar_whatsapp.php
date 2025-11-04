<?php
require_once '../config/database.php';
require_once '../vendor/autoload.php'; // Para carregar o EvolutionApiPlugin

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

// Carregar configurações do WhatsApp
$conn = getDBConnection();
$config = [];
$result = $conn->query("SELECT chave, valor FROM configuracoes WHERE tipo = 'whatsapp'");
while ($row = $result->fetch_assoc()) {
    $config[$row['chave']] = $row['valor'];
}
$conn->close();

if (empty($config['api_key'])) {
    echo json_encode(['success' => false, 'message' => 'API Key não configurada']);
    exit;
}

try {
    // Instanciar Evolution API
    $evolutionApi = new EvolutionApiPlugin\EvolutionApi(
        $config['api_key'],
        $config['api_url'] ?? ''
    );
    
    // Testar listagem de instâncias (método simples para teste)
    $response = $evolutionApi->fetchInstance($config['numero'] ?? '');
    
    if (isset($response['error'])) {
        throw new Exception($response['error']);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Conexão com Evolution API estabelecida com sucesso!'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao conectar com Evolution API: ' . $e->getMessage()
    ]);
}
?>