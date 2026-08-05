<?php
declare(strict_types=1);

require_once __DIR__ . '/../../models/MediaGalleryModel.php';

$galleryModel = new MediaGalleryModel();

$selectedCategorySlug = trim((string) ($_GET['topic'] ?? ''));
$selectedType = trim((string) ($_GET['type'] ?? ''));
$page = max(1, (int) ($_GET['p'] ?? 1));
$perPage = 12;

$categories = $galleryModel->getCategories(true);

$activeCategory = null;
$categoryId = null;
if ($selectedCategorySlug !== '') {
    $activeCategory = $galleryModel->findCategoryBySlug($selectedCategorySlug);
    if (is_array($activeCategory)) {
        $categoryId = (int) $activeCategory['id'];
    }
}

$mediaData = $galleryModel->getMediaItems(
    $categoryId,
    $selectedType !== '' ? $selectedType : null,
    $page,
    $perPage,
    true,
    null
);

$items = $mediaData['items'];
$totalItems = $mediaData['total'];
$totalPages = $mediaData['pages'];
?>

<!-- Hero Banner (Bright Creative Split Gallery Theme) -->
<div class="relative overflow-hidden bg-gradient-to-br from-sky-100 via-indigo-100/90 to-blue-100 border-b border-indigo-100 py-12 sm:py-16">
    <!-- Ambient glowing colorful background blobs -->
    <div class="absolute -top-20 -left-20 h-80 w-80 rounded-full bg-sky-400/30 blur-3xl pointer-events-none"></div>
    <div class="absolute top-1/2 -right-20 h-80 w-80 rounded-full bg-indigo-400/30 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-20 left-1/3 h-72 w-72 rounded-full bg-rose-400/25 blur-3xl pointer-events-none"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center">
            
            <!-- Left Column: Content & Badges -->
            <div class="lg:col-span-7 space-y-6 text-left">
                <div class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-xs font-black text-indigo-700 shadow-md shadow-indigo-100 border border-indigo-200">
                    <span class="flex h-2.5 w-2.5 rounded-full bg-rose-500 animate-pulse"></span>
                    <span><?= e(t('gallery.hero_kicker')); ?></span>
                </div>

                <h1 class="text-3xl font-black text-slate-900 sm:text-4xl lg:text-5xl leading-snug tracking-normal">
                    <?= e(t('gallery.hero_title_1')); ?> <span class="bg-gradient-to-r from-blue-700 to-indigo-700 bg-clip-text text-transparent"><?= e(t('gallery.hero_title_2')); ?></span>
                    <span class="block mt-1 sm:mt-2">
                        & <span class="bg-gradient-to-r from-rose-600 to-amber-600 bg-clip-text text-transparent"><?= e(t('gallery.hero_title_3')); ?></span>
                    </span>
                </h1>

                <p class="text-sm sm:text-base text-slate-700 leading-relaxed font-medium max-w-2xl">
                    <?= e(t('gallery.subtitle')); ?>
                </p>

                <!-- Media Highlights List -->
                <div class="pt-2 grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="flex items-center gap-3 p-3 rounded-2xl bg-white/80 border border-slate-200/80 shadow-sm backdrop-blur-sm">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-600 font-bold text-base">
                            📸
                        </span>
                        <div>
                            <div class="text-xs font-black text-slate-900"><?= e(t('gallery.stat_photos')); ?></div>
                            <div class="text-[11px] text-slate-500"><?= e(t('gallery.stat_photos_sub')); ?></div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 p-3 rounded-2xl bg-white/80 border border-slate-200/80 shadow-sm backdrop-blur-sm">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-rose-100 text-rose-600 font-bold text-base">
                            🎥
                        </span>
                        <div>
                            <div class="text-xs font-black text-slate-900"><?= e(t('gallery.stat_videos')); ?></div>
                            <div class="text-[11px] text-slate-500"><?= e(t('gallery.stat_videos_sub')); ?></div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 p-3 rounded-2xl bg-white/80 border border-slate-200/80 shadow-sm backdrop-blur-sm">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-600 font-bold text-base">
                            🏆
                        </span>
                        <div>
                            <div class="text-xs font-black text-slate-900"><?= e(t('gallery.stat_awards')); ?></div>
                            <div class="text-[11px] text-slate-500"><?= e(t('gallery.stat_awards_sub')); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Interactive Polaroid Photo Stack -->
            <div class="lg:col-span-5 relative">
                <div class="relative w-full max-w-md mx-auto aspect-[4/3] sm:aspect-[1/1] flex items-center justify-center">
                    
                    <!-- Back Photo Card 1 (Tilted Right) -->
                    <div class="absolute w-64 sm:w-72 rounded-3xl bg-white p-3 shadow-xl border border-slate-200/80 rotate-6 translate-x-8 -translate-y-4 transition-transform duration-300 hover:rotate-3 hover:scale-105">
                        <img src="assets/images/course.jpg" alt="Hoạt động trung tâm" class="w-full h-40 sm:h-44 object-cover rounded-2xl">
                        <div class="pt-2 text-center">
                            <span class="text-[11px] font-bold text-slate-700">🏆 Lễ Trao Chứng Chỉ Cambridge</span>
                        </div>
                    </div>

                    <!-- Back Photo Card 2 (Tilted Left) -->
                    <div class="absolute w-64 sm:w-72 rounded-3xl bg-white p-3 shadow-xl border border-slate-200/80 -rotate-6 -translate-x-8 translate-y-4 transition-transform duration-300 hover:-rotate-2 hover:scale-105">
                        <img src="assets/images/student.jpg" alt="Học viên Nhuệ Minh" class="w-full h-40 sm:h-44 object-cover rounded-2xl">
                        <div class="pt-2 text-center">
                            <span class="text-[11px] font-bold text-slate-700">🎈 Ngoại Khóa & Summer Camp</span>
                        </div>
                    </div>

                    <!-- Main Front Photo Card (Centered) -->
                    <div class="relative z-10 w-68 sm:w-76 rounded-3xl bg-white p-3.5 shadow-2xl border border-slate-200/80 -rotate-1 transition-transform duration-300 hover:rotate-0 hover:scale-105">
                        <img src="assets/images/center.jpg" alt="Lớp học tại Nhuệ Minh" class="w-full h-44 sm:h-48 object-cover rounded-2xl">
                        <div class="pt-2.5 flex items-center justify-between px-1">
                            <span class="text-xs font-black text-slate-900">🏫 Lớp Học Tương Tác Sĩ Số Vàng</span>
                            <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-extrabold">● Active</span>
                        </div>
                    </div>

                    <!-- Floating Badge Chip -->
                    <div class="absolute -bottom-2 right-4 z-20 px-4 py-2 rounded-2xl bg-slate-900 text-white text-xs font-bold shadow-xl border border-slate-700 flex items-center gap-2">
                        <span>✨ 100% Ảnh Thực Tế</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Category & Filter Tabs -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6 pb-8 border-b border-slate-200">
        <!-- Topic Pills -->
        <div class="flex flex-wrap items-center gap-2">
            <a href="<?= e(page_url('gallery', $selectedType ? ['type' => $selectedType] : [])); ?>"
               class="px-4 py-2 text-sm font-semibold rounded-full transition-all duration-200 <?= $selectedCategorySlug === '' ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'; ?>">
                🌟 <?= e(t('gallery.all_topics')); ?>
            </a>

            <?php foreach ($categories as $cat): ?>
                <?php
                $isActive = ($selectedCategorySlug === $cat['slug']);
                ?>
                <a href="<?= e(page_url('gallery', array_merge(['topic' => $cat['slug']], $selectedType ? ['type' => $selectedType] : []))); ?>"
                   class="px-4 py-2 text-sm font-semibold rounded-full transition-all duration-200 <?= $isActive ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'; ?>">
                    <?= e($cat['name']); ?>
                    <span class="ml-1 text-xs opacity-75">(<?= (int) ($cat['item_count'] ?? 0); ?>)</span>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Format Type Filter (All / Image / Video) -->
        <div class="inline-flex rounded-xl bg-slate-100 p-1 border border-slate-200 self-start md:self-auto shrink-0">
            <a href="<?= e(page_url('gallery', array_merge($selectedCategorySlug ? ['topic' => $selectedCategorySlug] : [], ['type' => '']))); ?>"
               class="px-3 py-1.5 text-xs font-semibold rounded-lg transition <?= $selectedType === '' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-600 hover:text-slate-900'; ?>">
                <?= e(t('gallery.all_types')); ?>
            </a>
            <a href="<?= e(page_url('gallery', array_merge($selectedCategorySlug ? ['topic' => $selectedCategorySlug] : [], ['type' => 'image']))); ?>"
               class="px-3 py-1.5 text-xs font-semibold rounded-lg transition <?= $selectedType === 'image' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-600 hover:text-slate-900'; ?>">
                📷 <?= e(t('gallery.type_image')); ?>
            </a>
            <a href="<?= e(page_url('gallery', array_merge($selectedCategorySlug ? ['topic' => $selectedCategorySlug] : [], ['type' => 'video']))); ?>"
               class="px-3 py-1.5 text-xs font-semibold rounded-lg transition <?= in_array($selectedType, ['video', 'youtube'], true) ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-600 hover:text-slate-900'; ?>">
                🎥 <?= e(t('gallery.type_video')); ?>
            </a>
        </div>
    </div>

    <!-- Active Topic Info Header -->
    <?php if ($activeCategory): ?>
        <div class="mt-6 rounded-2xl bg-blue-50/70 border border-blue-100 p-4 sm:p-6">
            <h2 class="text-xl font-bold text-blue-900">📌 <?= e(t('gallery.topic_prefix')); ?><?= e($activeCategory['name']); ?></h2>
            <?php if (!empty($activeCategory['description'])): ?>
                <p class="text-sm text-blue-700 mt-1"><?= e($activeCategory['description']); ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Media Grid -->
    <?php if (empty($items)): ?>
        <div class="mt-12 rounded-3xl border-2 border-dashed border-slate-200 p-12 text-center">
            <div class="inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-3xl mb-4">🖼️</div>
            <h3 class="text-lg font-bold text-slate-800"><?= e(t('gallery.empty_title')); ?></h3>
            <p class="text-sm text-slate-500 mt-1"><?= e(t('gallery.empty_desc')); ?></p>
        </div>
    <?php else: ?>
        <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach ($items as $item): ?>
                <?php
                $isYoutube = ($item['media_type'] === 'youtube');
                $isVideo = ($item['media_type'] === 'video');
                $filePath = $item['file_path_or_url'];
                
                $thumb = $item['thumbnail_url'] ?: $filePath;
                $ytEmbedUrl = '';

                if ($isYoutube) {
                    preg_match('/(?:v=|\/embed\/|\/11\/)([^"&?\/ ]{11})/', $filePath, $matches);
                    if (!empty($matches[1])) {
                        $ytId = $matches[1];
                        $ytEmbedUrl = 'https://www.youtube.com/embed/' . $ytId . '?autoplay=1';
                        if (empty($item['thumbnail_url'])) {
                            $thumb = 'https://img.youtube.com/vi/' . $ytId . '/hqdefault.jpg';
                        }
                    }
                }
                ?>
                <div class="group relative flex flex-col overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl cursor-pointer"
                     onclick="openGalleryModal(<?= htmlspecialchars(json_encode([
                         'title' => $item['title'],
                         'category' => $item['category_name'],
                         'description' => $item['description'],
                         'media_type' => $item['media_type'],
                         'file_path' => $filePath,
                         'yt_embed' => $ytEmbedUrl,
                         'created_at' => date('d/m/Y', strtotime($item['created_at'])),
                     ]), ENT_QUOTES, 'UTF-8'); ?>)">
                    
                    <!-- Media Thumbnail Container -->
                    <div class="relative aspect-[4/3] w-full overflow-hidden bg-slate-900">
                        <img src="<?= e($thumb); ?>" alt="<?= e($item['title']); ?>" 
                             class="h-full w-full object-cover transition duration-500 group-hover:scale-110 group-hover:opacity-90"
                             loading="lazy" onError="this.src='https://placehold.co/600x450?text=Media+Preview';">

                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/70 via-transparent to-transparent opacity-60 group-hover:opacity-80 transition duration-300"></div>

                        <!-- Top Badges -->
                        <div class="absolute top-3 left-3 right-3 flex items-center justify-between pointer-events-none">
                            <span class="rounded-full bg-slate-900/80 backdrop-blur-md px-3 py-1 text-[11px] font-bold text-white shadow-sm border border-white/10">
                                <?= e($item['category_name']); ?>
                            </span>

                            <?php if (!empty($item['is_featured'])): ?>
                                <span class="rounded-full bg-amber-400 px-2.5 py-0.5 text-[11px] font-bold text-slate-900 shadow-sm">
                                    ⭐ <?= e(t('gallery.featured')); ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Center Play Button for Video -->
                        <?php if ($isVideo || $isYoutube): ?>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-600/90 text-white shadow-lg backdrop-blur-md transition duration-300 group-hover:scale-115 group-hover:bg-blue-600">
                                    <svg class="h-6 w-6 translate-x-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Card Body -->
                    <div class="flex flex-1 flex-col p-4">
                        <h3 class="line-clamp-2 text-base font-bold text-slate-800 group-hover:text-blue-600 transition">
                            <?= e($item['title']); ?>
                        </h3>
                        <?php if (!empty($item['description'])): ?>
                            <p class="mt-1.5 line-clamp-2 text-xs text-slate-500 leading-relaxed">
                                <?= e($item['description']); ?>
                            </p>
                        <?php endif; ?>

                        <div class="mt-auto pt-3 flex items-center justify-between text-[11px] font-medium text-slate-400 border-t border-slate-100">
                            <span>📅 <?= date('d/m/Y', strtotime($item['created_at'])); ?></span>
                            <span class="font-semibold text-blue-600 group-hover:underline inline-flex items-center gap-1">
                                <?= e(t('gallery.view_detail')); ?> &rarr;
                            </span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Phân trang -->
        <?php if ($totalPages > 1): ?>
            <div class="mt-12 flex justify-center items-center gap-2">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="<?= e(page_url('gallery', array_merge($selectedCategorySlug ? ['topic' => $selectedCategorySlug] : [], $selectedType ? ['type' => $selectedType] : [], ['p' => $i]))); ?>"
                       class="px-4 py-2 text-sm font-semibold rounded-xl transition <?= $i === $page ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'; ?>">
                        <?= $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</main>

