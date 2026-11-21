<?php
declare(strict_types=1);

function secure_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) { return; }
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
          || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
    session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'secure' => $https,
                               'httponly' => true, 'samesite' => 'Lax']);
    session_name('lmc_sid');
    session_start();
}

function send_security_headers(): void
{
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}

function e(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

/** Aceita só http(s); evita javascript: em links vindos do banco. */
function safe_url(?string $u): string
{
    $u = trim((string)$u);
    return preg_match('#^https?://#i', $u) ? $u : '#';
}

function csrf_token(): string
{
    secure_session();
    if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(32)); }
    return $_SESSION['csrf'];
}

function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">'; }

function csrf_check(): void
{
    secure_session();
    $ok = isset($_POST['csrf'], $_SESSION['csrf']) && hash_equals($_SESSION['csrf'], (string)$_POST['csrf']);
    if (!$ok) { http_response_code(419); exit('Sessão expirada ou requisição inválida. Volte e tente de novo.'); }
}

function require_admin(): void
{
    secure_session();
    if (empty($_SESSION['admin_id'])) { header('Location: login.php'); exit; }
}
