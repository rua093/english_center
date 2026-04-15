<?php
declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
redirect(page_url('login'));
}

$username = trim((string) ($_POST['username'] ?? ''));
$password = (string) ($_POST['password'] ?? '');

if ($username === '' || $password === '') {
set_flash('error', 'Vui lòng nhập đầy đủ thông tin đăng nhập.');
redirect(page_url('login'));
}

if (!login_attempt($username, $password)) {
set_flash('error', 'Thông tin đăng nhập không đúng hoặc tài khoản bị khóa.');
redirect(page_url('login'));
}

$user = auth_user();
if ($user && $user['role'] === 'student') {
redirect(page_url('dashboard-student'));
}
if ($user && $user['role'] === 'teacher') {
redirect(page_url('dashboard-teacher'));
}
if (has_permission('admin.dashboard.view')) {
redirect(page_url('dashboard-admin'));
}

redirect(page_url('classes-academic'));
