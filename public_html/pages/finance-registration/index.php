<?php
require_admin_or_staff();
require_any_permission(['finance.registration.view']);

$academicModel = new AcademicModel();
$lookups = $academicModel->registrationLookups();
$students = is_array($lookups['students'] ?? null) ? $lookups['students'] : [];
$courses = is_array($lookups['courses'] ?? null) ? $lookups['courses'] : [];
$classes = is_array($lookups['classes'] ?? null) ? $lookups['classes'] : [];
$promotions = is_array($lookups['promotions'] ?? null) ? $lookups['promotions'] : [];

$viewer = auth_user();
$isAdmin = (($viewer['role'] ?? '') === 'admin');
$canCreateRegistration = $isAdmin || has_any_permission(['finance.registration.create', 'finance.registration.update']);
$registrationRows = $academicModel->listRegistrationEnrollmentRows(400);

$success = get_flash('success');
$error = get_flash('error');
$successTuitionId = (int) (get_flash('registration_success_tuition_id') ?? 0);

$formState = [
    'student_id' => 0,
    'course_id' => 0,
    'class_id' => 0,
    'package_id' => 0,
    'payment_plan' => 'full',
    'monthly_months' => '',
    'monthly_start_month' => '',
    'monthly_end_month' => '',
    'monthly_payment_day' => '',
];

$oldFormPayloadRaw = get_flash('registration_form_old');
if (is_string($oldFormPayloadRaw) && $oldFormPayloadRaw !== '') {
    $decoded = json_decode($oldFormPayloadRaw, true);
    if (is_array($decoded)) {
        $formState['student_id'] = max(0, (int) ($decoded['student_id'] ?? 0));
        $formState['course_id'] = max(0, (int) ($decoded['course_id'] ?? 0));
        $formState['class_id'] = max(0, (int) ($decoded['class_id'] ?? 0));
        $formState['package_id'] = max(0, (int) ($decoded['package_id'] ?? 0));

        $paymentPlan = (string) ($decoded['payment_plan'] ?? 'full');
        $formState['payment_plan'] = in_array($paymentPlan, ['full', 'monthly'], true)
            ? $paymentPlan
            : 'full';

        $formState['monthly_months'] = (string) ($decoded['monthly_months'] ?? '');
        $formState['monthly_start_month'] = (string) ($decoded['monthly_start_month'] ?? '');
        $formState['monthly_end_month'] = (string) ($decoded['monthly_end_month'] ?? '');
        $formState['monthly_payment_day'] = (string) ($decoded['monthly_payment_day'] ?? '');
    }
}

$courseMapForJs = [];
$courseNameById = [];
foreach ($courses as $course) {
    $courseId = (int) ($course['id'] ?? 0);
    if ($courseId <= 0) {
        continue;
    }

    $courseName = (string) ($course['course_name'] ?? '');
    $courseNameById[$courseId] = $courseName;
    $courseMapForJs[(string) $courseId] = [
        'id' => $courseId,
        'course_name' => $courseName,
        'base_price' => (float) ($course['base_price'] ?? 0),
        'total_sessions' => (int) ($course['total_sessions'] ?? 0),
    ];
}

$classStatusLabels = [
    'upcoming' => 'Sắp mở',
    'active' => 'Đang học',
    'graduated' => 'Đã kết thúc',
    'cancelled' => 'Đã hủy',
];

$promoTypeLabels = [
    'DURATION' => 'Ưu đãi thời lượng',
    'SOCIAL' => 'Ưu đãi truyền thông',
    'EVENT' => 'Ưu đãi sự kiện',
    'GROUP' => 'Ưu đãi nhóm',
];

