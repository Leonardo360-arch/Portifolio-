<?php
declare(strict_types=1);

/** Parser nativo de .env (sem dependências). */
function env_load(string $path): void
{
    static $loaded = false;
    if ($loaded || !is_readable($path)) { return; }
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) { continue; }
        [$k, $v] = explode('=', $line, 2);
        $_ENV[trim($k)] = trim($v, " \t\"'");
    }
    $loaded = true;
}

function env(string $key, ?string $default = null): ?string
{
    env_load(dirname(__DIR__) . '/.env');
    return $_ENV[$key] ?? $default;
}
