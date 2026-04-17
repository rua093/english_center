<?php
require_permission('academic.assignments.view');

$academicModel = new AcademicModel();
$assignmentPage = max(1, (int) ($_GET['assignment_page'] ?? 1));
$assignmentPerPage = ui_pagination_resolve_per_page('assignment_per_page', 10);
$assignmentTotal = $academicModel->countAssignments();
$assignmentTotalPages = max(1, (int) ceil($assignmentTotal / $assignmentPerPage));
if ($assignmentPage > $assignmentTotalPages) {
    $assignmentPage = $assignmentTotalPages;
}
$assignments = $academicModel->listAssignmentsPage($assignmentPage, $assignmentPerPage);
$assignmentPerPageOptions = ui_pagination_per_page_options();
$lessons = $academicModel->assignmentLookups();

$editingAssignment = null;
if (!empty($_GET['edit'])) {
    $editingAssignment = $academicModel->findAssignment((int) $_GET['edit']);
}

$assignmentClasses = [];
foreach ($lessons as $lesson) {
    $classId = (int) ($lesson['class_id'] ?? 0);
    if ($classId <= 0 || isset($assignmentClasses[$classId])) {
        continue;
    }

    $assignmentClasses[$classId] = [
        'id' => $classId,
        'class_name' => (string) ($lesson['class_name'] ?? ('Lớp #' . $classId)),
    ];
}

$selectedAssignmentClassId = 0;
$selectedAssignmentLessonId = 0;
if (is_array($editingAssignment)) {
    $selectedAssignmentLessonId = (int) ($editingAssignment['lesson_id'] ?? 0);
    foreach ($lessons as $lesson) {
        if ((int) ($lesson['id'] ?? 0) !== $selectedAssignmentLessonId) {
            continue;
        }

        $selectedAssignmentClassId = (int) ($lesson['class_id'] ?? 0);
        break;
    }
} else {
    $requestedClassId = max(0, (int) ($_GET['class_id'] ?? 0));
    $requestedLessonId = max(0, (int) ($_GET['lesson_id'] ?? 0));

    if ($requestedClassId > 0 && isset($assignmentClasses[$requestedClassId])) {
        $selectedAssignmentClassId = $requestedClassId;
    }

    if ($requestedLessonId > 0) {
        foreach ($lessons as $lesson) {
            if ((int) ($lesson['id'] ?? 0) !== $requestedLessonId) {
                continue;
            }

            $lessonClassId = (int) ($lesson['class_id'] ?? 0);
            if ($selectedAssignmentClassId > 0 && $lessonClassId !== $selectedAssignmentClassId) {
                break;
            }

            $selectedAssignmentClassId = $lessonClassId;
            $selectedAssignmentLessonId = $requestedLessonId;
            break;
        }
    }
}

$module = 'assignments';
$adminTitle = 'Học vụ - Bài tập';

$success = get_flash('success');
$error = get_flash('error');

$canCreateClass = has_permission('academic.classes.create');
$canUpdateClass = has_permission('academic.classes.update');

$canCreateSchedule = has_permission('academic.schedules.create');
$canUpdateSchedule = has_permission('academic.schedules.update');

$canCreateAssignment = has_permission('academic.assignments.create');
$canUpdateAssignment = has_permission('academic.assignments.update');
$canDeleteAssignment = has_permission('academic.assignments.delete');

