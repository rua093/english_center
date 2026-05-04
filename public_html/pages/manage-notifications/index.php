<?php
require_any_permission(['notifications.view']);

$academicModel = new AcademicModel();
$searchQuery = trim((string) ($_GET['search'] ?? ''));
$isReadFilter = trim((string) ($_GET['is_read'] ?? ''));
if ($isReadFilter !== '0' && $isReadFilter !== '1') {
    $isReadFilter = '';
}
$notificationFilters = ['is_read' => $isReadFilter];
$editingNotification = null;
if (!empty($_GET['edit'])) {
    $editingNotification = $academicModel->findNotification((int) $_GET['edit']);
}

$notificationPage = max(1, (int) ($_GET['notification_page'] ?? 1));
$notificationPerPage = ui_pagination_resolve_per_page('notification_per_page', 10);
$notificationTotal = $academicModel->countNotifications($searchQuery, $notificationFilters);
$notificationTotalPages = max(1, (int) ceil($notificationTotal / $notificationPerPage));
if ($notificationPage > $notificationTotalPages) {
    $notificationPage = $notificationTotalPages;
}
$notifications = $academicModel->listNotificationsPage($notificationPage, $notificationPerPage, $searchQuery, $notificationFilters);
$notificationPerPageOptions = ui_pagination_per_page_options();
$recipientUsers = $academicModel->notificationRecipientLookups();

$module = 'notifications';
$adminTitle = 'Quản lý thông báo';

$success = get_flash('success');
$error = get_flash('error');

