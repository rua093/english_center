<?php
require_login();
require_once __DIR__ . '/../../core/file_storage.php';

$academicModel = new AcademicModel();

$portfolioPage = max(1, (int) ($_GET['portfolio_page'] ?? 1));
$portfolioPerPage = ui_pagination_resolve_per_page('portfolio_per_page', 10);
$portfolioTotal = $academicModel->countPortfolios();
$portfolioTotalPages = max(1, (int) ceil($portfolioTotal / $portfolioPerPage));
if ($portfolioPage > $portfolioTotalPages) {
    $portfolioPage = $portfolioTotalPages;
}

$portfolios = $academicModel->listPortfoliosPage($portfolioPage, $portfolioPerPage);
$portfolioPerPageOptions = ui_pagination_per_page_options();
$students = $academicModel->studentLookups();

$editingPortfolio = null;
if (!empty($_GET['edit'])) {
    $editingPortfolio = $academicModel->findPortfolio((int) $_GET['edit']);
}

$module = 'portfolios';
$adminTitle = 'Học vụ - Portfolio học viên';

$viewer = auth_user();
$viewerRole = (string) ($viewer['role'] ?? '');
$viewerId = (int) ($viewer['id'] ?? 0);
$isStudentViewer = $viewerRole === 'student';

$success = get_flash('success');
$error = get_flash('error');

$portfolioTypeLabels = [
    'progress_video' => 'Video tiến bộ',
    'activity_photo' => 'Ảnh hoạt động',
    'feedback' => 'Feedback',
];

$selectedType = (string) ($editingPortfolio['type'] ?? 'progress_video');
$selectedDescription = trim((string) ($editingPortfolio['description'] ?? ''));
$selectedIsPublicWeb = (int) ($editingPortfolio['is_public_web'] ?? 0);
$editingPortfolioMediaPath = normalize_public_file_url((string) ($editingPortfolio['media_url'] ?? ''));
?>

