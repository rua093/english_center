<?php
require_admin_or_staff();
require_any_permission(['finance.tuition.view']);

$academicModel = new AcademicModel();
$searchQuery = trim((string) ($_GET['search'] ?? ''));
$statusFilter = strtolower(trim((string) ($_GET['status'] ?? '')));
if (!in_array($statusFilter, ['paid', 'debt'], true)) {
    $statusFilter = '';
}
$paymentPlanFilter = strtolower(trim((string) ($_GET['payment_plan'] ?? '')));
if (!in_array($paymentPlanFilter, ['full', 'monthly'], true)) {
    $paymentPlanFilter = '';
}
$filters = [
    'status' => $statusFilter,
    'payment_plan' => $paymentPlanFilter,
];
$tuitionPage = max(1, (int) ($_GET['tuition_page'] ?? 1));
$tuitionPerPage = ui_pagination_resolve_per_page('tuition_per_page', 10);
$tuitionTotal = $academicModel->countTuitionFees($searchQuery, $filters);
$tuitionTotalPages = max(1, (int) ceil($tuitionTotal / $tuitionPerPage));
if ($tuitionPage > $tuitionTotalPages) {
    $tuitionPage = $tuitionTotalPages;
}
$tuitionFees = $academicModel->listTuitionFeesPage($tuitionPage, $tuitionPerPage, $searchQuery, $filters);
$tuitionPerPageOptions = ui_pagination_per_page_options();
$tuitionOptions = $academicModel->listTuitionFeesPage(1, 200);
$registrationLookups = $academicModel->registrationLookups();
$promotions = $registrationLookups['promotions'] ?? [];
$registrationClasses = $registrationLookups['classes'] ?? [];

$lookups = $academicModel->scheduleLookups();
$classes = $lookups['classes'] ?? [];
$students = $academicModel->studentLookups();
$classStudentRows = $academicModel->tuitionStudentClassLookups();

$studentNameMap = [];
foreach ($students as $student) {
    $studentId = (int) ($student['id'] ?? 0);
    if ($studentId > 0) {
        $studentNameMap[$studentId] = student_dropdown_label($student, 'Học viên #' . $studentId);
    }
}

$classMap = [];
foreach ($classes as $class) {
    $classId = (int) ($class['id'] ?? 0);
    if ($classId > 0) {
        $classMap[$classId] = $class;
    }
}
foreach ($registrationClasses as $class) {
    $classId = (int) ($class['id'] ?? 0);
    if ($classId > 0) {
        $existing = $classMap[$classId] ?? [];
        $classMap[$classId] = array_merge($existing, $class);
    }
}

$classStudentMap = [];
foreach ($classStudentRows as $row) {
    $classId = (int) ($row['class_id'] ?? 0);
    $studentId = (int) ($row['student_id'] ?? 0);
    if ($classId <= 0 || $studentId <= 0) {
        continue;
    }

    if (!isset($classStudentMap[$classId])) {
        $classStudentMap[$classId] = [];
    }

    $classStudentMap[$classId][] = [
        'id' => $studentId,
        'name' => student_dropdown_label($row, $studentNameMap[$studentId] ?? ('Học viên #' . $studentId)),
    ];
}

$editingTuition = null;
if (!empty($_GET['edit'])) {
    $editingTuition = $academicModel->findTuitionFeeForEdit((int) $_GET['edit']);
}

$highlightTuitionId = (int) ($_GET['highlight_tuition_id'] ?? 0);

$today = new DateTimeImmutable('today');
$currentMonth = $today->format('Y-m');
$currentDay = (int) $today->format('j');
$daysInMonth = (int) $today->format('t');

$normalizeMonthValue = static function (?string $value): string {
    $value = trim((string) $value);
    if ($value === '') {
        return '';
    }

    if (preg_match('/^(\d{4}-\d{2})/', $value, $matches)) {
        return $matches[1];
    }

    return $value;
};

