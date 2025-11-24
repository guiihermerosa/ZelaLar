<?php
require_once '../config/config.php';
require_once '../config/database.php';

$mensagem = '';
$tipo_mensagem = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';

    if (empty($nome) || empty($telefone) || empty($email) || empty($senha)) {
        $mensagem = 'Por favor, preencha todos os campos obrigatórios.';
        $tipo_mensagem = 'erro';
    } elseif ($senha !== $confirmar_senha) {
        $mensagem = 'As senhas não coincidem.';
        $tipo_mensagem = 'erro';
    } elseif (strlen($senha) < 6) {
        $mensagem = 'A senha deve ter pelo menos 6 caracteres.';
        $tipo_mensagem = 'erro';
    } else {
        try {
            $db = getDatabase();
            // Checa se telefone ou email já cadastrados
            $existe = dbFetchOne('SELECT id FROM clientes WHERE telefone = ? OR email = ?', [$telefone, $email]);
            if ($existe) {
                $mensagem = 'Telefone ou Email já cadastrado.';
                $tipo_mensagem = 'erro';
            } else {
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                $db->prepare('INSERT INTO clientes (nome, telefone, email, senha) VALUES (?, ?, ?, ?)')->execute([$nome, $telefone, $email, $senha_hash]);
                $mensagem = 'Cadastro realizado com sucesso! Faça o login para acessar.';
                $tipo_mensagem = 'sucesso';
                $nome = $telefone = $email = '';
            }
        } catch (Exception $e) {
            error_log('Erro ao cadastrar cliente: ' . $e->getMessage());
            $mensagem = 'Erro ao cadastrar. Tente novamente.';
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
    <title>Cadastro de Cliente - ZelaLar</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/profissionais.css">
    <link rel="icon" href="../img/logo.png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
                    <a href="../profissionais.php">Cadastrar Profissional</a>
                    <a href="cadastro_cliente.php" class="active">Cadastrar Cliente</a>
                    <a href="login_cliente.php">Login Cliente</a>
                </nav>
            </div>
        </div>
    </header>
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1><i class="fas fa-user-plus"></i> Cadastro de Cliente</h1>
                <p>Registre-se para agendar serviços e acompanhar suas avaliações.</p>
            </div>
            <?php if ($mensagem): ?>
                <div class="mensagem mensagem-<?= $tipo_mensagem ?>">
                    <?= htmlspecialchars($mensagem) ?>
                </div>
            <?php endif; ?>
            <div class="form-container">
                <form method="POST" class="form-profissional">
                    <div class="form-group">
                        <label for="nome">Nome Completo *</label>
                        <input type="text" id="nome" name="nome" required value="<?= htmlspecialchars($nome ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="telefone">Telefone/WhatsApp *</label>
                        <input type="tel" id="telefone" name="telefone" required value="<?= htmlspecialchars($telefone ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="email">E-mail *</label>
                        <input type="email" id="email" name="email" required value="<?= htmlspecialchars($email ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="senha">Senha *</label>
                        <input type="password" id="senha" name="senha" required>
                    </div>
                    <div class="form-group">
                        <label for="confirmar_senha">Confirmar Senha *</label>
                        <input type="password" id="confirmar_senha" name="confirmar_senha" required>
                    </div>
                    <div class="form-actions">
                        <button class="btn btn-primary" type="submit">Cadastrar</button>
                        <a class="btn btn-secondary" href="login_cliente.php">Já tenho conta</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>ZelaLar</h3>
                    <p>Conectando você aos melhores profissionais da região.</p>
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
    <script src="../js/utils.js"></script>
    <script src="../js/main.js"></script>
    <script>Utils.initPhoneMask();</script>
</body>
</html>
