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
        $studentNameMap[$studentId] = student_dropdown_label($student, t('admin.tuition.student_fallback', ['id' => $studentId]));
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
        'name' => student_dropdown_label($row, $studentNameMap[$studentId] ?? t('admin.tuition.student_fallback', ['id' => $studentId])),
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

$monthlyStatusLabels = [
    'paid' => t('admin.tuition.monthly_status.paid'),
    'not_due' => t('admin.tuition.monthly_status.not_due'),
    'late' => t('admin.tuition.monthly_status.late'),
    'no_day' => t('admin.tuition.monthly_status.no_day'),
    'due' => t('admin.tuition.monthly_status.due'),
    'na' => t('admin.tuition.monthly_status.na'),
];

$monthlyStatusLabel = function (array $fee) use ($currentMonth, $currentDay, $daysInMonth, $normalizeMonthValue): string {
    $plan = (string) ($fee['payment_plan'] ?? '');
    if ($plan !== 'monthly') {
        return 'na';
    }

    $totalAmount = (float) ($fee['total_amount'] ?? 0);
    $amountPaid = (float) ($fee['amount_paid'] ?? 0);
    if ($totalAmount > 0 && $amountPaid >= $totalAmount) {
        return 'paid';
    }

    $startMonth = $normalizeMonthValue((string) ($fee['monthly_start_month'] ?? ''));
    $endMonth = $normalizeMonthValue((string) ($fee['monthly_end_month'] ?? ''));
    $paymentDay = (int) ($fee['monthly_payment_day'] ?? 0);

    if ($startMonth !== '' && $currentMonth < $startMonth) {
        return 'not_due';
    }

    if ($endMonth !== '' && $currentMonth > $endMonth) {
        return 'late';
    }

    if ($paymentDay <= 0) {
        return 'no_day';
    }

    $effectiveDay = min($paymentDay, $daysInMonth);
    if ($currentDay > $effectiveDay) {
        return 'late';
    }

    if ($currentDay === $effectiveDay) {
        return 'due';
    }

    return 'not_due';
};

$monthlyStatusClass = function (string $statusKey): string {
    return match ($statusKey) {
        'late' => 'text-rose-700',
        'due' => 'text-amber-700',
        'paid' => 'text-emerald-700',
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
$editingStudentName = $editingStudentId > 0 ? ($studentNameMap[$editingStudentId] ?? t('admin.tuition.student_fallback', ['id' => $editingStudentId])) : '';
$studentOptionsForSelectedClass = $editingClassId > 0 ? ($classStudentMap[$editingClassId] ?? []) : [];
$editingMonthlyStart = $normalizeMonthValue((string) ($editingTuition['monthly_start_month'] ?? ''));
$editingMonthlyEnd = $normalizeMonthValue((string) ($editingTuition['monthly_end_month'] ?? ''));
$editingMonthlyDay = (int) ($editingTuition['monthly_payment_day'] ?? 0);
$editingMonthlyMonths = (int) ($editingTuition['monthly_months'] ?? 0);
$editingMonthlyStatusKey = $editingTuition ? $monthlyStatusLabel($editingTuition) : 'na';
$editingMonthlyStatus = $monthlyStatusLabels[$editingMonthlyStatusKey] ?? t('admin.tuition.monthly_status.na');
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
        $fallbackName = $studentNameMap[$editingStudentId] ?? t('admin.tuition.student_fallback', ['id' => $editingStudentId]);
        $studentOptionsForSelectedClass[] = [
            'id' => $editingStudentId,
            'name' => t('admin.tuition.student_not_in_class', ['name' => $fallbackName]),
        ];
        if (!isset($classStudentMap[$editingClassId])) {
            $classStudentMap[$editingClassId] = [];
        }
        $classStudentMap[$editingClassId][] = [
            'id' => $editingStudentId,
            'name' => t('admin.tuition.student_not_in_class', ['name' => $fallbackName]),
        ];
    }
}

