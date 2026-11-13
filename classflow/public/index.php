<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap/app.php';

use App\Core\Security;

try {
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $routes = require dirname(__DIR__) . '/routes/web.php';

    foreach ($routes as [$verb, $pattern, $controller, $action]) {
        if ($verb !== $method || !preg_match($pattern, $path, $matches)) {
            continue;
        }

        $identifier = isset($matches[1]) ? (int) $matches[1] : null;
        $instance = new $controller();

        if ($identifier === null) {
            $instance->$action();
        } else {
            $instance->$action($identifier);
        }

        exit;
    }

    Security::abort($method === 'GET' ? 404 : 405);
} catch (Throwable $exception) {
    error_log((string) $exception);
    http_response_code(500);

    if (filter_var($_ENV['APP_DEBUG'] ?? 'false', FILTER_VALIDATE_BOOLEAN)) {
        echo '<pre>' . e($exception->getMessage()) . '</pre>';
    } else {
        echo 'An unexpected error occurred. See server log.';
    }
}
