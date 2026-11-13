<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap/app.php';

use App\Services\ListQuery;

$routes = require dirname(__DIR__) . '/routes/web.php';
$expected = [
    '/admin/accounts', '/admin/courses', '/admin/sections',
    '/teacher/sections', '/teacher/assignments',
    '/student/sections', '/student/assignments', '/student/join',
];

foreach ($expected as $path) {
    $matched = false;
    foreach ($routes as [$verb, $pattern]) {
        if ($verb === 'GET' && preg_match($pattern, $path)) {
            $matched = true;
            break;
        }
    }
    assert($matched, 'Missing route: ' . $path);
    if (!$matched) {
        throw new RuntimeException('Missing route: ' . $path);
    }
}

$_GET = ['q' => str_repeat('x', 120), 'sort' => 'DROP TABLE users', 'page' => '-12'];
$filters = ListQuery::request(['newest' => 'id DESC']);
if ($filters['sort'] !== 'newest' || $filters['page'] !== 1 || strlen($filters['q']) !== 100) {
    throw new RuntimeException('Invalid list filter normalization.');
}

$_GET = ['q' => [], 'sort' => [], 'page' => []];
$filters = ListQuery::request(['newest' => 'id DESC']);
if ($filters['q'] !== '' || $filters['sort'] !== 'newest' || $filters['page'] !== 1) {
    throw new RuntimeException('Invalid malformed query handling.');
}

print "PASS: v1.3 routes and filter validation.\n";
