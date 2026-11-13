<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';

if (is_file($autoload)) {
    require $autoload;
} else {
    // Fallback for local environments without Composer's generated autoloader.
    spl_autoload_register(function (string $class) use ($root): void {
        if (!str_starts_with($class, 'App\\')) {
            return;
        }

        $relativePath = str_replace('\\', '/', substr($class, 4));
        $file = $root . '/app/' . $relativePath . '.php';

        if (is_file($file)) {
            require $file;
        }
    });
}

$environmentFile = $root . '/.env';

if (is_file($environmentFile)) {
    foreach (file($environmentFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim(trim($value), "\"'");
    }
}

ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');

if (filter_var($_ENV['SESSION_SECURE'] ?? 'false', FILTER_VALIDATE_BOOLEAN)) {
    ini_set('session.cookie_secure', '1');
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = '/'): string
{
    return $path;
}

function csrf(): string
{
    return \App\Core\Security::token();
}

function input(string $key, int $limit = 10000): string
{
    $value = trim((string) ($_POST[$key] ?? ''));
    return function_exists('mb_substr')
        ? mb_substr($value, 0, $limit)
        : substr($value, 0, $limit);
}
