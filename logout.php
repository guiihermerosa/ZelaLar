<?php

require_once __DIR__ . '/config/config.php';
session_start();

// Se não houver sessão iniciada, redireciona direto
if (empty($_SESSION)) {
    header('Location: login.php');
    exit;
}

// Processa logout via POST (melhor prática) com verificação CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST[CSRF_TOKEN_NAME] ?? '';
    if (!verifyCSRFToken($token)) {
        // Token inválido — redireciona ou exibe erro simples
        header('Location: index.php');
        exit;
    }

    // Limpar variáveis de sessão
    $_SESSION = [];

    // Destruir cookie de sessão
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // Destruir sessão no servidor
    session_destroy();

    // Regenerar ID de sessão por segurança
    session_start();
    session_regenerate_id(true);
    session_destroy();

    // Redirecionar para login ou página inicial
    header('Location: login.php');
    exit;
}

// Se for GET, exibe confirmação simples
$csrf = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Logout - ZelaLar</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="Styles/index.css">
</head>
<body>
    <main style="max-width:480px;margin:6rem auto;padding:2rem;border:1px solid #eee;border-radius:6px;text-align:center;">
        <h1>Confirmar saída</h1>
        <p>Você tem certeza que deseja sair da sua conta?</p>
        <form method="post" action="logout.php">
            <input type="hidden" name="<?php echo htmlspecialchars(CSRF_TOKEN_NAME); ?>" value="<?php echo htmlspecialchars($csrf); ?>">
            <button type="submit" style="padding:0.6rem 1.2rem;margin-right:0.5rem;">Sair</button>
            <a href="index.php" style="padding:0.6rem 1.2rem;background:#eee;border-radius:4px;text-decoration:none;">Cancelar</a>
        </form>
    </main>
</body>
</html>