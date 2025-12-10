<?php

require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../services/Response.php';
require_once __DIR__ . '/../services/Validator.php';
require_once __DIR__ . '/../models/Lead.php';
require_once __DIR__ . '/BaseController.php';

class LeadController extends BaseController
{
    private array $leadStatuses = ['new', 'contacted', 'qualified', 'not_qualified'];
    private array $propertyTypes = [
        'Studio',
        '1 Bedroom',
        '2 Bedroom',
        '3 Bedroom',
        '4 Bedroom',
        'Townhouse/Villa',
        'Commercial Warehouse',
        'Commercial Office',
        'Commercial Rental',
    ];
    private array $currencies = ['USD', 'AED'];
    private array $sources = ['Bayut', 'Property Finder', 'Dubizzel', 'Reference/Random', 'Social Media'];
    private array $propertyFor = ['Rent/Lease', 'Sale/Buy', 'Off-Plan/Buyer'];
    private array $paymentOptions = ['Cash', 'Mortgage'];

    public function index(): void
    {
        $user = AuthMiddleware::require();
        $filters = [
            'status' => $_GET['status'] ?? null,
            'source' => $_GET['source'] ?? null,
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
        $total = Lead::countAll((int)$user['id'], $filters);
        $leads = Lead::all((int)$user['id'], $filters, $pagination);
        Response::success([
            'leads' => $leads,
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

        $errors = Validator::required($input, ['name']);
        if (!empty($input['email'])) {
            $errors = array_merge($errors, Validator::email($input['email']));
        }
        if (!empty($input['status'])) {
            $errors = array_merge($errors, Validator::inEnum($input['status'], $this->leadStatuses, 'status'));
        }
        if (!empty($input['interested_property'])) {
            $errors = array_merge($errors, Validator::inEnum($input['interested_property'], $this->propertyTypes, 'interested_property'));
        }
        if (!empty($input['property_for'])) {
            $errors = array_merge($errors, Validator::inEnum($input['property_for'], $this->propertyFor, 'property_for'));
        }
        if (!empty($input['source'])) {
            $errors = array_merge($errors, Validator::inEnum($input['source'], $this->sources, 'source'));
        }
        if (!empty($input['payment_option'])) {
            $errors = array_merge($errors, Validator::inEnum($input['payment_option'], $this->paymentOptions, 'payment_option'));
        }
        $currency = strtoupper(trim($input['currency'] ?? ''));
        if ($currency !== '') {
            $errors = array_merge($errors, Validator::inEnum($currency, $this->currencies, 'currency'));
        } else {
            $currency = null;
        }
        $budget = isset($input['budget']) ? str_replace([',', ' '], '', (string)$input['budget']) : null;
        if ($budget === '') {
            $budget = null;
        }
        if ($budget !== null && !is_numeric($budget)) {
            $errors = array_merge($errors, ['budget' => 'Budget must be a number.']);
        }
        if (!empty($input['last_contact_at'])) {
            $errors = array_merge($errors, Validator::dateYmd(substr($input['last_contact_at'], 0, 10), 'last_contact_at'));
        }

        if ($errors) {
            Response::error('Validation failed', 422, $errors);
        }

        $payload = array_merge($input, [
            'property_for' => $input['property_for'] ?? null,
            'currency' => $currency,
            'budget' => $budget !== null ? (float)$budget : null,
            'area' => $input['area'] ?? null,
            'payment_option' => !empty($input['payment_option']) ? $input['payment_option'] : null,
        ]);

        $id = Lead::create((int)$user['id'], $payload);
        $lead = Lead::find((int)$user['id'], $id);
        Response::success(['lead' => $lead], 201);
    }

    public function update(int $id): void
    {
        $user = AuthMiddleware::require();
        $existing = Lead::find((int)$user['id'], $id);
        if (!$existing) {
            Response::error('Lead not found', 404);
        }

        $input = $this->getJsonInput();
        $errors = Validator::required($input, ['name']);
        if (!empty($input['email'])) {
            $errors = array_merge($errors, Validator::email($input['email']));
        }
        $status = $input['status'] ?? $existing['status'];
        $errors = array_merge($errors, Validator::inEnum($status, $this->leadStatuses, 'status'));
        $property = $input['interested_property'] ?? $existing['interested_property'] ?? null;
        if (!empty($property)) {
            $errors = array_merge($errors, Validator::inEnum($property, $this->propertyTypes, 'interested_property'));
        }
        $propertyFor = $input['property_for'] ?? $existing['property_for'] ?? null;
        if (!empty($propertyFor)) {
            $errors = array_merge($errors, Validator::inEnum($propertyFor, $this->propertyFor, 'property_for'));
        }
        $source = $input['source'] ?? $existing['source'] ?? null;
        if (!empty($source)) {
            $errors = array_merge($errors, Validator::inEnum($source, $this->sources, 'source'));
        }
        $paymentOption = $input['payment_option'] ?? $existing['payment_option'] ?? null;
        if (!empty($paymentOption)) {
            $errors = array_merge($errors, Validator::inEnum($paymentOption, $this->paymentOptions, 'payment_option'));
        }
        $currency = strtoupper(trim($input['currency'] ?? ($existing['currency'] ?? '')));
        if ($currency !== '') {
            $errors = array_merge($errors, Validator::inEnum($currency, $this->currencies, 'currency'));
        } else {
            $currency = null;
        }
        $budget = isset($input['budget']) ? str_replace([',', ' '], '', (string)$input['budget']) : ($existing['budget'] ?? null);
        if ($budget === '') {
            $budget = null;
        }
        if ($budget !== null && !is_numeric($budget)) {
            $errors = array_merge($errors, ['budget' => 'Budget must be a number.']);
        }
        if (!empty($input['last_contact_at'])) {
            $errors = array_merge($errors, Validator::dateYmd(substr($input['last_contact_at'], 0, 10), 'last_contact_at'));
        }

        if ($errors) {
            Response::error('Validation failed', 422, $errors);
        }

        $payload = array_merge($existing, $input, [
            'status' => $status,
            'interested_property' => $property,
            'currency' => $currency,
            'budget' => $budget !== null ? (float)$budget : null,
            'source' => $source,
            'property_for' => $propertyFor,
            'area' => $input['area'] ?? $existing['area'] ?? null,
            'payment_option' => !empty($paymentOption) ? $paymentOption : null,
        ]);
        Lead::updateLead((int)$user['id'], $id, $payload);
        $lead = Lead::find((int)$user['id'], $id);
        Response::success(['lead' => $lead]);
    }

    public function bulkUpdate(): void
    {
        $user = AuthMiddleware::require();
        $input = $this->getJsonInput();
        $errors = Validator::required($input, ['ids', 'status']);
        $errors = array_merge($errors, Validator::inEnum($input['status'] ?? '', $this->leadStatuses, 'status'));
        if ($errors) {
            Response::error('Validation failed', 422, $errors);
        }
        $ids = is_array($input['ids']) ? array_map('intval', $input['ids']) : [];
        $updated = Lead::bulkUpdateStatus((int)$user['id'], $ids, $input['status']);
        Response::success(['updated' => $updated]);
    }

    public function destroy(int $id): void
    {
        $user = AuthMiddleware::require();
        $deleted = Lead::deleteLead((int)$user['id'], $id);
        if (!$deleted) {
            Response::error('Lead not found', 404);
        }
        Response::success(['message' => 'Lead deleted']);
    }

}
