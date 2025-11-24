<?php
require_once '../config/config.php';
require_once '../config/database.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login_admin.php');
    exit;
}
// Resumo quantitativo
$num_profissionais = dbFetchValue('SELECT COUNT(*) FROM profissionais');
$num_clientes = dbFetchValue('SELECT COUNT(*) FROM clientes');
$num_avaliacoes = dbFetchValue('SELECT COUNT(*) FROM avaliacoes');
$admin_usuario = $_SESSION['admin_usuario'];
// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login_admin.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Painel Admin - ZelaLar</title>
<link rel="stylesheet" href="../css/style.css">
<link rel="icon" href="../img/logo.png">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
<header class="header">
  <div class="container">
    <div class="header-content">
      <div class="logo">
        <a href="../index.php"><img src="../img/logo_nome.png" alt="ZelaLar" class="logo-image"></a>
      </div>
      <nav class="nav">
        <a href="dashboard_admin.php" class="active">Admin</a>
        <a href="<?= site_url('admin/usuarios') ?>">Usuários</a>
        <a href="<?= site_url('admin/avaliacoes') ?>">Avaliações</a>
      </nav>
      <div class="user-menu">
        <span>Admin: <?= htmlspecialchars($admin_usuario) ?></span>
        <a href="<?= site_url('admin') ?>?logout=1" class="btn btn-secondary btn-sm"><i class="fas fa-sign-out-alt"></i> Sair</a>
      </div>
    </div>
  </div>
</header>
<main class="main-content">
  <div class="container">
    <h1>Painel Administrativo</h1>
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-user-tie"></i></div>
        <div class="stat-content"><h3><?= $num_profissionais ?></h3><p>Profissionais</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-users"></i></div>
        <div class="stat-content"><h3><?= $num_clientes ?></h3><p>Clientes</p></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-star"></i></div>
        <div class="stat-content"><h3><?= $num_avaliacoes ?></h3><p>Avaliações</p></div>
      </div>
    </div>
    <div class="dashboard-section">
      <div class="actions-grid">
        <a href="usuarios.php" class="action-card">
          <i class="fas fa-user-cog"></i>
          <h3>Gerenciar Usuários</h3>
          <p>Profissionais e Clientes</p>
        </a>
        <a href="avaliacoes.php" class="action-card">
          <i class="fas fa-star"></i>
          <h3>Ver Avaliações</h3>
          <p>Visualizar, aprovar e moderar avaliações</p>
        </a>
      </div>
    </div>
  </div>
</main>
<footer class="footer"><div class="container"><div class="footer-content"><div class="footer-section"><h3>ZelaLar</h3><p>Administração Interna</p></div></div><div class="footer-bottom"><p>&copy; <?= date('Y') ?> ZelaLar.</p></div></div></footer>
</body>
</html>
