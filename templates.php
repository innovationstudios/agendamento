<?php
require_once 'config/database.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$conn = getDBConnection();

// Obter todos os templates
$query = "SELECT * FROM mensagem_templates ORDER BY dias_antes";
$result = $conn->query($query);
$templates = [];

while ($row = $result->fetch_assoc()) {
    $templates[] = $row;
}

// Processar formulário de adição/edição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    $id = $_POST['id'] ?? null;
    $titulo = $_POST['titulo'] ?? '';
    $mensagem = $_POST['mensagem'] ?? '';
    $dias_antes = $_POST['dias_antes'] ?? 1;
    $ativo = isset($_POST['ativo']) ? 1 : 0;

    if ($acao === 'adicionar' || $acao === 'editar') {
        if (empty($titulo) || empty($mensagem)) {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Título e mensagem são obrigatórios!'];
        } else {
            if ($acao === 'adicionar') {
                $stmt = $conn->prepare("INSERT INTO mensagem_templates (titulo, mensagem, dias_antes, ativo) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssii", $titulo, $mensagem, $dias_antes, $ativo);
            } else {
                $stmt = $conn->prepare("UPDATE mensagem_templates SET titulo = ?, mensagem = ?, dias_antes = ?, ativo = ? WHERE id = ?");
                $stmt->bind_param("ssiii", $titulo, $mensagem, $dias_antes, $ativo, $id);
            }

            if ($stmt->execute()) {
                $_SESSION['mensagem'] = ['tipo' => 'success', 'texto' => 'Template ' . ($acao === 'adicionar' ? 'adicionado' : 'atualizado') . ' com sucesso!'];
            } else {
                $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Erro ao salvar template: ' . $conn->error];
            }
        }
        header('Location: templates.php');
        exit;
    } elseif ($acao === 'excluir' && $id) {
        $stmt = $conn->prepare("DELETE FROM mensagem_templates WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $_SESSION['mensagem'] = ['tipo' => 'success', 'texto' => 'Template excluído com sucesso!'];
        } else {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Erro ao excluir template: ' . $conn->error];
        }
        header('Location: templates.php');
        exit;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php include 'includes/head.php'; ?>
    <title>Templates de Mensagem - Gestor de Mensalidades</title>
    <style>
        .template-card {
            border-left: 4px solid #4e73df;
            margin-bottom: 20px;
        }
        .template-card.inativo {
            border-left-color: #e74a3b;
            opacity: 0.8;
        }
        .placeholders {
            background-color: #f8f9fa;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 15px;
        }
        .placeholder-tag {
            display: inline-block;
            background-color: #e9ecef;
            padding: 2px 6px;
            border-radius: 3px;
            margin-right: 5px;
            margin-bottom: 5px;
            font-family: monospace;
        }
    </style>
</head>
<body id="page-top">
    <div id="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include 'includes/navbar.php'; ?>
                
                <div class="container-fluid">
                    <h1 class="h3 mb-4 text-gray-800">Gerenciar Templates de Mensagem</h1>
                    
                    <?php if (isset($_SESSION['mensagem'])): ?>
                    <div class="alert alert-<?= $_SESSION['mensagem']['tipo'] ?> alert-dismissible fade show">
                        <?= $_SESSION['mensagem']['texto'] ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php unset($_SESSION['mensagem']); endif; ?>
                    
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                    <h6 class="m-0 font-weight-bold text-primary">Lista de Templates</h6>
                                    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#adicionarTemplateModal">
                                        <i class="fas fa-plus"></i> Adicionar Template
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div class="placeholders">
                                        <strong>Placeholders disponíveis:</strong><br>
                                        <span class="placeholder-tag">{cliente_nome}</span>
                                        <span class="placeholder-tag">{plano_nome}</span>
                                        <span class="placeholder-tag">{valor}</span>
                                        <span class="placeholder-tag">{data_vencimento}</span>
                                        <span class="placeholder-tag">{dias_restantes}</span>
                                    </div>
                                    
                                    <div class="row">
                                        <?php foreach ($templates as $template): ?>
                                        <div class="col-md-6">
                                            <div class="card template-card <?= $template['ativo'] ? '' : 'inativo' ?>">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <h5 class="card-title"><?= htmlspecialchars($template['titulo']) ?></h5>
                                                        <div>
                                                            <span class="badge <?= $template['ativo'] ? 'badge-success' : 'badge-secondary' ?>">
                                                                <?= $template['ativo'] ? 'Ativo' : 'Inativo' ?>
                                                            </span>
                                                            <span class="badge badge-info">
                                                                <?= $template['dias_antes'] ?> dia(s) antes
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <p class="card-text"><?= nl2br(htmlspecialchars($template['mensagem'])) ?></p>
                                                    <div class="d-flex justify-content-end">
                                                        <button class="btn btn-sm btn-info btn-editar-template mr-2" 
                                                                data-id="<?= $template['id'] ?>"
                                                                data-titulo="<?= htmlspecialchars($template['titulo']) ?>"
                                                                data-mensagem="<?= htmlspecialchars($template['mensagem']) ?>"
                                                                data-dias_antes="<?= $template['dias_antes'] ?>"
                                                                data-ativo="<?= $template['ativo'] ?>">
                                                            <i class="fas fa-edit"></i> Editar
                                                        </button>
                                                        <button class="btn btn-sm btn-danger btn-excluir-template" data-id="<?= $template['id'] ?>">
                                                            <i class="fas fa-trash"></i> Excluir
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
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

    <!-- Modal Adicionar Template -->
    <div class="modal fade" id="adicionarTemplateModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Adicionar Novo Template</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form method="POST">
                    <input type="hidden" name="acao" value="adicionar">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="titulo">Título do Template</label>
                            <input type="text" class="form-control" id="titulo" name="titulo" required>
                        </div>
                        <div class="form-group">
                            <label for="dias_antes">Dias antes do vencimento</label>
                            <input type="number" class="form-control" id="dias_antes" name="dias_antes" min="1" value="1" required>
                        </div>
                        <div class="form-group">
                            <label for="mensagem">Mensagem</label>
                            <textarea class="form-control" id="mensagem" name="mensagem" rows="6" required></textarea>
                            <small class="form-text text-muted">Use os placeholders: {cliente_nome}, {plano_nome}, {valor}, {data_vencimento}, {dias_restantes}</small>
                        </div>
                        <div class="form-group form-check">
                            <input type="checkbox" class="form-check-input" id="ativo" name="ativo" checked>
                            <label class="form-check-label" for="ativo">Template ativo</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar Template</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Template -->
    <div class="modal fade" id="editarTemplateModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Editar Template</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form method="POST">
                    <input type="hidden" name="acao" value="editar">
                    <input type="hidden" name="id" id="editar_id">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="editar_titulo">Título do Template</label>
                            <input type="text" class="form-control" id="editar_titulo" name="titulo" required>
                        </div>
                        <div class="form-group">
                            <label for="editar_dias_antes">Dias antes do vencimento</label>
                            <input type="number" class="form-control" id="editar_dias_antes" name="dias_antes" min="1" required>
                        </div>
                        <div class="form-group">
                            <label for="editar_mensagem">Mensagem</label>
                            <textarea class="form-control" id="editar_mensagem" name="mensagem" rows="6" required></textarea>
                            <small class="form-text text-muted">Use os placeholders: {cliente_nome}, {plano_nome}, {valor}, {data_vencimento}, {dias_restantes}</small>
                        </div>
                        <div class="form-group form-check">
                            <input type="checkbox" class="form-check-input" id="editar_ativo" name="ativo">
                            <label class="form-check-label" for="editar_ativo">Template ativo</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Confirmar Exclusão -->
    <div class="modal fade" id="confirmarExclusaoModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Confirmar Exclusão</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form method="POST">
                    <input type="hidden" name="acao" value="excluir">
                    <input type="hidden" name="id" id="excluir_id">
                    <div class="modal-body">
                        <p>Tem certeza que deseja excluir este template? Esta ação não pode ser desfeita.</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Confirmar Exclusão</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <?php include 'includes/scripts.php'; ?>
    
    <script>
    $(document).ready(function() {
        // Configurar botão de edição
        $('.btn-editar-template').click(function() {
            const id = $(this).data('id');
            const titulo = $(this).data('titulo');
            const mensagem = $(this).data('mensagem');
            const dias_antes = $(this).data('dias_antes');
            const ativo = $(this).data('ativo');
            
            $('#editar_id').val(id);
            $('#editar_titulo').val(titulo);
            $('#editar_mensagem').val(mensagem);
            $('#editar_dias_antes').val(dias_antes);
            $('#editar_ativo').prop('checked', ativo == 1);
            
            $('#editarTemplateModal').modal('show');
        });
        
        // Configurar botão de exclusão
        $('.btn-excluir-template').click(function() {
            const id = $(this).data('id');
            $('#excluir_id').val(id);
            $('#confirmarExclusaoModal').modal('show');
        });
    });
    </script>
</body>
</html>