<?php
declare(strict_types=1);

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/functions.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
	session_start();
}

function auth_user(): ?array
{
	return $_SESSION['auth_user'] ?? null;
}

function is_logged_in(): bool
{
	return auth_user() !== null;
}

function login_attempt(string $username, string $password): bool
{
	$pdo = Database::connection();
	$sql = 'SELECT u.id, u.username, u.password, u.full_name, u.status, r.role_name
			FROM users u
			INNER JOIN roles r ON r.id = u.role_id
			WHERE u.username = :username AND u.deleted_at IS NULL
			LIMIT 1';
	$stmt = $pdo->prepare($sql);
	$stmt->execute(['username' => $username]);
	$user = $stmt->fetch();

	if (!$user || $user['status'] !== 'active') {
		return false;
	}

	$stored = (string) $user['password'];
	$valid = password_verify($password, $stored);

	if (!$valid && $stored !== '' && hash_equals($stored, $password)) {
		$valid = true;
		$upgrade = $pdo->prepare('UPDATE users SET password = :password WHERE id = :id');
		$upgrade->execute([
			'id' => (int) $user['id'],
			'password' => password_hash($password, PASSWORD_DEFAULT),
		]);
	}

	if (!$valid) {
		return false;
	}

	session_regenerate_id(true);
	rotate_csrf_token();

	$_SESSION['auth_user'] = [
		'id' => (int) $user['id'],
		'username' => (string) $user['username'],
		'full_name' => (string) $user['full_name'],
		'role' => (string) $user['role_name'],
	];
	$_SESSION['auth_permissions'] = fetch_permission_slugs((int) $user['id']);

	return true;
}

function logout_user(): void
{
	$_SESSION = [];
	if (ini_get('session.use_cookies')) {
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool) $params['secure'], (bool) $params['httponly']);
	}
	session_destroy();
	session_start();
	rotate_csrf_token();
}

function fetch_permission_slugs(int $userId): array
{
	$pdo = Database::connection();
	$sql = "SELECT p.slug
		FROM users u
		INNER JOIN role_permissions rp ON rp.role_id = u.role_id
		INNER JOIN permissions p ON p.id = rp.permission_id
		WHERE u.id = :user_id";
	$stmt = $pdo->prepare($sql);
	$stmt->execute(['user_id' => $userId]);
	$rows = $stmt->fetchAll();

	$slugs = [];
	foreach ($rows as $row) {
		$slugs[] = (string) $row['slug'];
	}

	return array_values(array_unique($slugs));
}

function sync_auth_permissions(): void
{
	if (!is_logged_in()) {
		return;
	}

	$user = auth_user();
	$userId = (int) ($user['id'] ?? 0);
	if ($userId <= 0) {
		return;
	}

	$_SESSION['auth_permissions'] = fetch_permission_slugs($userId);
}

function auth_permissions(): array
{
	return $_SESSION['auth_permissions'] ?? [];
}

function has_permission(string $slug): bool
{
	$user = auth_user();
	$role = strtolower((string) ($user['role'] ?? ''));
	if ($role === 'admin') {
		return true;
	}

	return in_array($slug, auth_permissions(), true);
}

function checkPermission(string $slug): bool
{
	return has_permission($slug);
}

function has_any_permission(array $slugs): bool
{
	$user = auth_user();
	$role = strtolower((string) ($user['role'] ?? ''));
	if ($role === 'admin') {
		return true;
	}

	foreach ($slugs as $slug) {
		if (has_permission((string) $slug)) {
			return true;
		}
	}

	return false;
}

function require_any_permission(array $slugs): void
{
	require_login();

	if (!has_any_permission($slugs)) {
		http_response_code(403);
		echo '403 Forbidden';
		exit;
	}
}

function require_login(): void
{
	if (!is_logged_in()) {
		set_flash('error', 'Vui lòng đăng nhập để tiếp tục.');
		redirect('/?page=login');
	}
}

function is_admin_or_staff(): bool
{
	$user = auth_user();
	$role = (string) ($user['role'] ?? '');
	return in_array($role, ['admin', 'staff'], true);
}

function is_admin_area(): bool
{
	$user = auth_user();
	$role = (string) ($user['role'] ?? '');
	return in_array($role, ['admin', 'staff', 'teacher'], true);
}

function require_admin_or_staff(): void
{
	require_login();

	if (!is_admin_or_staff()) {
		set_flash('error', 'Bạn không có quyền truy cập khu vực quản trị.');
		redirect('/?page=home');
	}
}

function require_role(array $roles): void
{
	require_login();

	$user = auth_user();
	if (!$user || !in_array($user['role'], $roles, true)) {
		http_response_code(403);
		echo '403 Forbidden';
		exit;
	}
}

function require_permission(string $slug): void
{
	require_login();

	if (!has_permission($slug)) {
		http_response_code(403);
		echo '403 Forbidden';
		exit;
	}
}

function can_access_page(string $page): bool
{
	$page = resolve_page_slug($page);

	if (!is_logged_in()) {
		return in_array($page, ['home', 'login'], true);
	}

	$user = auth_user();
	$role = (string) ($user['role'] ?? '');

	switch ($page) {
		case 'dashboard-student':
			return in_array($role, ['student', 'admin'], true);
		case 'dashboard-teacher':
			return false;
		case 'profile':
		case 'classes-my':
		case 'assignments-my':
		case 'feedback':
			return true;
		case 'admin':
			return in_array($role, ['admin', 'staff', 'teacher'], true);
		case 'portfolios-academic':
			return has_any_permission(['academic.portfolios.view']);
		case 'dashboard-admin':
			return has_permission('admin.dashboard.view');
		case 'users-admin':
			return has_any_permission(['admin.user.view']);
		case 'tuition-finance':
			return has_any_permission(['finance.tuition.view']);
		case 'registration-finance':
			return has_any_permission(['finance.registration.view']);
		case 'promotions-manage':
			return has_any_permission(['finance.promotions.view']);
		case 'payments-finance':
			return has_any_permission(['finance.payments.view']);
		case 'feedbacks-manage':
			return has_any_permission(['feedback.view']);
		case 'student-leads-manage':
			return has_any_permission(['student_lead.view']);
		case 'job-applications-manage':
			return has_any_permission(['job_application.view']);
		case 'approvals-manage':
			return has_any_permission(['approval.view']);
		case 'activities-manage':
			return has_any_permission(['activity.view']);
		case 'bank-manage':
			return false;
		case 'rooms-manage':
			return has_any_permission(['academic.schedules.view']);
		case 'notifications-manage':
			return has_any_permission(['admin.dashboard.view']);
		case 'courses-academic':
			return has_permission('academic.courses.view');
		case 'roadmaps-academic':
			return has_permission('academic.roadmaps.view');
		case 'classes-academic':
			return has_any_permission(['academic.classes.view', 'academic.schedules.view']);
		case 'classrooms-academic':
			return has_any_permission(['academic.classes.view', 'academic.schedules.view']);
		case 'schedules-academic':
			return has_permission('academic.schedules.view');
		case 'assignments-academic':
			return has_permission('academic.assignments.view');
		case 'materials-academic':
			return has_permission('materials.view');
		default:
			return false;
	}
}
