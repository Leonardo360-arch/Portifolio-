<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/security.php';
secure_session(); send_security_headers();

if (!empty($_SESSION['admin_id'])) { header('Location: dashboard.php'); exit; }

$erro = '';
const MAX_TENTATIVAS = 5; const BLOQUEIO_SEG = 300;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $bloqueado = ($_SESSION['lock_until'] ?? 0) > time();
    if ($bloqueado) {
        $erro = 'Muitas tentativas. Aguarde alguns minutos e tente de novo.';
    } else {
        $email = trim((string)($_POST['email'] ?? ''));
        $senha = (string)($_POST['senha'] ?? '');
        $st = Database::conn()->prepare('SELECT id, senha_hash FROM admin_users WHERE email = ? LIMIT 1');
        $st->execute([$email]);
        $u = $st->fetch();
        // hash falso mantém o tempo de resposta igual quando o e-mail não existe
        $hash = $u['senha_hash'] ?? '$2y$10$usesomesillystringforsaltuOq1m0S3m2Yw8Jz8p1pQ3y9K1p4q2e';
        if (password_verify($senha, $hash) && $u) {
            session_regenerate_id(true);
            $_SESSION = [];
            $_SESSION['admin_id'] = (int)$u['id'];
            unset($_SESSION['tentativas'], $_SESSION['lock_until']);
            header('Location: dashboard.php'); exit;
        }
        $_SESSION['tentativas'] = ($_SESSION['tentativas'] ?? 0) + 1;
        if ($_SESSION['tentativas'] >= MAX_TENTATIVAS) {
            $_SESSION['lock_until'] = time() + BLOQUEIO_SEG; $_SESSION['tentativas'] = 0;
        }
        $erro = 'E-mail ou senha incorretos.';
    }
}
$semAdmin = false;
try { $semAdmin = (int)Database::conn()->query('SELECT COUNT(*) FROM admin_users')->fetchColumn() === 0; } catch (Throwable $e) {}
?><!DOCTYPE html>
<html lang="pt-BR"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Entrar — Admin</title>
<link rel="stylesheet" href="../assets/css/themes.css">
<link rel="stylesheet" href="../assets/css/style.css">
<script src="../assets/js/themes.js"></script>
</head><body>
<main class="container" style="max-width:420px;padding-top:12vh">
  <form method="post" class="card" style="padding:1.5rem;display:grid;gap:1rem" autocomplete="on">
    <h1 style="font-size:1.5rem">Entrar no painel</h1>
    <?php if ($erro): ?><p role="alert"><?= e($erro) ?></p><?php endif; ?>
    <?= csrf_field() ?>
    <label>E-mail<br><input type="email" name="email" required autofocus style="width:100%;padding:.6rem"></label>
    <label>Senha<br><input type="password" name="senha" required style="width:100%;padding:.6rem"></label>
    <button class="btn" type="submit">Entrar</button>
    <?php if ($semAdmin): ?><p><a href="setup.php">Ainda não há administrador: criar o primeiro</a></p><?php endif; ?>
  </form>
</main></body></html>
