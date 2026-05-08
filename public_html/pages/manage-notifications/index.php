<?php
require_any_permission(['notifications.view']);

$academicModel = new AcademicModel();
$searchQuery = trim((string) ($_GET['search'] ?? ''));
$targetTypeFilter = strtoupper(trim((string) ($_GET['target_type'] ?? '')));
if (!in_array($targetTypeFilter, ['ALL', 'ROLE', 'CLASS', 'GROUP', 'USER'], true)) {
    $targetTypeFilter = '';
}
$notificationFilters = ['target_type' => $targetTypeFilter];
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
$targetLookups = $academicModel->notificationTargetLookups();
$recipientUsers = $targetLookups['users'] ?? [];
$recipientRoles = $targetLookups['roles'] ?? [];
$recipientClasses = $targetLookups['classes'] ?? [];

$editingTargetType = strtoupper(trim((string) ($editingNotification['target_type'] ?? 'ALL')));
if (!in_array($editingTargetType, ['ALL', 'ROLE', 'CLASS', 'USER'], true)) {
    $editingTargetType = 'ALL';
}
$editingTargetId = isset($editingNotification['target_id']) ? (int) $editingNotification['target_id'] : 0;

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
            <form class="grid gap-3 md:grid-cols-2" method="post" action="/api/notifications/save" autocomplete="off" data-notification-form="1">
                <?= csrf_input(); ?>
                <input type="hidden" name="id" value="<?= (int) ($editingNotification['id'] ?? 0); ?>">

                <label>
                    Loại đối tượng nhận
                    <select name="target_type" required data-notification-target-type="1">
                        <option value="ALL" <?= $editingTargetType === 'ALL' ? 'selected' : ''; ?>>Toàn hệ thống</option>
                        <option value="ROLE" <?= $editingTargetType === 'ROLE' ? 'selected' : ''; ?>>Theo vai trò</option>
                        <option value="CLASS" <?= $editingTargetType === 'CLASS' ? 'selected' : ''; ?>>Theo lớp</option>
                        <option value="USER" <?= $editingTargetType === 'USER' ? 'selected' : ''; ?>>Theo cá nhân</option>
                    </select>
                </label>


                <label data-target-select="ROLE" style="<?= $editingTargetType === 'ROLE' ? '' : 'display:none;'; ?>">
                    Vai trò nhận thông báo
                    <select name="target_role_id" <?= $editingTargetType === 'ROLE' ? 'required' : 'disabled'; ?>>
                        <option value="">-- Chọn vai trò --</option>
                        <?php foreach ($recipientRoles as $role): ?>
                            <?php $roleId = (int) ($role['id'] ?? 0); ?>
                            <option value="<?= $roleId; ?>" <?= $editingTargetType === 'ROLE' && $roleId === $editingTargetId ? 'selected' : ''; ?>>
                                <?= e((string) ($role['role_name'] ?? ('Vai trò #' . $roleId))); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label data-target-select="CLASS" style="<?= $editingTargetType === 'CLASS' ? '' : 'display:none;'; ?>">
                    Lớp nhận thông báo
                    <select name="target_class_id" <?= $editingTargetType === 'CLASS' ? 'required' : 'disabled'; ?>>
                        <option value="">-- Chọn lớp --</option>
                        <?php foreach ($recipientClasses as $class): ?>
                            <?php $classId = (int) ($class['id'] ?? 0); ?>
                            <option value="<?= $classId; ?>" <?= $editingTargetType === 'CLASS' && $classId === $editingTargetId ? 'selected' : ''; ?>>
                                <?= e((string) ($class['class_name'] ?? ('Lớp #' . $classId))); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label data-target-select="USER" style="<?= $editingTargetType === 'USER' ? '' : 'display:none;'; ?>">
                    Người nhận cụ thể
                    <select name="target_user_id" <?= $editingTargetType === 'USER' ? 'required' : 'disabled'; ?>>
                        <option value="">-- Chọn người nhận --</option>
                        <?php foreach ($recipientUsers as $recipient): ?>
                            <?php
                            $recipientId = (int) ($recipient['id'] ?? 0);
                            $recipientName = user_dropdown_label($recipient, 'Người dùng #' . $recipientId);
                            ?>
                            <option value="<?= $recipientId; ?>" <?= $editingTargetType === 'USER' && $recipientId === $editingTargetId ? 'selected' : ''; ?>>
                                <?= e($recipientName); ?>
                            </option>
                        <?php endforeach; ?>
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
                        <a class="<?= ui_btn_secondary_classes(); ?>" href="<?= e(page_url('notifications-manage', ['notification_page' => $notificationPage, 'notification_per_page' => $notificationPerPage, 'search' => $searchQuery, 'target_type' => $targetTypeFilter])); ?>">Tạo mới</a>
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
                        placeholder="Tìm người gửi, đối tượng, tiêu đề, nội dung..."
                        class="h-10 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                    >
                </label>
                <select name="target_type" data-ajax-filter="1" class="h-10 rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                    <option value="">Tất cả đối tượng</option>
                    <option value="ALL" <?= $targetTypeFilter === 'ALL' ? 'selected' : ''; ?>>Toàn hệ thống</option>
                    <option value="ROLE" <?= $targetTypeFilter === 'ROLE' ? 'selected' : ''; ?>>Theo vai trò</option>
                    <option value="CLASS" <?= $targetTypeFilter === 'CLASS' ? 'selected' : ''; ?>>Theo lớp</option>
                    <option value="USER" <?= $targetTypeFilter === 'USER' ? 'selected' : ''; ?>>Theo cá nhân</option>
                </select>
            </div>
        </div>
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full border-collapse text-sm" data-enable-row-detail="1" data-disable-global-filter="1">
                <thead>
                    <tr>
                        <th>Người gửi</th>
                        <th>Đối tượng nhận</th>
                        <th>Tiêu đề</th>
                        <th>Nội dung</th>
                        <th>Đã đọc</th>
                        <th>Tạo lúc</th>
                        <th width="220">Hành động</th>
                    </tr>
                </thead>
                <tbody data-ajax-tbody="1">
                    <?php if (empty($notifications)): ?>
                        <tr>
                            <td colspan="7">
                                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">Chưa có thông báo nào.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($notifications as $notification): ?>
                            <?php
                            $notificationId = (int) ($notification['id'] ?? 0);
                            $senderLabel = trim((string) ($notification['sender_name'] ?? ''));
                            if ($senderLabel === '') {
                                $senderLabel = trim((string) ($notification['sender_username'] ?? ''));
                            }
                            if ($senderLabel === '') {
                                $senderLabel = 'Hệ thống';
                            } else {
                                $senderLabel = user_dropdown_label([
                                    'full_name' => (string) ($notification['sender_name'] ?? $senderLabel),
                                    'teacher_code' => (string) ($notification['sender_teacher_code'] ?? ''),
                                    'student_code' => (string) ($notification['sender_student_code'] ?? ''),
                                ], $senderLabel);
                            }
                            $fullMessage = (string) ($notification['message'] ?? '');
                            $totalRecipients = max(0, (int) ($notification['total_recipients'] ?? 0));
                            $readCount = max(0, (int) ($notification['read_count'] ?? 0));
                            ?>
                            <tr>
                                <td><?= e($senderLabel); ?></td>
                                <td><?= e((string) ($notification['target_summary'] ?? 'Chưa xác định')); ?></td>
                                <td><?= e((string) ($notification['title'] ?? '')); ?></td>
                                <td><span data-full-value="<?= e($fullMessage); ?>"><?= e((string) substr($fullMessage, 0, 80)); ?></span></td>
                                <td>
                                    <span class="inline-flex items-center rounded-full border border-sky-200 bg-sky-50 px-2.5 py-1 text-xs font-bold text-sky-700">
                                        <?= $readCount; ?>/<?= $totalRecipients; ?>
                                    </span>
                                </td>
                                <td><?= e(ui_format_datetime((string) ($notification['created_at'] ?? ''))); ?></td>
                                <td>
                                    <span class="inline-flex flex-wrap items-center gap-2">
                                        <button
                                            type="button"
                                            class="admin-row-detail-button admin-action-icon-btn"
                                            data-action-kind="detail"
                                            data-admin-row-detail="1"
                                            data-detail-url="<?= e(page_url('notifications-manage', ['edit' => $notificationId, 'notification_page' => $notificationPage, 'notification_per_page' => $notificationPerPage, 'search' => $searchQuery, 'target_type' => $targetTypeFilter])); ?>"
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
                                            href="<?= e(page_url('notifications-manage', ['edit' => $notificationId, 'notification_page' => $notificationPage, 'notification_per_page' => $notificationPerPage, 'search' => $searchQuery, 'target_type' => $targetTypeFilter])); ?>"
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
                                <input type="hidden" name="target_type" value="<?= e($targetTypeFilter); ?>">
                                <label class="text-[11px] font-semibold text-slate-500" for="notification-per-page">Số dòng</label>
                                <select id="notification-per-page" name="notification_per_page" data-ajax-per-page="1" class="h-7 rounded-md border border-slate-200 bg-white px-2 text-xs font-semibold text-slate-700">
                                    <?php foreach ($notificationPerPageOptions as $option): ?>
                                        <option value="<?= (int) $option; ?>" <?= $notificationPerPage === (int) $option ? 'selected' : ''; ?>><?= (int) $option; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>

                            <?php if ($notificationPage > 1): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('notifications-manage', ['notification_page' => $notificationPage - 1, 'notification_per_page' => $notificationPerPage, 'search' => $searchQuery, 'target_type' => $targetTypeFilter])); ?>">Trước</a>
                            <?php else: ?>
                                <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400">Trước</span>
                            <?php endif; ?>

                            <?php if ($notificationPage < $notificationTotalPages): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('notifications-manage', ['notification_page' => $notificationPage + 1, 'notification_per_page' => $notificationPerPage, 'search' => $searchQuery, 'target_type' => $targetTypeFilter])); ?>">Sau</a>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    function syncTargetFields(form) {
        if (!(form instanceof HTMLElement)) {
            return;
        }

        const typeSelect = form.querySelector('[data-notification-target-type="1"]');
        const targetBlocks = form.querySelectorAll('[data-target-select]');
        if (!(typeSelect instanceof HTMLSelectElement) || targetBlocks.length === 0) {
            return;
        }

        const selectedType = typeSelect ? typeSelect.value : 'ALL';

        targetBlocks.forEach(function (block) {
            const blockType = block.getAttribute('data-target-select');
            const isActive = blockType === selectedType;
            block.style.display = isActive ? '' : 'none';

            block.querySelectorAll('select').forEach(function (select) {
                select.disabled = !isActive;
                if (isActive) {
                    select.setAttribute('required', 'required');
                } else {
                    select.removeAttribute('required');
                }

                if (select.tomselect) {
                    if (isActive) {
                        select.tomselect.enable();
                        select.tomselect.sync();
                    } else {
                        select.tomselect.disable();
                        select.tomselect.close();
                    }
                }
            });
        });
    }

    function initNotificationForms(root) {
        const scope = root instanceof HTMLElement || root instanceof Document ? root : document;
        scope.querySelectorAll('[data-notification-form="1"]').forEach(function (form) {
            syncTargetFields(form);
        });
    }

    document.addEventListener('change', function (event) {
        const target = event.target;
        if (!(target instanceof HTMLElement)) {
            return;
        }

        if (!target.matches('[data-notification-target-type="1"]')) {
            return;
        }

        const form = target.closest('[data-notification-form="1"]');
        if (form) {
            syncTargetFields(form);
        }
    });

    initNotificationForms(document);

    if (document.body instanceof HTMLElement && typeof MutationObserver !== 'undefined') {
        const observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                mutation.addedNodes.forEach(function (node) {
                    if (!(node instanceof HTMLElement)) {
                        return;
                    }

                    if (node.matches('[data-notification-form="1"]')) {
                        syncTargetFields(node);
                        return;
                    }

                    if (node.querySelector('[data-notification-form="1"]')) {
                        initNotificationForms(node);
                    }
                });
            });
        });

        observer.observe(document.body, { childList: true, subtree: true });
    }
});
</script>
