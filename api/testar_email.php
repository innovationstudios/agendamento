<?php
require_once '../config/database.php';

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

$email = $_POST['email'] ?? '';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Email inválido']);
    exit;
}

// Carregar configurações de email
$conn = getDBConnection();
$config = [];
$result = $conn->query("SELECT chave, valor FROM configuracoes WHERE tipo = 'email'");
while ($row = $result->fetch_assoc()) {
    $config[$row['chave']] = $row['valor'];
}
$conn->close();

try {
    // Configurar PHPMailer
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    // Configurações do servidor
    $mail->isSMTP();
    $mail->Host = $config['host'] ?? '';
    $mail->SMTPAuth = true;
    $mail->Username = $config['usuario'] ?? '';
    $mail->Password = $config['senha'] ?? '';
    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = $config['porta'] ?? 587;
    
    // Remetente e destinatário
    $mail->setFrom($config['usuario'] ?? 'noreply@seusistema.com', 'Sistema de Mensalidades');
    $mail->addAddress($email);
    
    // Conteúdo do email
    $mail->isHTML(true);
    $mail->Subject = 'Teste de Configuração de Email';
    $mail->Body = 'Este é um email de teste para verificar as configurações do sistema.';
    $mail->AltBody = 'Este é um email de teste para verificar as configurações do sistema.';
    
    $mail->send();
    
    echo json_encode([
        'success' => true,
        'message' => 'Email de teste enviado com sucesso para ' . $email
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao enviar email: ' . $mail->ErrorInfo
    ]);
}
?>