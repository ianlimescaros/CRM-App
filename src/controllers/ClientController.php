<?php

require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../services/Response.php';
require_once __DIR__ . '/../services/Validator.php';
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../models/Client.php';
require_once __DIR__ . '/../models/Lead.php';
require_once __DIR__ . '/../models/Deal.php';
require_once __DIR__ . '/../models/Task.php';
require_once __DIR__ . '/../models/ClientNote.php';
require_once __DIR__ . '/../models/ClientFile.php';
require_once __DIR__ . '/../models/ClientActivity.php';
require_once __DIR__ . '/BaseController.php';

class ClientController extends BaseController
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
        $total = Client::countAll((int)$user['id'], $filters);
        $clients = Client::all((int)$user['id'], $pagination, $filters);
        Response::success([
            'clients' => $clients,
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

        if (!empty($input['email']) && Client::findByEmail((int)$user['id'], $input['email'])) {
            Response::error('Validation failed', 422, ['email' => 'Email already exists.']);
        }

        $id = Client::create((int)$user['id'], $input);
        $client = Client::find((int)$user['id'], $id);
        Response::success(['client' => $client], 201);
    }

    public function update(int $id): void
    {
        $user = AuthMiddleware::require();
        $existing = Client::find((int)$user['id'], $id);
        if (!$existing) {
            Response::error('Client not found', 404);
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
            $dupe = Client::findByEmail((int)$user['id'], $input['email']);
            if ($dupe && (int)$dupe['id'] !== (int)$id) {
                Response::error('Validation failed', 422, ['email' => 'Email already exists.']);
            }
        }

        $payload = array_merge($existing, $input);
        Client::updateClient((int)$user['id'], $id, $payload);
        $client = Client::find((int)$user['id'], $id);
        Response::success(['client' => $client]);
    }

    public function destroy(int $id): void
    {
        $user = AuthMiddleware::require();
        $deleted = Client::deleteClient((int)$user['id'], $id);
        if (!$deleted) {
            Response::error('Client not found', 404);
        }
        Response::success(['message' => 'Client deleted']);
    }

    public function show(int $id): void
    {
        $user = AuthMiddleware::require();
        $client = Client::find((int)$user['id'], $id);
        if (!$client) {
            Response::error('Client not found', 404);
        }

        $dealsCount = Deal::countAll((int)$user['id'], ['client_id' => $id]);
        $tasksCount = Task::countAll((int)$user['id'], ['client_id' => $id]);
        // Leads are not linked to clients in the current schema; report 0 instead of total user leads to avoid misrepresentation.
        $leadCount = 0;

        Response::success([
            'client' => $client,
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
        $client = Client::find((int)$user['id'], $id);
        if (!$client) {
            Response::error('Client not found', 404);
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = min(50, max(5, (int)($_GET['per_page'] ?? 10)));
        $offset = ($page - 1) * $perPage;

        $total = ClientActivity::countForClient((int)$user['id'], $id);
        $timeline = ClientActivity::listForClientPaginated((int)$user['id'], $id, $perPage, $offset);
        $totalPages = (int)ceil($total / max(1, $perPage));

        Response::success([
            'timeline' => $timeline,
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages,
            ],
        ]);
    }

    public function files(int $id): void
    {
        // Allow token in query string for GET (for hosts stripping Authorization header)
        $user = null;
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
            $queryToken = isset($_GET['token']) ? trim((string)$_GET['token']) : '';
            if ($queryToken !== '') {
                $auth = new AuthService();
                $user = $auth->requireAuth($queryToken);
            }
        }
        if (!$user) {
            $user = AuthMiddleware::require();
        }
        $client = Client::find((int)$user['id'], $id);
        if (!$client) {
            Response::error('Client not found', 404);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $allowedExt = ['pdf', 'doc', 'docx', 'png', 'jpg', 'jpeg', 'txt'];
            $allowedMime = [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'image/png',
                'image/jpeg',
                'text/plain',
            ];
            $maxSize = 10 * 1024 * 1024; // 10 MB

            // Multipart upload (preferred for PDFs/Word)
            if (!empty($_FILES['file']) && isset($_FILES['file']['error']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['file'];
                $original = trim($file['name'] ?? 'upload');
                $ext = strtolower((string)pathinfo($original, PATHINFO_EXTENSION));
                if ($file['size'] > $maxSize) {
                    Response::error('Validation failed', 422, ['file' => 'File is too large (max 5MB).']);
                }
                if ($ext && !in_array($ext, $allowedExt, true)) {
                    Response::error('Validation failed', 422, ['file' => 'File type not allowed.']);
                }
                $tmpPath = $file['tmp_name'];
                $mime = null;
                if ($tmpPath && is_uploaded_file($tmpPath) && function_exists('finfo_open')) {
                    $finfo = @finfo_open(FILEINFO_MIME_TYPE);
                    if ($finfo) {
                        $mime = @finfo_file($finfo, $tmpPath);
                        finfo_close($finfo);
                    }
                }
                if ($mime && !in_array($mime, $allowedMime, true)) {
                    Response::error('Validation failed', 422, ['file' => 'File type not allowed.']);
                }

                $uploadDir = __DIR__ . '/../../storage/uploads/client_' . $id;
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0775, true);
                }
                try {
                    $randomName = bin2hex(random_bytes(16));
                } catch (Exception $e) {
                    $randomName = uniqid('file_', true);
                }
                $finalName = $ext ? $randomName . '.' . $ext : $randomName;
                $targetPath = $uploadDir . '/' . $finalName;
                if (!move_uploaded_file($tmpPath, $targetPath)) {
                    Response::error('Failed to save file', 500);
                }
                // Store on disk outside web root; do not expose a public URL directly.
                $relUrl = null;
                $sizeLabel = $this->formatSize((int)filesize($targetPath));
                $created = ClientFile::create((int)$user['id'], $id, $original, $relUrl, $sizeLabel, $targetPath);
                unset($created['disk_path']);
                ClientActivity::create((int)$user['id'], $id, 'note', 'File added: ' . $original);
                Response::success(['file' => $created], 201);
            }

            // JSON metadata-only
            $input = $this->getJsonInput();
            $name = trim($input['name'] ?? '');
            if ($name === '') {
                Response::error('Validation failed', 422, ['name' => 'File name is required.']);
            }
            $url = isset($input['url']) ? trim((string)$input['url']) : null;
            if ($url && !preg_match('#^https?://#i', $url)) {
                $url = null;
            }
            $sizeLabel = isset($input['size_label']) ? trim((string)$input['size_label']) : null;
            $created = ClientFile::create(
                (int)$user['id'],
                $id,
                $name,
                $url,
                $sizeLabel
            );
            unset($created['disk_path']);
            ClientActivity::create((int)$user['id'], $id, 'note', 'File added: ' . $name);
            Response::success(['file' => $created], 201);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            $input = $this->getJsonInput();
            $fileId = isset($input['file_id']) ? (int)$input['file_id'] : 0;
            if (!$fileId) {
                Response::error('Validation failed', 422, ['file_id' => 'file_id is required']);
            }
            $file = ClientFile::find((int)$user['id'], $id, $fileId);
            if (!$file) {
                Response::error('File not found', 404);
            }
            if (!empty($file['disk_path']) && file_exists($file['disk_path'])) {
                @unlink($file['disk_path']);
            }
            ClientFile::deleteFile((int)$user['id'], $id, $fileId);
            ClientActivity::create((int)$user['id'], $id, 'note', 'File deleted: ' . ($file['name'] ?? ''));
            Response::success(['message' => 'File deleted']);
        }

        $files = ClientFile::listForClient((int)$user['id'], $id);
        Response::success(['files' => $files]);
    }

    public function notes(int $id): void
    {
        $user = AuthMiddleware::require();
        $client = Client::find((int)$user['id'], $id);
        if (!$client) {
            Response::error('Client not found', 404);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $this->getJsonInput();
            $note = trim($input['content'] ?? '');
            if ($note === '') {
                Response::error('Validation failed', 422, ['content' => 'Note content is required.']);
            }
            $created = ClientNote::create((int)$user['id'], $id, $note);
            ClientActivity::create((int)$user['id'], $id, 'note', $note);
            Response::success(['note' => $created], 201);
        }

        $notes = ClientNote::listForClient((int)$user['id'], $id);
        Response::success(['notes' => $notes]);
    }

    public function addTask(int $id): void
    {
        $user = AuthMiddleware::require();
        $client = Client::find((int)$user['id'], $id);
        if (!$client) {
            Response::error('Client not found', 404);
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
            'client_id' => $id,
        ]);
        $task = Task::find((int)$user['id'], $taskId);
        ClientActivity::create((int)$user['id'], $id, 'task', 'Task created: ' . $task['title']);
        Response::success(['task' => $task], 201);
    }

    public function addDeal(int $id): void
    {
        $user = AuthMiddleware::require();
        $client = Client::find((int)$user['id'], $id);
        if (!$client) {
            Response::error('Client not found', 404);
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
            'client_id' => $id,
            'lead_id' => $input['lead_id'] ?? null,
        ]);
        $deal = Deal::find((int)$user['id'], $dealId);
        ClientActivity::create((int)$user['id'], $id, 'note', 'Deal created: ' . $deal['title']);
        Response::success(['deal' => $deal], 201);
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

    public function downloadFile(int $id, int $fileId): void
    {
        // Allow token via query (?token=...) for direct download links (no headers)
        $user = null;
        $queryToken = isset($_GET['token']) ? trim((string)$_GET['token']) : '';
        if ($queryToken !== '') {
            $auth = new AuthService();
            $user = $auth->requireAuth($queryToken);
        }
        if (!$user) {
            $user = AuthMiddleware::require();
        }
        $client = Client::find((int)$user['id'], $id);
        if (!$client) {
            Response::error('Client not found', 404);
        }
        $file = ClientFile::find((int)$user['id'], $id, $fileId);
        if (!$file || empty($file['disk_path']) || !is_file($file['disk_path'])) {
            Response::error('File not found', 404);
        }

        $path = $file['disk_path'];
        $filename = $file['name'] ?? basename($path);
        $mime = 'application/octet-stream';
        if (function_exists('mime_content_type')) {
            $mime = mime_content_type($path) ?: $mime;
        }

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($path));
        header('Content-Disposition: attachment; filename="' . addslashes($filename) . '"');
        readfile($path);
        exit;
    }
}
