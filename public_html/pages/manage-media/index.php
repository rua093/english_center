<?php
declare(strict_types=1);

require_role(['admin', 'academic']);
require_once __DIR__ . '/../../models/MediaGalleryModel.php';

$galleryModel = new MediaGalleryModel();

$activeTab = trim((string) ($_GET['tab'] ?? 'media'));
if (!in_array($activeTab, ['media', 'categories'], true)) {
    $activeTab = 'media';
}

$page = max(1, (int) ($_GET['page_num'] ?? 1));
$perPage = 12;
$selectedCategory = max(0, (int) ($_GET['category_id'] ?? 0));
$selectedType = trim((string) ($_GET['media_type'] ?? ''));
$searchQuery = trim((string) ($_GET['search'] ?? ''));

$categories = $galleryModel->getCategories(false);
$mediaData = $galleryModel->getMediaItems(
    $selectedCategory > 0 ? $selectedCategory : null,
    $selectedType !== '' ? $selectedType : null,
    $page,
    $perPage,
    false,
    $searchQuery !== '' ? $searchQuery : null
);

$items = $mediaData['items'];
$totalItems = $mediaData['total'];
$totalPages = $mediaData['pages'];

$editingItem = null;
if (!empty($_GET['edit_item'])) {
    $editingItem = $galleryModel->findMediaItemById((int) $_GET['edit_item']);
}

$editingCategory = null;
if (!empty($_GET['edit_cat'])) {
    $editingCategory = $galleryModel->findCategoryById((int) $_GET['edit_cat']);
}

$success = get_flash('success');
$error = get_flash('error');

$adminTitle = 'Quản lý Thư viện Media';
?>

