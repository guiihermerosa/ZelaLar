<?php
require_once '../config/config.php';
require_once '../config/database.php';
session_start();
if (!isset($_SESSION['cliente_id'])) {
    header('Location: login_cliente.php');
    exit;
}
$cliente_id = $_SESSION['cliente_id'];
$cliente_nome = $_SESSION['cliente_nome'];
// Buscar avaliações feitas pelo cliente
$avaliacoes = [];
try {
    $db = getDatabase();
    $avaliacoes = $db->prepare('SELECT a.*, p.nome AS profissional_nome
                               FROM avaliacoes a 
                               LEFT JOIN profissionais p ON a.profissional_id = p.id
                               WHERE a.cliente_id = ?
                               ORDER BY a.data_criacao DESC');
    $avaliacoes->execute([$cliente_id]);
    $avaliacoes = $avaliacoes->fetchAll();
} catch (Exception $e) {
    $avaliacoes = [];
}
// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login_cliente.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Cliente - ZelaLar</title>
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
                    <a href="../index.php">Início</a>
                    <a href="../listagem.php">Profissionais</a>
                    <a href="<?= site_url('clientes/dashboard') ?>" class="active">Minha Conta</a>
                </nav>
                <div class="user-menu">
                    <span>Olá, <?= htmlspecialchars($cliente_nome) ?></span>
                    <a href="<?= site_url('clientes') ?>?logout=1" class="btn btn-secondary btn-sm"><i class="fas fa-sign-out-alt"></i> Sair</a>
                </div>
            </div>
        </div>
    </header>
    <main class="main-content">
        <div class="container">
            <h1>Olá, <?= htmlspecialchars($cliente_nome) ?>!</h1>
            <p>Veja suas avaliações realizadas abaixo.</p>
            <div class="dashboard-section">
                <h2>Minhas Avaliações</h2>
                <?php if (empty($avaliacoes)): ?>
                    <div class="empty-state">
                        <i class="fas fa-star"></i>
                        <h3>Nenhuma avaliação realizada</h3>
                        <p>Quando você avaliar profissionais, aparecerão aqui.</p>
                    </div>
                <?php else: ?>
                    <ul class="avaliacoes-list">
                        <?php foreach($avaliacoes as $ava): ?>
                        <li class="avaliacao-item">
                            <strong><?= htmlspecialchars($ava['profissional_nome']) ?></strong>
                            — Nota: <?= intval($ava['nota']) ?>
                            <?php if (!empty($ava['comentario'])): ?>
                              <div class="comentario">"<?= htmlspecialchars($ava['comentario']) ?>"</div>
                            <?php endif; ?>
                            <time><?= date('d/m/Y', strtotime($ava['data_criacao'])) ?></time>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </main>
    <footer class="footer"><div class="container"><div class="footer-content"><div class="footer-section"><h3>ZelaLar</h3><p>Conectando você aos melhores profissionais da região.</p></div></div><div class="footer-bottom"><p>&copy; <?= date('Y') ?> ZelaLar. Todos os direitos reservados.</p></div></div></footer>
</body>
</html>
