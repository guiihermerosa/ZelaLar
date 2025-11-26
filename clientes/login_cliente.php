<?php
require_once '../config/config.php';
require_once '../config/database.php';
session_start();
$mensagem = '';
$tipo_mensagem = '';
if (isset($_SESSION['cliente_id'])) {
    header('Location: dashboard_cliente.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $telefone = trim($_POST['telefone'] ?? '');
    $senha = $_POST['senha'] ?? '';
    if (!$telefone || !$senha) {
        $mensagem = 'Preencha telefone e senha.';
        $tipo_mensagem = 'erro';
    } else {
        try {
            $cliente = dbFetchOne('SELECT id, nome, senha FROM clientes WHERE telefone = ?', [$telefone]);
            if ($cliente && password_verify($senha, $cliente['senha'])) {
                $_SESSION['cliente_id'] = $cliente['id'];
                $_SESSION['cliente_nome'] = $cliente['nome'];
                header('Location: dashboard_cliente.php');
                exit;
            } else {
                $mensagem = 'Telefone ou senha incorretos.';
                $tipo_mensagem = 'erro';
            }
        } catch (Exception $e) {
            error_log('Erro login cliente: ' . $e->getMessage());
            $mensagem = 'Erro interno.';
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
    <title>Login Cliente - ZelaLar</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="icon" href="../img/logo.png">
     <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <main class="main-content">
        <div class="container">
            <div class="login-container">
                <div class="login-card">
                    <div class="login-header">
                        <h1><i class="fas fa-user"></i> Login Cliente</h1>
                        <p>Acesse sua conta para consultar avaliações e agendar serviços.</p>
                    </div>
                    <?php if ($mensagem): ?>
                        <div class="mensagem mensagem-<?= $tipo_mensagem ?>">
                            <?= htmlspecialchars($mensagem) ?>
                        </div>
                    <?php endif; ?>
                    <form class="login-form" method="POST">
                        <div class="form-group">
                            <label for="telefone">Telefone</label>
                            <input type="tel" id="telefone" name="telefone" required autofocus>
                        </div>
                        <div class="form-group">
                            <label for="senha">Senha</label>
                            <input type="password" id="senha" name="senha" required>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Entrar</button>
                        </div>
                        <div class="form-links">
                            <a href="<?= site_url('clientes/cadastro') ?>">Cadastrar-se</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
    <script src="../js/utils.js"></script>
    <script src="../js/main.js"></script>
    <script>Utils.initPhoneMask();</script>
</body>
</html>
