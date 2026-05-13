<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/bootstrap.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/AcademicModel.php';
require_once __DIR__ . '/../models/AdminModel.php';

$resource = strtolower(trim((string) ($_GET['resource'] ?? '')));
$method = strtolower(trim((string) ($_GET['method'] ?? '')));
$legacyAction = strtolower(trim((string) ($_GET['action'] ?? '')));

$legacyActionMap = [
    'do-approve' => ['approvals', 'approve'],
    'do-delete-activity' => ['activities', 'delete'],
    'do-delete-assignment' => ['assignments', 'delete'],
    'do-delete-bank' => ['banks', 'delete'],
    'do-delete-class' => ['classes', 'delete'],
    'do-delete-feedback' => ['feedbacks', 'delete'],
    'do-delete-material' => ['materials', 'delete'],
    'do-delete-portfolio' => ['portfolios', 'delete'],
    'do-delete-schedule' => ['schedules', 'delete'],
    'do-delete-tuition' => ['tuitions', 'delete'],
    'do-delete-user' => ['users', 'delete'],
    'do-edit-assignment' => ['assignments', 'edit'],
    'do-edit-class' => ['classes', 'edit'],
    'do-edit-material' => ['materials', 'edit'],
    'do-edit-portfolio' => ['portfolios', 'edit'],
    'do-edit-schedule' => ['schedules', 'edit'],
    'do-grade-submission' => ['submissions', 'grade'],
    'do-login' => ['auth', 'login'],
    'do-request-teacher-leave' => ['teachers', 'request-leave'],
    'do-request-tuition-adjust' => ['tuitions', 'request-adjust'],
    'do-request-tuition-delete' => ['tuitions', 'request-delete'],
    'do-register-activity' => ['activities', 'join'],
    'do-join-activity' => ['activities', 'join'],
    'do-save-activity' => ['activities', 'save'],
    'do-save-assignment' => ['assignments', 'save'],
    'do-save-bank' => ['banks', 'save'],
    'do-save-class' => ['classes', 'save'],
    'do-save-feedback' => ['feedbacks', 'save'],
    'do-save-material' => ['materials', 'save'],
    'do-save-portfolio' => ['portfolios', 'save'],
    'do-save-role-permissions' => ['roles', 'save-permissions'],
    'do-save-schedule' => ['schedules', 'save'],
    'do-save-user' => ['users', 'save'],
    'do-submit-assignment' => ['assignments', 'submit'],
    'do-update-tuition' => ['tuitions', 'update'],
];

if (($resource === '' || $method === '') && $legacyAction !== '' && isset($legacyActionMap[$legacyAction])) {
    [$resource, $method] = $legacyActionMap[$legacyAction];
}

if (
    $resource === '' ||
    $method === '' ||
    !preg_match('/^[a-z0-9-]+$/', $resource) ||
    !preg_match('/^[a-z0-9-]+$/', $method)
) {
    http_response_code(404);
    echo '404 Not Found';
    exit;
}

$_GET['resource'] = $resource;
$_GET['method'] = $method;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = request_csrf_token();
    if (!validate_csrf_token($csrfToken)) {
        api_fail_invalid_csrf(page_url('home'));
    }
}

$canonicalModuleFile = __DIR__ . '/canonical/' . $resource . '.php';
$canonicalFunction = 'api_' . $resource . '_' . str_replace('-', '_', $method) . '_action';

if (is_file($canonicalModuleFile)) {
    require_once $canonicalModuleFile;

    if (function_exists($canonicalFunction)) {
        api_run_action($resource . '.' . $method, $canonicalFunction, page_url('home'));
    }
}

$legacyHandlerFile = __DIR__ . '/' . $resource . '/' . $method . '.php';
if (!is_file($legacyHandlerFile)) {
    http_response_code(404);
    echo '404 Not Found';
    exit;
}

try {
    require $legacyHandlerFile;
} catch (Throwable $exception) {
    app_log('error', 'Unhandled API dispatcher error', [
        'resource' => $resource,
        'method' => $method,
        'request_uri' => (string) ($_SERVER['REQUEST_URI'] ?? ''),
        'error' => $exception->getMessage(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'user_id' => (int) ((auth_user()['id'] ?? 0)),
    ]);

    if (api_expects_json()) {
        api_error(t('api.internal_server_error'), ['code' => 'SERVER_ERROR'], 500);
    }

    set_flash('error', t('flash.system_error'));
    redirect(page_url('home'));
}
