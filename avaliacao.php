<?php
require_once 'config/config.php';
require_once 'config/database.php';

$profissional_id = isset($_GET['profissional_id']) ? (int)$_GET['profissional_id'] : 0;
if ($profissional_id <= 0) {
    header('Location: listagem.php');
    exit;
}

$profissional = dbFetchOne('SELECT id, nome, foto FROM profissionais WHERE id = ?', [$profissional_id]);
if (!$profissional) {
    header('Location: listagem.php');
    exit;
}

$mensagem = $_GET['m'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Avaliar Profissional - ZelaLar</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <main class="container">
        <h1>Contratar e Avaliar</h1>
        <?php if ($mensagem): ?><p class="success"><?=htmlspecialchars($mensagem)?></p><?php endif; ?>

        <div class="profissional-card">
            <div class="profissional-foto">
                <?php if (!empty($profissional['foto'])): ?>
                    <img src="<?=htmlspecialchars($profissional['foto'])?>" alt="<?=htmlspecialchars($profissional['nome'])?>">
                <?php else: ?>
                    <div class="foto-placeholder"><?=htmlspecialchars(substr($profissional['nome'],0,1))?></div>
                <?php endif; ?>
            </div>
            <div class="profissional-info">
                <h2><?=htmlspecialchars($profissional['nome'])?></h2>
            </div>
        </div>

        <form action="salvar_avaliacao.php" method="post">
            <input type="hidden" name="profissional_id" value="<?=htmlspecialchars($profissional['id'])?>">
            <div class="form-group">
                <label for="cliente_nome">Seu nome</label>
                <input id="cliente_nome" name="cliente_nome" required>
            </div>
            <div class="form-group">
                <label for="nota">Nota (1-5)</label>
                <select id="nota" name="nota" required>
                    <option value="">--</option>
                    <?php for ($i=5;$i>=1;$i--): ?>
                        <option value="<?=$i?>"><?=$i?> estrela<?= $i>1 ? 's' : '' ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="comentario">Comentário (opcional)</label>
                <textarea id="comentario" name="comentario" rows="4"></textarea>
            </div>
            <button class="btn btn-primary" type="submit">Enviar Avaliação</button>
            <a class="btn btn-secondary" href="listagem.php">Voltar</a>
        </form>
    </main>
    <script src="js/utils.js"></script>
    <script src="js/main.js"></script>
    <?php if ($mensagem): ?>
    <script>Utils && Utils.showNotification('<?= htmlspecialchars($mensagem) ?>','success');</script>
    <?php endif; ?>
</body>
</html>
