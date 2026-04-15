<?php
declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
redirect('/?page=login');
}

$username = trim((string) ($_POST['username'] ?? ''));
$password = (string) ($_POST['password'] ?? '');

if ($username === '' || $password === '') {
set_flash('error', 'Vui lòng nhập đầy đủ thông tin đăng nhập.');
redirect('/?page=login');
}

if (!login_attempt($username, $password)) {
set_flash('error', 'Thông tin đăng nhập không đúng hoặc tài khoản bị khóa.');
redirect('/?page=login');
}

$user = auth_user();
if ($user && $user['role'] === 'student') {
redirect('/?page=student-dashboard');
}
if ($user && $user['role'] === 'teacher') {
redirect('/?page=teacher-dashboard');
}
if (has_permission('admin.dashboard.view')) {
redirect('/?page=admin-dashboard');
}

redirect('/?page=academic-classes');
