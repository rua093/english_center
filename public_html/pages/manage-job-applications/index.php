<?php
declare(strict_types=1);

require_admin_or_staff();
require_permission('job_application.manage');

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
            'new' => 'is-pending',
            'interviewed' => 'is-upcoming',
            'official' => 'is-approved',
            'rejected' => 'is-rejected',
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

$adminModel = new AdminModel();
$statusOptions = [
    'new' => 'Moi nhan',
    'interviewed' => 'Da phong van',
    'official' => 'Da trung tuyen',
    'rejected' => 'Khong dat',
];

$statusFilter = strtolower(trim((string) ($_GET['application_status'] ?? '')));
if (!isset($statusOptions[$statusFilter])) {
    $statusFilter = '';
}

$applicationPage = max(1, (int) ($_GET['application_page'] ?? 1));
$applicationPerPage = ui_pagination_resolve_per_page('application_per_page', 10);
$applicationTotal = $adminModel->countJobApplications($statusFilter === '' ? null : $statusFilter);
$applicationTotalPages = max(1, (int) ceil($applicationTotal / $applicationPerPage));
if ($applicationPage > $applicationTotalPages) {
    $applicationPage = $applicationTotalPages;
}

$applications = $adminModel->listJobApplicationsPage(
    $applicationPage,
    $applicationPerPage,
    $statusFilter === '' ? null : $statusFilter
);
$applicationPerPageOptions = ui_pagination_per_page_options();

$statusSummary = [];
foreach ($statusOptions as $statusKey => $statusLabel) {
    $statusSummary[$statusKey] = [
        'label' => $statusLabel,
        'count' => $adminModel->countJobApplications($statusKey),
        'badgeClass' => job_application_status_badge_class($statusKey),
    ];
}

$editingApplication = null;
if (!empty($_GET['edit'])) {
    $editingApplication = $adminModel->findJobApplication((int) $_GET['edit']);
}

$module = 'job-applications';
$adminTitle = 'Quan ly ho so ung tuyen giao vien';
$adminDescription = 'Theo doi thong tin ung vien, cap nhat trang thai phong van va chuyen doi thanh tai khoan giao vien.';

