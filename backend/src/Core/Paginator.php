<?php
namespace App\Core;

class Paginator
{
    public static function parse(array $query, array $options = []): array
    {
        $page = max(1, (int)($query['page'] ?? 1));
        $perPage = min(max(1, (int)($query['per_page'] ?? 20)), (int)($options['max_per_page'] ?? 100));
        $offset = ($page - 1) * $perPage;
        $sortBy = $query['sort_by'] ?? $options['default_sort_by'] ?? 'id';
        $sortDir = strtolower($query['sort_dir'] ?? $options['default_sort_dir'] ?? 'desc');
        $sortDir = in_array($sortDir, ['asc', 'desc'], true) ? $sortDir : 'desc';

        return [
            'page' => $page,
            'per_page' => $perPage,
            'offset' => $offset,
            'sort_by' => $sortBy,
            'sort_dir' => $sortDir,
        ];
    }

    public static function meta(int $page, int $perPage, int $total): array
    {
        $totalPages = (int)ceil($total / max(1, $perPage));
        return [
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'total_pages' => $totalPages,
        ];
    }
}
