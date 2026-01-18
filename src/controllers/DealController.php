<?php
// Controller for deals and related file handling.

require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../services/Response.php';
require_once __DIR__ . '/../services/Validator.php';
require_once __DIR__ . '/../models/Deal.php';
require_once __DIR__ . '/../models/Lead.php';
require_once __DIR__ . '/../models/Client.php';
require_once __DIR__ . '/../models/DealFile.php';
require_once __DIR__ . '/BaseController.php';

class DealController extends BaseController
{
    /** @var array<int|string, string> */
    private array $stages = ['ongoing', 'pending'];
    /** @var array<int|string, string> */
    private array $currencies = ['AED', 'USD'];

    public function index(): void
    {
        $user = AuthMiddleware::require();
        $filters = [
            'stage' => $_GET['stage'] ?? null,
            'client_id' => $_GET['client_id'] ?? ($_GET['contact_id'] ?? null),
            'lead_id' => $_GET['lead_id'] ?? null,
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
        $input = $this->normalizeClientLink($this->getJsonInput());

        $errors = (array) Validator::required($input, ['title']);
        $currency = strtoupper(trim((string)($input['currency'] ?? '')));
        $errors = array_merge($errors, (array)Validator::inEnum((string)$currency, $this->currencies, 'currency'));
        $stage = trim((string)($input['stage'] ?? '')) ?: 'ongoing';
        $errors = array_merge($errors, (array)Validator::inEnum((string)$stage, $this->stages, 'stage'));
        $amount = $this->normalizeAmount($input['amount'] ?? null);
        if ($amount === false) {
            $errors['amount'] = 'Amount must be a valid number';
        }
        if (!empty($input['location']) && strlen((string)$input['location']) > 255) {
            $errors['location'] = 'Location is too long';
        }
        if ($errors) {
            Response::error('Validation failed', 422, $errors);
        }

        $this->assertLinkOwnership($user, $input);

        $id = Deal::create((int)$user['id'], array_merge($input, [
            'stage' => $stage,
            'currency' => $currency,
            'amount' => $amount === false ? 0 : $amount,
            'location' => $input['location'] ?? null,
            'property_detail' => $input['property_detail'] ?? null,
        ]));
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

        $input = $this->normalizeClientLink($this->getJsonInput());
        $payload = array_merge((array)$existing, (array)$input);
        $stage = trim((string)($payload['stage'] ?? '')) ?: 'ongoing';
        $currency = strtoupper(trim($input['currency'] ?? ($existing['currency'] ?? '')));
        $errors = (array)Validator::inEnum($stage, $this->stages, 'stage');
        $errors = array_merge($errors, (array)Validator::inEnum($currency, $this->currencies, 'currency'));
        $errors = array_merge($errors, (array)Validator::required($payload, ['title']));
        $amount = $this->normalizeAmount($payload['amount'] ?? null);
        if ($amount === false) {
            $errors['amount'] = 'Amount must be a valid number';
        }
        if (!empty($payload['location']) && strlen((string)$payload['location']) > 255) {
            $errors['location'] = 'Location is too long';
        }
        if ($errors) {
            Response::error('Validation failed', 422, $errors);
        }

        $this->assertLinkOwnership($user, $payload);

        $payload['stage'] = $stage;
        $payload['currency'] = $currency;
        $payload['amount'] = $amount === false ? 0 : $amount;
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

    public function files(int $id): void
    {
        $user = AuthMiddleware::require();
        $deal = Deal::find((int)$user['id'], $id);
        if (!$deal) {
            Response::error('Deal not found', 404);
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

            // Multipart upload
            if (!empty($_FILES['file']) && isset($_FILES['file']['error']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['file'];
                $original = trim($file['name'] ?? 'upload');
                $ext = strtolower((string)pathinfo($original, PATHINFO_EXTENSION));
                if ($file['size'] > $maxSize) {
                    Response::error('Validation failed', 422, ['file' => 'File is too large (max 10MB).']);
                }
                if ($ext && !in_array($ext, $allowedExt, true)) {
                    Response::error('Validation failed', 422, ['file' => 'File type not allowed.']);
                }
                $tmpPath = $file['tmp_name'];
                $mime = null;
                if ($tmpPath && is_uploaded_file($tmpPath) && function_exists('finfo_open')) {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    if ($finfo) {
                        $m = finfo_file($finfo, $tmpPath);
                        if ($m !== false) {
                            $mime = $m;
                        }
                        finfo_close($finfo);
                    }
                }
                if ($mime && !in_array($mime, $allowedMime, true)) {
                    Response::error('Validation failed', 422, ['file' => 'File type not allowed.']);
                }

                $uploadDir = __DIR__ . '/../../storage/uploads/deal_' . $id;
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
                @chmod($targetPath, 0644);
                $relUrl = null;
                $sizeLabel = $this->formatSize((int)filesize($targetPath));
                $created = DealFile::create((int)$user['id'], $id, $original, $relUrl, $sizeLabel, $targetPath);
                unset($created['disk_path']);
                Response::success(['file' => $created], 201);
            }

            // JSON metadata-only
            $input = $this->getJsonInput();
            $name = trim($this->asString($input['name'] ?? ''));
            if ($name === '') {
                Response::error('Validation failed', 422, ['name' => 'File name is required.']);
            }
            $url = isset($input['url']) ? trim($this->asString($input['url'])) : null;
            if ($url && !preg_match('#^https?://#i', $url)) {
                $url = null;
            }
            $sizeLabel = isset($input['size_label']) ? trim($this->asString($input['size_label'])) : null;
            $created = DealFile::create((int)$user['id'], $id, $name, $url, $sizeLabel);
            unset($created['disk_path']);
            Response::success(['file' => $created], 201);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            $input = $this->getJsonInput();
            $fileId = isset($input['file_id']) ? (int)$input['file_id'] : 0;
            if (!$fileId) {
                Response::error('Validation failed', 422, ['file_id' => 'file_id is required']);
            }
            $file = DealFile::find((int)$user['id'], $id, $fileId);
            if (!$file) {
                Response::error('File not found', 404);
            }
            if (!empty($file['disk_path']) && file_exists($file['disk_path'])) {
                if (!unlink($file['disk_path'])) {
                    error_log('Failed to delete file: ' . $file['disk_path']);
                }
            }
            DealFile::deleteFile((int)$user['id'], $id, $fileId);
            Response::success(['message' => 'File deleted']);
        }

        $files = DealFile::listForDeal((int)$user['id'], $id);
        Response::success(['files' => $files]);
    }

    public function downloadFile(int $id, int $fileId): void
    {
        $user = AuthMiddleware::require();
        $deal = Deal::find((int)$user['id'], $id);
        if (!$deal) {
            Response::error('Deal not found', 404);
        }
        $file = DealFile::find((int)$user['id'], $id, $fileId);
        if (!$file || empty($file['disk_path']) || !is_file($file['disk_path'])) {
            Response::error('File not found', 404);
        }

        $path = (string)($file['disk_path'] ?? '');
        $filename = $file['name'] ?? basename($path);
        $mime = 'application/octet-stream';
        if (function_exists('mime_content_type')) {
            $mime = mime_content_type($path) ?: $mime;
        }

        // Defense-in-depth: ensure the on-disk path is inside the canonical uploads directory
        $uploadsDir = realpath(__DIR__ . '/../../storage/uploads');
        $real = realpath($path);
        if ($real === false || $uploadsDir === false || strpos($real, $uploadsDir) !== 0) {
            Response::error('File not found', 404);
        }
        if (!is_file($real) || !is_readable($real)) {
            Response::error('File not found', 404);
        }

        // Sanitize filename to avoid header injection and support UTF-8
        $safeName = basename($filename);
        $safeName = preg_replace('/[\r\n\"]+/', '_', $safeName);

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($real));
        header('Content-Disposition: attachment; filename="' . $safeName . '"; filename*=UTF-8\'\'' . rawurlencode((string)$safeName));
        readfile($real);
        exit;
    }

    /**
     * @param array<string,mixed> $user
     * @param array<string,mixed> $data
     * @return void
     */
    private function assertLinkOwnership(array $user, array $data): void
    {
        if (!empty($data['lead_id'])) {
            $lead = Lead::find((int)$user['id'], (int)$data['lead_id']);
            if (!$lead) {
                Response::error('Invalid lead_id', 422, ['lead_id' => 'Lead not found or not owned.']);
            }
        }
        if (!empty($data['client_id'])) {
            $client = Client::find((int)$user['id'], (int)$data['client_id']);
            if (!$client) {
                Response::error('Invalid client_id', 422, ['client_id' => 'Client not found or not owned.']);
            }
        }
    }

    /**
     * @param array<string,mixed> $data
     * @return array<string,mixed>
     */
    private function normalizeClientLink(array $data): array
    {
        if (isset($data['contact_id']) && empty($data['client_id'])) {
            $data['client_id'] = $data['contact_id'];
        }
        return $data;
    }

    private function formatSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        $size = $bytes;
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }
        return round($size, 1) . $units[$i];
    }

    private function normalizeAmount(mixed $value): int|float|bool
    {
        if ($value === null || $value === '') {
            return 0;
        }
        $num = str_replace(',', '', (string)$value);
        if (!is_numeric($num)) {
            return false;
        }
        return round((float)$num, 2);
    }
}
