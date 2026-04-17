<?php
$assignmentId = (int) ($_GET['id'] ?? 0);
if ($assignmentId > 0) {
    require_permission('academic.assignments.update');
} else {
    require_permission('academic.assignments.create');
}

$academicModel = new AcademicModel();
$editingAssignment = $assignmentId > 0 ? $academicModel->findAssignment($assignmentId) : null;
$lessonRows = $academicModel->assignmentLookups();
$lessons = array_map(static function (array $lesson): array {
    $title = trim((string) ($lesson['actual_title'] ?? ''));
    $className = trim((string) ($lesson['class_name'] ?? ''));
    return [
        'id' => (int) ($lesson['id'] ?? 0),
        'class_id' => (int) ($lesson['class_id'] ?? 0),
        'class_name' => $className,
        'title' => $className !== '' ? ($title . ' - ' . $className) : $title,
    ];
}, $lessonRows);

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

$deadlineValue = !empty($editingAssignment['deadline']) ? date('Y-m-d\TH:i', strtotime((string) $editingAssignment['deadline'])) : '';

$module = 'assignments';
$adminTitle = $editingAssignment ? 'Học vụ - Sửa bài tập' : 'Học vụ - Thêm bài tập';
?>
<div class="grid gap-4">
    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2><?= $editingAssignment ? 'Chỉnh sửa bài tập' : 'Thêm bài tập'; ?></h2>
        <form class="grid gap-3" method="post" action="/api/assignments/save" enctype="multipart/form-data">
                <?= csrf_input(); ?>
                <input type="hidden" name="id" value="<?= (int) ($editingAssignment['id'] ?? 0); ?>">
                <input type="hidden" name="existing_file_url" value="<?= e((string) ($editingAssignment['file_url'] ?? '')); ?>">
                <label>Lớp học
                    <select id="assignment-class-select" name="class_id" required>
                        <option value="">-- Chọn lớp --</option>
                        <?php foreach ($assignmentClasses as $assignmentClass): ?>
                            <option value="<?= (int) $assignmentClass['id']; ?>" <?= $selectedAssignmentClassId === (int) $assignmentClass['id'] ? 'selected' : ''; ?>><?= e((string) $assignmentClass['class_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Buổi học
                    <select id="assignment-lesson-select" name="lesson_id" required>
                        <option value="">-- Chọn buổi học --</option>
                        <?php foreach ($lessons as $lesson): ?>
                            <option data-class-id="<?= (int) ($lesson['class_id'] ?? 0); ?>" value="<?= (int) $lesson['id']; ?>" <?= $selectedAssignmentLessonId === (int) $lesson['id'] ? 'selected' : ''; ?>><?= e((string) $lesson['title']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Tiêu đề<input type="text" name="title" value="<?= e((string) ($editingAssignment['title'] ?? '')); ?>" required></label>
                <label>Mô tả<textarea name="description" rows="4"><?= e((string) ($editingAssignment['description'] ?? '')); ?></textarea></label>
                <label>Hạn nộp<input type="datetime-local" name="deadline" value="<?= e($deadlineValue); ?>" required></label>
                <label>Tải lên file đính kèm<input type="file" name="assignment_file" accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.png"></label>
                <?php if (!empty($editingAssignment['file_url'])): ?>
                    <p class="text-xs text-slate-500">File hiện tại: <a class="font-semibold text-blue-700 hover:underline" href="<?= e((string) $editingAssignment['file_url']); ?>" target="_blank" rel="noopener noreferrer">Mở file</a>. Chọn file mới để thay thế.</p>
                <?php endif; ?>
            <button class="<?= ui_btn_primary_classes(); ?>" type="submit">Lưu bài tập</button>
            <a class="<?= ui_btn_secondary_classes(); ?>" href="<?= e(page_url('assignments-academic')); ?>">Quay lại</a>
        </form>
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


