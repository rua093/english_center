<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/api_helpers.php';
require_once __DIR__ . '/../../models/AdminModel.php';

function api_leads_submit_action(): void
{
    $redirectPath = page_url('home') . '#lien-he';
    api_require_post($redirectPath);

    $payload = [
        'full_name' => input_string($_POST, 'full_name'),
        'phone' => input_string($_POST, 'phone'),
        'email' => input_string($_POST, 'email'),
        'age' => max(0, input_int($_POST, 'age')),
        'parent_name' => input_string($_POST, 'parent_name'),
        'parent_phone' => input_string($_POST, 'parent_phone'),
        'school_name' => input_string($_POST, 'school_name'),
        'target_program' => input_string($_POST, 'target_program'),
        'target_score' => input_string($_POST, 'target_score'),
        'desired_schedule' => input_string($_POST, 'desired_schedule'),
        'note' => input_string($_POST, 'note'),
        'source' => 'website',
    ];

    if ($payload['full_name'] === '' || $payload['phone'] === '') {
        set_flash('home_error', 'Vui long nhap ho ten va so dien thoai de gui yeu cau tu van.');
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
        (new AdminModel())->submitStudentLead($payload);
        set_flash('home_success', 'Yeu cau tu van da duoc ghi nhan. Trung tam se lien he voi ban som nhat.');
    } catch (Throwable $exception) {
        set_flash('home_error', 'Khong the gui yeu cau tu van. Vui long thu lai sau.');
    }

    redirect($redirectPath);
}

function api_leads_update_action(): void
{
    api_guard_permission('student_lead.manage');
    api_require_post(page_url('student-leads-manage'));

    $leadId = input_int($_POST, 'id');
    $status = input_string($_POST, 'status');
    $adminNote = input_string($_POST, 'admin_note');

    if ($leadId <= 0) {
        set_flash('error', 'Lead hoc vien khong hop le.');
        redirect(page_url('student-leads-manage'));
    }

    try {
        (new AdminModel())->updateStudentLeadReview($leadId, $status, $adminNote);
        set_flash('success', 'Da cap nhat trang thai lead hoc vien.');
    } catch (Throwable $exception) {
        set_flash('error', 'Khong the cap nhat lead: ' . $exception->getMessage());
    }

    redirect(page_url('student-leads-manage', ['edit' => $leadId]));
}

function api_leads_convert_action(): void
{
    api_guard_permission('student_lead.manage');
    api_guard_permission('admin.user.manage');
    api_require_post(page_url('student-leads-manage'));

    $leadId = input_int($_POST, 'id');
    $username = input_string($_POST, 'username');
    $password = input_string($_POST, 'password');
    $adminNote = input_string($_POST, 'admin_note');

    if ($leadId <= 0) {
        set_flash('error', 'Lead hoc vien khong hop le.');
        redirect(page_url('student-leads-manage'));
    }

    try {
        $result = (new AdminModel())->convertStudentLeadToUser($leadId, [
            'username' => $username,
            'password' => $password,
            'admin_note' => $adminNote,
        ]);

        $successMessage = 'Da tao tai khoan hoc vien: ' . (string) ($result['username'] ?? '');
        if (!empty($result['used_default_password'])) {
            $successMessage .= ' (mat khau tam: ' . (string) ($result['password'] ?? '') . ').';
        }

        set_flash('success', $successMessage);
    } catch (Throwable $exception) {
        set_flash('error', 'Khong the tao tai khoan hoc vien: ' . $exception->getMessage());
    }

    redirect(page_url('student-leads-manage', ['edit' => $leadId]));
}
