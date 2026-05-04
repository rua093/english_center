<?php
declare(strict_types=1);

require_admin_or_staff();
require_any_permission(['student_lead.view']);

if (!function_exists('student_lead_extract_email')) {
    function student_lead_extract_email(string $value): string
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

if (!function_exists('student_lead_extract_phone')) {
    function student_lead_extract_phone(string $value): string
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

if (!function_exists('student_lead_suggested_username')) {
    function student_lead_suggested_username(array $lead): string
    {
        $parentName = (string) ($lead['parent_name'] ?? '');
        $parentPhone = trim((string) ($lead['parent_phone'] ?? ''));

        $email = student_lead_extract_email($parentName);
        if ($email !== '' && str_contains($email, '@')) {
            $localPart = (string) strstr($email, '@', true);
            if ($localPart !== '') {
                return $localPart;
            }
        }

        $phoneDigits = student_lead_extract_phone($parentPhone);
        if ($phoneDigits === '') {
            $phoneDigits = student_lead_extract_phone($parentName);
        }
        if ($phoneDigits !== '') {
            return 'student' . substr($phoneDigits, -6);
        }

        $studentName = strtolower(trim((string) ($lead['student_name'] ?? '')));
        if ($studentName !== '') {
            $slug = preg_replace('/[^a-z0-9]+/', '', $studentName) ?? '';
            if ($slug !== '') {
                return 'student.' . substr($slug, 0, 24);
            }
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

        $snippet = function_exists('mb_substr') ? mb_substr($text, 0, $limit - 1) : substr($text, 0, $limit - 1);
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

if (!function_exists('student_lead_format_date')) {
    function student_lead_format_date(?string $value): string
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return '—';
        }

        try {
            $dt = new DateTimeImmutable($raw);
            return $dt->format('d/m/Y');
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

$statusFilter = strtolower(trim((string) ($_GET['status'] ?? '')));
if (!isset($statusOptions[$statusFilter])) {
    $statusFilter = '';
}
$searchQuery = trim((string) ($_GET['search'] ?? ''));
$filters = [
    'status' => $statusFilter,
];

$leadPage = max(1, (int) ($_GET['lead_page'] ?? 1));
$leadPerPage = ui_pagination_resolve_per_page('lead_per_page', 10);
$leadTotal = $adminModel->countStudentLeads($filters, $searchQuery);
$leadTotalPages = max(1, (int) ceil($leadTotal / $leadPerPage));
if ($leadPage > $leadTotalPages) {
    $leadPage = $leadTotalPages;
}

$leads = $adminModel->listStudentLeadsPage($leadPage, $leadPerPage, $filters, $searchQuery);
$leadPerPageOptions = ui_pagination_per_page_options();

$statusSummary = [];
foreach ($statusOptions as $statusKey => $statusLabel) {
    $statusSummary[$statusKey] = [
        'label' => $statusLabel,
        'count' => $adminModel->countStudentLeads(['status' => $statusKey]),
        'badgeClass' => student_lead_status_badge_class($statusKey),
    ];
}

$editingLead = null;
if (!empty($_GET['edit'])) {
    $editingLead = $adminModel->findStudentLead((int) $_GET['edit']);
}

$module = 'student-leads';
$adminTitle = 'Quản lý lead học viên';
$adminDescription = 'Theo dõi chi tiết pipeline tư vấn học viên từ lúc tiếp nhận đến chuyển đổi tài khoản.';

$success = get_flash('success');
$error = get_flash('error');
$canConvertLead = has_any_permission(['student_lead.update']);
$canDeleteLead = has_permission('student_lead.delete');
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
                    <h3 class="mb-1">Xử lý lead #<?= (int) ($editingLead['id'] ?? 0); ?> - <?= e((string) ($editingLead['student_name'] ?? '')); ?></h3>
                    <p class="text-sm text-slate-600">Nguồn: <strong><?= e(student_lead_value_or_dash($editingLead['referral_source'] ?? 'website')); ?></strong> • Tạo lúc: <strong><?= e(student_lead_format_datetime((string) ($editingLead['created_at'] ?? ''))); ?></strong></p>
                </div>
                <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-extrabold <?= e(student_lead_status_badge_class($editingStatus)); ?>">
                    <?= e($editingStatusLabel); ?>
                </span>
            </div>

            <div class="grid gap-3 xl:grid-cols-3">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <h4 class="mb-2 text-sm font-extrabold text-slate-800">Thông tin học viên</h4>
                    <dl class="grid gap-1 text-sm text-slate-700">
                        <div><dt class="inline font-semibold">Họ tên:</dt> <dd class="inline"><?= e(student_lead_value_or_dash($editingLead['student_name'] ?? '')); ?></dd></div>
                        <div><dt class="inline font-semibold">Giới tính:</dt> <dd class="inline"><?= e(student_lead_value_or_dash($editingLead['gender'] ?? '')); ?></dd></div>
                        <div><dt class="inline font-semibold">Ngày sinh:</dt> <dd class="inline"><?= e(student_lead_format_date((string) ($editingLead['dob'] ?? ''))); ?></dd></div>
                        <div><dt class="inline font-semibold">Trình độ hiện tại:</dt> <dd class="inline"><?= e(student_lead_value_or_dash($editingLead['current_level'] ?? '')); ?></dd></div>
                        <div><dt class="inline font-semibold">Tính cách:</dt> <dd class="inline"><?= e(student_lead_value_or_dash($editingLead['personality'] ?? '')); ?></dd></div>
                    </dl>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <h4 class="mb-2 text-sm font-extrabold text-slate-800">Thông tin phụ huynh & mục tiêu</h4>
                    <dl class="grid gap-1 text-sm text-slate-700">
                        <div><dt class="inline font-semibold">Liên hệ:</dt> <dd class="inline"><?= e(student_lead_value_or_dash(trim((string) ($editingLead['parent_name'] ?? '') . ' ' . ($editingLead['parent_phone'] ?? '')))); ?></dd></div>
                        <div><dt class="inline font-semibold">Trường / Khối:</dt> <dd class="inline"><?= e(student_lead_value_or_dash(trim((string) ($editingLead['school_name'] ?? '') . ' ' . ($editingLead['current_grade'] ?? '')))); ?></dd></div>
                        <div><dt class="inline font-semibold">Sở thích / Quan tâm:</dt> <dd class="inline"><?= e(student_lead_value_or_dash($editingLead['interests'] ?? '')); ?></dd></div>
                        <div><dt class="inline font-semibold">Khung giờ mong muốn:</dt> <dd class="inline"><?= e(student_lead_value_or_dash($editingLead['study_time'] ?? '')); ?></dd></div>
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
                            <textarea name="admin_note" rows="4" placeholder="Ví dụ: Đã gọi phụ huynh, hẹn test..."><?= e((string) ($editingLead['admin_note'] ?? '')); ?></textarea>
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
                    <p class="text-sm leading-relaxed text-slate-700"><?= nl2br(e(student_lead_value_or_dash($editingLead['parent_expectation'] ?? ''))); ?></p>
                </div>

                <?php if ((int) ($editingLead['converted_user_id'] ?? 0) > 0): ?>
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-700">
                        <p class="font-semibold">Lead này đã được chuyển đổi thành học viên chính thức.</p>
                        <p class="mt-1">Mã tài khoản:
                            <button
                                type="button"
                                class="font-bold text-emerald-800 underline"
                                data-admin-row-detail="1"
                                data-detail-url="<?= e(page_url('users-admin', ['edit' => (int) $editingLead['converted_user_id']])); ?>"
                            >#<?= (int) $editingLead['converted_user_id']; ?></button>
                        </p>
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
                <h3 class="mb-1">Danh sách học viên đăng ký</h3>
                <p class="text-sm text-slate-600">Bảng chỉ hiển thị thông tin tóm tắt. Bấm Xem chi tiết hoặc Xử lý để mở hồ sơ đầy đủ.</p>
            </div>
            <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-bold text-slate-600">Tổng: <?= (int) $leadTotal; ?> lead</span>
        </div>

        <div
            data-ajax-table-root="1"
            data-ajax-page-key="page"
            data-ajax-page-value="student-leads-manage"
            data-ajax-page-param="lead_page"
            data-ajax-search-param="search"
        >
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div class="flex w-full flex-wrap items-center gap-3">
                <input
                    data-ajax-search="1"
                    type="search"
                    value="<?= e($searchQuery); ?>"
                    placeholder="Tìm theo tên học viên, phụ huynh, SĐT, nguồn lead..."
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
                        <th>Học viên & phụ huynh</th>
                        <th>Mục tiêu học</th>
                        <th>Trạng thái</th>
                        <th>Ghi chú xử lý</th>
                        <th>Chuyển đổi</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody data-ajax-tbody="1">
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
                                    <div class="font-bold text-slate-800"><?= e(student_lead_value_or_dash($lead['student_name'] ?? '')); ?></div>
                                    <div class="text-xs text-slate-600">Liên hệ: <?= e(student_lead_short_text(trim((string) ($lead['parent_name'] ?? '') . ' ' . ($lead['parent_phone'] ?? '')), 60)); ?></div>
                                </td>
                                <td>
                                    <div class="font-semibold text-slate-700">Trình độ: <?= e(student_lead_value_or_dash($lead['current_level'] ?? '')); ?></div>
                                    <div class="text-xs text-slate-600">Khung giờ: <?= e(student_lead_short_text($lead['study_time'] ?? '', 35)); ?></div>
                                    <div class="text-xs text-slate-500">Sở thích: <?= e(student_lead_short_text($lead['interests'] ?? '', 35)); ?></div>
                                </td>
                                <td>
                                    <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-bold <?= e(student_lead_status_badge_class($statusValue)); ?>"><?= e($statusLabel); ?></span>
                                    <div class="mt-1 text-xs text-slate-500">Tạo lúc: <?= e(student_lead_format_datetime((string) ($lead['created_at'] ?? ''))); ?></div>
                                </td>
                                <td>
                                    <?php $summaryNote = trim((string) ($lead['admin_note'] ?? '')) !== '' ? (string) ($lead['admin_note'] ?? '') : (string) ($lead['parent_expectation'] ?? ''); ?>
                                    <div class="text-sm text-slate-700" data-full-value="<?= e($summaryNote); ?>"><?= e(student_lead_short_text($summaryNote, 80)); ?></div>
                                </td>
                                <td>
                                    <?php if ((int) ($lead['converted_user_id'] ?? 0) > 0): ?>
                                        <button
                                            type="button"
                                            class="font-semibold text-blue-700 hover:underline"
                                            data-admin-row-detail="1"
                                            data-detail-url="<?= e(page_url('users-admin', ['edit' => (int) $lead['converted_user_id'], 'search' => $searchQuery])); ?>"
                                        >User #<?= (int) $lead['converted_user_id']; ?></button>
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
                                            data-detail-url="<?= e(page_url('student-leads-manage', ['edit' => (int) $lead['id'], 'lead_page' => $leadPage, 'lead_per_page' => $leadPerPage, 'status' => $statusFilter, 'search' => $searchQuery])); ?>"
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
                                            href="<?= e(page_url('student-leads-manage', ['edit' => (int) $lead['id'], 'lead_page' => $leadPage, 'lead_per_page' => $leadPerPage, 'status' => $statusFilter, 'search' => $searchQuery])); ?>"
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
                                        <?php if ($canDeleteLead): ?>
                                            <form class="inline-block" method="post" action="/api/leads/delete?id=<?= (int) $lead['id']; ?>" onsubmit="return confirm('Bạn có chắc muốn xóa lead này?');">
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

            <?php if ($leadTotal > 0): ?>
                <div data-ajax-pagination="1" class="border-t border-slate-200 bg-slate-50/80 px-3 py-2">
                    <div class="flex flex-wrap items-center gap-2 text-xs text-slate-600">
                        <span data-ajax-row-info="1" class="min-w-0 flex-1 font-medium">Hiển thị trang <?= (int) $leadPage; ?>/<?= (int) $leadTotalPages; ?> • Tổng <?= (int) $leadTotal; ?> lead</span>
                        <div class="ml-auto inline-flex items-center gap-1.5">
                            <form class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2 py-1" method="get" action="<?= e(page_url('student-leads-manage')); ?>">
                                <input type="hidden" name="status" value="<?= e($statusFilter); ?>">
                                <input type="hidden" name="search" value="<?= e($searchQuery); ?>">
                                <label class="text-[11px] font-semibold text-slate-500" for="lead-per-page">Số dòng</label>
                                <select id="lead-per-page" name="lead_per_page" data-ajax-per-page="1" class="h-7 rounded-md border border-slate-200 bg-white px-2 text-xs font-semibold text-slate-700">
                                    <?php foreach ($leadPerPageOptions as $option): ?>
                                        <option value="<?= (int) $option; ?>" <?= $leadPerPage === (int) $option ? 'selected' : ''; ?>><?= (int) $option; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>

                            <?php if ($leadPage > 1): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('student-leads-manage', ['lead_page' => $leadPage - 1, 'lead_per_page' => $leadPerPage, 'status' => $statusFilter, 'search' => $searchQuery])); ?>">Trước</a>
                            <?php else: ?>
                                <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400">Trước</span>
                            <?php endif; ?>

                            <?php if ($leadPage < $leadTotalPages): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('student-leads-manage', ['lead_page' => $leadPage + 1, 'lead_per_page' => $leadPerPage, 'status' => $statusFilter, 'search' => $searchQuery])); ?>">Sau</a>
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
