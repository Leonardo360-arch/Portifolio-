<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../config/database.php';

[$_, $email, $senha] = $argv + [null, null, null];
if (!$email || !$senha || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($senha) < 8) {
    fwrite(STDERR, "Uso: php docker/create-admin.php email@exemplo.com SenhaComMin8Caracteres\n"); exit(1);
}
Database::conn()->prepare(
    'INSERT INTO admin_users (email, senha_hash) VALUES (?, ?) ON DUPLICATE KEY UPDATE senha_hash = VALUES(senha_hash)'
)->execute([$email, password_hash($senha, PASSWORD_BCRYPT)]);
echo "Admin pronto: $email\n";
