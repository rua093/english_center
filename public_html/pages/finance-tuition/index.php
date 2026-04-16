<?php
require_admin_or_staff();
require_permission('finance.tuition.view');

$academicModel = new AcademicModel();
$tuitionPage = max(1, (int) ($_GET['tuition_page'] ?? 1));
$tuitionPerPage = 10;
$tuitionTotal = $academicModel->countTuitionFees();
$tuitionTotalPages = max(1, (int) ceil($tuitionTotal / $tuitionPerPage));
if ($tuitionPage > $tuitionTotalPages) {
    $tuitionPage = $tuitionTotalPages;
}
$tuitionFees = $academicModel->listTuitionFeesPage($tuitionPage, $tuitionPerPage);
$tuitionOptions = $academicModel->listTuitionFeesPage(1, 200);

$lookups = $academicModel->scheduleLookups();
$classes = $lookups['classes'] ?? [];
$students = $academicModel->studentLookups();
$classStudentRows = $academicModel->tuitionStudentClassLookups();

$studentNameMap = [];
foreach ($students as $student) {
    $studentId = (int) ($student['id'] ?? 0);
    if ($studentId > 0) {
        $studentNameMap[$studentId] = (string) ($student['full_name'] ?? ('Học viên #' . $studentId));
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
        'name' => (string) ($row['student_name'] ?? ($studentNameMap[$studentId] ?? ('Học viên #' . $studentId))),
    ];
}

$editingTuition = null;
if (!empty($_GET['edit'])) {
    $editingTuition = $academicModel->findTuitionFeeForEdit((int) $_GET['edit']);
}

$editingClassId = (int) ($editingTuition['class_id'] ?? 0);
$editingStudentId = (int) ($editingTuition['student_id'] ?? 0);
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

$canCreateTuition = $isAdmin || has_any_permission(['finance.tuition.manage', 'finance.tuition.create']);
$canUpdateTuition = $isAdmin || has_any_permission(['finance.tuition.manage', 'finance.tuition.update']);
$canDeleteTuition = $isAdmin || has_any_permission(['finance.tuition.manage', 'finance.tuition.delete']);

