<?php
require_once 'config/database.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

// Carregar configurações existentes
$conn = getDBConnection();
$configuracoes = [
    'email' => [],
    'whatsapp' => []
];

// Buscar configurações de email
$result = $conn->query("SELECT * FROM configuracoes WHERE tipo = 'email'");
while ($row = $result->fetch_assoc()) {
    $configuracoes['email'][$row['chave']] = $row['valor'];
}

// Buscar configurações do WhatsApp (Evolution API)
$result = $conn->query("SELECT * FROM configuracoes WHERE tipo = 'whatsapp'");
while ($row = $result->fetch_assoc()) {
    $configuracoes['whatsapp'][$row['chave']] = $row['valor'];
}

// Processar formulários de atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['tipo']) && $_POST['tipo'] === 'email') {
        // Atualizar configurações de email
        $stmt = $conn->prepare("REPLACE INTO configuracoes (tipo, chave, valor) VALUES (?, ?, ?)");
        
        $campos = ['host', 'porta', 'usuario', 'senha'];
        foreach ($campos as $campo) {
            $valor = $_POST[$campo] ?? '';
            $stmt->bind_param("sss", $_POST['tipo'], $campo, $valor);
            $stmt->execute();
        }
        
        $_SESSION['mensagem'] = ['tipo' => 'success', 'texto' => 'Configurações de email atualizadas com sucesso!'];
    } 
    elseif (isset($_POST['tipo']) && $_POST['tipo'] === 'whatsapp') {
        // Atualizar configurações do WhatsApp (Evolution API)
        $stmt = $conn->prepare("REPLACE INTO configuracoes (tipo, chave, valor) VALUES (?, ?, ?)");
        
        $campos = ['api_key', 'api_url', 'numero'];
        foreach ($campos as $campo) {
            $valor = $_POST[$campo] ?? '';
            $stmt->bind_param("sss", $_POST['tipo'], $campo, $valor);
            $stmt->execute();
        }
        
        $_SESSION['mensagem'] = ['tipo' => 'success', 'texto' => 'Configurações do WhatsApp atualizadas com sucesso!'];
    }
    
    header('Location: configuracoes.php');
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php include 'includes/head.php'; ?>
    <title>Configurações - Gestor de Mensalidades</title>
</head>
<body id="page-top">
    <div id="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include 'includes/navbar.php'; ?>
                
                <div class="container-fluid">
                    <h1 class="h3 mb-4 text-gray-800">Configurações do Sistema</h1>
                    
                    <?php if (isset($_SESSION['mensagem'])): ?>
                    <div class="alert alert-<?= $_SESSION['mensagem']['tipo'] ?> alert-dismissible fade show">
                        <?= $_SESSION['mensagem']['texto'] ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php unset($_SESSION['mensagem']); endif; ?>
                    
                    <div class="row">
                        <!-- Configurações do WhatsApp (Evolution API) -->
                        <div class="col-lg-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Evolution API (WhatsApp)</h6>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <input type="hidden" name="tipo" value="whatsapp">
                                        <div class="form-group">
                                            <label for="whatsapp_api_key">API Key</label>
                                            <input type="text" class="form-control" id="whatsapp_api_key" 
                                                   name="api_key" value="<?= htmlspecialchars($configuracoes['whatsapp']['api_key'] ?? '') ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="whatsapp_api_url">API URL</label>
                                            <input type="text" class="form-control" id="whatsapp_api_url" 
                                                   name="api_url" value="<?= htmlspecialchars($configuracoes['whatsapp']['api_url'] ?? '') ?>">
                                            <small class="form-text text-muted">Deixe em branco para usar o padrão da Evolution API</small>
                                        </div>
                                        <div class="form-group">
                                            <label for="whatsapp_numero">Número do WhatsApp</label>
                                            <input type="text" class="form-control" id="whatsapp_numero" 
                                                   name="numero" value="<?= htmlspecialchars($configuracoes['whatsapp']['numero'] ?? '') ?>" required>
                                            <small class="form-text text-muted">Número associado à instância no formato 5511999999999</small>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Salvar Configurações</button>
                                    </form>
                                    
                                    <hr>
                                    
                                    <div class="mt-4">
                                        <h6 class="font-weight-bold">Testar Conexão</h6>
                                        <button id="btnTestarWhatsApp" class="btn btn-info">
                                            <i class="fas fa-plug"></i> Testar Conexão com Evolution API
                                        </button>
                                        <div id="testeWhatsAppResult" class="mt-2"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Configurações de Email -->
                        <div class="col-lg-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Configurações de Email</h6>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <input type="hidden" name="tipo" value="email">
                                        <div class="form-group">
                                            <label for="email_host">SMTP Host</label>
                                            <input type="text" class="form-control" id="email_host" 
                                                   name="host" value="<?= htmlspecialchars($configuracoes['email']['host'] ?? '') ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="email_porta">Porta SMTP</label>
                                            <input type="number" class="form-control" id="email_porta" 
                                                   name="porta" value="<?= htmlspecialchars($configuracoes['email']['porta'] ?? '') ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="email_usuario">Usuário</label>
                                            <input type="text" class="form-control" id="email_usuario" 
                                                   name="usuario" value="<?= htmlspecialchars($configuracoes['email']['usuario'] ?? '') ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="email_senha">Senha</label>
                                            <input type="password" class="form-control" id="email_senha" 
                                                   name="senha" value="<?= htmlspecialchars($configuracoes['email']['senha'] ?? '') ?>" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Salvar Configurações</button>
                                    </form>
                                    
                                    <hr>
                                    
                                    <div class="mt-4">
                                        <h6 class="font-weight-bold">Testar Conexão</h6>
                                        <div class="form-group">
                                            <label for="email_teste">Email para teste</label>
                                            <input type="email" class="form-control" id="email_teste" 
                                                   placeholder="Digite um email para enviar teste">
                                        </div>
                                        <button id="btnTestarEmail" class="btn btn-info">
                                            <i class="fas fa-paper-plane"></i> Enviar Email de Teste
                                        </button>
                                        <div id="testeEmailResult" class="mt-2"></div>
                                    </div>
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
        // Testar conexão com Evolution API
        $('#btnTestarWhatsApp').click(function() {
            const btn = $(this);
            const resultado = $('#testeWhatsAppResult');
            
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Testando...');
            resultado.html('');
            
            $.ajax({
                url: 'api/testar_whatsapp.php',
                method: 'POST',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        resultado.html('<div class="alert alert-success">' + response.message + '</div>');
                    } else {
                        resultado.html('<div class="alert alert-danger">' + response.message + '</div>');
                    }
                },
                error: function() {
                    resultado.html('<div class="alert alert-danger">Erro ao testar conexão com a API</div>');
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="fas fa-plug"></i> Testar Conexão com Evolution API');
                }
            });
        });
        
        // Testar configurações de email
        $('#btnTestarEmail').click(function() {
            const email = $('#email_teste').val();
            if (!email) {
                alert('Por favor, digite um email para enviar o teste');
                return;
            }
            
            const btn = $(this);
            const resultado = $('#testeEmailResult');
            
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Enviando...');
            resultado.html('');
            
            $.ajax({
                url: 'api/testar_email.php',
                method: 'POST',
                data: { email: email },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        resultado.html('<div class="alert alert-success">' + response.message + '</div>');
                    } else {
                        resultado.html('<div class="alert alert-danger">' + response.message + '</div>');
                    }
                },
                error: function() {
                    resultado.html('<div class="alert alert-danger">Erro ao enviar email de teste</div>');
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Enviar Email de Teste');
                }
            });
        });
    });
    </script>
</body>
</html>