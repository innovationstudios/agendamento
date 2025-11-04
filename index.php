<?php
require_once 'config/database.php';
session_start();

if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    
    $conn = getDBConnection();
    // Alterado para comparar a senha diretamente (sem hash)
    $stmt = $conn->prepare("SELECT id, nome, senha FROM usuarios WHERE email = ? AND senha = ?");
    $stmt->bind_param("ss", $email, $senha);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $usuario = $result->fetch_assoc();
        
        // Debug: Mostrar informações (remover em produção)
        echo "<pre>Usuário encontrado: ";
        print_r($usuario);
        echo "Senha digitada: " . $senha . "\n";
        echo "Senha armazenada: " . $usuario['senha'] . "\n";
        echo "Verificação: " . ($senha === $usuario['senha'] ? 'true' : 'false') . "</pre>";
        
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        header('Location: dashboard.php');
        exit;
    } else {
        echo "<pre>Credenciais não encontradas ou incorretas</pre>";
    }
    
    $erro = "Email ou senha incorretos!";
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <!-- Incluir head do SB Admin 2 -->
    <?php include 'includes/head.php'; ?>
</head>
<body class="bg-gradient-primary">
    <div class="container">
        <!-- Conteúdo do login -->
        <div class="row justify-content-center">
            <div class="col-xl-6 col-lg-6 col-md-9">
                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <div class="p-5">
                            <div class="text-center">
                                <h1 class="h4 text-gray-900 mb-4">Bem-vindo ao Gestor de Mensalidades</h1>
                            </div>
                            <?php if (isset($erro)): ?>
                                <div class="alert alert-danger"><?php echo $erro; ?></div>
                            <?php endif; ?>
                            <form class="user" method="POST">
                                <div class="form-group">
                                    <input type="email" class="form-control form-control-user" name="email" placeholder="Email" required>
                                </div>
                                <div class="form-group">
                                    <input type="password" class="form-control form-control-user" name="senha" placeholder="Senha" required>
                                </div>
                                <button type="submit" class="btn btn-primary btn-user btn-block">
                                    Login
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Incluir scripts do SB Admin 2 -->
    <?php include 'includes/scripts.php'; ?>
</body>
</html>