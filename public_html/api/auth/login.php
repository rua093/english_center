<?php
declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
redirect(page_url('login'));
}

$username = trim((string) ($_POST['username'] ?? ''));
$password = (string) ($_POST['password'] ?? '');

if ($username === '' || $password === '') {
set_flash('error', t('auth.login.missing_credentials'));
redirect(page_url('login'));
}

if (!login_attempt($username, $password)) {
set_flash('error', t('auth.login.invalid_credentials'));
redirect(page_url('login'));
}

$user = auth_user();
if ($user && $user['role'] === 'student') {
redirect(page_url('dashboard-student'));
}
if ($user && $user['role'] === 'teacher') {
redirect(page_url('admin'));
}
if ($user && $user['role'] === 'staff') {
redirect(page_url('admin'));
}
if (has_permission('admin.dashboard.view')) {
redirect(page_url('dashboard-admin'));
}

if (can_access_page('admin')) {
redirect(page_url('admin'));
}

if (can_access_page('classes-academic')) {
redirect(page_url('classes-academic'));
}

redirect(page_url('home'));