$canManageNotifications = has_any_permission(['notifications.create', 'notifications.update']);
?>
<div class="grid gap-4">
    <?php if ($success): ?>
        <div class="rounded-xl border-l-4 border-emerald-500 bg-emerald-50 p-3 text-sm text-emerald-700"><?= e($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="rounded-xl border-l-4 border-rose-500 bg-rose-50 p-3 text-sm text-rose-700"><?= e($error); ?></div>
    <?php endif; ?>

    <?php if ($canManageNotifications): ?>
        <article class="order-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3><?= $editingNotification ? 'Sửa thông báo' : 'Thêm thông báo'; ?></h3>
            <form class="grid gap-3 md:grid-cols-2" method="post" action="/api/notifications/save" autocomplete="off">
                <?= csrf_input(); ?>
                <input type="hidden" name="id" value="<?= (int) ($editingNotification['id'] ?? 0); ?>">

                <label>
                    Người nhận
                    <select name="user_id" required>
                        <option value="">-- Chọn người nhận --</option>
                        <?php foreach ($recipientUsers as $recipient): ?>
                            <?php
                            $recipientId = (int) ($recipient['id'] ?? 0);
                            $recipientName = user_dropdown_label($recipient, 'Người dùng #' . $recipientId);
                            ?>
                            <option value="<?= $recipientId; ?>" <?= $recipientId === (int) ($editingNotification['user_id'] ?? 0) ? 'selected' : ''; ?>>
                                <?= e($recipientName); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    Trạng thái đã đọc
                    <select name="is_read">
                        <option value="0" <?= ((int) ($editingNotification['is_read'] ?? 0) === 0) ? 'selected' : ''; ?>>Chưa đọc</option>
                        <option value="1" <?= ((int) ($editingNotification['is_read'] ?? 0) === 1) ? 'selected' : ''; ?>>Đã đọc</option>
                    </select>
                </label>

                <label class="md:col-span-2">
                    Tiêu đề
                    <input type="text" name="title" value="<?= e((string) ($editingNotification['title'] ?? '')); ?>" required>
                </label>

                <label class="md:col-span-2">
                    Nội dung
                    <textarea name="message" rows="4" required><?= e((string) ($editingNotification['message'] ?? '')); ?></textarea>
                </label>

                <div class="md:col-span-2 flex flex-wrap items-center gap-2">
                    <button class="<?= ui_btn_primary_classes(); ?>" type="submit">Lưu thông báo</button>
                    <?php if ($editingNotification): ?>
                        <a class="<?= ui_btn_secondary_classes(); ?>" href="<?= e(page_url('notifications-manage', ['notification_page' => $notificationPage, 'notification_per_page' => $notificationPerPage, 'search' => $searchQuery, 'is_read' => $isReadFilter])); ?>">Tạo mới</a>
                    <?php endif; ?>
                </div>
            </form>
        </article>
    <?php endif; ?>

    <article
        class="order-1 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
        data-ajax-table-root="1"
        data-ajax-page-key="page"
        data-ajax-page-value="notifications-manage"
        data-ajax-page-param="notification_page"
        data-ajax-search-param="search"
    >
        <h3>Danh sách thông báo</h3>
        <div class="mb-3 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div class="flex flex-1 flex-col gap-3 md:flex-row md:items-center">
                <label class="relative block w-full md:max-w-sm">
                    <span class="pointer-events-none absolute inset-y-0 left-3 inline-flex items-center text-slate-400">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="7"></circle>
                            <path d="m20 20-3.5-3.5"></path>
                        </svg>
                    </span>
                    <input
                        type="search"
                        value="<?= e($searchQuery); ?>"
                        data-ajax-search="1"
                        placeholder="Tìm người nhận, tiêu đề, nội dung..."
                        class="h-10 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                    >
                </label>
                <select name="is_read" data-ajax-filter="1" class="h-10 rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                    <option value="">Tất cả trạng thái</option>
                    <option value="0" <?= $isReadFilter === '0' ? 'selected' : ''; ?>>Chưa đọc</option>
                    <option value="1" <?= $isReadFilter === '1' ? 'selected' : ''; ?>>Đã đọc</option>
                </select>
            </div>
        </div>
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full border-collapse text-sm" data-enable-row-detail="1" data-disable-global-filter="1">
                <thead>
                    <tr>
                        <th>Người nhận</th>
                        <th>Tiêu đề</th>
                        <th>Nội dung</th>
                        <th>Trạng thái</th>
                        <th>Tạo lúc</th>
                        <th width="220">Hành động</th>
                    </tr>
                </thead>
                <tbody data-ajax-tbody="1">
                    <?php if (empty($notifications)): ?>
                        <tr>
                            <td colspan="6">
                                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">Chưa có thông báo nào.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($notifications as $notification): ?>
                            <?php
                            $notificationId = (int) ($notification['id'] ?? 0);
                            $recipientName = user_display_name($notification, 'Người dùng #' . (int) ($notification['user_id'] ?? 0));
                            $recipientUsername = trim((string) ($notification['username'] ?? ''));
                            $recipientLabel = $recipientName !== '' ? $recipientName : ('Người dùng #' . (int) ($notification['user_id'] ?? 0));
                            if ($recipientUsername !== '') {
                                $recipientLabel .= ' (' . $recipientUsername . ')';
                            }
                            $fullMessage = (string) ($notification['message'] ?? '');
                            ?>
                            <tr>
                                <td><?= e($recipientLabel); ?></td>
                                <td><?= e((string) ($notification['title'] ?? '')); ?></td>
                                <td><span data-full-value="<?= e($fullMessage); ?>"><?= e((string) substr($fullMessage, 0, 80)); ?></span></td>
                                <td>
                                    <?php if ((int) ($notification['is_read'] ?? 0) === 1): ?>
                                        <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">Đã đọc</span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700">Chưa đọc</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= e((string) ($notification['created_at'] ?? '')); ?></td>
                                <td>
                                    <span class="inline-flex flex-wrap items-center gap-2">
                                        <button
                                            type="button"
                                            class="admin-row-detail-button admin-action-icon-btn"
                                            data-action-kind="detail"
                                            data-admin-row-detail="1"
                                            data-detail-url="<?= e(page_url('notifications-manage', ['edit' => $notificationId, 'notification_page' => $notificationPage, 'notification_per_page' => $notificationPerPage, 'search' => $searchQuery, 'is_read' => $isReadFilter])); ?>"
                                            data-skip-action-icon="1"
                                            title="Xem chi tiết"
                                            aria-label="Xem chi tiết"
                                        >
                                            <span class="admin-action-icon-label">Xem chi tiết</span>
                                            <span class="admin-action-icon-glyph" aria-hidden="true">
                                                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"></circle><path d="M2 12s3.5-6.5 10-6.5S22 12 22 12s-3.5 6.5-10 6.5S2 12 2 12z"></path></svg>
                                            </span>
                                        </button>
                                        <a
                                            href="<?= e(page_url('notifications-manage', ['edit' => $notificationId, 'notification_page' => $notificationPage, 'notification_per_page' => $notificationPerPage, 'search' => $searchQuery, 'is_read' => $isReadFilter])); ?>"
                                            class="admin-action-icon-btn"
                                            data-action-kind="edit"
                                            data-skip-action-icon="1"
                                            title="Sửa"
                                            aria-label="Sửa"
                                        >
                                            <span class="admin-action-icon-label">Sửa</span>
                                            <span class="admin-action-icon-glyph" aria-hidden="true">
                                                <svg viewBox="0 0 24 24"><path d="M12 20h9"></path><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"></path></svg>
                                            </span>
                                        </a>
                                        <form class="inline-block" method="post" action="/api/notifications/delete?id=<?= $notificationId; ?>" onsubmit="return confirm('Bạn có chắc muốn xóa thông báo này?');">
                                            <?= csrf_input(); ?>
                                            <button
                                                class="<?= ui_btn_danger_classes('sm'); ?> admin-action-icon-btn"
                                                data-action-kind="delete"
                                                data-skip-action-icon="1"
                                                type="submit"
                                                title="Xóa"
                                                aria-label="Xóa"
                                            >
                                                <span class="admin-action-icon-label">Xóa</span>
                                                <span class="admin-action-icon-glyph" aria-hidden="true">
                                                    <svg viewBox="0 0 24 24"><path d="M3 6h18"></path><path d="M8 6V4h8v2"></path><path d="M19 6l-1 14H6L5 6"></path><path d="M10 11v6"></path><path d="M14 11v6"></path></svg>
                                                </span>
                                            </button>
                                        </form>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if ($notificationTotal > 0): ?>
                <div class="border-t border-slate-200 bg-slate-50/80 px-3 py-2" data-ajax-pagination="1">
                    <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-slate-600">
                        <span class="min-w-0 flex-1 font-medium" data-ajax-row-info="1">Trang <?= (int) $notificationPage; ?>/<?= (int) $notificationTotalPages; ?> - Tổng <?= (int) $notificationTotal; ?> thông báo</span>
                        <div class="ml-auto inline-flex items-center gap-1.5">
                            <form class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2 py-1" method="get" action="<?= e(page_url('notifications-manage')); ?>">
                                <input type="hidden" name="page" value="notifications-manage">
                                <input type="hidden" name="search" value="<?= e($searchQuery); ?>">
                                <input type="hidden" name="is_read" value="<?= e($isReadFilter); ?>">
                                <label class="text-[11px] font-semibold text-slate-500" for="notification-per-page">Số dòng</label>
                                <select id="notification-per-page" name="notification_per_page" data-ajax-per-page="1" class="h-7 rounded-md border border-slate-200 bg-white px-2 text-xs font-semibold text-slate-700">
                                    <?php foreach ($notificationPerPageOptions as $option): ?>
                                        <option value="<?= (int) $option; ?>" <?= $notificationPerPage === (int) $option ? 'selected' : ''; ?>><?= (int) $option; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>

                            <?php if ($notificationPage > 1): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('notifications-manage', ['notification_page' => $notificationPage - 1, 'notification_per_page' => $notificationPerPage, 'search' => $searchQuery, 'is_read' => $isReadFilter])); ?>">Trước</a>
                            <?php else: ?>
                                <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400">Trước</span>
                            <?php endif; ?>

                            <?php if ($notificationPage < $notificationTotalPages): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('notifications-manage', ['notification_page' => $notificationPage + 1, 'notification_per_page' => $notificationPerPage, 'search' => $searchQuery, 'is_read' => $isReadFilter])); ?>">Sau</a>
                            <?php else: ?>
                                <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400">Sau</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </article>
</div>
