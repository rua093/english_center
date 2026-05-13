<?php
$academicModel = new AcademicModel();
$materialPage = max(1, (int) ($_GET['material_page'] ?? 1));
$materialPerPage = ui_pagination_resolve_per_page('material_per_page', 12);
$materialTotal = $academicModel->countMaterials();
$materialTotalPages = max(1, (int) ceil($materialTotal / $materialPerPage));
if ($materialPage > $materialTotalPages) {
    $materialPage = $materialTotalPages;
}

$documents = $materialTotal > 0 ? $academicModel->listMaterialsPage($materialPage, $materialPerPage) : [];

$fileBadge = static function (string $filePath): array {
    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    return match ($extension) {
        'mp3', 'wav', 'm4a' => ['label' => 'Audio', 'icon' => 'fa-file-audio', 'color' => 'text-sky-600 bg-sky-50 border-sky-100'],
        'mp4', 'mov', 'webm', 'avi' => ['label' => 'Video', 'icon' => 'fa-file-video', 'color' => 'text-violet-600 bg-violet-50 border-violet-100'],
        'doc', 'docx' => ['label' => 'DOC', 'icon' => 'fa-file-word', 'color' => 'text-blue-600 bg-blue-50 border-blue-100'],
        default => ['label' => 'PDF', 'icon' => 'fa-file-pdf', 'color' => 'text-rose-600 bg-rose-50 border-rose-100'],
    };
};
?>

