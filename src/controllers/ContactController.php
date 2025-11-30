<?php

require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../services/Response.php';
require_once __DIR__ . '/../services/Validator.php';
require_once __DIR__ . '/../models/Contact.php';
require_once __DIR__ . '/../models/Lead.php';
require_once __DIR__ . '/../models/Deal.php';
require_once __DIR__ . '/../models/Task.php';

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

    public function show(int $id): void
    {
        $user = AuthMiddleware::require();
        $contact = Contact::find((int)$user['id'], $id);
        if (!$contact) {
            Response::error('Contact not found', 404);
        }

        $dealsCount = Deal::countAll((int)$user['id'], ['contact_id' => $id]);
        $tasksCount = Task::countAll((int)$user['id'], ['contact_id' => $id]);
        $leadCount = Lead::countAll((int)$user['id'], []);

        Response::success([
            'contact' => $contact,
            'stats' => [
                'deals' => $dealsCount,
                'tasks' => $tasksCount,
                'leads' => $leadCount,
            ],
        ]);
    }

    public function timeline(int $id): void
    {
        $user = AuthMiddleware::require();
        $contact = Contact::find((int)$user['id'], $id);
        if (!$contact) {
            Response::error('Contact not found', 404);
        }

        $timeline = [
            ['at' => date('Y-m-d'), 'type' => 'call', 'detail' => 'Discussed next steps and budget alignment'],
            ['at' => date('Y-m-d', strtotime('-2 days')), 'type' => 'email', 'detail' => 'Shared property shortlist and brochure'],
            ['at' => date('Y-m-d', strtotime('-5 days')), 'type' => 'meeting', 'detail' => 'Initial consultation completed'],
        ];

        Response::success(['timeline' => $timeline]);
    }

    public function files(int $id): void
    {
        $user = AuthMiddleware::require();
        $contact = Contact::find((int)$user['id'], $id);
        if (!$contact) {
            Response::error('Contact not found', 404);
        }

        $files = [
            ['name' => 'Brochure.pdf', 'size' => '1.2MB', 'updated_at' => date('Y-m-d', strtotime('-1 day'))],
            ['name' => 'Floorplan.png', 'size' => '820KB', 'updated_at' => date('Y-m-d', strtotime('-3 days'))],
        ];

        Response::success(['files' => $files]);
    }

    public function notes(int $id): void
    {
        $user = AuthMiddleware::require();
        $contact = Contact::find((int)$user['id'], $id);
        if (!$contact) {
            Response::error('Contact not found', 404);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $this->getJsonInput();
            $note = trim($input['content'] ?? '');
            if ($note === '') {
                Response::error('Validation failed', 422, ['content' => 'Note content is required.']);
            }
            $newNote = [
                'content' => $note,
                'created_at' => date('Y-m-d H:i:s'),
            ];
            Response::success(['note' => $newNote], 201);
        }

        $notes = [
            ['content' => 'Prefers email; interested in 3-bed units.', 'created_at' => date('Y-m-d', strtotime('-2 days'))],
            ['content' => 'Budget flexible if location is prime.', 'created_at' => date('Y-m-d', strtotime('-5 days'))],
        ];
        Response::success(['notes' => $notes]);
    }

    private function getJsonInput(): array
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }
}
