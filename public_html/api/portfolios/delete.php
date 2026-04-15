<?php
declare(strict_types=1);

require_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
redirect('/?page=academic-portfolios');
}

$user = auth_user();
$portfolio = (new AcademicModel())->findPortfolio((int) ($_GET['id'] ?? 0));
if ($portfolio && $user && $user['role'] === 'student' && (int) $portfolio['student_id'] !== (int) $user['id']) {
http_response_code(403);
echo '403 Forbidden';
exit;
}

(new AcademicModel())->deletePortfolio((int) ($_GET['id'] ?? 0));
set_flash('success', 'Đã xóa portfolio.');
redirect('/?page=academic-portfolios');
