<?php
// DEBUG TEMPORÁRIO: mostrar erros completos (remover em produção)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config/config.php';
require_once 'config/database.php';

$profissional_id = $_GET['id'] ?? null;
$profissional = null;

if ($profissional_id) {
    try {
        $db = getDatabase();
        $profissional = dbFetchOne("SELECT * FROM profissionais WHERE id = ?", [$profissional_id]);
    } catch (Exception $e) {
        error_log("Erro ao buscar profissional: " . $e->getMessage());
    }
}

if (!$profissional) {
    header('Location: index.php');
    exit;
}

$mensagem = '';
$tipo_mensagem = '';

if ($_POST) {
    $cliente_nome = trim($_POST['cliente_nome'] ?? '');
    $cliente_telefone = trim($_POST['cliente_telefone'] ?? '');
    $nota = (int)($_POST['nota'] ?? 0);
    $comentario = trim($_POST['comentario'] ?? '');

    if (empty($cliente_nome) || empty($cliente_telefone) || $nota < 1 || $nota > 5) {
        $mensagem = 'Por favor, preencha todos os campos corretamente.';
        $tipo_mensagem = 'erro';
    } else {
        try {
            // Inserir avaliação (não referenciar coluna inexistente)
            dbExecute(
                "INSERT INTO avaliacoes (profissional_id, cliente_nome, cliente_telefone, nota, comentario, status) VALUES (?, ?, ?, ?, ?, 'pendente')",
                [$profissional_id, $cliente_nome, $cliente_telefone, $nota, $comentario]
            );

            // Atualizar média e total aprovados do profissional
            dbExecute(
                "UPDATE profissionais 
                 SET avaliacao = (
                     SELECT COALESCE(AVG(nota), 0) 
                     FROM avaliacoes 
                     WHERE profissional_id = ? AND status = 'aprovada'
                 ),
                 total_avaliacoes = (
                     SELECT COUNT(*) 
                     FROM avaliacoes 
                     WHERE profissional_id = ? AND status = 'aprovada'
                 )
                 WHERE id = ?",
                [$profissional_id, $profissional_id, $profissional_id]
            );

            $mensagem = 'Obrigado pela sua avaliação! Ela será revisada e publicada em breve.';
            $tipo_mensagem = 'sucesso';
        } catch (Exception $e) {
            // Log detalhado e mensagem amigável (em dev exibe a mensagem real)
            error_log("Erro ao salvar avaliação: " . $e->getMessage());
           
                $mensagem = 'Erro ao salvar avaliação. Tente novamente.';
          
            $tipo_mensagem = 'erro';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avaliar <?= htmlspecialchars($profissional['nome']) ?> - ZelaLar</title>
    <meta name="description" content="Avalie o serviço prestado por <?= htmlspecialchars($profissional['nome']) ?>">

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
                    <a href="listagem.php">Profissionais</a>
                    <a href="profissionais.php">Cadastrar</a>
                    <a href="login.php">Login</a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Breadcrumb -->
            <nav class="breadcrumb">
                <a href="index.php">Início</a>
                <span>/</span>
                <a href="listagem.php">Profissionais</a>
                <span>/</span>
                <span>Avaliar</span>
            </nav>

            <!-- Avaliação Form -->
            <div class="avaliacao-container">
                <div class="avaliacao-header">
                    <div class="profissional-info">
                        <img src="<?= $profissional['foto'] ?: 'img/default-avatar.png' ?>"
                            alt="<?= htmlspecialchars($profissional['nome']) ?>"
                            class="profissional-foto">
                        <div class="profissional-detalhes">
                            <h1><?= htmlspecialchars($profissional['nome']) ?></h1>
                            <p class="categoria"><?= htmlspecialchars($profissional['categoria']) ?></p>
                            <div class="avaliacao-atual">
                                <div class="estrelas">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?= $i <= round($profissional['avaliacao']) ? 'ativa' : '' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <span class="nota"><?= number_format($profissional['avaliacao'], 1) ?></span>
                                <span class="total">(<?= $profissional['total_avaliacoes'] ?> avaliações)</span>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($mensagem): ?>
                    <div class="mensagem mensagem-<?= $tipo_mensagem ?>">
                        <?= htmlspecialchars($mensagem) ?>
                    </div>
                <?php endif; ?>

                <form class="avaliacao-form" method="POST">
                    <div class="form-group">
                        <label for="cliente_nome">Seu Nome *</label>
                        <input type="text" id="cliente_nome" name="cliente_nome"
                            value="<?= htmlspecialchars($_POST['cliente_nome'] ?? '') ?>"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="cliente_telefone">Seu Telefone *</label>
                        <input type="tel" id="cliente_telefone" name="cliente_telefone"
                            value="<?= htmlspecialchars($_POST['cliente_telefone'] ?? '') ?>"
                            required>
                    </div>

                    <div class="form-group">
                        <label>Avaliação *</label>
                        <div class="rating-input">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <input type="radio" id="star<?= $i ?>" name="nota" value="<?= $i ?>"
                                    <?= ($_POST['nota'] ?? 0) == $i ? 'checked' : '' ?>>
                                <label for="star<?= $i ?>">
                                    <i class="fas fa-star"></i>
                                </label>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="comentario">Comentário (opcional)</label>
                        <textarea id="comentario" name="comentario" rows="4"
                            placeholder="Conte como foi sua experiência com o profissional..."><?= htmlspecialchars($_POST['comentario'] ?? '') ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-star"></i>
                            Enviar Avaliação
                        </button>
                        <a href="listagem.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Voltar
                        </a>
                    </div>
                </form>
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

    <!-- WhatsApp Float -->
    <a href="https://wa.me/<?= getConfig('CONTACT_WHATSAPP') ?>?text=Olá! Preciso de ajuda com o ZelaLar."
        class="whatsapp-float" target="_blank">
        <i class="fab fa-whatsapp"></i>
    </a>

    <!-- Scripts -->
    <script src="js/utils.js"></script>
    <script src="js/main.js"></script>

    <script>
        // Inicializar máscara de telefone
        document.addEventListener('DOMContentLoaded', function() {
            Utils.initPhoneMask();

            // Adicionar classe ativa nas estrelas ao clicar
            const ratingInputs = document.querySelectorAll('.rating-input input');
            const ratingLabels = document.querySelectorAll('.rating-input label');

            ratingInputs.forEach((input, index) => {
                input.addEventListener('change', function() {
                    ratingLabels.forEach((label, labelIndex) => {
                        if (labelIndex < 5 - index) {
                            label.classList.add('ativa');
                        } else {
                            label.classList.remove('ativa');
                        }
                    });
                });
            });
        });
    </script>
</body>

</html>