<?php
declare(strict_types=1);

$homeWidgets = [
	'student_progress' => null,
	'teacher_schedules' => [],
];
if (is_logged_in()) {
	$user = auth_user();
	if ($user) {
		$homeWidgets = (new UserModel())->homeWidgetData((int) $user['id'], (string) $user['role']);
	}
}

require_once __DIR__ . '/main.php';
