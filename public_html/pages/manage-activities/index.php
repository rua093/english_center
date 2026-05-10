<?php
require_admin_or_staff();
require_any_permission(['activity.view']);
require_once __DIR__ . '/../../core/file_storage.php';

$academicModel = new AcademicModel();
$searchQuery = trim((string) ($_GET['search'] ?? ''));
$activityStatusFilter = strtolower(trim((string) ($_GET['status'] ?? '')));
if (!in_array($activityStatusFilter, ['upcoming', 'ongoing', 'finished'], true)) {
    $activityStatusFilter = '';
}
$activityFilters = ['status' => $activityStatusFilter];
$editingActivity = null;
if (!empty($_GET['edit'])) {
    $editingActivity = $academicModel->findActivity((int) $_GET['edit']);
}

$activityPage = max(1, (int) ($_GET['activity_page'] ?? 1));
$activityPerPage = ui_pagination_resolve_per_page('activity_per_page', 10);
$activityTotal = $academicModel->countActivities($searchQuery, $activityFilters);
$activityTotalPages = max(1, (int) ceil($activityTotal / $activityPerPage));
if ($activityPage > $activityTotalPages) {
    $activityPage = $activityTotalPages;
}
$activities = $academicModel->listActivitiesPage($activityPage, $activityPerPage, $searchQuery, $activityFilters);
$activityPerPageOptions = ui_pagination_per_page_options();
$registrationLookups = $academicModel->registrationLookups();
$registrationStudents = is_array($registrationLookups['students'] ?? null) ? $registrationLookups['students'] : [];
$selectedRegistrationActivityId = max(0, (int) ($_GET['registrations_activity'] ?? 0));
$selectedRegistrationStudentId = max(0, (int) ($_GET['registration_student'] ?? 0));
$selectedRegistrationActivity = null;
$selectedRegistrations = [];
$editingRegistration = null;
$availableRegistrationStudents = $registrationStudents;
if ($selectedRegistrationActivityId > 0) {
    $selectedRegistrationActivity = $academicModel->findActivity($selectedRegistrationActivityId);
    if (is_array($selectedRegistrationActivity)) {
        $selectedRegistrations = $academicModel->listActivityRegistrations($selectedRegistrationActivityId);
        $registeredStudentIds = [];
        foreach ($selectedRegistrations as $registrationRow) {
            $registeredStudentIds[(int) ($registrationRow['user_id'] ?? 0)] = true;
        }
        $availableRegistrationStudents = array_values(array_filter(
            $registrationStudents,
            static function (array $studentRow) use ($registeredStudentIds): bool {
                $studentId = (int) ($studentRow['id'] ?? 0);
                return $studentId > 0 && !isset($registeredStudentIds[$studentId]);
            }
        ));
        if ($selectedRegistrationStudentId > 0) {
            foreach ($selectedRegistrations as $registrationRow) {
                if ((int) ($registrationRow['user_id'] ?? 0) === $selectedRegistrationStudentId) {
                    $editingRegistration = $registrationRow;
                    break;
                }
            }
        }
    } else {
        $selectedRegistrationActivityId = 0;
    }
}

$module = 'activities';
$adminTitle = 'Quản lý hoạt động';

$success = get_flash('success');
$error = get_flash('error');

$editingThumbnailUrl = normalize_public_file_url((string) ($editingActivity['image_thumbnail'] ?? ''));
?>
<style>
    #activity-registration-list {
        scroll-margin-top: 2rem;
    }
