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
$highlightRegistrationStudentId = max(0, (int) ($_GET['highlight_registration_student'] ?? 0));
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
$adminTitle = t('admin.activities.title');

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
            <h3><?= e($editingActivity ? t('admin.activities.edit') : t('admin.activities.add')); ?></h3>
            <form class="grid gap-3 md:grid-cols-2" method="post" action="/api/activities/save" enctype="multipart/form-data">
                <?= csrf_input(); ?>
                <input type="hidden" name="id" value="<?= (int) ($editingActivity['id'] ?? 0); ?>">
                <input type="hidden" name="existing_image_thumbnail" value="<?= e((string) ($editingActivity['image_thumbnail'] ?? '')); ?>">
                <label>
                    <?= e(t('admin.activities.name')); ?>
                    <input type="text" name="activity_name" value="<?= e((string) ($editingActivity['activity_name'] ?? '')); ?>" required>
                </label>
                <div class="md:col-span-2">
                    <label for="activity-description"><?= e(t('admin.activities.description')); ?></label>
                    <?= render_bbcode_editor('description', (string) ($editingActivity['description'] ?? ''), ['id' => 'activity-description', 'rows' => 4]); ?>
                </div>
                <div class="md:col-span-2">
                    <label for="activity-content"><?= e(t('admin.activities.content')); ?></label>
                    <?= render_bbcode_editor('content', (string) ($editingActivity['content'] ?? ''), ['id' => 'activity-content', 'rows' => 5]); ?>
                </div>
                <label>
                    <?= e(t('admin.activities.location')); ?>
                    <input type="text" name="location" value="<?= e((string) ($editingActivity['location'] ?? '')); ?>" placeholder="<?= e(t('admin.activities.location_placeholder')); ?>">
                </label>
                <label>
                    <?= e(t('admin.activities.start_date')); ?>
                    <input type="date" name="start_date" value="<?= e((string) ($editingActivity['start_date'] ?? '')); ?>" required>
                </label>
                <label>
                    <?= e(t('admin.activities.status')); ?>
                    <select name="status">
                        <option value="upcoming" <?= (($editingActivity['status'] ?? 'upcoming') === 'upcoming') ? 'selected' : ''; ?>><?= e(t('admin.activities.status_upcoming')); ?></option>
                        <option value="ongoing" <?= (($editingActivity['status'] ?? '') === 'ongoing') ? 'selected' : ''; ?>><?= e(t('admin.activities.status_ongoing')); ?></option>
                        <option value="finished" <?= (($editingActivity['status'] ?? '') === 'finished') ? 'selected' : ''; ?>><?= e(t('admin.activities.status_finished')); ?></option>
                    </select>
                </label>
                <label>
                    <?= e(t('admin.activities.fee')); ?>
                    <input type="number" step="1000" min="0" name="fee" value="<?= (float) ($editingActivity['fee'] ?? 0); ?>">
                </label>
                <label>
                    <?= e(t('admin.activities.thumbnail')); ?>
                    <input type="file" name="activity_thumbnail" accept=".jpg,.jpeg,.png,.gif,.webp">
                </label>
                <?php if ($editingThumbnailUrl !== ''): ?>
                    <div class="md:col-span-2 flex flex-wrap items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm text-slate-600">
                        <img class="h-16 w-24 rounded-lg border border-slate-200 object-cover" src="<?= e($editingThumbnailUrl); ?>" alt="<?= e(t('admin.activities.thumbnail_alt')); ?>">
                        <p><?= e(t('admin.activities.current_image')); ?>: <a class="font-semibold text-blue-700 hover:underline" href="<?= e($editingThumbnailUrl); ?>" target="_blank" rel="noopener noreferrer"><?= e(t('admin.activities.open_image')); ?></a>. <?= e(t('admin.activities.replace_hint')); ?></p>
                    </div>
                <?php endif; ?>
                <div class="md:col-span-2">
                    <button class="<?= ui_btn_primary_classes(); ?>" type="submit"><?= e(t('admin.activities.save')); ?></button>
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
        <h3><?= e(t('admin.activities.list')); ?></h3>
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
                        placeholder="<?= e(t('admin.activities.search_placeholder')); ?>"
                        class="h-10 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                    >
                </label>
                <select name="status" data-ajax-filter="1" class="h-10 rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                    <option value=""><?= e(t('admin.activities.status_all')); ?></option>
                    <option value="upcoming" <?= $activityStatusFilter === 'upcoming' ? 'selected' : ''; ?>><?= e(t('admin.activities.status_upcoming')); ?></option>
                    <option value="ongoing" <?= $activityStatusFilter === 'ongoing' ? 'selected' : ''; ?>><?= e(t('admin.activities.status_ongoing')); ?></option>
                    <option value="finished" <?= $activityStatusFilter === 'finished' ? 'selected' : ''; ?>><?= e(t('admin.activities.status_finished')); ?></option>
                </select>
            </div>
        </div>
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full border-collapse text-sm" data-disable-global-filter="1">
                <thead>
                    <tr>
                        <th><?= e(t('admin.activities.table_name')); ?></th>
                        <th><?= e(t('admin.activities.table_thumbnail')); ?></th>
                        <th><?= e(t('admin.activities.table_start')); ?></th>
                        <th><?= e(t('admin.activities.table_end')); ?></th>
                        <th><?= e(t('admin.activities.table_location')); ?></th>
                        <th><?= e(t('admin.activities.table_registrations')); ?></th>
                        <th><?= e(t('admin.activities.table_actions')); ?></th>
                    </tr>
                </thead>
                <tbody data-ajax-tbody="1">
                    <?php if (empty($activities)): ?>
                        <tr>
                            <td colspan="7">
                                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500"><?= e(t('admin.activities.empty')); ?></div>
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
                                            <img class="h-12 w-16 rounded-md border border-slate-200 object-cover" src="<?= e($thumbnailUrl); ?>" alt="<?= e(t('admin.activities.thumbnail_alt')); ?>">
                                        </a>
                                    <?php else: ?>
                                        <span class="text-xs text-slate-400"><?= e(t('admin.activities.no_thumbnail')); ?></span>
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
                                            title="<?= e(t('admin.activities.view_detail')); ?>"
                                            aria-label="<?= e(t('admin.activities.view_detail')); ?>"
                                        >
                                            <span class="admin-action-icon-label"><?= e(t('admin.activities.view_detail')); ?></span>
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
                                                title="<?= e(t('admin.common.edit')); ?>"
                                                aria-label="<?= e(t('admin.common.edit')); ?>"
                                            >
                                                <span class="admin-action-icon-label"><?= e(t('admin.common.edit')); ?></span>
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
                                                title="<?= e(t('admin.activities.students_title')); ?>"
                                                aria-label="<?= e(t('admin.activities.students_title')); ?>"
                                            >
                                                <span class="admin-action-icon-label"><?= e(t('admin.activities.students_label')); ?></span>
                                                <span class="admin-action-icon-glyph" aria-hidden="true">
                                                    <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><path d="M20 8v6"></path><path d="M23 11h-6"></path></svg>
                                                </span>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($canDeleteActivity): ?>
                                            <form class="inline-block" method="post" action="/api/activities/delete?id=<?= (int) $act['id']; ?>" onsubmit="return confirm('<?= e(t('admin.activities.delete_confirm')); ?>')">
                                                <?= csrf_input(); ?>
                                                <button
                                                    class="<?= ui_btn_danger_classes('sm'); ?> admin-action-icon-btn"
                                                    data-action-kind="delete"
                                                    data-skip-action-icon="1"
                                                    type="submit"
                                                    title="<?= e(t('admin.common.delete')); ?>"
                                                    aria-label="<?= e(t('admin.common.delete')); ?>"
                                                >
                                                    <span class="admin-action-icon-label"><?= e(t('admin.common.delete')); ?></span>
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
                        <span class="min-w-0 flex-1 font-medium" data-ajax-row-info="1"><?= e(t('admin.activities.page_info', ['current' => (int) $activityPage, 'total' => (int) $activityTotalPages, 'count' => (int) $activityTotal])); ?></span>
                        <div class="ml-auto inline-flex items-center gap-1.5">
                            <form class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2 py-1" method="get" action="<?= e(page_url('activities-manage')); ?>">
                                <input type="hidden" name="page" value="activities-manage">
                                <input type="hidden" name="search" value="<?= e($searchQuery); ?>">
                                <input type="hidden" name="status" value="<?= e($activityStatusFilter); ?>">
                                <label class="text-[11px] font-semibold text-slate-500" for="activity-per-page"><?= e(t('admin.common.rows')); ?></label>
                                <select id="activity-per-page" name="activity_per_page" data-ajax-per-page="1" class="h-7 rounded-md border border-slate-200 bg-white px-2 text-xs font-semibold text-slate-700">
                                    <?php foreach ($activityPerPageOptions as $option): ?>
                                        <option value="<?= (int) $option; ?>" <?= $activityPerPage === (int) $option ? 'selected' : ''; ?>><?= (int) $option; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                            <?php if ($activityPage > 1): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('activities-manage', ['activity_page' => $activityPage - 1, 'activity_per_page' => $activityPerPage, 'search' => $searchQuery, 'status' => $activityStatusFilter])); ?>"><?= e(t('admin.common.previous')); ?></a>
                            <?php else: ?>
                                <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400"><?= e(t('admin.common.previous')); ?></span>
                            <?php endif; ?>

                            <?php if ($activityPage < $activityTotalPages): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('activities-manage', ['activity_page' => $activityPage + 1, 'activity_per_page' => $activityPerPage, 'search' => $searchQuery, 'status' => $activityStatusFilter])); ?>"><?= e(t('admin.common.next')); ?></a>
                            <?php else: ?>
                                <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400"><?= e(t('admin.common.next')); ?></span>
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
                <h3><?= e(t('admin.activities.registrations_title', ['name' => (string) ($selectedRegistrationActivity['activity_name'] ?? '')])); ?></h3>
                <a class="<?= ui_btn_secondary_classes('sm'); ?>" href="<?= e(page_url('activities-manage', ['activity_page' => $activityPage, 'activity_per_page' => $activityPerPage, 'search' => $searchQuery, 'status' => $activityStatusFilter])); ?>"><?= e(t('admin.activities.close')); ?></a>
            </div>

            <?php if ($canUpdateActivity): ?>
                <div class="mb-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <h4 class="text-sm font-extrabold text-slate-800"><?= e(t('admin.activities.register_admin_title')); ?></h4>
                    <p class="mt-1 text-xs text-slate-500"><?= e(t('admin.activities.register_admin_copy')); ?></p>
                    <form class="mt-3 grid gap-3 md:grid-cols-[minmax(0,1fr)_auto]" method="post" action="/api/activities/add-student">
                        <?= csrf_input(); ?>
                        <input type="hidden" name="activity_id" value="<?= (int) $selectedRegistrationActivityId; ?>">
                        <input type="hidden" name="activity_page" value="<?= (int) $activityPage; ?>">
                        <input type="hidden" name="activity_per_page" value="<?= (int) $activityPerPage; ?>">
                        <input type="hidden" name="search" value="<?= e($searchQuery); ?>">
                        <input type="hidden" name="status" value="<?= e($activityStatusFilter); ?>">
                        <label>
                            <?= e(t('admin.activities.student')); ?>
                            <select name="student_id" required>
                                <option value=""><?= e(t('admin.activities.choose_student')); ?></option>
                                <?php foreach ($availableRegistrationStudents as $student): ?>
                                    <option value="<?= (int) ($student['id'] ?? 0); ?>"><?= e(student_dropdown_label($student)); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <div class="flex items-end">
                            <button class="<?= ui_btn_primary_classes(); ?>" type="submit" <?= empty($availableRegistrationStudents) ? 'disabled' : ''; ?>><?= e(t('admin.activities.register_student')); ?></button>
                        </div>
                    </form>
                    <?php if (empty($availableRegistrationStudents)): ?>
                        <p class="mt-2 text-xs font-medium text-slate-500"><?= e(t('admin.activities.register_all_added')); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($canUpdateActivity && is_array($editingRegistration)): ?>
                <div class="hidden" aria-hidden="true">
                    <h4 class="mb-3 text-sm font-extrabold text-slate-800"><?= e(t('admin.activities.payment_update_title', ['name' => student_display_name($editingRegistration)])); ?></h4>
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
                            <?= e(t('admin.activities.payment_status')); ?>
                            <?php
                            $editingAmountPaid = max(0, (float) ($editingRegistration['amount_paid'] ?? 0));
                            $editingActivityFee = max(0, (float) ($selectedRegistrationActivity['fee'] ?? 0));
                            $editingBadgeLabel = t('admin.activities.payment_status_unpaid');
                            if ($editingAmountPaid >= $editingActivityFee) {
                                $editingBadgeLabel = t('admin.activities.payment_status_paid_full');
                            } elseif ($editingAmountPaid > 0) {
                                $editingBadgeLabel = t('admin.activities.payment_status_paid_partial');
                            }
                            ?>
                            <div class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700"><?= e($editingBadgeLabel); ?></div>
                            <span class="mt-1 block text-xs text-slate-500"><?= e(t('admin.activities.payment_status_hint')); ?></span>
                        </label>
                        <label>
                            <?= e(t('admin.activities.amount_paid')); ?>
                            <input type="number" step="1000" min="0" name="amount_paid" value="<?= e((string) ((float) ($editingRegistration['amount_paid'] ?? 0))); ?>">
                        </label>
                        <label>
                            <?= e(t('admin.activities.payment_date')); ?>
                            <input type="datetime-local" name="payment_date" value="<?= e(str_replace(' ', 'T', substr((string) ($editingRegistration['payment_date'] ?? ''), 0, 16))); ?>">
                        </label>
                        <div class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600">
                            <div class="font-semibold text-slate-700"><?= e(t('admin.activities.base_fee')); ?></div>
                            <div><?= format_money((float) ($selectedRegistrationActivity['fee'] ?? 0)); ?></div>
                        </div>
                        <div class="md:col-span-2 xl:col-span-4 flex flex-wrap items-center gap-2">
                            <button class="<?= ui_btn_primary_classes(); ?>" type="submit"><?= e(t('admin.activities.save_payment')); ?></button>
                            <a class="<?= ui_btn_secondary_classes(); ?>" href="<?= e(page_url('activities-manage', ['registrations_activity' => $selectedRegistrationActivityId, 'activity_page' => $activityPage, 'activity_per_page' => $activityPerPage, 'search' => $searchQuery, 'status' => $activityStatusFilter])) ?>" data-admin-edit-close="1"><?= e(t('admin.common.cancel')); ?></a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
                <table class="min-w-full border-collapse text-sm">
                    <thead>
                        <tr>
                            <th><?= e(t('admin.activities.registration_table_student_code')); ?></th>
                            <th><?= e(t('admin.activities.registration_table_student')); ?></th>
                            <th><?= e(t('admin.activities.registration_table_date')); ?></th>
                            <th><?= e(t('admin.activities.registration_table_payment_status')); ?></th>
                            <th><?= e(t('admin.activities.registration_table_amount_paid')); ?></th>
                            <th><?= e(t('admin.activities.registration_table_payment_date')); ?></th>
                            <th><?= e(t('admin.activities.registration_table_actions')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($selectedRegistrations)): ?>
                            <tr>
                                <td colspan="7">
                                    <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500"><?= e(t('admin.activities.registration_empty')); ?></div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($selectedRegistrations as $registration): ?>
                                <?php
                                $studentId = (int) ($registration['user_id'] ?? 0);
                                $paymentStatus = (string) ($registration['payment_status'] ?? 'unpaid');
                                $amountPaid = max(0, (float) ($registration['amount_paid'] ?? 0));
                                $activityFee = max(0, (float) ($selectedRegistrationActivity['fee'] ?? 0));
                                $badgeLabel = t('admin.activities.payment_status_unpaid');
                                $statusBadgeClass = 'border-amber-200 bg-amber-50 text-amber-700';

                                if ($amountPaid >= $activityFee) {
                                    $badgeLabel = t('admin.activities.payment_status_paid_full');
                                    $statusBadgeClass = 'border-emerald-200 bg-emerald-50 text-emerald-700';
                                } elseif ($amountPaid > 0) {
                                    $badgeLabel = t('admin.activities.payment_status_paid_partial');
                                    $statusBadgeClass = 'border-sky-200 bg-sky-50 text-sky-700';
                                } elseif ($paymentStatus === 'paid' && $activityFee <= 0) {
                                    $badgeLabel = t('admin.activities.payment_status_paid_full');
                                    $statusBadgeClass = 'border-emerald-200 bg-emerald-50 text-emerald-700';
                                }
                                $isHighlightedRegistration = $highlightRegistrationStudentId > 0 && $highlightRegistrationStudentId === $studentId;
                                ?>
                                <tr id="activity-registration-row-<?= $studentId; ?>" <?= $isHighlightedRegistration ? 'class="bg-amber-50/80"' : ''; ?>>
                                    <td><?= e((string) ($registration['student_code'] ?? '-')); ?></td>
                                    <td>
                                        <div class="font-semibold text-slate-800"><?= e((string) ($registration['full_name'] ?? t('admin.activities.student_fallback', ['id' => $studentId]))); ?></div>
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
                                                <a class="<?= ui_btn_secondary_classes('sm'); ?>" href="<?= e(page_url('activities-manage', ['registrations_activity' => $selectedRegistrationActivityId, 'registration_student' => $studentId, 'registration_edit' => 1, 'activity_page' => $activityPage, 'activity_per_page' => $activityPerPage, 'search' => $searchQuery, 'status' => $activityStatusFilter])) ?>" title="<?= e(t('admin.activities.edit_payment')); ?>" aria-label="<?= e(t('admin.activities.edit_payment')); ?>"><?= e(t('admin.activities.edit_payment')); ?></a>
                                                <form method="post" action="/api/activities/remove-student" onsubmit="return confirm('<?= e(t('admin.activities.remove_student_confirm')); ?>');">
                                                    <?= csrf_input(); ?>
                                                    <input type="hidden" name="activity_id" value="<?= (int) $selectedRegistrationActivityId; ?>">
                                                    <input type="hidden" name="student_id" value="<?= $studentId; ?>">
                                                    <input type="hidden" name="activity_page" value="<?= (int) $activityPage; ?>">
                                                    <input type="hidden" name="activity_per_page" value="<?= (int) $activityPerPage; ?>">
                                                    <input type="hidden" name="search" value="<?= e($searchQuery); ?>">
                                                    <input type="hidden" name="status" value="<?= e($activityStatusFilter); ?>">
                                                    <button class="<?= ui_btn_danger_classes('sm'); ?>" type="submit"><?= e(t('admin.activities.remove_student')); ?></button>
                                                </form>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-sm text-slate-500"><?= e(t('admin.common.view_only')); ?></span>
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

        const highlightedRow = document.getElementById('activity-registration-row-<?= (int) $highlightRegistrationStudentId; ?>');
        if (highlightedRow instanceof HTMLElement) {
            window.setTimeout(function () {
                highlightedRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 420);
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
