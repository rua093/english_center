<?php
require_permission('academic.roadmaps.view');

$academicModel = new AcademicModel();
$courseOptions = $academicModel->classLookups()['courses'] ?? [];
$selectedCourseId = max(0, (int) ($_GET['course_id'] ?? 0));
$roadmapPage = max(1, (int) ($_GET['roadmap_page'] ?? 1));
$roadmapPerPage = ui_pagination_resolve_per_page('roadmap_per_page', 10);
$roadmapPerPageOptions = ui_pagination_per_page_options();

$editingRoadmap = null;
if (!empty($_GET['edit'])) {
    $editingRoadmap = $academicModel->findRoadmap((int) $_GET['edit']);
    if (is_array($editingRoadmap) && $selectedCourseId <= 0) {
        $selectedCourseId = (int) ($editingRoadmap['course_id'] ?? 0);
    }
}

$courseMap = [];
foreach ($courseOptions as $courseRow) {
    $courseId = (int) ($courseRow['id'] ?? 0);
    if ($courseId > 0) {
        $courseMap[$courseId] = $courseRow;
    }
}

if (is_array($editingRoadmap) && $selectedCourseId > 0 && (int) ($editingRoadmap['course_id'] ?? 0) !== $selectedCourseId) {
    $editingRoadmap = null;
}

$selectedCourse = $selectedCourseId > 0 ? ($courseMap[$selectedCourseId] ?? null) : null;
$roadmapTotal = $selectedCourseId > 0 ? $academicModel->countRoadmapsByCourse($selectedCourseId) : 0;
$roadmapTotalPages = max(1, (int) ceil($roadmapTotal / $roadmapPerPage));
if ($roadmapPage > $roadmapTotalPages) {
    $roadmapPage = $roadmapTotalPages;
}
$roadmaps = $selectedCourseId > 0 ? $academicModel->listRoadmapsByCoursePage($selectedCourseId, $roadmapPage, $roadmapPerPage) : [];

$module = 'roadmaps';
$adminTitle = 'Học vụ - Lộ trình khóa học';

$success = get_flash('success');
$error = get_flash('error');

$canCreateRoadmap = has_permission('academic.roadmaps.create');
$canUpdateRoadmap = has_permission('academic.roadmaps.update');
$canDeleteRoadmap = has_permission('academic.roadmaps.delete');

