<?php

require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/LeadController.php';
require_once __DIR__ . '/../controllers/ContactController.php';
require_once __DIR__ . '/../controllers/DealController.php';
require_once __DIR__ . '/../controllers/TaskController.php';
require_once __DIR__ . '/../controllers/AiController.php';

return [
    ['method' => 'POST', 'path' => '/auth/register', 'handler' => [AuthController::class, 'register']],
    ['method' => 'POST', 'path' => '/auth/login', 'handler' => [AuthController::class, 'login']],
    ['method' => 'POST', 'path' => '/auth/logout', 'handler' => [AuthController::class, 'logout']],

    ['method' => 'GET', 'path' => '/leads', 'handler' => [LeadController::class, 'index']],
    ['method' => 'POST', 'path' => '/leads', 'handler' => [LeadController::class, 'store']],
    ['method' => 'PUT', 'path' => '/leads/{id}', 'handler' => [LeadController::class, 'update']],
    ['method' => 'DELETE', 'path' => '/leads/{id}', 'handler' => [LeadController::class, 'destroy']],
    ['method' => 'PATCH', 'path' => '/leads/bulk', 'handler' => [LeadController::class, 'bulkUpdate']],

    ['method' => 'GET', 'path' => '/contacts', 'handler' => [ContactController::class, 'index']],
    ['method' => 'POST', 'path' => '/contacts', 'handler' => [ContactController::class, 'store']],
    ['method' => 'PUT', 'path' => '/contacts/{id}', 'handler' => [ContactController::class, 'update']],
    ['method' => 'DELETE', 'path' => '/contacts/{id}', 'handler' => [ContactController::class, 'destroy']],

    ['method' => 'GET', 'path' => '/deals', 'handler' => [DealController::class, 'index']],
    ['method' => 'POST', 'path' => '/deals', 'handler' => [DealController::class, 'store']],
    ['method' => 'PUT', 'path' => '/deals/{id}', 'handler' => [DealController::class, 'update']],
    ['method' => 'DELETE', 'path' => '/deals/{id}', 'handler' => [DealController::class, 'destroy']],

    ['method' => 'GET', 'path' => '/tasks', 'handler' => [TaskController::class, 'index']],
    ['method' => 'POST', 'path' => '/tasks', 'handler' => [TaskController::class, 'store']],
    ['method' => 'PUT', 'path' => '/tasks/{id}', 'handler' => [TaskController::class, 'update']],
    ['method' => 'DELETE', 'path' => '/tasks/{id}', 'handler' => [TaskController::class, 'destroy']],

    ['method' => 'POST', 'path' => '/ai/summarize', 'handler' => [AiController::class, 'summarize']],
    ['method' => 'POST', 'path' => '/ai/suggest-followup', 'handler' => [AiController::class, 'suggestFollowup']],
];