$monthlyStatusLabel = function (array $fee) use ($currentMonth, $currentDay, $daysInMonth, $normalizeMonthValue): string {
    $plan = (string) ($fee['payment_plan'] ?? '');
    if ($plan !== 'monthly') {
        return '-';
    }

    $totalAmount = (float) ($fee['total_amount'] ?? 0);
    $amountPaid = (float) ($fee['amount_paid'] ?? 0);
    if ($totalAmount > 0 && $amountPaid >= $totalAmount) {
        return 'Đã đủ';
    }

    $startMonth = $normalizeMonthValue((string) ($fee['monthly_start_month'] ?? ''));
    $endMonth = $normalizeMonthValue((string) ($fee['monthly_end_month'] ?? ''));
    $paymentDay = (int) ($fee['monthly_payment_day'] ?? 0);

    if ($startMonth !== '' && $currentMonth < $startMonth) {
        return 'Chưa tới kỳ';
    }

    if ($endMonth !== '' && $currentMonth > $endMonth) {
        return 'Trễ hạn';
    }

    if ($paymentDay <= 0) {
        return 'Chưa đặt ngày';
    }

    $effectiveDay = min($paymentDay, $daysInMonth);
    if ($currentDay > $effectiveDay) {
        return 'Trễ hạn';
    }

    if ($currentDay === $effectiveDay) {
        return 'Đến hạn';
    }

    return 'Chưa tới kỳ';
};

$monthlyStatusClass = function (string $label): string {
    return match ($label) {
        'Trễ hạn' => 'text-rose-700',
        'Đến hạn' => 'text-amber-700',
        'Đã đủ' => 'text-emerald-700',
        default => 'text-slate-500',
    };
};

$editingClassId = (int) ($editingTuition['class_id'] ?? 0);
$editingStudentId = (int) ($editingTuition['student_id'] ?? 0);
$editingPackageId = max(0, (int) ($editingTuition['package_id'] ?? 0));
$editingBaseAmount = max(0, (float) ($editingTuition['base_amount'] ?? 0));
$editingClass = $editingClassId > 0 ? ($classMap[$editingClassId] ?? $academicModel->findClass($editingClassId)) : null;
$editingCourseId = (int) ($editingClass['course_id'] ?? 0);
$editingClassName = (string) ($editingClass['class_name'] ?? '');
$editingCourseName = (string) ($editingClass['course_name'] ?? '');
$editingStudentName = $editingStudentId > 0 ? ($studentNameMap[$editingStudentId] ?? ('Học viên #' . $editingStudentId)) : '';
$studentOptionsForSelectedClass = $editingClassId > 0 ? ($classStudentMap[$editingClassId] ?? []) : [];
$editingMonthlyStart = $normalizeMonthValue((string) ($editingTuition['monthly_start_month'] ?? ''));
$editingMonthlyEnd = $normalizeMonthValue((string) ($editingTuition['monthly_end_month'] ?? ''));
$editingMonthlyDay = (int) ($editingTuition['monthly_payment_day'] ?? 0);
$editingMonthlyMonths = (int) ($editingTuition['monthly_months'] ?? 0);
$editingMonthlyStatus = $editingTuition ? $monthlyStatusLabel($editingTuition) : '-';
$editingIsMonthly = (($editingTuition['payment_plan'] ?? 'full') === 'monthly');

if ($editingClassId > 0 && $editingStudentId > 0) {
    $hasEditingStudent = false;
    foreach ($studentOptionsForSelectedClass as $studentOption) {
        if ((int) ($studentOption['id'] ?? 0) === $editingStudentId) {
            $hasEditingStudent = true;
            break;
        }
    }

    if (!$hasEditingStudent) {
        $fallbackName = $studentNameMap[$editingStudentId] ?? ('Học viên #' . $editingStudentId);
        $studentOptionsForSelectedClass[] = [
            'id' => $editingStudentId,
            'name' => $fallbackName . ' (không còn trong lớp)',
        ];
        if (!isset($classStudentMap[$editingClassId])) {
            $classStudentMap[$editingClassId] = [];
        }
        $classStudentMap[$editingClassId][] = [
            'id' => $editingStudentId,
            'name' => $fallbackName . ' (không còn trong lớp)',
        ];
    }
}