$module = 'tuition';
$adminTitle = t('admin.tuition.title');

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
                    <?= e(t('admin.tuition.student')); ?>
                    <input type="text" value="<?= e($editingStudentName); ?>" readonly>
                </label>
                <label class="tuition-readonly-field">
                    <?= e(t('admin.tuition.class')); ?>
                    <input type="text" value="<?= e($editingClassName !== '' ? $editingClassName : t('admin.tuition.choose_class')); ?>" readonly>
                </label>
                <label class="tuition-readonly-field">
                    <?= e(t('admin.tuition.course')); ?>
                    <input type="text" value="<?= e($editingCourseName !== '' ? $editingCourseName : t('admin.tuition.course_empty')); ?>" readonly>
                </label>
                <label class="tuition-readonly-field">
                    <?= e(t('admin.tuition.total_amount')); ?>
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
                    <?= e(t('admin.tuition.amount_paid')); ?>
                    <input id="tuition-amount-paid" type="number" step="1000" min="0" value="<?= e((string) ($editingTuition['amount_paid'] ?? '0')); ?>" readonly>
                </label>
                <label>
                    <?= e(t('admin.tuition.payment_plan')); ?>
                    <select
                        name="payment_plan"
                        id="tuition-payment-plan-select"
                        data-tuition-payment-plan="1"
                        onchange="window.toggleTuitionMonthlyFields && window.toggleTuitionMonthlyFields(this.form); window.updateTuitionEditPreview && window.updateTuitionEditPreview(this.form);"
                    >
                        <option value="full" <?= (($editingTuition['payment_plan'] ?? 'full') === 'full') ? 'selected' : ''; ?>><?= e(t('admin.tuition.payment_plan_full')); ?></option>
                        <option value="monthly" <?= (($editingTuition['payment_plan'] ?? '') === 'monthly') ? 'selected' : ''; ?>><?= e(t('admin.tuition.payment_plan_monthly')); ?></option>
                    </select>
                </label>
                <div id="tuition-monthly-fields" data-tuition-monthly="1" class="md:col-span-2 <?= $editingIsMonthly ? '' : 'hidden'; ?> rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-4">
                    <div class="grid gap-3 md:grid-cols-2">
                        <label>
                            <?= e(t('admin.tuition.monthly_months')); ?>
                            <input type="number" min="1" step="1" name="monthly_months" value="<?= e((string) $editingMonthlyMonths); ?>" <?= $editingIsMonthly ? '' : 'disabled'; ?>>
                        </label>
                        <label>
                            <?= e(t('admin.tuition.monthly_start')); ?>
                            <input type="month" name="monthly_start_month" value="<?= e($editingMonthlyStart); ?>" <?= $editingIsMonthly ? '' : 'disabled'; ?>>
                        </label>
                        <label>
                            <?= e(t('admin.tuition.monthly_end')); ?>
                            <input type="month" name="monthly_end_month" value="<?= e($editingMonthlyEnd); ?>" <?= $editingIsMonthly ? '' : 'disabled'; ?>>
                        </label>
                        <label>
                            <?= e(t('admin.tuition.monthly_day')); ?>
                            <input type="number" min="1" max="31" step="1" name="monthly_payment_day" value="<?= e($editingMonthlyDay > 0 ? (string) $editingMonthlyDay : ''); ?>" <?= $editingIsMonthly ? '' : 'disabled'; ?>>
                        </label>
                        <p class="md:col-span-2 text-xs text-slate-500"><?= e(t('admin.tuition.monthly_hint')); ?></p>
                    </div>
                </div>
                <label>
                    <?= e(t('admin.tuition.promotion')); ?>
                    <select
                        id="tuition-package-select"
                        name="package_id"
                        data-base-amount="<?= e(number_format($editingBaseAmount, 2, '.', '')); ?>"
                        data-current-course-id="<?= (int) $editingCourseId; ?>"
                        onchange="window.updateTuitionEditPreview && window.updateTuitionEditPreview(this.form);"
                    >
                        <option value="0" data-discount-value="0" data-course-id="0" <?= $editingPackageId === 0 ? 'selected' : ''; ?>><?= e(t('admin.tuition.promotion_none')); ?></option>
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
                            $promoName = trim((string) ($promo['name'] ?? t('admin.tuition.promotion_fallback', ['id' => $promoId])));
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
                    <?= e(t('admin.tuition.status')); ?>
                    <input id="tuition-status" type="text" value="<?= e((string) ($editingTuition['status'] ?? 'debt')); ?>" readonly>
                </label>
                <p class="md:col-span-2 text-xs text-slate-500"><?= e(t('admin.tuition.status_auto_note')); ?></p>
                <div class="md:col-span-2 inline-flex flex-wrap items-center gap-2">
                    <button class="<?= ui_btn_primary_classes(); ?>" type="submit"><?= e(t('admin.tuition.update_invoice')); ?></button>
                    <a class="<?= ui_btn_secondary_classes(); ?>" href="<?= e(page_url('tuition-finance')); ?>"><?= e(t('admin.common.cancel')); ?></a>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3><?= e(t('admin.tuition.list')); ?></h3>
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
                <label class="tuition-search-shell" aria-label="<?= e(t('admin.tuition.search_label')); ?>">
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
                        placeholder="<?= e(t('admin.tuition.search_placeholder')); ?>"
                        autocomplete="off"
                    >
                </label>
                <select
                    name="status"
                    data-ajax-filter="1"
                    class="h-11 rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-700 shadow-sm outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100"
                >
                    <option value=""><?= e(t('admin.tuition.status_all')); ?></option>
                    <option value="debt" <?= $statusFilter === 'debt' ? 'selected' : ''; ?>><?= e(t('admin.tuition.status_debt')); ?></option>
                    <option value="paid" <?= $statusFilter === 'paid' ? 'selected' : ''; ?>><?= e(t('admin.tuition.status_paid')); ?></option>
                </select>
                <select
                    name="payment_plan"
                    data-ajax-filter="1"
                    class="h-11 rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-700 shadow-sm outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100"
                >
                    <option value=""><?= e(t('admin.tuition.payment_plan_all')); ?></option>
                    <option value="full" <?= $paymentPlanFilter === 'full' ? 'selected' : ''; ?>><?= e(t('admin.tuition.payment_plan_full_short')); ?></option>
                    <option value="monthly" <?= $paymentPlanFilter === 'monthly' ? 'selected' : ''; ?>><?= e(t('admin.tuition.payment_plan_monthly_short')); ?></option>
                </select>
            </div>
            <span
                data-ajax-row-info="1"
                data-visible="<?= (int) count($tuitionFees); ?>"
                data-total="<?= (int) $tuitionTotal; ?>"
                style="color: #64748b; font-size: 0.875rem; font-weight: 500; white-space: nowrap;"
            ><?= e(t('admin.tuition.showing_rows', ['shown' => (int) count($tuitionFees), 'total' => (int) $tuitionTotal])); ?></span>
        </div>
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full border-collapse text-sm" data-disable-global-filter="1" data-disable-row-detail="1">
                <thead>
                    <tr>
                        <th><?= e(t('admin.tuition.table_student_code')); ?></th>
                        <th><?= e(t('admin.tuition.table_student')); ?></th>
                        <th><?= e(t('admin.tuition.table_course')); ?></th>
                        <th><?= e(t('admin.tuition.table_class')); ?></th>
                        <th><?= e(t('admin.tuition.table_total')); ?></th>
                        <th><?= e(t('admin.tuition.table_paid')); ?></th>
                        <th><?= e(t('admin.tuition.table_remaining')); ?></th>
                        <th><?= e(t('admin.tuition.table_status')); ?></th>
                        <th><?= e(t('admin.tuition.table_payment_plan')); ?></th>
                        <th><?= e(t('admin.tuition.table_overdue')); ?></th>
                        <th><?= e(t('admin.tuition.table_actions')); ?></th>
                    </tr>
                </thead>
                <tbody data-ajax-tbody="1">
                    <?php if (empty($tuitionFees)): ?>
                        <tr>
                            <td colspan="11">
                                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500"><?= e(t('admin.tuition.empty')); ?></div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($tuitionFees as $fee): ?>
                            <?php $monthlyStatus = $monthlyStatusLabel($fee); ?>
                            <?php $monthlyStatusText = $monthlyStatusLabels[$monthlyStatus] ?? t('admin.tuition.monthly_status.na'); ?>
                            <tr data-tuition-id="<?= (int) $fee['id']; ?>">
                                <td><?= e((string) ($fee['student_code'] ?? '-')); ?></td>
                                <td><?= e((string) ($fee['full_name'] ?? t('admin.tuition.student_fallback', ['id' => (int) ($fee['student_id'] ?? 0)]))); ?></td>
                                <td><?= e((string) ($fee['course_name'] ?? '')); ?></td>
                                <td><?= e((string) ($fee['class_name'] ?? '')); ?></td>
                                <td><?= format_money((float) $fee['total_amount']); ?></td>
                                <td><?= format_money((float) $fee['amount_paid']); ?></td>
                                <td><?= format_money((float) ($fee['total_amount'] - $fee['amount_paid'])); ?></td>
                                <td><span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-bold capitalize is-<?= e((string) $fee['status']); ?>"><?= e((string) $fee['status']); ?></span></td>
                                <td><?= e((string) ($fee['payment_plan'] ?? 'full')); ?></td>
                                <td>
                                    <?php if ($monthlyStatus === 'na'): ?>
                                        <span class="text-slate-400"><?= e($monthlyStatusText); ?></span>
                                    <?php else: ?>
                                        <span class="text-xs font-semibold <?= e($monthlyStatusClass($monthlyStatus)); ?>"><?= e($monthlyStatusText); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="inline-flex flex-wrap items-center gap-2">
                                        <?php if ($canUpdateTuition): ?>
                                            <a href="<?= e(page_url('tuition-finance', ['edit' => (int) $fee['id'], 'tuition_page' => $tuitionPage, 'tuition_per_page' => $tuitionPerPage, 'search' => $searchQuery, 'status' => $statusFilter, 'payment_plan' => $paymentPlanFilter])); ?>"
                                               class="admin-action-icon-btn"
                                               data-action-kind="edit"
                                               data-skip-action-icon="1"
                                               title="<?= e(t('admin.common.edit')); ?>"
                                               aria-label="<?= e(t('admin.common.edit')); ?>">
                                                <span class="admin-action-icon-label"><?= e(t('admin.common.edit')); ?></span>
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
                                                title="<?= e(t('admin.tuition.collect_payment')); ?>"
                                                aria-label="<?= e(t('admin.tuition.collect_payment')); ?>"
                                            >
                                                <span class="admin-action-icon-label"><?= e(t('admin.tuition.collect_payment')); ?></span>
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
                                            <form method="post" action="/api/tuitions/delete" onsubmit="return confirm('<?= e(t('admin.tuition.delete_confirm')); ?>');">
                                                <?= csrf_input(); ?>
                                                <input type="hidden" name="tuition_id" value="<?= (int) $fee['id']; ?>">
                                                <button type="submit"
                                                        class="admin-action-icon-btn"
                                                        data-action-kind="delete"
                                                        data-skip-action-icon="1"
                                                        title="<?= e(t('admin.common.delete')); ?>"
                                                        aria-label="<?= e(t('admin.common.delete')); ?>">
                                                    <span class="admin-action-icon-label"><?= e(t('admin.common.delete')); ?></span>
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
                                                <input type="hidden" name="reason" value="<?= e(t('admin.tuition.request_delete_reason')); ?>">
                                                <button class="<?= ui_btn_secondary_classes('sm'); ?>" type="submit"><?= e(t('admin.tuition.request_delete')); ?></button>
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
                        <span class="font-medium"><?= e(t('admin.tuition.page_info', ['current' => (int) $tuitionPage, 'total' => (int) $tuitionTotalPages, 'count' => (int) $tuitionTotal])); ?></span>
                        <div class="inline-flex items-center gap-1.5">
                            <form class="ajax-table-per-page-form inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2 py-1" method="get" action="<?= e(page_url('tuition-finance')); ?>">
                                <input type="hidden" name="page" value="tuition-finance">
                                <input type="hidden" name="search" value="<?= e($searchQuery); ?>">
                                <input type="hidden" name="status" value="<?= e($statusFilter); ?>">
                                <input type="hidden" name="payment_plan" value="<?= e($paymentPlanFilter); ?>">
                                <label class="text-[11px] font-semibold text-slate-500" for="tuition-per-page"><?= e(t('admin.common.rows')); ?></label>
                                <select id="tuition-per-page" name="tuition_per_page" data-ajax-per-page="1" class="h-7 rounded-md border border-slate-200 bg-white px-2 text-xs font-semibold text-slate-700">
                                    <?php foreach ($tuitionPerPageOptions as $option): ?>
                                        <option value="<?= (int) $option; ?>" <?= $tuitionPerPage === (int) $option ? 'selected' : ''; ?>><?= (int) $option; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                            <?php if ($tuitionPage > 1): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('tuition-finance', ['tuition_page' => $tuitionPage - 1, 'tuition_per_page' => $tuitionPerPage, 'search' => $searchQuery, 'status' => $statusFilter, 'payment_plan' => $paymentPlanFilter])); ?>"><?= e(t('admin.common.previous')); ?></a>
                            <?php else: ?>
                                <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400"><?= e(t('admin.common.previous')); ?></span>
                            <?php endif; ?>

                            <?php if ($tuitionPage < $tuitionTotalPages): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('tuition-finance', ['tuition_page' => $tuitionPage + 1, 'tuition_per_page' => $tuitionPerPage, 'search' => $searchQuery, 'status' => $statusFilter, 'payment_plan' => $paymentPlanFilter])); ?>"><?= e(t('admin.common.next')); ?></a>
                            <?php else: ?>
                                <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400"><?= e(t('admin.common.next')); ?></span>
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
            <h3><?= e(t('admin.tuition.adjust_request_title')); ?></h3>
            <form class="grid gap-3 md:grid-cols-2" method="post" action="/api/tuitions/request-adjust">
                <?= csrf_input(); ?>
                <label class="md:col-span-2">
                    <?= e(t('admin.tuition.invoice')); ?>
                    <select name="tuition_id" required>
                        <option value=""><?= e(t('admin.tuition.choose_invoice')); ?></option>
                        <?php foreach ($tuitionOptions as $fee): ?>
                            <option value="<?= (int) $fee['id']; ?>">
                                #<?= (int) $fee['id']; ?> - <?= e(student_dropdown_label($fee)); ?> - <?= e((string) $fee['course_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    <?= e(t('admin.tuition.requested_amount')); ?>
                    <input type="number" step="1000" min="0" name="requested_amount_paid" required>
                </label>
                <label>
                    <?= e(t('admin.tuition.adjust_reason')); ?>
                    <input type="text" name="reason" required placeholder="<?= e(t('admin.tuition.adjust_reason_placeholder')); ?>">
                </label>
                <div class="md:col-span-2">
                    <button class="<?= ui_btn_primary_classes('sm'); ?>" type="submit"><?= e(t('admin.tuition.submit_adjust_request')); ?></button>
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



