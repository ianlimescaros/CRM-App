<?php
// API route map binding paths to controllers.

require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/LeadController.php';
require_once __DIR__ . '/../controllers/ClientController.php';
require_once __DIR__ . '/../controllers/DealController.php';
require_once __DIR__ . '/../controllers/TaskController.php';
require_once __DIR__ . '/../controllers/AiController.php';
require_once __DIR__ . '/../controllers/TenancyContractController.php';
require_once __DIR__ . '/../controllers/NocLeasingController.php';
require_once __DIR__ . '/../controllers/SearchController.php';

return [
    ['method' => 'POST', 'path' => '/auth/register', 'handler' => [AuthController::class, 'register']],
    ['method' => 'POST', 'path' => '/auth/login', 'handler' => [AuthController::class, 'login']],
    ['method' => 'POST', 'path' => '/auth/logout', 'handler' => [AuthController::class, 'logout']],
    ['method' => 'POST', 'path' => '/auth/session', 'handler' => [AuthController::class, 'createSession']],
    ['method' => 'POST', 'path' => '/auth/forgot', 'handler' => [AuthController::class, 'forgot']],
    ['method' => 'POST', 'path' => '/auth/reset', 'handler' => [AuthController::class, 'reset']],
    ['method' => 'GET', 'path' => '/auth/me', 'handler' => [AuthController::class, 'me']],
    ['method' => 'PUT', 'path' => '/auth/profile', 'handler' => [AuthController::class, 'updateProfile']],

    ['method' => 'GET', 'path' => '/leads', 'handler' => [LeadController::class, 'index']],
    ['method' => 'POST', 'path' => '/leads', 'handler' => [LeadController::class, 'store']],
    ['method' => 'PUT', 'path' => '/leads/{id}', 'handler' => [LeadController::class, 'update']],
    ['method' => 'DELETE', 'path' => '/leads/{id}', 'handler' => [LeadController::class, 'destroy']],
    ['method' => 'POST', 'path' => '/leads/{id}/archive', 'handler' => [LeadController::class, 'archive']],
    ['method' => 'POST', 'path' => '/leads/{id}/restore', 'handler' => [LeadController::class, 'restore']],
    ['method' => 'PATCH', 'path' => '/leads/bulk', 'handler' => [LeadController::class, 'bulkUpdate']],
    ['method' => 'POST', 'path' => '/leads/bulk/archive', 'handler' => [LeadController::class, 'bulkArchive']],
    ['method' => 'POST', 'path' => '/leads/bulk/restore', 'handler' => [LeadController::class, 'bulkRestore']],

    ['method' => 'GET', 'path' => '/clients', 'handler' => [ClientController::class, 'index']],
    ['method' => 'POST', 'path' => '/clients', 'handler' => [ClientController::class, 'store']],
    ['method' => 'GET', 'path' => '/clients/{id}', 'handler' => [ClientController::class, 'show']],
    ['method' => 'GET', 'path' => '/clients/{id}/timeline', 'handler' => [ClientController::class, 'timeline']],
    ['method' => 'GET', 'path' => '/clients/{id}/files', 'handler' => [ClientController::class, 'files']],
    ['method' => 'POST', 'path' => '/clients/{id}/files', 'handler' => [ClientController::class, 'files']],
    ['method' => 'DELETE', 'path' => '/clients/{id}/files', 'handler' => [ClientController::class, 'files']],
    ['method' => 'GET', 'path' => '/clients/{id}/files/{file_id}/download', 'handler' => [ClientController::class, 'downloadFile']],
    ['method' => 'GET', 'path' => '/clients/{id}/notes', 'handler' => [ClientController::class, 'notes']],
    ['method' => 'POST', 'path' => '/clients/{id}/notes', 'handler' => [ClientController::class, 'notes']],
    ['method' => 'POST', 'path' => '/clients/{id}/tasks', 'handler' => [ClientController::class, 'addTask']],
    ['method' => 'POST', 'path' => '/clients/{id}/deals', 'handler' => [ClientController::class, 'addDeal']],
    ['method' => 'PUT', 'path' => '/clients/{id}', 'handler' => [ClientController::class, 'update']],
    ['method' => 'DELETE', 'path' => '/clients/{id}', 'handler' => [ClientController::class, 'destroy']],

    ['method' => 'GET', 'path' => '/deals', 'handler' => [DealController::class, 'index']],
    ['method' => 'POST', 'path' => '/deals', 'handler' => [DealController::class, 'store']],
    ['method' => 'PUT', 'path' => '/deals/{id}', 'handler' => [DealController::class, 'update']],
    ['method' => 'DELETE', 'path' => '/deals/{id}', 'handler' => [DealController::class, 'destroy']],
    ['method' => 'GET', 'path' => '/deals/{id}/files', 'handler' => [DealController::class, 'files']],
    ['method' => 'POST', 'path' => '/deals/{id}/files', 'handler' => [DealController::class, 'files']],
    ['method' => 'DELETE', 'path' => '/deals/{id}/files', 'handler' => [DealController::class, 'files']],
    ['method' => 'GET', 'path' => '/deals/{id}/files/{file_id}/download', 'handler' => [DealController::class, 'downloadFile']],

    ['method' => 'GET', 'path' => '/tasks', 'handler' => [TaskController::class, 'index']],
    ['method' => 'POST', 'path' => '/tasks', 'handler' => [TaskController::class, 'store']],
    ['method' => 'PUT', 'path' => '/tasks/{id}', 'handler' => [TaskController::class, 'update']],
    ['method' => 'DELETE', 'path' => '/tasks/{id}', 'handler' => [TaskController::class, 'destroy']],

    ['method' => 'POST', 'path' => '/ai/summarize', 'handler' => [AiController::class, 'summarize']],
    ['method' => 'POST', 'path' => '/ai/suggest-followup', 'handler' => [AiController::class, 'suggestFollowup']],

    ['method' => 'POST', 'path' => '/tenancy-contracts/pdf', 'handler' => [TenancyContractController::class, 'downloadPdf']],
    ['method' => 'POST', 'path' => '/noc-leasing/pdf', 'handler' => [NocLeasingController::class, 'downloadPdf']],
    ['method' => 'GET', 'path' => '/search', 'handler' => [SearchController::class, 'search']],
];
