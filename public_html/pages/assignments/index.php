<?php
declare(strict_types=1);

require_login();

$user = auth_user();
if ($user && $user['role'] === 'student') {
redirect('/?page=student-dashboard');
}
if ($user && $user['role'] === 'teacher') {
redirect('/?page=academic-assignments');
}

redirect('/?page=academic-assignments');
