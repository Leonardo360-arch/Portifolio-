<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/security.php';
secure_session();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $_SESSION = []; session_destroy();
}
header('Location: login.php'); exit;
