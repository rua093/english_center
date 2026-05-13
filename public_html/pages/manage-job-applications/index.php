<?php
declare(strict_types=1);

require_admin_or_staff();
require_any_permission(['job_application.view']);

if (!function_exists('job_application_extract_email')) {
    function job_application_extract_email(string $value): string
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
}

if (!function_exists('job_application_extract_phone')) {
    function job_application_extract_phone(string $value): string
    {
        $normalized = trim($value);
        if ($normalized === '') {
            return '';
        }

        if (preg_match('/(?:\+?\d[\d\s().-]{7,}\d)/', $normalized, $matches) !== 1) {
            return '';
        }

        $digits = preg_replace('/\D+/', '', (string) ($matches[0] ?? ''));
        if (!is_string($digits) || $digits === '') {
            return '';
        }

        return $digits;
    }
}

if (!function_exists('job_application_suggested_username')) {
    function job_application_suggested_username(array $application): string
    {
        $email = strtolower(trim((string) ($application['email'] ?? '')));
        if ($email !== '' && str_contains($email, '@')) {
            $localPart = (string) strstr($email, '@', true);
            if ($localPart !== '') {
                return $localPart;
            }
        }

        $phone = trim((string) ($application['phone'] ?? ''));
        if ($phone !== '') {
            $digits = preg_replace('/\D+/', '', $phone);
            if ($digits !== '') {
                return 'teacher' . substr($digits, -6);
            }
        }

        $fullName = strtolower(trim((string) ($application['full_name'] ?? '')));
        if ($fullName !== '') {
            $slug = preg_replace('/[^a-z0-9]+/', '', $fullName) ?? '';
            if ($slug !== '') {
                return 'teacher.' . substr($slug, 0, 24);
            }
        }

        return 'teacher' . date('His');
    }
}

if (!function_exists('job_application_status_badge_class')) {
    function job_application_status_badge_class(string $status): string
    {
        return match ($status) {
            'PENDING' => 'is-pending',
            'INTERVIEWING' => 'is-upcoming',
            'PASSED' => 'is-approved',
            'REJECTED' => 'is-rejected',
            default => 'is-pending',
        };
    }
}

if (!function_exists('job_application_value_or_dash')) {
    function job_application_value_or_dash(mixed $value): string
    {
        $normalized = trim((string) $value);
        return $normalized === '' ? '—' : $normalized;
    }
}

if (!function_exists('job_application_short_text')) {
    function job_application_short_text(mixed $value, int $limit = 120): string
    {
        $text = trim((string) $value);
        if ($text === '') {
            return '—';
        }

        $length = function_exists('mb_strlen') ? mb_strlen($text) : strlen($text);
        if ($length <= $limit) {
            return $text;
        }

        $snippet = function_exists('mb_substr')
            ? mb_substr($text, 0, $limit - 1)
            : substr($text, 0, $limit - 1);

        return rtrim($snippet) . '…';
    }
}

if (!function_exists('job_application_format_datetime')) {
    function job_application_format_datetime(?string $value): string
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return '—';
        }

        try {
            $dt = new DateTimeImmutable($raw);
            return $dt->format('d/m/Y H:i');
        } catch (Throwable) {
            return $raw;
        }
    }
}

if (!function_exists('job_application_can_convert_status')) {
    function job_application_can_convert_status(string $status): bool
    {
        return in_array(strtoupper(trim($status)), ['INTERVIEWING', 'PASSED'], true);
    }
}

$adminModel = new AdminModel();
$statusOptions = [
    'PENDING'      => t('admin.job_applications.status.pending'),
    'INTERVIEWING' => t('admin.job_applications.status.interviewing'),
    'PASSED'       => t('admin.job_applications.status.passed'),
    'REJECTED'     => t('admin.job_applications.status.rejected'),
];

$statusFilter = strtoupper(trim((string) ($_GET['status'] ?? '')));
if (!isset($statusOptions[$statusFilter])) {
    $statusFilter = '';
}
$searchQuery = trim((string) ($_GET['search'] ?? ''));
$highlightApplicationId = max(0, (int) ($_GET['highlight_application_id'] ?? 0));
$filters = [
    'status' => $statusFilter,
];

