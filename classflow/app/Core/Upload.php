<?php

namespace App\Core;

use finfo;
use RuntimeException;

final class Upload
{
    private const TYPES = [
        'application/pdf' => 'pdf',
        'image/png' => 'png',
        'image/jpeg' => 'jpg',
        'text/plain' => 'txt',
        'application/zip' => 'zip',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
    ];

    public static function save(string $field): array
    {
        $file = $_FILES[$field] ?? null;

        if (!$file || $file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
            throw new RuntimeException('Select a valid file.');
        }

        $maximumMb = max(1, (int) ($_ENV['UPLOAD_MAX_MB'] ?? 10));
        $maximumBytes = $maximumMb * 1024 * 1024;

        if ($file['size'] <= 0 || $file['size'] > $maximumBytes) {
            throw new RuntimeException('Invalid file size; maximum ' . $maximumMb . ' MB.');
        }

        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);

        if (!isset(self::TYPES[$mime])) {
            throw new RuntimeException('Unsupported file type (PDF, PNG, JPG, TXT, ZIP, DOCX).');
        }

        $stored = bin2hex(random_bytes(20)) . '.' . self::TYPES[$mime];
        $directory = dirname(__DIR__, 2) . '/storage/uploads';

        if (!is_dir($directory) && !mkdir($directory, 0700, true)) {
            throw new RuntimeException('Upload directory unavailable.');
        }

        if (!move_uploaded_file($file['tmp_name'], $directory . '/' . $stored)) {
            throw new RuntimeException('Unable to save file.');
        }

        return [
            'path' => $stored,
            'name' => mb_substr(basename(str_replace('\\', '/', $file['name'])), 0, 255),
        ];
    }

    public static function send(string $file, string $name): never
    {
        if (!preg_match('/^[a-f0-9]{40}\.(pdf|png|jpg|txt|zip|docx)$/', $file)) {
            Security::abort();
        }

        $path = dirname(__DIR__, 2) . '/storage/uploads/' . $file;

        if (!is_file($path)) {
            Security::abort();
        }

        $safeName = str_replace(['"', "\r", "\n"], '', basename($name));
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $safeName . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }
}