$module = 'registration';
$adminTitle = 'Đăng ký khóa học';
?>
<div class="grid gap-4">
    <?php if ($success): ?>
        <div class="rounded-xl border-l-4 border-emerald-500 bg-emerald-50 p-3 text-sm text-emerald-700">
            <div><?= e($success); ?></div>
            <?php if ($successTuitionId > 0): ?>
                <div class="mt-2">
                    <a class="font-semibold text-emerald-700 underline" href="<?= e(page_url('tuition-finance', ['search' => (string) $successTuitionId, 'highlight_tuition_id' => $successTuitionId])); ?>">Xem học phí vừa tạo</a>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="rounded-xl border-l-4 border-rose-500 bg-rose-50 p-3 text-sm text-rose-700"><?= e($error); ?></div>
    <?php endif; ?>

    <?php if ($canCreateRegistration): ?>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3>Form đăng ký khóa học</h3>
            <form id="registration-form" class="grid gap-3 lg:grid-cols-2" method="post" action="/api/tuitions/register-course">
                <?= csrf_input(); ?>

                <label>
                    Học viên
                    <select id="registration-student" name="student_id" required>
                        <option value="">-- Chọn học viên --</option>
                        <?php foreach ($students as $student): ?>
                            <option value="<?= (int) ($student['id'] ?? 0); ?>" <?= $formState['student_id'] === (int) ($student['id'] ?? 0) ? 'selected' : ''; ?>>
                                <?= e(student_dropdown_label($student)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    Khóa học
                    <select id="registration-course" name="course_id" required>
                        <option value="">-- Chọn khóa học --</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?= (int) ($course['id'] ?? 0); ?>" <?= $formState['course_id'] === (int) ($course['id'] ?? 0) ? 'selected' : ''; ?>>
                                <?= e((string) ($course['course_name'] ?? '')); ?> - <?= format_money((float) ($course['base_price'] ?? 0)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    Lớp học
                    <select id="registration-class" name="class_id" required>
                        <option value="">-- Chọn lớp học --</option>
                        <?php foreach ($classes as $class): ?>
                            <?php
                            $classStatus = (string) ($class['status'] ?? 'upcoming');
                            $classStatusLabel = (string) ($classStatusLabels[$classStatus] ?? $classStatus);
                            $classLabel = (string) ($class['class_name'] ?? 'Lớp chưa đặt tên') . ' (' . $classStatusLabel . ')';
                            ?>
                            <option
                                value="<?= (int) ($class['id'] ?? 0); ?>"
                                data-course-id="<?= (int) ($class['course_id'] ?? 0); ?>"
                                data-status="<?= e($classStatus); ?>"
                                <?= $formState['class_id'] === (int) ($class['id'] ?? 0) ? 'selected' : ''; ?>
                            >
                                <?= e($classLabel); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small id="registration-class-hint" class="mt-1 block text-xs text-slate-500">Vui lòng chọn khóa học trước để lọc lớp phù hợp.</small>
                </label>

                <label>
                    Chế độ đóng tiền
                    <select id="registration-payment-plan" name="payment_plan" required>
                        <option value="full" <?= $formState['payment_plan'] === 'full' ? 'selected' : ''; ?>>Đóng một lần (full)</option>
                        <option value="monthly" <?= $formState['payment_plan'] === 'monthly' ? 'selected' : ''; ?>>Đóng theo tháng (monthly)</option>
                    </select>
                </label>

                <div
                    id="registration-monthly-fields"
                    class="grid gap-3 lg:col-span-2 lg:grid-cols-4"
                    style="<?= $formState['payment_plan'] === 'monthly' ? '' : 'display:none;'; ?>"
                >
                    <label>
                        Số tháng đóng
                        <input
                            type="number"
                            min="1"
                            step="1"
                            name="monthly_months"
                            id="registration-monthly-months"
                            value="<?= e((string) $formState['monthly_months']); ?>"
                            placeholder="VD: 6"
                        >
                    </label>
                    <label>
                        Từ tháng
                        <input
                            type="month"
                            name="monthly_start_month"
                            id="registration-monthly-start"
                            value="<?= e((string) $formState['monthly_start_month']); ?>"
                        >
                    </label>
                    <label>
                        Đến tháng
                        <input
                            type="month"
                            name="monthly_end_month"
                            id="registration-monthly-end"
                            value="<?= e((string) $formState['monthly_end_month']); ?>"
                            readonly
                        >
                    </label>
                    <label>
                        Ngày đóng hàng tháng
                        <input
                            type="number"
                            min="1"
                            max="31"
                            step="1"
                            name="monthly_payment_day"
                            id="registration-monthly-day"
                            value="<?= e((string) $formState['monthly_payment_day']); ?>"
                            placeholder="VD: 15"
                        >
                    </label>
                    <p class="lg:col-span-4 text-xs text-slate-500">Trường hợp tháng không đủ ngày, hệ thống sẽ ghi nhận vào ngày cuối tháng.</p>
                </div>

                <label>
                    Ưu đãi giảm giá
                    <select id="registration-package" name="package_id">
                        <option value="0" <?= $formState['package_id'] === 0 ? 'selected' : ''; ?>>Không áp dụng ưu đãi</option>
                        <?php foreach ($promotions as $promo): ?>
                            <?php
                            $promoId = (int) ($promo['id'] ?? 0);
                            if ($promoId <= 0) {
                                continue;
                            }

                            $promoCourseId = (int) ($promo['course_id'] ?? 0);
                            $discountPercent = max(0, min(100, (float) ($promo['discount_value'] ?? 0)));
                            $discountPercentText = rtrim(rtrim(number_format($discountPercent, 2, '.', ''), '0'), '.');
                            $promoName = trim((string) ($promo['name'] ?? ('Ưu đãi #' . $promoId)));
                            ?>
                            <option
                                value="<?= $promoId; ?>"
                                data-course-id="<?= $promoCourseId; ?>"
                                data-discount-value="<?= e((string) $discountPercent); ?>"
                                <?= $formState['package_id'] === $promoId ? 'selected' : ''; ?>
                            >
                                <?= e($promoName . ' - ' . $discountPercentText . '%'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small id="registration-promo-hint" class="mt-1 block text-xs text-slate-500">Chọn khóa học để lọc ưu đãi áp dụng tương ứng.</small>
                </label>


                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 lg:col-span-2">
                    <h4 class="mb-3 text-sm font-extrabold uppercase tracking-wide text-slate-600">Xem trước hóa đơn sẽ tạo</h4>
                    <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-4">
                        <div class="rounded-lg border border-slate-200 bg-white p-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Học phí gốc</p>
                            <p id="preview-base-amount" class="mt-1 text-base font-extrabold text-slate-800">0 đ</p>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-white p-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Giảm giá áp dụng</p>
                            <p id="preview-discount-amount" class="mt-1 text-base font-extrabold text-amber-700">0 đ</p>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-white p-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Tổng cần thu</p>
                            <p id="preview-total-amount" class="mt-1 text-base font-extrabold text-emerald-700">0 đ</p>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-white p-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Trạng thái sau khi tạo</p>
                            <p id="preview-invoice-status" class="mt-1 inline-flex items-center rounded-full border border-rose-200 bg-rose-50 px-2.5 py-1 text-xs font-bold uppercase tracking-wide text-rose-700">debt | đã thu 0 đ</p>
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-slate-500">Ghi chú: hệ thống sẽ kiểm tra đúng học viên, đúng khóa - lớp và ngăn tạo trùng học phí.</p>
                </div>

                <div class="inline-flex flex-wrap items-center gap-2 lg:col-span-2">
                    <button class="<?= ui_btn_primary_classes(); ?>" type="submit">Đăng ký khóa học</button>
                    <a class="<?= ui_btn_secondary_classes(); ?>" href="<?= e(page_url('tuition-finance')); ?>">Xem danh sách học phí</a>
                </div>
            </form>
        </article>
    <?php else: ?>
        <article class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
            <h3>Bạn chưa có quyền tạo đăng ký</h3>
            <p class="text-sm text-amber-800">Tài khoản hiện tại chưa có quyền tạo học phí trực tiếp. Vui lòng liên hệ Admin để cấp quyền <strong>finance.tuition.create</strong>.</p>
        </article>
    <?php endif; ?>

</div>

<?php if ($canCreateRegistration): ?>
<script>
    (function () {
        const courseSelect = document.getElementById('registration-course');
        const classSelect = document.getElementById('registration-class');
        const classHint = document.getElementById('registration-class-hint');
        const packageSelect = document.getElementById('registration-package');
        const promoHint = document.getElementById('registration-promo-hint');
        const previewBaseAmount = document.getElementById('preview-base-amount');
        const previewDiscountAmount = document.getElementById('preview-discount-amount');
        const previewTotalAmount = document.getElementById('preview-total-amount');
        const previewInvoiceStatus = document.getElementById('preview-invoice-status');
        const paymentPlanSelect = document.getElementById('registration-payment-plan');
        const monthlyFields = document.getElementById('registration-monthly-fields');
        const monthlyMonthsInput = document.getElementById('registration-monthly-months');
        const monthlyStartInput = document.getElementById('registration-monthly-start');
        const monthlyEndInput = document.getElementById('registration-monthly-end');
        const monthlyDayInput = document.getElementById('registration-monthly-day');
        const courseMap = <?= json_encode($courseMapForJs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

        const allClassOptions = classSelect ? Array.from(classSelect.options).map(function(o) { return o.cloneNode(true); }) : [];
        const allPackageOptions = packageSelect ? Array.from(packageSelect.options).map(function(o) { return o.cloneNode(true); }) : [];

        if (!courseSelect || !classSelect || !packageSelect || !paymentPlanSelect) {
            return;
        }

        function parseMonth(value) {
            if (!value || !/^[0-9]{4}-[0-9]{2}$/.test(value)) {
                return null;
            }
            const parts = value.split('-').map(Number);
            if (parts.length !== 2) {
                return null;
            }
            const year = parts[0];
            const month = parts[1];
            if (!Number.isFinite(year) || !Number.isFinite(month) || month < 1 || month > 12) {
                return null;
            }
            return { year, month };
        }

        function formatMonth(year, month) {
            return `${year}-${String(month).padStart(2, '0')}`;
        }

        function addMonths(year, month, delta) {
            const total = year * 12 + (month - 1) + delta;
            const nextYear = Math.floor(total / 12);
            const nextMonth = (total % 12) + 1;
            return { year: nextYear, month: nextMonth };
        }

        function updateMonthlyEndMonth() {
            if (!monthlyEndInput || !monthlyStartInput || !monthlyMonthsInput) {
                return;
            }

            const start = parseMonth(monthlyStartInput.value);
            const monthsCount = Number(monthlyMonthsInput.value || 0);
            if (!start || monthsCount <= 0) {
                monthlyEndInput.value = '';
                return;
            }

            const end = addMonths(start.year, start.month, monthsCount - 1);
            monthlyEndInput.value = formatMonth(end.year, end.month);
        }

        function syncMonthlyFields() {
            if (!monthlyFields || !monthlyMonthsInput || !monthlyStartInput || !monthlyEndInput || !monthlyDayInput) {
                return;
            }

            const isMonthly = paymentPlanSelect.value === 'monthly';
            monthlyFields.style.display = isMonthly ? '' : 'none';
            monthlyMonthsInput.required = isMonthly;
            monthlyStartInput.required = isMonthly;
            monthlyEndInput.required = isMonthly;
            monthlyDayInput.required = isMonthly;

            if (!isMonthly) {
                monthlyMonthsInput.value = '';
                monthlyStartInput.value = '';
                monthlyEndInput.value = '';
                monthlyDayInput.value = '';
            }
        }

        function toMoney(value) {
            const amount = Number.isFinite(value) ? value : 0;
            return new Intl.NumberFormat('vi-VN').format(Math.max(0, Math.round(amount))) + ' đ';
        }

        function syncClassOptions() {
            const selectedCourseId = Number(courseSelect.value || 0);
            let visibleCount = 0;

            classSelect.innerHTML = '';
            allClassOptions.forEach(function (option) {
                const clone = option.cloneNode(true);
                if (!clone.value) {
                    classSelect.appendChild(clone);
                    return;
                }

                const optionCourseId = Number(clone.dataset.courseId || 0);
                const show = selectedCourseId > 0 && optionCourseId === selectedCourseId;
                if (show) {
                    classSelect.appendChild(clone);
                    visibleCount += 1;
                }
            });

            if (classSelect.tomselect) {
                classSelect.tomselect.sync();
            }

            if (selectedCourseId <= 0) {
                classSelect.value = '';
                classSelect.disabled = true;
                if (classSelect.tomselect) {
                    classSelect.tomselect.setValue('');
                    classSelect.tomselect.disable();
                }
                if (classHint) {
                    classHint.textContent = 'Vui lòng chọn khóa học trước để lọc lớp phù hợp.';
                }
                return;
            }

            if (visibleCount <= 0) {
                classSelect.value = '';
                classSelect.disabled = true;
                if (classSelect.tomselect) {
                    classSelect.tomselect.setValue('');
                    classSelect.tomselect.disable();
                }
                if (classHint) {
                    classHint.textContent = 'Khóa học này chưa có lớp khả dụng để đăng ký.';
                }
                return;
            }

            const selectedOption = classSelect.selectedOptions && classSelect.selectedOptions[0]
                ? classSelect.selectedOptions[0]
                : null;
            if (!selectedOption || !selectedOption.value) {
                classSelect.value = '';
                if (classSelect.tomselect) {
                    classSelect.tomselect.setValue('');
                }
            }

            classSelect.disabled = false;
            if (classSelect.tomselect) {
                classSelect.tomselect.enable();
            }
            if (classHint) {
                classHint.textContent = 'Chỉ hiển thị lớp thuộc khóa học đã chọn.';
            }
        }

        function syncPackageOptions() {
            const selectedCourseId = Number(courseSelect.value || 0);
            let visibleCount = 0;

            packageSelect.innerHTML = '';
            allPackageOptions.forEach(function (option) {
                const clone = option.cloneNode(true);
                if (!clone.value || Number(clone.value) === 0) {
                    packageSelect.appendChild(clone);
                    return;
                }

                const optionCourseId = Number(clone.dataset.courseId || 0);
                const show = selectedCourseId > 0 && (optionCourseId === 0 || optionCourseId === selectedCourseId);
                if (show) {
                    packageSelect.appendChild(clone);
                    visibleCount += 1;
                }
            });

            if (packageSelect.tomselect) {
                packageSelect.tomselect.sync();
            }

            if (selectedCourseId <= 0) {
                packageSelect.value = '0';
                packageSelect.disabled = true;
                if (packageSelect.tomselect) {
                    packageSelect.tomselect.setValue('0');
                    packageSelect.tomselect.disable();
                }
                if (promoHint) {
                    promoHint.textContent = 'Vui lòng chọn khóa học trước khi chọn ưu đãi.';
                }
                return;
            }

            const selectedOption = packageSelect.selectedOptions && packageSelect.selectedOptions[0]
                ? packageSelect.selectedOptions[0]
                : null;
            if (!selectedOption || !selectedOption.value) {
                packageSelect.value = '0';
                if (packageSelect.tomselect) {
                    packageSelect.tomselect.setValue('0');
                }
            }

            packageSelect.disabled = false;
            if (packageSelect.tomselect) {
                packageSelect.tomselect.enable();
            }
            if (promoHint) {
                promoHint.textContent = visibleCount > 0
                    ? 'Đang hiển thị ưu đãi toàn trung tâm và ưu đãi theo khóa học đã chọn.'
                    : 'Khóa học này chưa có ưu đãi riêng. Bạn vẫn có thể tiếp tục với tùy chọn không ưu đãi.';
            }
        }

        function getSelectedDiscountPercent() {
            const selectedOption = packageSelect.selectedOptions && packageSelect.selectedOptions[0]
                ? packageSelect.selectedOptions[0]
                : null;
            if (!selectedOption || !selectedOption.value || Number(selectedOption.value) === 0) {
                return 0;
            }

            const discountValue = Number(selectedOption.dataset.discountValue || 0);
            if (!Number.isFinite(discountValue)) {
                return 0;
            }

            return Math.max(0, Math.min(100, discountValue));
        }

        function updatePreview() {
            const selectedCourseId = String(Number(courseSelect.value || 0));
            const selectedCourse = courseMap[selectedCourseId] || null;
            const baseAmount = selectedCourse ? Number(selectedCourse.base_price || 0) : 0;

            const discountPercent = getSelectedDiscountPercent();
            const discountApplied = (baseAmount * discountPercent) / 100;
            const totalAmount = Math.max(0, baseAmount - discountApplied);
            if (previewBaseAmount) {
                previewBaseAmount.textContent = toMoney(baseAmount);
            }
            if (previewDiscountAmount) {
                previewDiscountAmount.textContent = toMoney(discountApplied);
            }
            if (previewTotalAmount) {
                previewTotalAmount.textContent = toMoney(totalAmount);
            }
            if (previewInvoiceStatus) {
                previewInvoiceStatus.textContent = 'debt | đã thu 0 đ';
                previewInvoiceStatus.className = 'mt-1 inline-flex items-center rounded-full border border-rose-200 bg-rose-50 px-2.5 py-1 text-xs font-bold uppercase tracking-wide text-rose-700';
            }
        }

        courseSelect.addEventListener('change', function () {
            syncClassOptions();
            syncPackageOptions();
            updatePreview();
        });

        classSelect.addEventListener('change', function () {
            updatePreview();
        });

        packageSelect.addEventListener('change', function () {
            updatePreview();
        });

        if (monthlyMonthsInput) {
            monthlyMonthsInput.addEventListener('input', updateMonthlyEndMonth);
        }
        if (monthlyStartInput) {
            monthlyStartInput.addEventListener('input', updateMonthlyEndMonth);
        }
        paymentPlanSelect.addEventListener('change', function () {
            syncMonthlyFields();
            updateMonthlyEndMonth();
        });

        syncClassOptions();
        syncPackageOptions();
        updatePreview();
        syncMonthlyFields();
        updateMonthlyEndMonth();
    })();
</script>
<?php endif; ?>

<script>
    (function () {
        const feedbackElement = document.getElementById('registration-status-feedback');

        function toMoney(value) {
            const amount = Number.isFinite(value) ? value : 0;
            return new Intl.NumberFormat('vi-VN').format(Math.max(0, Math.round(amount))) + ' đ';
        }

        function showFeedback(message, isSuccess) {
            if (!(feedbackElement instanceof HTMLElement)) {
                return;
            }

            feedbackElement.classList.remove('hidden');
            feedbackElement.textContent = String(message || '');
            feedbackElement.classList.remove('border-emerald-500', 'bg-emerald-50', 'text-emerald-700');
            feedbackElement.classList.remove('border-rose-500', 'bg-rose-50', 'text-rose-700');
            if (isSuccess) {
                feedbackElement.classList.add('border-emerald-500', 'bg-emerald-50', 'text-emerald-700');
            } else {
                feedbackElement.classList.add('border-rose-500', 'bg-rose-50', 'text-rose-700');
            }
        }

        function normalizeLearningStatus(status) {
            const normalized = String(status || '').toLowerCase();
            return normalized === 'trial' ? 'trial' : 'official';
        }

        function statusLabel(status) {
            return normalizeLearningStatus(status) === 'trial' ? 'Học thử' : 'Chính thức';
        }

        function statusBadgeClass(status) {
            return normalizeLearningStatus(status) === 'trial'
                ? 'border-blue-200 bg-blue-50 text-blue-700'
                : 'border-emerald-200 bg-emerald-50 text-emerald-700';
        }

        function renderTuitionCell(cell, rowData) {
            if (!(cell instanceof HTMLElement) || !rowData || typeof rowData !== 'object') {
                return;
            }

            const tuitionId = Number(rowData.tuition_id || 0);
            const totalAmount = Number(rowData.total_amount || 0);
            const amountPaid = Number(rowData.amount_paid || 0);
            const remainingAmount = Number(rowData.remaining_amount || 0);
            const tuitionStatus = String(rowData.tuition_status || '').toLowerCase() === 'paid' ? 'paid' : 'debt';
            const badgeClass = tuitionStatus === 'paid'
                ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                : 'border-rose-200 bg-rose-50 text-rose-700';

            if (tuitionId > 0) {
                cell.innerHTML = ''
                    + '<div class="text-xs text-slate-700 leading-5">'
                    + '<div>Tổng: ' + toMoney(totalAmount) + '</div>'
                    + '<div>Đã thu: ' + toMoney(amountPaid) + '</div>'
                    + '<div>Còn: ' + toMoney(remainingAmount) + '</div>'
                    + '</div>'
                    + '<div class="mt-1 inline-flex items-center rounded-full border px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide ' + badgeClass + '">' + tuitionStatus + '</div>';
                return;
            }

            cell.innerHTML = '<span class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">Chưa tạo học phí</span>';
        }

        function renderLockedStatusCell(cell, status) {
            if (!(cell instanceof HTMLElement)) {
                return;
            }

            const normalized = normalizeLearningStatus(status);
            cell.innerHTML = '<span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-bold ' + statusBadgeClass(normalized) + '">' + statusLabel(normalized) + '</span>';
        }

        const selects = Array.from(document.querySelectorAll('[data-learning-status-select="1"]'));
        if (selects.length === 0) {
            return;
        }

        selects.forEach(function (selectElement) {
            if (!(selectElement instanceof HTMLSelectElement)) {
                return;
            }

            if (String(selectElement.dataset.hasPayment || '') === '1') {
                selectElement.disabled = true;
            }

            selectElement.addEventListener('change', function () {
                const currentStatus = String(selectElement.dataset.currentStatus || '').toLowerCase();
                const nextStatus = String(selectElement.value || '').toLowerCase();
                if (nextStatus === '' || nextStatus === currentStatus) {
                    return;
                }

                if (currentStatus === 'official' && nextStatus === 'trial') {
                    const accepted = window.confirm('Chuyển từ chính thức sang học thử chỉ hợp lệ khi học viên chưa thanh toán. Nếu hợp lệ, hệ thống sẽ xóa học phí của học viên này. Bạn muốn tiếp tục?');
                    if (!accepted) {
                        selectElement.value = currentStatus;
                        return;
                    }
                }

                const formElement = selectElement.closest('form');
                if (!(formElement instanceof HTMLFormElement)) {
                    return;
                }

                const formData = new FormData(formElement);
                const originalStatus = String(selectElement.dataset.originalStatus || currentStatus || 'official').toLowerCase();
                const rowElement = selectElement.closest('tr');
                const statusCell = rowElement ? rowElement.querySelector('[data-learning-status-cell="1"]') : null;
                const tuitionCell = rowElement ? rowElement.querySelector('[data-tuition-cell="1"]') : null;

                selectElement.disabled = true;

                fetch(formElement.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                })
                    .then(function (response) {
                        return response.json().catch(function () {
                            return {
                                status: 'error',
                                message: 'Không nhận được phản hồi hợp lệ từ máy chủ.',
                            };
                        });
                    })
                    .then(function (payload) {
                        const isSuccess = String(payload && payload.status || '').toLowerCase() === 'success';
                        const message = String(payload && payload.message || (isSuccess ? 'Cập nhật thành công.' : 'Cập nhật thất bại.'));

                        if (!isSuccess) {
                            selectElement.value = originalStatus;
                            selectElement.disabled = String(selectElement.dataset.hasPayment || '') === '1';
                            showFeedback(message, false);
                            return;
                        }

                        const data = payload && payload.data && typeof payload.data === 'object' ? payload.data : {};
                        const rowData = data.row && typeof data.row === 'object' ? data.row : {};

                        const resolvedStatus = normalizeLearningStatus(rowData.learning_status || nextStatus);
                        selectElement.value = resolvedStatus;
                        selectElement.dataset.currentStatus = resolvedStatus;
                        selectElement.dataset.originalStatus = resolvedStatus;

                        const hasPayment = Boolean(rowData.has_payment);
                        selectElement.dataset.hasPayment = hasPayment ? '1' : '0';

                        if (hasPayment) {
                            if (statusCell instanceof HTMLElement) {
                                renderLockedStatusCell(statusCell, resolvedStatus);
                            }
                        } else {
                            selectElement.disabled = false;
                        }

                        renderTuitionCell(tuitionCell, rowData);
                        showFeedback(message, true);
                    })
                    .catch(function () {
                        selectElement.value = originalStatus;
                        selectElement.disabled = String(selectElement.dataset.hasPayment || '') === '1';
                        showFeedback('Không thể cập nhật trạng thái lúc này. Vui lòng thử lại.', false);
                    });
            });
        });
    })();
</script>


