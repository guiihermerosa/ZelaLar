<?php

require_once 'config/config.php';
require_once 'config/database.php';
require_once 'classes/Profissional.php';

session_start();

// Verificar se está logado
if (!isset($_SESSION['profissional_id'])) {
    header('Location: profissional_login.php');
    exit;
}

$profissional_id = $_SESSION['profissional_id'];
$profissional_obj = new Profissional();

// Obter dados
$prof = dbFetchOne("SELECT * FROM profissionais WHERE id = ?", [$profissional_id]);
$stats = $profissional_obj->obterEstatisticas($profissional_id);
$avaliacoes = $profissional_obj->obterAvaliacoes($profissional_id, 1, 5);
$contatos = $profissional_obj->obterContatos($profissional_id, 1, 10);

$aba_ativa = $_GET['aba'] ?? 'dashboard';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo $prof['nome']; ?> - ZelaLar</title>
    <link rel="stylesheet" href="Styles/index.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background-color: #f5f7fa;
            font-family: 'Inter', sans-serif;
        }
        
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            left: 0;
            top: 0;
        }
        
        .sidebar-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            padding-bottom: 20px;
        }
        
        .sidebar-header img {
            max-width: 100px;
            margin-bottom: 10px;
        }
        
        .sidebar-header h2 {
            font-size: 18px;
        }
        
        .sidebar-menu {
            list-style: none;
        }
        
        .sidebar-menu li {
            margin-bottom: 10px;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255,255,255,0.2);
        }
        
        .sidebar-menu i {
            margin-right: 10px;
            width: 20px;
        }
        
        .main-content {
            margin-left: 250px;
            flex: 1;
            padding: 20px;
        }
        
        .header-bar {
            background: white;
            padding: 20px;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #667eea;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        
        .logout-btn {
            padding: 10px 20px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.3s;
        }
        
        .logout-btn:hover {
            background: #c82333;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
        }
        
        .stat-icon.blue {
            background: #e3f2fd;
            color: #2196f3;
        }
        
        .stat-icon.green {
            background: #e8f5e9;
            color: #4caf50;
        }
        
        .stat-icon.orange {
            background: #fff3e0;
            color: #ff9800;
        }
        
        .stat-icon.purple {
            background: #f3e5f5;
            color: #9c27b0;
        }
        
        .stat-content h3 {
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .stat-content .value {
            font-size: 28px;
            font-weight: bold;
            color: #333;
        }
        
        .content-section {
            display: none;
        }
        
        .content-section.active {
            display: block;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .card-header {
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }
        
        .card-header h2 {
            font-size: 20px;
            color: #333;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th {
            background: #f5f7fa;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #666;
            border-bottom: 1px solid #ddd;
        }
        
        .table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        
        .table tr:hover {
            background: #f9fafc;
        }
        
        .rating {
            display: flex;
            gap: 2px;
            color: #ffc107;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        
        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
            opacity: 0.3;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .dashboard-container {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="img/logo.png" alt="ZelaLar">
                <h2><?php echo htmlspecialchars($prof['nome']); ?></h2>
            </div>
            
            <ul class="sidebar-menu">
                <li>
                    <a href="?aba=dashboard" class="<?php echo $aba_ativa === 'dashboard' ? 'active' : ''; ?>">
                        <i class="fas fa-chart-line"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="?aba=avaliacoes" class="<?php echo $aba_ativa === 'avaliacoes' ? 'active' : ''; ?>">
                        <i class="fas fa-star"></i> Avaliações
                    </a>
                </li>
                <li>
                    <a href="?aba=contatos" class="<?php echo $aba_ativa === 'contatos' ? 'active' : ''; ?>">
                        <i class="fas fa-phone"></i> Contatos Recebidos
                    </a>
                </li>
                <li>
                    <a href="?aba=perfil" class="<?php echo $aba_ativa === 'perfil' ? 'active' : ''; ?>">
                        <i class="fas fa-user"></i> Meu Perfil
                    </a>
                </li>
                <li>
                    <a href="logout.php">
                        <i class="fas fa-sign-out-alt"></i> Sair
                    </a>
                </li>
            </ul>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <div class="header-bar">
                <h1>Bem-vindo, <?php echo htmlspecialchars($prof['nome']); ?>!</h1>
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <a href="logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Sair
                    </a>
                </div>
            </div>
            
            <!-- Dashboard Principal -->
            <section class="content-section <?php echo $aba_ativa === 'dashboard' ? 'active' : ''; ?>" id="dashboard">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon blue">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="stat-content">
                            <h3>Avaliações</h3>
                            <div class="value"><?php echo $stats['total_avaliacoes'] ?? 0; ?></div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon green">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="stat-content">
                            <h3>Contatos Totais</h3>
                            <div class="value"><?php echo $stats['total_contatos'] ?? 0; ?></div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon orange">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                        <div class="stat-content">
                            <h3>Contatos Hoje</h3>
                            <div class="value"><?php echo $stats['contatos_hoje'] ?? 0; ?></div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon purple">
                            <i class="fas fa-calendar-week"></i>
                        </div>
                        <div class="stat-content">
                            <h3>Contatos Esta Semana</h3>
                            <div class="value"><?php echo $stats['contatos_semana'] ?? 0; ?></div>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($avaliacoes['avaliacoes'])): ?>
                    <div class="card">
                        <div class="card-header">
                            <h2>Últimas Avaliações</h2>
                        </div>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Avaliação</th>
                                    <th>Comentário</th>
                                    <th>Data</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($avaliacoes['avaliacoes'] as $avaliacao): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($avaliacao['cliente_nome']); ?></td>
                                        <td>
                                            <div class="rating">
                                                <?php for ($i = 0; $i < $avaliacao['nota']; $i++): ?>
                                                    <i class="fas fa-star"></i>
                                                <?php endfor; ?>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars(substr($avaliacao['comentario'], 0, 50) . '...'); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($avaliacao['data_avaliacao'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="card">
                        <div class="empty-state">
                            <i class="fas fa-star"></i>
                            <p>Você ainda não tem avaliações</p>
                        </div>
                    </div>
                <?php endif; ?>
            </section>
            
            <!-- Avaliações -->
            <section class="content-section <?php echo $aba_ativa === 'avaliacoes' ? 'active' : ''; ?>" id="avaliacoes">
                <div class="card">
                    <div class="card-header">
                        <h2>Minhas Avaliações (<?php echo $avaliacoes['total']; ?>)</h2>
                    </div>
                    
                    <?php if (!empty($avaliacoes['avaliacoes'])): ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Telefone</th>
                                    <th>Avaliação</th>
                                    <th>Comentário</th>
                                    <th>Data</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($avaliacoes['avaliacoes'] as $avaliacao): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($avaliacao['cliente_nome']); ?></td>
                                        <td><?php echo htmlspecialchars($avaliacao['cliente_telefone']); ?></td>
                                        <td>
                                            <div class="rating">
                                                <?php for ($i = 0; $i < $avaliacao['nota']; $i++): ?>
                                                    <i class="fas fa-star"></i>
                                                <?php endfor; ?>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($avaliacao['comentario']); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($avaliacao['data_avaliacao'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-star"></i>
                            <p>Você ainda não tem avaliações</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
            
            <!-- Contatos Recebidos -->
            <section class="content-section <?php echo $aba_ativa === 'contatos' ? 'active' : ''; ?>" id="contatos">
                <div class="card">
                    <div class="card-header">
                        <h2>Contatos Recebidos (<?php echo $contatos['total']; ?>)</h2>
                    </div>
                    
                    <?php if (!empty($contatos['contatos'])): ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Telefone</th>
                                    <th>Email</th>
                                    <th>Mensagem</th>
                                    <th>Data</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($contatos['contatos'] as $contato): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($contato['cliente_nome']); ?></td>
                                        <td>
                                            <a href="tel:<?php echo htmlspecialchars($contato['cliente_telefone']); ?>">
                                                <?php echo htmlspecialchars($contato['cliente_telefone']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo htmlspecialchars($contato['cliente_email'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars(substr($contato['mensagem'], 0, 30) . '...'); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($contato['data_contato'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-phone"></i>
                            <p>Você ainda não recebeu contatos</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
            
            <!-- Perfil -->
            <section class="content-section <?php echo $aba_ativa === 'perfil' ? 'active' : ''; ?>" id="perfil">
                <div class="card">
                    <div class="card-header">
                        <h2>Meu Perfil</h2>
                    </div>
                    
                    <div style="display: grid; gap: 15px;">
                        <div>
                            <label>Nome:</label>
                            <p><?php echo htmlspecialchars($prof['nome']); ?></p>
                        </div>
                        <div>
                            <label>Telefone:</label>
                            <p><?php echo htmlspecialchars($prof['telefone']); ?></p>
                        </div>
                        <div>
                            <label>Email:</label>
                            <p><?php echo htmlspecialchars($prof['email']); ?></p>
                        </div>
                        <div>
                            <label>Categoria:</label>
                            <p><?php echo htmlspecialchars($prof['categoria']); ?></p>
                        </div>
                        <div>
                            <label>Cidade:</label>
                            <p><?php echo htmlspecialchars($prof['cidade']); ?></p>
                        </div>
                        <div>
                            <label>Avaliação Média:</label>
                            <p><?php echo number_format($prof['avaliacao'], 1); ?>/5.0</p>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
    
    <script>
        document.querySelectorAll('.sidebar-menu a').forEach(link => {
            link.addEventListener('click', function() {
                document.querySelectorAll('.sidebar-menu a').forEach(l => l.classList.remove('active'));
                this.classList.add('active');
            });
        });
    </script>
</body>
</html>