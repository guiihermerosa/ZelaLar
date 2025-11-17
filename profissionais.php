<?php
require_once 'config/config.php';
require_once 'config/database.php';

$mensagem = '';
$tipo_mensagem = '';

// Processar formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $categoria = trim($_POST['categoria'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';

    // Validação básica
    if (empty($nome) || empty($telefone) || empty($categoria) || empty($senha)) {
        $mensagem = 'Por favor, preencha todos os campos obrigatórios.';
        $tipo_mensagem = 'erro';
    } elseif ($senha !== $confirmar_senha) {
        $mensagem = 'As senhas não coincidem.';
        $tipo_mensagem = 'erro';
    } elseif (strlen($senha) < 6) {
        $mensagem = 'A senha deve ter pelo menos 6 caracteres.';
        $tipo_mensagem = 'erro';
    } else {
        // Processar upload de foto
        $foto_path = '';
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $upload_dir = getUploadPath();
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_extension = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = getConfig('UPLOAD_ALLOWED_TYPES');

            if (in_array($file_extension, $allowed_extensions)) {
                $foto_name = uniqid() . '.' . $file_extension;
                $foto_path = $upload_dir . $foto_name;

                if (move_uploaded_file($_FILES['foto']['tmp_name'], $foto_path)) {
                    // Foto enviada com sucesso
                } else {
                    $mensagem = 'Erro ao fazer upload da foto.';
                    $tipo_mensagem = 'erro';
                }
            } else {
                $mensagem = 'Formato de arquivo não suportado. Use JPG, PNG ou GIF.';
                $tipo_mensagem = 'erro';
            }
        }

        // Se não houve erro na foto, inserir no banco
        if (empty($mensagem)) {
            try {
                $db = getDatabase();

                // Verificar se o telefone já existe
                $existe = dbFetchOne("SELECT id FROM profissionais WHERE telefone = ?", [$telefone]);
                if ($existe) {
                    $mensagem = 'Este telefone já está cadastrado.';
                    $tipo_mensagem = 'erro';
                } else {
                    // Hash da senha
                    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

                    $db->execute(
                        "INSERT INTO profissionais (nome, telefone, categoria, descricao, foto, senha, email) VALUES (?, ?, ?, ?, ?, ?, ?)",
                        [$nome, $telefone, $categoria, $descricao, $foto_path, $senha_hash, $email]
                    );

                    $mensagem = 'Profissional cadastrado com sucesso! Agora você pode fazer login para acessar seu perfil.';
                    $tipo_mensagem = 'sucesso';

                    // Limpar campos do formulário
                    $nome = $telefone = $categoria = $descricao = $email = '';
                }
            } catch (Exception $e) {
                error_log("Erro ao cadastrar profissional: " . $e->getMessage());
                $mensagem = 'Erro ao cadastrar. Tente novamente.';
                $tipo_mensagem = 'erro';
            }
        }
    }
}

