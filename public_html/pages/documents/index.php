<?php
// Dữ liệu mẫu (10 item để test giao diện)
$documents = array_fill(0, 10, [
    'title' => 'Trọn bộ 1000 từ vựng IELTS cốt lõi',
    'category' => 'IELTS',
    'type' => 'PDF',
    'pages' => 45,
    'downloads' => '1.2k',
    'image' => 'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?q=80&w=400&auto=format&fit=crop',
    'is_new' => true
]);
?>

<style>
    .resource-card:hover { transform: translateY(-5px); border-color: #10b981; }
    .btn-download-gradient { background: linear-gradient(135deg, #065f46 0%, #10b981 100%); }
    .compact-text { font-size: 0.8rem; line-height: 1.25rem; }
    .pagination-btn:hover { background: #10b981; color: white; border-color: #10b981; }
    .pagination-active { background: #065f46; color: white; border-color: #065f46; }
    
    /* Style cho Checkbox Custom */
    .filter-checkbox:checked + div { background-color: #10b981; border-color: #10b981; }
    .filter-checkbox:checked + div svg { opacity: 1; transform: scale(1); }
</style>

<section class="relative pt-12 pb-16 overflow-hidden">
    <div class="absolute top-0 left-0 w-full h-full pointer-events-none -z-10">
        <div class="absolute top-0 right-0 w-[300px] h-[300px] bg-emerald-50/50 rounded-full blur-[80px]"></div>
        <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-blue-50/40 rounded-full blur-[100px]"></div>
    </div>

    <div class="mx-auto px-4 w-[96%] max-w-[1700px]"> 
        
        <div class="text-center mb-10" data-aos="fade-down">
            <h1 class="text-3xl md:text-4xl font-black text-slate-900 mb-3">
                Kho Tài Liệu <span class="text-emerald-600">Học Tập</span>
            </h1>
            <p class="text-slate-500 text-sm font-medium">Tìm kiếm, lọc và tải xuống hàng ngàn tài liệu miễn phí</p>
        </div>

        <div class="flex flex-col lg:flex-row gap-8 items-start">
            
            <aside class="w-full lg:w-[260px] xl:w-[300px] shrink-0 lg:sticky lg:top-28 z-20" data-aos="fade-right">
                <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-xl shadow-slate-200/40">
                    
                    <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-100">
                        <h2 class="text-lg font-black text-slate-800 flex items-center gap-2">
                            <i class="fa-solid fa-filter text-emerald-500"></i> Bộ lọc
                        </h2>
                        <button class="text-[10px] font-bold text-rose-500 hover:text-rose-600 uppercase tracking-wider bg-rose-50 px-2 py-1 rounded-md">Bỏ lọc</button>
                    </div>

                    <form class="space-y-6">
                        <div>
                            <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Chủ đề học</h3>
                            <div class="space-y-2.5">
                                <?php 
                                $categories = ['IELTS', 'TOEIC', 'Giao tiếp', 'Ngữ pháp', 'Từ vựng'];
                                foreach($categories as $cat): 
                                ?>
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <div class="relative flex items-center justify-center">
                                        <input type="checkbox" class="filter-checkbox peer sr-only" name="category[]" value="<?= $cat ?>">
                                        <div class="w-5 h-5 rounded-[6px] border-2 border-slate-300 bg-white group-hover:border-emerald-400 transition-all flex items-center justify-center">
                                            <svg class="w-3 h-3 text-white opacity-0 transform scale-50 transition-all duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                        </div>
                                    </div>
                                    <span class="text-sm font-semibold text-slate-600 group-hover:text-emerald-600 transition-colors"><?= $cat ?></span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-100">
                            <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Trình độ</h3>
                            <div class="space-y-2.5">
                                <?php 
                                $levels = ['Cơ bản (Mất gốc)', 'Trung cấp', 'Nâng cao'];
                                foreach($levels as $lvl): 
                                ?>
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <div class="relative flex items-center justify-center">
                                        <input type="checkbox" class="filter-checkbox peer sr-only" name="level[]" value="<?= $lvl ?>">
                                        <div class="w-5 h-5 rounded-[6px] border-2 border-slate-300 bg-white group-hover:border-emerald-400 transition-all flex items-center justify-center">
                                            <svg class="w-3 h-3 text-white opacity-0 transform scale-50 transition-all duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                        </div>
                                    </div>
                                    <span class="text-sm font-semibold text-slate-600 group-hover:text-emerald-600 transition-colors"><?= $lvl ?></span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-100">
                            <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Định dạng</h3>
                            <div class="grid grid-cols-2 gap-2">
                                <label class="cursor-pointer group">
                                    <input type="checkbox" class="peer sr-only" name="type[]" value="PDF">
                                    <div class="text-center py-2 rounded-xl border border-slate-200 bg-slate-50 text-xs font-bold text-slate-500 group-hover:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-600 peer-checked:border-emerald-500 transition-all">
                                        <i class="fa-solid fa-file-pdf mr-1 text-rose-500"></i> PDF
                                    </div>
                                </label>
                                <label class="cursor-pointer group">
                                    <input type="checkbox" class="peer sr-only" name="type[]" value="DOCX">
                                    <div class="text-center py-2 rounded-xl border border-slate-200 bg-slate-50 text-xs font-bold text-slate-500 group-hover:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-600 peer-checked:border-emerald-500 transition-all">
                                        <i class="fa-solid fa-file-word mr-1 text-blue-500"></i> DOCX
                                    </div>
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="w-full mt-4 bg-slate-900 hover:bg-emerald-600 text-white font-black py-3.5 rounded-xl shadow-lg transition-all text-sm uppercase tracking-wider flex justify-center items-center gap-2">
                            Áp dụng lọc <span class="w-1.5 h-1.5 rounded-full bg-lime-400 animate-pulse"></span>
                        </button>
                    </form>

                </div>
            </aside>

            <div class="flex-1 w-full min-w-0" data-aos="fade-up" data-aos-delay="100">
                
                <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6">
                    <p class="text-sm font-bold text-slate-500">Tìm thấy <span class="text-emerald-600 font-black">150+</span> tài liệu</p>
                    
                    <div class="relative w-full sm:w-72">
                        <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input type="text" placeholder="Tìm tài liệu theo tên..." class="w-full pl-10 pr-4 py-3 rounded-2xl bg-white border border-slate-200 outline-none text-sm font-bold focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all shadow-sm">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-5">
                    
                    <?php foreach($documents as $doc): ?>
                    <article class="resource-card group relative bg-white rounded-3xl border border-slate-100 p-3 transition-all duration-300 shadow-lg shadow-slate-200/40">
                        <div class="relative rounded-2xl overflow-hidden aspect-[4/3] mb-4 bg-slate-100">
                            <img src="<?= $doc['image'] ?>" alt="<?= $doc['title'] ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            <?php if($doc['is_new']): ?>
                            <span class="absolute top-2 right-2 bg-rose-500 text-white px-2 py-0.5 rounded-lg text-[9px] font-black uppercase shadow-md">Mới</span>
                            <?php endif; ?>
                            <div class="absolute bottom-2 left-2 flex gap-1">
                                <span class="bg-white/90 backdrop-blur-sm text-emerald-700 px-2 py-1 rounded-md text-[9px] font-black uppercase">
                                    <?= $doc['category'] ?>
                                </span>
                            </div>
                        </div>

                        <div class="px-1 flex flex-col h-[calc(100%-140px)]">
                            <h3 class="text-[13px] font-black text-slate-800 mb-2 leading-snug line-clamp-2 h-10 group-hover:text-emerald-600 transition-colors">
                                <?= $doc['title'] ?>
                            </h3>
                            
                            <div class="flex items-center justify-between mt-auto mb-3 text-slate-400 text-[10px] font-bold border-t border-slate-50 pt-3">
                                <span class="flex items-center gap-1" title="Số trang"><i class="fa-regular fa-file-lines"></i> <?= $doc['pages'] ?>tr</span>
                                <span class="flex items-center gap-1" title="Lượt tải"><i class="fa-solid fa-download"></i> <?= $doc['downloads'] ?></span>
                                <span class="text-emerald-500 uppercase font-black bg-emerald-50 px-1.5 py-0.5 rounded"><?= $doc['type'] ?></span>
                            </div>

                            <a href="#" class="btn-download-gradient w-full py-2.5 rounded-xl text-white text-[11px] font-black flex items-center justify-center gap-2 transition-all hover:shadow-md hover:-translate-y-0.5">
                                Tải ngay <i class="fa-solid fa-arrow-down-to-bracket text-[10px]"></i>
                            </a>
                        </div>
                    </article>
                    <?php endforeach; ?>

                </div>

                <div class="mt-12 flex items-center justify-center gap-2">
                    <button class="w-10 h-10 rounded-xl border border-slate-200 flex items-center justify-center text-slate-400 hover:bg-white hover:text-emerald-600 hover:border-emerald-200 transition-all shadow-sm">
                        <i class="fa-solid fa-chevron-left text-xs"></i>
                    </button>
                    <button class="w-10 h-10 rounded-xl flex items-center justify-center text-sm font-black pagination-active shadow-md">1</button>
                    <button class="w-10 h-10 rounded-xl border border-slate-200 flex items-center justify-center text-sm font-bold text-slate-500 bg-white pagination-btn transition-all shadow-sm">2</button>
                    <button class="w-10 h-10 rounded-xl border border-slate-200 flex items-center justify-center text-sm font-bold text-slate-500 bg-white pagination-btn transition-all shadow-sm">3</button>
                    <span class="text-slate-300 font-bold px-1">...</span>
                    <button class="w-10 h-10 rounded-xl border border-slate-200 flex items-center justify-center text-sm font-bold text-slate-500 bg-white pagination-btn transition-all shadow-sm">12</button>
                    <button class="w-10 h-10 rounded-xl border border-slate-200 flex items-center justify-center text-slate-400 hover:bg-white hover:text-emerald-600 hover:border-emerald-200 transition-all shadow-sm">
                        <i class="fa-solid fa-chevron-right text-xs"></i>
                    </button>
                </div>

            </div>
        </div>
    </div>
</section>
