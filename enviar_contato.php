<?php
declare(strict_types=1);
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/security.php';
require_once __DIR__ . '/config/mailer.php';
secure_session(); send_security_headers();
header('Content-Type: application/json; charset=utf-8');

function resp(int $status, bool $ok, string $msg): never
{
    http_response_code($status);
    echo json_encode(['ok' => $ok, 'msg' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { resp(405, false, 'Método não permitido.'); }
if (!isset($_POST['csrf'], $_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], (string)$_POST['csrf'])) {
    resp(419, false, 'A página expirou. Recarregue e envie de novo.');
}
if (trim((string)($_POST['website'] ?? '')) !== '') { resp(200, true, 'Mensagem enviada. Obrigado!'); } // honeypot: bot finge sucesso

// limite simples por sessão: 1 envio a cada 30s e no máximo 5 por hora
$agora = time();
$_SESSION['contato_log'] = array_values(array_filter($_SESSION['contato_log'] ?? [], fn($t) => $t > $agora - 3600));
if ($_SESSION['contato_log'] && end($_SESSION['contato_log']) > $agora - 30) { resp(429, false, 'Aguarde alguns segundos antes de enviar outra mensagem.'); }
if (count($_SESSION['contato_log']) >= 5) { resp(429, false, 'Limite de mensagens atingido. Tente novamente mais tarde.'); }

$nome = trim((string)($_POST['nome'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$assunto = trim((string)($_POST['assunto'] ?? ''));
$mensagem = trim((string)($_POST['mensagem'] ?? ''));

if ($nome === '' || mb_strlen($nome) > 120) { resp(422, false, 'Informe seu nome (até 120 caracteres).'); }
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) { resp(422, false, 'Informe um e-mail válido.'); }
if (mb_strlen($assunto) > 150) { resp(422, false, 'O assunto pode ter até 150 caracteres.'); }
if (mb_strlen($mensagem) < 10 || mb_strlen($mensagem) > 5000) { resp(422, false, 'A mensagem deve ter entre 10 e 5000 caracteres.'); }

// 1) cópia de segurança no banco, sempre antes do e-mail (RF-06)
try {
    Database::conn()->prepare('INSERT INTO mensagens (nome, email, assunto, mensagem) VALUES (?,?,?,?)')
        ->execute([$nome, $email, $assunto ?: null, $mensagem]);
} catch (Throwable $ex) {
    error_log('contato/db: ' . $ex->getMessage());
    resp(500, false, 'Não foi possível registrar sua mensagem agora. Tente pelo WhatsApp.');
}
$_SESSION['contato_log'][] = $agora;

// 2) e-mail via SMTP; se falhar, a mensagem continua salva no painel
$enviado = false;
try {
    $user = env('SMTP_USER', ''); $pass = env('SMTP_PASS', ''); $to = env('MAIL_TO', $user);
    if ($user && $pass && $to) {
        (new SmtpMailer(env('SMTP_HOST', 'smtp.gmail.com'), (int)env('SMTP_PORT', '587'), $user, $pass))
            ->send($to, 'Portfólio: ' . ($assunto ?: 'nova mensagem de ' . $nome),
                   "Nome: $nome\nE-mail: $email\nAssunto: " . ($assunto ?: '-') . "\n\n$mensagem", $email, $nome);
        $enviado = true;
    }
} catch (Throwable $ex) { error_log('contato/smtp: ' . $ex->getMessage()); }

resp(200, true, $enviado ? 'Mensagem enviada. Obrigado pelo contato!'
                         : 'Mensagem recebida e salva. Responderei assim que possível.');
