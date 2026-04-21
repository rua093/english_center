<?php
declare(strict_types=1);

require_admin_or_staff();
require_permission('student_lead.manage');

if (!function_exists('student_lead_suggested_username')) {
    function student_lead_suggested_username(array $lead): string
    {
        $email = strtolower(trim((string) ($lead['email'] ?? '')));
        if ($email !== '' && str_contains($email, '@')) {
            $localPart = (string) strstr($email, '@', true);
            if ($localPart !== '') {
                return $localPart;
            }
        }

        $phoneDigits = preg_replace('/\D+/', '', (string) ($lead['phone'] ?? ''));
        if (is_string($phoneDigits) && $phoneDigits !== '') {
            return 'student' . substr($phoneDigits, -6);
        }

        return 'student' . date('His');
    }
}

if (!function_exists('student_lead_status_badge_class')) {
    function student_lead_status_badge_class(string $status): string
    {
        return match ($status) {
            'new' => 'is-pending',
            'entry_tested' => 'is-upcoming',
            'trial_completed' => 'is-trial',
            'official' => 'is-approved',
            'cancelled' => 'is-rejected',
            default => 'is-pending',
        };
    }
}

if (!function_exists('student_lead_value_or_dash')) {
    function student_lead_value_or_dash(mixed $value): string
    {
        $normalized = trim((string) $value);
        return $normalized === '' ? '—' : $normalized;
    }
}

