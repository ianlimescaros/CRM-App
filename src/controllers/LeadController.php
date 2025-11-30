<?php

require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../services/Response.php';
require_once __DIR__ . '/../services/Validator.php';
require_once __DIR__ . '/../models/Lead.php';

class LeadController
{
    private array $leadStatuses = ['new', 'contacted', 'qualified', 'lost', 'won'];

    public function index(): void
    {
        $user = AuthMiddleware::require();
        $filters = [
            'status' => $_GET['status'] ?? null,
            'source' => $_GET['source'] ?? null,
        ];
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = min(50, max(5, (int)($_GET['per_page'] ?? 20)));
        $orderBy = $_GET['sort'] ?? 'created_at';
        $orderDir = $_GET['direction'] ?? 'DESC';
        $pagination = [
            'limit' => $perPage,
            'offset' => ($page - 1) * $perPage,
            'order_by' => $orderBy,
            'order_dir' => $orderDir,
        ];
        $total = Lead::countAll((int)$user['id'], $filters);
        $leads = Lead::all((int)$user['id'], $filters, $pagination);
        Response::success([
            'leads' => $leads,
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
            ],
        ]);
    }

    public function store(): void
    {
        $user = AuthMiddleware::require();
        $input = $this->getJsonInput();

        $errors = Validator::required($input, ['name']);
        if (!empty($input['email'])) {
            $errors = array_merge($errors, Validator::email($input['email']));
        }
        if (!empty($input['status'])) {
            $errors = array_merge($errors, Validator::inEnum($input['status'], $this->leadStatuses, 'status'));
        }
        if (!empty($input['owner_id'])) {
            $errors = array_merge($errors, Validator::numeric($input['owner_id'], 'owner_id'));
        }
        if (!empty($input['last_contact_at'])) {
            $errors = array_merge($errors, Validator::dateYmd(substr($input['last_contact_at'], 0, 10), 'last_contact_at'));
        }

        if ($errors) {
            Response::error('Validation failed', 422, $errors);
        }

        $id = Lead::create((int)$user['id'], $input);
        $lead = Lead::find((int)$user['id'], $id);
        Response::success(['lead' => $lead], 201);
    }

    public function update(int $id): void
    {
        $user = AuthMiddleware::require();
        $existing = Lead::find((int)$user['id'], $id);
        if (!$existing) {
            Response::error('Lead not found', 404);
        }

        $input = $this->getJsonInput();
        $errors = Validator::required($input, ['name']);
        if (!empty($input['email'])) {
            $errors = array_merge($errors, Validator::email($input['email']));
        }
        $status = $input['status'] ?? $existing['status'];
        $errors = array_merge($errors, Validator::inEnum($status, $this->leadStatuses, 'status'));
        if (!empty($input['owner_id'])) {
            $errors = array_merge($errors, Validator::numeric($input['owner_id'], 'owner_id'));
        }
        if (!empty($input['last_contact_at'])) {
            $errors = array_merge($errors, Validator::dateYmd(substr($input['last_contact_at'], 0, 10), 'last_contact_at'));
        }

        if ($errors) {
            Response::error('Validation failed', 422, $errors);
        }

        $payload = array_merge($existing, $input, ['status' => $status]);
        Lead::updateLead((int)$user['id'], $id, $payload);
        $lead = Lead::find((int)$user['id'], $id);
        Response::success(['lead' => $lead]);
    }

    public function bulkUpdate(): void
    {
        $user = AuthMiddleware::require();
        $input = $this->getJsonInput();
        $errors = Validator::required($input, ['ids', 'status']);
        $errors = array_merge($errors, Validator::inEnum($input['status'] ?? '', $this->leadStatuses, 'status'));
        if ($errors) {
            Response::error('Validation failed', 422, $errors);
        }
        $ids = is_array($input['ids']) ? array_map('intval', $input['ids']) : [];
        $updated = Lead::bulkUpdateStatus((int)$user['id'], $ids, $input['status']);
        Response::success(['updated' => $updated]);
    }

    public function destroy(int $id): void
    {
        $user = AuthMiddleware::require();
        $deleted = Lead::deleteLead((int)$user['id'], $id);
        if (!$deleted) {
            Response::error('Lead not found', 404);
        }
        Response::success(['message' => 'Lead deleted']);
    }

    private function getJsonInput(): array
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }
}
