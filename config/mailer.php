<?php
declare(strict_types=1);

/** Cliente SMTP mínimo: STARTTLS (porta 587) + AUTH LOGIN. Sem dependências. */
final class SmtpMailer
{
    /** @var resource|null */
    private $fp = null;

    public function __construct(private string $host, private int $port, private string $user, private string $pass, private bool $tls = true) {}

    private function read(): array
    {
        $code = 0; $text = '';
        while (($l = fgets($this->fp, 515)) !== false) {
            $text .= $l; $code = (int)substr($l, 0, 3);
            if (!isset($l[3]) || $l[3] === ' ') { break; }
        }
        return [$code, $text];
    }

    private function cmd(string $c, array $ok): void
    {
        fwrite($this->fp, $c . "\r\n");
        [$code, $t] = $this->read();
        if (!in_array($code, $ok, true)) { throw new RuntimeException('SMTP recusou o comando (' . $code . ')'); }
    }

    private static function clean(string $s): string { return trim(str_replace(["\r", "\n", '<', '>'], ' ', $s)); }

    public function send(string $to, string $subject, string $body, string $replyTo, string $replyName): void
    {
        $this->fp = @stream_socket_client("tcp://{$this->host}:{$this->port}", $en, $es, 10);
        if (!$this->fp) { throw new RuntimeException('Não foi possível conectar ao servidor SMTP.'); }
        stream_set_timeout($this->fp, 10);
        try {
            [$code] = $this->read();
            if ($code !== 220) { throw new RuntimeException('Servidor SMTP não respondeu corretamente.'); }
            $me = 'localhost';
            $this->cmd("EHLO $me", [250]);
            if ($this->tls) {
                $this->cmd('STARTTLS', [220]);
                if (!stream_socket_enable_crypto($this->fp, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT)) {
                    throw new RuntimeException('Falha ao iniciar TLS.');
                }
                $this->cmd("EHLO $me", [250]);
            }
            $this->cmd('AUTH LOGIN', [334]);
            $this->cmd(base64_encode($this->user), [334]);
            $this->cmd(base64_encode($this->pass), [235]);
            $this->cmd('MAIL FROM:<' . self::clean($this->user) . '>', [250]);
            $this->cmd('RCPT TO:<' . self::clean($to) . '>', [250, 251]);
            $this->cmd('DATA', [354]);

            $headers = [
                'Date: ' . date('r'),
                'From: =?UTF-8?B?' . base64_encode('Portfólio LMC') . '?= <' . self::clean($this->user) . '>',
                'To: <' . self::clean($to) . '>',
                'Reply-To: =?UTF-8?B?' . base64_encode(self::clean($replyName)) . '?= <' . self::clean($replyTo) . '>',
                'Subject: =?UTF-8?B?' . base64_encode(self::clean($subject)) . '?=',
                'MIME-Version: 1.0',
                'Content-Type: text/plain; charset=UTF-8',
                'Content-Transfer-Encoding: base64',
            ];
            // base64 evita problemas de "dot-stuffing" e quebras de linha no corpo
            $msg = implode("\r\n", $headers) . "\r\n\r\n" . chunk_split(base64_encode($body), 76, "\r\n");
            fwrite($this->fp, $msg . "\r\n.\r\n");
            [$code] = $this->read();
            if ($code !== 250) { throw new RuntimeException('SMTP não aceitou a mensagem (' . $code . ').'); }
            @fwrite($this->fp, "QUIT\r\n");
        } finally {
            if (is_resource($this->fp)) { fclose($this->fp); }
        }
    }
}
