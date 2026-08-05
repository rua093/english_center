<?php
declare(strict_types=1);

require_once __DIR__ . '/../../models/FaqModel.php';

$faqModel = new FaqModel();
$categoriesData = $faqModel->getCategories(true);

$selectedCategory = trim((string) ($_GET['category'] ?? 'ALL'));
$searchQuery = trim((string) ($_GET['search'] ?? ''));

$faqsResult = $faqModel->getAllFaqs($selectedCategory === 'ALL' ? null : $selectedCategory, true, $searchQuery, 1, 200);
$faqItems = $faqsResult['items'] ?? [];

// Category icon map
$categoryIcons = [
    'ALL' => '✨',
    'Độ tuổi & Đầu vào' => '👶',
    'Môi trường học tập' => '🏫',
    'Đăng ký & Trải nghiệm' => '🎁',
    'Khóa học & Học phí' => '💰',
    'Lịch học & Chính sách' => '📅',
    'Góc phụ huynh' => '🤝',
    'Giải đáp các câu hỏi thường gặp' => '❓',
    'Chứng chỉ quốc tế' => '🏆',
];
?>

<!-- Hero Banner (Vibrant Modern Luxury Theme) -->
<div class="relative overflow-hidden bg-gradient-to-br from-blue-950 via-indigo-950 to-slate-950 py-16 sm:py-24 text-white">
    <!-- Subtle Grid Overlay -->
    <div class="absolute inset-0 bg-[linear-gradient(to_right,#ffffff0a_1px,transparent_1px),linear-gradient(to_bottom,#ffffff0a_1px,transparent_1px)] bg-[size:32px_32px] pointer-events-none"></div>

    <!-- Ambient glowing colorful orbs -->
    <div class="absolute -top-24 -left-24 h-96 w-96 rounded-full bg-cyan-500/30 blur-[100px] pointer-events-none"></div>
    <div class="absolute top-1/3 -right-24 h-96 w-96 rounded-full bg-purple-500/25 blur-[100px] pointer-events-none"></div>
    <div class="absolute -bottom-24 left-1/3 h-80 w-80 rounded-full bg-amber-400/15 blur-[90px] pointer-events-none"></div>

    <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-6">
        <!-- Shimmer Tag Badge -->
        <span class="inline-flex items-center gap-2.5 rounded-full bg-white/10 px-6 sm:px-7 py-2 text-xs font-black text-sky-200 border border-white/20 shadow-lg backdrop-blur-md max-w-full">
            <span class="flex h-2 w-2 shrink-0 rounded-full bg-emerald-400 animate-pulse"></span>
            <span class="truncate"><?= e(t('faq.kicker')); ?></span>
        </span>
        
        <h1 class="text-3xl font-black sm:text-5xl lg:text-6xl tracking-tight leading-tight bg-gradient-to-r from-white via-sky-100 to-indigo-200 bg-clip-text text-transparent drop-shadow-sm">
            <?= e(t('faq.title')); ?>
        </h1>
        
        <p class="max-w-2xl mx-auto text-sm sm:text-base text-slate-300 leading-relaxed font-medium">
            <?= e(t('faq.subtitle')); ?>
        </p>

        <!-- Glassmorphic Search Box -->
        <div class="max-w-2xl mx-auto pt-4">
            <form method="GET" action="<?= e(page_url('faq')); ?>" class="relative group">
                <?php if ($selectedCategory !== 'ALL'): ?>
                    <input type="hidden" name="category" value="<?= e($selectedCategory); ?>">
                <?php endif; ?>
                <div class="relative rounded-2xl bg-white/10 p-1.5 border border-white/25 backdrop-blur-2xl shadow-2xl transition-all duration-300 group-focus-within:border-sky-300 group-focus-within:ring-4 group-focus-within:ring-sky-400/20 group-focus-within:bg-slate-900/90">
                    <input 
                        type="text" 
                        id="faqSearchInput"
                        name="search" 
                        value="<?= e($searchQuery); ?>"
                        placeholder="<?= e(t('faq.search_placeholder')); ?>"
                        class="w-full rounded-xl bg-transparent px-5 py-3.5 pl-12 text-sm text-white placeholder-slate-300 focus:outline-none font-medium"
                    >
                    <div class="absolute left-5 top-1/2 -translate-y-1/2 text-sky-300 group-focus-within:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <?php if ($searchQuery !== ''): ?>
                        <a href="<?= e(page_url('faq')); ?>" class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-bold bg-white/20 hover:bg-white/30 text-white px-3 py-1.5 rounded-xl transition">
                            <?= e(t('faq.clear_search')); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Quick Feature Chips -->
        <div class="pt-2 flex flex-wrap items-center justify-center gap-3 text-xs text-sky-100/90 font-semibold">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/5 border border-white/10 backdrop-blur-md">
                <?= e(t('faq.chip_support')); ?>
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/5 border border-white/10 backdrop-blur-md">
                <?= e(t('faq.chip_verified')); ?>
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/5 border border-white/10 backdrop-blur-md">
                <?= e(t('faq.chip_categories')); ?>
            </span>
        </div>
    </div>
