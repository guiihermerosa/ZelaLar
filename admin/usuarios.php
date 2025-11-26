<?php
require_once '../config/config.php';
require_once '../config/database.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ' . site_url('admin'));
    exit;
}

$mensagem = '';
$tipo_mensagem = '';

// Processar ações via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token']) && verifyCSRFToken($_POST['csrf_token'])) {
    $acao = $_POST['acao'] ?? '';
    $tipo = $_POST['tipo'] ?? '';
    $id = intval($_POST['id'] ?? 0);
    try {
        $db = getDatabase();
        if ($acao === 'excluir' && $id) {
            if ($tipo === 'profissional') {
                dbExecute('DELETE FROM profissionais WHERE id = ?', [$id]);
                $mensagem = 'Profissional excluído.';
            } elseif ($tipo === 'cliente') {
                dbExecute('DELETE FROM clientes WHERE id = ?', [$id]);
                $mensagem = 'Cliente excluído.';
            }
            $tipo_mensagem = 'success';
        } elseif ($acao === 'toggle_disponivel' && $tipo === 'profissional' && $id) {
            $user = dbFetchOne('SELECT disponivel FROM profissionais WHERE id = ?', [$id]);
            if ($user) {
                $novo = $user['disponivel'] ? 0 : 1;
                dbExecute('UPDATE profissionais SET disponivel=? WHERE id = ?', [$novo, $id]);
                $mensagem = $novo ? 'Profissional ativado.' : 'Profissional desativado.';
                $tipo_mensagem = 'success';
            }
        }
    } catch(Exception $e) {
        $mensagem = 'Erro ao executar ação.';
        $tipo_mensagem = 'erro';
    }
}

$tab = $_GET['tab'] ?? 'profissionais';
$q = trim($_GET['q'] ?? '');
$profissionais = $clientes = [];
if ($tab === 'profissionais') {
    $sql = 'SELECT id, nome, telefone, categoria, email, disponivel FROM profissionais';
    $params = [];
    if ($q) {
        $sql .= ' WHERE nome LIKE ? OR telefone LIKE ?';
        $params = ["%$q%", "%$q%"];
    }
    $sql .= ' ORDER BY nome';
    $profissionais = dbQuery($sql, $params);
} else {
    $sql = 'SELECT id, nome, telefone, email, criado_em FROM clientes';
    $params = [];
    if ($q) {
        $sql .= ' WHERE nome LIKE ? OR telefone LIKE ?';
        $params = ["%$q%", "%$q%"];
    }
    $sql .= ' ORDER BY nome';
    $clientes = dbQuery($sql, $params);
}
$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gerenciar Usuários - Admin ZelaLar</title>
<link rel="stylesheet" href="../css/style.css">
<link rel="icon" href="../img/logo.png">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
<header class="header">
    <div class="container">
        <div class="header-content">
            <div class="logo"><a href="<?= site_url() ?>"><img src="../img/logo_nome.png" alt="ZelaLar" class="logo-image"></a></div>
            <nav class="nav">
                <a href="<?= site_url('admin/dashboard') ?>">Admin</a>
                <a href="<?= site_url('admin/usuarios') ?>" class="active">Usuários</a>
                <a href="<?= site_url('admin/avaliacoes') ?>">Avaliações</a>
            </nav>
        </div>
    </div>
</header>
<main class="main-content">
    <div class="container">
        <h1>Gerenciar Usuários</h1>
        <div class="tabs" style="margin-bottom: 16px;">
            <a href="<?= site_url('admin/usuarios') ?>?tab=profissionais" class="btn <?= $tab==='profissionais'?'btn-primary':'btn-secondary' ?>">Profissionais</a>
            <a href="<?= site_url('admin/usuarios') ?>?tab=clientes" class="btn <?= $tab==='clientes'?'btn-primary':'btn-secondary' ?>">Clientes</a>
        </div>
        <form method="GET" style="margin-bottom: 24px; display:flex; gap:12px;">
            <input type="hidden" name="tab" value="<?= htmlspecialchars($tab) ?>">
            <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Buscar por nome ou telefone..." style="flex:1;max-width:350px;" class="form-control">
            <button type="submit" class="btn btn-secondary"><i class="fas fa-search"></i> Buscar</button>
        </form>
        <?php if ($mensagem): ?>
            <script>window.addEventListener('DOMContentLoaded', function(){Utils && Utils.showNotification('<?= htmlspecialchars($mensagem) ?>','<?= $tipo_mensagem==='erro'?'error':'success' ?>')});</script>
        <?php endif; ?>
        <?php if ($tab==='profissionais'): ?>
        <table class="data-table" style="width:100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f8f9fa;"><th>Nome</th><th>Categoria</th><th>Telefone</th><th>Email</th><th>Status</th><th>Ações</th></tr>
            </thead>
            <tbody>
            <?php foreach($profissionais as $pro): ?>
                <tr style="border-bottom:1px solid #eee;">
                    <td><?= htmlspecialchars($pro['nome']) ?></td>
                    <td><?= htmlspecialchars($pro['categoria']) ?></td>
                    <td><?= htmlspecialchars($pro['telefone']) ?></td>
                    <td><?= htmlspecialchars($pro['email']) ?></td>
                    <td><?= $pro['disponivel'] ? '<span style="color:green">Ativo</span>' : '<span style="color:#888">Inativo</span>' ?></td>
                    <td style="min-width:190px;">
                        <!-- Toggle ativo/inativo -->
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                            <input type="hidden" name="acao" value="toggle_disponivel">
                            <input type="hidden" name="tipo" value="profissional">
                            <input type="hidden" name="id" value="<?= $pro['id'] ?>">
                            <button title="<?= $pro['disponivel']?'Desativar':'Ativar' ?>" class="btn btn-sm btn-secondary"><i class="fas <?= $pro['disponivel']?'fa-user-slash':'fa-user-check' ?>"></i></button>
                        </form>
                        <!-- Excluir -->
                        <form method="post" style="display:inline;" onsubmit="return confirm('Excluir este profissional?')">
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                            <input type="hidden" name="acao" value="excluir">
                            <input type="hidden" name="tipo" value="profissional">
                            <input type="hidden" name="id" value="<?= $pro['id'] ?>">
                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <table class="data-table" style="width:100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f8f9fa;"><th>Nome</th><th>Telefone</th><th>Email</th><th>Data Cadastro</th><th>Ações</th></tr>
            </thead>
            <tbody>
            <?php foreach($clientes as $cli): ?>
                <tr style="border-bottom:1px solid #eee;">
                    <td><?= htmlspecialchars($cli['nome']) ?></td>
                    <td><?= htmlspecialchars($cli['telefone']) ?></td>
                    <td><?= htmlspecialchars($cli['email']) ?></td>
                    <td><?= date('d/m/Y', strtotime($cli['criado_em'])) ?></td>
                    <td style="min-width:100px;">
                        <form method="post" style="display:inline;" onsubmit="return confirm('Excluir este cliente?')">
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                            <input type="hidden" name="acao" value="excluir">
                            <input type="hidden" name="tipo" value="cliente">
                            <input type="hidden" name="id" value="<?= $cli['id'] ?>">
                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</main>
<footer class="footer"><div class="container"><div class="footer-content"><div class="footer-section"><h3>ZelaLar</h3></div></div><div class="footer-bottom"><p>&copy; <?= date('Y') ?> ZelaLar.</p></div></div></footer>
<script src="../js/utils.js"></script><script src="../js/main.js"></script>
</body>
</html>
