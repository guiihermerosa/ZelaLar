<?php
require_once 'config/config.php';
require_once 'config/database.php';

session_start();

// Se já estiver logado, redireciona para o dashboard
if (isset($_SESSION['profissional_id'])) {
    header('Location: dashboard.php');
    exit;
}

$mensagem = '';
$tipo_mensagem = '';

if ($_POST) {
    $telefone = trim($_POST['telefone'] ?? '');
    $senha = $_POST['senha'] ?? '';
    
    if (empty($telefone) || empty($senha)) {
        $mensagem = 'Por favor, preencha todos os campos.';
        $tipo_mensagem = 'erro';
    } else {
        try {
            $db = getDatabase();
            $profissional = dbFetchOne(
                "SELECT id, nome, telefone, senha, categoria FROM profissionais WHERE telefone = ?", 
                [$telefone]
            );
            
            if ($profissional && password_verify($senha, $profissional['senha'])) {
                $_SESSION['profissional_id'] = $profissional['id'];
                $_SESSION['profissional_nome'] = $profissional['nome'];
                $_SESSION['profissional_telefone'] = $profissional['telefone'];
                $_SESSION['profissional_categoria'] = $profissional['categoria'];
                
                header('Location: dashboard.php');
                exit;
            } else {
                $mensagem = 'Telefone ou senha incorretos.';
                $tipo_mensagem = 'erro';
            }
        } catch (Exception $e) {
            error_log("Erro no login: " . $e->getMessage());
            $mensagem = 'Erro interno. Tente novamente.';
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
    <title>Login Profissional - ZelaLar</title>
    <meta name="description" content="Acesse sua conta de profissional no ZelaLar">
    
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
                    <a href="login.php" class="active">Login</a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="login-container">
                <div class="login-card">
                    <div class="login-header">
                        <h1><i class="fas fa-user-tie"></i> Login Profissional</h1>
                        <p>Acesse sua conta para gerenciar seu perfil</p>
                    </div>

                    <?php if ($mensagem): ?>
                        <div class="mensagem mensagem-<?= $tipo_mensagem ?>">
                            <?= htmlspecialchars($mensagem) ?>
                        </div>
                    <?php endif; ?>

                    <form class="login-form" method="POST">
                        <div class="form-group">
                            <label for="telefone">Telefone</label>
                            <div class="input-group">
                                <span class="input-prefix">+55</span>
                                <input type="tel" id="telefone" name="telefone" 
                                       value="<?= htmlspecialchars($_POST['telefone'] ?? '') ?>" 
                                       placeholder="(11) 99999-9999" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="senha">Senha</label>
                            <div class="input-group">
                                <input type="password" id="senha" name="senha" 
                                       placeholder="Digite sua senha" required>
                                <button type="button" class="toggle-password" onclick="togglePassword()">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-sign-in-alt"></i>
                                Entrar
                            </button>
                        </div>

                        <div class="form-links">
                            <a href="profissionais.php">Não tem conta? Cadastre-se</a>
                            <a href="#" onclick="esqueciSenha()">Esqueci minha senha</a>
                        </div>
                    </form>
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
    <?php if (!empty($mensagem)): ?>
    <script>Utils && Utils.showNotification('<?= htmlspecialchars($mensagem) ?>','<?= $tipo_mensagem==='erro'?'error':'success' ?>');</script>
    <?php endif; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Utils.initPhoneMask();
        });

        function togglePassword() {
            const senhaInput = document.getElementById('senha');
            const toggleBtn = document.querySelector('.toggle-password i');
            
            if (senhaInput.type === 'password') {
                senhaInput.type = 'text';
                toggleBtn.className = 'fas fa-eye-slash';
            } else {
                senhaInput.type = 'password';
                toggleBtn.className = 'fas fa-eye';
            }
        }

        function esqueciSenha() {
            alert('Entre em contato conosco pelo WhatsApp para redefinir sua senha.');
        }
    </script>
</body>
</html>
