<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../config/upload.php';

function flash(?string $msg = null, string $tipo = 'ok'): ?array
{
    secure_session();
    if ($msg !== null) { $_SESSION['flash'] = [$msg, $tipo]; return null; }
    $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f;
}
function redirect(string $to): never { header('Location: ' . $to); exit; }

function admin_header(string $titulo): void
{
    require_admin(); send_security_headers();
    $f = flash(); ?>
<!DOCTYPE html>
<html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow"><title><?= e($titulo) ?> — Admin</title>
<link rel="stylesheet" href="../assets/css/themes.css"><link rel="stylesheet" href="../assets/css/style.css">
<script src="../assets/js/themes.js"></script></head><body>
<header class="container admin-nav">
  <a href="dashboard.php">Painel</a><a href="projetos.php">Projetos</a><a href="../index.php" target="_blank" rel="noopener">Ver site</a>
  <form method="post" action="logout.php"><?= csrf_field() ?><button class="btn">Sair</button></form>
</header>
<main class="container admin" style="padding-bottom:4rem">
<h1><?= e($titulo) ?></h1>
<?php if ($f): ?><p class="flash <?= $f[1] === 'erro' ? 'flash-erro' : '' ?>" role="status"><?= e($f[0]) ?></p><?php endif;
}
function admin_footer(): void { echo '</main></body></html>'; }
