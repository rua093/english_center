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
$lessons = [
    'lessons' => array_map(static function (array $lesson): array {
        $title = trim((string) ($lesson['actual_title'] ?? ''));
        $className = trim((string) ($lesson['class_name'] ?? ''));
        return [
            'id' => (int) ($lesson['id'] ?? 0),
            'title' => $className !== '' ? ($title . ' - ' . $className) : $title,
        ];
    }, $lessonRows),
];

$deadlineValue = !empty($editingAssignment['deadline']) ? date('Y-m-d\TH:i', strtotime((string) $editingAssignment['deadline'])) : '';

$module = 'assignments';
$adminTitle = $editingAssignment ? 'Học vụ - Sửa bài tập' : 'Học vụ - Thêm bài tập';
?>
<section class="py-10 md:py-14">
    <div class="mx-auto w-full max-w-3xl px-4 sm:px-6">
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2><?= $editingAssignment ? 'Chỉnh sửa bài tập' : 'Thêm bài tập'; ?></h2>
            <form class="grid gap-3" method="post" action="/api/assignments/save" enctype="multipart/form-data">
                <?= csrf_input(); ?>
                <input type="hidden" name="id" value="<?= (int) ($editingAssignment['id'] ?? 0); ?>">
                <label>Lesson
                    <select name="lesson_id" required>
                        <?php foreach ($lessons['lessons'] as $lesson): ?>
                            <option value="<?= (int) $lesson['id']; ?>" <?= (int) ($editingAssignment['lesson_id'] ?? 0) === (int) $lesson['id'] ? 'selected' : ''; ?>><?= e((string) $lesson['title']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Tiêu đề<input type="text" name="title" value="<?= e((string) ($editingAssignment['title'] ?? '')); ?>" required></label>
                <label>Mô tả<textarea name="description" rows="4"><?= e((string) ($editingAssignment['description'] ?? '')); ?></textarea></label>
                <label>Hạn nộp<input type="datetime-local" name="deadline" value="<?= e($deadlineValue); ?>" required></label>
                <label>File URL<input type="text" name="file_url" value="<?= e((string) ($editingAssignment['file_url'] ?? '')); ?>"></label>
                <label>Tải lên file<input type="file" name="assignment_file" accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.png"></label>
                <button class="<?= ui_btn_primary_classes(); ?>" type="submit">Lưu bài tập</button>
                <a class="<?= ui_btn_secondary_classes(); ?>" href="<?= e(page_url('assignments-academic')); ?>">Quay lại</a>
            </form>
        </article>
    </div>
</section>


