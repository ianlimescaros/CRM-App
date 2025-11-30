<?php

require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../services/Response.php';
require_once __DIR__ . '/../services/Validator.php';
require_once __DIR__ . '/../models/Contact.php';
require_once __DIR__ . '/../models/Lead.php';
require_once __DIR__ . '/../models/Deal.php';
require_once __DIR__ . '/../models/Task.php';
require_once __DIR__ . '/../models/ContactNote.php';
require_once __DIR__ . '/../models/ContactFile.php';
require_once __DIR__ . '/../models/ContactActivity.php';

class ContactController
{
    public function index(): void
    {
        $user = AuthMiddleware::require();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = min(50, max(5, (int)($_GET['per_page'] ?? 20)));
        $orderBy = $_GET['sort'] ?? 'created_at';
        $orderDir = $_GET['direction'] ?? 'DESC';
        $filters = [];
        if (!empty($_GET['search'])) {
            $filters['search'] = $_GET['search'];
        }
        $pagination = [
            'limit' => $perPage,
            'offset' => ($page - 1) * $perPage,
            'order_by' => $orderBy,
            'order_dir' => $orderDir,
        ];
        $total = Contact::countAll((int)$user['id'], $filters);
        $contacts = Contact::all((int)$user['id'], $pagination, $filters);
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

        $timeline = ContactActivity::listForContact((int)$user['id'], $id);
        Response::success(['timeline' => $timeline]);
    }

    public function files(int $id): void
    {
        $user = AuthMiddleware::require();
        $contact = Contact::find((int)$user['id'], $id);
        if (!$contact) {
            Response::error('Contact not found', 404);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Multipart upload (preferred for PDFs/Word)
            if (!empty($_FILES['file']) && isset($_FILES['file']['error']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../storage/uploads/contact_' . $id;
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0775, true);
                }
                $original = $_FILES['file']['name'];
                $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', $original);
                $targetPath = $uploadDir . '/' . $safeName;
                if (!move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
                    Response::error('Failed to save file', 500);
                }
                $relUrl = '/storage/uploads/contact_' . $id . '/' . $safeName;
                $sizeLabel = $this->formatSize(filesize($targetPath));
                $created = ContactFile::create((int)$user['id'], $id, $original, $relUrl, $sizeLabel);
                ContactActivity::create((int)$user['id'], $id, 'note', 'File added: ' . $original);
                Response::success(['file' => $created], 201);
            }

            // JSON metadata-only
            $input = $this->getJsonInput();
            $name = trim($input['name'] ?? '');
            if ($name === '') {
                Response::error('Validation failed', 422, ['name' => 'File name is required.']);
            }
            $created = ContactFile::create(
                (int)$user['id'],
                $id,
                $name,
                $input['url'] ?? null,
                $input['size_label'] ?? null
            );
            ContactActivity::create((int)$user['id'], $id, 'note', 'File added: ' . $name);
            Response::success(['file' => $created], 201);
        }

        $files = ContactFile::listForContact((int)$user['id'], $id);
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
            $created = ContactNote::create((int)$user['id'], $id, $note);
            ContactActivity::create((int)$user['id'], $id, 'note', $note);
            Response::success(['note' => $created], 201);
        }

        $notes = ContactNote::listForContact((int)$user['id'], $id);
        Response::success(['notes' => $notes]);
    }

    public function addTask(int $id): void
    {
        $user = AuthMiddleware::require();
        $contact = Contact::find((int)$user['id'], $id);
        if (!$contact) {
            Response::error('Contact not found', 404);
        }
        $input = $this->getJsonInput();
        $errors = Validator::required($input, ['title']);
        if ($errors) {
            Response::error('Validation failed', 422, $errors);
        }
        $taskId = Task::create((int)$user['id'], [
            'title' => $input['title'],
            'description' => $input['description'] ?? null,
            'due_date' => $input['due_date'] ?? null,
            'status' => $input['status'] ?? 'pending',
            'contact_id' => $id,
        ]);
        $task = Task::find((int)$user['id'], $taskId);
        ContactActivity::create((int)$user['id'], $id, 'task', 'Task created: ' . $task['title']);
        Response::success(['task' => $task], 201);
    }

    public function addDeal(int $id): void
    {
        $user = AuthMiddleware::require();
        $contact = Contact::find((int)$user['id'], $id);
        if (!$contact) {
            Response::error('Contact not found', 404);
        }
        $input = $this->getJsonInput();
        $errors = Validator::required($input, ['title', 'stage']);
        if ($errors) {
            Response::error('Validation failed', 422, $errors);
        }
        $dealId = Deal::create((int)$user['id'], [
            'title' => $input['title'],
            'stage' => $input['stage'],
            'amount' => $input['amount'] ?? 0,
            'close_date' => $input['close_date'] ?? null,
            'contact_id' => $id,
            'lead_id' => $input['lead_id'] ?? null,
        ]);
        $deal = Deal::find((int)$user['id'], $dealId);
        ContactActivity::create((int)$user['id'], $id, 'note', 'Deal created: ' . $deal['title']);
        Response::success(['deal' => $deal], 201);
    }

    private function getJsonInput(): array
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    private function formatSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 1) . $units[$i];
    }
}
