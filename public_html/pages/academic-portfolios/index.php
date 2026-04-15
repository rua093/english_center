<?php
require_login();

$academicModel = new AcademicModel();
$portfolios = $academicModel->listPortfolios();
$students = $academicModel->studentLookups();
$editingPortfolio = null;
if (!empty($_GET['edit'])) {
    $editingPortfolio = $academicModel->findPortfolio((int) $_GET['edit']);
}

$module = 'portfolios';
$viewer = auth_user();
?>
<section class="py-10 md:py-14">
    <div class="mx-auto w-full max-w-6xl px-4 sm:px-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <h1>Portfolio học viên</h1>
                <p>Thêm, xem trước và quản lý media thực tế cho học viên.</p>
            </div>
        </div>

        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3><?= $editingPortfolio ? 'Sửa portfolio' : 'Thêm portfolio'; ?></h3>
            <form class="grid gap-3" method="post" action="/api/portfolios/save" enctype="multipart/form-data">
                <?= csrf_input(); ?>
                <input type="hidden" name="id" value="<?= (int) ($editingPortfolio['id'] ?? 0); ?>">
                <label>
                    Học viên
                    <?php if (($viewer['role'] ?? '') === 'student'): ?>
                        <input type="hidden" name="student_id" value="<?= (int) $viewer['id']; ?>">
                        <input type="text" value="<?= e((string) $viewer['full_name']); ?>" disabled>
                    <?php else: ?>
                        <select name="student_id" required>
                            <?php foreach ($students as $student): ?>
                                <option value="<?= (int) $student['id']; ?>" <?= (int) ($editingPortfolio['student_id'] ?? 0) === (int) $student['id'] ? 'selected' : ''; ?>><?= e((string) $student['full_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </label>
                <label>
                    Loại
                    <select name="type" required>
                        <option value="progress_video" <?= (($editingPortfolio['type'] ?? 'progress_video') === 'progress_video') ? 'selected' : ''; ?>>Video tiến bộ</option>
                        <option value="activity_photo" <?= (($editingPortfolio['type'] ?? '') === 'activity_photo') ? 'selected' : ''; ?>>Ảnh hoạt động</option>
                        <option value="feedback" <?= (($editingPortfolio['type'] ?? '') === 'feedback') ? 'selected' : ''; ?>>Feedback</option>
                    </select>
                </label>
                <label>
                    Mô tả
                    <textarea name="description" rows="4"><?= e((string) ($editingPortfolio['description'] ?? '')); ?></textarea>
                </label>
                <label>
                    Tải lên media
                    <input type="file" name="portfolio_file" accept=".jpg,.jpeg,.png,.mp4,.mov,.webm">
                </label>
                <label>
                    Hoặc đường dẫn media hiện có
                    <input type="text" name="media_url" value="<?= e((string) ($editingPortfolio['media_url'] ?? '')); ?>">
                </label>
                <label>
                    Hiển thị công khai
                    <select name="is_public_web">
                        <option value="1" <?= (int) ($editingPortfolio['is_public_web'] ?? 0) === 1 ? 'selected' : ''; ?>>Có</option>
                        <option value="0" <?= (int) ($editingPortfolio['is_public_web'] ?? 0) === 0 ? 'selected' : ''; ?>>Không</option>
                    </select>
                </label>
                <button class="inline-flex items-center justify-center rounded-xl bg-blue-700 px-4 py-2.5 text-sm font-bold text-white transition hover:-translate-y-0.5 hover:bg-blue-800" type="submit">Lưu portfolio</button>
            </form>
        </article>

        <div class="grid gap-4 grid-cols-1 md:grid-cols-2 lg:grid-cols-3 mt-6">
            <?php foreach ($portfolios as $portfolio): ?>
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3><?= e((string) $portfolio['student_name']); ?></h3>
                    <p><?= e((string) $portfolio['type']); ?></p>
                    <p><?= e((string) $portfolio['description']); ?></p>
                    <?php if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', (string) $portfolio['media_url'])): ?>
                        <img class="w-full rounded-xl" src="<?= e((string) $portfolio['media_url']); ?>" alt="portfolio">
                    <?php elseif (preg_match('/\.(mp4|mov|webm)$/i', (string) $portfolio['media_url'])): ?>
                        <video class="w-full rounded-xl" controls><source src="<?= e((string) $portfolio['media_url']); ?>"></video>
                    <?php else: ?>
                        <a href="<?= e((string) $portfolio['media_url']); ?>" target="_blank">Mở media</a>
                    <?php endif; ?>
                    <div class="mt-2.5">
                        <?php
                        $canManagePortfolio = ($viewer['role'] ?? '') !== 'student' || (int) ($viewer['id'] ?? 0) === (int) $portfolio['student_id'];
                        ?>
                        <?php if ($canManagePortfolio): ?>
                            <a href="<?= e(page_url('portfolios-academic', ['edit' => (int) $portfolio['id']])); ?>">Sửa</a>
                            |
                            <form class="inline-block" method="post" action="/api/portfolios/delete?id=<?= (int) $portfolio['id']; ?>">
                                <?= csrf_input(); ?>
                                <button class="cursor-pointer border-0 bg-transparent p-0 text-inherit" type="submit">Xóa</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>