$success = get_flash('success');
$error = get_flash('error');
?>
<div class="grid gap-4">
    <?php if ($success): ?>
        <div class="rounded-xl border-l-4 border-emerald-500 bg-emerald-50 p-3 text-sm text-emerald-700"><?= e($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="rounded-xl border-l-4 border-rose-500 bg-rose-50 p-3 text-sm text-rose-700"><?= e($error); ?></div>
    <?php endif; ?>

    <?php if ($canCreateTuition || $canUpdateTuition): ?>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3><?= $editingTuition ? 'Sửa hóa đơn học phí' : 'Tạo hóa đơn học phí'; ?></h3>
            <form class="grid gap-3 md:grid-cols-2" method="post" action="/api/tuitions/save">
                <?= csrf_input(); ?>
                <input type="hidden" name="id" value="<?= (int) ($editingTuition['id'] ?? 0); ?>">
                <label>
                    Học viên
                    <select id="tuition-student-select" name="student_id" data-selected="<?= (int) $editingStudentId; ?>" <?= empty($studentOptionsForSelectedClass) ? 'disabled' : ''; ?> required>
                        <option value=""><?= $editingClassId > 0 ? '-- Chọn học viên của lớp --' : '-- Chọn lớp trước --'; ?></option>
                        <?php foreach ($studentOptionsForSelectedClass as $student): ?>
                            <option value="<?= (int) ($student['id'] ?? 0); ?>" <?= $editingStudentId === (int) ($student['id'] ?? 0) ? 'selected' : ''; ?>>
                                <?= e((string) ($student['name'] ?? '')); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small id="tuition-student-hint" class="mt-1 block text-xs text-slate-500">Chỉ hiển thị học viên thuộc lớp đã chọn.</small>
                </label>
                <label>
                    Lớp học
                    <select id="tuition-class-select" name="class_id" required>
                        <option value="">-- Chọn lớp học --</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?= (int) $class['id']; ?>" <?= (int) ($editingTuition['class_id'] ?? 0) === (int) $class['id'] ? 'selected' : ''; ?>>
                                <?= e((string) $class['class_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    Tổng tiền
                    <input type="number" step="1000" min="0" name="total_amount" required value="<?= e((string) ($editingTuition['total_amount'] ?? '0')); ?>">
                </label>
                <label>
                    Đã thu
                    <input type="number" step="1000" min="0" name="amount_paid" required value="<?= e((string) ($editingTuition['amount_paid'] ?? '0')); ?>">
                </label>
                <label>
                    Chế độ đóng
                    <select name="payment_plan">
                        <option value="full" <?= (($editingTuition['payment_plan'] ?? 'full') === 'full') ? 'selected' : ''; ?>>full</option>
                        <option value="monthly" <?= (($editingTuition['payment_plan'] ?? '') === 'monthly') ? 'selected' : ''; ?>>monthly</option>
                    </select>
                </label>
                <label>
                    Trạng thái
                    <select name="status">
                        <option value="debt" <?= (($editingTuition['status'] ?? '') === 'debt') ? 'selected' : ''; ?>>debt</option>
                        <option value="paid" <?= (($editingTuition['status'] ?? '') === 'paid') ? 'selected' : ''; ?>>paid</option>
                    </select>
                </label>
                <p class="md:col-span-2 text-xs text-slate-500">Trạng thái sẽ tự động là <strong>debt</strong> nếu số đã thu chưa đủ và tự chuyển <strong>paid</strong> khi thu đủ.</p>
                <div class="md:col-span-2 inline-flex flex-wrap items-center gap-2">
                    <button class="<?= ui_btn_primary_classes(); ?>" type="submit"><?= $editingTuition ? 'Cập nhật hóa đơn' : 'Tạo hóa đơn'; ?></button>
                    <?php if ($editingTuition): ?>
                        <a class="<?= ui_btn_secondary_classes(); ?>" href="<?= e(page_url('tuition-finance')); ?>">Hủy chỉnh sửa</a>
                    <?php endif; ?>
                </div>
            </form>
        </article>
    <?php endif; ?>

    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3>Danh sách học phí</h3>
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full border-collapse text-sm">
                <thead>
                    <tr>
                        <th>Học viên</th>
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
                            <td colspan="8">
                                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">Chưa có dữ liệu học phí.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($tuitionFees as $fee): ?>
                            <tr>
                                <td><?= e((string) $fee['student_name']); ?></td>
                                <td><?= e((string) $fee['course_name']); ?></td>
                                <td><?= format_money((float) $fee['total_amount']); ?></td>
                                <td><?= format_money((float) $fee['amount_paid']); ?></td>
                                <td><?= format_money((float) ($fee['total_amount'] - $fee['amount_paid'])); ?></td>
                                <td><span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-bold capitalize is-<?= e((string) $fee['status']); ?>"><?= e((string) $fee['status']); ?></span></td>
                                <td><?= e((string) ($fee['payment_plan'] ?? 'full')); ?></td>
                                <td>
                                    <div class="inline-flex flex-wrap items-center gap-2">
                                        <?php if ($canUpdateTuition): ?>
                                            <a class="text-sm font-semibold text-blue-700 hover:underline" href="<?= e(page_url('tuition-finance', ['edit' => (int) $fee['id'], 'tuition_page' => $tuitionPage])); ?>">Sửa</a>
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
        </div>

        <?php if ($tuitionTotalPages > 1): ?>
            <div class="mt-3 flex flex-wrap items-center justify-between gap-2 text-sm text-slate-600">
                <span>Trang <?= (int) $tuitionPage; ?>/<?= (int) $tuitionTotalPages; ?> - Tổng <?= (int) $tuitionTotal; ?> hóa đơn</span>
                <div class="inline-flex items-center gap-1">
                    <?php if ($tuitionPage > 1): ?>
                        <a class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('tuition-finance', ['tuition_page' => $tuitionPage - 1])); ?>">Trước</a>
                    <?php else: ?>
                        <span class="rounded-lg border border-slate-200 bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-400">Trước</span>
                    <?php endif; ?>

                    <?php if ($tuitionPage < $tuitionTotalPages): ?>
                        <a class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('tuition-finance', ['tuition_page' => $tuitionPage + 1])); ?>">Sau</a>
                    <?php else: ?>
                        <span class="rounded-lg border border-slate-200 bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-400">Sau</span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
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
                                #<?= (int) $fee['id']; ?> - <?= e((string) $fee['student_name']); ?> - <?= e((string) $fee['course_name']); ?>
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
        const classSelect = document.getElementById('tuition-class-select');
        const studentSelect = document.getElementById('tuition-student-select');
        const studentHint = document.getElementById('tuition-student-hint');
        const classStudentMap = <?= json_encode($classStudentMap, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

        if (!classSelect || !studentSelect) {
            return;
        }

        function renderStudents(preferredStudentId) {
            const classId = String(Number(classSelect.value || 0));
            const students = Array.isArray(classStudentMap[classId]) ? classStudentMap[classId] : [];

            studentSelect.innerHTML = '';
            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = Number(classId) > 0 ? '-- Chọn học viên của lớp --' : '-- Chọn lớp trước --';
            studentSelect.appendChild(placeholder);

            let hasPreferred = false;
            students.forEach(function (student) {
                const option = document.createElement('option');
                option.value = String(student.id || '');
                option.textContent = String(student.name || '');
                if (Number(option.value) === Number(preferredStudentId || 0)) {
                    option.selected = true;
                    hasPreferred = true;
                }
                studentSelect.appendChild(option);
            });

            if (students.length === 0) {
                studentSelect.value = '';
                studentSelect.disabled = true;
                if (studentHint) {
                    studentHint.textContent = Number(classId) > 0
                        ? 'Lớp này chưa có học viên. Không thể tạo học phí thủ công.'
                        : 'Vui lòng chọn lớp trước để chọn học viên.';
                }
                return;
            }

            studentSelect.disabled = false;
            if (!hasPreferred) {
                studentSelect.value = '';
            }
            if (studentHint) {
                studentHint.textContent = 'Chỉ hiển thị học viên thuộc lớp đã chọn.';
            }
        }

        classSelect.addEventListener('change', function () {
            renderStudents(0);
        });

        renderStudents(Number(studentSelect.dataset.selected || 0));
    })();
</script>



