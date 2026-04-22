<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/api_helpers.php';
require_once __DIR__ . '/../../models/AdminModel.php';

function api_applications_extract_email(string $value): string
{
    $normalized = strtolower(trim($value));
    if ($normalized === '') {
        return '';
    }

    if (preg_match('/[a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,}/i', $normalized, $matches) === 1) {
        return strtolower((string) ($matches[0] ?? ''));
    }

    return '';
}

function api_applications_extract_phone(string $value): string
{
    $normalized = trim($value);
    if ($normalized === '') {
        return '';
    }

    if (preg_match('/(?:\+?\d[\d\s().-]{7,}\d)/', $normalized, $matches) !== 1) {
        return '';
    }

    $digits = preg_replace('/\D+/', '', (string) ($matches[0] ?? ''));
    if (!is_string($digits) || strlen($digits) < 8) {
        return '';
    }

    return $digits;
}

function api_applications_submit_action(): void
{
    $redirectPath = page_url('home') . '#lien-he';
    api_require_post($redirectPath);

    $fullName = input_string($_POST, 'full_name');
    $email = input_string($_POST, 'email');
    $phone = input_string($_POST, 'phone');
    $address = input_string($_POST, 'address');

    $positionApplied = input_string($_POST, 'position_applied');
    if ($positionApplied === '') {
        $positionApplied = input_string($_POST, 'applying_position');
    }

    $workMode = input_string($_POST, 'work_mode');
    if ($workMode === '') {
        $workMode = input_string($_POST, 'work_type');
    }

    $highestDegree = input_string($_POST, 'highest_degree');
    if ($highestDegree === '') {
        $highestDegree = input_string($_POST, 'degree');
    }

    $experienceYears = null;
    if (isset($_POST['experience_years'])) {
        $experienceYears = max(0, input_int($_POST, 'experience_years'));
    } else {
        $legacyExperienceYears = max(0, input_int($_POST, 'experience_years'));
        if ($legacyExperienceYears > 0) {
            $experienceYears = $legacyExperienceYears;
        }
    }

    $educationDetail = input_string($_POST, 'education_detail');
    if ($educationDetail === '') {
        $educationDetail = input_string($_POST, 'degree');
    }

    $workHistory = input_string($_POST, 'work_history');

    $bioSummary = input_string($_POST, 'bio_summary');

    $startDate = input_string($_POST, 'start_date');
    if ($startDate === '') {
        $startDate = input_string($_POST, 'start_date_available');
        if ($startDate === '') {
            $startDate = input_string($_POST, 'available_schedule');
        }
    }

    $payload = [
        'full_name' => $fullName,
        'email' => $email,
        'phone' => $phone,
        'address' => $address,
        'position_applied' => $positionApplied,
        'work_mode' => $workMode,
        'highest_degree' => $highestDegree,
        'experience_years' => $experienceYears,
        'education_detail' => $educationDetail,
        'work_history' => $workHistory,
        'skills_set' => input_string($_POST, 'skills_set'),
        'bio_summary' => $bioSummary,
        'start_date' => $startDate,
        'salary_expectation' => input_string($_POST, 'salary_expectation'),
        'cv_file_url' => input_string($_POST, 'cv_file_url'),
    ];

    // Require name and at least one contact method (email or phone)
    if ($payload['full_name'] === '' || ($payload['email'] === '' && $payload['phone'] === '')) {
        set_flash('home_error', 'Vui long nhap ho ten va thong tin lien he (email hoac so dien thoai).');
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
    $adminNote = input_string($_POST, 'hr_note');
    if ($adminNote === '') {
        $adminNote = input_string($_POST, 'admin_note');
    }

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
    if ($adminNote === '') {
        $adminNote = input_string($_POST, 'hr_note');
    }

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
