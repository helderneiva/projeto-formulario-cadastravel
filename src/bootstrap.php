<?php

declare(strict_types=1);

use Dotenv\Dotenv;

require_once dirname(__DIR__) . '/vendor/autoload.php';

// Carrega as variáveis do arquivo .env (credenciais do banco).
// safeLoad() não falha se o arquivo não existir; em seguida, required()
// confere se as variáveis indispensáveis estão presentes.
$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();
$dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER']);

date_default_timezone_set('America/Sao_Paulo');

// Cabeçalhos de segurança. A política de conteúdo (CSP) só permite
// recursos do próprio site: por isso o projeto não usa CSS nem JavaScript
// escritos dentro das páginas (style="..." ou <script> inline).
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: same-origin');
    header(
        "Content-Security-Policy: default-src 'self'; "
        . "img-src 'self' blob:; "
        . "style-src 'self'; "
        . "script-src 'self'; "
        . "form-action 'self'; "
        . "base-uri 'self'; "
        . "frame-ancestors 'none'"
    );
}

// Sessão: só aceita identificadores gerados pelo servidor e só por cookie.
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');

session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => !empty($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();