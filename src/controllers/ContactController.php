<?php

require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../services/Response.php';
require_once __DIR__ . '/../services/Validator.php';
require_once __DIR__ . '/../models/Contact.php';

class ContactController
{
    public function index(): void
    {
        $user = AuthMiddleware::require();
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
        $total = Contact::countAll((int)$user['id']);
        $contacts = Contact::all((int)$user['id'], $pagination);
        Response::success([
            'contacts' => $contacts,
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

        $errors = Validator::required($input, ['full_name']);
        if (!empty($input['email'])) {
            $errors = array_merge($errors, Validator::email($input['email']));
        }
        if ($errors) {
            Response::error('Validation failed', 422, $errors);
        }

        if (!empty($input['email']) && Contact::findByEmail((int)$user['id'], $input['email'])) {
            Response::error('Validation failed', 422, ['email' => 'Email already exists.']);
        }

        $id = Contact::create((int)$user['id'], $input);
        $contact = Contact::find((int)$user['id'], $id);
        Response::success(['contact' => $contact], 201);
    }

    public function update(int $id): void
    {
        $user = AuthMiddleware::require();
        $existing = Contact::find((int)$user['id'], $id);
        if (!$existing) {
            Response::error('Contact not found', 404);
        }

        $input = $this->getJsonInput();
        $errors = Validator::required(array_merge($existing, $input), ['full_name']);
        if (!empty($input['email'])) {
            $errors = array_merge($errors, Validator::email($input['email']));
        }
        if ($errors) {
            Response::error('Validation failed', 422, $errors);
        }

        if (!empty($input['email']) && $input['email'] !== ($existing['email'] ?? null)) {
            $dupe = Contact::findByEmail((int)$user['id'], $input['email']);
            if ($dupe && (int)$dupe['id'] !== (int)$id) {
                Response::error('Validation failed', 422, ['email' => 'Email already exists.']);
            }
        }

        $payload = array_merge($existing, $input);
        Contact::updateContact((int)$user['id'], $id, $payload);
        $contact = Contact::find((int)$user['id'], $id);
        Response::success(['contact' => $contact]);
    }

    public function destroy(int $id): void
    {
        $user = AuthMiddleware::require();
        $deleted = Contact::deleteContact((int)$user['id'], $id);
        if (!$deleted) {
            Response::error('Contact not found', 404);
        }
        Response::success(['message' => 'Contact deleted']);
    }

    private function getJsonInput(): array
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }
}
