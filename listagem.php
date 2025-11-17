<?php
require_once 'config/config.php';
require_once 'config/database.php';

// Filtro por categoria
$categoria_filtro = $_GET['categoria'] ?? '';

// Buscar profissionais
try {
    if (!empty($categoria_filtro)) {
        $profissionais = dbQuery("
            SELECT * FROM profissionais 
            WHERE categoria = ? AND disponivel = 1 
            ORDER BY media_avaliacao DESC, nome
        ", [$categoria_filtro]);
    } else {
        $profissionais = dbQuery("
            SELECT * FROM profissionais 
            WHERE disponivel = 1 
            ORDER BY media_avaliacao DESC, nome
        ");
    }
} catch (Exception $e) {
    error_log("Erro ao buscar profissionais: " . $e->getMessage());
    $profissionais = [];
    $erro = "Erro ao buscar profissionais. Tente novamente." . $e->getMessage();
    
}

// Buscar categorias para o filtro
try {
    $categorias = dbQuery("
        SELECT nome, descricao FROM categorias 
        WHERE ativa = 1 
        ORDER BY ordem
    ");
} catch (Exception $e) {
    error_log("Erro ao buscar categorias: " . $e->getMessage());
    $categorias = [];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profissionais - ZelaLar</title>
    <meta name="description" content="Encontre profissionais qualificados para seus serviços no ZelaLar">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="img/logo.png">
    <!-- CSS -->
    <link rel="stylesheet" href="css/style.css">
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
                    <a href="listagem.php" class="active">Profissionais</a>
                    <a href="profissionais.php">Cadastrar</a>
                    <a href="login.php">Login</a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1><i class="fas fa-users"></i> Profissionais Disponíveis</h1>
                <p>Encontre o profissional ideal para seu projeto</p>
            </div>

            <!-- Filtros -->
            <div class="filters">
                <form method="GET" class="filter-form">
                    <div class="filter-group">
                        <label for="categoria">Filtrar por Categoria:</label>
                        <select id="categoria" name="categoria" onchange="this.form.submit()">
                            <option value="">Todas as categorias</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?= htmlspecialchars($cat['nome']) ?>"
                                    <?= ($categoria_filtro == $cat['nome']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                </form>

                <div class="filter-actions">
                    <a href="profissionais.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Cadastrar Profissional
                    </a>
                </div>
            </div>

            <!-- Mensagem de erro -->
            <?php if (isset($erro)): ?>
                <div class="mensagem mensagem-erro">
                    <?= htmlspecialchars($erro) ?>
                </div>
            <?php endif; ?>

            <!-- Lista de Profissionais -->
            <div class="profissionais-grid">
                <?php if (empty($profissionais)): ?>
                    <div class="no-results">
                        <i class="fas fa-search"></i>
                        <h3>Nenhum profissional encontrado</h3>
                        <p>
                            <?php if (!empty($categoria_filtro)): ?>
                                Não encontramos profissionais na categoria "<?= htmlspecialchars($categoria_filtro) ?>".
                            <?php else: ?>
                                Ainda não há profissionais cadastrados no sistema.
                            <?php endif; ?>
                        </p>
                        <a href="profissionais.php" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i> Seja o primeiro a se cadastrar!
                        </a>
                    </div>
                <?php else: ?>
                    <?php foreach ($profissionais as $profissional): ?>
                        <div class="profissional-card">
                            <div class="profissional-foto">
                                <?php if (!empty($profissional['foto'])): ?>
                                    <img  style="  width: 100%; border-radius:50%; height: 100%; object-fit: cover; display: block;   " src="<?= htmlspecialchars($profissional['foto']) ?>"
                                        alt="Foto de <?= htmlspecialchars($profissional['nome']) ?>">
                                <?php else: ?>
                                    <div class="foto-placeholder">
                                        <i class="fas fa-user"></i>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="profissional-info">
                                <h3><?= htmlspecialchars($profissional['nome']) ?></h3>
                                <div class="categoria-badge">
                                    <?= htmlspecialchars($profissional['categoria']) ?>
                                </div>

                                <!-- Avaliação -->
                                <div class="avaliacao-info">
                                    <div class="estrelas">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?= $i <= $profissional['media_avaliacao'] ? 'ativa' : '' ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="nota"><?= number_format($profissional['media_avaliacao'], 1) ?></span>
                                    <span class="total">(<?= $profissional['total_avaliacoes'] ?> avaliações)</span>
                                </div>

                                <?php if (!empty($profissional['descricao'])): ?>
                                    <p class="descricao"><?= htmlspecialchars($profissional['descricao']) ?></p>
                                <?php endif; ?>

                                <div class="profissional-actions">
                                    <a href="https://wa.me/55<?= preg_replace('/[^0-9]/', '', $profissional['telefone']) ?>?text=Olá! Gostaria de agendar um serviço com <?= urlencode($profissional['nome']) ?>"
                                        class="btn btn-whatsapp" target="_blank">
                                        <i class="fab fa-whatsapp"></i> Contratar
                                    </a>

                                    <a href="avaliacao.php?id=<?= $profissional['id'] ?>"
                                        class="btn btn-secondary">
                                        <i class="fas fa-star"></i> Avaliar
                                    </a>

                                    <a href="tel:<?= htmlspecialchars($profissional['telefone']) ?>"
                                        class="btn btn-phone">
                                        <i class="fas fa-phone"></i> Ligar
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Estatísticas -->
            <?php if (!empty($profissionais)): ?>
                <div class="stats">
                    <div class="stat-item">
                        <i class="fas fa-users"></i>
                        <span><?= count($profissionais) ?> profissional(is) encontrado(s)</span>
                    </div>
                    <?php if (!empty($categoria_filtro)): ?>
                        <div class="stat-item">
                            <i class="fas fa-filter"></i>
                            <span>Filtrado por: <?= htmlspecialchars($categoria_filtro) ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="stat-item">
                        <i class="fas fa-star"></i>
                        <span>Ordenado por avaliação</span>
                    </div>
                </div>
            <?php endif; ?>
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
                    <h4>Links Úteis</h4>
                    <ul>
                        <li><a href="index.php">Início</a></li>
                        <li><a href="listagem.php">Profissionais</a></li>
                        <li><a href="profissionais.php">Cadastrar</a></li>
                        <li><a href="login.php">Login</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contato</h4>
                    <p><i class="fab fa-whatsapp"></i> <?= getConfig('CONTACT_WHATSAPP') ?></p>
                    <p><i class="fas fa-envelope"></i> <?= getConfig('CONTACT_EMAIL') ?></p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> ZelaLar. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- WhatsApp Float -->
    <a href="https://wa.me/<?= getConfig('CONTACT_WHATSAPP') ?>?text=Olá! Preciso de ajuda para encontrar um profissional."
        class="whatsapp-float" target="_blank">
        <i class="fab fa-whatsapp"></i>
    </a>

    <!-- Scripts -->
    <script src="js/utils.js"></script>
    <script src="js/main.js"></script>
</body>

</html>