</div>

<main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-10">
    
    <!-- Category Filter Tabs -->
    <div class="flex items-center gap-2.5 overflow-x-auto pb-3 scrollbar-none">
        <a href="<?= e(page_url('faq')); ?>" class="px-5 py-2.5 rounded-2xl text-xs font-extrabold whitespace-nowrap transition-all border <?= $selectedCategory === 'ALL' ? 'bg-indigo-600 text-white border-indigo-600 shadow-md shadow-indigo-600/20' : 'bg-white text-slate-700 border-slate-200 hover:border-indigo-300 hover:bg-indigo-50/50' ?>">
            ✨ <?= e(t('faq.all_categories')); ?> (<?= (int) ($faqsResult['total'] ?? count($faqItems)); ?>)
        </a>
        
        <?php foreach ($categoriesData as $cat): ?>
            <?php 
            $catName = (string) ($cat['category'] ?? '');
            $icon = $categoryIcons[$catName] ?? '💬';
            $isSelected = ($selectedCategory === $catName);
            ?>
            <a href="<?= e(page_url('faq', ['category' => $catName])); ?>" class="px-4 py-2.5 rounded-2xl text-xs font-extrabold whitespace-nowrap transition-all border flex items-center gap-2 <?= $isSelected ? 'bg-indigo-600 text-white border-indigo-600 shadow-md shadow-indigo-600/20' : 'bg-white text-slate-700 border-slate-200 hover:border-indigo-300 hover:bg-indigo-50/50' ?>">
                <span><?= $icon; ?></span>
                <span><?= e($catName); ?></span>
                <span class="px-2 py-0.5 rounded-full text-[10px] <?= $isSelected ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500' ?>"><?= (int) ($cat['total_items'] ?? 0); ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- FAQ Accordion List -->
    <div class="space-y-4" id="faqAccordionContainer">
        <?php if (empty($faqItems)): ?>
            <div class="text-center py-16 bg-white rounded-3xl border border-slate-200 p-8 shadow-sm space-y-4">
                <div class="text-5xl">🔍</div>
                <h3 class="text-lg font-black text-slate-800"><?= e(t('faq.no_results_title')); ?></h3>
                <p class="text-xs text-slate-500 max-w-md mx-auto">
                    <?= e(t('faq.no_results_desc')); ?>
                </p>
                <a href="<?= e(page_url('register-consultation')); ?>" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black uppercase tracking-wider shadow-lg shadow-indigo-600/30 transition">
                    <?= e(t('faq.contact_now')); ?> &rarr;
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($faqItems as $index => $item): ?>
                <?php 
                $faqId = (int) ($item['id'] ?? 0);
                $question = (string) ($item['question'] ?? '');
                $answer = (string) ($item['answer'] ?? '');
                $category = (string) ($item['category'] ?? 'General');
                $icon = $categoryIcons[$category] ?? '❓';
                ?>
                <div class="faq-item rounded-3xl bg-white border border-slate-200/80 shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden group">
                    <button 
                        type="button" 
                        onclick="toggleFaq(<?= $faqId; ?>)" 
                        class="w-full p-6 sm:p-7 text-left flex items-start justify-between gap-4 focus:outline-none focus:bg-slate-50/80 transition"
                        aria-expanded="false"
                        id="faq-btn-<?= $faqId; ?>"
                    >
                        <div class="flex items-start gap-4">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 font-extrabold text-lg border border-indigo-100 group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-300">
                                <?= $icon; ?>
                            </span>
                            <div class="space-y-1">
                                <span class="inline-block rounded-full bg-slate-100 px-3 py-0.5 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                                    <?= e($category); ?>
                                </span>
                                <h3 class="text-base sm:text-lg font-black text-slate-900 leading-snug group-hover:text-indigo-600 transition-colors">
                                    <?= e($question); ?>
                                </h3>
                            </div>
                        </div>

                        <div class="shrink-0 pt-1 text-slate-400 group-hover:text-indigo-600 transition">
                            <svg id="faq-arrow-<?= $faqId; ?>" class="w-6 h-6 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </button>

                    <div id="faq-ans-<?= $faqId; ?>" class="hidden px-6 sm:px-7 pb-7 pt-2 text-xs sm:text-sm text-slate-600 leading-relaxed border-t border-slate-100/60 bg-slate-50/40">
                        <div class="prose prose-slate max-w-none space-y-3 font-medium">
                            <?= nl2br(e($answer)); ?>
                        </div>
                        
                        <div class="mt-5 pt-4 border-t border-slate-200/60 flex items-center justify-between text-xs text-slate-400">
                            <span>Vẫn còn thắc mắc? Chi tiết xin liên hệ Hotline: <strong class="text-slate-700">0899 925 259</strong></span>
                            <button 
                                type="button" 
                                onclick="copyFaqText(`<?= e(addslashes($question)); ?>\n<?= e(addslashes($answer)); ?>`, this)"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-slate-600 font-bold hover:bg-indigo-50 hover:text-indigo-600 hover:border-indigo-200 transition"
                            >
                                <?= e(t('faq.copy_button')); ?>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- CTA Section -->
    <div class="rounded-3xl bg-gradient-to-r from-indigo-900 via-indigo-800 to-slate-900 p-8 sm:p-12 text-white shadow-2xl relative overflow-hidden flex flex-col md:flex-row items-center justify-between gap-8">
        <div class="absolute -right-20 -bottom-20 h-64 w-64 rounded-full bg-rose-500/20 blur-3xl pointer-events-none"></div>
        <div class="space-y-3 text-left max-w-xl">
            <span class="rounded-full bg-white/10 px-3.5 py-1 text-xs font-bold text-indigo-200 border border-white/10">
                💬 <?= e(t('faq.chip_support')); ?>
            </span>
            <h2 class="text-2xl sm:text-3xl font-black"><?= e(t('faq.need_help_title')); ?></h2>
            <p class="text-xs sm:text-sm text-indigo-100/80 leading-relaxed">
                <?= e(t('faq.need_help_desc')); ?>
            </p>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto shrink-0">
            <a href="<?= e(page_url('register-consultation')); ?>" class="px-8 py-4 rounded-2xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-black uppercase tracking-wider shadow-xl shadow-rose-600/30 transition text-center transform hover:-translate-y-0.5">
                <?= e(t('faq.contact_now')); ?> &rarr;
            </a>
            <a href="tel:0899925259" class="px-6 py-4 rounded-2xl bg-white/10 hover:bg-white/20 text-white text-xs font-bold border border-white/20 backdrop-blur-md transition text-center">
                📞 Hotline: 0899 925 259
            </a>
        </div>
    </div>
</main>

<script>
function toggleFaq(id) {
    const ans = document.getElementById('faq-ans-' + id);
    const arrow = document.getElementById('faq-arrow-' + id);
    const btn = document.getElementById('faq-btn-' + id);

    if (!ans || !arrow) return;

    const isHidden = ans.classList.contains('hidden');

    if (isHidden) {
        ans.classList.remove('hidden');
        arrow.classList.add('rotate-180');
        if (btn) btn.setAttribute('aria-expanded', 'true');
    } else {
        ans.classList.add('hidden');
        arrow.classList.remove('rotate-180');
        if (btn) btn.setAttribute('aria-expanded', 'false');
    }
}

function copyFaqText(text, btn) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            const original = btn.innerHTML;
            btn.innerHTML = '✅ Đã sao chép!';
            setTimeout(() => { btn.innerHTML = original; }, 2000);
        });
    } else {
        alert('Đã sao chép câu trả lời vào bộ nhớ tạm.');
    }
}
</script>
