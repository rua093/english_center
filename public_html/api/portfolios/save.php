<?php
declare(strict_types=1);

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
redirect('/?page=academic-portfolios');
}

$user = auth_user();
$academicModel = new AcademicModel();
$portfolioId = (int) ($_POST['id'] ?? 0);

if ($portfolioId > 0) {
$existing = $academicModel->findPortfolio($portfolioId);
if ($existing && $user && $user['role'] === 'student' && (int) $existing['student_id'] !== (int) $user['id']) {
http_response_code(403);
echo '403 Forbidden';
exit;
}
}

if ($user && $user['role'] === 'student') {
$_POST['student_id'] = (string) $user['id'];
}

$uploadPath = trim((string) ($_POST['media_url'] ?? ''));
if (!empty($_FILES['portfolio_file']['name'])) {
$fileUpload = store_uploaded_file($_FILES['portfolio_file'], 'portfolio');
if ($fileUpload === null) {
set_flash('error', 'Tải lên media portfolio thất bại.');
redirect('/?page=academic-portfolios');
}
$uploadPath = $fileUpload;
}

$_POST['media_url'] = $uploadPath;
(new AcademicModel())->savePortfolio($_POST);
set_flash('success', 'Đã lưu portfolio thành công.');

redirect('/?page=academic-portfolios');
