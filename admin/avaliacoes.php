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

// Processar ação
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token']) && verifyCSRFToken($_POST['csrf_token'])) {
    $acao = $_POST['acao'] ?? '';
    $id = intval($_POST['id'] ?? 0);
    try {
        if ($id) {
            if ($acao === 'aprovar') {
                dbExecute("UPDATE avaliacoes SET status='aprovada' WHERE id = ?", [$id]);
                $mensagem = 'Avaliação aprovada.';
            } elseif ($acao === 'reprovar') {
                dbExecute("UPDATE avaliacoes SET status='reprovada' WHERE id = ?", [$id]);
                $mensagem = 'Avaliação reprovada.';
            } elseif ($acao === 'excluir') {
                dbExecute("DELETE FROM avaliacoes WHERE id = ?", [$id]);
                $mensagem = 'Avaliação excluída.';
            }
            $tipo_mensagem = 'success';
        }
    } catch(Exception $e) {
        $mensagem = 'Erro ao executar ação.';
        $tipo_mensagem = 'erro';
    }
}
$q = trim($_GET['q'] ?? '');
$status = $_GET['status'] ?? '';
$params = [];
$sql = 'SELECT a.id, a.cliente_nome, a.nota, a.comentario, a.status, a.data_criacao, p.nome AS profissional_nome FROM avaliacoes a LEFT JOIN profissionais p ON a.profissional_id = p.id WHERE 1=1';
if ($q) {
    $sql .= " AND (a.cliente_nome LIKE ? OR p.nome LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
}
if (in_array($status, ['aprovada','pendente','reprovada'])) {
    $sql .= " AND a.status = ?";
    $params[] = $status;
}
$sql .= " ORDER BY a.data_criacao DESC LIMIT 100";
$avaliacoes = dbQuery($sql, $params);
$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin - Avaliações</title>
<link rel="stylesheet" href="../css/style.css">
<link rel="icon" href="../img/logo.png">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
<header class="header"><div class="container"><div class="header-content"><div class="logo"><a href="<?= site_url('admin/dashboard') ?>"><img src="../img/logo_nome.png" alt="ZelaLar" class="logo-image"></a></div><nav class="nav"><a href="<?= site_url('admin/dashboard') ?>">Admin</a><a href="<?= site_url('admin/usuarios') ?>">Usuários</a><a href="<?= site_url('admin/avaliacoes') ?>" class="active">Avaliações</a></nav></div></div></header>
<main class="main-content"><div class="container">
<h1>Moderar Avaliações</h1>
<?php if ($mensagem): ?>
<script>window.addEventListener('DOMContentLoaded', function(){Utils && Utils.showNotification('<?= htmlspecialchars($mensagem) ?>','<?= $tipo_mensagem==='erro'?'error':'success' ?>')});</script>
<?php endif; ?>
<form method="GET" style="margin-bottom: 20px; display:flex; gap:12px;">
<input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Buscar cliente ou profissional..." style="flex:1;max-width:300px;" class="form-control">
<select name="status" class="form-control" style="max-width:160px;">
  <option value="">Status</option>
  <option value="aprovada" <?= $status==='aprovada'?'selected':'' ?>>Aprovada</option>
  <option value="pendente" <?= $status==='pendente'?'selected':'' ?>>Pendente</option>
  <option value="reprovada" <?= $status==='reprovada'?'selected':'' ?>>Reprovada</option>
</select>
<button type="submit" class="btn btn-secondary"><i class="fas fa-search"></i> Buscar</button>
</form>
<table class="data-table" style="width:100%; border-collapse: collapse;">
<thead><tr style="background: #f8f9fa;"><th>Cliente</th><th>Profissional</th><th>Nota</th><th>Comentário</th><th>Status</th><th>Data</th><th>Ações</th></tr></thead>
<tbody>
<?php foreach($avaliacoes as $ava): ?>
<tr style="border-bottom:1px solid #eee;">
  <td><?= htmlspecialchars($ava['cliente_nome']) ?></td>
  <td><?= htmlspecialchars($ava['profissional_nome']) ?></td>
  <td><?= intval($ava['nota']) ?></td>
  <td><?= htmlspecialchars($ava['comentario']) ?></td>
  <td><?= htmlspecialchars($ava['status']) ?></td>
  <td><?= date('d/m/Y', strtotime($ava['data_criacao'])) ?></td>
  <td style="min-width:140px;">
      <form method="post" style="display:inline;">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <input type="hidden" name="acao" value="aprovar">
        <input type="hidden" name="id" value="<?= $ava['id'] ?>">
        <button class="btn btn-sm btn-success" title="Aprovar"><i class="fas fa-check"></i></button>
      </form>
      <form method="post" style="display:inline;">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <input type="hidden" name="acao" value="reprovar">
        <input type="hidden" name="id" value="<?= $ava['id'] ?>">
        <button class="btn btn-sm btn-warning" title="Reprovar"><i class="fas fa-times"></i></button>
      </form>
      <form method="post" style="display:inline;" onsubmit="return confirm('Excluir esta avaliação?')">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <input type="hidden" name="acao" value="excluir">
        <input type="hidden" name="id" value="<?= $ava['id'] ?>">
        <button class="btn btn-sm btn-danger" title="Excluir"><i class="fas fa-trash"></i></button>
      </form>
  </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div></main>
<footer class="footer"><div class="container"><div class="footer-content"><div class="footer-section"><h3>ZelaLar</h3></div></div><div class="footer-bottom"><p>&copy; <?= date('Y') ?> ZelaLar.</p></div></div></footer>
<script src="../js/utils.js"></script><script src="../js/main.js"></script>
</body>
</html>