$module = 'tuition';
$adminTitle = 'Quản lý học phí';

$viewer = auth_user();
$isAdmin = (($viewer['role'] ?? '') === 'admin');
$isStaff = (($viewer['role'] ?? '') === 'staff');

$canCreateTuition = $isAdmin;
$canUpdateTuition = $isAdmin;
$canDeleteTuition = $isAdmin;
$canCreatePayment = $isAdmin || has_permission('finance.payments.create');

$success = get_flash('success');
$error = get_flash('error');
?>
<style>
    .tuition-readonly-field {
        position: relative;
    }

    .tuition-search-toolbar {
        display: flex;
        justify-content: flex-start;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .tuition-search-shell {
        position: relative;
        width: min(100%, 22rem);
    }

    .tuition-search-icon {
        position: absolute;
        top: 50%;
        left: 0.95rem;
        width: 1rem;
        height: 1rem;
        color: #94a3b8;
        transform: translateY(-50%);
        pointer-events: none;
    }

    .tuition-search-bespoke {
        width: 100%;
        height: 2.75rem;
        padding: 0 1rem 0 2.75rem;
        border: 1px solid #cbd5e1;
        border-radius: 0.9rem;
        background: #ffffff;
        color: #0f172a;
        font-size: 0.875rem;
        font-weight: 500;
        line-height: 1.25rem;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        outline: none;
        transition: border-color 0.18s ease, box-shadow 0.18s ease;
    }

    .tuition-search-bespoke::placeholder {
        color: #94a3b8;
        font-weight: 500;
    }

    .tuition-search-bespoke:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
    }

    @media (max-width: 767px) {
        .tuition-search-toolbar {
            justify-content: stretch;
            align-items: stretch;
            flex-direction: column;
        }

        .tuition-search-shell {
            width: 100%;
        }
    }

    .tuition-readonly-field > input[readonly],
    .tuition-readonly-field > select[disabled],
    .tuition-readonly-field > textarea[readonly] {
        cursor: not-allowed;
        background: #f8fafc !important;
        color: #475569 !important;
        border-color: #cbd5e1 !important;
    }

    .tuition-row-highlight {
        outline: 2px solid #22c55e;
        outline-offset: -2px;
        background: #f0fdf4;
        box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.12);
    }

