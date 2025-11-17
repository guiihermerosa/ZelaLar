<?php
/**
 * ZelaLar - Configurações Avançadas do Sistema
 * Configurações centralizadas para o sistema ZelaLar
 */

// ===== CONFIGURAÇÕES DO SISTEMA =====
define('SITE_NAME', 'ZelaLar');
define('SITE_DESCRIPTION', 'Profissionais de Aluguel');
define('SITE_VERSION', '1.0.0');
define('SITE_URL', 'http://localhost');
define('SITE_ENVIRONMENT', 'development'); // development, staging, production

// ===== CONFIGURAÇÕES DE CONTATO =====
define('CONTACT_WHATSAPP', '5511999999999');
define('CONTACT_EMAIL', 'contato@zelalar.com');
define('CONTACT_PHONE', '(11) 99999-9999');
define('CONTACT_ADDRESS', 'Interior de São Paulo, SP');

// ===== CONFIGURAÇÕES DE UPLOAD =====
define('UPLOAD_MAX_SIZE', 10 * 1024 * 1024); // 10MB
define('UPLOAD_ALLOWED_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('UPLOAD_DIR', 'img/profissionais/');
define('UPLOAD_THUMBNAIL_SIZE', 300);

// ===== CONFIGURAÇÕES DE PAGINAÇÃO =====
define('ITEMS_PER_PAGE', 12);
define('MAX_PAGES_VISIBLE', 5);

// ===== CONFIGURAÇÕES DE SEGURANÇA =====
define('SESSION_TIMEOUT', 1800); // 30 minutos
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT', 900); // 15 minutos
define('CSRF_TOKEN_NAME', 'zelalar_csrf');
define('PASSWORD_MIN_LENGTH', 8);

// ===== CONFIGURAÇÕES DE CACHE =====
define('CACHE_ENABLED', true);
define('CACHE_DURATION', 3600); // 1 hora
define('CACHE_DIR', 'cache/');

// ===== CONFIGURAÇÕES DE EMAIL =====
define('EMAIL_ENABLED', true);
define('EMAIL_FROM', 'noreply@zelalar.com');
define('EMAIL_FROM_NAME', 'ZelaLar');
define('SMTP_HOST', 'localhost');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('SMTP_SECURE', 'tls');

// ===== CONFIGURAÇÕES DE NOTIFICAÇÕES =====
define('PUSH_NOTIFICATIONS_ENABLED', true);
define('VAPID_PUBLIC_KEY', '');
define('VAPID_PRIVATE_KEY', '');

// ===== CONFIGURAÇÕES DE ANALYTICS =====
define('GOOGLE_ANALYTICS_ID', '');
define('FACEBOOK_PIXEL_ID', '');
define('HOTJAR_ID', '');

// ===== CONFIGURAÇÕES DE REDES SOCIAIS =====
define('FACEBOOK_URL', 'https://facebook.com/zelalar');
define('INSTAGRAM_URL', 'https://instagram.com/zelalar');
define('LINKEDIN_URL', 'https://linkedin.com/company/zelalar');
define('YOUTUBE_URL', 'https://youtube.com/zelalar');

// ===== CONFIGURAÇÕES DE LOCALIZAÇÃO =====
define('DEFAULT_TIMEZONE', 'America/Sao_Paulo');
define('DEFAULT_LOCALE', 'pt_BR');
define('CURRENCY', 'BRL');
define('COUNTRY_CODE', 'BR');

// ===== CONFIGURAÇÕES DE PERFORMANCE =====
define('GZIP_COMPRESSION', true);
define('BROWSER_CACHING', true);
define('MINIFY_CSS', true);
define('MINIFY_JS', true);
define('LAZY_LOADING', true);

// ===== CONFIGURAÇÕES DE SEO =====
define('META_DESCRIPTION', 'ZelaLar - Encontre profissionais de aluguel para serviços diversos no interior de São Paulo. CFTV, pedreiros, pintores, encanadores e mais.');
define('META_KEYWORDS', 'profissionais, aluguel, serviços, interior, são paulo, CFTV, pedreiro, pintor, encanador');
define('OG_IMAGE', '/img/og-image.jpg');
define('FAVICON', '/img/logo.png');

// ===== CONFIGURAÇÕES DE BACKUP =====
define('BACKUP_ENABLED', false);
define('BACKUP_FREQUENCY', 'daily'); // daily, weekly, monthly
define('BACKUP_RETENTION', 30); // dias
define('BACKUP_DIR', 'backups/');

// ===== CONFIGURAÇÕES DE LOG =====
define('LOG_ENABLED', true);
define('LOG_LEVEL', 'INFO'); // DEBUG, INFO, WARNING, ERROR
define('LOG_DIR', 'logs/');
define('LOG_MAX_SIZE', 10 * 1024 * 1024); // 10MB

// ===== CONFIGURAÇÕES DE API =====
define('API_ENABLED', true);
define('API_VERSION', 'v1');
define('API_RATE_LIMIT', 100); // requests per hour
define('API_KEY_REQUIRED', false);

// ===== CONFIGURAÇÕES DE TESTES =====
define('TESTING_MODE', false);
define('MOCK_DATA_ENABLED', false);

// ===== INICIALIZAÇÃO =====
date_default_timezone_set(DEFAULT_TIMEZONE);
setlocale(LC_ALL, DEFAULT_LOCALE . '.UTF-8');

// ===== FUNÇÕES DE CONFIGURAÇÃO =====


function getConfig($key, $default = null) {
    return defined($key) ? constant($key) : $default;
}

function isDevelopment() {
    return SITE_ENVIRONMENT === 'development';
}


function isProduction() {
    return SITE_ENVIRONMENT === 'production';
}

function getSiteUrl($path = '') {
    return rtrim(SITE_URL, '/') . '/' . ltrim($path, '/');
}


function getUploadPath($filename = '') {
    return UPLOAD_DIR . ltrim($filename, '/');
}


function getUploadUrl($filename = '') {
    return getSiteUrl(UPLOAD_DIR . ltrim($filename, '/'));
}

function generateCSRFToken() {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}


function verifyCSRFToken($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

function getEnvironmentConfig() {
    return [
        'environment' => SITE_ENVIRONMENT,
        'debug' => isDevelopment(),
        'cache' => CACHE_ENABLED,
        'compression' => GZIP_COMPRESSION,
        'minification' => MINIFY_CSS || MINIFY_JS
    ];
}


function loadEnvironmentConfig() {
    $envFile = __DIR__ . '/../.env.' . SITE_ENVIRONMENT;
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                if (!defined($key)) {
                    define($key, $value);
                }
            }
        }
    }
}


loadEnvironmentConfig();
?>
