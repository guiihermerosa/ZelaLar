<?php
/**
 * ZelaLar - Configurações Principais (Padrão 2024)
 */

// === Global
const SITE_NAME = 'ZelaLar';
const SITE_DESCRIPTION = 'Profissionais de Aluguel';
const SITE_URL = 'http://localhost/ZelaLar';
const SITE_VERSION = '1.0.0';
const SITE_ENVIRONMENT = 'production'; // development, production

// Contato
const CONTACT_WHATSAPP = '5511999999999';
const CONTACT_EMAIL = 'contato@zelalar.com';

// Uploads
const UPLOAD_MAX_SIZE = 10 * 1024 * 1024; // 10 MB
const UPLOAD_ALLOWED_TYPES = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
const UPLOAD_DIR = 'img/profissionais/';

// Paginação
const ITEMS_PER_PAGE = 12;

// Segurança
const SESSION_TIMEOUT = 1800; // 30 min
const PASSWORD_MIN_LENGTH = 8;
const CSRF_TOKEN_NAME = 'zelalar_csrf';

// Cache e performance
const CACHE_ENABLED = false;
const CACHE_DURATION = 3600;

// SEO
const META_DESCRIPTION = 'ZelaLar - Encontre profissionais qualificados.';
const META_KEYWORDS = 'profissionais, aluguel, interior, SP';

// Inicialização
setlocale(LC_ALL, 'pt_BR.UTF-8');
date_default_timezone_set('America/Sao_Paulo');

// Utilitários globais
function getConfig($key, $default = null) {
    return defined($key) ? constant($key) : $default;
}

function site_base_path(): string
{
    static $path = null;
    if ($path === null) {
        $parsed = parse_url(SITE_URL, PHP_URL_PATH) ?? '';
        $path = rtrim($parsed, '/');
    }
    return $path;
}

function site_url(string $path = ''): string
{
    $basePath = site_base_path();
    $prefix = $basePath === '' ? '' : $basePath . '/';
    if ($path === '' || $path === '/') {
        return $basePath === '' ? '/' : $basePath . '/';
    }
    return $prefix . ltrim($path, '/');
}

function absolute_url(string $path = ''): string
{
    $base = rtrim(SITE_URL, '/');
    if ($path === '' || $path === '/') {
        return $base . '/';
    }
    return $base . '/' . ltrim($path, '/');
}

function asset_url(string $path = ''): string
{
    return site_url($path);
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

/**
 * Retorna o caminho completo do diretório de uploads
 */
function getUploadPath(): string {
    $uploadDir = getConfig('UPLOAD_DIR', 'img/profissionais/');
    // Garantir que o caminho termine com barra
    $uploadDir = rtrim($uploadDir, '/') . '/';
    
    // Retornar caminho relativo ao diretório raiz do projeto
    return $uploadDir;
}

// Segue com outros utilitários principais se forem genuinamente usados.
