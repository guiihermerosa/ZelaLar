<?php
require_once 'config/config.php';
require_once 'config/database.php';

// Buscar estatísticas do sistema
try {
    $db = getDatabase();
    $totalProfissionais = $db->fetchValue("SELECT COUNT(*) FROM profissionais");
    $totalCategorias = $db->fetchValue("SELECT COUNT(DISTINCT categoria) FROM profissionais");
    $topCategorias = $db->query("SELECT categoria, COUNT(*) as total FROM profissionais GROUP BY categoria ORDER BY total DESC LIMIT 5");
} catch (Exception $e) {
    $totalProfissionais = 0;
    $totalCategorias = 0;
    $topCategorias = [];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZelaLar - Conectando Profissionais e Clientes no Interior de São Paulo</title>
    <meta name="description" content="Encontre os melhores profissionais de CFTV, construção, manutenção e muito mais no interior de São Paulo. Agende serviços com segurança e qualidade.">
    <meta name="keywords" content="profissionais, interior SP, CFTV, pedreiro, pintor, encanador, eletricista, jardinagem, serviços">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo getConfig('SITE_URL'); ?>">
    <meta property="og:title" content="ZelaLar - Profissionais de Qualidade no Interior de SP">
    <meta property="og:description" content="Conectamos você aos melhores profissionais da região. Agende serviços com segurança e confiança.">
    <meta property="og:image" content="<?php echo getConfig('SITE_URL'); ?>/img/og-image.jpg">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?php echo getConfig('SITE_URL'); ?>">
    <meta property="twitter:title" content="ZelaLar - Profissionais de Qualidade no Interior de SP">
    <meta property="twitter:description" content="Conectamos você aos melhores profissionais da região. Agende serviços com segurança e confiança.">
    <meta property="twitter:image" content="<?php echo getConfig('SITE_URL'); ?>/img/og-image.jpg">

    <link rel="icon" type="image/png" href="img/logo.png">
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Preload de recursos críticos -->
    <link rel="preload" href="css/style.css" as="style">
    <link rel="preload" href="js/main.js" as="script">
</head>
<body>
    <!-- Loading Screen -->
    <div id="loading-screen" class="loading-screen">
        <div class="loading-content">
            <div class="loading-logo">
                <img src="img/logo.png" alt="ZelaLar" class="loading-logo-image">
            </div>
            <div class="loading-spinner"></div>
            <p>Carregando ZelaLar...</p>
        </div>
    </div>

    <!-- Cabeçalho Moderno -->
    <header class="header" id="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="#inicio">
                        <img src="img/logo_nome.png" alt="ZelaLar - Profissionais de Qualidade" class="logo-image">
                    </a>
                </div>
                
                <nav class="nav" id="main-nav">
                    <a href="#inicio" class="nav-link active">Início</a>
                    <a href="#categorias" class="nav-link">Categorias</a>
                    <a href="#como-funciona" class="nav-link">Como Funciona</a>
                    <a href="#sobre" class="nav-link">Sobre</a>
                    <a href="listagem.php" class="nav-link">Profissionais</a>
                    <a href="profissionais.php" class="nav-link">Cadastrar</a>
                    <a href="login.php" class="nav-link">Login</a>
                </nav>
                
                <div class="header-actions">
                    <a href="tel:<?php echo getConfig('CONTACT_PHONE'); ?>" class="btn-phone-header">
                        <i class="fas fa-phone"></i>
                        <span>Ligar</span>
                    </a>
                    <button class="mobile-menu-toggle" id="mobile-menu-toggle">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section Moderno -->
    <section class="hero" id="inicio">
        <div class="hero-background">
            <div class="hero-particles" id="hero-particles"></div>
        </div>
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <h1 class="hero-title">
                        Encontre o <span class="highlight">Profissional Ideal</span> 
                        para seu Projeto
                    </h1>
                    <p class="hero-subtitle">
                        Conectamos você aos melhores profissionais do interior de São Paulo. 
                        CFTV, construção, manutenção e muito mais com qualidade e confiança.
                    </p>
                    <div class="hero-stats">
                        <div class="stat-item">
                            <span class="stat-number" data-target="<?php echo $totalProfissionais; ?>">0</span>
                            <span class="stat-label">Profissionais</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number" data-target="<?php echo $totalCategorias; ?>">0</span>
                            <span class="stat-label">Categorias</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number" data-target="98">0</span>
                            <span class="stat-label">% Satisfação</span>
                        </div>
                    </div>
                    <div class="hero-actions">
                        <a href="listagem.php" class="btn-primary btn-hero">
                            <i class="fas fa-search"></i>
                            Encontrar Profissional
                        </a>
                        <a href="profissionais.php" class="btn-secondary btn-hero">
                            <i class="fas fa-user-plus"></i>
                            Seja um Profissional
                        </a>
                    </div>
                </div>
                <div class="hero-visual">
                    <div class="hero-image">
                        <div class="floating-card card-1">
                            <i class="fas fa-star"></i>
                            <span>5.0</span>
                        </div>
                        <div class="floating-card card-2">
                            <i class="fas fa-clock"></i>
                            <span>24h</span>
                        </div>
                        <div class="floating-card card-3">
                            <i class="fas fa-shield-alt"></i>
                            <span>Seguro</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Scroll Indicator -->
        <div class="scroll-indicator">
            <div class="scroll-arrow"></div>
            <span>Role para descobrir</span>
        </div>
    </section>

    <!-- Seção de Categorias Moderna -->
    <section class="categories" id="categorias">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Nossas Categorias</h2>
                <p class="section-subtitle">Encontre profissionais especializados em diversas áreas</p>
            </div>
            
            <div class="categories-grid">
                <div class="category-card" data-category="CFTV">
                    <div class="category-icon">
                        <i class="fas fa-video"></i>
                    </div>
                    <h3>CFTV & Segurança</h3>
                    <p>Instalação e manutenção de sistemas de segurança residencial e comercial</p>
                    <div class="category-features">
                        <span><i class="fas fa-check"></i> Instalação</span>
                        <span><i class="fas fa-check"></i> Manutenção</span>
                        <span><i class="fas fa-check"></i> Configuração</span>
                    </div>
                    <div class="category-actions">
                        <a href="listagem.php?categoria=CFTV" class="btn-category">
                            Ver Profissionais
                        </a>
                        <a href="https://wa.me/<?php echo getConfig('CONTACT_WHATSAPP'); ?>?text=Olá! Gostaria de agendar um serviço de CFTV" 
                           class="btn-whatsapp-category" target="_blank">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                </div>

                <div class="category-card" data-category="Pedreiro">
                    <div class="category-icon">
                        <i class="fas fa-hammer"></i>
                    </div>
                    <h3>Construção Civil</h3>
                    <p>Pedreiros experientes para construção, reforma e acabamento</p>
                    <div class="category-features">
                        <span><i class="fas fa-check"></i> Alvenaria</span>
                        <span><i class="fas fa-check"></i> Acabamento</span>
                        <span><i class="fas fa-check"></i> Reformas</span>
                    </div>
                    <div class="category-actions">
                        <a href="listagem.php?categoria=Pedreiro" class="btn-category">
                            Ver Profissionais
                        </a>
                        <a href="https://wa.me/<?php echo getConfig('CONTACT_WHATSAPP'); ?>?text=Olá! Gostaria de agendar um serviço de Pedreiro" 
                           class="btn-whatsapp-category" target="_blank">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                </div>

                <div class="category-card" data-category="Pintor">
                    <div class="category-icon">
                        <i class="fas fa-paint-roller"></i>
                    </div>
                    <h3>Pintura Profissional</h3>
                    <p>Pintores qualificados para residências, comércios e indústrias</p>
                    <div class="category-features">
                        <span><i class="fas fa-check"></i> Residencial</span>
                        <span><i class="fas fa-check"></i> Comercial</span>
                        <span><i class="fas fa-check"></i> Texturas</span>
                    </div>
                    <div class="category-actions">
                        <a href="listagem.php?categoria=Pintor" class="btn-category">
                            Ver Profissionais
                        </a>
                        <a href="https://wa.me/<?php echo getConfig('CONTACT_WHATSAPP'); ?>?text=Olá! Gostaria de agendar um serviço de Pintor" 
                           class="btn-whatsapp-category" target="_blank">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                </div>

                <div class="category-card" data-category="Encanador">
                    <div class="category-icon">
                        <i class="fas fa-wrench"></i>
                    </div>
                    <h3>Hidráulica</h3>
                    <p>Encanadores especializados em instalações e reparos hidráulicos</p>
                    <div class="category-features">
                        <span><i class="fas fa-check"></i> Instalação</span>
                        <span><i class="fas fa-check"></i> Reparos</span>
                        <span><i class="fas fa-check"></i> Manutenção</span>
                    </div>
                    <div class="category-actions">
                        <a href="listagem.php?categoria=Encanador" class="btn-category">
                            Ver Profissionais
                        </a>
                        <a href="https://wa.me/<?php echo getConfig('CONTACT_WHATSAPP'); ?>?text=Olá! Gostaria de agendar um serviço de Encanador" 
                           class="btn-whatsapp-category" target="_blank">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                </div>

                <div class="category-card" data-category="Eletricista">
                    <div class="category-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h3>Instalações Elétricas</h3>
                    <p>Eletricistas certificados para instalações seguras e eficientes</p>
                    <div class="category-features">
                        <span><i class="fas fa-check"></i> Instalação</span>
                        <span><i class="fas fa-check"></i> Manutenção</span>
                        <span><i class="fas fa-check"></i> Segurança</span>
                    </div>
                    <div class="category-actions">
                        <a href="listagem.php?categoria=Eletricista" class="btn-category">
                            Ver Profissionais
                        </a>
                        <a href="https://wa.me/<?php echo getConfig('CONTACT_WHATSAPP'); ?>?text=Olá! Gostaria de agendar um serviço de Eletricista" 
                           class="btn-whatsapp-category" target="_blank">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                </div>

                <div class="category-card" data-category="Jardinagem">
                    <div class="category-icon">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <h3>Jardinagem & Paisagismo</h3>
                    <p>Especialistas em manutenção de jardins e paisagismo</p>
                    <div class="category-features">
                        <span><i class="fas fa-check"></i> Manutenção</span>
                        <span><i class="fas fa-check"></i> Paisagismo</span>
                        <span><i class="fas fa-check"></i> Podas</span>
                    </div>
                    <div class="category-actions">
                        <a href="listagem.php?categoria=Jardinagem" class="btn-category">
                            Ver Profissionais
                        </a>
                        <a href="https://wa.me/<?php echo getConfig('CONTACT_WHATSAPP'); ?>?text=Olá! Gostaria de agendar um serviço de Jardinagem" 
                           class="btn-whatsapp-category" target="_blank">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Seção Como Funciona -->
    <section class="how-it-works" id="como-funciona">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Como Funciona</h2>
                <p class="section-subtitle">Processo simples e seguro para encontrar profissionais</p>
            </div>
            
            <div class="steps-grid">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <div class="step-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>Busque</h3>
                    <p>Escolha a categoria de serviço que você precisa</p>
                </div>
                
                <div class="step-card">
                    <div class="step-number">2</div>
                    <div class="step-icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <h3>Compare</h3>
                    <p>Veja perfis, avaliações e preços dos profissionais</p>
                </div>
                
                <div class="step-card">
                    <div class="step-number">3</div>
                    <div class="step-icon">
                        <i class="fab fa-whatsapp"></i>
                    </div>
                    <h3>Agende</h3>
                    <p>Entre em contato direto via WhatsApp ou telefone</p>
                </div>
                
                <div class="step-card">
                    <div class="step-number">4</div>
                    <div class="step-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <h3>Avalie</h3>
                    <p>Deixe sua avaliação para ajudar outros clientes</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Seção Sobre -->
    <section class="about" id="sobre">
        <div class="container">
            <div class="about-content">
                <div class="about-text">
                    <h2>Sobre o ZelaLar</h2>
                    <p class="about-description">
                        O ZelaLar nasceu da necessidade de conectar profissionais qualificados 
                        aos moradores do interior de São Paulo. Nossa missão é facilitar o 
                        encontro entre quem precisa de serviços e quem pode oferecer com qualidade.
                    </p>
                    
                    <div class="about-features">
                        <div class="feature-item">
                            <i class="fas fa-shield-alt"></i>
                            <div>
                                <h4>Profissionais Verificados</h4>
                                <p>Todos os profissionais passam por processo de verificação</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <h4>Atendimento Local</h4>
                                <p>Focamos no interior de São Paulo para melhor atendimento</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <i class="fas fa-headset"></i>
                            <div>
                                <h4>Suporte 24/7</h4>
                                <p>Nossa equipe está sempre disponível para ajudar</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="about-visual">
                    <div class="about-image">
                        <div class="image-overlay">
                            <div class="overlay-content">
                                <i class="fas fa-users"></i>
                                <h3>+500</h3>
                                <p>Clientes Satisfeitos</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Seção de Depoimentos -->
    <section class="testimonials">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">O que Dizem Nossos Clientes</h2>
                <p class="section-subtitle">Depoimentos de quem já usou nossos serviços</p>
            </div>
            
            <div class="testimonials-slider" id="testimonials-slider">
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <p>"Excelente serviço! Encontrei um pedreiro muito competente que reformou minha casa com qualidade."</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="author-info">
                            <h4>Maria Silva</h4>
                            <span>Ribeirão Preto, SP</span>
                        </div>
                        <div class="rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <p>"O sistema de CFTV instalado superou minhas expectativas. Profissional muito atencioso e competente."</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="author-info">
                            <h4>João Santos</h4>
                            <span>Franca, SP</span>
                        </div>
                        <div class="rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <p>"Pintor muito profissional! Trabalhou com cuidado e deixou minha casa linda. Recomendo!"</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="author-info">
                            <h4>Ana Costa</h4>
                            <span>Araraquara, SP</span>
                        </div>
                        <div class="rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="testimonials-nav">
                <button class="nav-btn prev" id="prev-testimonial">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="nav-btn next" id="next-testimonial">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>Pronto para Encontrar seu Profissional?</h2>
                <p>Junte-se a centenas de clientes satisfeitos que já encontraram profissionais de qualidade</p>
                <div class="cta-actions">
                    <a href="listagem.php" class="btn-primary btn-large">
                        <i class="fas fa-search"></i>
                        Buscar Profissional
                    </a>
                    <a href="profissionais.php" class="btn-outline btn-large">
                        <i class="fas fa-user-plus"></i>
                        Cadastrar como Profissional
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Rodapé Moderno -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                                         <div class="footer-logo">
                         <img src="img/logo_nome.png" alt="ZelaLar - Profissionais de Qualidade" class="footer-logo-image">
                     </div>
                    <p class="footer-description">
                        Conectando profissionais qualificados aos moradores do interior de São Paulo. 
                        Qualidade, confiança e satisfação garantida.
                    </p>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-whatsapp"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h4>Links Rápidos</h4>
                    <ul class="footer-links">
                        <li><a href="#inicio">Início</a></li>
                        <li><a href="#categorias">Categorias</a></li>
                        <li><a href="#como-funciona">Como Funciona</a></li>
                        <li><a href="#sobre">Sobre</a></li>
                        <li><a href="listagem.php">Profissionais</a></li>
                        <li><a href="profissionais.php">Cadastrar</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Categorias</h4>
                    <ul class="footer-links">
                        <li><a href="listagem.php?categoria=CFTV">CFTV & Segurança</a></li>
                        <li><a href="listagem.php?categoria=Pedreiro">Construção Civil</a></li>
                        <li><a href="listagem.php?categoria=Pintor">Pintura</a></li>
                        <li><a href="listagem.php?categoria=Encanador">Hidráulica</a></li>
                        <li><a href="listagem.php?categoria=Eletricista">Elétrica</a></li>
                        <li><a href="listagem.php?categoria=Jardinagem">Jardinagem</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Contato</h4>
                    <div class="contact-info">
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <span><?php echo getConfig('CONTACT_PHONE'); ?></span>
                        </div>
                        <div class="contact-item">
                            <i class="fab fa-whatsapp"></i>
                            <span>WhatsApp: <?php echo getConfig('CONTACT_PHONE'); ?></span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <span><?php echo getConfig('CONTACT_EMAIL'); ?></span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Interior de São Paulo</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <div class="footer-bottom-content">
                    <p>&copy; 2024 ZelaLar. Todos os direitos reservados.</p>
                    <div class="footer-bottom-links">
                        <a href="#">Termos de Uso</a>
                        <a href="#">Política de Privacidade</a>
                        <a href="#">Cookies</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- WhatsApp Float Button -->
    <div class="whatsapp-float" id="whatsapp-float">
        <a href="https://wa.me/<?php echo getConfig('CONTACT_WHATSAPP'); ?>?text=Olá! Gostaria de saber mais sobre os serviços do ZelaLar" 
           target="_blank" class="whatsapp-link">
            <i class="fab fa-whatsapp"></i>
        </a>
        <div class="whatsapp-tooltip">
            Fale conosco no WhatsApp!
        </div>
    </div>

    <!-- Back to Top Button -->
    <button class="back-to-top" id="back-to-top">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Scripts -->
    <script src="js/main.js"></script>
    <script src="js/particles.js"></script>
    <script src="js/animations.js"></script>
</body>
</html>
