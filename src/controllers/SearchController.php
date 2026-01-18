<?php
// Controller for global search endpoints.

require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../services/Response.php';
require_once __DIR__ . '/../models/Client.php';
require_once __DIR__ . '/../models/Lead.php';

class SearchController
{
    public function search(): void
    {
        $user = AuthMiddleware::require();
        $q = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
        if ($q === '') {
            Response::success(['clients' => [], 'leads' => []]);
            return;
        }
        $limit = 6;
        $clients = Client::all(
            (int)$user['id'],
            ['limit' => $limit, 'offset' => 0, 'order_by' => 'full_name', 'order_dir' => 'ASC'],
            ['search' => $q]
        );
        $leads = Lead::all(
            (int)$user['id'],
            ['search' => $q],
            ['limit' => $limit, 'offset' => 0, 'order_by' => 'created_at', 'order_dir' => 'DESC']
        );
        Response::success(['clients' => $clients, 'leads' => $leads]);
    }
}
