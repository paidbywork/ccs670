<?php

namespace App\Services;

use App\Core\DB;

/**
 * Whitelisted server-side list queries with bounded pagination.
 * Filter values are bound parameters; sort identifiers are never read as SQL.
 */
final class ListQuery
{
    public static function request(array $allowedSorts, string $defaultSort = 'newest'): array
    {
        $search = is_string($_GET['q'] ?? null) ? trim($_GET['q']) : '';
        $search = substr($search, 0, 100);
        $sort = is_string($_GET['sort'] ?? null) ? $_GET['sort'] : $defaultSort;

        if (!array_key_exists($sort, $allowedSorts)) {
            $sort = $defaultSort;
        }

        return [
            'q' => $search,
            'sort' => $sort,
            'page' => max(1, min(100000, (int) (is_scalar($_GET['page'] ?? null) ? $_GET['page'] : 1))),
            'perPage' => 10,
        ];
    }

    public static function fetch(
        string $fromWhere,
        array $params,
        string $select,
        string $orderBy,
        array $filters
    ): array {
        $count = DB::one('SELECT COUNT(*) AS total ' . $fromWhere, $params);
        $total = (int) ($count['total'] ?? 0);
        $pages = max(1, (int) ceil($total / $filters['perPage']));
        $page = min($filters['page'], $pages);
        $offset = ($page - 1) * $filters['perPage'];

        // LIMIT is cast to int, while all filter values remain bound.
        $sql = 'SELECT ' . $select . ' ' . $fromWhere
            . ' ORDER BY ' . $orderBy
            . ' LIMIT ' . (int) $filters['perPage']
            . ' OFFSET ' . (int) $offset;

        return [
            'items' => DB::all($sql, $params),
            'total' => $total,
            'page' => $page,
            'pages' => $pages,
            'perPage' => $filters['perPage'],
        ];
    }
}
