<?php
require_any_permission(['academic.rooms.view']);

$academicModel = new AcademicModel();
$editingRoom = null;
if (!empty($_GET['edit'])) {
    $editingRoom = $academicModel->findRoom((int) $_GET['edit']);
}

$roomPage = max(1, (int) ($_GET['room_page'] ?? 1));
$roomPerPage = ui_pagination_resolve_per_page('room_per_page', 10);
$roomTotal = $academicModel->countRooms();
$roomTotalPages = max(1, (int) ceil($roomTotal / $roomPerPage));
if ($roomPage > $roomTotalPages) {
    $roomPage = $roomTotalPages;
}
$rooms = $academicModel->listRoomsPage($roomPage, $roomPerPage);
$roomPerPageOptions = ui_pagination_per_page_options();

$module = 'rooms';
$adminTitle = 'Quản lý phòng học';

$success = get_flash('success');
$error = get_flash('error');

$canCreateRoom = has_permission('academic.rooms.create');
$canUpdateRoom = has_permission('academic.rooms.update');
$canDeleteRoom = has_permission('academic.rooms.delete');
$canShowForm = $editingRoom ? $canUpdateRoom : ($canCreateRoom || $canUpdateRoom);
?>
<div class="grid gap-4">
    <?php if ($success): ?>
        <div class="rounded-xl border-l-4 border-emerald-500 bg-emerald-50 p-3 text-sm text-emerald-700"><?= e($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="rounded-xl border-l-4 border-rose-500 bg-rose-50 p-3 text-sm text-rose-700"><?= e($error); ?></div>
    <?php endif; ?>

    <?php if ($canShowForm): ?>
        <article class="order-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3><?= $editingRoom ? 'Sửa phòng học' : 'Thêm phòng học'; ?></h3>
            <form class="grid gap-3 md:grid-cols-2" method="post" action="/api/rooms/save" autocomplete="off">
                <?= csrf_input(); ?>
                <input type="hidden" name="id" value="<?= (int) ($editingRoom['id'] ?? 0); ?>">

                <label class="md:col-span-2">
                    Tên phòng học
                    <input type="text" name="room_name" value="<?= e((string) ($editingRoom['room_name'] ?? '')); ?>" required>
                </label>

                <div class="md:col-span-2 flex flex-wrap items-center gap-2">
                    <button class="<?= ui_btn_primary_classes(); ?>" type="submit">Lưu phòng học</button>
                    <?php if ($editingRoom): ?>
                        <a class="<?= ui_btn_secondary_classes(); ?>" href="<?= e(page_url('rooms-manage')); ?>">Tạo mới</a>
                    <?php endif; ?>
                </div>
            </form>
        </article>
    <?php endif; ?>

    <article class="order-1 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3>Danh sách phòng học</h3>
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full border-collapse text-sm" data-enable-row-detail="1">
                <thead>
                    <tr>
                        <th width="100">ID</th>
                        <th>Tên phòng học</th>
                        <th width="220">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rooms)): ?>
                        <tr>
                            <td colspan="3">
                                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">Chưa có phòng học nào.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rooms as $room): ?>
                            <?php $roomId = (int) ($room['id'] ?? 0); ?>
                            <tr>
                                <td><?= $roomId; ?></td>
                                <td><?= e((string) ($room['room_name'] ?? '')); ?></td>
                                <td>
                                    <span class="inline-flex flex-wrap items-center gap-2">
                                        <button
                                            type="button"
                                            class="admin-row-detail-button admin-action-icon-btn"
                                            data-action-kind="detail"
                                            data-admin-row-detail="1"
                                            data-detail-url="<?= e(page_url('rooms-manage', ['edit' => $roomId, 'room_page' => $roomPage, 'room_per_page' => $roomPerPage])); ?>"
                                            data-skip-action-icon="1"
                                            title="Xem chi tiết"
                                            aria-label="Xem chi tiết"
                                        >
                                            <span class="admin-action-icon-label">Xem chi tiết</span>
                                            <span class="admin-action-icon-glyph" aria-hidden="true">
                                                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"></circle><path d="M2 12s3.5-6.5 10-6.5S22 12 22 12s-3.5 6.5-10 6.5S2 12 2 12z"></path></svg>
                                            </span>
                                        </button>
                                        <?php if ($canUpdateRoom): ?>
                                            <a
                                                href="<?= e(page_url('rooms-manage', ['edit' => $roomId, 'room_page' => $roomPage, 'room_per_page' => $roomPerPage])); ?>"
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
                                        <?php endif; ?>
                                        <?php if ($canDeleteRoom): ?>
                                            <form class="inline-block" method="post" action="/api/rooms/delete?id=<?= $roomId; ?>" onsubmit="return confirm('Bạn có chắc muốn xóa phòng học này?');">
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
                                        <?php endif; ?>
                                        <?php if (!$canUpdateRoom && !$canDeleteRoom): ?>
                                            <span class="text-sm text-slate-500">Chỉ có quyền xem</span>
                                        <?php endif; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if ($roomTotal > 0): ?>
                <div class="border-t border-slate-200 bg-slate-50/80 px-3 py-2">
                    <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-slate-600">
                        <span class="font-medium">Trang <?= (int) $roomPage; ?>/<?= (int) $roomTotalPages; ?> - Tổng <?= (int) $roomTotal; ?> phòng học</span>
                        <div class="inline-flex items-center gap-1.5">
                            <form class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2 py-1" method="get" action="<?= e(page_url('rooms-manage')); ?>">
                                <input type="hidden" name="page" value="rooms-manage">
                                <label class="text-[11px] font-semibold text-slate-500" for="room-per-page">Số dòng</label>
                                <select id="room-per-page" name="room_per_page" class="h-7 rounded-md border border-slate-200 bg-white px-2 text-xs font-semibold text-slate-700" onchange="this.form.submit()">
                                    <?php foreach ($roomPerPageOptions as $option): ?>
                                        <option value="<?= (int) $option; ?>" <?= $roomPerPage === (int) $option ? 'selected' : ''; ?>><?= (int) $option; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>

                            <?php if ($roomPage > 1): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('rooms-manage', ['room_page' => $roomPage - 1, 'room_per_page' => $roomPerPage])); ?>">Trước</a>
                            <?php else: ?>
                                <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400">Trước</span>
                            <?php endif; ?>

                            <?php if ($roomPage < $roomTotalPages): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('rooms-manage', ['room_page' => $roomPage + 1, 'room_per_page' => $roomPerPage])); ?>">Sau</a>
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