<div class="grid gap-4">
    <?php if ($success): ?>
        <div class="rounded-xl border-l-4 border-emerald-500 bg-emerald-50 p-3 text-sm text-emerald-700"><?= e($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="rounded-xl border-l-4 border-rose-500 bg-rose-50 p-3 text-sm text-rose-700"><?= e($error); ?></div>
    <?php endif; ?>

    <article class="order-2 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3><?= $editingPortfolio ? 'Sửa portfolio' : 'Thêm portfolio'; ?></h3>
        <form class="grid gap-3" method="post" action="/api/portfolios/save" enctype="multipart/form-data">
            <?= csrf_input(); ?>
            <input type="hidden" name="id" value="<?= (int) ($editingPortfolio['id'] ?? 0); ?>">

            <label>
                Học viên
                <?php if ($isStudentViewer): ?>
                    <input type="hidden" name="student_id" value="<?= $viewerId; ?>">
                    <input type="text" value="<?= e((string) ($viewer['full_name'] ?? '')); ?>" disabled>
                <?php else: ?>
                    <select name="student_id" required>
                        <?php if (empty($students)): ?>
                            <option value="">-- Chưa có học viên --</option>
                        <?php else: ?>
                            <?php foreach ($students as $student): ?>
                                <option value="<?= (int) ($student['id'] ?? 0); ?>" <?= (int) ($editingPortfolio['student_id'] ?? 0) === (int) ($student['id'] ?? 0) ? 'selected' : ''; ?>><?= e((string) ($student['full_name'] ?? '')); ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                <?php endif; ?>
            </label>

            <label>
                Loại nội dung
                <select name="type" required>
                    <?php foreach ($portfolioTypeLabels as $typeValue => $typeLabel): ?>
                        <option value="<?= e($typeValue); ?>" <?= $selectedType === $typeValue ? 'selected' : ''; ?>><?= e($typeLabel); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Mô tả
                <textarea name="description" rows="4" placeholder="Ghi chú ngắn về nội dung portfolio"><?= e($selectedDescription); ?></textarea>
            </label>

            <label>
                Tải lên media
                <input type="file" name="portfolio_file" accept=".jpg,.jpeg,.png,.mp4,.mov,.webm" <?= $editingPortfolio ? '' : 'required'; ?>>
            </label>

            <?php if ($editingPortfolioMediaPath !== ''): ?>
                <p class="text-xs text-slate-500">
                    Media hiện tại:
                    <a class="font-semibold text-blue-700 hover:underline" href="<?= e($editingPortfolioMediaPath); ?>" target="_blank" rel="noopener noreferrer">Mở media</a>.
                    Chọn file mới để thay thế.
                </p>
            <?php endif; ?>

            <label>
                Hiển thị công khai
                <select name="is_public_web">
                    <option value="1" <?= $selectedIsPublicWeb === 1 ? 'selected' : ''; ?>>Có</option>
                    <option value="0" <?= $selectedIsPublicWeb === 0 ? 'selected' : ''; ?>>Không</option>
                </select>
            </label>

            <div class="inline-flex flex-wrap items-center gap-2">
                <button class="<?= ui_btn_primary_classes(); ?>" type="submit"><?= $editingPortfolio ? 'Cập nhật portfolio' : 'Lưu portfolio'; ?></button>
                <?php if ($editingPortfolio): ?>
                    <a class="<?= ui_btn_secondary_classes(); ?>" href="<?= e(page_url('portfolios-academic', ['portfolio_page' => $portfolioPage, 'portfolio_per_page' => $portfolioPerPage])); ?>">Hủy chỉnh sửa</a>
                <?php endif; ?>
            </div>
        </form>
    </article>

    <article class="order-1 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3>Danh sách portfolio</h3>
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full border-collapse text-sm">
                <thead>
                    <tr>
                        <th>Học viên</th>
                        <th>Loại</th>
                        <th>Media</th>
                        <th>Mô tả</th>
                        <th>Hiển thị</th>
                        <th>Ngày tạo</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($portfolios)): ?>
                        <tr>
                            <td colspan="7">
                                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">Chưa có portfolio nào.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($portfolios as $portfolio): ?>
                            <?php
                            $portfolioId = (int) ($portfolio['id'] ?? 0);
                            $portfolioStudentId = (int) ($portfolio['student_id'] ?? 0);
                            $portfolioType = (string) ($portfolio['type'] ?? '');
                            $portfolioTypeLabel = $portfolioTypeLabels[$portfolioType] ?? str_replace('_', ' ', $portfolioType);
                            $portfolioDescription = trim((string) ($portfolio['description'] ?? ''));

                            $portfolioMediaRaw = trim((string) ($portfolio['media_url'] ?? ''));
                            $portfolioMediaUrl = normalize_public_file_url($portfolioMediaRaw);
                            $isImage = $portfolioMediaUrl !== '' && preg_match('/\.(jpg|jpeg|png|gif|webp)(\?.*)?$/i', $portfolioMediaUrl) === 1;
                            $isVideo = $portfolioMediaUrl !== '' && preg_match('/\.(mp4|mov|webm)(\?.*)?$/i', $portfolioMediaUrl) === 1;

                            $createdAtRaw = trim((string) ($portfolio['created_at'] ?? ''));
                            $createdAtTimestamp = $createdAtRaw !== '' ? strtotime($createdAtRaw) : false;
                            $createdAtText = $createdAtTimestamp ? date('d/m/Y H:i', $createdAtTimestamp) : '-';

                            $canManagePortfolio = !$isStudentViewer || $viewerId === $portfolioStudentId;
                            ?>
                            <tr>
                                <td><?= e((string) ($portfolio['full_name'] ?? '')); ?></td>
                                <td>
                                    <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-xs font-semibold text-slate-700"><?= e($portfolioTypeLabel); ?></span>
                                </td>
                                <td>
                                    <?php if ($portfolioMediaUrl === ''): ?>
                                        <span class="text-xs text-slate-400">-</span>
                                    <?php elseif ($isImage): ?>
                                        <a href="<?= e($portfolioMediaUrl); ?>" target="_blank" rel="noopener noreferrer" aria-label="Xem ảnh" class="inline-block">
                                            <img src="<?= e($portfolioMediaUrl); ?>" alt="Preview" class="h-10 w-14 rounded-md border border-slate-200 object-cover">
                                        </a>
                                    <?php elseif ($isVideo): ?>
                                        <a href="<?= e($portfolioMediaUrl); ?>" target="_blank" rel="noopener noreferrer" class="text-xs font-semibold text-blue-700 hover:underline">Xem video</a>
                                    <?php else: ?>
                                        <a href="<?= e($portfolioMediaUrl); ?>" target="_blank" rel="noopener noreferrer" class="text-xs font-semibold text-blue-700 hover:underline">Mở liên kết</a>
                                    <?php endif; ?>
                                </td>
                                <td><?= e($portfolioDescription !== '' ? $portfolioDescription : '-'); ?></td>
                                <td>
                                    <?php if ((int) ($portfolio['is_public_web'] ?? 0) === 1): ?>
                                        <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700">Công khai</span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">Nội bộ</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= e($createdAtText); ?></td>
                                <td>
                                    <?php if ($canManagePortfolio): ?>
                                        <span class="inline-flex flex-wrap items-center gap-2">
                                            <a
                                                href="<?= e(page_url('portfolios-academic', ['edit' => $portfolioId, 'portfolio_page' => $portfolioPage, 'portfolio_per_page' => $portfolioPerPage])); ?>"
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
                                            <form class="inline-block" method="post" action="/api/portfolios/delete?id=<?= $portfolioId; ?>" onsubmit="return confirm('Bạn có chắc muốn xóa portfolio này không?');">
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
                                        </span>
                                    <?php else: ?>
                                        <span class="text-xs text-slate-400">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if ($portfolioTotal > 0): ?>
                <div class="border-t border-slate-200 bg-slate-50/80 px-3 py-2">
                    <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-slate-600">
                        <span class="font-medium">Trang <?= (int) $portfolioPage; ?>/<?= (int) $portfolioTotalPages; ?> - Tổng <?= (int) $portfolioTotal; ?> portfolio</span>
                        <div class="inline-flex items-center gap-1.5">
                            <form class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2 py-1" method="get" action="<?= e(page_url('portfolios-academic')); ?>">
                                <input type="hidden" name="page" value="portfolios-academic">
                                <label class="text-[11px] font-semibold text-slate-500" for="portfolio-per-page">Số dòng</label>
                                <select id="portfolio-per-page" name="portfolio_per_page" class="h-7 rounded-md border border-slate-200 bg-white px-2 text-xs font-semibold text-slate-700" onchange="this.form.submit()">
                                    <?php foreach ($portfolioPerPageOptions as $option): ?>
                                        <option value="<?= (int) $option; ?>" <?= $portfolioPerPage === (int) $option ? 'selected' : ''; ?>><?= (int) $option; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>

                            <?php if ($portfolioPage > 1): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('portfolios-academic', ['portfolio_page' => $portfolioPage - 1, 'portfolio_per_page' => $portfolioPerPage])); ?>">Trước</a>
                            <?php else: ?>
                                <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400">Trước</span>
                            <?php endif; ?>

                            <?php if ($portfolioPage < $portfolioTotalPages): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('portfolios-academic', ['portfolio_page' => $portfolioPage + 1, 'portfolio_per_page' => $portfolioPerPage])); ?>">Sau</a>
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