<style>
    .resource-card:hover { transform: translateY(-5px); border-color: #10b981; }
    .btn-download-gradient { background: linear-gradient(135deg, #065f46 0%, #10b981 100%); }
    .compact-text { font-size: 0.8rem; line-height: 1.25rem; }
    .pagination-btn:hover { background: #10b981; color: white; border-color: #10b981; }
    .pagination-active { background: #065f46; color: white; border-color: #065f46; }
    .material-description blockquote {
        margin: 0.75rem 0;
        padding: 0.8rem 1rem;
        border-left: 4px solid #10b981;
        background: #ecfdf5;
        border-radius: 1rem;
        font-style: italic;
    }
    .material-description ul,
    .material-description ol {
        margin: 0.75rem 0;
        padding-left: 1.35rem;
    }
    .material-description li {
        margin: 0.25rem 0;
    }
    .material-description a {
        color: #047857;
        text-decoration: underline;
        text-underline-offset: 0.18em;
    }
    .material-description code {
        padding: 0.12rem 0.4rem;
        border-radius: 0.45rem;
        background: #f1f5f9;
        font-size: 0.95em;
    }
    
    /* Style cho Checkbox Custom */
    .filter-checkbox:checked + div { background-color: #10b981; border-color: #10b981; }
    .filter-checkbox:checked + div svg { opacity: 1; transform: scale(1); }
</style>

<section class="relative pt-12 pb-16 overflow-hidden bg-lime-100">
    <div class="absolute inset-0 z-0 pointer-events-none opacity-[0.08]" style="background-image: radial-gradient(#475569 1.5px, transparent 1.5px); background-size: 24px 24px;"></div>
    <div class="absolute inset-x-0 top-0 z-0 h-72 pointer-events-none bg-gradient-to-b from-lime-200/75 via-lime-100/45 to-transparent"></div>
    <div class="absolute top-0 left-0 w-full h-full pointer-events-none -z-10">
        <div class="absolute top-0 right-0 w-[300px] h-[300px] bg-emerald-50/50 rounded-full blur-[80px]"></div>
        <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-blue-50/40 rounded-full blur-[100px]"></div>
    </div>

    <div class="mx-auto px-4 w-[96%] max-w-[1700px]"> 
        
        <div class="text-center mb-10" data-aos="fade-down">
            <h1 class="text-3xl md:text-4xl font-black text-slate-950 mb-3 drop-shadow-[0_1px_0_rgba(255,255,255,0.55)]">
                <?= e(t('documents.title')); ?> <span class="text-emerald-600"><?= e(t('documents.highlight')); ?></span>
            </h1>
            <p class="text-slate-700 text-sm md:text-base font-semibold leading-relaxed max-w-2xl mx-auto"><?= e(t('documents.subtitle')); ?></p>
        </div>

        <div class="flex flex-col gap-6" data-aos="fade-up" data-aos-delay="100">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-3">
                <div class="text-center sm:text-left">
                    <p class="text-sm font-bold text-slate-500"><?= e(t('documents.found', ['count' => number_format($materialTotal, 0, ',', '.')])); ?></p>
                    <p class="text-xs font-medium text-slate-400 mt-1"><?= e(t('documents.source_note')); ?></p>
                </div>

                <div class="relative w-full sm:w-72">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                    <input type="text" placeholder="<?= e(t('documents.search_placeholder')); ?>" class="w-full pl-10 pr-4 py-3 rounded-2xl bg-white border border-slate-200 outline-none text-sm font-bold focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all shadow-sm">
                </div>
            </div>

            <?php if (empty($documents)): ?>
                <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-10 text-center shadow-sm">
                    <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl mx-auto mb-4">
                        <i class="fa-regular fa-folder-open"></i>
                    </div>
                    <h2 class="text-lg font-black text-slate-800 mb-2"><?= e(t('documents.empty_title')); ?></h2>
                    <p class="text-sm text-slate-500"><?= e(t('documents.empty_copy')); ?></p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-4">
                    <?php foreach ($documents as $doc): ?>
                        <?php
                        $materialFilePath = normalize_public_file_url((string) ($doc['file_path'] ?? ''));
                        $badge = $fileBadge((string) ($doc['file_path'] ?? ''));
                        $courseName = trim((string) ($doc['course_name'] ?? ''));
                        $description = trim((string) ($doc['description'] ?? ''));
                        ?>
                        <article class="resource-card group relative bg-white rounded-2xl border border-slate-100 p-4 transition-all duration-300 shadow-lg shadow-slate-200/40">
                            <div class="flex items-start justify-between gap-3 mb-3">
                                <div class="min-w-0">
                                    <p class="text-[9px] font-black uppercase tracking-[0.28em] text-slate-400 mb-1.5"><?= e(t('documents.card_kicker')); ?></p>
                                    <h3 class="text-sm font-black text-slate-800 leading-snug line-clamp-2 group-hover:text-emerald-600 transition-colors">
                                        <?= e((string) ($doc['title'] ?? '')) ?>
                                    </h3>
                                </div>
                                <div class="w-10 h-10 rounded-2xl border flex items-center justify-center text-sm shrink-0 <?= e($badge['color']) ?>">
                                    <i class="fa-solid <?= e($badge['icon']) ?>"></i>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-1.5 mb-3">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full border text-[10px] font-black <?= e($badge['color']) ?>">
                                    <i class="fa-solid fa-layer-group"></i>
                                    <?= e($badge['label']) ?>
                                </span>
                                <?php if ($courseName !== ''): ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100 text-[10px] font-black">
                                        <i class="fa-solid fa-book-open"></i>
                                        <?= e($courseName) ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="material-description text-xs text-slate-500 leading-5 min-h-[3rem]">
                                <?= $description !== '' ? ui_render_bbcode($description) : e(t('documents.default_description')) ?>
                            </div>

                            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                                <span class="text-[10px] font-bold text-slate-400 truncate">
                                    <i class="fa-regular fa-file-lines mr-1"></i>
                                    <?= e((string) basename((string) ($doc['file_path'] ?? ''))) ?>
                                </span>
                                <?php if ($materialFilePath !== ''): ?>
                                    <a href="<?= e($materialFilePath) ?>" target="_blank" rel="noopener noreferrer" class="btn-download-gradient inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-white text-[10px] font-black transition-all hover:shadow-md hover:-translate-y-0.5">
                                        <?= e(t('documents.download_now')); ?> <i class="fa-solid fa-arrow-down-to-bracket text-[9px]"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="inline-flex items-center justify-center px-3 py-2 rounded-xl bg-slate-100 text-slate-400 text-[10px] font-black"><?= e(t('documents.no_file')); ?></span>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($materialTotal > 0 && $materialTotalPages > 1): ?>
                <div class="mt-6 flex flex-wrap items-center justify-center gap-2">
                    <?php if ($materialPage > 1): ?>
                        <a class="w-10 h-10 rounded-xl border border-slate-200 flex items-center justify-center text-slate-400 hover:bg-white hover:text-emerald-600 hover:border-emerald-200 transition-all shadow-sm" href="<?= e(page_url('documents', ['material_page' => $materialPage - 1, 'material_per_page' => $materialPerPage])); ?>">
                            <i class="fa-solid fa-chevron-left text-xs"></i>
                        </a>
                    <?php endif; ?>
                    <span class="w-10 h-10 rounded-xl flex items-center justify-center text-sm font-black pagination-active shadow-md"><?= (int) $materialPage ?></span>
                    <span class="text-slate-400 text-sm font-bold">/ <?= (int) $materialTotalPages ?></span>
                    <?php if ($materialPage < $materialTotalPages): ?>
                        <a class="w-10 h-10 rounded-xl border border-slate-200 flex items-center justify-center text-slate-400 hover:bg-white hover:text-emerald-600 hover:border-emerald-200 transition-all shadow-sm" href="<?= e(page_url('documents', ['material_page' => $materialPage + 1, 'material_per_page' => $materialPerPage])); ?>">
                            <i class="fa-solid fa-chevron-right text-xs"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        </div>
    </div>
</section>