// Buscar categorias do banco
$categorias = [];
try {
    $db = getDatabase();
    $categorias = $db->query("SELECT nome, descricao FROM categorias WHERE ativa = 1 ORDER BY ordem");
} catch (Exception $e) {
    error_log("Erro ao buscar categorias: " . $e->getMessage());
    // Categorias padrão caso não consiga buscar do banco
    $categorias = [
        ['nome' => 'CFTV', 'descricao' => 'Sistemas de Câmeras e Segurança'],
        ['nome' => 'Pedreiro', 'descricao' => 'Construção e Reformas'],
        ['nome' => 'Pintor', 'descricao' => 'Pintura Residencial e Comercial'],
        ['nome' => 'Encanador', 'descricao' => 'Instalações Hidráulicas'],
        ['nome' => 'Eletricista', 'descricao' => 'Instalações Elétricas'],
        ['nome' => 'Jardineiro', 'descricao' => 'Jardinagem e Paisagismo'],
        ['nome' => 'Limpeza', 'descricao' => 'Serviços de Limpeza'],
        ['nome' => 'Manutenção', 'descricao' => 'Manutenção Geral']
    ];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Profissional - ZelaLar</title>
    <meta name="description" content="Cadastre-se como profissional no ZelaLar e comece a receber solicitações de serviços">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="img/logo.png">
    <!-- CSS -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/profissionais.css">
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
                    <a href="profissionais.php" class="active">Cadastrar</a>
                    <a href="login.php">Login</a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1><i class="fas fa-user-plus"></i> Cadastrar Profissional</h1>
                <p>Preencha o formulário abaixo para cadastrar um novo profissional</p>
            </div>

            <!-- Mensagem de feedback -->
            <?php if (!empty($mensagem)): ?>
                <div class="mensagem mensagem-<?= $tipo_mensagem ?>">
                    <?= htmlspecialchars($mensagem) ?>
                </div>
            <?php endif; ?>

            <!-- Formulário de Cadastro -->
            <div class="form-container">
                <form method="POST" enctype="multipart/form-data" class="form-profissional">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nome">Nome Completo *</label>
                            <input type="text" id="nome" name="nome"
                                value="<?= htmlspecialchars($nome ?? '') ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="telefone">Telefone/WhatsApp *</label>
                            <input type="tel" id="telefone" name="telefone"
                                value="<?= htmlspecialchars($telefone ?? '') ?>"
                                placeholder="(11) 99999-9999" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="categoria">Categoria *</label>
                            <select id="categoria" name="categoria" required>
                                <option value="">Selecione uma categoria</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= htmlspecialchars($cat['nome']) ?>"
                                        <?= (isset($categoria) && $categoria == $cat['nome']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="email">Email (opcional)</label>
                            <input type="email" id="email" name="email"
                                value="<?= htmlspecialchars($email ?? '') ?>"
                                placeholder="seu@email.com">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="descricao">Breve Descrição</label>
                        <textarea id="descricao" name="descricao" rows="4"
                            placeholder="Descreva suas habilidades, experiência e especialidades..."><?= htmlspecialchars($descricao ?? '') ?></textarea>
                    </div>

                    <!-- Modificação na senha -->
                    <div class="form-group">
                        <label for="senha">Senha *</label>
                        <div style="position: relative;">
                            <input type="password" id="senha" name="senha"
                                placeholder="Digite uma senha para acessar sua conta" required>
                            <button type="button" class="toggle-password"
                                style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none;">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <small>Esta senha será usada para você acessar seu perfil e ver suas avaliações.</small>
                    </div>

                    <!-- Modificação na confirmação de senha -->
                    <div class="form-group">
                        <label for="confirmar_senha">Confirmar Senha *</label>
                        <div style="position: relative;">
                            <input type="password" id="confirmar_senha" name="confirmar_senha"
                                placeholder="Confirme sua senha" required>
                            <button type="button" class="toggle-password"
                                style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none;">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <p id="password-error" style="color: red; margin-top: 5px;"></p>

                    <div class="form-group">
                        <label for="foto">Foto (opcional)</label>
                        <input type="file" id="foto" name="foto" accept="image/*">
                        <small>Formatos aceitos: JPG, PNG, GIF. Máximo 5MB.</small>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i>
                            Cadastrar Profissional
                        </button>
                        <a href="listagem.php" class="btn btn-secondary">
                            <i class="fas fa-list"></i>
                            Ver Listagem
                        </a>
                    </div>

                    <div class="form-info">
                        <p><i class="fas fa-info-circle"></i>
                            Após o cadastro, você poderá fazer login para gerenciar seu perfil e ver as avaliações dos clientes.</p>
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

    <!-- Scripts -->
    <script src="js/utils.js"></script>
    <script src="js/main.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Utils.initPhoneMask();
            Utils.initFormValidation();
        });

        // Alternar exibição das senhas
        document.querySelectorAll('.toggle-password').forEach(function(toggle) {
            toggle.addEventListener('click', function() {
                const passwordInput = this.previousElementSibling;
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    this.innerHTML = '<i class="fas fa-eye-slash"></i>';
                } else {
                    passwordInput.type = 'password';
                    this.innerHTML = '<i class="fas fa-eye"></i>';
                }
            });
        });

        // Verifica se as senhas coincidem
        const senhaInput = document.getElementById('senha');
        const confirmarInput = document.getElementById('confirmar_senha');
        const errorMessage = document.getElementById('password-error');

        function checkPasswordMatch() {
            if (senhaInput.value !== confirmarInput.value) {
                errorMessage.innerText = 'As senhas não coincidem.';
            } else {
                errorMessage.innerText = '';
            }
        }

        senhaInput.addEventListener('input', checkPasswordMatch);
        confirmarInput.addEventListener('input', checkPasswordMatch);
    </script>
</body>

</html>