</style>
<div class="grid gap-4">
    <?php if ($success): ?>
        <div class="rounded-xl border-l-4 border-emerald-500 bg-emerald-50 p-3 text-sm text-emerald-700"><?= e($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="rounded-xl border-l-4 border-rose-500 bg-rose-50 p-3 text-sm text-rose-700"><?= e($error); ?></div>
    <?php endif; ?>

    <?php if ($canUpdateTuition && $editingTuition): ?>
        <div>
            <form class="grid gap-3 md:grid-cols-2" method="post" action="/api/tuitions/save">
                <?= csrf_input(); ?>
                <input type="hidden" name="id" value="<?= (int) ($editingTuition['id'] ?? 0); ?>">
                <label class="tuition-readonly-field">
                    Học viên
                    <input type="text" value="<?= e($editingStudentName); ?>" readonly>
                </label>
                <label class="tuition-readonly-field">
                    Lớp học
                    <input type="text" value="<?= e($editingClassName !== '' ? $editingClassName : '-- Chọn lớp học --'); ?>" readonly>
                </label>
                <label class="tuition-readonly-field">
                    Khóa học
                    <input type="text" value="<?= e($editingCourseName !== '' ? $editingCourseName : '-- Chưa có khóa học --'); ?>" readonly>
                </label>
                <label class="tuition-readonly-field">
                    Tổng tiền
                    <input
                        id="tuition-total-amount"
                        type="number"
                        step="1000"
                        min="0"
                        value="<?= e((string) ($editingTuition['total_amount'] ?? '0')); ?>"
                        readonly
                    >
                </label>
                <label class="tuition-readonly-field">
                    Đã thu
                    <input id="tuition-amount-paid" type="number" step="1000" min="0" value="<?= e((string) ($editingTuition['amount_paid'] ?? '0')); ?>" readonly>
                </label>
                <label>
                    Chế độ đóng
                    <select
                        name="payment_plan"
                        id="tuition-payment-plan-select"
                        data-tuition-payment-plan="1"
                        onchange="window.toggleTuitionMonthlyFields && window.toggleTuitionMonthlyFields(this.form); window.updateTuitionEditPreview && window.updateTuitionEditPreview(this.form);"
                    >
                        <option value="full" <?= (($editingTuition['payment_plan'] ?? 'full') === 'full') ? 'selected' : ''; ?>>Đóng một lần (full)</option>
                        <option value="monthly" <?= (($editingTuition['payment_plan'] ?? '') === 'monthly') ? 'selected' : ''; ?>>Đóng theo tháng (monthly)</option>
                    </select>
                </label>
                <div id="tuition-monthly-fields" data-tuition-monthly="1" class="md:col-span-2 <?= $editingIsMonthly ? '' : 'hidden'; ?> rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-4">
                    <div class="grid gap-3 md:grid-cols-2">
                        <label>
                            Số tháng đóng
                            <input type="number" min="1" step="1" name="monthly_months" value="<?= e((string) $editingMonthlyMonths); ?>" <?= $editingIsMonthly ? '' : 'disabled'; ?>>
                        </label>
                        <label>
                            Từ tháng
                            <input type="month" name="monthly_start_month" value="<?= e($editingMonthlyStart); ?>" <?= $editingIsMonthly ? '' : 'disabled'; ?>>
                        </label>
                        <label>
                            Đến tháng
                            <input type="month" name="monthly_end_month" value="<?= e($editingMonthlyEnd); ?>" <?= $editingIsMonthly ? '' : 'disabled'; ?>>
                        </label>
                        <label>
                            Ngày đóng hàng tháng
                            <input type="number" min="1" max="31" step="1" name="monthly_payment_day" value="<?= e($editingMonthlyDay > 0 ? (string) $editingMonthlyDay : ''); ?>" <?= $editingIsMonthly ? '' : 'disabled'; ?>>
                        </label>
                        <p class="md:col-span-2 text-xs text-slate-500">Chỉ dùng khi chọn chế độ <strong>monthly</strong>. Nếu chuyển sang <strong>full</strong>, các field này sẽ được ẩn và không gửi lên server.</p>
                    </div>
                </div>
                <label>
                    Ưu đãi áp dụng
                    <select
                        id="tuition-package-select"
                        name="package_id"
                        data-base-amount="<?= e(number_format($editingBaseAmount, 2, '.', '')); ?>"
                        data-current-course-id="<?= (int) $editingCourseId; ?>"
                        onchange="window.updateTuitionEditPreview && window.updateTuitionEditPreview(this.form);"
                    >
                        <option value="0" data-discount-value="0" data-course-id="0" <?= $editingPackageId === 0 ? 'selected' : ''; ?>>Không áp dụng ưu đãi</option>
                        <?php foreach ($promotions as $promo): ?>
                            <?php
                            $promoId = (int) ($promo['id'] ?? 0);
                            if ($promoId <= 0) {
                                continue;
                            }

                            $promoCourseId = (int) ($promo['course_id'] ?? 0);
                            if ($editingCourseId > 0 && $promoCourseId > 0 && $promoCourseId !== $editingCourseId) {
                                continue;
                            }

                            $discountPercent = max(0, min(100, (float) ($promo['discount_value'] ?? 0)));
                            $discountPercentText = rtrim(rtrim(number_format($discountPercent, 2, '.', ''), '0'), '.');
                            $promoName = trim((string) ($promo['name'] ?? ('Ưu đãi #' . $promoId)));
                            ?>
                            <option
                                value="<?= $promoId; ?>"
                                data-course-id="<?= $promoCourseId; ?>"
                                data-discount-value="<?= e((string) $discountPercent); ?>"
                                <?= $editingPackageId === $promoId ? 'selected' : ''; ?>
                            >
                                <?= e($promoName . ' - ' . $discountPercentText . '%'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="tuition-readonly-field">
                    Trạng thái
                    <input id="tuition-status" type="text" value="<?= e((string) ($editingTuition['status'] ?? 'debt')); ?>" readonly>
                </label>
                <p class="md:col-span-2 text-xs text-slate-500">Trạng thái sẽ tự động là <strong>debt</strong> nếu số đã thu chưa đủ và tự chuyển <strong>paid</strong> khi thu đủ.</p>
                <div class="md:col-span-2 inline-flex flex-wrap items-center gap-2">
                    <button class="<?= ui_btn_primary_classes(); ?>" type="submit">Cập nhật hóa đơn</button>
                    <a class="<?= ui_btn_secondary_classes(); ?>" href="<?= e(page_url('tuition-finance')); ?>">Hủy chỉnh sửa</a>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3>Danh sách học phí</h3>
        <div
            class="tuition-list-shell"
            data-ajax-table-root="1"
            data-ajax-page-key="page"
            data-ajax-page-value="tuition-finance"
            data-ajax-page-param="tuition_page"
            data-ajax-search-param="search"
        >
        <div class="admin-table-toolbar tuition-search-toolbar">
            <div class="flex w-full flex-wrap items-center gap-3">
                <label class="tuition-search-shell" aria-label="Tìm kiếm học phí">
                    <span class="tuition-search-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="11" cy="11" r="7"></circle>
                            <path d="m20 20-3.5-3.5"></path>
                        </svg>
                    </span>
                    <input
                        class="tuition-search-bespoke"
                        data-ajax-search="1"
                        type="search"
                        value="<?= e($searchQuery); ?>"
                        placeholder="Tìm học viên, mã HV, mã hóa đơn..."
                        autocomplete="off"
                    >
                </label>
                <select
                    name="status"
                    data-ajax-filter="1"
                    class="h-11 rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-700 shadow-sm outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100"
                >
                    <option value="">Tất cả trạng thái</option>
                    <option value="debt" <?= $statusFilter === 'debt' ? 'selected' : ''; ?>>Còn nợ</option>
                    <option value="paid" <?= $statusFilter === 'paid' ? 'selected' : ''; ?>>Đã thanh toán</option>
                </select>
                <select
                    name="payment_plan"
                    data-ajax-filter="1"
                    class="h-11 rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-700 shadow-sm outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100"
                >
                    <option value="">Tất cả chế độ đóng</option>
                    <option value="full" <?= $paymentPlanFilter === 'full' ? 'selected' : ''; ?>>Đóng một lần</option>
                    <option value="monthly" <?= $paymentPlanFilter === 'monthly' ? 'selected' : ''; ?>>Đóng theo tháng</option>
                </select>
            </div>
            <span
                data-ajax-row-info="1"
                data-visible="<?= (int) count($tuitionFees); ?>"
                data-total="<?= (int) $tuitionTotal; ?>"
                style="color: #64748b; font-size: 0.875rem; font-weight: 500; white-space: nowrap;"
            >Hiển thị <?= (int) count($tuitionFees); ?> / <?= (int) $tuitionTotal; ?> dòng</span>
        </div>
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full border-collapse text-sm" data-disable-global-filter="1" data-disable-row-detail="1">
                <thead>
                    <tr>
                        <th>Mã HV</th>
                        <th>Học viên</th>
                        <th>Khóa học</th>
                        <th>Lớp học</th>
                        <th>Tổng tiền</th>
                        <th>Đã thu</th>
                        <th>Còn lại</th>
                        <th>Trạng thái</th>
                        <th>Chế độ đóng</th>
                        <th>Trễ hạn</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody data-ajax-tbody="1">
                    <?php if (empty($tuitionFees)): ?>
                        <tr>
                            <td colspan="11">
                                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">Chưa có dữ liệu học phí.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($tuitionFees as $fee): ?>
                            <?php $monthlyStatus = $monthlyStatusLabel($fee); ?>
                            <tr data-tuition-id="<?= (int) $fee['id']; ?>">
                                <td><?= e((string) ($fee['student_code'] ?? '-')); ?></td>
                                <td><?= e((string) ($fee['full_name'] ?? 'Học viên')); ?></td>
                                <td><?= e((string) ($fee['course_name'] ?? '')); ?></td>
                                <td><?= e((string) ($fee['class_name'] ?? '')); ?></td>
                                <td><?= format_money((float) $fee['total_amount']); ?></td>
                                <td><?= format_money((float) $fee['amount_paid']); ?></td>
                                <td><?= format_money((float) ($fee['total_amount'] - $fee['amount_paid'])); ?></td>
                                <td><span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-bold capitalize is-<?= e((string) $fee['status']); ?>"><?= e((string) $fee['status']); ?></span></td>
                                <td><?= e((string) ($fee['payment_plan'] ?? 'full')); ?></td>
                                <td>
                                    <?php if ($monthlyStatus === '-'): ?>
                                        <span class="text-slate-400">-</span>
                                    <?php else: ?>
                                        <span class="text-xs font-semibold <?= e($monthlyStatusClass($monthlyStatus)); ?>"><?= e($monthlyStatus); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="inline-flex flex-wrap items-center gap-2">
                                        <?php if ($canUpdateTuition): ?>
                                            <a href="<?= e(page_url('tuition-finance', ['edit' => (int) $fee['id'], 'tuition_page' => $tuitionPage, 'tuition_per_page' => $tuitionPerPage, 'search' => $searchQuery, 'status' => $statusFilter, 'payment_plan' => $paymentPlanFilter])); ?>"
                                               class="admin-action-icon-btn"
                                               data-action-kind="edit"
                                               data-skip-action-icon="1"
                                               title="Sửa"
                                               aria-label="Sửa">
                                                <span class="admin-action-icon-label">Sửa</span>
                                                <span class="admin-action-icon-glyph" aria-hidden="true">
                                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                                        <path d="M12 20h9"></path>
                                                        <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"></path>
                                                    </svg>
                                                </span>
                                            </a>
                                        <?php endif; ?>

                                        <?php
                                        $remainingAmount = (float) ($fee['total_amount'] ?? 0) - (float) ($fee['amount_paid'] ?? 0);
                                        $remainingAmount = max(0, $remainingAmount);
                                        ?>

                                        <?php if ($canCreatePayment && $remainingAmount > 0): ?>
                                            <a
                                                href="<?= e(page_url('payments-finance', ['tuition_id' => (int) $fee['id']])); ?>"
                                                class="admin-action-icon-btn"
                                                data-action-kind="pay"
                                                data-skip-action-icon="1"
                                                title="Đóng học phí"
                                                aria-label="Đóng học phí"
                                            >
                                                <span class="admin-action-icon-label">Đóng học phí</span>
                                                <span class="admin-action-icon-glyph" aria-hidden="true">
                                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                                        <path d="M3 7h18"></path>
                                                        <path d="M5 12h14"></path>
                                                        <path d="M7 17h10"></path>
                                                    </svg>
                                                </span>
                                            </a>
                                        <?php endif; ?>

                                        <?php if ($canDeleteTuition): ?>
                                            <form method="post" action="/api/tuitions/delete" onsubmit="return confirm('Bạn chắc chắn muốn xóa hóa đơn học phí này?');">
                                                <?= csrf_input(); ?>
                                                <input type="hidden" name="tuition_id" value="<?= (int) $fee['id']; ?>">
                                                <button type="submit"
                                                        class="admin-action-icon-btn"
                                                        data-action-kind="delete"
                                                        data-skip-action-icon="1"
                                                        title="Xóa"
                                                        aria-label="Xóa">
                                                    <span class="admin-action-icon-label">Xóa</span>
                                                    <span class="admin-action-icon-glyph" aria-hidden="true">
                                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                                            <path d="M3 6h18"></path>
                                                            <path d="M8 6V4h8v2"></path>
                                                            <path d="M19 6l-1 14H6L5 6"></path>
                                                            <path d="M10 11v6"></path>
                                                            <path d="M14 11v6"></path>
                                                        </svg>
                                                    </span>
                                                </button>
                                            </form>
                                        <?php elseif ($isStaff): ?>
                                            <form method="post" action="/api/tuitions/request-delete">
                                                <?= csrf_input(); ?>
                                                <input type="hidden" name="tuition_id" value="<?= (int) $fee['id']; ?>">
                                                <input type="hidden" name="reason" value="Yêu cầu xóa học phí do cần điều chỉnh nghiệp vụ.">
                                                <button class="<?= ui_btn_secondary_classes('sm'); ?>" type="submit">Gửi duyệt xóa</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <div data-ajax-pagination="1">
            <?php if ($tuitionTotal > 0): ?>
                <div class="border-t border-slate-200 bg-slate-50/80 px-3 py-2">
                    <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-slate-600">
                        <span class="font-medium">Trang <?= (int) $tuitionPage; ?>/<?= (int) $tuitionTotalPages; ?> - Tổng <?= (int) $tuitionTotal; ?> hóa đơn</span>
                        <div class="inline-flex items-center gap-1.5">
                            <form class="ajax-table-per-page-form inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2 py-1" method="get" action="<?= e(page_url('tuition-finance')); ?>">
                                <input type="hidden" name="page" value="tuition-finance">
                                <input type="hidden" name="search" value="<?= e($searchQuery); ?>">
                                <input type="hidden" name="status" value="<?= e($statusFilter); ?>">
                                <input type="hidden" name="payment_plan" value="<?= e($paymentPlanFilter); ?>">
                                <label class="text-[11px] font-semibold text-slate-500" for="tuition-per-page">Số dòng</label>
                                <select id="tuition-per-page" name="tuition_per_page" data-ajax-per-page="1" class="h-7 rounded-md border border-slate-200 bg-white px-2 text-xs font-semibold text-slate-700">
                                    <?php foreach ($tuitionPerPageOptions as $option): ?>
                                        <option value="<?= (int) $option; ?>" <?= $tuitionPerPage === (int) $option ? 'selected' : ''; ?>><?= (int) $option; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                            <?php if ($tuitionPage > 1): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('tuition-finance', ['tuition_page' => $tuitionPage - 1, 'tuition_per_page' => $tuitionPerPage, 'search' => $searchQuery, 'status' => $statusFilter, 'payment_plan' => $paymentPlanFilter])); ?>">Trước</a>
                            <?php else: ?>
                                <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400">Trước</span>
                            <?php endif; ?>

                            <?php if ($tuitionPage < $tuitionTotalPages): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('tuition-finance', ['tuition_page' => $tuitionPage + 1, 'tuition_per_page' => $tuitionPerPage, 'search' => $searchQuery, 'status' => $statusFilter, 'payment_plan' => $paymentPlanFilter])); ?>">Sau</a>
                            <?php else: ?>
                                <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400">Sau</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            </div>
        </div>
        </div>
    </article>

    <?php if ($isStaff): ?>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3>Gửi yêu cầu điều chỉnh tài chính</h3>
            <form class="grid gap-3 md:grid-cols-2" method="post" action="/api/tuitions/request-adjust">
                <?= csrf_input(); ?>
                <label class="md:col-span-2">
                    Hóa đơn học phí
                    <select name="tuition_id" required>
                        <option value="">-- Chọn hóa đơn --</option>
                        <?php foreach ($tuitionOptions as $fee): ?>
                            <option value="<?= (int) $fee['id']; ?>">
                                #<?= (int) $fee['id']; ?> - <?= e(student_dropdown_label($fee)); ?> - <?= e((string) $fee['course_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    Số đã thu đề xuất
                    <input type="number" step="1000" min="0" name="requested_amount_paid" required>
                </label>
                <label>
                    Lý do điều chỉnh
                    <input type="text" name="reason" required placeholder="Ví dụ: Nhập sai số tiền khi thu tại quầy">
                </label>
                <div class="md:col-span-2">
                    <button class="<?= ui_btn_primary_classes('sm'); ?>" type="submit">Gửi yêu cầu phê duyệt</button>
                </div>
            </form>
        </article>
    <?php endif; ?>
