<?php
// Controller for AI assistant API requests.

require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../services/Response.php';
require_once __DIR__ . '/../services/Validator.php';
require_once __DIR__ . '/../services/AiService.php';
require_once __DIR__ . '/BaseController.php';

class AiController extends BaseController
{
    private AiService $ai;

    public function __construct()
    {
        $this->ai = new AiService();
    }

    public function summarize(): void
    {
        AuthMiddleware::require();
        $input = $this->getJsonInput();
        $errors = (array) Validator::required($input, ['notes']);
        $errors = array_merge($errors, Validator::stringLength((string)($input['notes'] ?? ''), 'notes', 1, 4000));
        if ($errors) {
            Response::error('Validation failed', 422, $errors);
        }

        $result = $this->ai->summarizeNotes($this->asString($input['notes'] ?? ''));
        if (isset($result['error'])) {
            Response::error($result['error'], 502);
        }

        Response::success(['summary' => $result['text']]);
    }

    public function suggestFollowup(): void
    {
        AuthMiddleware::require();
        $input = $this->getJsonInput();
        $errors = (array) Validator::required($input, ['lead_name', 'context']);
        $errors = array_merge(
            $errors,
            (array)Validator::stringLength((string)($input['lead_name'] ?? ''), 'lead_name', 1, 200),
            (array)Validator::stringLength((string)($input['context'] ?? ''), 'context', 1, 4000)
        );
        if ($errors) {
            Response::error('Validation failed', 422, $errors);
        }

        $result = $this->ai->suggestFollowup($this->asString($input['lead_name'] ?? ''), $this->asString($input['context'] ?? ''));
        if (isset($result['error'])) {
            Response::error($result['error'], 502);
        }

        Response::success(['message' => $result['text']]);
    }
}
