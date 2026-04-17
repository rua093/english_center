<?php
require_login();

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
$viewer = auth_user();
?>

<div class="min-h-screen bg-[#f0f4f8] py-8 px-4 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">
        
        <header class="mb-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-black text-blue-900 tracking-tight">
                    Portfolio <span class="text-blue-600 underline decoration-blue-200">Học Viên</span>
                </h1>
                <p class="text-slate-500 font-medium mt-1">Quản lý kho lưu trữ media và tiến độ học tập thực tế.</p>
            </div>
            <div class="flex items-center gap-2 text-sm font-bold text-blue-700 bg-blue-50 px-4 py-2 rounded-full border border-blue-100">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-600"></span>
                </span>
                <?= count($portfolios); ?> Media Items
            </div>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <aside class="lg:col-span-4">
                <div class="sticky top-8 rounded-3xl border border-white bg-white/70 backdrop-blur-xl p-6 shadow-xl shadow-blue-900/5">
                    <div class="mb-6 flex items-center gap-3">
                        <div class="p-2 bg-blue-600 rounded-lg text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-800"><?= $editingPortfolio ? 'Cập nhật Portfolio' : 'Tạo Portfolio mới'; ?></h3>
                    </div>

                    <form class="space-y-4" method="post" action="/api/portfolios/save" enctype="multipart/form-data">
                        <?= csrf_input(); ?>
                        <input type="hidden" name="id" value="<?= (int) ($editingPortfolio['id'] ?? 0); ?>">

                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Học viên mục tiêu</label>
                            <?php if (($viewer['role'] ?? '') === 'student'): ?>
                                <input type="hidden" name="student_id" value="<?= (int) $viewer['id']; ?>">
                                <div class="w-full rounded-2xl bg-slate-100 px-4 py-3 text-sm font-semibold text-slate-600 border border-transparent italic">
                                    <?= e((string) $viewer['full_name']); ?> (Cá nhân)
                                </div>
                            <?php else: ?>
                                <select name="student_id" required class="w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-sm font-medium focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all appearance-none">
                                    <?php foreach ($students as $student): ?>
                                        <option value="<?= (int) $student['id']; ?>" <?= (int) ($editingPortfolio['student_id'] ?? 0) === (int) $student['id'] ? 'selected' : ''; ?>><?= e((string) $student['full_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="col-span-2">
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Loại nội dung</label>
                                <select name="type" required class="w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-sm font-medium focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all">
                                    <option value="progress_video" <?= (($editingPortfolio['type'] ?? 'progress_video') === 'progress_video') ? 'selected' : ''; ?>>📹 Video tiến bộ</option>
                                    <option value="activity_photo" <?= (($editingPortfolio['type'] ?? '') === 'activity_photo') ? 'selected' : ''; ?>>🖼️ Ảnh hoạt động</option>
                                    <option value="feedback" <?= (($editingPortfolio['type'] ?? '') === 'feedback') ? 'selected' : ''; ?>>💬 Feedback khách</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Mô tả ngắn</label>
                            <textarea name="description" rows="3" class="w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-sm placeholder:text-slate-300 focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all" placeholder="Ghi chú về dự án hoặc bài học..."><?= e((string) ($editingPortfolio['description'] ?? '')); ?></textarea>
                        </div>

                        <div class="rounded-2xl border-2 border-dashed border-blue-100 bg-blue-50/50 p-4">
                            <label class="block text-xs font-bold text-blue-900/40 uppercase tracking-widest mb-2 text-center">Tải media lên</label>
                            <input type="file" name="portfolio_file" accept=".jpg,.jpeg,.png,.mp4,.mov,.webm" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer">
                            <p class="mt-2 text-center text-[10px] text-blue-400 italic">Hoặc dán URL bên dưới</p>
                            <input type="text" name="media_url" value="<?= e((string) ($editingPortfolio['media_url'] ?? '')); ?>" class="mt-2 w-full rounded-xl border-0 bg-white px-3 py-2 text-xs shadow-sm" placeholder="https://...">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Chế độ hiển thị</label>
                            <select name="is_public_web" class="w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-sm font-medium focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all">
                                <option value="1" <?= (int) ($editingPortfolio['is_public_web'] ?? 0) === 1 ? 'selected' : ''; ?>>🌐 Công khai trên Website</option>
                                <option value="0" <?= (int) ($editingPortfolio['is_public_web'] ?? 0) === 0 ? 'selected' : ''; ?>>🔒 Lưu trữ nội bộ</option>
                            </select>
                        </div>

                        <button class="w-full rounded-2xl bg-blue-700 py-4 text-sm font-black text-white shadow-lg shadow-blue-200 transition-all hover:bg-blue-800 hover:-translate-y-1 active:scale-95" type="submit">
                            <?= $editingPortfolio ? 'CẬP NHẬT NGAY' : 'LƯU PORTFOLIO'; ?>
                        </button>
                        
                        <?php if ($editingPortfolio): ?>
                            <a href="<?= page_url('portfolios-academic'); ?>" class="block text-center text-xs font-bold text-slate-400 hover:text-rose-500 transition-colors mt-2">Hủy chỉnh sửa</a>
                        <?php endif; ?>
                    </form>
                </div>
            </aside>

            <main class="lg:col-span-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php foreach ($portfolios as $portfolio): ?>
                        <article class="group relative overflow-hidden rounded-3xl bg-white border border-slate-100 shadow-sm transition-all duration-300 hover:shadow-xl hover:shadow-blue-900/5 hover:-translate-y-1">
                            
                            <div class="aspect-video w-full overflow-hidden bg-slate-900 relative">
                                <?php if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', (string) $portfolio['media_url'])): ?>
                                    <img class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110" src="<?= e((string) $portfolio['media_url']); ?>" alt="portfolio">
                                <?php elseif (preg_match('/\.(mp4|mov|webm)$/i', (string) $portfolio['media_url'])): ?>
                                    <video class="h-full w-full object-cover" muted><source src="<?= e((string) $portfolio['media_url']); ?>"></video>
                                    <div class="absolute inset-0 flex items-center justify-center bg-black/20 group-hover:bg-black/40 transition-colors">
                                        <div class="rounded-full bg-white/20 p-3 backdrop-blur-md">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="flex h-full items-center justify-center bg-blue-50">
                                        <a class="font-bold text-blue-600 underline" href="<?= e((string) $portfolio['media_url']); ?>" target="_blank">Xem Link Ngoài</a>
                                    </div>
                                <?php endif; ?>

                                <div class="absolute top-4 left-4">
                                    <span class="rounded-lg bg-white/90 backdrop-blur px-2.5 py-1 text-[10px] font-black uppercase text-blue-900 shadow-sm">
                                        <?= str_replace('_', ' ', e((string) $portfolio['type'])); ?>
                                    </span>
                                </div>
                            </div>

                            <div class="p-5">
                                <div class="mb-3 flex items-start justify-between">
                                    <div>
                                        <h3 class="text-base font-bold text-slate-800 group-hover:text-blue-700 transition-colors"><?= e((string) $portfolio['student_name']); ?></h3>
                                        <p class="mt-1 text-xs text-slate-500 line-clamp-2"><?= e((string) $portfolio['description']); ?></p>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between border-t border-slate-50 pt-4 mt-2">
                                    <div class="flex gap-3">
                                        <?php
                                        $canManagePortfolio = ($viewer['role'] ?? '') !== 'student' || (int) ($viewer['id'] ?? 0) === (int) $portfolio['student_id'];
                                        ?>
                                        <?php if ($canManagePortfolio): ?>
                                            <a class="text-xs font-bold text-blue-600 hover:text-blue-800 flex items-center gap-1" href="<?= e(page_url('portfolios-academic', ['edit' => (int) $portfolio['id']])); ?>">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" /></svg>
                                                Sửa
                                            </a>
                                            <form class="inline-block" method="post" action="/api/portfolios/delete?id=<?= (int) $portfolio['id']; ?>" onsubmit="return confirm('Xóa nội dung này?')">
                                                <?= csrf_input(); ?>
                                                <button class="text-xs font-bold text-rose-400 hover:text-rose-600 cursor-pointer flex items-center gap-1" type="submit">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                                                    Xóa
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                    <span class="text-[10px] font-bold text-slate-300 uppercase italic">
                                        <?= (int)$portfolio['is_public_web'] === 1 ? 'Public' : 'Private'; ?>
                                    </span>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </main>
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
        <?php if (empty($portfolios)): ?>
            <article class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500 md:col-span-2 lg:col-span-3">Chưa có portfolio nào.</article>
        <?php else: ?>
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
                            <a href="<?= e(page_url('portfolios-academic', ['edit' => (int) $portfolio['id'], 'portfolio_page' => $portfolioPage, 'portfolio_per_page' => $portfolioPerPage])); ?>">Sửa</a>
                            |
                            <form class="inline-block" method="post" action="/api/portfolios/delete?id=<?= (int) $portfolio['id']; ?>">
                                <?= csrf_input(); ?>
                                <button class="cursor-pointer border-0 bg-transparent p-0 text-inherit" type="submit">Xóa</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if ($portfolioTotal > 0): ?>
        <div class="mt-3 rounded-xl border border-slate-200 bg-slate-50/80 px-3 py-2">
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


