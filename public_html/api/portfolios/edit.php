<?php
declare(strict_types=1);

require_login();

$user = auth_user();
$portfolio = (new AcademicModel())->findPortfolio((int) ($_GET['id'] ?? 0));
if ($portfolio && $user && $user['role'] === 'student' && (int) $portfolio['student_id'] !== (int) $user['id']) {
http_response_code(403);
echo '403 Forbidden';
exit;
}

redirect('/?page=academic-portfolios&edit=' . (int) ($_GET['id'] ?? 0));
