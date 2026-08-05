<?php
declare(strict_types=1);

require_admin_or_staff();
require_once __DIR__ . '/../../models/FaqModel.php';

$faqModel = new FaqModel();
$categoriesList = $faqModel->getCategories(false);

$search = trim((string) ($_GET['search'] ?? ''));
$categoryFilter = trim((string) ($_GET['category'] ?? ''));
$page = max(1, (int) ($_GET['p'] ?? 1));

$faqsData = $faqModel->getAllFaqs(
    $categoryFilter !== '' ? $categoryFilter : null,
    false,
    $search !== '' ? $search : null,
    $page,
    20
);

$items = $faqsData['items'] ?? [];
$total = (int) ($faqsData['total'] ?? 0);
$totalPages = (int) ($faqsData['pages'] ?? 1);

$success = get_flash('success');
$error = get_flash('error');

// Edit item prefill if edit param set
$editingFaq = null;
$editId = (int) ($_GET['edit'] ?? 0);
if ($editId > 0) {
    $editingFaq = $faqModel->findFaqById($editId);
}
?>

<main class="py-8 font-jakarta">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        
        <!-- Header & Action Bar -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-3xl shadow-sm border border-slate-200">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1 rounded-full bg-indigo-100 text-indigo-700 text-xs font-black uppercase">Quản lý hệ thống</span>
                    <span class="text-xs text-slate-400">/ FAQ & Hỏi Đáp</span>
                </div>
                <h1 class="text-2xl font-black text-slate-900 mt-1">Quản Lý Câu Hỏi Thường Gặp (FAQ)</h1>
                <p class="text-xs text-slate-500 mt-1">Thêm mới, chỉnh sửa, sắp xếp thứ tự và ẩn/hiện câu hỏi trên website chính.</p>
            </div>

            <div class="flex items-center gap-3">
                <a href="<?= e(page_url('faq')); ?>" target="_blank" class="px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-2">
                    <span>👁️ Xem trang FAQ Public</span>
                </a>
                <button type="button" onclick="openFaqModal()" class="px-5 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black uppercase tracking-wider shadow-lg shadow-indigo-600/30 transition flex items-center gap-2">
                    <span>➕ Thêm Câu Hỏi Mới</span>
                </button>
            </div>
        </div>

        <!-- Flash Messages -->
        <?php if (!empty($success)): ?>
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center justify-between">
                <span>✅ <?= e($success); ?></span>
                <button onclick="this.parentElement.remove()" class="text-emerald-500 font-bold">&times;</button>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold flex items-center justify-between">
                <span>❌ <?= e($error); ?></span>
                <button onclick="this.parentElement.remove()" class="text-rose-500 font-bold">&times;</button>
            </div>
        <?php endif; ?>

        <!-- Search & Filter Bar -->
        <form method="GET" action="<?= e(page_url('manage-faqs')); ?>" class="grid grid-cols-1 sm:grid-cols-12 gap-4 bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <div class="sm:col-span-6">
                <input 
                    type="text" 
                    name="search" 
                    value="<?= e($search); ?>" 
                    placeholder="Tìm kiếm từ khóa câu hỏi, câu trả lời..." 
                    class="w-full rounded-xl bg-slate-50 px-4 py-2.5 text-xs text-slate-800 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium"
                >
            </div>

            <div class="sm:col-span-4">
                <select name="category" class="w-full rounded-xl bg-slate-50 px-4 py-2.5 text-xs text-slate-800 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium">
                    <option value="">-- Tất cả danh mục --</option>
                    <?php foreach ($categoriesList as $cat): ?>
                        <?php $cName = (string) ($cat['category'] ?? ''); ?>
                        <option value="<?= e($cName); ?>" <?= $categoryFilter === $cName ? 'selected' : ''; ?>><?= e($cName); ?> (<?= (int) ($cat['total_items'] ?? 0); ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="sm:col-span-2 flex gap-2">
                <button type="submit" class="w-full rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition">
                    Lọc dữ liệu
                </button>
                <?php if ($search !== '' || $categoryFilter !== ''): ?>
                    <a href="<?= e(page_url('manage-faqs')); ?>" class="px-3 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold transition flex items-center justify-center">
                        ↺
                    </a>
                <?php endif; ?>
            </div>
        </form>

        <!-- FAQs Data Table -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                            <th class="p-4 w-12 text-center">STT</th>
                            <th class="p-4 w-36">Danh mục</th>
                            <th class="p-4">Câu hỏi & Nội dung trả lời</th>
                            <th class="p-4 w-20 text-center">Thứ tự</th>
                            <th class="p-4 w-28 text-center">Trạng thái</th>
                            <th class="p-4 w-36 text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($items)): ?>
                            <tr>
                                <td colspan="6" class="p-12 text-center text-slate-400">
                                    <div class="text-3xl mb-2">📂</div>
                                    Không có câu hỏi FAQ nào. Hãy bấm "Thêm Câu Hỏi Mới" để tạo đầu tiên.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($items as $idx => $row): ?>
                                <?php 
                                $id = (int) ($row['id'] ?? 0);
                                $cat = (string) ($row['category'] ?? '');
                                $q = (string) ($row['question'] ?? '');
                                $a = (string) ($row['answer'] ?? '');
                                $order = (int) ($row['sort_order'] ?? 0);
                                $active = !empty($row['is_active']);
                                ?>
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="p-4 text-center font-bold text-slate-400">
                                        <?= ($page - 1) * 20 + $idx + 1; ?>
                                    </td>
                                    
                                    <td class="p-4">
                                        <span class="inline-block px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 font-bold text-[11px] border border-indigo-100">
                                            <?= e($cat); ?>
                                        </span>
                                    </td>

                                    <td class="p-4 space-y-1">
                                        <div class="font-black text-slate-900 text-sm">
                                            <?= e($q); ?>
                                        </div>
                                        <div class="text-slate-500 line-clamp-2 leading-relaxed">
                                            <?= e($a); ?>
                                        </div>
                                    </td>

                                    <td class="p-4 text-center font-bold text-slate-600">
                                        <?= $order; ?>
                                    </td>

                                    <td class="p-4 text-center">
                                        <form method="POST" action="/api/index.php?resource=faqs&method=toggle-active" class="inline">
                                            <?= csrf_input(); ?>
                                            <input type="hidden" name="id" value="<?= $id; ?>">
                                            <button type="submit" class="px-3 py-1 rounded-full text-[11px] font-extrabold border transition <?= $active ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 border-slate-200 hover:bg-slate-200' ?>">
                                                <?= $active ? '● Hiển thị' : '○ Đang ẩn'; ?>
                                            </button>
                                        </form>
                                    </td>

                                    <td class="p-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <button 
                                                type="button" 
                                                onclick='editFaq(<?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)' 
                                                class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-slate-700 font-bold transition text-[11px]"
                                            >
                                                ✏️ Sửa
                                            </button>

                                            <form method="POST" action="/api/index.php?resource=faqs&method=delete" onsubmit="return confirm('Bạn có chắc chắn muốn xóa câu hỏi này?');" class="inline">
                                                <?= csrf_input(); ?>
                                                <input type="hidden" name="id" value="<?= $id; ?>">
                                                <button type="submit" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-rose-50 hover:text-rose-600 text-slate-700 font-bold transition text-[11px]">
                                                    🗑️ Xóa
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="p-4 border-t border-slate-200 bg-slate-50 flex items-center justify-between text-xs">
                    <span class="text-slate-500">Hiển thị trang <?= $page; ?> / <?= $totalPages; ?> (Tổng <?= $total; ?> câu hỏi)</span>
                    <div class="flex items-center gap-1.5">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="<?= e(page_url('manage-faqs', ['p' => $i, 'search' => $search, 'category' => $categoryFilter])); ?>" class="px-3 py-1.5 rounded-xl font-bold transition <?= $i === $page ? 'bg-indigo-600 text-white' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-100' ?>">
                                <?= $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<!-- Modal Add/Edit FAQ -->
<div id="faqModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl border border-slate-100 space-y-6 relative animate-fadeIn">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <h3 class="text-xl font-black text-slate-900" id="faqModalTitle">Thêm Câu Hỏi Thường Gặp Mới</h3>
            <button type="button" onclick="closeFaqModal()" class="text-slate-400 hover:text-slate-600 text-2xl font-bold leading-none">&times;</button>
        </div>

        <form method="POST" action="/api/index.php?resource=faqs&method=save" class="space-y-5">
            <?= csrf_input(); ?>
            <input type="hidden" name="id" id="faq_id" value="0">

            <div class="grid sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-700 uppercase">Danh Mục *</label>
                    <input 
                        type="text" 
                        name="category" 
                        id="faq_category" 
                        required 
                        list="categorySuggestions"
                        placeholder="VD: Khóa học & Học phí" 
                        class="w-full rounded-xl bg-slate-50 px-4 py-2.5 text-xs text-slate-800 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium"
                    >
                    <datalist id="categorySuggestions">
                        <option value="Độ tuổi & Đầu vào">
                        <option value="Môi trường học tập">
                        <option value="Đăng ký & Trải nghiệm">
                        <option value="Khóa học & Học phí">
                        <option value="Lịch học & Chính sách">
                        <option value="Góc phụ huynh">
                        <option value="Chứng chỉ quốc tế">
                    </datalist>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-700 uppercase">Thứ tự sắp xếp (Sort Order)</label>
                    <input 
                        type="number" 
                        name="sort_order" 
                        id="faq_sort_order" 
                        value="0" 
                        class="w-full rounded-xl bg-slate-50 px-4 py-2.5 text-xs text-slate-800 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium"
                    >
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-700 uppercase">Câu hỏi (Question) *</label>
                <input 
                    type="text" 
                    name="question" 
                    id="faq_question" 
                    required 
                    placeholder="VD: Bao nhiêu tuổi học được?" 
                    class="w-full rounded-xl bg-slate-50 px-4 py-2.5 text-xs text-slate-800 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium"
                >
            </div>

            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-700 uppercase">Nội dung trả lời (Answer) *</label>
                <textarea 
                    name="answer" 
                    id="faq_answer" 
                    rows="5" 
                    required 
                    placeholder="Nhập chi tiết nội dung giải đáp cho phụ huynh / học viên..." 
                    class="w-full rounded-xl bg-slate-50 px-4 py-2.5 text-xs text-slate-800 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium leading-relaxed"
                ></textarea>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" name="is_active" id="faq_is_active" value="1" checked class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500">
                <label for="faq_is_active" class="text-xs font-bold text-slate-700">Hiển thị câu hỏi này trên trang web public</label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeFaqModal()" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                    Hủy bỏ
                </button>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black uppercase tracking-wider shadow-lg shadow-indigo-600/30 transition">
                    Lưu Thông Tin
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openFaqModal() {
    document.getElementById('faqModalTitle').innerText = 'Thêm Câu Hỏi Thường Gặp Mới';
    document.getElementById('faq_id').value = '0';
    document.getElementById('faq_category').value = '';
    document.getElementById('faq_question').value = '';
    document.getElementById('faq_answer').value = '';
    document.getElementById('faq_sort_order').value = '0';
    document.getElementById('faq_is_active').checked = true;

    document.getElementById('faqModal').classList.remove('hidden');
}

function closeFaqModal() {
    document.getElementById('faqModal').classList.add('hidden');
}

function editFaq(item) {
    if (!item) return;

    document.getElementById('faqModalTitle').innerText = 'Chỉnh Sửa Câu Hỏi FAQ #' + item.id;
    document.getElementById('faq_id').value = item.id || 0;
    document.getElementById('faq_category').value = item.category || '';
    document.getElementById('faq_question').value = item.question || '';
    document.getElementById('faq_answer').value = item.answer || '';
    document.getElementById('faq_sort_order').value = item.sort_order || 0;
    document.getElementById('faq_is_active').checked = (parseInt(item.is_active) === 1);

    document.getElementById('faqModal').classList.remove('hidden');
}

<?php if ($editingFaq): ?>
document.addEventListener('DOMContentLoaded', function() {
    editFaq(<?= json_encode($editingFaq, JSON_HEX_APOS | JSON_HEX_QUOT); ?>);
});
<?php endif; ?>
</script>
