<?php
require_once 'config/database.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$conn = getDBConnection();
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $_SESSION['usuario_id']);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php include 'includes/head.php'; ?>
    <title>Perfil - Gestor de Mensalidades</title>
</head>
<body id="page-top">
    <div id="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include 'includes/navbar.php'; ?>
                
                <div class="container-fluid">
                    <h1 class="h3 mb-4 text-gray-800">Meu Perfil</h1>
                    
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Informações Pessoais</h6>
                                </div>
                                <div class="card-body">
                                    <form id="formPerfil">
                                        <div class="form-group">
                                            <label for="perfil_nome">Nome Completo</label>
                                            <input type="text" class="form-control" id="perfil_nome" value="<?php echo htmlspecialchars($usuario['nome']); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="perfil_email">Email</label>
                                            <input type="email" class="form-control" id="perfil_email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="perfil_telefone">Telefone</label>
                                            <input type="text" class="form-control" id="perfil_telefone" value="<?php echo htmlspecialchars($usuario['telefone']); ?>" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Atualizar Perfil</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Alterar Senha</h6>
                                </div>
                                <div class="card-body">
                                    <form id="formSenha">
                                        <div class="form-group">
                                            <label for="senha_atual">Senha Atual</label>
                                            <input type="password" class="form-control" id="senha_atual" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="nova_senha">Nova Senha</label>
                                            <input type="password" class="form-control" id="nova_senha" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="confirmar_senha">Confirmar Nova Senha</label>
                                            <input type="password" class="form-control" id="confirmar_senha" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Alterar Senha</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php include 'includes/footer.php'; ?>
        </div>
    </div>
    
    <?php include 'includes/scripts.php'; ?>
    
    <script>
    $(document).ready(function() {
        // Máscara para telefone
        $('#perfil_telefone').inputmask('(99) 99999-9999');
        
        // Atualizar perfil
        $('#formPerfil').submit(function(e) {
            e.preventDefault();
            
            // Aqui você pode adicionar o código para atualizar o perfil
            alert('Perfil atualizado com sucesso!');
        });
        
        // Alterar senha
        $('#formSenha').submit(function(e) {
            e.preventDefault();
            
            // Aqui você pode adicionar o código para alterar a senha
            alert('Senha alterada com sucesso!');
        });
    });
    </script>
</body>
</html>