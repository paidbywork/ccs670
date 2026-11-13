<?php

namespace App\Controllers;

use App\Core\Security;
use PDOException;
use RuntimeException;

abstract class BaseController
{
    protected function requirePost(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            Security::abort(405);
        }

        Security::verify();
    }

    protected function required(string $key, int $limit = 191): string
    {
        $value = \input($key, $limit);

        if ($value === '') {
            throw new RuntimeException(ucfirst($key) . ' is required.');
        }

        return $value;
    }

    protected function id(string $key): int
    {
        $id = filter_var($_POST[$key] ?? null, FILTER_VALIDATE_INT);

        if (!$id || $id < 1) {
            throw new RuntimeException('Invalid ' . $key);
        }

        return (int) $id;
    }

    protected function execute(callable $operation, string $fallback): void
    {
        try {
            $operation();
        } catch (PDOException $exception) {
            error_log($exception->getMessage());
            Security::redirect($fallback, 'Database operation failed. Check for duplicates or referenced records.');
        } catch (RuntimeException $exception) {
            Security::redirect($fallback, $exception->getMessage());
        }
    }
}