<!-- Lightbox Modal Fullview -->
<div id="galleryModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-950/85 backdrop-blur-md p-4 sm:p-6 md:p-10 flex items-center justify-center transition-opacity duration-300">
    <div class="relative w-full max-w-4xl overflow-hidden rounded-3xl bg-slate-900 text-white shadow-2xl border border-white/10" onclick="event.stopPropagation()">
        
        <!-- Close Button -->
        <button onclick="closeGalleryModal()" class="absolute top-4 right-4 z-20 flex h-10 w-10 items-center justify-center rounded-full bg-slate-800/80 text-white hover:bg-slate-700 backdrop-blur-md transition">
            ✕
        </button>

        <!-- Media Display Box -->
        <div id="modalMediaContainer" class="relative aspect-video w-full bg-black flex items-center justify-center overflow-hidden">
            <!-- Dynamic Image / Video / Iframe will be injected here -->
        </div>

        <!-- Content Details -->
        <div class="p-6">
            <div class="flex items-center gap-3">
                <span id="modalCategory" class="rounded-full bg-blue-500/20 px-3 py-1 text-xs font-semibold text-blue-300 border border-blue-400/30"></span>
                <span id="modalDate" class="text-xs text-slate-400"></span>
            </div>
            <h2 id="modalTitle" class="mt-2 text-xl sm:text-2xl font-extrabold text-white leading-snug"></h2>
            <p id="modalDescription" class="mt-2 text-sm text-slate-300 leading-relaxed"></p>
        </div>
    </div>
</div>

<script>
function openGalleryModal(item) {
    const modal = document.getElementById('galleryModal');
    const container = document.getElementById('modalMediaContainer');
    const category = document.getElementById('modalCategory');
    const date = document.getElementById('modalDate');
    const title = document.getElementById('modalTitle');
    const desc = document.getElementById('modalDescription');

    category.innerText = item.category || 'Trung tâm';
    date.innerText = '📅 ' + (item.created_at || '');
    title.innerText = item.title || '';
    desc.innerText = item.description || '';

    container.innerHTML = '';

    if (item.media_type === 'youtube' && item.yt_embed) {
        container.innerHTML = `<iframe src="${item.yt_embed}" class="w-full h-full border-0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>`;
    } else if (item.media_type === 'video') {
        container.innerHTML = `<video src="${item.file_path}" controls autoplay class="w-full h-full object-contain"></video>`;
    } else {
        container.innerHTML = `<img src="${item.file_path}" alt="${item.title}" class="w-full h-full object-contain">`;
    }

    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeGalleryModal() {
    const modal = document.getElementById('galleryModal');
    const container = document.getElementById('modalMediaContainer');
    container.innerHTML = '';
    modal.classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeGalleryModal();
    }
});
</script>
