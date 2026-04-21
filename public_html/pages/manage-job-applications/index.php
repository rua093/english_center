<?php
declare(strict_types=1);

require_admin_or_staff();
require_permission('job_application.manage');

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

        $phoneDigits = preg_replace('/\D+/', '', (string) ($application['phone'] ?? ''));
        if (is_string($phoneDigits) && $phoneDigits !== '') {
            return 'teacher' . substr($phoneDigits, -6);
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
    'new' => 'Mới nhận',
    'interviewed' => 'Đã phỏng vấn',
    'official' => 'Đã trúng tuyển',
    'rejected' => 'Không đạt',
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
$adminTitle = 'Quản lý hồ sơ ứng tuyển giáo viên';
$adminDescription = 'Theo dõi đầy đủ thông tin ứng viên, trạng thái phỏng vấn và chuyển đổi thành tài khoản giáo viên chính thức.';

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
            $editingStatus = (string) ($editingApplication['status'] ?? 'new');
            $editingStatusLabel = (string) ($statusOptions[$editingStatus] ?? $editingStatus);
        ?>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h3 class="mb-1">Xử lý hồ sơ #<?= (int) ($editingApplication['id'] ?? 0); ?> - <?= e((string) ($editingApplication['full_name'] ?? '')); ?></h3>
                    <p class="text-sm text-slate-600">Nguồn: <strong><?= e(job_application_value_or_dash($editingApplication['source'] ?? 'website')); ?></strong> • Tạo lúc: <strong><?= e(job_application_format_datetime((string) ($editingApplication['created_at'] ?? ''))); ?></strong></p>
                </div>
                <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-extrabold <?= e(job_application_status_badge_class($editingStatus)); ?>">
                    <?= e($editingStatusLabel); ?>
                </span>
            </div>

            <div class="grid gap-3 xl:grid-cols-3">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <h4 class="mb-2 text-sm font-extrabold text-slate-800">Thông tin ứng viên</h4>
                    <dl class="grid gap-1 text-sm text-slate-700">
                        <div><dt class="inline font-semibold">Họ tên:</dt> <dd class="inline"><?= e(job_application_value_or_dash($editingApplication['full_name'] ?? '')); ?></dd></div>
                        <div><dt class="inline font-semibold">Điện thoại:</dt> <dd class="inline"><?= e(job_application_value_or_dash($editingApplication['phone'] ?? '')); ?></dd></div>
                        <div><dt class="inline font-semibold">Email:</dt> <dd class="inline"><?= e(job_application_value_or_dash($editingApplication['email'] ?? '')); ?></dd></div>
                        <div><dt class="inline font-semibold">Vị trí ứng tuyển:</dt> <dd class="inline"><?= e(job_application_value_or_dash($editingApplication['applying_position'] ?? '')); ?></dd></div>
                        <div><dt class="inline font-semibold">Bằng cấp:</dt> <dd class="inline"><?= e(job_application_value_or_dash($editingApplication['degree'] ?? '')); ?></dd></div>
                        <div><dt class="inline font-semibold">Kinh nghiệm:</dt> <dd class="inline"><?= e((string) ((int) ($editingApplication['experience_years'] ?? 0))); ?> năm</dd></div>
                        <div><dt class="inline font-semibold">Lịch có thể dạy:</dt> <dd class="inline"><?= e(job_application_value_or_dash($editingApplication['available_schedule'] ?? '')); ?></dd></div>
                    </dl>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <h4 class="mb-2 text-sm font-extrabold text-slate-800">Giới thiệu bản thân</h4>
                    <p class="text-sm leading-relaxed text-slate-700"><?= nl2br(e(job_application_value_or_dash($editingApplication['intro'] ?? ''))); ?></p>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-3">
                    <h4 class="mb-2 text-sm font-extrabold text-slate-800">Cập nhật quy trình tuyển dụng</h4>
                    <form class="grid gap-2" method="post" action="/api/applications/update">
                        <?= csrf_input(); ?>
                        <input type="hidden" name="id" value="<?= (int) ($editingApplication['id'] ?? 0); ?>">
                        <label>
                            Trạng thái
                            <select name="status" required>
                                <?php foreach ($statusOptions as $statusValue => $statusLabel): ?>
                                    <option value="<?= e($statusValue); ?>" <?= $editingStatus === $statusValue ? 'selected' : ''; ?>><?= e($statusLabel); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label>
                            Ghi chú nội bộ
                            <textarea name="admin_note" rows="4" placeholder="Ví dụ: Đã phỏng vấn vòng 1, hẹn demo lớp vào thứ 7..."><?= e((string) ($editingApplication['admin_note'] ?? '')); ?></textarea>
                        </label>
                        <div>
                            <button class="<?= ui_btn_primary_classes('sm'); ?>" type="submit">Lưu cập nhật</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="mt-3">
                <?php if ((int) ($editingApplication['converted_user_id'] ?? 0) > 0): ?>
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-700">
                        <p class="font-semibold">Hồ sơ này đã được chuyển thành tài khoản giáo viên chính thức.</p>
                        <p class="mt-1">Mã tài khoản: <a class="font-bold text-emerald-800 underline" href="<?= e(page_url('users-admin', ['edit' => (int) $editingApplication['converted_user_id']])); ?>">#<?= (int) $editingApplication['converted_user_id']; ?></a></p>
                        <p class="mt-1 text-xs">Thời gian chuyển đổi: <?= e(job_application_format_datetime((string) ($editingApplication['converted_at'] ?? ''))); ?></p>
                    </div>
                <?php elseif ($canConvertApplication): ?>
                    <div class="rounded-xl border border-blue-200 bg-blue-50 p-3">
                        <h4 class="mb-2 text-sm font-extrabold text-blue-900">Tạo tài khoản giáo viên chính thức</h4>
                        <form class="grid gap-2 md:grid-cols-2" method="post" action="/api/applications/convert">
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
                                <textarea name="admin_note" rows="2"><?= e((string) ($editingApplication['admin_note'] ?? '')); ?></textarea>
                            </label>
                            <div class="md:col-span-2">
                                <button class="<?= ui_btn_primary_classes('sm'); ?>" type="submit">Tạo user giáo viên</button>
                            </div>
                        </form>
                        <p class="mt-2 text-xs font-semibold text-blue-700">Lưu ý: chỉ chuyển đổi khi ứng viên đã qua vòng phỏng vấn.</p>
                    </div>
                <?php endif; ?>
            </div>
        </article>
    <?php endif; ?>

    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
            <div>
                <h3 class="mb-1">Danh sách hồ sơ ứng tuyển</h3>
                <p class="text-sm text-slate-600">Bảng chỉ hiển thị thông tin tóm tắt. Bấm Xem chi tiết hoặc Xử lý để xem toàn bộ hồ sơ.</p>
            </div>
            <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-bold text-slate-600">Tổng: <?= (int) $applicationTotal; ?> hồ sơ</span>
        </div>

        <div class="table-filter-bar">
            <form class="table-filter-controls" method="get" action="<?= e(page_url('job-applications-manage')); ?>">
                <input type="hidden" name="application_per_page" value="<?= (int) $applicationPerPage; ?>">
                <label class="text-xs font-semibold text-slate-500" for="application-status-filter">Trạng thái</label>
                <select id="application-status-filter" name="application_status">
                    <option value="">Tất cả trạng thái</option>
                    <?php foreach ($statusOptions as $statusValue => $statusLabel): ?>
                        <option value="<?= e($statusValue); ?>" <?= $statusFilter === $statusValue ? 'selected' : ''; ?>><?= e($statusLabel); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Áp dụng lọc</button>
                <?php if ($statusFilter !== ''): ?>
                    <a class="inline-flex items-center rounded-md border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('job-applications-manage', ['application_per_page' => $applicationPerPage])); ?>">Bỏ lọc</a>
                <?php endif; ?>
            </form>
            <span class="table-filter-counter">Trang <?= (int) $applicationPage; ?>/<?= (int) $applicationTotalPages; ?></span>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full border-collapse text-sm" data-enable-row-detail="1" data-disable-global-filter="1">
                <thead>
                    <tr>
                        <th>Mã</th>
                        <th>Ứng viên</th>
                        <th>Hồ sơ chuyên môn</th>
                        <th>Trạng thái</th>
                        <th>Ghi chú xử lý</th>
                        <th>Chuyển đổi</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($applications)): ?>
                        <tr>
                            <td colspan="7">
                                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">Chưa có hồ sơ ứng tuyển nào.</div>
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
                                    <div class="text-xs text-slate-600">SĐT: <?= e(job_application_value_or_dash($application['phone'] ?? '')); ?></div>
                                </td>
                                <td>
                                    <div class="font-semibold text-slate-700"><?= e(job_application_short_text($application['applying_position'] ?? '', 45)); ?></div>
                                    <div class="text-xs text-slate-600">Kinh nghiệm: <?= (int) ($application['experience_years'] ?? 0); ?> năm</div>
                                    <div class="text-xs text-slate-500">Bằng cấp: <?= e(job_application_short_text($application['degree'] ?? '', 30)); ?></div>
                                </td>
                                <td>
                                    <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-bold <?= e(job_application_status_badge_class($statusValue)); ?>"><?= e($statusLabel); ?></span>
                                    <div class="text-xs text-slate-500">Tạo lúc: <?= e(job_application_format_datetime((string) ($application['created_at'] ?? ''))); ?></div>
                                </td>
                                <td>
                                    <div class="text-sm text-slate-700" data-full-value="<?= e((string) ($application['admin_note'] ?? '')); ?>"><?= e(job_application_short_text($application['admin_note'] ?? '', 80)); ?></div>
                                </td>
                                <td>
                                    <?php if ((int) ($application['converted_user_id'] ?? 0) > 0): ?>
                                        <a class="font-semibold text-blue-700 hover:underline" href="<?= e(page_url('users-admin', ['edit' => (int) $application['converted_user_id']])); ?>">User #<?= (int) $application['converted_user_id']; ?></a>
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
                                            data-detail-url="<?= e(page_url('job-applications-manage', ['edit' => (int) $application['id'], 'application_page' => $applicationPage, 'application_per_page' => $applicationPerPage, 'application_status' => $statusFilter])); ?>"
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
                                            href="<?= e(page_url('job-applications-manage', ['edit' => (int) $application['id'], 'application_page' => $applicationPage, 'application_per_page' => $applicationPerPage, 'application_status' => $statusFilter])); ?>"
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
                        <span class="font-medium">Hiển thị trang <?= (int) $applicationPage; ?>/<?= (int) $applicationTotalPages; ?> • Tổng <?= (int) $applicationTotal; ?> hồ sơ</span>
                        <div class="inline-flex items-center gap-1.5">
                            <form class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2 py-1" method="get" action="<?= e(page_url('job-applications-manage')); ?>">
                                <input type="hidden" name="application_status" value="<?= e($statusFilter); ?>">
                                <label class="text-[11px] font-semibold text-slate-500" for="application-per-page">Số dòng</label>
                                <select id="application-per-page" name="application_per_page" class="h-7 rounded-md border border-slate-200 bg-white px-2 text-xs font-semibold text-slate-700" onchange="this.form.submit()">
                                    <?php foreach ($applicationPerPageOptions as $option): ?>
                                        <option value="<?= (int) $option; ?>" <?= $applicationPerPage === (int) $option ? 'selected' : ''; ?>><?= (int) $option; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>

                            <?php if ($applicationPage > 1): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('job-applications-manage', ['application_page' => $applicationPage - 1, 'application_per_page' => $applicationPerPage, 'application_status' => $statusFilter])); ?>">Trước</a>
                            <?php else: ?>
                                <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400">Trước</span>
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