$success = get_flash('success');
$error = get_flash('error');
$canConvertApplication = has_permission('admin.user.manage');
?>
<div class="grid gap-4">
    <?php if ($success): ?>
        <div class="rounded-xl border-l-4 border-emerald-500 bg-emerald-50 p-3 text-sm text-emerald-700"><?= e($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="rounded-xl border-l-4 border-rose-500 bg-rose-50 p-3 text-sm text-rose-700"><?= e($error); ?></div>
    <?php endif; ?>

    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3>Tong quan pipeline ung tuyen giao vien</h3>
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
            $editingStatus = (string) ($editingApplication['status'] ?? 'new');
            $editingStatusLabel = (string) ($statusOptions[$editingStatus] ?? $editingStatus);
        ?>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h3 class="mb-1">Xu ly ho so #<?= (int) ($editingApplication['id'] ?? 0); ?> - <?= e((string) ($editingApplication['full_name'] ?? '')); ?></h3>
                    <p class="text-sm text-slate-600">Tao luc: <strong><?= e(job_application_format_datetime((string) ($editingApplication['created_at'] ?? ''))); ?></strong></p>
                </div>
                <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-extrabold <?= e(job_application_status_badge_class($editingStatus)); ?>">
                    <?= e($editingStatusLabel); ?>
                </span>
            </div>

            <div class="grid gap-3 xl:grid-cols-3">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <h4 class="mb-2 text-sm font-extrabold text-slate-800">Thong tin ung vien</h4>
                    <dl class="grid gap-1 text-sm text-slate-700">
                        <div><dt class="inline font-semibold">Ho ten:</dt> <dd class="inline"><?= e(job_application_value_or_dash($editingApplication['full_name'] ?? '')); ?></dd></div>
                        <div><dt class="inline font-semibold">Email:</dt> <dd class="inline"><?= e(job_application_value_or_dash($editingApplication['email'] ?? '')); ?></dd></div>
                        <div><dt class="inline font-semibold">SDT:</dt> <dd class="inline"><?= e(job_application_value_or_dash($editingApplication['phone'] ?? '')); ?></dd></div>
                        <div><dt class="inline font-semibold">Dia chi:</dt> <dd class="inline"><?= e(job_application_value_or_dash($editingApplication['address'] ?? '')); ?></dd></div>
                        <div><dt class="inline font-semibold">Vi tri:</dt> <dd class="inline"><?= e(job_application_value_or_dash($editingApplication['position_applied'] ?? '')); ?></dd></div>
                        <div><dt class="inline font-semibold">Hinh thuc:</dt> <dd class="inline"><?= e(job_application_value_or_dash($editingApplication['work_mode'] ?? '')); ?></dd></div>
                        <div><dt class="inline font-semibold">San sang di lam:</dt> <dd class="inline"><?= e(job_application_value_or_dash($editingApplication['start_date'] ?? '')); ?></dd></div>
                        <div><dt class="inline font-semibold">Muc luong mong muon:</dt> <dd class="inline"><?= e(job_application_value_or_dash($editingApplication['salary_expectation'] ?? '')); ?></dd></div>
                        <div><dt class="inline font-semibold">CV:</dt> <dd class="inline"><?= e(job_application_value_or_dash($editingApplication['cv_file_url'] ?? '')); ?></dd></div>
                    </dl>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <h4 class="mb-2 text-sm font-extrabold text-slate-800">Ho so chuyen mon</h4>
                    <dl class="grid gap-2 text-sm text-slate-700">
                        <div>
                            <dt class="font-semibold">Hoc van</dt>
                            <dd><?= e(job_application_value_or_dash($editingApplication['education_detail'] ?? '')); ?></dd>
                        </div>
                        <div>
                            <dt class="font-semibold">Kinh nghiem</dt>
                            <dd><?= e(job_application_value_or_dash($editingApplication['work_history'] ?? '')); ?></dd>
                        </div>
                        <div>
                            <dt class="font-semibold">Ky nang</dt>
                            <dd><?= e(job_application_value_or_dash($editingApplication['skills_set'] ?? '')); ?></dd>
                        </div>
                    </dl>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-3">
                    <h4 class="mb-2 text-sm font-extrabold text-slate-800">Cap nhat quy trinh tuyen dung</h4>
                    <form class="grid gap-2" method="post" action="/api/applications/update">
                        <?= csrf_input(); ?>
                        <input type="hidden" name="id" value="<?= (int) ($editingApplication['id'] ?? 0); ?>">
                        <label>
                            Trang thai
                            <select name="status" required>
                                <?php foreach ($statusOptions as $statusValue => $statusLabel): ?>
                                    <option value="<?= e($statusValue); ?>" <?= $editingStatus === $statusValue ? 'selected' : ''; ?>><?= e($statusLabel); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label>
                            Ghi chu HR
                            <textarea name="hr_note" rows="4" placeholder="Vi du: Da phong van vong 1, hen day demo vao thu 7..."><?= e((string) ($editingApplication['hr_note'] ?? '')); ?></textarea>
                        </label>
                        <div>
                            <button class="<?= ui_btn_primary_classes('sm'); ?>" type="submit">Luu cap nhat</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="mt-3 grid gap-3 md:grid-cols-2">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <h4 class="mb-2 text-sm font-extrabold text-slate-800">Gioi thieu ban than</h4>
                    <p class="text-sm leading-relaxed text-slate-700"><?= nl2br(e(job_application_value_or_dash($editingApplication['bio_summary'] ?? ''))); ?></p>
                </div>

                <?php if ((int) ($editingApplication['converted_user_id'] ?? 0) > 0): ?>
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-700">
                        <p class="font-semibold">Ho so nay da duoc chuyen thanh tai khoan giao vien.</p>
                        <p class="mt-1">Ma tai khoan: <a class="font-bold text-emerald-800 underline" href="<?= e(page_url('users-admin', ['edit' => (int) $editingApplication['converted_user_id']])); ?>">#<?= (int) $editingApplication['converted_user_id']; ?></a></p>
                        <p class="mt-1 text-xs">Thoi gian chuyen doi: <?= e(job_application_format_datetime((string) ($editingApplication['converted_at'] ?? ''))); ?></p>
                    </div>
                <?php elseif ($canConvertApplication): ?>
                    <div class="rounded-xl border border-blue-200 bg-blue-50 p-3">
                        <h4 class="mb-2 text-sm font-extrabold text-blue-900">Tao tai khoan giao vien</h4>
                        <form class="grid gap-2 md:grid-cols-2" method="post" action="/api/applications/convert">
                            <?= csrf_input(); ?>
                            <input type="hidden" name="id" value="<?= (int) ($editingApplication['id'] ?? 0); ?>">
                            <label>
                                Username
                                <input type="text" name="username" value="<?= e(job_application_suggested_username($editingApplication)); ?>" required>
                            </label>
                            <label>
                                Mat khau (de trong dung 123456)
                                <input type="text" name="password" value="">
                            </label>
                            <label class="md:col-span-2">
                                Ghi chu khi chuyen doi
                                <textarea name="admin_note" rows="2"><?= e((string) ($editingApplication['hr_note'] ?? '')); ?></textarea>
                            </label>
                            <div class="md:col-span-2">
                                <button class="<?= ui_btn_primary_classes('sm'); ?>" type="submit">Tao user giao vien</button>
                            </div>
                        </form>
                        <p class="mt-2 text-xs font-semibold text-blue-700">Luu y: chi chuyen doi khi ung vien da qua phong van.</p>
                    </div>
                <?php endif; ?>
            </div>
        </article>
    <?php endif; ?>

    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
            <div>
                <h3 class="mb-1">Danh sach ho so ung tuyen</h3>
                <p class="text-sm text-slate-600">Bang chi hien thi tom tat. Bam Xem chi tiet hoac Xu ly de mo toan bo ho so.</p>
            </div>
            <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-bold text-slate-600">Tong: <?= (int) $applicationTotal; ?> ho so</span>
        </div>

        <div class="table-filter-bar">
            <form class="table-filter-controls" method="get" action="<?= e(page_url('job-applications-manage')); ?>">
                <input type="hidden" name="application_per_page" value="<?= (int) $applicationPerPage; ?>">
                <label class="text-xs font-semibold text-slate-500" for="application-status-filter">Trang thai</label>
                <select id="application-status-filter" name="application_status">
                    <option value="">Tat ca trang thai</option>
                    <?php foreach ($statusOptions as $statusValue => $statusLabel): ?>
                        <option value="<?= e($statusValue); ?>" <?= $statusFilter === $statusValue ? 'selected' : ''; ?>><?= e($statusLabel); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Ap dung loc</button>
                <?php if ($statusFilter !== ''): ?>
                    <a class="inline-flex items-center rounded-md border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('job-applications-manage', ['application_per_page' => $applicationPerPage])); ?>">Bo loc</a>
                <?php endif; ?>
            </form>
            <span class="table-filter-counter">Trang <?= (int) $applicationPage; ?>/<?= (int) $applicationTotalPages; ?></span>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full border-collapse text-sm" data-enable-row-detail="1" data-disable-global-filter="1">
                <thead>
                    <tr>
                        <th>Ma</th>
                        <th>Ung vien</th>
                        <th>Ho so chuyen mon</th>
                        <th>Trang thai</th>
                        <th>Ghi chu HR</th>
                        <th>Chuyen doi</th>
                        <th>Hanh dong</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($applications)): ?>
                        <tr>
                            <td colspan="7">
                                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">Chua co ho so ung tuyen nao.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($applications as $application): ?>
                            <?php
                                $statusValue = (string) ($application['status'] ?? 'new');
                                $statusLabel = (string) ($statusOptions[$statusValue] ?? $statusValue);
                            ?>
                            <tr>
                                <td class="font-semibold">#<?= (int) $application['id']; ?></td>
                                <td>
                                    <div class="font-bold text-slate-800"><?= e(job_application_value_or_dash($application['full_name'] ?? '')); ?></div>
                                    <div class="text-xs text-slate-600">Lien he: <?= e(job_application_short_text(trim((string) (($application['email'] ?? '') . ' ' . ($application['phone'] ?? ''))), 50)); ?></div>
                                </td>
                                <td>
                                    <div class="font-semibold text-slate-700">Vi tri: <?= e(job_application_short_text($application['position_applied'] ?? '', 40)); ?></div>
                                    <div class="text-xs text-slate-600">Kinh nghiem: <?= e(job_application_short_text($application['work_history'] ?? '', 35)); ?></div>
                                    <div class="text-xs text-slate-500">Hoc van: <?= e(job_application_short_text($application['education_detail'] ?? '', 35)); ?></div>
                                </td>
                                <td>
                                    <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-bold <?= e(job_application_status_badge_class($statusValue)); ?>"><?= e($statusLabel); ?></span>
                                    <div class="text-xs text-slate-500">Tao luc: <?= e(job_application_format_datetime((string) ($application['created_at'] ?? ''))); ?></div>
                                </td>
                                <td>
                                    <div class="text-sm text-slate-700" data-full-value="<?= e((string) ($application['hr_note'] ?? '')); ?>"><?= e(job_application_short_text($application['hr_note'] ?? '', 80)); ?></div>
                                </td>
                                <td>
                                    <?php if ((int) ($application['converted_user_id'] ?? 0) > 0): ?>
                                        <a class="font-semibold text-blue-700 hover:underline" href="<?= e(page_url('users-admin', ['edit' => (int) $application['converted_user_id']])); ?>">User #<?= (int) $application['converted_user_id']; ?></a>
                                    <?php else: ?>
                                        <span class="text-xs font-semibold text-slate-500">Chua tao user</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="inline-flex flex-wrap items-center gap-2">
                                        <button
                                            type="button"
                                            class="admin-row-detail-button admin-action-icon-btn"
                                            data-action-kind="detail"
                                            data-admin-row-detail="1"
                                            data-detail-url="<?= e(page_url('job-applications-manage', ['edit' => (int) $application['id'], 'application_page' => $applicationPage, 'application_per_page' => $applicationPerPage, 'application_status' => $statusFilter])); ?>"
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
                                            href="<?= e(page_url('job-applications-manage', ['edit' => (int) $application['id'], 'application_page' => $applicationPage, 'application_per_page' => $applicationPerPage, 'application_status' => $statusFilter])); ?>"
                                            class="admin-action-icon-btn"
                                            data-action-kind="edit"
                                            data-skip-action-icon="1"
                                            title="Xu ly"
                                            aria-label="Xu ly"
                                        >
                                            <span class="admin-action-icon-label">Xu ly</span>
                                            <span class="admin-action-icon-glyph" aria-hidden="true">
                                                <svg viewBox="0 0 24 24"><path d="M12 20h9"></path><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"></path></svg>
                                            </span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if ($applicationTotal > 0): ?>
                <div class="border-t border-slate-200 bg-slate-50/80 px-3 py-2">
                    <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-slate-600">
                        <span class="font-medium">Hien thi trang <?= (int) $applicationPage; ?>/<?= (int) $applicationTotalPages; ?> • Tong <?= (int) $applicationTotal; ?> ho so</span>
                        <div class="inline-flex items-center gap-1.5">
                            <form class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2 py-1" method="get" action="<?= e(page_url('job-applications-manage')); ?>">
                                <input type="hidden" name="application_status" value="<?= e($statusFilter); ?>">
                                <label class="text-[11px] font-semibold text-slate-500" for="application-per-page">So dong</label>
                                <select id="application-per-page" name="application_per_page" class="h-7 rounded-md border border-slate-200 bg-white px-2 text-xs font-semibold text-slate-700" onchange="this.form.submit()">
                                    <?php foreach ($applicationPerPageOptions as $option): ?>
                                        <option value="<?= (int) $option; ?>" <?= $applicationPerPage === (int) $option ? 'selected' : ''; ?>><?= (int) $option; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>

                            <?php if ($applicationPage > 1): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('job-applications-manage', ['application_page' => $applicationPage - 1, 'application_per_page' => $applicationPerPage, 'application_status' => $statusFilter])); ?>">Truoc</a>
                            <?php else: ?>
                                <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400">Truoc</span>
                            <?php endif; ?>

                            <?php if ($applicationPage < $applicationTotalPages): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('job-applications-manage', ['application_page' => $applicationPage + 1, 'application_per_page' => $applicationPerPage, 'application_status' => $statusFilter])); ?>">Sau</a>
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