</div>

<script>
    (function () {
        window.toggleTuitionMonthlyFields = function (scope) {
            const root = scope instanceof HTMLFormElement || scope instanceof HTMLElement ? scope : document;
            const paymentPlanSelect = root.querySelector('#tuition-payment-plan-select')
                || root.querySelector('select[name="payment_plan"]');
            const monthlyFields = root.querySelector('#tuition-monthly-fields')
                || root.querySelector('[data-tuition-monthly="1"]');

            if (!(paymentPlanSelect instanceof HTMLSelectElement) || !(monthlyFields instanceof HTMLElement)) {
                return;
            }

            const isMonthly = paymentPlanSelect.value === 'monthly';
            monthlyFields.classList.toggle('hidden', !isMonthly);
            monthlyFields.querySelectorAll('input, select, textarea').forEach((field) => {
                if (field instanceof HTMLInputElement || field instanceof HTMLSelectElement || field instanceof HTMLTextAreaElement) {
                    field.disabled = !isMonthly;
                }
            });
        };

        window.updateTuitionEditPreview = function (scope) {
            const root = scope instanceof HTMLFormElement || scope instanceof HTMLElement ? scope : document;
            const packageSelect = root.querySelector('#tuition-package-select');
            const totalAmountInput = root.querySelector('#tuition-total-amount');
            const amountPaidInput = root.querySelector('#tuition-amount-paid');
            const statusInput = root.querySelector('#tuition-status');

            if (!(packageSelect instanceof HTMLSelectElement) || !(totalAmountInput instanceof HTMLInputElement) || !(statusInput instanceof HTMLInputElement)) {
                return;
            }

            window.toggleTuitionMonthlyFields(root);

            const baseAmount = Number(packageSelect.dataset.baseAmount || 0);
            const amountPaid = amountPaidInput instanceof HTMLInputElement ? Number(amountPaidInput.value || 0) : 0;
            const selectedOption = packageSelect.selectedOptions[0];
            const discountPercent = selectedOption ? Number(selectedOption.dataset.discountValue || 0) : 0;
            const discountApplied = Math.max(0, (baseAmount * discountPercent) / 100);
            const totalAmount = Math.max(0, Math.round((baseAmount - discountApplied) * 100) / 100);
            const status = amountPaid >= totalAmount ? 'paid' : 'debt';

            totalAmountInput.value = totalAmount.toFixed(2);
            statusInput.value = status;
        };

        document.addEventListener('change', function (event) {
            const target = event.target;
            if (!(target instanceof HTMLElement)) {
                return;
            }

            if (target.matches('#tuition-payment-plan-select')) {
                const scope = target.closest('form') || document;
                window.toggleTuitionMonthlyFields(scope);
                window.updateTuitionEditPreview(scope);
                return;
            }

            if (target.matches('#tuition-package-select, #tuition-amount-paid')) {
                const scope = target.closest('form') || document;
                window.updateTuitionEditPreview(scope);
            }
        });

        document.querySelectorAll('#tuition-payment-plan-select, select[name="payment_plan"]').forEach(function (select) {
            if (!(select instanceof HTMLSelectElement)) {
                return;
            }
            const scope = select.closest('form') || document;
            window.toggleTuitionMonthlyFields(scope);
            window.updateTuitionEditPreview(scope);
        });
    })();
</script>

<?php if ($highlightTuitionId > 0): ?>
<script>
    (function () {
        const targetRow = document.querySelector('[data-tuition-id="<?= (int) $highlightTuitionId; ?>"]');
        if (!targetRow) {
            return;
        }

        targetRow.classList.add('tuition-row-highlight');
        targetRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
    })();
</script>
<?php endif; ?>