$canCreateMaterial = has_permission('materials.create');
$canUpdateMaterial = has_permission('materials.update');
?>
<div class="grid gap-4">
        <?php if ($success): ?>
            <div class="rounded-xl border-l-4 p-3 text-sm border-emerald-500 bg-emerald-50 text-emerald-700"><?= e($success); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="rounded-xl border-l-4 p-3 text-sm border-rose-500 bg-rose-50 text-rose-700"><?= e($error); ?></div>
        <?php endif; ?>

        <?php if ($canCreateAssignment || $canUpdateAssignment): ?>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3><?= $editingAssignment ? 'Sửa bài tập' : 'Thêm bài tập'; ?></h3>
            <form class="grid gap-3" method="post" action="/api/assignments/save" enctype="multipart/form-data">
                <?= csrf_input(); ?>
                <input type="hidden" name="id" value="<?= (int) ($editingAssignment['id'] ?? 0); ?>">
                <input type="hidden" name="existing_file_url" value="<?= e((string) ($editingAssignment['file_url'] ?? '')); ?>">
                <label>
                    Lớp học
                    <select id="assignment-class-select" name="class_id" required>
                        <option value="">-- Chọn lớp --</option>
                        <?php foreach ($assignmentClasses as $assignmentClass): ?>
                            <option value="<?= (int) $assignmentClass['id']; ?>" <?= $selectedAssignmentClassId === (int) $assignmentClass['id'] ? 'selected' : ''; ?>><?= e((string) $assignmentClass['class_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    Buổi học
                    <select id="assignment-lesson-select" name="lesson_id" required>
                        <option value="">-- Chọn buổi học --</option>
                        <?php foreach ($lessons as $lesson): ?>
                            <option data-class-id="<?= (int) ($lesson['class_id'] ?? 0); ?>" value="<?= (int) $lesson['id']; ?>" <?= $selectedAssignmentLessonId === (int) $lesson['id'] ? 'selected' : ''; ?>><?= e((string) $lesson['actual_title']); ?> - <?= e((string) $lesson['class_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    Tiêu đề
                    <input type="text" name="title" required value="<?= e((string) ($editingAssignment['title'] ?? '')); ?>">
                </label>
                <label>
                    Mô tả
                    <textarea name="description" rows="4"><?= e((string) ($editingAssignment['description'] ?? '')); ?></textarea>
                </label>
                <label>
                    Hạn nộp
                    <input type="datetime-local" name="deadline" required value="<?= !empty($editingAssignment['deadline']) ? e(date('Y-m-d\TH:i', strtotime((string) $editingAssignment['deadline']))) : ''; ?>">
                </label>
                <label>
                    Tải lên file đính kèm
                    <input type="file" name="assignment_file" accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.png">
                </label>
                <?php if (!empty($editingAssignment['file_url'])): ?>
                    <p class="text-xs text-slate-500">File hiện tại: <a class="font-semibold text-blue-700 hover:underline" href="<?= e((string) $editingAssignment['file_url']); ?>" target="_blank" rel="noopener noreferrer">Mở file</a>. Chọn file mới để thay thế.</p>
                <?php endif; ?>
                <button class="<?= ui_btn_primary_classes(); ?>" type="submit">Lưu bài tập</button>
            </form>
        </article>
        <?php endif; ?>

        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3>Danh sách bài tập</h3>
            <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
                <table class="min-w-full border-collapse text-sm">
                <thead>
                    <tr><th>Bài tập</th><th>Lớp học</th><th>Hạn nộp</th><th>Hành động</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($assignments)): ?>
                        <tr><td colspan="4"><div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">Chưa có bài tập nào.</div></td></tr>
                    <?php else: ?>
                    <?php foreach ($assignments as $assignment): ?>
                        <tr>
                            <td><?= e((string) $assignment['title']); ?></td>
                            <td><?= e((string) $assignment['class_name']); ?></td>
                            <td><?= e((string) $assignment['deadline']); ?></td>
                            <td>
                                <span class="inline-flex flex-wrap items-center gap-2">
                                    <?php if ($canUpdateAssignment): ?>
                                        <a
                                            href="<?= e(page_url('assignments-academic-edit', ['id' => (int) $assignment['id'], 'assignment_page' => $assignmentPage, 'assignment_per_page' => $assignmentPerPage])); ?>"
                                            class="admin-action-icon-btn"
                                            data-action-kind="edit"
                                            data-skip-action-icon="1"
                                            title="Sửa"
                                            aria-label="Sửa"
                                        >
                                            <span class="admin-action-icon-label">Sửa</span>
                                            <span class="admin-action-icon-glyph" aria-hidden="true">
                                                <svg viewBox="0 0 24 24"><path d="M12 20h9"></path><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"></path></svg>
                                            </span>
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($canDeleteAssignment): ?>
                                        <form class="inline-block" method="post" action="/api/assignments/delete?id=<?= (int) $assignment['id']; ?>">
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
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                </table>
                <?php if ($assignmentTotal > 0): ?>
                    <div class="border-t border-slate-200 bg-slate-50/80 px-3 py-2">
                        <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-slate-600">
                            <span class="font-medium">Trang <?= (int) $assignmentPage; ?>/<?= (int) $assignmentTotalPages; ?> - Tổng <?= (int) $assignmentTotal; ?> bài tập</span>
                            <div class="inline-flex items-center gap-1.5">
                                <form class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2 py-1" method="get" action="<?= e(page_url('assignments-academic')); ?>">
                                    <input type="hidden" name="page" value="assignments-academic">
                                    <label class="text-[11px] font-semibold text-slate-500" for="assignment-per-page">Số dòng</label>
                                    <select id="assignment-per-page" name="assignment_per_page" class="h-7 rounded-md border border-slate-200 bg-white px-2 text-xs font-semibold text-slate-700" onchange="this.form.submit()">
                                        <?php foreach ($assignmentPerPageOptions as $option): ?>
                                            <option value="<?= (int) $option; ?>" <?= $assignmentPerPage === (int) $option ? 'selected' : ''; ?>><?= (int) $option; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                                <?php if ($assignmentPage > 1): ?>
                                    <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('assignments-academic', ['assignment_page' => $assignmentPage - 1, 'assignment_per_page' => $assignmentPerPage])); ?>">Trước</a>
                                <?php else: ?>
                                    <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400">Trước</span>
                                <?php endif; ?>

                                <?php if ($assignmentPage < $assignmentTotalPages): ?>
                                    <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('assignments-academic', ['assignment_page' => $assignmentPage + 1, 'assignment_per_page' => $assignmentPerPage])); ?>">Sau</a>
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

<script>
(function () {
    const classSelect = document.getElementById('assignment-class-select');
    const lessonSelect = document.getElementById('assignment-lesson-select');
    if (!classSelect || !lessonSelect) {
        return;
    }

    const lessonOptions = Array.from(lessonSelect.querySelectorAll('option[data-class-id]')).map(function (option) {
        return {
            value: String(option.value || ''),
            classId: String(option.getAttribute('data-class-id') || ''),
            label: String(option.textContent || ''),
        };
    });

    const initialLessonValue = String(lessonSelect.value || '');

    function renderLessonOptions(preferredValue) {
        const selectedClassId = String(classSelect.value || '');
        lessonSelect.innerHTML = '';

        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = '-- Chọn buổi học --';
        lessonSelect.appendChild(placeholder);

        if (selectedClassId === '') {
            lessonSelect.value = '';
            lessonSelect.disabled = true;
            return;
        }

        const matchingLessons = lessonOptions.filter(function (lesson) {
            return lesson.classId === selectedClassId;
        });

        matchingLessons.forEach(function (lesson) {
            const option = document.createElement('option');
            option.value = lesson.value;
            option.textContent = lesson.label;
            lessonSelect.appendChild(option);
        });

        lessonSelect.disabled = matchingLessons.length === 0;

        const safePreferredValue = String(preferredValue || '');
        if (safePreferredValue !== '' && matchingLessons.some(function (lesson) { return lesson.value === safePreferredValue; })) {
            lessonSelect.value = safePreferredValue;
        }
    }

    classSelect.addEventListener('change', function () {
        renderLessonOptions('');
    });

    renderLessonOptions(initialLessonValue);
})();
</script>




