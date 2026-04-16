<?php
require_permission('academic.submissions.view');

$academicModel = new AcademicModel();
$submissions = $academicModel->listSubmissionsForGrading();

$module = 'submissions';
$adminTitle = 'Học vụ - Bài nộp';

$success = get_flash('success');
$error = get_flash('error');

$canCreateClass = has_permission('academic.classes.create');
$canUpdateClass = has_permission('academic.classes.update');

$canCreateSchedule = has_permission('academic.schedules.create');
$canUpdateSchedule = has_permission('academic.schedules.update');

$canCreateAssignment = has_permission('academic.assignments.create');
$canUpdateAssignment = has_permission('academic.assignments.update');

$canCreateMaterial = has_permission('materials.create');
$canUpdateMaterial = has_permission('materials.update');

$canGradeSubmission = has_permission('academic.submissions.grade');
?>
<div class="grid gap-4">
        <?php if ($success): ?>
            <div class="rounded-xl border-l-4 p-3 text-sm border-emerald-500 bg-emerald-50 text-emerald-700"><?= e($success); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="rounded-xl border-l-4 p-3 text-sm border-rose-500 bg-rose-50 text-rose-700"><?= e($error); ?></div>
        <?php endif; ?>

        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full border-collapse text-sm">
                <thead>
                    <tr><th>Học viên</th><th>Bài tập</th><th>File</th><th>Điểm</th><th>Nhận xét</th><th>Hành động</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($submissions)): ?>
                        <tr><td colspan="6"><div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">Chưa có bài nộp nào.</div></td></tr>
                    <?php else: ?>
                    <?php foreach ($submissions as $submission): ?>
                        <tr>
                            <td><?= e((string) $submission['student_name']); ?></td>
                            <td><?= e((string) $submission['assignment_title']); ?></td>
                            <td><?= e((string) ($submission['file_url'] ?? '')); ?></td>
                            <td colspan="3">
                                <?php if ($canGradeSubmission): ?>
                                    <form method="post" action="/api/submissions/grade" class="grid gap-2">
                                        <?= csrf_input(); ?>
                                        <input type="hidden" name="submission_id" value="<?= (int) $submission['id']; ?>">
                                        <div class="grid gap-2 md:grid-cols-[120px_1fr_auto]">
                                            <input type="number" step="0.1" min="0" max="10" name="score" value="<?= e((string) ($submission['score'] ?? '')); ?>" placeholder="Điểm">
                                            <textarea name="teacher_comment" rows="2" placeholder="Nhận xét"><?= e((string) ($submission['teacher_comment'] ?? '')); ?></textarea>
                                            <button
                                                class="<?= ui_btn_primary_classes('sm'); ?> admin-action-icon-btn"
                                                data-action-kind="save"
                                                data-skip-action-icon="1"
                                                type="submit"
                                                title="Lưu"
                                                aria-label="Lưu"
                                            >
                                                <span class="admin-action-icon-label">Lưu</span>
                                                <span class="admin-action-icon-glyph" aria-hidden="true">
                                                    <svg viewBox="0 0 24 24"><path d="m20 7-11 11-5-5"></path></svg>
                                                </span>
                                            </button>
                                        </div>
                                    </form>
                                <?php else: ?>
                                    <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-bold capitalize border-amber-200 bg-amber-50 text-amber-700">Chỉ có quyền xem</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>




