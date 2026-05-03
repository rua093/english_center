<?php
require_admin_or_staff();
require_any_permission(['finance.tuition.view']);

$academicModel = new AcademicModel();
$tuitionPage = max(1, (int) ($_GET['tuition_page'] ?? 1));
$tuitionPerPage = ui_pagination_resolve_per_page('tuition_per_page', 10);
$tuitionTotal = $academicModel->countTuitionFees();
$tuitionTotalPages = max(1, (int) ceil($tuitionTotal / $tuitionPerPage));
if ($tuitionPage > $tuitionTotalPages) {
    $tuitionPage = $tuitionTotalPages;
}
$tuitionFees = $academicModel->listTuitionFeesPage($tuitionPage, $tuitionPerPage);
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

$success = get_flash('success');
$error = get_flash('error');
?>
<style>
    .tuition-readonly-field {
        position: relative;
    }

    .tuition-readonly-field > input[readonly],
    .tuition-readonly-field > select[disabled],
    .tuition-readonly-field > textarea[readonly] {
        cursor: not-allowed;
        background: #f8fafc !important;
        color: #475569 !important;
        border-color: #cbd5e1 !important;
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
        <div class="hidden" aria-hidden="true">
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
                    <select name="payment_plan">
                        <option value="full" <?= (($editingTuition['payment_plan'] ?? 'full') === 'full') ? 'selected' : ''; ?>>Đóng một lần (full)</option>
                        <option value="monthly" <?= (($editingTuition['payment_plan'] ?? '') === 'monthly') ? 'selected' : ''; ?>>Đóng theo tháng (monthly)</option>
                    </select>
                </label>
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
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full border-collapse text-sm">
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
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tuitionFees)): ?>
                        <tr>
                            <td colspan="10">
                                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">Chưa có dữ liệu học phí.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($tuitionFees as $fee): ?>
                            <tr>
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
                                    <div class="inline-flex flex-wrap items-center gap-2">
                                        <?php if ($canUpdateTuition): ?>
                                            <a class="text-sm font-semibold text-blue-700 hover:underline" href="<?= e(page_url('tuition-finance', ['edit' => (int) $fee['id'], 'tuition_page' => $tuitionPage, 'tuition_per_page' => $tuitionPerPage])); ?>">Sửa</a>
                                        <?php endif; ?>

                                        <?php if ($canDeleteTuition): ?>
                                            <form method="post" action="/api/tuitions/delete" onsubmit="return confirm('Bạn chắc chắn muốn xóa hóa đơn học phí này?');">
                                                <?= csrf_input(); ?>
                                                <input type="hidden" name="tuition_id" value="<?= (int) $fee['id']; ?>">
                                                <button class="<?= ui_btn_danger_classes('sm'); ?>" type="submit">Xóa</button>
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
            <?php if ($tuitionTotal > 0): ?>
                <div class="border-t border-slate-200 bg-slate-50/80 px-3 py-2">
                    <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-slate-600">
                        <span class="font-medium">Trang <?= (int) $tuitionPage; ?>/<?= (int) $tuitionTotalPages; ?> - Tổng <?= (int) $tuitionTotal; ?> hóa đơn</span>
                        <div class="inline-flex items-center gap-1.5">
                            <form class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2 py-1" method="get" action="<?= e(page_url('tuition-finance')); ?>">
                                <input type="hidden" name="page" value="tuition-finance">
                                <label class="text-[11px] font-semibold text-slate-500" for="tuition-per-page">Số dòng</label>
                                <select id="tuition-per-page" name="tuition_per_page" class="h-7 rounded-md border border-slate-200 bg-white px-2 text-xs font-semibold text-slate-700" onchange="this.form.submit()">
                                    <?php foreach ($tuitionPerPageOptions as $option): ?>
                                        <option value="<?= (int) $option; ?>" <?= $tuitionPerPage === (int) $option ? 'selected' : ''; ?>><?= (int) $option; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                            <?php if ($tuitionPage > 1): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('tuition-finance', ['tuition_page' => $tuitionPage - 1, 'tuition_per_page' => $tuitionPerPage])); ?>">Trước</a>
                            <?php else: ?>
                                <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400">Trước</span>
                            <?php endif; ?>

                            <?php if ($tuitionPage < $tuitionTotalPages): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('tuition-finance', ['tuition_page' => $tuitionPage + 1, 'tuition_per_page' => $tuitionPerPage])); ?>">Sau</a>
                            <?php else: ?>
                                <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400">Sau</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
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
        window.updateTuitionEditPreview = function (scope) {
            const root = scope instanceof HTMLFormElement || scope instanceof HTMLElement ? scope : document;
            const packageSelect = root.querySelector('#tuition-package-select');
            const totalAmountInput = root.querySelector('#tuition-total-amount');
            const amountPaidInput = root.querySelector('#tuition-amount-paid');
            const statusInput = root.querySelector('#tuition-status');

            if (!(packageSelect instanceof HTMLSelectElement) || !(totalAmountInput instanceof HTMLInputElement) || !(statusInput instanceof HTMLInputElement)) {
                return;
            }

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

        window.updateTuitionEditPreview(document);
    })();
</script>



