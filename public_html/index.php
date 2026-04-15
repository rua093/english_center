<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/functions.php';
require_once __DIR__ . '/core/get_version.php';
require_once __DIR__ . '/core/auth.php';
require_once __DIR__ . '/core/page_actions.php';
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

$_GET['page'] = $page;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$csrfToken = (string) ($_POST['_csrf'] ?? '');
	if (!validate_csrf_token($csrfToken)) {
		set_flash('error', 'Yêu cầu không hợp lệ. Vui lòng thử lại.');
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

$pageDir = __DIR__ . '/pages/' . $page;
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