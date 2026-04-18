<?php
declare(strict_types=1);

function page_route_definitions(): array
{
	return [
		'home' => ['directory' => 'home', 'aliases' => []],
		'login' => ['directory' => 'login', 'aliases' => []],
		'register' => ['directory' => 'register', 'aliases' => []],
		'logout' => ['directory' => 'logout', 'aliases' => []],
		'admin' => ['directory' => 'admin', 'aliases' => []],
		'profile' => ['directory' => 'profile', 'aliases' => []],
		'classes-my' => ['directory' => 'my-classes', 'aliases' => ['my-classes']],
		'assignments-my' => ['directory' => 'assignments', 'aliases' => ['assignments']],
		'dashboard-student' => ['directory' => 'student-dashboard', 'aliases' => ['student-dashboard']],
		'dashboard-teacher' => ['directory' => 'teacher-dashboard', 'aliases' => ['teacher-dashboard']],
		'dashboard-admin' => ['directory' => 'admin-dashboard', 'aliases' => ['admin-dashboard']],
		'users-admin' => ['directory' => 'admin-users', 'aliases' => ['admin-users']],
		'tuition-finance' => ['directory' => 'finance-tuition', 'aliases' => ['finance-tuition']],
		'payments-finance' => ['directory' => 'finance-payments', 'aliases' => ['finance-payments']],
		'feedbacks-manage' => ['directory' => 'manage-feedbacks', 'aliases' => ['manage-feedbacks']],
		'approvals-manage' => ['directory' => 'manage-approvals', 'aliases' => ['manage-approvals']],
		'activities-manage' => ['directory' => 'manage-activities', 'aliases' => ['manage-activities']],
		'bank-manage' => ['directory' => 'manage-bank', 'aliases' => ['manage-bank']],
		'classes-academic' => ['directory' => 'academic-classes', 'aliases' => ['academic-classes']],
		'classes-academic-edit' => ['directory' => 'academic-class-edit', 'aliases' => ['academic-class-edit']],
		'schedules-academic' => ['directory' => 'academic-schedules', 'aliases' => ['academic-schedules']],
		'schedules-academic-edit' => ['directory' => 'academic-schedule-edit', 'aliases' => ['academic-schedule-edit']],
		'assignments-academic' => ['directory' => 'academic-assignments', 'aliases' => ['academic-assignments']],
		'assignments-academic-edit' => ['directory' => 'academic-assignment-edit', 'aliases' => ['academic-assignment-edit']],
		'materials-academic' => ['directory' => 'academic-materials', 'aliases' => ['academic-materials']],
		'materials-academic-edit' => ['directory' => 'academic-material-edit', 'aliases' => ['academic-material-edit']],
		'portfolios-academic' => ['directory' => 'academic-portfolios', 'aliases' => ['academic-portfolios']],
		'submissions-academic' => ['directory' => 'academic-submissions', 'aliases' => ['academic-submissions']],
		'activities-student' => ['directory' => 'student-activities', 'aliases' => ['activities']],
		'activities-details' => ['directory' => 'student-activities/activites-details', 'aliases' => ['activities-details']],
	];
}

function resolve_page_slug(string $page): string
{
	$normalized = strtolower(trim($page));
	if ($normalized === '') {
		return 'home';
	}

	$definitions = page_route_definitions();
	if (isset($definitions[$normalized])) {
		return $normalized;
	}

	foreach ($definitions as $canonicalSlug => $definition) {
		$aliases = $definition['aliases'] ?? [];
		if (in_array($normalized, $aliases, true)) {
			return $canonicalSlug;
		}
	}
	
	return $normalized;
}

function page_directory_slug(string $canonicalSlug): string
{
	$definitions = page_route_definitions();
	$route = $definitions[$canonicalSlug] ?? null;
	if (!is_array($route)) {
		return $canonicalSlug;
	}

	$directory = (string) ($route['directory'] ?? '');
	return $directory !== '' ? $directory : $canonicalSlug;
}

function page_url(string $canonicalSlug, array $query = []): string
{
	$resolved = resolve_page_slug($canonicalSlug);
	$queryString = http_build_query(array_merge(['page' => $resolved], $query));
	return '/?' . $queryString;
}