$selectedOrder = max(1, (int) ($editingRoadmap['order'] ?? 1));
$selectedTopicTitle = trim((string) ($editingRoadmap['topic_title'] ?? ''));
$selectedOutlineContent = trim((string) ($editingRoadmap['outline_content'] ?? ''));
?>
<div class="grid gap-4">
    <?php if ($success): ?>
        <div class="rounded-xl border-l-4 border-emerald-500 bg-emerald-50 p-3 text-sm text-emerald-700"><?= e($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="rounded-xl border-l-4 border-rose-500 bg-rose-50 p-3 text-sm text-rose-700"><?= e($error); ?></div>
    <?php endif; ?>

    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3>Bộ lọc lộ trình theo khóa học</h3>
        <form class="grid gap-3 md:grid-cols-3" method="get" action="<?= e(page_url('roadmaps-academic')); ?>">
            <input type="hidden" name="page" value="roadmaps-academic">
            <label>
                Khóa học
                <select name="course_id">
                    <option value="">-- Chọn khóa học --</option>
                    <?php foreach ($courseOptions as $course): ?>
                        <?php $courseId = (int) ($course['id'] ?? 0); ?>
                        <option value="<?= $courseId; ?>" <?= $selectedCourseId === $courseId ? 'selected' : ''; ?>><?= e((string) ($course['course_name'] ?? ('Khóa #' . $courseId))); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <div class="flex items-end gap-2">
                <button class="<?= ui_btn_primary_classes(); ?>" type="submit">Lọc dữ liệu</button>
                <a class="<?= ui_btn_secondary_classes(); ?>" href="<?= e(page_url('roadmaps-academic')); ?>">Đặt lại</a>
            </div>

            <div class="flex items-end justify-start md:justify-end">
                <a class="<?= ui_btn_secondary_classes(); ?>" href="<?= e(page_url('courses-academic')); ?>">Danh mục khóa học</a>
            </div>
        </form>
        <?php if (is_array($selectedCourse)): ?>
            <p class="mt-3 text-sm text-slate-600">Đang quản lý lộ trình cho khóa <strong><?= e((string) ($selectedCourse['course_name'] ?? '')); ?></strong>.</p>
        <?php endif; ?>
    </article>

    <?php if (!is_array($selectedCourse)): ?>
        <article class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500 shadow-sm">
            Chọn một khóa học để xem và quản lý lộ trình theo từng chủ đề.
        </article>
    <?php else: ?>
        <?php if ($canCreateRoadmap || $canUpdateRoadmap): ?>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3><?= $editingRoadmap ? 'Sửa chủ đề lộ trình' : 'Thêm chủ đề lộ trình'; ?></h3>
                <form class="grid gap-3 md:grid-cols-2" method="post" action="/api/roadmaps/save">
                    <?= csrf_input(); ?>
                    <input type="hidden" name="id" value="<?= (int) ($editingRoadmap['id'] ?? 0); ?>">
                    <input type="hidden" name="course_id" value="<?= (int) $selectedCourseId; ?>">
                    <input type="hidden" name="roadmap_page" value="<?= (int) $roadmapPage; ?>">
                    <input type="hidden" name="roadmap_per_page" value="<?= (int) $roadmapPerPage; ?>">

                    <label>
                        Thứ tự lộ trình
                        <input type="number" min="1" step="1" name="order" required value="<?= e((string) $selectedOrder); ?>">
                    </label>

                    <label>
                        Chủ đề
                        <input type="text" name="topic_title" required value="<?= e($selectedTopicTitle); ?>">
                    </label>

                    <label class="md:col-span-2">
                        Nội dung khung
                        <textarea name="outline_content" rows="4" placeholder="Mô tả mục tiêu, kiến thức và kỹ năng cần đạt của chủ đề này."><?= e($selectedOutlineContent); ?></textarea>
                    </label>

                    <div class="md:col-span-2 inline-flex flex-wrap items-center gap-2">
                        <button class="<?= ui_btn_primary_classes(); ?>" type="submit"><?= $editingRoadmap ? 'Cập nhật lộ trình' : 'Tạo lộ trình'; ?></button>
                        <?php if ($editingRoadmap): ?>
                            <a class="<?= ui_btn_secondary_classes(); ?>" href="<?= e(page_url('roadmaps-academic', ['course_id' => $selectedCourseId, 'roadmap_page' => $roadmapPage, 'roadmap_per_page' => $roadmapPerPage])); ?>">Hủy chỉnh sửa</a>
                        <?php endif; ?>
                    </div>
                </form>
            </article>
        <?php endif; ?>

        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3>Danh sách lộ trình</h3>
            <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
                <table class="min-w-full border-collapse text-sm">
                    <thead>
                        <tr>
                            <th>Thứ tự</th>
                            <th>Chủ đề</th>
                            <th>Nội dung khung</th>
                            <th>Đã dùng</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($roadmaps)): ?>
                            <tr>
                                <td colspan="5">
                                    <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">Chưa có lộ trình nào cho khóa học này.</div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($roadmaps as $roadmap): ?>
                                <?php
                                $roadmapId = (int) ($roadmap['id'] ?? 0);
                                $lessonCount = max(0, (int) ($roadmap['lesson_count'] ?? 0));
                                $outlineText = trim((string) ($roadmap['outline_content'] ?? ''));
                                ?>
                                <tr>
                                    <td>#<?= (int) ($roadmap['order'] ?? 0); ?></td>
                                    <td>
                                        <div class="font-semibold text-slate-800"><?= e((string) ($roadmap['topic_title'] ?? ('Chủ đề #' . $roadmapId))); ?></div>
                                    </td>
                                    <td>
                                        <?php if ($outlineText === ''): ?>
                                            <span class="text-slate-400">-</span>
                                        <?php else: ?>
                                            <?= e($outlineText); ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700"><?= $lessonCount; ?> buổi</span>
                                    </td>
                                    <td>
                                        <span class="inline-flex flex-wrap items-center gap-2">
                                            <?php if ($canUpdateRoadmap): ?>
                                                <a
                                                    href="<?= e(page_url('roadmaps-academic', ['course_id' => $selectedCourseId, 'edit' => $roadmapId, 'roadmap_page' => $roadmapPage, 'roadmap_per_page' => $roadmapPerPage])); ?>"
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

                                            <?php if ($canDeleteRoadmap): ?>
                                                <form class="inline-block" method="post" action="/api/roadmaps/delete?id=<?= $roadmapId; ?>&course_id=<?= (int) $selectedCourseId; ?>&roadmap_page=<?= (int) $roadmapPage; ?>&roadmap_per_page=<?= (int) $roadmapPerPage; ?>" onsubmit="return confirm('Bạn có chắc muốn xóa chủ đề lộ trình này không?');">
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

                <?php if ($roadmapTotal > 0): ?>
                    <div class="border-t border-slate-200 bg-slate-50/80 px-3 py-2">
                        <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-slate-600">
                            <span class="font-medium">Trang <?= (int) $roadmapPage; ?>/<?= (int) $roadmapTotalPages; ?> - Tổng <?= (int) $roadmapTotal; ?> chủ đề</span>
                            <div class="inline-flex items-center gap-1.5">
                                <form class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2 py-1" method="get" action="<?= e(page_url('roadmaps-academic')); ?>">
                                    <input type="hidden" name="page" value="roadmaps-academic">
                                    <input type="hidden" name="course_id" value="<?= (int) $selectedCourseId; ?>">
                                    <label class="text-[11px] font-semibold text-slate-500" for="roadmap-per-page">Số dòng</label>
                                    <select id="roadmap-per-page" name="roadmap_per_page" class="h-7 rounded-md border border-slate-200 bg-white px-2 text-xs font-semibold text-slate-700" onchange="this.form.submit()">
                                        <?php foreach ($roadmapPerPageOptions as $option): ?>
                                            <option value="<?= (int) $option; ?>" <?= $roadmapPerPage === (int) $option ? 'selected' : ''; ?>><?= (int) $option; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>

                                <?php if ($roadmapPage > 1): ?>
                                    <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('roadmaps-academic', ['course_id' => $selectedCourseId, 'roadmap_page' => $roadmapPage - 1, 'roadmap_per_page' => $roadmapPerPage])); ?>">Trước</a>
                                <?php else: ?>
                                    <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400">Trước</span>
                                <?php endif; ?>

                                <?php if ($roadmapPage < $roadmapTotalPages): ?>
                                    <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('roadmaps-academic', ['course_id' => $selectedCourseId, 'roadmap_page' => $roadmapPage + 1, 'roadmap_per_page' => $roadmapPerPage])); ?>">Sau</a>
                                <?php else: ?>
                                    <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400">Sau</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </article>
    <?php endif; ?>
</div>