$applicationPage = max(1, (int) ($_GET['application_page'] ?? 1));
$applicationPerPage = ui_pagination_resolve_per_page('application_per_page', 10);
$applicationTotal = $adminModel->countJobApplications($filters, $searchQuery);
$applicationTotalPages = max(1, (int) ceil($applicationTotal / $applicationPerPage));
if ($applicationPage > $applicationTotalPages) {
    $applicationPage = $applicationTotalPages;
}

$applications = $adminModel->listJobApplicationsPage(
    $applicationPage,
    $applicationPerPage,
    $filters,
    $searchQuery
);
$applicationPerPageOptions = ui_pagination_per_page_options();

$statusSummary = [];
foreach ($statusOptions as $statusKey => $statusLabel) {
    $statusSummary[$statusKey] = [
        'label' => $statusLabel,
        'count' => $adminModel->countJobApplications(['status' => $statusKey]),
        'badgeClass' => job_application_status_badge_class($statusKey),
    ];
}

$editingApplication = null;
if (!empty($_GET['edit'])) {
    $editingApplication = $adminModel->findJobApplication((int) $_GET['edit']);
}

$module = 'job-applications';
$adminTitle = t('admin.job_applications.title');
$adminDescription = t('admin.job_applications.description');

$success = get_flash('success');
$error = get_flash('error');
$canConvertApplication = has_any_permission(['job_application.update']);
$canDeleteApplication = has_permission('job_application.delete');
?>
<div class="grid gap-4">
    <?php if ($success): ?>
        <div class="rounded-xl border-l-4 border-emerald-500 bg-emerald-50 p-3 text-sm text-emerald-700"><?= e($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="rounded-xl border-l-4 border-rose-500 bg-rose-50 p-3 text-sm text-rose-700"><?= e($error); ?></div>
    <?php endif; ?>

    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3><?= e(t('admin.job_applications.pipeline_title')); ?></h3>
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <?php foreach ($statusSummary as $summary): ?>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-500"><?= e($summary['label']); ?></p>
                    <p class="mt-1 text-2xl font-black text-slate-800"><?= (int) $summary['count']; ?></p>
                    <span class="mt-2 inline-flex items-center rounded-full border px-2.5 py-1 text-[11px] font-bold <?= e((string) $summary['badgeClass']); ?>">
                        <?= e($summary['label']); ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    </article>

    <?php if (is_array($editingApplication)): ?>
        <?php
            $editingStatus = (string) ($editingApplication['status'] ?? 'PENDING');
            $editingStatusLabel = (string) ($statusOptions[$editingStatus] ?? $editingStatus);
            $editingApplicationConverted = (int) ($editingApplication['converted_user_id'] ?? 0) > 0;
            $editingApplicationLockReason = t('admin.job_applications.lock_reason');
            $editingApplicationCanConvertNow = job_application_can_convert_status($editingStatus);
            $editingApplicationUrl = page_url('job-applications-manage', [
                'edit' => (int) ($editingApplication['id'] ?? 0),
                'application_page' => $applicationPage,
                'application_per_page' => $applicationPerPage,
                'status' => $statusFilter !== '' ? $statusFilter : null,
                'search' => $searchQuery !== '' ? $searchQuery : null,
            ]);
        ?>
        <a id="job-application-edit-auto-open" href="<?= e($editingApplicationUrl); ?>" class="hidden" aria-hidden="true" tabindex="-1"><?= e(t('admin.job_applications.open_modal')); ?></a>
        <article class="hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" aria-hidden="true" data-edit-form-source="1" data-edit-modal-mode="process" data-edit-modal-title="<?= e(t('admin.job_applications.modal_title')); ?>">
            <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h3 class="mb-1"><?= e(t('admin.job_applications.process_title', ['id' => (int) ($editingApplication['id'] ?? 0), 'name' => (string) ($editingApplication['full_name'] ?? '')])); ?></h3>
                    <p class="text-sm text-slate-600"><?= e(t('admin.job_applications.created_at_label')); ?> <strong><?= e(job_application_format_datetime((string) ($editingApplication['created_at'] ?? ''))); ?></strong></p>
                </div>
                <span data-process-status-badge="1" class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-extrabold <?= e(job_application_status_badge_class($editingStatus)); ?>">
                    <?= e($editingStatusLabel); ?>
                </span>
            </div>

            <div class="grid gap-3 xl:grid-cols-3">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <h4 class="mb-2 text-sm font-extrabold text-slate-800"><?= e(t('admin.job_applications.candidate_info')); ?></h4>
                    <dl class="grid gap-1 text-sm text-slate-700">
                        <div><dt class="inline font-semibold"><?= e(t('admin.job_applications.full_name')); ?>:</dt> <dd class="inline"><?= e(job_application_value_or_dash($editingApplication['full_name'] ?? '')); ?></dd></div>
                        <div><dt class="inline font-semibold"><?= e(t('admin.job_applications.email')); ?>:</dt> <dd class="inline"><?= e(job_application_value_or_dash($editingApplication['email'] ?? '')); ?></dd></div>
                        <div><dt class="inline font-semibold"><?= e(t('admin.job_applications.phone')); ?>:</dt> <dd class="inline"><?= e(job_application_value_or_dash($editingApplication['phone'] ?? '')); ?></dd></div>
                        <div><dt class="inline font-semibold"><?= e(t('admin.job_applications.address')); ?>:</dt> <dd class="inline"><?= e(job_application_value_or_dash($editingApplication['address'] ?? '')); ?></dd></div>
                        <div><dt class="inline font-semibold"><?= e(t('admin.job_applications.position')); ?>:</dt> <dd class="inline"><?= e(job_application_value_or_dash($editingApplication['position_applied'] ?? '')); ?></dd></div>
                        <div><dt class="inline font-semibold"><?= e(t('admin.job_applications.work_mode')); ?>:</dt> <dd class="inline"><?= e(job_application_value_or_dash($editingApplication['work_mode'] ?? '')); ?></dd></div>
                        <div><dt class="inline font-semibold"><?= e(t('admin.job_applications.start_date')); ?>:</dt> <dd class="inline"><?= e(ui_format_date((string) ($editingApplication['start_date'] ?? ''), '—')); ?></dd></div>
                        <div><dt class="inline font-semibold"><?= e(t('admin.job_applications.salary')); ?>:</dt> <dd class="inline"><?= e(job_application_value_or_dash($editingApplication['salary_expectation'] ?? '')); ?></dd></div>
                        <div><dt class="inline font-semibold"><?= e(t('admin.job_applications.cv')); ?>:</dt> <dd class="inline"><?= e(job_application_value_or_dash($editingApplication['cv_file_url'] ?? '')); ?></dd></div>
                    </dl>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <h4 class="mb-2 text-sm font-extrabold text-slate-800"><?= e(t('admin.job_applications.professional_profile')); ?></h4>
                    <dl class="grid gap-2 text-sm text-slate-700">
                        <div>
                            <dt class="font-semibold"><?= e(t('admin.job_applications.education')); ?></dt>
                            <dd><?= e(job_application_value_or_dash($editingApplication['education_detail'] ?? '')); ?></dd>
                        </div>
                        <div>
                            <dt class="font-semibold"><?= e(t('admin.job_applications.experience')); ?></dt>
                            <dd><?= e(job_application_value_or_dash($editingApplication['work_history'] ?? '')); ?></dd>
                        </div>
                        <div>
                            <dt class="font-semibold"><?= e(t('admin.job_applications.skills')); ?></dt>
                            <dd><?= e(job_application_value_or_dash($editingApplication['skills_set'] ?? '')); ?></dd>
                        </div>
                    </dl>
                </div>

                <div
                    class="rounded-xl border border-slate-200 bg-white p-3"
                    <?= $editingApplicationConverted ? 'data-process-locked-section="1" title="' . e($editingApplicationLockReason) . '"' : ''; ?>
                >
                    <h4 class="mb-2 text-sm font-extrabold text-slate-800"><?= e(t('admin.job_applications.update_process')); ?></h4>
                    <form class="grid gap-2" method="post" action="/api/applications/update">
                        <?= csrf_input(); ?>
                        <input type="hidden" name="id" value="<?= (int) ($editingApplication['id'] ?? 0); ?>">
                        <fieldset class="grid gap-2" <?= $editingApplicationConverted ? 'disabled aria-disabled="true"' : ''; ?>>
                            <label>
                                <?= e(t('admin.job_applications.status_label')); ?>
                                <div class="relative mt-1">
                                    <select
                                        name="status"
                                        required
                                        data-process-status-select="1"
                                        data-is-converted="<?= $editingApplicationConverted ? '1' : '0'; ?>"
                                        data-locked-status="passed"
                                        <?= $editingApplicationConverted ? 'disabled aria-disabled="true"' : ''; ?>
                                    >
                                        <?php foreach ($statusOptions as $statusValue => $statusLabel): ?>
                                            <?php $isLockedOutStatus = $editingApplicationConverted && $statusValue !== 'PASSED'; ?>
                                            <?php $isSelectedStatus = $editingApplicationConverted ? $statusValue === 'PASSED' : $editingStatus === $statusValue; ?>
                                            <option value="<?= e($statusValue); ?>" <?= $isSelectedStatus ? 'selected' : ''; ?> <?= $isLockedOutStatus ? 'disabled' : ''; ?>><?= e($statusLabel); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if ($editingApplicationConverted): ?>
                                        <span
                                            class="absolute inset-0 z-10 cursor-not-allowed rounded-xl"
                                            title="<?= e($editingApplicationLockReason); ?>"
                                            aria-label="<?= e($editingApplicationLockReason); ?>"
                                        ></span>
                                    <?php endif; ?>
                                </div>
                            </label>
                            <label>
                                <?= e(t('admin.job_applications.hr_note')); ?>
                                <textarea name="hr_note" rows="4" placeholder="<?= e(t('admin.job_applications.hr_note_placeholder')); ?>"><?= e((string) ($editingApplication['hr_note'] ?? '')); ?></textarea>
                            </label>
                            <div>
                                <button class="<?= ui_btn_primary_classes('sm'); ?>" type="submit"><?= e(t('admin.job_applications.save_update')); ?></button>
                            </div>
                        </fieldset>
                    </form>
                </div>
            </div>

            <div class="mt-3 grid gap-3 md:grid-cols-2">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <h4 class="mb-2 text-sm font-extrabold text-slate-800"><?= e(t('admin.job_applications.bio')); ?></h4>
                    <p class="text-sm leading-relaxed text-slate-700"><?= nl2br(e(job_application_value_or_dash($editingApplication['bio_summary'] ?? ''))); ?></p>
                </div>

                <?php if ($editingApplicationConverted): ?>
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-700">
                        <p class="font-semibold"><?= e(t('admin.job_applications.converted_notice')); ?></p>
                        <p class="mt-1"><?= e(t('admin.job_applications.account_id')); ?>
                            <button
                                type="button"
                                class="font-bold text-emerald-800 underline"
                                data-admin-row-detail="1"
                                data-detail-url="<?= e(page_url('users-admin', ['edit' => (int) $editingApplication['converted_user_id']])); ?>"
                            >#<?= (int) $editingApplication['converted_user_id']; ?></button>
                        </p>
                        <p class="mt-1 text-xs"><?= e(t('admin.job_applications.converted_at')); ?> <?= e(job_application_format_datetime((string) ($editingApplication['converted_at'] ?? ''))); ?></p>
                    </div>
                <?php elseif ($canConvertApplication): ?>
                    <div class="rounded-xl border border-blue-200 bg-blue-50 p-3">
                        <h4 class="mb-2 text-sm font-extrabold text-blue-900"><?= e(t('admin.job_applications.create_teacher_account')); ?></h4>
                        <form
                            class="grid gap-2 md:grid-cols-2"
                            method="post"
                            action="/api/applications/convert"
                            data-process-convert-form="1"
                            data-valid-statuses="interviewing,passed"
                            data-locked-status="passed"
                            data-is-converted="0"
                        >
                            <?= csrf_input(); ?>
                            <input type="hidden" name="id" value="<?= (int) ($editingApplication['id'] ?? 0); ?>">
                            <label>
                                <?= e(t('admin.job_applications.username')); ?>
                                <input type="text" name="username" value="<?= e(job_application_suggested_username($editingApplication)); ?>" required>
                            </label>
                            <label>
                                <?= e(t('admin.job_applications.password')); ?>
                                <input type="text" name="password" value="">
                            </label>
                            <label class="md:col-span-2">
                                <?= e(t('admin.job_applications.convert_note')); ?>
                                <textarea name="admin_note" rows="2"><?= e((string) ($editingApplication['hr_note'] ?? '')); ?></textarea>
                            </label>
                            <div class="md:col-span-2">
                                <button
                                    class="<?= ui_btn_primary_classes('sm'); ?>"
                                    type="submit"
                                    data-process-convert-button="1"
                                    <?= $editingApplicationCanConvertNow ? '' : 'disabled aria-disabled="true" title="' . e(t('admin.job_applications.convert_blocked')) . '"'; ?>
                                ><?= e(t('admin.job_applications.create_user')); ?></button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </article>
    <?php endif; ?>

    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
            <div>
                <h3 class="mb-1"><?= e(t('admin.job_applications.list_title')); ?></h3>
                <p class="text-sm text-slate-600"><?= e(t('admin.job_applications.list_note')); ?></p>
            </div>
            <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-bold text-slate-600"><?= e(t('admin.job_applications.total_badge', ['count' => (int) $applicationTotal])); ?></span>
        </div>

        <div
            data-ajax-table-root="1"
            data-ajax-page-key="page"
            data-ajax-page-value="job-applications-manage"
            data-ajax-page-param="application_page"
            data-ajax-search-param="search"
        >
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div class="flex w-full flex-wrap items-center gap-3">
                <input
                    data-ajax-search="1"
                    type="search"
                    value="<?= e($searchQuery); ?>"
                    placeholder="<?= e(t('admin.job_applications.search_placeholder')); ?>"
                    autocomplete="off"
                    class="w-full max-w-sm rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100"
                >
                <select
                    name="status"
                    data-ajax-filter="1"
                    class="h-11 rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-700 shadow-sm outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100"
                >
                    <option value=""><?= e(t('admin.job_applications.status_all')); ?></option>
                    <?php foreach ($statusOptions as $statusKey => $statusLabel): ?>
                        <option value="<?= e($statusKey); ?>" <?= $statusFilter === $statusKey ? 'selected' : ''; ?>><?= e($statusLabel); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full border-collapse text-sm" data-enable-row-detail="1" data-disable-global-filter="1">
                <thead>
                    <tr>
                        <th><?= e(t('admin.job_applications.table_id')); ?></th>
                        <th><?= e(t('admin.job_applications.table_candidate')); ?></th>
                        <th><?= e(t('admin.job_applications.table_profile')); ?></th>
                        <th><?= e(t('admin.job_applications.table_status')); ?></th>
                        <th><?= e(t('admin.job_applications.table_hr_note')); ?></th>
                        <th><?= e(t('admin.job_applications.table_conversion')); ?></th>
                        <th><?= e(t('admin.common.actions')); ?></th>
                    </tr>
                </thead>
                <tbody data-ajax-tbody="1">
                    <?php if (empty($applications)): ?>
                        <tr>
                            <td colspan="7">
                                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500"><?= e(t('admin.job_applications.empty')); ?></div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($applications as $application): ?>
                            <?php
                                $statusValue = (string) ($application['status'] ?? 'PENDING');
                                $statusLabel = (string) ($statusOptions[$statusValue] ?? $statusValue);
                                $applicationId = (int) ($application['id'] ?? 0);
                                $isHighlightedApplication = $highlightApplicationId > 0 && $highlightApplicationId === $applicationId;
                            ?>
                            <tr id="job-application-row-<?= $applicationId; ?>" <?= $isHighlightedApplication ? 'class="bg-amber-50/80"' : ''; ?>>
                                <td class="font-semibold">#<?= $applicationId; ?></td>
                                <td>
                                    <div class="font-bold text-slate-800"><?= e(job_application_value_or_dash($application['full_name'] ?? '')); ?></div>
                                    <div class="text-xs text-slate-600"><?= e(t('admin.job_applications.contact_label')); ?> <?= e(job_application_short_text(trim((string) (($application['email'] ?? '') . ' ' . ($application['phone'] ?? ''))), 50)); ?></div>
                                </td>
                                <td>
                                    <div class="font-semibold text-slate-700"><?= e(t('admin.job_applications.position')); ?> <?= e(job_application_short_text($application['position_applied'] ?? '', 40)); ?></div>
                                    <div class="text-xs text-slate-600"><?= e(t('admin.job_applications.experience')); ?> <?= e(job_application_short_text($application['work_history'] ?? '', 35)); ?></div>
                                    <div class="text-xs text-slate-500"><?= e(t('admin.job_applications.education')); ?> <?= e(job_application_short_text($application['education_detail'] ?? '', 35)); ?></div>
                                </td>
                                <td>
                                    <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-bold <?= e(job_application_status_badge_class($statusValue)); ?>"><?= e($statusLabel); ?></span>
                                    <div class="text-xs text-slate-500"><?= e(t('admin.job_applications.created_at_label')); ?> <?= e(job_application_format_datetime((string) ($application['created_at'] ?? ''))); ?></div>
                                </td>
                                <td>
                                    <div class="text-sm text-slate-700" data-full-value="<?= e((string) ($application['hr_note'] ?? '')); ?>"><?= e(job_application_short_text($application['hr_note'] ?? '', 80)); ?></div>
                                </td>
                                <td>
                                    <?php if ((int) ($application['converted_user_id'] ?? 0) > 0): ?>
                                        <button
                                            type="button"
                                            class="font-semibold text-blue-700 hover:underline"
                                            data-admin-row-detail="1"
                                            data-detail-url="<?= e(page_url('users-admin', ['edit' => (int) $application['converted_user_id'], 'search' => $searchQuery])); ?>"
                                        >User #<?= (int) $application['converted_user_id']; ?></button>
                                    <?php else: ?>
                                        <span class="text-xs font-semibold text-slate-500"><?= e(t('admin.job_applications.not_converted')); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="inline-flex flex-wrap items-center gap-2">
                                        <button
                                            type="button"
                                            class="admin-row-detail-button admin-action-icon-btn"
                                            data-action-kind="detail"
                                            data-admin-row-detail="1"
                                            data-detail-url="<?= e(page_url('job-applications-manage', ['edit' => (int) $application['id'], 'application_page' => $applicationPage, 'application_per_page' => $applicationPerPage, 'status' => $statusFilter, 'search' => $searchQuery])); ?>"
                                            data-skip-action-icon="1"
                                            title="<?= e(t('admin.common.view_detail')); ?>"
                                            aria-label="<?= e(t('admin.common.view_detail')); ?>"
                                        >
                                            <span class="admin-action-icon-label"><?= e(t('admin.common.view_detail')); ?></span>
                                            <span class="admin-action-icon-glyph" aria-hidden="true">
                                                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"></circle><path d="M2 12s3.5-6.5 10-6.5S22 12 22 12s-3.5 6.5-10 6.5S2 12 2 12z"></path></svg>
                                            </span>
                                        </button>
                                        <a
                                            href="<?= e(page_url('job-applications-manage', ['edit' => (int) $application['id'], 'application_page' => $applicationPage, 'application_per_page' => $applicationPerPage, 'status' => $statusFilter, 'search' => $searchQuery])); ?>"
                                            class="admin-action-icon-btn"
                                            data-action-kind="edit"
                                            data-skip-action-icon="1"
                                            title="<?= e(t('admin.job_applications.process_action')); ?>"
                                            aria-label="<?= e(t('admin.job_applications.process_action')); ?>"
                                        >
                                            <span class="admin-action-icon-label"><?= e(t('admin.job_applications.process_action')); ?></span>
                                            <span class="admin-action-icon-glyph" aria-hidden="true">
                                                <svg viewBox="0 0 24 24"><path d="M12 20h9"></path><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"></path></svg>
                                            </span>
                                        </a>
                                        <?php if ($canDeleteApplication): ?>
                                            <form class="inline-block" method="post" action="/api/applications/delete?id=<?= (int) $application['id']; ?>" onsubmit="return confirm('<?= e(t('admin.job_applications.delete_confirm')); ?>');">
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
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if ($applicationTotal > 0): ?>
                <div data-ajax-pagination="1" class="border-t border-slate-200 bg-slate-50/80 px-3 py-2">
                    <div class="flex flex-wrap items-center gap-2 text-xs text-slate-600">
                        <span data-ajax-row-info="1" class="min-w-0 flex-1 font-medium"><?= e(t('admin.job_applications.page_info', ['current' => (int) $applicationPage, 'total' => (int) $applicationTotalPages, 'count' => (int) $applicationTotal])); ?></span>
                        <div class="ml-auto inline-flex items-center gap-1.5">
                            <form class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2 py-1" method="get" action="<?= e(page_url('job-applications-manage')); ?>">
                                <input type="hidden" name="status" value="<?= e($statusFilter); ?>">
                                <input type="hidden" name="search" value="<?= e($searchQuery); ?>">
                                <label class="text-[11px] font-semibold text-slate-500" for="application-per-page"><?= e(t('admin.common.rows')); ?></label>
                                <select id="application-per-page" name="application_per_page" data-ajax-per-page="1" class="h-7 rounded-md border border-slate-200 bg-white px-2 text-xs font-semibold text-slate-700">
                                    <?php foreach ($applicationPerPageOptions as $option): ?>
                                        <option value="<?= (int) $option; ?>" <?= $applicationPerPage === (int) $option ? 'selected' : ''; ?>><?= (int) $option; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>

                            <?php if ($applicationPage > 1): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('job-applications-manage', ['application_page' => $applicationPage - 1, 'application_per_page' => $applicationPerPage, 'status' => $statusFilter, 'search' => $searchQuery])); ?>"><?= e(t('admin.common.previous')); ?></a>
                            <?php else: ?>
                                <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400"><?= e(t('admin.common.previous')); ?></span>
                            <?php endif; ?>

                            <?php if ($applicationPage < $applicationTotalPages): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('job-applications-manage', ['application_page' => $applicationPage + 1, 'application_per_page' => $applicationPerPage, 'status' => $statusFilter, 'search' => $searchQuery])); ?>"><?= e(t('admin.common.next')); ?></a>
                            <?php else: ?>
                                <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400"><?= e(t('admin.common.next')); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        </div>
    </article>
</div>
<?php if (is_array($editingApplication)): ?>
<script>
window.addEventListener('load', function () {
    const autoOpenLink = document.getElementById('job-application-edit-auto-open');
    if (!(autoOpenLink instanceof HTMLAnchorElement) || autoOpenLink.dataset.opened === '1') {
        return;
    }

    window.setTimeout(function () {
        if (autoOpenLink.dataset.opened === '1') {
            return;
        }

        if (typeof window.__openAdminEditModal !== 'function') {
            return;
        }

        autoOpenLink.dataset.opened = '1';
        window.__openAdminEditModal(autoOpenLink.href);
    }, 0);
}, { once: true });
</script>
<?php endif; ?>
<?php if ($highlightApplicationId > 0): ?>
<script>
window.addEventListener('load', function () {
    const targetRow = document.getElementById('job-application-row-<?= (int) $highlightApplicationId; ?>');
    if (!(targetRow instanceof HTMLElement)) {
        return;
    }

    window.setTimeout(function () {
        targetRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }, 180);
}, { once: true });
</script>
<?php endif; ?>
