<?php
require_once 'config/config.php';
require_once 'config/database.php';

session_start();

// Verificar se está logado
if (!isset($_SESSION['profissional_id'])) {
    header('Location: login.php');
    exit;
}

$profissional_id = $_SESSION['profissional_id'];
$profissional = null;
$avaliacoes = [];
$estatisticas = [];

try {
    $db = getDatabase();

    // Buscar dados do profissional
    $profissional = dbFetchOne("SELECT * FROM profissionais WHERE id = ?", [$profissional_id]);

    // Buscar avaliações
    $avaliacoes = $db->query("
        SELECT * FROM avaliacoes 
        WHERE profissional_id = ? 
        ORDER BY data_avaliacao DESC 
        LIMIT 10
    ")->fetchAll();

    // Buscar estatísticas
    $total_avaliacoes = dbFetchValue("SELECT COUNT(*) FROM avaliacoes WHERE profissional_id = ?", [$profissional_id]);
    $media_nota = dbFetchValue("SELECT AVG(nota) FROM avaliacoes WHERE profissional_id = ? AND status = 'aprovada'", [$profissional_id]);
    $avaliacoes_5_estrelas = dbFetchValue("SELECT COUNT(*) FROM avaliacoes WHERE profissional_id = ? AND nota = 5", [$profissional_id]);
    $avaliacoes_1_estrela = dbFetchValue("SELECT COUNT(*) FROM avaliacoes WHERE profissional_id = ? AND nota = 1", [$profissional_id]);

    $estatisticas = [
        'total_avaliacoes' => $total_avaliacoes !== false ? (int)$total_avaliacoes : 0,
        'media_nota' => $media_nota !== false ? (float)$media_nota : 0,
        'avaliacoes_5_estrelas' => $avaliacoes_5_estrelas !== false ? (int)$avaliacoes_5_estrelas : 0,
        'avaliacoes_1_estrela' => $avaliacoes_1_estrela !== false ? (int)$avaliacoes_1_estrela : 0
    ];
} catch (Exception $e) {
    error_log("Erro no dashboard: " . $e->getMessage());
    // Definir valores padrão caso ocorra erro
    $estatisticas = [
        'total_avaliacoes' => 0,
        'media_nota' => 0,
        'avaliacoes_5_estrelas' => 0,
        'avaliacoes_1_estrela' => 0
    ];
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?= htmlspecialchars($profissional['nome']) ?> - ZelaLar</title>
    <meta name="description" content="Gerencie seu perfil e visualize suas avaliações">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="img/logo.png">
    <!-- CSS -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- PWA -->
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#1B4965">
</head>

<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="index.php">
                        <img src="img/logo_nome.png" alt="ZelaLar" class="logo-image">
                    </a>
                </div>
                <nav class="nav">
                    <a href="index.php">Início</a>
                    <a href="listagem.php">Profissionais</a>
                    <a href="profissionais.php">Cadastrar</a>
                    <a href="dashboard.php" class="active">Dashboard</a>
                </nav>
                <div class="user-menu">
                    <span>Olá, <?= htmlspecialchars($profissional['nome']) ?></span>
                    <a href="?logout=1" class="btn btn-secondary btn-sm">
                        <i class="fas fa-sign-out-alt"></i>
                        Sair
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Dashboard Header -->
            <div class="dashboard-header">
                <div class="profissional-profile">
                    <img src="<?= $profissional['foto'] ?: 'img/default-avatar.png' ?>"
                        alt="<?= htmlspecialchars($profissional['nome']) ?>"
                        class="profile-foto">
                    <div class="profile-info">
                        <h1><?= htmlspecialchars($profissional['nome']) ?></h1>
                        <p class="categoria"><?= htmlspecialchars($profissional['categoria']) ?></p>
                        <p class="telefone"><?= htmlspecialchars($profissional['telefone']) ?></p>
                    </div>
                </div>
                <div class="profile-actions">
                    <a href="editar_perfil.php" class="btn btn-primary">
                        <i class="fas fa-edit"></i>
                        Editar Perfil
                    </a>
                </div>
            </div>

            <!-- Estatísticas -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= number_format($estatisticas['media_nota'], 1) ?></h3>
                        <p>Média de Avaliação</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= $estatisticas['total_avaliacoes'] ?></h3>
                        <p>Total de Avaliações</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-thumbs-up"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= $estatisticas['avaliacoes_5_estrelas'] ?></h3>
                        <p>5 Estrelas</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-thumbs-down"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= $estatisticas['avaliacoes_1_estrela'] ?></h3>
                        <p>1 Estrela</p>
                    </div>
                </div>
            </div>

            <!-- Avaliações Recentes -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h2><i class="fas fa-star"></i> Avaliações Recentes</h2>
                    <a href="minhas_avaliacoes.php" class="btn btn-secondary btn-sm">
                        Ver Todas
                    </a>
                </div>

                <?php if (empty($avaliacoes)): ?>
                    <div class="empty-state">
                        <i class="fas fa-star"></i>
                        <h3>Nenhuma avaliação ainda</h3>
                        <p>Quando os clientes avaliarem seus serviços, elas aparecerão aqui.</p>
                    </div>
                <?php else: ?>
                    <div class="avaliacoes-list">
                        <?php foreach ($avaliacoes as $avaliacao): ?>
                            <div class="avaliacao-item">
                                <div class="avaliacao-header">
                                    <div class="cliente-info">
                                        <h4><?= htmlspecialchars($avaliacao['cliente_nome']) ?></h4>
                                        <span class="data">
                                            <?= date('d/m/Y', strtotime($avaliacao['data_avaliacao'])) ?>
                                        </span>
                                    </div>
                                    <div class="nota">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?= $i <= $avaliacao['nota'] ? 'ativa' : '' ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <?php if ($avaliacao['comentario']): ?>
                                    <div class="comentario">
                                        "<?= htmlspecialchars($avaliacao['comentario']) ?>"
                                    </div>
                                <?php endif; ?>
                                <div class="status">
                                    <span class="status-badge status-<?= $avaliacao['status'] ?>">
                                        <?= ucfirst($avaliacao['status']) ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Ações Rápidas -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h2><i class="fas fa-bolt"></i> Ações Rápidas</h2>
                </div>
                <div class="actions-grid">
                    <a href="editar_perfil.php" class="action-card">
                        <i class="fas fa-user-edit"></i>
                        <h3>Editar Perfil</h3>
                        <p>Atualize suas informações e foto</p>
                    </a>

                    <a href="minhas_avaliacoes.php" class="action-card">
                        <i class="fas fa-star"></i>
                        <h3>Minhas Avaliações</h3>
                        <p>Veja todas as suas avaliações</p>
                    </a>

                    <a href="estatisticas.php" class="action-card">
                        <i class="fas fa-chart-bar"></i>
                        <h3>Estatísticas</h3>
                        <p>Analise seu desempenho</p>
                    </a>

                    <a href="configuracoes.php" class="action-card">
                        <i class="fas fa-cog"></i>
                        <h3>Configurações</h3>
                        <p>Gerencie sua conta</p>
                    </a>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>ZelaLar</h3>
                    <p>Conectando você aos melhores profissionais da região.</p>
                </div>
                <div class="footer-section">
                    <h3>Contato</h3>
                    <p><i class="fab fa-whatsapp"></i> <?= getConfig('CONTACT_WHATSAPP') ?></p>
                    <p><i class="fas fa-envelope"></i> <?= getConfig('CONTACT_EMAIL') ?></p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> ZelaLar. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="js/utils.js"></script>
    <script src="js/main.js"></script>
</body>

</html>