</style>
<div class="grid gap-4">
    <?php
    $canCreateActivity = has_permission('activity.create');
    $canUpdateActivity = has_permission('activity.update');
    $canDeleteActivity = has_permission('activity.delete');
    ?>

    <?php if ($success): ?>
        <div class="rounded-xl border-l-4 border-emerald-500 bg-emerald-50 p-3 text-sm text-emerald-700"><?= e($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="rounded-xl border-l-4 border-rose-500 bg-rose-50 p-3 text-sm text-rose-700"><?= e($error); ?></div>
    <?php endif; ?>

    <?php if ($canCreateActivity || $canUpdateActivity): ?>
        <article class="order-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3><?= $editingActivity ? 'Sửa hoạt động' : 'Thêm hoạt động'; ?></h3>
            <form class="grid gap-3 md:grid-cols-2" method="post" action="/api/activities/save" enctype="multipart/form-data">
                <?= csrf_input(); ?>
                <input type="hidden" name="id" value="<?= (int) ($editingActivity['id'] ?? 0); ?>">
                <input type="hidden" name="existing_image_thumbnail" value="<?= e((string) ($editingActivity['image_thumbnail'] ?? '')); ?>">
                <label>
                    Tên hoạt động
                    <input type="text" name="activity_name" value="<?= e((string) ($editingActivity['activity_name'] ?? '')); ?>" required>
                </label>
                <div class="md:col-span-2">
                    <label for="activity-description">Mô tả</label>
                    <?= render_bbcode_editor('description', (string) ($editingActivity['description'] ?? ''), ['id' => 'activity-description', 'rows' => 4]); ?>
                </div>
                <div class="md:col-span-2">
                    <label for="activity-content">Nội dung chi tiết</label>
                    <?= render_bbcode_editor('content', (string) ($editingActivity['content'] ?? ''), ['id' => 'activity-content', 'rows' => 5]); ?>
                </div>
                <label>
                    Địa điểm
                    <input type="text" name="location" value="<?= e((string) ($editingActivity['location'] ?? '')); ?>" placeholder="Ví dụ: Cơ sở A - Phòng 203">
                </label>
                <label>
                    Ngày bắt đầu
                    <input type="date" name="start_date" value="<?= e((string) ($editingActivity['start_date'] ?? '')); ?>" required>
                </label>
                <label>
                    Trạng thái
                    <select name="status">
                        <option value="upcoming" <?= (($editingActivity['status'] ?? 'upcoming') === 'upcoming') ? 'selected' : ''; ?>>upcoming</option>
                        <option value="ongoing" <?= (($editingActivity['status'] ?? '') === 'ongoing') ? 'selected' : ''; ?>>ongoing</option>
                        <option value="finished" <?= (($editingActivity['status'] ?? '') === 'finished') ? 'selected' : ''; ?>>finished</option>
                    </select>
                </label>
                <label>
                    Phí tham gia
                    <input type="number" step="1000" min="0" name="fee" value="<?= (float) ($editingActivity['fee'] ?? 0); ?>">
                </label>
                <label>
                    Ảnh thumbnail
                    <input type="file" name="activity_thumbnail" accept=".jpg,.jpeg,.png,.gif,.webp">
                </label>
                <?php if ($editingThumbnailUrl !== ''): ?>
                    <div class="md:col-span-2 flex flex-wrap items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm text-slate-600">
                        <img class="h-16 w-24 rounded-lg border border-slate-200 object-cover" src="<?= e($editingThumbnailUrl); ?>" alt="Thumbnail hoạt động">
                        <p>Ảnh hiện tại: <a class="font-semibold text-blue-700 hover:underline" href="<?= e($editingThumbnailUrl); ?>" target="_blank" rel="noopener noreferrer">Mở ảnh</a>. Tải ảnh mới để thay thế.</p>
                    </div>
                <?php endif; ?>
                <div class="md:col-span-2">
                    <button class="<?= ui_btn_primary_classes(); ?>" type="submit">Lưu hoạt động</button>
                </div>
            </form>
        </article>
    <?php endif; ?>

    <article
        class="order-1 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
        data-ajax-table-root="1"
        data-ajax-page-key="page"
        data-ajax-page-value="activities-manage"
        data-ajax-page-param="activity_page"
        data-ajax-search-param="search"
    >
        <h3>Danh sách hoạt động</h3>
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
                        placeholder="Tìm tên hoạt động, địa điểm..."
                        class="h-10 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                    >
                </label>
                <select name="status" data-ajax-filter="1" class="h-10 rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                    <option value="">Tất cả trạng thái</option>
                    <option value="upcoming" <?= $activityStatusFilter === 'upcoming' ? 'selected' : ''; ?>>Sắp diễn ra</option>
                    <option value="ongoing" <?= $activityStatusFilter === 'ongoing' ? 'selected' : ''; ?>>Đang diễn ra</option>
                    <option value="finished" <?= $activityStatusFilter === 'finished' ? 'selected' : ''; ?>>Đã kết thúc</option>
                </select>
            </div>
        </div>
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full border-collapse text-sm" data-disable-global-filter="1">
                <thead>
                    <tr>
                        <th>Tên hoạt động</th>
                        <th>Thumbnail</th>
                        <th>Bắt đầu</th>
                        <th>Kết thúc</th>
                        <th>Địa điểm</th>
                        <th>Số đăng ký</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody data-ajax-tbody="1">
                    <?php if (empty($activities)): ?>
                        <tr>
                            <td colspan="7">
                                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">Chưa có hoạt động nào.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($activities as $act): ?>
                            <?php $thumbnailUrl = normalize_public_file_url((string) ($act['image_thumbnail'] ?? '')); ?>
                            <tr>
                                <td><?= e((string) $act['activity_name']); ?></td>
                                <td>
                                    <?php if ($thumbnailUrl !== ''): ?>
                                        <a class="inline-flex" data-skip-action-icon="1" href="<?= e($thumbnailUrl); ?>" target="_blank" rel="noopener noreferrer">
                                            <img class="h-12 w-16 rounded-md border border-slate-200 object-cover" src="<?= e($thumbnailUrl); ?>" alt="Thumbnail hoạt động">
                                        </a>
                                    <?php else: ?>
                                        <span class="text-xs text-slate-400">Chưa có ảnh</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= e(ui_format_date((string) ($act['start_date'] ?? ''))); ?></td>
                                <td><?= e(ui_format_date((string) ($act['end_date'] ?? ''))); ?></td>
                                <td><?= e((string) ($act['location'] ?? '-')); ?></td>
                                <td><?= (int) $act['registered']; ?></td>
                                <td>
                                    <span class="inline-flex flex-wrap items-center gap-2">
                                        <button
                                            type="button"
                                            class="admin-row-detail-button admin-action-icon-btn"
                                            data-action-kind="detail"
                                            data-admin-row-detail="1"
                                            data-detail-url="<?= e(page_url('activities-manage', ['edit' => (int) $act['id'], 'activity_page' => $activityPage, 'activity_per_page' => $activityPerPage, 'search' => $searchQuery, 'status' => $activityStatusFilter])); ?>"
                                            data-skip-action-icon="1"
                                            title="Xem chi tiết"
                                            aria-label="Xem chi tiết"
                                        >
                                            <span class="admin-action-icon-label">Xem chi tiết</span>
                                            <span class="admin-action-icon-glyph" aria-hidden="true">
                                                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"></circle><path d="M2 12s3.5-6.5 10-6.5S22 12 22 12s-3.5 6.5-10 6.5S2 12 2 12z"></path></svg>
                                            </span>
                                        </button>
                                        <?php if ($canUpdateActivity): ?>
                                            <a
                                                href="<?= e(page_url('activities-manage', ['edit' => (int) $act['id'], 'activity_page' => $activityPage, 'activity_per_page' => $activityPerPage, 'search' => $searchQuery, 'status' => $activityStatusFilter])); ?>"
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
                                            <a
                                                href="<?= e(page_url('activities-manage', ['registrations_activity' => (int) $act['id'], 'activity_page' => $activityPage, 'activity_per_page' => $activityPerPage, 'search' => $searchQuery, 'status' => $activityStatusFilter]) . '#activity-registration-list') ?>"
                                                class="admin-action-icon-btn"
                                                data-registration-activity-link="1"
                                                data-activity-id="<?= (int) $act['id']; ?>"
                                                data-action-kind="detail"
                                                data-skip-action-icon="1"
                                                title="Danh sách học viên"
                                                aria-label="Danh sách học viên"
                                            >
                                                <span class="admin-action-icon-label">Học viên</span>
                                                <span class="admin-action-icon-glyph" aria-hidden="true">
                                                    <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><path d="M20 8v6"></path><path d="M23 11h-6"></path></svg>
                                                </span>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($canDeleteActivity): ?>
                                            <form class="inline-block" method="post" action="/api/activities/delete?id=<?= (int) $act['id']; ?>" onsubmit="return confirm('Có chắc không?')">
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
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <?php if ($activityTotal > 0): ?>
                <div class="border-t border-slate-200 bg-slate-50/80 px-3 py-2" data-ajax-pagination="1">
                    <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-slate-600">
                        <span class="min-w-0 flex-1 font-medium" data-ajax-row-info="1">Trang <?= (int) $activityPage; ?>/<?= (int) $activityTotalPages; ?> - Tổng <?= (int) $activityTotal; ?> hoạt động</span>
                        <div class="ml-auto inline-flex items-center gap-1.5">
                            <form class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2 py-1" method="get" action="<?= e(page_url('activities-manage')); ?>">
                                <input type="hidden" name="page" value="activities-manage">
                                <input type="hidden" name="search" value="<?= e($searchQuery); ?>">
                                <input type="hidden" name="status" value="<?= e($activityStatusFilter); ?>">
                                <label class="text-[11px] font-semibold text-slate-500" for="activity-per-page">Số dòng</label>
                                <select id="activity-per-page" name="activity_per_page" data-ajax-per-page="1" class="h-7 rounded-md border border-slate-200 bg-white px-2 text-xs font-semibold text-slate-700">
                                    <?php foreach ($activityPerPageOptions as $option): ?>
                                        <option value="<?= (int) $option; ?>" <?= $activityPerPage === (int) $option ? 'selected' : ''; ?>><?= (int) $option; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                            <?php if ($activityPage > 1): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('activities-manage', ['activity_page' => $activityPage - 1, 'activity_per_page' => $activityPerPage, 'search' => $searchQuery, 'status' => $activityStatusFilter])); ?>">Trước</a>
                            <?php else: ?>
                                <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400">Trước</span>
                            <?php endif; ?>

                            <?php if ($activityPage < $activityTotalPages): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('activities-manage', ['activity_page' => $activityPage + 1, 'activity_per_page' => $activityPerPage, 'search' => $searchQuery, 'status' => $activityStatusFilter])); ?>">Sau</a>
                            <?php else: ?>
                                <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400">Sau</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </article>

    <?php if ($selectedRegistrationActivityId > 0 && is_array($selectedRegistrationActivity)): ?>
        <article id="activity-registration-list" class="order-2 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                <h3>Danh sách học viên đăng ký: <?= e((string) ($selectedRegistrationActivity['activity_name'] ?? '')); ?></h3>
                <a class="<?= ui_btn_secondary_classes('sm'); ?>" href="<?= e(page_url('activities-manage', ['activity_page' => $activityPage, 'activity_per_page' => $activityPerPage, 'search' => $searchQuery, 'status' => $activityStatusFilter])); ?>">Đóng</a>
            </div>

            <?php if ($canUpdateActivity): ?>
                <div class="mb-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <h4 class="text-sm font-extrabold text-slate-800">Đăng ký giúp học viên từ phía admin</h4>
                    <p class="mt-1 text-xs text-slate-500">Chọn học viên chưa có trong danh sách để thêm trực tiếp vào hoạt động ngoại khóa này.</p>
                    <form class="mt-3 grid gap-3 md:grid-cols-[minmax(0,1fr)_auto]" method="post" action="/api/activities/add-student">
                        <?= csrf_input(); ?>
                        <input type="hidden" name="activity_id" value="<?= (int) $selectedRegistrationActivityId; ?>">
                        <input type="hidden" name="activity_page" value="<?= (int) $activityPage; ?>">
                        <input type="hidden" name="activity_per_page" value="<?= (int) $activityPerPage; ?>">
                        <input type="hidden" name="search" value="<?= e($searchQuery); ?>">
                        <input type="hidden" name="status" value="<?= e($activityStatusFilter); ?>">
                        <label>
                            Học viên
                            <select name="student_id" required>
                                <option value="">-- Chọn học viên --</option>
                                <?php foreach ($availableRegistrationStudents as $student): ?>
                                    <option value="<?= (int) ($student['id'] ?? 0); ?>"><?= e(student_dropdown_label($student)); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <div class="flex items-end">
                            <button class="<?= ui_btn_primary_classes(); ?>" type="submit" <?= empty($availableRegistrationStudents) ? 'disabled' : ''; ?>>Đăng ký học viên</button>
                        </div>
                    </form>
                    <?php if (empty($availableRegistrationStudents)): ?>
                        <p class="mt-2 text-xs font-medium text-slate-500">Tất cả học viên đang hoạt động đã có trong danh sách đăng ký của hoạt động này.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($canUpdateActivity && is_array($editingRegistration)): ?>
                <div class="hidden" aria-hidden="true">
                    <h4 class="mb-3 text-sm font-extrabold text-slate-800">Cập nhật thanh toán cho <?= e(student_display_name($editingRegistration)); ?></h4>
                    <form class="grid gap-3 md:grid-cols-2 xl:grid-cols-4" method="post" action="/api/activities/update-registration">
                        <?= csrf_input(); ?>
                        <input type="hidden" name="id" value="<?= (int) ($editingRegistration['id'] ?? 0); ?>">
                        <input type="hidden" name="activity_id" value="<?= (int) $selectedRegistrationActivityId; ?>">
                        <input type="hidden" name="student_id" value="<?= (int) ($editingRegistration['user_id'] ?? 0); ?>">
                        <input type="hidden" name="activity_page" value="<?= (int) $activityPage; ?>">
                        <input type="hidden" name="activity_per_page" value="<?= (int) $activityPerPage; ?>">
                        <input type="hidden" name="search" value="<?= e($searchQuery); ?>">
                        <input type="hidden" name="status" value="<?= e($activityStatusFilter); ?>">
                        <label>
                            Trạng thái đóng phí
                            <?php
                            $editingAmountPaid = max(0, (float) ($editingRegistration['amount_paid'] ?? 0));
                            $editingActivityFee = max(0, (float) ($selectedRegistrationActivity['fee'] ?? 0));
                            $editingBadgeLabel = 'Chưa đóng phí';
                            if ($editingAmountPaid >= $editingActivityFee) {
                                $editingBadgeLabel = 'Đã đóng đủ';
                            } elseif ($editingAmountPaid > 0) {
                                $editingBadgeLabel = 'Đóng một phần';
                            }
                            ?>
                            <div class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700"><?= e($editingBadgeLabel); ?></div>
                            <span class="mt-1 block text-xs text-slate-500">Trạng thái sẽ được tính tự động theo số tiền đã đóng so với phí gốc.</span>
                        </label>
                        <label>
                            Số tiền đã đóng
                            <input type="number" step="1000" min="0" name="amount_paid" value="<?= e((string) ((float) ($editingRegistration['amount_paid'] ?? 0))); ?>">
                        </label>
                        <label>
                            Thời điểm ghi nhận
                            <input type="datetime-local" name="payment_date" value="<?= e(str_replace(' ', 'T', substr((string) ($editingRegistration['payment_date'] ?? ''), 0, 16))); ?>">
                        </label>
                        <div class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600">
                            <div class="font-semibold text-slate-700">Phí gốc hoạt động</div>
                            <div><?= format_money((float) ($selectedRegistrationActivity['fee'] ?? 0)); ?></div>
                        </div>
                        <div class="md:col-span-2 xl:col-span-4 flex flex-wrap items-center gap-2">
                            <button class="<?= ui_btn_primary_classes(); ?>" type="submit">Lưu thanh toán</button>
                            <a class="<?= ui_btn_secondary_classes(); ?>" href="<?= e(page_url('activities-manage', ['registrations_activity' => $selectedRegistrationActivityId, 'activity_page' => $activityPage, 'activity_per_page' => $activityPerPage, 'search' => $searchQuery, 'status' => $activityStatusFilter])) ?>" data-admin-edit-close="1">Hủy</a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
                <table class="min-w-full border-collapse text-sm">
                    <thead>
                        <tr>
                            <th>Mã HV</th>
                            <th>Học viên</th>
                            <th>Ngày đăng ký</th>
                            <th>Trạng thái đóng phí</th>
                            <th>Số tiền đã đóng</th>
                            <th>Thời điểm ghi nhận</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($selectedRegistrations)): ?>
                            <tr>
                                <td colspan="7">
                                    <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">Hoạt động này chưa có học viên đăng ký.</div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($selectedRegistrations as $registration): ?>
                                <?php
                                $studentId = (int) ($registration['user_id'] ?? 0);
                                $paymentStatus = (string) ($registration['payment_status'] ?? 'unpaid');
                                $amountPaid = max(0, (float) ($registration['amount_paid'] ?? 0));
                                $activityFee = max(0, (float) ($selectedRegistrationActivity['fee'] ?? 0));
                                $badgeLabel = 'Chưa đóng phí';
                                $statusBadgeClass = 'border-amber-200 bg-amber-50 text-amber-700';

                                if ($amountPaid >= $activityFee) {
                                    $badgeLabel = 'Đã đóng đủ';
                                    $statusBadgeClass = 'border-emerald-200 bg-emerald-50 text-emerald-700';
                                } elseif ($amountPaid > 0) {
                                    $badgeLabel = 'Đóng một phần';
                                    $statusBadgeClass = 'border-sky-200 bg-sky-50 text-sky-700';
                                } elseif ($paymentStatus === 'paid' && $activityFee <= 0) {
                                    $badgeLabel = 'Đã đóng đủ';
                                    $statusBadgeClass = 'border-emerald-200 bg-emerald-50 text-emerald-700';
                                }
                                ?>
                                <tr>
                                    <td><?= e((string) ($registration['student_code'] ?? '-')); ?></td>
                                    <td>
                                        <div class="font-semibold text-slate-800"><?= e((string) ($registration['full_name'] ?? ('Học viên #' . $studentId))); ?></div>
                                        <div class="text-xs text-slate-500"><?= e((string) ($registration['username'] ?? '')); ?></div>
                                    </td>
                                    <td><?= e(ui_format_datetime((string) ($registration['registration_date'] ?? ''))); ?></td>
                                    <td>
                                        <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-bold <?= e($statusBadgeClass); ?>">
                                            <?= e($badgeLabel); ?>
                                        </span>
                                    </td>
                                    <td><?= format_money($amountPaid); ?></td>
                                    <td><?= e(ui_format_datetime((string) ($registration['payment_date'] ?? ''), '-')); ?></td>
                                    <td>
                                        <?php if ($canUpdateActivity): ?>
                                            <div class="flex flex-wrap items-center gap-2">
                                                <a class="<?= ui_btn_secondary_classes('sm'); ?>" href="<?= e(page_url('activities-manage', ['registrations_activity' => $selectedRegistrationActivityId, 'registration_student' => $studentId, 'registration_edit' => 1, 'activity_page' => $activityPage, 'activity_per_page' => $activityPerPage, 'search' => $searchQuery, 'status' => $activityStatusFilter])) ?>" title="Sửa thanh toán" aria-label="Sửa thanh toán">Sửa thanh toán</a>
                                                <form method="post" action="/api/activities/remove-student" onsubmit="return confirm('Bạn có chắc muốn xóa học viên khỏi hoạt động này?');">
                                                    <?= csrf_input(); ?>
                                                    <input type="hidden" name="activity_id" value="<?= (int) $selectedRegistrationActivityId; ?>">
                                                    <input type="hidden" name="student_id" value="<?= $studentId; ?>">
                                                    <input type="hidden" name="activity_page" value="<?= (int) $activityPage; ?>">
                                                    <input type="hidden" name="activity_per_page" value="<?= (int) $activityPerPage; ?>">
                                                    <input type="hidden" name="search" value="<?= e($searchQuery); ?>">
                                                    <input type="hidden" name="status" value="<?= e($activityStatusFilter); ?>">
                                                    <button class="<?= ui_btn_danger_classes('sm'); ?>" type="submit">Xóa khỏi hoạt động</button>
                                                </form>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-sm text-slate-500">Chỉ có quyền xem</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </article>
    <?php endif; ?>
