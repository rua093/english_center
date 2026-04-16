<?php
require_admin_or_staff();
require_permission('feedback.view');

$academicModel = new AcademicModel();
$lookups = $academicModel->feedbackLookups();
$students = $lookups['students'] ?? [];
$teachers = $lookups['teachers'] ?? [];
$classes = $lookups['classes'] ?? [];

$feedbackPage = max(1, (int) ($_GET['feedback_page'] ?? 1));
$feedbackPerPage = 10;
$feedbackTotal = $academicModel->countFeedbacks();
$feedbackTotalPages = max(1, (int) ceil($feedbackTotal / $feedbackPerPage));
if ($feedbackPage > $feedbackTotalPages) {
    $feedbackPage = $feedbackTotalPages;
}
$feedbacks = $academicModel->listFeedbacksPage($feedbackPage, $feedbackPerPage);

$module = 'feedbacks';
$adminTitle = 'Quản lý phản hồi';

$success = get_flash('success');
$error = get_flash('error');
?>
<div class="grid gap-4">
    <?php
    $canCreateFeedback = has_permission('feedback.create');
    $canDeleteFeedback = has_permission('feedback.delete');
    ?>

    <?php if ($success): ?>
        <div class="rounded-xl border-l-4 border-emerald-500 bg-emerald-50 p-3 text-sm text-emerald-700"><?= e($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="rounded-xl border-l-4 border-rose-500 bg-rose-50 p-3 text-sm text-rose-700"><?= e($error); ?></div>
    <?php endif; ?>

        <?php if ($canCreateFeedback): ?>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3>Thêm đánh giá</h3>
                <form class="grid gap-3 md:grid-cols-2" method="post" action="/api/feedbacks/save">
                    <?= csrf_input(); ?>
                    <input type="hidden" name="id" value="">
                    <label>
                        Học viên
                        <select name="student_id" required>
                            <option value="">Chọn học viên</option>
                            <?php foreach ($students as $s): ?>
                                <option value="<?= (int) $s['id']; ?>"><?= e((string) $s['full_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>
                        Giáo viên (tùy chọn)
                        <select name="teacher_id">
                            <option value="">Không chọn</option>
                            <?php foreach ($teachers as $t): ?>
                                <option value="<?= (int) $t['id']; ?>"><?= e((string) $t['full_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>
                        Lớp học
                        <select name="class_id" required>
                            <option value="">Chọn lớp học</option>
                            <?php foreach ($classes as $c): ?>
                                <option value="<?= (int) $c['id']; ?>"><?= e((string) $c['class_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>
                        Đánh giá (1-5)
                        <input type="number" name="rating" min="1" max="5" required>
                    </label>
                    <label class="md:col-span-2">
                        Nhận xét
                        <textarea name="comment" rows="4"></textarea>
                    </label>
                    <div class="md:col-span-2">
                        <button class="<?= ui_btn_primary_classes(); ?>" type="submit">Lưu đánh giá</button>
                    </div>
                </form>
            </article>
        <?php endif; ?>

        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3>Danh sách đánh giá</h3>
            <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
                <table class="min-w-full border-collapse text-sm" data-enable-row-detail="1">
                    <thead>
                        <tr>
                            <th>Học viên</th>
                            <th>Giáo viên</th>
                            <th>Lớp học</th>
                            <th>Đánh giá</th>
                            <th>Nhận xét</th>
                            <th>Trạng thái</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($feedbacks)): ?>
                            <tr>
                                <td colspan="7">
                                    <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">Chưa có đánh giá nào.</div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($feedbacks as $fb): ?>
                                <tr>
                                    <td><?= e((string) $fb['student_name']); ?></td>
                                    <td><?= $fb['teacher_name'] ? e((string) $fb['teacher_name']) : '-'; ?></td>
                                    <td><?= e((string) $fb['course_name']); ?></td>
                                    <td><?= (int) $fb['rating']; ?>/5</td>
                                    <td>
                                        <?php $fullComment = (string) ($fb['comment'] ?? ''); ?>
                                        <span data-full-value="<?= e($fullComment); ?>"><?= e((string) substr($fullComment, 0, 50)); ?></span>
                                    </td>
                                    <td><span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-bold capitalize border-blue-200 bg-blue-50 text-blue-700"><?= e((string) ($fb['status'] ?? 'reviewed')); ?></span></td>
                                    <td>
                                        <?php if ($canDeleteFeedback): ?>
                                            <form class="inline-block" method="post" action="/api/feedbacks/delete?id=<?= (int) $fb['id']; ?>" onsubmit="return confirm('Có chắc không?')">
                                                <?= csrf_input(); ?>
                                                <button class="<?= ui_btn_danger_classes('sm'); ?>" type="submit">Xóa</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-sm text-slate-500">Không có quyền xóa</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($feedbackTotalPages > 1): ?>
                <div class="mt-3 flex flex-wrap items-center justify-between gap-2 text-sm text-slate-600">
                    <span>Trang <?= (int) $feedbackPage; ?>/<?= (int) $feedbackTotalPages; ?> - Tổng <?= (int) $feedbackTotal; ?> đánh giá</span>
                    <div class="inline-flex items-center gap-1">
                        <?php if ($feedbackPage > 1): ?>
                            <a class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('feedbacks-manage', ['feedback_page' => $feedbackPage - 1])); ?>">Trước</a>
                        <?php else: ?>
                            <span class="rounded-lg border border-slate-200 bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-400">Trước</span>
                        <?php endif; ?>

                        <?php if ($feedbackPage < $feedbackTotalPages): ?>
                            <a class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('feedbacks-manage', ['feedback_page' => $feedbackPage + 1])); ?>">Sau</a>
                        <?php else: ?>
                            <span class="rounded-lg border border-slate-200 bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-400">Sau</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </article>
</div>