<div class="grid gap-6">
    <!-- Notifications -->
    <?php if ($success): ?>
        <div class="rounded-xl border-l-4 border-emerald-500 bg-emerald-50 p-4 text-sm font-medium text-emerald-800 shadow-sm">
            <?= e($success); ?>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="rounded-xl border-l-4 border-rose-500 bg-rose-50 p-4 text-sm font-medium text-rose-800 shadow-sm">
            <?= e($error); ?>
        </div>
    <?php endif; ?>

    <!-- Navigation Header & Tabs -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div>
            <h2 class="text-xl font-bold text-slate-800">🖼️ Quản lý Thư viện Hình ảnh & Video</h2>
            <p class="text-sm text-slate-500 mt-1">Đăng ảnh/video hoạt động trung tâm theo từng chủ đề để học viên & phụ huynh theo dõi.</p>
        </div>
        <div class="inline-flex rounded-xl bg-slate-100 p-1.5 border border-slate-200">
            <a href="<?= e(page_url('manage-media', ['tab' => 'media'])); ?>" 
               class="px-4 py-2 text-sm font-semibold rounded-lg transition <?= $activeTab === 'media' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-600 hover:text-slate-900'; ?>">
                📸 Bài đăng Media (<?= (int) $totalItems; ?>)
            </a>
            <a href="<?= e(page_url('manage-media', ['tab' => 'categories'])); ?>" 
               class="px-4 py-2 text-sm font-semibold rounded-lg transition <?= $activeTab === 'categories' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-600 hover:text-slate-900'; ?>">
                🏷️ Chủ đề / Danh mục (<?= count($categories); ?>)
            </a>
        </div>
    </div>

    <?php if ($activeTab === 'media'): ?>
        <!-- ================= TAB MEDIA ITEMS ================= -->

        <!-- Form Đăng / Sửa Media -->
        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
                <h3 class="text-lg font-bold text-slate-800">
                    <?= $editingItem ? '✏️ Chỉnh sửa bài đăng Media' : '➕ Đăng Ảnh / Video mới'; ?>
                </h3>
                <?php if ($editingItem): ?>
                    <a href="<?= e(page_url('manage-media')); ?>" class="text-xs font-semibold text-rose-600 hover:underline">Hủy chỉnh sửa</a>
                <?php endif; ?>
            </div>

            <form class="grid gap-4 md:grid-cols-2" method="post" action="/?action=do-save-media-item" enctype="multipart/form-data">
                <?= csrf_input(); ?>
                <input type="hidden" name="id" value="<?= (int) ($editingItem['id'] ?? 0); ?>">

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Tiêu đề bài đăng <span class="text-rose-500">*</span></label>
                    <input type="text" name="title" value="<?= e((string) ($editingItem['title'] ?? '')); ?>" required
                           placeholder="Ví dụ: Hình ảnh lễ trao chứng chỉ KET/PET đợt 1 2026..."
                           class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Chủ đề thuộc về <span class="text-rose-500">*</span></label>
                    <select name="category_id" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none">
                        <option value="">-- Chọn Chủ đề --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= (int) $cat['id']; ?>" <?= (int) ($editingItem['category_id'] ?? 0) === (int) $cat['id'] ? 'selected' : ''; ?>>
                                <?= e($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Định dạng Media <span class="text-rose-500">*</span></label>
                    <select name="media_type" id="media_type_select" onchange="toggleMediaTypeFields()" required 
                            class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none">
                        <option value="image" <?= ($editingItem['media_type'] ?? 'image') === 'image' ? 'selected' : ''; ?>>🖼️ Hình ảnh (Upload File)</option>
                        <option value="video" <?= ($editingItem['media_type'] ?? '') === 'video' ? 'selected' : ''; ?>>🎥 Video (Upload MP4/WebM)</option>
                        <option value="youtube" <?= ($editingItem['media_type'] ?? '') === 'youtube' ? 'selected' : ''; ?>>▶️ Link YouTube / Vimeo</option>
                    </select>
                </div>

                <!-- Field Upload File -->
                <div id="file_upload_wrapper">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Tải tệp Media từ máy tính (Có thể chọn nhiều tệp ảnh cùng lúc)</label>
                    <input type="file" name="media_files[]" accept="image/*,video/*" multiple
                           class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="text-xs text-slate-500 mt-1">Giữ phím <kbd class="px-1 py-0.5 bg-slate-100 border border-slate-300 rounded font-mono">Ctrl</kbd> hoặc <kbd class="px-1 py-0.5 bg-slate-100 border border-slate-300 rounded font-mono">Shift</kbd> để chọn nhiều ảnh/video cùng lúc cho chủ đề này.</p>
                    <?php if ($editingItem && !empty($editingItem['file_path_or_url']) && str_starts_with($editingItem['file_path_or_url'], '/assets/uploads')): ?>
                        <p class="text-xs text-slate-500 mt-1">Đường dẫn hiện tại: <a href="<?= e($editingItem['file_path_or_url']); ?>" target="_blank" class="text-blue-600 underline">Xem file hiện tại</a></p>
                    <?php endif; ?>
                </div>

                <!-- Field YouTube Link -->
                <div id="youtube_link_wrapper" class="hidden">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Đường dẫn YouTube / Video Link</label>
                    <input type="url" name="youtube_url" value="<?= e(($editingItem['media_type'] ?? '') === 'youtube' ? ($editingItem['file_path_or_url'] ?? '') : ''); ?>"
                           placeholder="https://www.youtube.com/watch?v=..."
                           class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none">
                </div>

                <!-- Field Upload Thumbnail (Optional for Video) -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Ảnh xem trước (Thumbnail đại diện - Tùy chọn)</label>
                    <input type="file" name="thumbnail_file" accept="image/*"
                           class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Mô tả / Ghi chú bài đăng</label>
                    <textarea name="description" rows="3" placeholder="Nhập thêm thông tin kỉ niệm, địa điểm hoặc lưu ý..."
                              class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none"><?= e((string) ($editingItem['description'] ?? '')); ?></textarea>
                </div>

                <div class="md:col-span-2 flex flex-wrap items-center gap-6 pt-2">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_featured" value="1" <?= !empty($editingItem['is_featured']) ? 'checked' : ''; ?> class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm font-medium text-slate-700">⭐ Ghim bài đăng nổi bật</span>
                    </label>
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" <?= ($editingItem['is_active'] ?? 1) == 1 ? 'checked' : ''; ?> class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        <span class="text-sm font-medium text-slate-700">👁️ Hiển thị công khai</span>
                    </label>
                </div>

                <div class="md:col-span-2 pt-3 flex items-center gap-3">
                    <button type="submit" class="rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-md hover:bg-blue-700 transition">
                        <?= $editingItem ? 'Cập nhật bài đăng' : 'Đăng ngay'; ?>
                    </button>
                    <?php if ($editingItem): ?>
                        <a href="<?= e(page_url('manage-media')); ?>" class="rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">Hủy</a>
                    <?php endif; ?>
                </div>
            </form>
        </article>

        <!-- Danh sách Media -->
        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-slate-100 pb-4 mb-5">
                <h3 class="text-lg font-bold text-slate-800">📸 Danh sách Ảnh & Video đã đăng</h3>
                
                <!-- Filter Form -->
                <form method="get" action="/" class="flex flex-wrap items-center gap-2">
                    <input type="hidden" name="page" value="manage-media">
                    <input type="hidden" name="tab" value="media">
                    
                    <select name="category_id" onchange="this.form.submit()" class="rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 focus:outline-none">
                        <option value="0">Tất cả chủ đề</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= (int) $cat['id']; ?>" <?= $selectedCategory === (int) $cat['id'] ? 'selected' : ''; ?>>
                                <?= e($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <select name="media_type" onchange="this.form.submit()" class="rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 focus:outline-none">
                        <option value="">Tất cả loại media</option>
                        <option value="image" <?= $selectedType === 'image' ? 'selected' : ''; ?>>Hình ảnh</option>
                        <option value="video" <?= $selectedType === 'video' ? 'selected' : ''; ?>>Video Upload</option>
                        <option value="youtube" <?= $selectedType === 'youtube' ? 'selected' : ''; ?>>YouTube</option>
                    </select>

                    <input type="text" name="search" value="<?= e($searchQuery); ?>" placeholder="Tìm kiếm..." class="rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 focus:outline-none">
                    <button type="submit" class="rounded-xl bg-slate-800 px-3 py-1.5 text-xs font-medium text-white hover:bg-slate-900">Lọc</button>
                </form>
            </div>

            <?php if (empty($items)): ?>
                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-sm text-slate-500">
                    Chưa có bài đăng media nào phù hợp với bộ lọc.
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <?php foreach ($items as $item): ?>
                        <?php
                        $thumb = $item['thumbnail_url'] ?: $item['file_path_or_url'];
                        if ($item['media_type'] === 'youtube' && empty($item['thumbnail_url'])) {
                            preg_match('/(?:v=|\/embed\/|\/11\/)([^"&?\/ ]{11})/', $item['file_path_or_url'], $matches);
                            if (!empty($matches[1])) {
                                $thumb = 'https://img.youtube.com/vi/' . $matches[1] . '/hqdefault.jpg';
                            }
                        }
                        ?>
                        <div class="group relative flex flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition hover:shadow-md">
                            <!-- Thumbnail -->
                            <div class="relative aspect-video w-full overflow-hidden bg-slate-100">
                                <img src="<?= e($thumb); ?>" alt="<?= e($item['title']); ?>" class="h-full w-full object-cover transition duration-300 group-hover:scale-105" loading="lazy" onError="this.src='https://placehold.co/600x400?text=Media+Preview';">
                                
                                <span class="absolute top-2 left-2 rounded-lg bg-black/60 px-2 py-0.5 text-[10px] font-bold text-white uppercase backdrop-blur-sm">
                                    <?= e($item['category_name']); ?>
                                </span>

                                <?php if ($item['media_type'] === 'video' || $item['media_type'] === 'youtube'): ?>
                                    <div class="absolute inset-0 flex items-center justify-center bg-black/20">
                                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-white/90 text-blue-600 shadow-md">▶</span>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($item['is_featured'])): ?>
                                    <span class="absolute top-2 right-2 rounded-full bg-amber-400 px-2 py-0.5 text-[10px] font-bold text-slate-900 shadow-sm">
                                        ⭐ Nổi bật
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- Content -->
                            <div class="flex flex-1 flex-col p-3">
                                <h4 class="line-clamp-2 text-sm font-semibold text-slate-800" title="<?= e($item['title']); ?>"><?= e($item['title']); ?></h4>
                                <p class="mt-1 line-clamp-2 text-xs text-slate-500"><?= e($item['description'] ?: 'Không có mô tả'); ?></p>

                                <div class="mt-auto pt-3 flex items-center justify-between border-t border-slate-100 text-xs">
                                    <span class="<?= $item['is_active'] ? 'text-emerald-600 font-medium' : 'text-slate-400'; ?>">
                                        <?= $item['is_active'] ? '● Hiển thị' : '○ Đang ẩn'; ?>
                                    </span>

                                    <div class="flex items-center gap-2">
                                        <a href="<?= e(page_url('manage-media', ['edit_item' => $item['id'], 'tab' => 'media'])); ?>" class="font-semibold text-blue-600 hover:underline">Sửa</a>
                                        
                                        <form method="post" action="/?action=do-delete-media-item" onsubmit="return confirm('Bạn có chắc muốn xóa bài đăng media này?')">
                                            <?= csrf_input(); ?>
                                            <input type="hidden" name="id" value="<?= (int) $item['id']; ?>">
                                            <button type="submit" class="font-semibold text-rose-600 hover:underline">Xóa</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Phân trang -->
                <?php if ($totalPages > 1): ?>
                    <div class="mt-6 flex justify-center items-center gap-2">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="<?= e(page_url('manage-media', ['tab' => 'media', 'page_num' => $i, 'category_id' => $selectedCategory, 'media_type' => $selectedType, 'search' => $searchQuery])); ?>"
                               class="px-3.5 py-1.5 text-xs font-semibold rounded-lg <?= $i === $page ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'; ?>">
                                <?= $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </article>

    <?php else: ?>
        <!-- ================= TAB CATEGORIES ================= -->

        <!-- Form Tạo / Sửa Chủ đề -->
        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
                <h3 class="text-lg font-bold text-slate-800">
                    <?= $editingCategory ? '✏️ Chỉnh sửa Chủ đề' : '➕ Thêm Chủ đề mới'; ?>
                </h3>
                <?php if ($editingCategory): ?>
                    <a href="<?= e(page_url('manage-media', ['tab' => 'categories'])); ?>" class="text-xs font-semibold text-rose-600 hover:underline">Hủy chỉnh sửa</a>
                <?php endif; ?>
            </div>

            <form class="grid gap-4 md:grid-cols-2" method="post" action="/?action=do-save-media-category">
                <?= csrf_input(); ?>
                <input type="hidden" name="id" value="<?= (int) ($editingCategory['id'] ?? 0); ?>">

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Tên chủ đề <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="<?= e((string) ($editingCategory['name'] ?? '')); ?>" required
                           placeholder="Ví dụ: Halloween, Noel, Trại hè, Summer Camp..."
                           class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Thứ tự hiển thị</label>
                    <input type="number" name="display_order" value="<?= (int) ($editingCategory['display_order'] ?? 0); ?>"
                           class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Mô tả ngắn</label>
                    <input type="text" name="description" value="<?= e((string) ($editingCategory['description'] ?? '')); ?>"
                           placeholder="Mô tả về chủ đề..."
                           class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none">
                </div>

                <div class="md:col-span-2 pt-2">
                    <button type="submit" class="rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-md hover:bg-blue-700 transition">
                        <?= $editingCategory ? 'Cập nhật Chủ đề' : 'Thêm Chủ đề'; ?>
                    </button>
                </div>
            </form>
        </article>

        <!-- Danh sách Chủ đề -->
        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-bold text-slate-800 mb-4">🏷️ Danh sách Chủ đề hiện có</h3>
            
            <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
                <table class="w-full text-left text-sm text-slate-700">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3">STT</th>
                            <th class="px-4 py-3">Tên Chủ đề</th>
                            <th class="px-4 py-3">Slug URL</th>
                            <th class="px-4 py-3">Số lượng Media</th>
                            <th class="px-4 py-3 text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($categories as $index => $cat): ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-4 py-3 font-medium text-slate-500"><?= $index + 1; ?></td>
                                <td class="px-4 py-3 font-bold text-slate-800"><?= e($cat['name']); ?></td>
                                <td class="px-4 py-3 text-slate-500 font-mono text-xs"><?= e($cat['slug']); ?></td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-bold text-blue-700">
                                        <?= (int) ($cat['item_count'] ?? 0); ?> media
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="inline-flex items-center gap-3">
                                        <a href="<?= e(page_url('manage-media', ['tab' => 'categories', 'edit_cat' => $cat['id']])); ?>" class="font-semibold text-blue-600 hover:underline">Sửa</a>
                                        
                                        <form method="post" action="/?action=do-delete-media-category" onsubmit="return confirm('Bạn có chắc muốn xóa chủ đề này?')">
                                            <?= csrf_input(); ?>
                                            <input type="hidden" name="id" value="<?= (int) $cat['id']; ?>">
                                            <button type="submit" class="font-semibold text-rose-600 hover:underline">Xóa</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </article>
    <?php endif; ?>
</div>

<script>
function toggleMediaTypeFields() {
    const select = document.getElementById('media_type_select');
    const fileWrapper = document.getElementById('file_upload_wrapper');
    const ytWrapper = document.getElementById('youtube_link_wrapper');

    if (select.value === 'youtube') {
        fileWrapper.classList.add('hidden');
        ytWrapper.classList.remove('hidden');
    } else {
        fileWrapper.classList.remove('hidden');
        ytWrapper.classList.add('hidden');
    }
}
document.addEventListener('DOMContentLoaded', toggleMediaTypeFields);
</script>
