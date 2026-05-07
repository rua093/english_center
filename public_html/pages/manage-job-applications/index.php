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
    'PENDING'      => 'Mới nhận',
    'INTERVIEWING' => 'Đã phỏng vấn',
    'PASSED'       => 'Đã trúng tuyển',
    'REJECTED'     => 'Không đạt',
];

$statusFilter = strtoupper(trim((string) ($_GET['status'] ?? '')));
if (!isset($statusOptions[$statusFilter])) {
    $statusFilter = '';
}
$searchQuery = trim((string) ($_GET['search'] ?? ''));
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
$adminTitle = 'Quản lý hồ sơ ứng tuyển giáo viên';
$adminDescription = 'Theo dõi thông tin ứng viên, cập nhật trạng thái phỏng vấn và chuyển đổi thành tài khoản giáo viên.';

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
        <h3>Tổng quan pipeline ứng tuyển giáo viên</h3>
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
            $editingApplicationCanConvertNow = job_application_can_convert_status($editingStatus);
            $editingApplicationUrl = page_url('job-applications-manage', [
                'edit' => (int) ($editingApplication['id'] ?? 0),
                'application_page' => $applicationPage,
                'application_per_page' => $applicationPerPage,
                'status' => $statusFilter !== '' ? $statusFilter : null,
                'search' => $searchQuery !== '' ? $searchQuery : null,
            ]);
        ?>
        <a id="job-application-edit-auto-open" href="<?= e($editingApplicationUrl); ?>" class="hidden" aria-hidden="true" tabindex="-1">Mở popup xử lý hồ sơ</a>
        <article class="hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" aria-hidden="true" data-edit-form-source="1" data-edit-modal-mode="process" data-edit-modal-title="Xử lý hồ sơ ứng tuyển">
            <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h3 class="mb-1">Xử lý hồ sơ #<?= (int) ($editingApplication['id'] ?? 0); ?> - <?= e((string) ($editingApplication['full_name'] ?? '')); ?></h3>
                    <p class="text-sm text-slate-600">Tạo lúc: <strong><?= e(job_application_format_datetime((string) ($editingApplication['created_at'] ?? ''))); ?></strong></p>
                </div>
                <span data-process-status-badge="1" class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-extrabold <?= e(job_application_status_badge_class($editingStatus)); ?>">
                    <?= e($editingStatusLabel); ?>
                </span>
            </div>

            <div class="grid gap-3 xl:grid-cols-3">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <h4 class="mb-2 text-sm font-extrabold text-slate-800">Thông tin ứng viên</h4>
                    <dl class="grid gap-1 text-sm text-slate-700">
                        <div><dt class="inline font-semibold">Họ tên:</dt> <dd class="inline"><?= e(job_application_value_or_dash($editingApplication['full_name'] ?? '')); ?></dd></div>
                        <div><dt class="inline font-semibold">Email:</dt> <dd class="inline"><?= e(job_application_value_or_dash($editingApplication['email'] ?? '')); ?></dd></div>
                        <div><dt class="inline font-semibold">SĐT:</dt> <dd class="inline"><?= e(job_application_value_or_dash($editingApplication['phone'] ?? '')); ?></dd></div>
                        <div><dt class="inline font-semibold">Địa chỉ:</dt> <dd class="inline"><?= e(job_application_value_or_dash($editingApplication['address'] ?? '')); ?></dd></div>
                        <div><dt class="inline font-semibold">Vị trí:</dt> <dd class="inline"><?= e(job_application_value_or_dash($editingApplication['position_applied'] ?? '')); ?></dd></div>
                        <div><dt class="inline font-semibold">Hình thức:</dt> <dd class="inline"><?= e(job_application_value_or_dash($editingApplication['work_mode'] ?? '')); ?></dd></div>
                        <div><dt class="inline font-semibold">Sẵn sàng đi làm:</dt> <dd class="inline"><?= e(ui_format_date((string) ($editingApplication['start_date'] ?? ''), '—')); ?></dd></div>
                        <div><dt class="inline font-semibold">Mức lương mong muốn:</dt> <dd class="inline"><?= e(job_application_value_or_dash($editingApplication['salary_expectation'] ?? '')); ?></dd></div>
                        <div><dt class="inline font-semibold">CV:</dt> <dd class="inline"><?= e(job_application_value_or_dash($editingApplication['cv_file_url'] ?? '')); ?></dd></div>
                    </dl>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <h4 class="mb-2 text-sm font-extrabold text-slate-800">Hồ sơ chuyên môn</h4>
                    <dl class="grid gap-2 text-sm text-slate-700">
                        <div>
                            <dt class="font-semibold">Học vấn</dt>
                            <dd><?= e(job_application_value_or_dash($editingApplication['education_detail'] ?? '')); ?></dd>
                        </div>
                        <div>
                            <dt class="font-semibold">Kinh nghiệm</dt>
                            <dd><?= e(job_application_value_or_dash($editingApplication['work_history'] ?? '')); ?></dd>
                        </div>
                        <div>
                            <dt class="font-semibold">Kỹ năng</dt>
                            <dd><?= e(job_application_value_or_dash($editingApplication['skills_set'] ?? '')); ?></dd>
                        </div>
                    </dl>
                </div>

                <div
                    class="rounded-xl border border-slate-200 bg-white p-3"
                    <?= $editingApplicationConverted ? 'data-process-locked-section="1" title="Hồ sơ đã được chuyển đổi thành user nên không thể chỉnh lại quy trình."' : ''; ?>
                >
                    <h4 class="mb-2 text-sm font-extrabold text-slate-800">Cập nhật quy trình tuyển dụng</h4>
                    <form class="grid gap-2" method="post" action="/api/applications/update">
                        <?= csrf_input(); ?>
                        <input type="hidden" name="id" value="<?= (int) ($editingApplication['id'] ?? 0); ?>">
                        <fieldset class="grid gap-2" <?= $editingApplicationConverted ? 'disabled aria-disabled="true"' : ''; ?>>
                            <label>
                                Trạng thái
                                <select
                                    name="status"
                                    required
                                    data-process-status-select="1"
                                    data-is-converted="<?= $editingApplicationConverted ? '1' : '0'; ?>"
                                    data-locked-status="passed"
                                    <?= $editingApplicationConverted ? 'title="Hồ sơ đã được chuyển đổi nên không thể đổi lại trạng thái cũ."' : ''; ?>
                                >
                                    <?php foreach ($statusOptions as $statusValue => $statusLabel): ?>
                                        <?php $isLockedOutStatus = $editingApplicationConverted && $statusValue !== 'PASSED'; ?>
                                        <?php $isSelectedStatus = $editingApplicationConverted ? $statusValue === 'PASSED' : $editingStatus === $statusValue; ?>
                                        <option value="<?= e($statusValue); ?>" <?= $isSelectedStatus ? 'selected' : ''; ?> <?= $isLockedOutStatus ? 'disabled' : ''; ?>><?= e($statusLabel); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <label>
                                Ghi chú HR
                                <textarea name="hr_note" rows="4" placeholder="Ví dụ: Đã phỏng vấn vòng 1, hẹn dạy demo vào thứ 7..."><?= e((string) ($editingApplication['hr_note'] ?? '')); ?></textarea>
                            </label>
                            <div>
                                <button class="<?= ui_btn_primary_classes('sm'); ?>" type="submit">Lưu cập nhật</button>
                            </div>
                        </fieldset>
                    </form>
                </div>
            </div>

            <div class="mt-3 grid gap-3 md:grid-cols-2">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <h4 class="mb-2 text-sm font-extrabold text-slate-800">Giới thiệu bản thân</h4>
                    <p class="text-sm leading-relaxed text-slate-700"><?= nl2br(e(job_application_value_or_dash($editingApplication['bio_summary'] ?? ''))); ?></p>
                </div>

                <?php if ($editingApplicationConverted): ?>
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-700">
                        <p class="font-semibold">Hồ sơ này đã được chuyển thành tài khoản giáo viên.</p>
                        <p class="mt-1">Mã tài khoản:
                            <button
                                type="button"
                                class="font-bold text-emerald-800 underline"
                                data-admin-row-detail="1"
                                data-detail-url="<?= e(page_url('users-admin', ['edit' => (int) $editingApplication['converted_user_id']])); ?>"
                            >#<?= (int) $editingApplication['converted_user_id']; ?></button>
                        </p>
                        <p class="mt-1 text-xs">Thời gian chuyển đổi: <?= e(job_application_format_datetime((string) ($editingApplication['converted_at'] ?? ''))); ?></p>
                    </div>
                <?php elseif ($canConvertApplication): ?>
                    <div class="rounded-xl border border-blue-200 bg-blue-50 p-3">
                        <h4 class="mb-2 text-sm font-extrabold text-blue-900">Tạo tài khoản giáo viên</h4>
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
                                Username
                                <input type="text" name="username" value="<?= e(job_application_suggested_username($editingApplication)); ?>" required>
                            </label>
                            <label>
                                Mật khẩu (để trống dùng 123456)
                                <input type="text" name="password" value="">
                            </label>
                            <label class="md:col-span-2">
                                Ghi chú khi chuyển đổi
                                <textarea name="admin_note" rows="2"><?= e((string) ($editingApplication['hr_note'] ?? '')); ?></textarea>
                            </label>
                            <div class="md:col-span-2">
                                <button
                                    class="<?= ui_btn_primary_classes('sm'); ?>"
                                    type="submit"
                                    data-process-convert-button="1"
                                    <?= $editingApplicationCanConvertNow ? '' : 'disabled aria-disabled="true" title="Cần chuyển hồ sơ sang Đã phỏng vấn hoặc Đã trúng tuyển trước khi tạo user."'; ?>
                                >Tạo user giáo viên</button>
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
                <h3 class="mb-1">Danh sách hồ sơ ứng tuyển</h3>
                <p class="text-sm text-slate-600">Bảng chỉ hiển thị tóm tắt. Bấm Xem chi tiết hoặc Xử lý để mở toàn bộ hồ sơ.</p>
            </div>
            <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-bold text-slate-600">Tổng: <?= (int) $applicationTotal; ?> hồ sơ</span>
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
                    placeholder="Tìm theo tên ứng viên, email, SĐT, vị trí..."
                    autocomplete="off"
                    class="w-full max-w-sm rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100"
                >
                <select
                    name="status"
                    data-ajax-filter="1"
                    class="h-11 rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-700 shadow-sm outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100"
                >
                    <option value="">Tất cả trạng thái</option>
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
                        <th>Mã</th>
                        <th>Ứng viên</th>
                        <th>Hồ sơ chuyên môn</th>
                        <th>Trạng thái</th>
                        <th>Ghi chú HR</th>
                        <th>Chuyển đổi</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody data-ajax-tbody="1">
                    <?php if (empty($applications)): ?>
                        <tr>
                            <td colspan="7">
                                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">Chưa có hồ sơ ứng tuyển nào.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($applications as $application): ?>
                            <?php
                                $statusValue = (string) ($application['status'] ?? 'PENDING');
                                $statusLabel = (string) ($statusOptions[$statusValue] ?? $statusValue);
                            ?>
                            <tr>
                                <td class="font-semibold">#<?= (int) $application['id']; ?></td>
                                <td>
                                    <div class="font-bold text-slate-800"><?= e(job_application_value_or_dash($application['full_name'] ?? '')); ?></div>
                                    <div class="text-xs text-slate-600">Liên hệ: <?= e(job_application_short_text(trim((string) (($application['email'] ?? '') . ' ' . ($application['phone'] ?? ''))), 50)); ?></div>
                                </td>
                                <td>
                                    <div class="font-semibold text-slate-700">Vị trí: <?= e(job_application_short_text($application['position_applied'] ?? '', 40)); ?></div>
                                    <div class="text-xs text-slate-600">Kinh nghiệm: <?= e(job_application_short_text($application['work_history'] ?? '', 35)); ?></div>
                                    <div class="text-xs text-slate-500">Học vấn: <?= e(job_application_short_text($application['education_detail'] ?? '', 35)); ?></div>
                                </td>
                                <td>
                                    <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-bold <?= e(job_application_status_badge_class($statusValue)); ?>"><?= e($statusLabel); ?></span>
                                    <div class="text-xs text-slate-500">Tạo lúc: <?= e(job_application_format_datetime((string) ($application['created_at'] ?? ''))); ?></div>
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
                                        <span class="text-xs font-semibold text-slate-500">Chưa tạo user</span>
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
                                            title="Xem chi tiết"
                                            aria-label="Xem chi tiết"
                                        >
                                            <span class="admin-action-icon-label">Xem chi tiết</span>
                                            <span class="admin-action-icon-glyph" aria-hidden="true">
                                                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"></circle><path d="M2 12s3.5-6.5 10-6.5S22 12 22 12s-3.5 6.5-10 6.5S2 12 2 12z"></path></svg>
                                            </span>
                                        </button>
                                        <a
                                            href="<?= e(page_url('job-applications-manage', ['edit' => (int) $application['id'], 'application_page' => $applicationPage, 'application_per_page' => $applicationPerPage, 'status' => $statusFilter, 'search' => $searchQuery])); ?>"
                                            class="admin-action-icon-btn"
                                            data-action-kind="edit"
                                            data-skip-action-icon="1"
                                            title="Xử lý"
                                            aria-label="Xử lý"
                                        >
                                            <span class="admin-action-icon-label">Xử lý</span>
                                            <span class="admin-action-icon-glyph" aria-hidden="true">
                                                <svg viewBox="0 0 24 24"><path d="M12 20h9"></path><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"></path></svg>
                                            </span>
                                        </a>
                                        <?php if ($canDeleteApplication): ?>
                                            <form class="inline-block" method="post" action="/api/applications/delete?id=<?= (int) $application['id']; ?>" onsubmit="return confirm('Bạn có chắc muốn xóa hồ sơ ứng tuyển này?');">
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
                        <span data-ajax-row-info="1" class="min-w-0 flex-1 font-medium">Hiển thị trang <?= (int) $applicationPage; ?>/<?= (int) $applicationTotalPages; ?> • Tổng <?= (int) $applicationTotal; ?> hồ sơ</span>
                        <div class="ml-auto inline-flex items-center gap-1.5">
                            <form class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2 py-1" method="get" action="<?= e(page_url('job-applications-manage')); ?>">
                                <input type="hidden" name="status" value="<?= e($statusFilter); ?>">
                                <input type="hidden" name="search" value="<?= e($searchQuery); ?>">
                                <label class="text-[11px] font-semibold text-slate-500" for="application-per-page">Số dòng</label>
                                <select id="application-per-page" name="application_per_page" data-ajax-per-page="1" class="h-7 rounded-md border border-slate-200 bg-white px-2 text-xs font-semibold text-slate-700">
                                    <?php foreach ($applicationPerPageOptions as $option): ?>
                                        <option value="<?= (int) $option; ?>" <?= $applicationPerPage === (int) $option ? 'selected' : ''; ?>><?= (int) $option; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>

                            <?php if ($applicationPage > 1): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('job-applications-manage', ['application_page' => $applicationPage - 1, 'application_per_page' => $applicationPerPage, 'status' => $statusFilter, 'search' => $searchQuery])); ?>">Trước</a>
                            <?php else: ?>
                                <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400">Trước</span>
                            <?php endif; ?>

                            <?php if ($applicationPage < $applicationTotalPages): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('job-applications-manage', ['application_page' => $applicationPage + 1, 'application_per_page' => $applicationPerPage, 'status' => $statusFilter, 'search' => $searchQuery])); ?>">Sau</a>
                            <?php else: ?>
                                <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400">Sau</span>
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
