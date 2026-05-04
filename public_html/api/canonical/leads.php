<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/api_helpers.php';
require_once __DIR__ . '/../../models/AdminModel.php';

function api_leads_extract_email(string $value): string
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

function api_leads_extract_phone(string $value): string
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

function api_leads_submit_action(): void
{
    $redirectPath = page_url('home') . '#lien-he';
    api_require_post($redirectPath);

    $studentName = input_string($_POST, 'student_name');
    if ($studentName === '') {
        $studentName = input_string($_POST, 'full_name');
    }

    $legacyPhone = input_string($_POST, 'phone');
    $legacyEmail = input_string($_POST, 'email');

    $parentName = input_string($_POST, 'parent_name');
    $parentPhone = normalize_phone_string(input_string($_POST, 'parent_phone'));

    $interests = input_string($_POST, 'interests');
    if ($interests === '') {
        $interests = input_string($_POST, 'target_program');
    }

    $schoolName = input_string($_POST, 'school_name');

    $currentGrade = input_string($_POST, 'current_grade');
    if ($currentGrade === '') {
        $currentGrade = input_string($_POST, 'class_level');
    }

    $currentLevel = input_string($_POST, 'current_level');
    if ($currentLevel === '') {
        $currentLevel = input_string($_POST, 'target_score');
    }

    $studyTime = input_string($_POST, 'study_time');
    if ($studyTime === '') {
        $studyTime = input_string($_POST, 'desired_schedule');
    }

    $parentExpectation = input_string($_POST, 'parent_expectation');
    if ($parentExpectation === '') {
        $parentExpectation = input_string($_POST, 'note');
    }

    $referralSource = input_string($_POST, 'referral_source');
    if ($referralSource === '') {
        $referralSource = input_string($_POST, 'source', 'website');
    }

    $payload = [
        'student_name' => $studentName,
        'gender' => input_string($_POST, 'gender'),
        'dob' => input_string($_POST, 'dob'),
        'interests' => $interests,
        'school_name' => $schoolName,
        'current_grade' => $currentGrade,
        'personality' => input_string($_POST, 'personality'),
        'parent_name' => $parentName,
        'parent_phone' => $parentPhone,
        'referral_source' => $referralSource,
        'current_level' => $currentLevel,
        'study_time' => $studyTime,
        'parent_expectation' => $parentExpectation,
    ];

    // Require name and at least one contact method (phone or email)
    $contactPhone = api_leads_extract_phone($parentPhone . ' ' . normalize_phone_string($legacyPhone) . ' ' . $parentName);
    $contactEmail = api_leads_extract_email($legacyEmail . ' ' . $parentName);

    if ($payload['student_name'] === '' || ($contactPhone === '' && $contactEmail === '')) {
        set_flash('home_error', 'Vui long nhap ten hoc vien va thong tin lien he phu huynh (so dien thoai hoac email).');
        redirect($redirectPath);
    }

    if ($contactEmail !== '' && filter_var($contactEmail, FILTER_VALIDATE_EMAIL) === false) {
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

function api_leads_submit_consultation_action(): void
{
    $redirectPath = page_url('register-consultation');
    api_require_post($redirectPath);

    $studentName = input_string($_POST, 'student_name');
    $parentName = input_string($_POST, 'parent_name');
    $fatherPhone = normalize_phone_string(input_string($_POST, 'father_phone'));
    $motherPhone = normalize_phone_string(input_string($_POST, 'mother_phone'));
    $legacyPhone = normalize_phone_string(input_string($_POST, 'parent_phone', input_string($_POST, 'phone')));
    $contactPhone = api_leads_extract_phone($fatherPhone . ' ' . $motherPhone . ' ' . $legacyPhone . ' ' . $parentName);

    if ($studentName === '' || $parentName === '' || $contactPhone === '') {
        set_flash('home_error', 'Vui long nhap ten hoc vien, ho ten phu huynh va it nhat mot so dien thoai lien he.');
        redirect($redirectPath);
    }

    $payload = [
        'student_name' => $studentName,
        'gender' => input_string($_POST, 'student_gender'),
        'dob' => input_string($_POST, 'student_dob'),
        'interests' => input_string($_POST, 'student_hobbies'),
        'personality' => input_string($_POST, 'student_personality'),
        'parent_name' => $parentName,
        'parent_phone' => $contactPhone,
        'school_name' => input_string($_POST, 'student_school'),
        'current_grade' => input_string($_POST, 'student_grade'),
        'referral_source' => implode(', ', array_filter((array) ($_POST['source_channels'] ?? []), static fn ($value) => trim((string) $value) !== '')),
        'current_level' => input_string($_POST, 'current_level'),
        'study_time' => implode(', ', array_filter(array_merge((array) ($_POST['available_shifts'] ?? []), (array) ($_POST['available_days'] ?? [])), static fn ($value) => trim((string) $value) !== '')),
        'parent_expectation' => implode(', ', array_filter((array) ($_POST['parent_expectations'] ?? []), static fn ($value) => trim((string) $value) !== '')),
    ];

    $otherChannel = trim((string) ($_POST['source_other_detail'] ?? ''));
    if ($otherChannel !== '') {
        $payload['referral_source'] = trim((string) ($payload['referral_source'] ?? ''));
        $payload['referral_source'] = $payload['referral_source'] !== '' ? $payload['referral_source'] . ', ' . $otherChannel : $otherChannel;
    }

    try {
        (new AdminModel())->saveConsultationLead($payload);
        set_flash('home_success', 'Yeu cau tu van da duoc ghi nhan. Trung tam se lien he voi ban som nhat.');
    } catch (Throwable $exception) {
        set_flash('home_error', 'Khong the gui yeu cau tu van. Vui long thu lai sau.');
    }

    redirect($redirectPath);
}

function api_leads_update_action(): void
{
    api_guard_permission('student_lead.update');
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
    api_guard_permission('student_lead.update');
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

function api_leads_delete_action(): void
{
    api_guard_permission('student_lead.delete');
    api_require_post(page_url('student-leads-manage'));

    $leadId = (int) ($_GET['id'] ?? 0);
    if ($leadId <= 0) {
        set_flash('error', 'Lead hoc vien khong hop le.');
        redirect(page_url('student-leads-manage'));
    }

    try {
        (new AdminModel())->deleteStudentLead($leadId);
        set_flash('success', 'Da xoa lead hoc vien.');
    } catch (Throwable $exception) {
        set_flash('error', 'Khong the xoa lead hoc vien nay.');
    }

    redirect(page_url('student-leads-manage'));
}
