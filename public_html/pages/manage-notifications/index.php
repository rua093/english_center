<?php
require_any_permission(['notifications.view']);

$academicModel = new AcademicModel();
$editingNotification = null;
if (!empty($_GET['edit'])) {
    $editingNotification = $academicModel->findNotification((int) $_GET['edit']);
}

$notificationPage = max(1, (int) ($_GET['notification_page'] ?? 1));
$notificationPerPage = ui_pagination_resolve_per_page('notification_per_page', 10);
$notificationTotal = $academicModel->countNotifications();
$notificationTotalPages = max(1, (int) ceil($notificationTotal / $notificationPerPage));
if ($notificationPage > $notificationTotalPages) {
    $notificationPage = $notificationTotalPages;
}
$notifications = $academicModel->listNotificationsPage($notificationPage, $notificationPerPage);
$notificationPerPageOptions = ui_pagination_per_page_options();
$recipientUsers = $academicModel->notificationRecipientLookups();

$module = 'notifications';
$adminTitle = 'Quan ly thong bao';

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
            <h3><?= $editingNotification ? 'Sua thong bao' : 'Them thong bao'; ?></h3>
            <form class="grid gap-3 md:grid-cols-2" method="post" action="/api/notifications/save" autocomplete="off">
                <?= csrf_input(); ?>
                <input type="hidden" name="id" value="<?= (int) ($editingNotification['id'] ?? 0); ?>">

                <label>
                    Nguoi nhan
                    <select name="user_id" required>
                        <option value="">-- Chon nguoi nhan --</option>
                        <?php foreach ($recipientUsers as $recipient): ?>
                            <?php
                            $recipientId = (int) ($recipient['id'] ?? 0);
                            $recipientName = trim((string) ($recipient['full_name'] ?? ''));
                            ?>
                            <option value="<?= $recipientId; ?>" <?= $recipientId === (int) ($editingNotification['user_id'] ?? 0) ? 'selected' : ''; ?>>
                                <?= e($recipientName !== '' ? $recipientName : ('User #' . $recipientId)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    Trang thai da doc
                    <select name="is_read">
                        <option value="0" <?= ((int) ($editingNotification['is_read'] ?? 0) === 0) ? 'selected' : ''; ?>>Chua doc</option>
                        <option value="1" <?= ((int) ($editingNotification['is_read'] ?? 0) === 1) ? 'selected' : ''; ?>>Da doc</option>
                    </select>
                </label>

                <label class="md:col-span-2">
                    Tieu de
                    <input type="text" name="title" value="<?= e((string) ($editingNotification['title'] ?? '')); ?>" required>
                </label>

                <label class="md:col-span-2">
                    Noi dung
                    <textarea name="message" rows="4" required><?= e((string) ($editingNotification['message'] ?? '')); ?></textarea>
                </label>

                <div class="md:col-span-2 flex flex-wrap items-center gap-2">
                    <button class="<?= ui_btn_primary_classes(); ?>" type="submit">Luu thong bao</button>
                    <?php if ($editingNotification): ?>
                        <a class="<?= ui_btn_secondary_classes(); ?>" href="<?= e(page_url('notifications-manage')); ?>">Tao moi</a>
                    <?php endif; ?>
                </div>
            </form>
        </article>
    <?php endif; ?>

    <article class="order-1 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3>Danh sach thong bao</h3>
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full border-collapse text-sm" data-enable-row-detail="1">
                <thead>
                    <tr>
                        <th>Nguoi nhan</th>
                        <th>Tieu de</th>
                        <th>Noi dung</th>
                        <th>Trang thai</th>
                        <th>Tao luc</th>
                        <th width="220">Hanh dong</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($notifications)): ?>
                        <tr>
                            <td colspan="6">
                                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">Chua co thong bao nao.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($notifications as $notification): ?>
                            <?php
                            $notificationId = (int) ($notification['id'] ?? 0);
                            $recipientName = trim((string) ($notification['full_name'] ?? ''));
                            $recipientUsername = trim((string) ($notification['username'] ?? ''));
                            $recipientLabel = $recipientName !== '' ? $recipientName : ('User #' . (int) ($notification['user_id'] ?? 0));
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
                                        <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">Da doc</span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700">Chua doc</span>
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
                                            data-detail-url="<?= e(page_url('notifications-manage', ['edit' => $notificationId, 'notification_page' => $notificationPage, 'notification_per_page' => $notificationPerPage])); ?>"
                                            data-skip-action-icon="1"
                                            title="Xem chi tiet"
                                            aria-label="Xem chi tiet"
                                        >
                                            <span class="admin-action-icon-label">Xem chi tiet</span>
                                            <span class="admin-action-icon-glyph" aria-hidden="true">
                                                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"></circle><path d="M2 12s3.5-6.5 10-6.5S22 12 22 12s-3.5 6.5-10 6.5S2 12 2 12z"></path></svg>
                                            </span>
                                        </button>
                                        <a
                                            href="<?= e(page_url('notifications-manage', ['edit' => $notificationId, 'notification_page' => $notificationPage, 'notification_per_page' => $notificationPerPage])); ?>"
                                            class="admin-action-icon-btn"
                                            data-action-kind="edit"
                                            data-skip-action-icon="1"
                                            title="Sua"
                                            aria-label="Sua"
                                        >
                                            <span class="admin-action-icon-label">Sua</span>
                                            <span class="admin-action-icon-glyph" aria-hidden="true">
                                                <svg viewBox="0 0 24 24"><path d="M12 20h9"></path><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"></path></svg>
                                            </span>
                                        </a>
                                        <form class="inline-block" method="post" action="/api/notifications/delete?id=<?= $notificationId; ?>" onsubmit="return confirm('Ban co chac muon xoa thong bao nay?');">
                                            <?= csrf_input(); ?>
                                            <button
                                                class="<?= ui_btn_danger_classes('sm'); ?> admin-action-icon-btn"
                                                data-action-kind="delete"
                                                data-skip-action-icon="1"
                                                type="submit"
                                                title="Xoa"
                                                aria-label="Xoa"
                                            >
                                                <span class="admin-action-icon-label">Xoa</span>
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
                <div class="border-t border-slate-200 bg-slate-50/80 px-3 py-2">
                    <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-slate-600">
                        <span class="font-medium">Trang <?= (int) $notificationPage; ?>/<?= (int) $notificationTotalPages; ?> - Tong <?= (int) $notificationTotal; ?> thong bao</span>
                        <div class="inline-flex items-center gap-1.5">
                            <form class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2 py-1" method="get" action="<?= e(page_url('notifications-manage')); ?>">
                                <input type="hidden" name="page" value="notifications-manage">
                                <label class="text-[11px] font-semibold text-slate-500" for="notification-per-page">So dong</label>
                                <select id="notification-per-page" name="notification_per_page" class="h-7 rounded-md border border-slate-200 bg-white px-2 text-xs font-semibold text-slate-700" onchange="this.form.submit()">
                                    <?php foreach ($notificationPerPageOptions as $option): ?>
                                        <option value="<?= (int) $option; ?>" <?= $notificationPerPage === (int) $option ? 'selected' : ''; ?>><?= (int) $option; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>

                            <?php if ($notificationPage > 1): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('notifications-manage', ['notification_page' => $notificationPage - 1, 'notification_per_page' => $notificationPerPage])); ?>">Truoc</a>
                            <?php else: ?>
                                <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400">Truoc</span>
                            <?php endif; ?>

                            <?php if ($notificationPage < $notificationTotalPages): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('notifications-manage', ['notification_page' => $notificationPage + 1, 'notification_per_page' => $notificationPerPage])); ?>">Sau</a>
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
