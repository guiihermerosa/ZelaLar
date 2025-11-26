<?php
require_once '../config/config.php';
require_once '../config/database.php';

session_start();
$mensagem = '';
$tipo_mensagem = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $senha = $_POST['senha'] ?? '';
    if (!$usuario || !$senha) {
        $mensagem = 'Usuário e senha obrigatórios.';
        $tipo_mensagem = 'erro';
    } else {
        try {
            $admin = dbFetchOne('SELECT * FROM admins WHERE usuario = ?', [$usuario]);
            if ($admin && password_verify($senha, $admin['senha'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_usuario'] = $admin['usuario'];
                header('Location: dashboard_admin.php');
                exit;
            } else {
                $mensagem = 'Acesso negado.';
                $tipo_mensagem = 'erro';
            }
        } catch (Exception $e) {
            error_log('Erro LOGIN ADMIN: ' . $e->getMessage());
            $mensagem = 'Erro ao acessar. Tente novamente.';
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
    <title>Admin Login - ZelaLar</title>
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
                        <h1><i class="fas fa-user-shield"></i> Admin ZelaLar</h1>
                    </div>
                    <?php if ($mensagem): ?>
                        <div class="mensagem mensagem-<?= $tipo_mensagem ?>">
                            <?= htmlspecialchars($mensagem) ?>
                        </div>
                    <?php endif; ?>
                    <form class="login-form" method="POST">
                        <div class="form-group">
                            <label for="usuario">Usuário</label>
                            <input type="text" id="usuario" name="usuario" required>
                        </div>
                        <div class="form-group">
                            <label for="senha">Senha</label>
                            <input type="password" id="senha" name="senha" required>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Entrar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
    <script src="../js/utils.js"></script>
    <script src="../js/main.js"></script>
</body>
</html>
