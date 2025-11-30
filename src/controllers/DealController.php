<?php

require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../services/Response.php';
require_once __DIR__ . '/../services/Validator.php';
require_once __DIR__ . '/../models/Deal.php';
require_once __DIR__ . '/../models/Lead.php';
require_once __DIR__ . '/../models/Contact.php';

class DealController
{
    private array $stages = ['prospecting', 'proposal', 'negotiation', 'closed_won', 'closed_lost'];

    public function index(): void
    {
        $user = AuthMiddleware::require();
        $filters = ['stage' => $_GET['stage'] ?? null];
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
        $total = Deal::countAll((int)$user['id'], $filters);
        $deals = Deal::all((int)$user['id'], $filters, $pagination);
        Response::success([
            'deals' => $deals,
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

        $errors = Validator::required($input, ['title']);
        $stage = $input['stage'] ?? 'prospecting';
        $errors = array_merge($errors, Validator::inEnum($stage, $this->stages, 'stage'));
        if ($errors) {
            Response::error('Validation failed', 422, $errors);
        }

        $this->assertLinkOwnership($user, $input);

        $id = Deal::create((int)$user['id'], array_merge($input, ['stage' => $stage]));
        $deal = Deal::find((int)$user['id'], $id);
        Response::success(['deal' => $deal], 201);
    }

    public function update(int $id): void
    {
        $user = AuthMiddleware::require();
        $existing = Deal::find((int)$user['id'], $id);
        if (!$existing) {
            Response::error('Deal not found', 404);
        }

        $input = $this->getJsonInput();
        $payload = array_merge($existing, $input);
        $stage = $payload['stage'] ?? 'prospecting';
        $errors = Validator::inEnum($stage, $this->stages, 'stage');
        $errors = array_merge($errors, Validator::required($payload, ['title']));
        if ($errors) {
            Response::error('Validation failed', 422, $errors);
        }

        $this->assertLinkOwnership($user, $payload);

        $payload['stage'] = $stage;
        Deal::updateDeal((int)$user['id'], $id, $payload);
        $deal = Deal::find((int)$user['id'], $id);
        Response::success(['deal' => $deal]);
    }

    public function destroy(int $id): void
    {
        $user = AuthMiddleware::require();
        $deleted = Deal::deleteDeal((int)$user['id'], $id);
        if (!$deleted) {
            Response::error('Deal not found', 404);
        }
        Response::success(['message' => 'Deal deleted']);
    }

    private function assertLinkOwnership(array $user, array $data): void
    {
        if (!empty($data['lead_id'])) {
            $lead = Lead::find((int)$user['id'], (int)$data['lead_id']);
            if (!$lead) {
                Response::error('Invalid lead_id', 422, ['lead_id' => 'Lead not found or not owned.']);
            }
        }
        if (!empty($data['contact_id'])) {
            $contact = Contact::find((int)$user['id'], (int)$data['contact_id']);
            if (!$contact) {
                Response::error('Invalid contact_id', 422, ['contact_id' => 'Contact not found or not owned.']);
            }
        }
    }

    private function getJsonInput(): array
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }
}
