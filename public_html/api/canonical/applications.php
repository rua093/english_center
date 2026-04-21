<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/api_helpers.php';
require_once __DIR__ . '/../../models/AdminModel.php';

function api_applications_submit_action(): void
{
    $redirectPath = page_url('home') . '#lien-he';
    api_require_post($redirectPath);

    $payload = [
        'full_name' => input_string($_POST, 'full_name'),
        'phone' => input_string($_POST, 'phone'),
        'email' => input_string($_POST, 'email'),
        'applying_position' => input_string($_POST, 'applying_position'),
        'degree' => input_string($_POST, 'degree'),
        'experience_years' => max(0, input_int($_POST, 'experience_years')),
        'available_schedule' => input_string($_POST, 'available_schedule'),
        'intro' => input_string($_POST, 'intro'),
        'source' => 'website',
    ];

    if ($payload['full_name'] === '' || $payload['phone'] === '') {
        set_flash('home_error', 'Vui long nhap ho ten va so dien thoai de gui ho so ung tuyen.');
        redirect($redirectPath);
    }

    $phoneDigits = preg_replace('/\D+/', '', $payload['phone']);
    if (!is_string($phoneDigits) || strlen($phoneDigits) < 8) {
        set_flash('home_error', 'So dien thoai khong hop le.');
        redirect($redirectPath);
    }

    if ($payload['email'] !== '' && filter_var($payload['email'], FILTER_VALIDATE_EMAIL) === false) {
        set_flash('home_error', 'Email khong hop le.');
        redirect($redirectPath);
    }

    try {
        (new AdminModel())->submitJobApplication($payload);
        set_flash('home_success', 'Ho so ung tuyen da duoc ghi nhan. Trung tam se lien he de phong van.');
    } catch (Throwable $exception) {
        set_flash('home_error', 'Khong the gui ho so ung tuyen. Vui long thu lai sau.');
    }

    redirect($redirectPath);
}

function api_applications_update_action(): void
{
    api_guard_permission('job_application.manage');
    api_require_post(page_url('job-applications-manage'));

    $applicationId = input_int($_POST, 'id');
    $status = input_string($_POST, 'status');
    $adminNote = input_string($_POST, 'admin_note');

    if ($applicationId <= 0) {
        set_flash('error', 'Ho so ung tuyen khong hop le.');
        redirect(page_url('job-applications-manage'));
    }

    try {
        (new AdminModel())->updateJobApplicationReview($applicationId, $status, $adminNote);
        set_flash('success', 'Da cap nhat trang thai ho so ung tuyen.');
    } catch (Throwable $exception) {
        set_flash('error', 'Khong the cap nhat ho so: ' . $exception->getMessage());
    }

    redirect(page_url('job-applications-manage', ['edit' => $applicationId]));
}

function api_applications_convert_action(): void
{
    api_guard_permission('job_application.manage');
    api_guard_permission('admin.user.manage');
    api_require_post(page_url('job-applications-manage'));

    $applicationId = input_int($_POST, 'id');
    $username = input_string($_POST, 'username');
    $password = input_string($_POST, 'password');
    $adminNote = input_string($_POST, 'admin_note');

    if ($applicationId <= 0) {
        set_flash('error', 'Ho so ung tuyen khong hop le.');
        redirect(page_url('job-applications-manage'));
    }

    try {
        $result = (new AdminModel())->convertJobApplicationToUser($applicationId, [
            'username' => $username,
            'password' => $password,
            'admin_note' => $adminNote,
        ]);

        $successMessage = 'Da tao tai khoan giao vien: ' . (string) ($result['username'] ?? '');
        if (!empty($result['used_default_password'])) {
            $successMessage .= ' (mat khau tam: ' . (string) ($result['password'] ?? '') . ').';
        }

        set_flash('success', $successMessage);
    } catch (Throwable $exception) {
        set_flash('error', 'Khong the tao tai khoan giao vien: ' . $exception->getMessage());
    }

    redirect(page_url('job-applications-manage', ['edit' => $applicationId]));
}
