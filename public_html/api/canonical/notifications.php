<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/api_helpers.php';
require_once __DIR__ . '/../../models/AcademicModel.php';

function api_notifications_save_action(): void
{
    api_require_post(page_url('notifications-manage'));

    $notificationId = input_int($_POST, 'id');
    api_guard_permission($notificationId > 0 ? 'notifications.update' : 'notifications.create');
    $currentUser = auth_user() ?? [];

    $targetType = strtoupper(trim((string) ($_POST['target_type'] ?? '')));
    $targetId = 0;
    if ($targetType === 'USER') {
        $targetId = input_int($_POST, 'target_user_id');
    } elseif ($targetType === 'ROLE') {
        $targetId = input_int($_POST, 'target_role_id');
    } elseif ($targetType === 'CLASS') {
        $targetId = input_int($_POST, 'target_class_id');
    }
    $title = trim((string) ($_POST['title'] ?? ''));
    $message = trim((string) ($_POST['message'] ?? ''));
    $sendEmail = (int) ($_POST['send_email'] ?? 0) === 1;

    $redirectQuery = [];
    if ($notificationId > 0) {
        $redirectQuery['edit'] = $notificationId;
    }

    $allowedTargetTypes = ['ALL', 'ROLE', 'CLASS', 'USER'];
    if (!in_array($targetType, $allowedTargetTypes, true) || $title === '' || $message === '') {
        set_flash('error', 'Vui long chon doi tuong nhan va nhap day du tieu de, noi dung.');
        redirect(page_url('notifications-manage', $redirectQuery));
    }

    if ($targetType !== 'ALL' && $targetId <= 0) {
        set_flash('error', 'Vui long chon doi tuong nhan cu the cho thong bao.');
        redirect(page_url('notifications-manage', $redirectQuery));
    }

    try {
        (new AcademicModel())->saveNotification([
            'id' => $notificationId,
            'sender_id' => (int) ($currentUser['id'] ?? 0),
            'target_type' => $targetType,
            'target_id' => $targetType === 'ALL' ? null : $targetId,
            'title' => $title,
            'message' => $message,
            'send_email' => $sendEmail,
        ]);
    } catch (Throwable $exception) {
        set_flash('error', 'Khong the luu thong bao. Vui long thu lai.');
        redirect(page_url('notifications-manage', $redirectQuery));
    }

    set_flash('success', 'Da luu thong bao thanh cong.');
    redirect(page_url('notifications-manage'));
}

function api_notifications_edit_action(): void
{
    api_guard_permission('notifications.update');
    redirect(page_url('notifications-manage', ['edit' => (int) ($_GET['id'] ?? 0)]));
}

function api_notifications_delete_action(): void
{
    api_guard_permission('notifications.delete');
    api_require_post(page_url('notifications-manage'));

    $notificationId = (int) ($_GET['id'] ?? 0);
    if ($notificationId <= 0) {
        set_flash('error', 'Thong bao khong hop le.');
        redirect(page_url('notifications-manage'));
    }

    try {
        (new AcademicModel())->deleteNotification($notificationId);
        set_flash('success', 'Da xoa thong bao.');
    } catch (Throwable) {
        set_flash('error', 'Khong the xoa thong bao. Vui long thu lai.');
    }
    redirect(page_url('notifications-manage'));
}
