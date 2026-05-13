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
        set_flash('error', 'Vui lòng chọn đối tượng nhận và nhập đầy đủ tiêu đề, nội dung.');
        redirect(page_url('notifications-manage', $redirectQuery));
    }

    if ($targetType !== 'ALL' && $targetId <= 0) {
        set_flash('error', 'Vui lòng chọn đối tượng nhận cụ thể cho thông báo.');
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
        set_flash('error', 'Không thể lưu thông báo. Vui lòng thử lại.');
        redirect(page_url('notifications-manage', $redirectQuery));
    }

    set_flash('success', 'Đã lưu thông báo thành công.');
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
        set_flash('error', 'Thông báo không hợp lệ.');
        redirect(page_url('notifications-manage'));
    }

    try {
        (new AcademicModel())->deleteNotification($notificationId);
        set_flash('success', 'Đã xóa thông báo.');
    } catch (Throwable) {
        set_flash('error', 'Không thể xóa thông báo. Vui lòng thử lại.');
    }
    redirect(page_url('notifications-manage'));
}

function api_notifications_admin_feed_action(): void
{
    api_guard_login();
    if (!can_use_notification_bell()) {
        api_error('Bạn không có quyền xem thông báo.', ['code' => 'NOTIFICATION_BELL_FORBIDDEN'], 403);
    }

    $currentUser = auth_user() ?? [];
    $userId = (int) ($currentUser['id'] ?? 0);
    if ($userId <= 0) {
        api_error('Phiên đăng nhập không hợp lệ.', ['code' => 'INVALID_USER'], 401);
    }

    $limit = max(1, min(10, (int) ($_GET['limit'] ?? 6)));
    $academicModel = new AcademicModel();
    $notifications = $academicModel->listNotificationDropdownItems($userId, $limit);
    $unreadCount = $academicModel->countUnreadNotifications($userId);
    $moduleCounts = $academicModel->countUnreadNotificationsByModule($userId);

    $items = array_map(static function (array $notification): array {
        $notificationId = (int) ($notification['id'] ?? 0);
        $fullMessage = trim((string) ($notification['message'] ?? ''));
        $preview = $fullMessage;
        if ($preview !== '') {
            if (function_exists('mb_strimwidth')) {
                $preview = mb_strimwidth($preview, 0, 140, '...');
            } elseif (strlen($preview) > 140) {
                $preview = substr($preview, 0, 137) . '...';
            }
        }

        $actionUrl = trim((string) ($notification['action_url'] ?? ''));
        if ($actionUrl === '' && can_manage_notification_center()) {
            $actionUrl = page_url('notifications-manage', ['edit' => $notificationId]);
        }

        return [
            'id' => $notificationId,
            'title' => trim((string) ($notification['title'] ?? 'Thông báo hệ thống')),
            'message' => $preview,
            'is_read' => (int) ($notification['is_read'] ?? 0) === 1,
            'created_at' => (string) ($notification['created_at'] ?? ''),
            'created_at_display' => ui_format_datetime((string) ($notification['created_at'] ?? '')),
            'action_url' => $actionUrl,
        ];
    }, $notifications);

    api_success('OK', [
        'unread_count' => $unreadCount,
        'module_counts' => $moduleCounts,
        'items' => $items,
    ]);
}

function api_notifications_mark_read_action(): void
{
    api_guard_login();
    if (!can_use_notification_bell()) {
        api_error('Bạn không có quyền xem thông báo.', ['code' => 'NOTIFICATION_BELL_FORBIDDEN'], 403);
    }
    api_require_post(page_url('admin'));

    $currentUser = auth_user() ?? [];
    $userId = (int) ($currentUser['id'] ?? 0);
    $notificationId = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);

    if ($userId <= 0 || $notificationId <= 0) {
        api_error('Thông báo không hợp lệ.', ['code' => 'INVALID_NOTIFICATION'], 422);
    }

    (new AcademicModel())->markNotificationRead($notificationId, $userId);
    api_success('Đã đánh dấu đã đọc.', ['id' => $notificationId]);
}
