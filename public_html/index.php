<?php
declare(strict_types=1);

require_once __DIR__ . '/core/bootstrap.php';
require_once __DIR__ . '/models/UserModel.php';
require_once __DIR__ . '/models/AcademicModel.php';
require_once __DIR__ . '/models/AdminModel.php';

$page = strtolower(trim((string) ($_GET['page'] ?? 'home')));
if ($page === '') {
	$page = 'home';
}

if (!preg_match('/^[a-z0-9-]+$/', $page)) {
	http_response_code(404);
	echo '404 Not Found';
	exit;
}

$page = resolve_page_slug($page);

$_GET['page'] = $page;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$csrfToken = request_csrf_token();
	if (!validate_csrf_token($csrfToken)) {
		set_flash('error', t('flash.invalid_request'));
		$refererPath = safe_referer_path((string) ($_SERVER['HTTP_REFERER'] ?? ''));
		if ($refererPath !== '') {
			header('Location: ' . $refererPath);
			exit;
		}
		redirect('/?page=home');
	}
}

if (str_starts_with($page, 'do-')) {
	$_GET['action'] = $page;
	require __DIR__ . '/api/index.php';
	exit;
}

$pageDirSlug = page_directory_slug($page);
$pageDir = __DIR__ . '/pages/' . $pageDirSlug;
$indexFile = $pageDir . '/index.php';
$wrapperFile = $pageDir . '/page.php';

if (!is_file($indexFile)) {
	http_response_code(404);
	echo '404 Not Found';
	exit;
}

if (is_file($wrapperFile)) {
	ob_start();
	require $indexFile;
	$__pageContent = ob_get_clean();
	require $wrapperFile;
	exit;
}

require $indexFile;
