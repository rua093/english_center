<?php
declare(strict_types=1);

require_login();

$user = auth_user();
if ($user && $user['role'] === 'student') {
redirect(page_url('dashboard-student'));
}
if ($user && $user['role'] === 'teacher') {
redirect(page_url('assignments-academic'));
}

redirect(page_url('assignments-academic'));
