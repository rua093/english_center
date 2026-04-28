<?php
declare(strict_types=1);

require_login();

$user = auth_user();
$role = strtolower((string) ($user['role'] ?? ''));

if ($role === 'teacher') {
	if (can_access_page('schedules-academic')) {
		redirect(page_url('schedules-academic'));
	}

	if (can_access_page('classes-academic')) {
		redirect(page_url('classes-academic'));
	}
}

if ($role === 'staff') {
	if (can_access_page('classes-academic')) {
		redirect(page_url('classes-academic'));
	}

	if (can_access_page('schedules-academic')) {
		redirect(page_url('schedules-academic'));
	}

	if (can_access_page('users-admin')) {
		redirect(page_url('users-admin'));
	}

	if (can_access_page('dashboard-admin')) {
		redirect(page_url('dashboard-admin'));
	}
}

if ($role === 'admin' && has_permission('admin.dashboard.view')) {
	redirect(page_url('dashboard-admin'));
}

if (can_access_page('schedules-academic')) {
	redirect(page_url('schedules-academic'));
}

http_response_code(403);
echo '403 Forbidden';
exit;
