<?php
// Controller for task CRUD.

require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../services/Response.php';
require_once __DIR__ . '/../services/Validator.php';
require_once __DIR__ . '/../models/Task.php';
require_once __DIR__ . '/../models/Lead.php';
require_once __DIR__ . '/../models/Client.php';
require_once __DIR__ . '/BaseController.php';

class TaskController extends BaseController
{
    /** @var array<int|string, string> */
    private array $statuses = ['pending', 'done'];

    public function index(): void
    {
        $user = AuthMiddleware::require();
        $filters = [
            'status' => $_GET['status'] ?? null,
            'due_date' => $_GET['due_date'] ?? null,
            'client_id' => $_GET['client_id'] ?? ($_GET['contact_id'] ?? null),
            'lead_id' => $_GET['lead_id'] ?? null,
        ];
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = min(50, max(5, (int)($_GET['per_page'] ?? 20)));
        $orderBy = $_GET['sort'] ?? 'due_date';
        $orderDir = $_GET['direction'] ?? 'ASC';
        $pagination = [
            'limit' => $perPage,
            'offset' => ($page - 1) * $perPage,
            'order_by' => $orderBy,
            'order_dir' => $orderDir,
        ];
        $total = Task::countAll((int)$user['id'], $filters);
        $tasks = Task::all((int)$user['id'], $filters, $pagination);
        Response::success([
            'tasks' => $tasks,
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
        $status = (string)($input['status'] ?? 'pending');
        $errors = array_merge($errors, (array)Validator::inEnum($status, $this->statuses, 'status'));
        if ($errors) {
            Response::error('Validation failed', 422, $errors);
        }

        $this->assertLinkOwnership($user, $input);

        $id = Task::create((int)$user['id'], array_merge($input, ['status' => $status]));
        $task = Task::find((int)$user['id'], $id);
        Response::success(['task' => $task], 201);
    }

    public function update(int $id): void
    {
        $user = AuthMiddleware::require();
        $existing = Task::find((int)$user['id'], $id);
        if (!$existing) {
            Response::error('Task not found', 404);
        }

        $input = $this->normalizeClientLink($this->getJsonInput());
        $payload = array_merge((array)$existing, (array)$input);
        $status = (string)($payload['status'] ?? 'pending');
        $errors = array_merge(
            (array)Validator::required($payload, ['title']),
            (array)Validator::inEnum($status, $this->statuses, 'status')
        );
        if ($errors) {
            Response::error('Validation failed', 422, $errors);
        }

        $this->assertLinkOwnership($user, $payload);

        $payload['status'] = $status;
        Task::updateTask((int)$user['id'], $id, $payload);
        $task = Task::find((int)$user['id'], $id);
        Response::success(['task' => $task]);
    }

    public function destroy(int $id): void
    {
        $user = AuthMiddleware::require();
        $deleted = Task::deleteTask((int)$user['id'], $id);
        if (!$deleted) {
            Response::error('Task not found', 404);
        }
        Response::success(['message' => 'Task deleted']);
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
}