if (!function_exists('student_lead_short_text')) {
    function student_lead_short_text(mixed $value, int $limit = 120): string
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

if (!function_exists('student_lead_format_datetime')) {
    function student_lead_format_datetime(?string $value): string
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
    'entry_tested' => 'Đã test đầu vào',
    'trial_completed' => 'Đã học thử',
    'official' => 'Đã chính thức',
    'cancelled' => 'Không tiếp tục',
];

$statusFilter = strtolower(trim((string) ($_GET['lead_status'] ?? '')));
if (!isset($statusOptions[$statusFilter])) {
    $statusFilter = '';
}

$leadPage = max(1, (int) ($_GET['lead_page'] ?? 1));
$leadPerPage = ui_pagination_resolve_per_page('lead_per_page', 10);
$leadTotal = $adminModel->countStudentLeads($statusFilter === '' ? null : $statusFilter);
$leadTotalPages = max(1, (int) ceil($leadTotal / $leadPerPage));
if ($leadPage > $leadTotalPages) {
    $leadPage = $leadTotalPages;
}

$leads = $adminModel->listStudentLeadsPage($leadPage, $leadPerPage, $statusFilter === '' ? null : $statusFilter);
$leadPerPageOptions = ui_pagination_per_page_options();

$statusSummary = [];
foreach ($statusOptions as $statusKey => $statusLabel) {
    $statusSummary[$statusKey] = [
        'label' => $statusLabel,
        'count' => $adminModel->countStudentLeads($statusKey),
        'badgeClass' => student_lead_status_badge_class($statusKey),
    ];
}

$editingLead = null;
if (!empty($_GET['edit'])) {
    $editingLead = $adminModel->findStudentLead((int) $_GET['edit']);
}

$module = 'student-leads';
$adminTitle = 'Quản lý lead học viên';
$adminDescription = 'Theo dõi chi tiết pipeline tư vấn học viên từ lúc tiếp nhận, test đầu vào, học thử đến chuyển đổi tài khoản chính thức.';

$success = get_flash('success');
$error = get_flash('error');
$canConvertLead = has_permission('admin.user.manage');
?>
<div class="grid gap-4">
    <?php if ($success): ?>
        <div class="rounded-xl border-l-4 border-emerald-500 bg-emerald-50 p-3 text-sm text-emerald-700"><?= e($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="rounded-xl border-l-4 border-rose-500 bg-rose-50 p-3 text-sm text-rose-700"><?= e($error); ?></div>
    <?php endif; ?>

    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3>Tổng quan pipeline lead học viên</h3>
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
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

    <?php if (is_array($editingLead)): ?>
        <?php
            $editingStatus = (string) ($editingLead['status'] ?? 'new');
            $editingStatusLabel = (string) ($statusOptions[$editingStatus] ?? $editingStatus);
        ?>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h3 class="mb-1">Xử lý lead #<?= (int) ($editingLead['id'] ?? 0); ?> - <?= e((string) ($editingLead['full_name'] ?? '')); ?></h3>
                    <p class="text-sm text-slate-600">Nguồn: <strong><?= e(student_lead_value_or_dash($editingLead['source'] ?? 'website')); ?></strong> • Tạo lúc: <strong><?= e(student_lead_format_datetime((string) ($editingLead['created_at'] ?? ''))); ?></strong></p>
                </div>
                <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-extrabold <?= e(student_lead_status_badge_class($editingStatus)); ?>">
                    <?= e($editingStatusLabel); ?>
                </span>
            </div>

            <div class="grid gap-3 xl:grid-cols-3">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <h4 class="mb-2 text-sm font-extrabold text-slate-800">Thông tin học viên</h4>
                    <dl class="grid gap-1 text-sm text-slate-700">
                        <div><dt class="inline font-semibold">Họ tên:</dt> <dd class="inline"><?= e(student_lead_value_or_dash($editingLead['full_name'] ?? '')); ?></dd></div>
                        <div><dt class="inline font-semibold">Tuổi:</dt> <dd class="inline"><?= e((string) ((int) ($editingLead['age'] ?? 0) > 0 ? (int) $editingLead['age'] : '—')); ?></dd></div>
                        <div><dt class="inline font-semibold">Điện thoại:</dt> <dd class="inline"><?= e(student_lead_value_or_dash($editingLead['phone'] ?? '')); ?></dd></div>
                        <div><dt class="inline font-semibold">Email:</dt> <dd class="inline"><?= e(student_lead_value_or_dash($editingLead['email'] ?? '')); ?></dd></div>
                        <div><dt class="inline font-semibold">Trường học:</dt> <dd class="inline"><?= e(student_lead_value_or_dash($editingLead['school_name'] ?? '')); ?></dd></div>
                    </dl>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <h4 class="mb-2 text-sm font-extrabold text-slate-800">Thông tin phụ huynh & mục tiêu</h4>
                    <dl class="grid gap-1 text-sm text-slate-700">
                        <div><dt class="inline font-semibold">Phụ huynh:</dt> <dd class="inline"><?= e(student_lead_value_or_dash($editingLead['parent_name'] ?? '')); ?></dd></div>
                        <div><dt class="inline font-semibold">SĐT phụ huynh:</dt> <dd class="inline"><?= e(student_lead_value_or_dash($editingLead['parent_phone'] ?? '')); ?></dd></div>
                        <div><dt class="inline font-semibold">Chương trình:</dt> <dd class="inline"><?= e(student_lead_value_or_dash($editingLead['target_program'] ?? '')); ?></dd></div>
                        <div><dt class="inline font-semibold">Mục tiêu điểm:</dt> <dd class="inline"><?= e(student_lead_value_or_dash($editingLead['target_score'] ?? '')); ?></dd></div>
                        <div><dt class="inline font-semibold">Lịch mong muốn:</dt> <dd class="inline"><?= e(student_lead_value_or_dash($editingLead['desired_schedule'] ?? '')); ?></dd></div>
                    </dl>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-3">
                    <h4 class="mb-2 text-sm font-extrabold text-slate-800">Cập nhật quy trình</h4>
                    <form class="grid gap-2" method="post" action="/api/leads/update">
                        <?= csrf_input(); ?>
                        <input type="hidden" name="id" value="<?= (int) ($editingLead['id'] ?? 0); ?>">
                        <label>
                            Trạng thái
                            <select name="status" required>
                                <?php foreach ($statusOptions as $statusValue => $statusLabel): ?>
                                    <option value="<?= e($statusValue); ?>" <?= $editingStatus === $statusValue ? 'selected' : ''; ?>><?= e($statusLabel); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label>
                            Ghi chú xử lý nội bộ
                            <textarea name="admin_note" rows="4" placeholder="Ví dụ: Đã gọi phụ huynh, hẹn test đầu vào vào chiều thứ 6..."><?= e((string) ($editingLead['admin_note'] ?? '')); ?></textarea>
                        </label>
                        <div>
                            <button class="<?= ui_btn_primary_classes('sm'); ?>" type="submit">Lưu cập nhật</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="mt-3 grid gap-3 md:grid-cols-2">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <h4 class="mb-2 text-sm font-extrabold text-slate-800">Ghi chú đăng ký từ khách hàng</h4>
                    <p class="text-sm leading-relaxed text-slate-700"><?= nl2br(e(student_lead_value_or_dash($editingLead['note'] ?? ''))); ?></p>
                </div>

                <?php if ((int) ($editingLead['converted_user_id'] ?? 0) > 0): ?>
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-700">
                        <p class="font-semibold">Lead này đã được chuyển đổi thành học viên chính thức.</p>
                        <p class="mt-1">Mã tài khoản: <a class="font-bold text-emerald-800 underline" href="<?= e(page_url('users-admin', ['edit' => (int) $editingLead['converted_user_id']])); ?>">#<?= (int) $editingLead['converted_user_id']; ?></a></p>
                        <p class="mt-1 text-xs">Thời gian chuyển đổi: <?= e(student_lead_format_datetime((string) ($editingLead['converted_at'] ?? ''))); ?></p>
                    </div>
                <?php elseif ($canConvertLead): ?>
                    <div class="rounded-xl border border-blue-200 bg-blue-50 p-3">
                        <h4 class="mb-2 text-sm font-extrabold text-blue-900">Tạo tài khoản học viên chính thức</h4>
                        <form class="grid gap-2 md:grid-cols-2" method="post" action="/api/leads/convert">
                            <?= csrf_input(); ?>
                            <input type="hidden" name="id" value="<?= (int) ($editingLead['id'] ?? 0); ?>">
                            <label>
                                Username
                                <input type="text" name="username" value="<?= e(student_lead_suggested_username($editingLead)); ?>" required>
                            </label>
                            <label>
                                Mật khẩu (để trống dùng 123456)
                                <input type="text" name="password" value="">
                            </label>
                            <label class="md:col-span-2">
                                Ghi chú khi chuyển đổi
                                <textarea name="admin_note" rows="2"><?= e((string) ($editingLead['admin_note'] ?? '')); ?></textarea>
                            </label>
                            <div class="md:col-span-2">
                                <button class="<?= ui_btn_primary_classes('sm'); ?>" type="submit">Tạo user học viên</button>
                            </div>
                        </form>
                        <p class="mt-2 text-xs font-semibold text-blue-700">Lưu ý: chỉ chuyển đổi khi lead đã qua giai đoạn học thử.</p>
                    </div>
                <?php endif; ?>
            </div>
        </article>
    <?php endif; ?>

    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
            <div>
                <h3 class="mb-1">Danh sách lead học viên</h3>
                <p class="text-sm text-slate-600">Bảng chỉ hiển thị thông tin tóm tắt. Bấm Xem chi tiết hoặc Xử lý để xem toàn bộ hồ sơ.</p>
            </div>
            <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-bold text-slate-600">Tổng: <?= (int) $leadTotal; ?> lead</span>
        </div>

        <div class="table-filter-bar">
            <form class="table-filter-controls" method="get" action="<?= e(page_url('student-leads-manage')); ?>">
                <input type="hidden" name="lead_per_page" value="<?= (int) $leadPerPage; ?>">
                <label class="text-xs font-semibold text-slate-500" for="lead-status-filter">Trạng thái</label>
                <select id="lead-status-filter" name="lead_status">
                    <option value="">Tất cả trạng thái</option>
                    <?php foreach ($statusOptions as $statusValue => $statusLabel): ?>
                        <option value="<?= e($statusValue); ?>" <?= $statusFilter === $statusValue ? 'selected' : ''; ?>><?= e($statusLabel); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Áp dụng lọc</button>
                <?php if ($statusFilter !== ''): ?>
                    <a class="inline-flex items-center rounded-md border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('student-leads-manage', ['lead_per_page' => $leadPerPage])); ?>">Bỏ lọc</a>
                <?php endif; ?>
            </form>
            <span class="table-filter-counter">Trang <?= (int) $leadPage; ?>/<?= (int) $leadTotalPages; ?></span>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full border-collapse text-sm" data-enable-row-detail="1" data-disable-global-filter="1">
                <thead>
                    <tr>
                        <th>Mã</th>
                        <th>Học viên & phụ huynh</th>
                        <th>Mục tiêu học</th>
                        <th>Trạng thái</th>
                        <th>Ghi chú xử lý</th>
                        <th>Chuyển đổi</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($leads)): ?>
                        <tr>
                            <td colspan="7">
                                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">Chưa có lead học viên nào.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($leads as $lead): ?>
                            <?php
                                $statusValue = (string) ($lead['status'] ?? 'new');
                                $statusLabel = (string) ($statusOptions[$statusValue] ?? $statusValue);
                            ?>
                            <tr>
                                <td class="font-semibold">#<?= (int) $lead['id']; ?></td>
                                <td>
                                    <div class="font-bold text-slate-800"><?= e(student_lead_value_or_dash($lead['full_name'] ?? '')); ?></div>
                                    <div class="text-xs text-slate-600">Liên hệ: <?= e(student_lead_value_or_dash($lead['phone'] ?? '')); ?></div>
                                    <div class="text-xs text-slate-500">PH: <?= e(student_lead_value_or_dash($lead['parent_name'] ?? '')); ?></div>
                                </td>
                                <td>
                                    <div class="font-semibold text-slate-700"><?= e(student_lead_short_text($lead['target_program'] ?? '', 45)); ?></div>
                                    <div class="text-xs text-slate-600">Mục tiêu: <?= e(student_lead_value_or_dash($lead['target_score'] ?? '')); ?></div>
                                    <div class="text-xs text-slate-500">Lịch: <?= e(student_lead_short_text($lead['desired_schedule'] ?? '', 28)); ?></div>
                                </td>
                                <td>
                                    <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-bold <?= e(student_lead_status_badge_class($statusValue)); ?>"><?= e($statusLabel); ?></span>
                                    <div class="mt-1 text-xs text-slate-500">Tạo lúc: <?= e(student_lead_format_datetime((string) ($lead['created_at'] ?? ''))); ?></div>
                                </td>
                                <td>
                                    <?php $summaryNote = trim((string) ($lead['admin_note'] ?? '')) !== '' ? (string) ($lead['admin_note'] ?? '') : (string) ($lead['note'] ?? ''); ?>
                                    <div class="text-sm text-slate-700" data-full-value="<?= e($summaryNote); ?>"><?= e(student_lead_short_text($summaryNote, 80)); ?></div>
                                </td>
                                <td>
                                    <?php if ((int) ($lead['converted_user_id'] ?? 0) > 0): ?>
                                        <a class="font-semibold text-blue-700 hover:underline" href="<?= e(page_url('users-admin', ['edit' => (int) $lead['converted_user_id']])); ?>">User #<?= (int) $lead['converted_user_id']; ?></a>
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
                                            data-detail-url="<?= e(page_url('student-leads-manage', ['edit' => (int) $lead['id'], 'lead_page' => $leadPage, 'lead_per_page' => $leadPerPage, 'lead_status' => $statusFilter])); ?>"
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
                                            href="<?= e(page_url('student-leads-manage', ['edit' => (int) $lead['id'], 'lead_page' => $leadPage, 'lead_per_page' => $leadPerPage, 'lead_status' => $statusFilter])); ?>"
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

            <?php if ($leadTotal > 0): ?>
                <div class="border-t border-slate-200 bg-slate-50/80 px-3 py-2">
                    <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-slate-600">
                        <span class="font-medium">Hiển thị trang <?= (int) $leadPage; ?>/<?= (int) $leadTotalPages; ?> • Tổng <?= (int) $leadTotal; ?> lead</span>
                        <div class="inline-flex items-center gap-1.5">
                            <form class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2 py-1" method="get" action="<?= e(page_url('student-leads-manage')); ?>">
                                <input type="hidden" name="lead_status" value="<?= e($statusFilter); ?>">
                                <label class="text-[11px] font-semibold text-slate-500" for="lead-per-page">Số dòng</label>
                                <select id="lead-per-page" name="lead_per_page" class="h-7 rounded-md border border-slate-200 bg-white px-2 text-xs font-semibold text-slate-700" onchange="this.form.submit()">
                                    <?php foreach ($leadPerPageOptions as $option): ?>
                                        <option value="<?= (int) $option; ?>" <?= $leadPerPage === (int) $option ? 'selected' : ''; ?>><?= (int) $option; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>

                            <?php if ($leadPage > 1): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('student-leads-manage', ['lead_page' => $leadPage - 1, 'lead_per_page' => $leadPerPage, 'lead_status' => $statusFilter])); ?>">Trước</a>
                            <?php else: ?>
                                <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400">Trước</span>
                            <?php endif; ?>

                            <?php if ($leadPage < $leadTotalPages): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('student-leads-manage', ['lead_page' => $leadPage + 1, 'lead_per_page' => $leadPerPage, 'lead_status' => $statusFilter])); ?>">Sau</a>
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

