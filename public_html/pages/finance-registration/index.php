<?php
require_admin_or_staff();
require_permission('finance.tuition.view');

$academicModel = new AcademicModel();
$lookups = $academicModel->registrationLookups();
$students = is_array($lookups['students'] ?? null) ? $lookups['students'] : [];
$courses = is_array($lookups['courses'] ?? null) ? $lookups['courses'] : [];
$classes = is_array($lookups['classes'] ?? null) ? $lookups['classes'] : [];
$promotions = is_array($lookups['promotions'] ?? null) ? $lookups['promotions'] : [];

$viewer = auth_user();
$isAdmin = (($viewer['role'] ?? '') === 'admin');
$canCreateRegistration = $isAdmin || has_any_permission(['finance.tuition.manage', 'finance.tuition.create', 'finance.tuition.update']);
$canChangeLearningStatus = $isAdmin || has_any_permission(['finance.tuition.manage', 'finance.tuition.create', 'finance.tuition.update']);
$registrationRows = $academicModel->listRegistrationEnrollmentRows(400);

$learningStatusLabels = [
    'official' => 'Chính thức',
    'trial' => 'Học thử',
];

$success = get_flash('success');
$error = get_flash('error');

$formState = [
    'student_id' => 0,
    'course_id' => 0,
    'class_id' => 0,
    'package_id' => 0,
    'payment_plan' => 'full',
    'learning_status' => 'official',
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

        $learningStatus = (string) ($decoded['learning_status'] ?? 'official');
        $formState['learning_status'] = in_array($learningStatus, ['trial', 'official'], true)
            ? $learningStatus
            : 'official';
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
        <div class="rounded-xl border-l-4 border-emerald-500 bg-emerald-50 p-3 text-sm text-emerald-700"><?= e($success); ?></div>
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
                                <?= e((string) ($student['full_name'] ?? '')); ?>
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
                            $promoType = strtoupper(trim((string) ($promo['promo_type'] ?? '')));
                            $promoTypeLabel = (string) ($promoTypeLabels[$promoType] ?? $promoType);
                            $discountPercent = max(0, min(100, (float) ($promo['discount_value'] ?? 0)));
                            $discountPercentText = rtrim(rtrim(number_format($discountPercent, 2, '.', ''), '0'), '.');
                            $promoName = trim((string) ($promo['name'] ?? ('Ưu đãi #' . $promoId)));
                            $scopeLabel = $promoCourseId > 0
                                ? ('Khóa: ' . (string) ($courseNameById[$promoCourseId] ?? ('#' . $promoCourseId)))
                                : 'Toàn trung tâm';
                            ?>
                            <option
                                value="<?= $promoId; ?>"
                                data-course-id="<?= $promoCourseId; ?>"
                                data-discount-value="<?= e((string) $discountPercent); ?>"
                                <?= $formState['package_id'] === $promoId ? 'selected' : ''; ?>
                            >
                                <?= e($promoName . ' - ' . $discountPercentText . '% | ' . $promoTypeLabel . ' | ' . $scopeLabel); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small id="registration-promo-hint" class="mt-1 block text-xs text-slate-500">Chọn khóa học để lọc ưu đãi áp dụng tương ứng.</small>
                </label>

                <label>
                    Trạng thái học khi ghi danh
                    <select name="learning_status">
                        <option value="official" <?= $formState['learning_status'] === 'official' ? 'selected' : ''; ?>>official</option>
                        <option value="trial" <?= $formState['learning_status'] === 'trial' ? 'selected' : ''; ?>>trial</option>
                    </select>
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
            <p class="text-sm text-amber-800">Tài khoản hiện tại chưa có quyền tạo học phí trực tiếp. Vui lòng liên hệ Admin để cấp quyền <strong>finance.tuition.create</strong> hoặc <strong>finance.tuition.manage</strong>.</p>
        </article>
    <?php endif; ?>

    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
            <div>
                <h3>Danh sách ghi danh & trạng thái học</h3>
                <p class="text-sm text-slate-500">Chuyển trực tiếp giữa <strong>Học thử</strong> và <strong>Chính thức</strong> trong cột Trạng thái học.</p>
            </div>
            <?php if (!$canChangeLearningStatus): ?>
                <span class="inline-flex items-center rounded-lg border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700">Bạn chưa có quyền chuyển trạng thái học viên.</span>
            <?php endif; ?>
        </div>

        <div id="registration-status-feedback" class="hidden mb-3 rounded-xl border-l-4 p-3 text-sm"></div>

        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full border-collapse text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                    <tr>
                        <th class="px-3 py-2">Học viên</th>
                        <th class="px-3 py-2">Khóa học</th>
                        <th class="px-3 py-2">Lớp học</th>
                        <th class="px-3 py-2">Trạng thái học</th>
                        <th class="px-3 py-2">Học phí</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($registrationRows)): ?>
                        <tr>
                            <td colspan="5">
                                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">Chưa có dữ liệu ghi danh.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($registrationRows as $row): ?>
                            <?php
                            $studentId = (int) ($row['student_id'] ?? 0);
                            $classId = (int) ($row['class_id'] ?? 0);
                            $learningStatus = (string) ($row['learning_status'] ?? 'official');
                            if (!in_array($learningStatus, ['trial', 'official'], true)) {
                                $learningStatus = 'official';
                            }

                            $learningStatusLabel = (string) ($learningStatusLabels[$learningStatus] ?? $learningStatus);
                            $badgeClass = $learningStatus === 'trial'
                                ? 'border-blue-200 bg-blue-50 text-blue-700'
                                : 'border-emerald-200 bg-emerald-50 text-emerald-700';

                            $tuitionId = (int) ($row['tuition_id'] ?? 0);
                            $totalAmount = max(0, (float) ($row['total_amount'] ?? 0));
                            $amountPaid = max(0, (float) ($row['amount_paid'] ?? 0));
                            $remainingAmount = max(0, $totalAmount - $amountPaid);
                            $tuitionStatus = strtolower(trim((string) ($row['tuition_status'] ?? 'debt')));
                            if (!in_array($tuitionStatus, ['paid', 'debt'], true)) {
                                $tuitionStatus = ($totalAmount > 0 && $amountPaid >= $totalAmount) ? 'paid' : 'debt';
                            }
                            $hasPayment = $amountPaid > 0.0001;
                            ?>
                            <tr class="border-b border-slate-100 last:border-b-0" data-registration-row="1" data-student-id="<?= $studentId; ?>" data-class-id="<?= $classId; ?>">
                                <td class="px-3 py-2 align-top font-semibold text-slate-800"><?= e((string) ($row['student_name'] ?? ('Học viên #' . $studentId))); ?></td>
                                <td class="px-3 py-2 align-top text-slate-700"><?= e((string) ($row['course_name'] ?? '--')); ?></td>
                                <td class="px-3 py-2 align-top text-slate-700"><?= e((string) ($row['class_name'] ?? '--')); ?></td>
                                <td class="px-3 py-2 align-top" data-learning-status-cell="1">
                                    <?php if ($canChangeLearningStatus && in_array($learningStatus, ['trial', 'official'], true)): ?>
                                        <form method="post" action="/api/tuitions/update-learning-status" class="inline-flex items-center gap-2">
                                            <?= csrf_input(); ?>
                                            <input type="hidden" name="student_id" value="<?= $studentId; ?>">
                                            <input type="hidden" name="class_id" value="<?= $classId; ?>">
                                            <select
                                                name="learning_status"
                                                class="h-9 rounded-md border border-slate-300 bg-white px-2 text-sm font-semibold"
                                                data-learning-status-select="1"
                                                data-current-status="<?= e($learningStatus); ?>"
                                                data-original-status="<?= e($learningStatus); ?>"
                                                data-has-payment="<?= $hasPayment ? '1' : '0'; ?>"
                                                <?= $hasPayment ? 'disabled' : ''; ?>
                                            >
                                                <option value="official" <?= $learningStatus === 'official' ? 'selected' : ''; ?>>Chính thức</option>
                                                <option value="trial" <?= $learningStatus === 'trial' ? 'selected' : ''; ?>>Học thử</option>
                                            </select>
                                        </form>
                                    <?php else: ?>
                                        <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-bold <?= e($badgeClass); ?>"><?= e($learningStatusLabel); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-3 py-2 align-top" data-tuition-cell="1">
                                    <?php if ($tuitionId > 0): ?>
                                        <div class="text-xs text-slate-700 leading-5">
                                            <div>Tổng: <?= format_money($totalAmount); ?></div>
                                            <div>Đã thu: <?= format_money($amountPaid); ?></div>
                                            <div>Còn: <?= format_money($remainingAmount); ?></div>
                                        </div>
                                        <div class="mt-1 inline-flex items-center rounded-full border px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide <?= $tuitionStatus === 'paid' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-rose-200 bg-rose-50 text-rose-700'; ?>"><?= e($tuitionStatus); ?></div>
                                    <?php else: ?>
                                        <span class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">Chưa tạo học phí</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </article>
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
        const learningStatusSelect = document.querySelector('#registration-form select[name="learning_status"]');

        const courseMap = <?= json_encode($courseMapForJs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

        if (!courseSelect || !classSelect || !packageSelect) {
            return;
        }

        function toMoney(value) {
            const amount = Number.isFinite(value) ? value : 0;
            return new Intl.NumberFormat('vi-VN').format(Math.max(0, Math.round(amount))) + ' đ';
        }

        function syncClassOptions() {
            const selectedCourseId = Number(courseSelect.value || 0);
            const options = Array.from(classSelect.options || []);
            let visibleCount = 0;

            options.forEach(function (option) {
                if (!option.value) {
                    option.hidden = false;
                    return;
                }

                const optionCourseId = Number(option.dataset.courseId || 0);
                const show = selectedCourseId > 0 && optionCourseId === selectedCourseId;
                option.hidden = !show;
                if (show) {
                    visibleCount += 1;
                }
            });

            if (selectedCourseId <= 0) {
                classSelect.value = '';
                classSelect.disabled = true;
                if (classHint) {
                    classHint.textContent = 'Vui lòng chọn khóa học trước để lọc lớp phù hợp.';
                }
                return;
            }

            if (visibleCount <= 0) {
                classSelect.value = '';
                classSelect.disabled = true;
                if (classHint) {
                    classHint.textContent = 'Khóa học này chưa có lớp khả dụng để đăng ký.';
                }
                return;
            }

            const selectedOption = classSelect.selectedOptions && classSelect.selectedOptions[0]
                ? classSelect.selectedOptions[0]
                : null;
            if (selectedOption && selectedOption.hidden) {
                classSelect.value = '';
            }

            classSelect.disabled = false;
            if (classHint) {
                classHint.textContent = 'Chỉ hiển thị lớp thuộc khóa học đã chọn.';
            }
        }

        function syncPackageOptions() {
            const selectedCourseId = Number(courseSelect.value || 0);
            const options = Array.from(packageSelect.options || []);
            let visibleCount = 0;

            options.forEach(function (option) {
                if (!option.value || Number(option.value) === 0) {
                    option.hidden = false;
                    return;
                }

                const optionCourseId = Number(option.dataset.courseId || 0);
                const show = selectedCourseId > 0 && (optionCourseId === 0 || optionCourseId === selectedCourseId);
                option.hidden = !show;
                if (show) {
                    visibleCount += 1;
                }
            });

            if (selectedCourseId <= 0) {
                packageSelect.value = '0';
                packageSelect.disabled = true;
                if (promoHint) {
                    promoHint.textContent = 'Vui lòng chọn khóa học trước khi chọn ưu đãi.';
                }
                return;
            }

            const selectedOption = packageSelect.selectedOptions && packageSelect.selectedOptions[0]
                ? packageSelect.selectedOptions[0]
                : null;
            if (selectedOption && selectedOption.hidden) {
                packageSelect.value = '0';
            }

            packageSelect.disabled = false;
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
            const selectedLearningStatus = learningStatusSelect ? String(learningStatusSelect.value || 'official') : 'official';
            const isTrialMode = selectedLearningStatus === 'trial';
            const invoiceTotal = isTrialMode ? 0 : totalAmount;

            if (previewBaseAmount) {
                previewBaseAmount.textContent = toMoney(baseAmount);
            }
            if (previewDiscountAmount) {
                previewDiscountAmount.textContent = toMoney(discountApplied);
            }
            if (previewTotalAmount) {
                previewTotalAmount.textContent = toMoney(invoiceTotal);
            }
            if (previewInvoiceStatus) {
                if (isTrialMode) {
                    previewInvoiceStatus.textContent = 'trial | chưa tạo học phí';
                    previewInvoiceStatus.className = 'mt-1 inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-bold uppercase tracking-wide text-blue-700';
                } else {
                    previewInvoiceStatus.textContent = 'debt | đã thu 0 đ';
                    previewInvoiceStatus.className = 'mt-1 inline-flex items-center rounded-full border border-rose-200 bg-rose-50 px-2.5 py-1 text-xs font-bold uppercase tracking-wide text-rose-700';
                }
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

        if (learningStatusSelect) {
            learningStatusSelect.addEventListener('change', function () {
                updatePreview();
            });
        }

        syncClassOptions();
        syncPackageOptions();
        updatePreview();
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
