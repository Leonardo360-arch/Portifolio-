<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/security.php';
secure_session(); send_security_headers();

/** Acesso local (localhost / rede privada do Docker)? */
function setup_local(): bool
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    if (!filter_var($ip, FILTER_VALIDATE_IP)) { return false; }
    return !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
}

$token = (string)env('SETUP_TOKEN', '');
$erro = ''; $fatal = '';

try {
    $total = (int)Database::conn()->query('SELECT COUNT(*) FROM admin_users')->fetchColumn();
} catch (Throwable $e) {
    $total = -1; $fatal = 'Não foi possível ler a tabela admin_users. Confirme que o schema.sql foi importado e que o .env aponta para o banco certo.';
}
if ($total > 0) {
    http_response_code(403);
    $fatal = 'A configuração inicial já foi concluída. Use o login normal.';
}
if (!$fatal && $token === '' && !setup_local()) {
    http_response_code(403);
    $fatal = 'Por segurança, em um servidor público defina SETUP_TOKEN no arquivo .env e use esse token aqui.';
}

if (!$fatal && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = trim((string)($_POST['email'] ?? ''));
    $senha = (string)($_POST['senha'] ?? '');
    if ($token !== '' && !hash_equals($token, (string)($_POST['token'] ?? ''))) { $erro = 'Token de configuração incorreto.'; }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) { $erro = 'Informe um e-mail válido.'; }
    elseif (strlen($senha) < 8) { $erro = 'A senha precisa ter pelo menos 8 caracteres.'; }
    elseif ($senha !== (string)($_POST['senha2'] ?? '')) { $erro = 'As senhas não conferem.'; }
    else {
        // insere só se ainda não houver nenhum admin (atômico, evita corrida)
        $st = Database::conn()->prepare(
            'INSERT INTO admin_users (email, senha_hash) SELECT ?, ? FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM admin_users)');
        $st->execute([$email, password_hash($senha, PASSWORD_BCRYPT)]);
        if ($st->rowCount() === 1) { header('Location: login.php'); exit; }
        $fatal = 'A configuração inicial já foi concluída. Use o login normal.';
    }
}
?><!DOCTYPE html>
<html lang="pt-BR"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow"><title>Criar administrador</title>
<link rel="stylesheet" href="../assets/css/themes.css"><link rel="stylesheet" href="../assets/css/style.css">
<script src="../assets/js/themes.js"></script></head><body>
<main class="container" style="max-width:460px;padding-top:10vh">
<?php if ($fatal): ?>
  <div class="card" style="padding:1.5rem"><h1 style="font-size:1.4rem">Criar administrador</h1>
  <p><?= e($fatal) ?></p><p><a href="login.php">Ir para o login</a></p></div>
<?php else: ?>
  <form method="post" class="card admin-form" style="max-width:none">
    <h1 style="font-size:1.4rem">Criar o primeiro administrador</h1>
    <p>Esta tela só funciona uma vez: depois que o admin existir, ela é bloqueada.</p>
    <?php if ($erro): ?><p class="flash flash-erro" role="alert"><?= e($erro) ?></p><?php endif; ?>
    <?= csrf_field() ?>
    <?php if ($token !== ''): ?><label>Token de configuração (SETUP_TOKEN do .env)<input type="password" name="token" required></label><?php endif; ?>
    <label>E-mail<input type="email" name="email" required autofocus value="<?= e($_POST['email'] ?? '') ?>"></label>
    <label>Senha (mínimo 8 caracteres)<input type="password" name="senha" minlength="8" required></label>
    <label>Repita a senha<input type="password" name="senha2" minlength="8" required></label>
    <p><button class="btn" type="submit">Criar administrador</button></p>
  </form>
<?php endif; ?>
</main></body></html>