</div>
<script>
(function () {
    // Hàm thực hiện cuộn mượt mà
    function scrollToRegistrationList() {
        const anchor = document.getElementById('activity-registration-list');
        if (!anchor) return;

        // Sử dụng setTimeout để đảm bảo trình duyệt đã tính toán xong Layout
        setTimeout(function () {
            anchor.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 300); 
    }

    // 1. Xử lý khi trang vừa nạp xong (từ link hoặc F5)
    window.addEventListener('DOMContentLoaded', function () {
        const params = new URLSearchParams(window.location.search);
        // Nếu có tham số registrations_activity trong URL, tiến hành scroll
        if (params.has('registrations_activity')) {
            scrollToRegistrationList();
        }
    });

    // 2. Xử lý sự kiện click vào các nút "Học viên"
    document.addEventListener('click', function (event) {
        const trigger = event.target.closest('a[data-registration-activity-link="1"]');
        if (!trigger) return;

        const targetUrl = new URL(trigger.href, window.location.origin);
        const currentUrl = new URL(window.location.href);

        // KIỂM TRA: Nếu bấm vào chính activity đang hiển thị (cùng ID trên URL)
        if (targetUrl.searchParams.get('registrations_activity') === currentUrl.searchParams.get('registrations_activity')) {
            event.preventDefault(); // Chặn load lại trang
            scrollToRegistrationList(); // Chỉ cuộn xuống
        }
        // Nếu là activity khác, trình duyệt sẽ tự load trang, 
        // và bước 1 (DOMContentLoaded) sẽ lo việc scroll sau khi trang mới nạp.
    });
})();
</script>
