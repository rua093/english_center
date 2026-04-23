<?php
$activities = [
    [
        'id' => 1,
        'title' => 'Trại hè Tiếng Anh: Khám phá rừng xanh 2026',
        'category' => 'Trại hè',
        'date' => '15/06/2026',
        'location' => 'Khu du lịch sinh thái, Đà Nẵng',
        'image' => 'https://images.unsplash.com/photo-1533227268428-f9ed0900fb3b?w=600&q=80',
        'tag' => 'Hot'
    ],
    [
        'id' => 2,
        'title' => 'Cuộc thi Hùng biện Tiếng Anh - Nhuệ Minh Cup',
        'category' => 'Cuộc thi',
        'date' => '20/07/2026',
        'location' => 'Hội trường Trung tâm',
        'image' => 'https://images.unsplash.com/photo-1475721027785-f74eccf877e2?w=600&q=80',
        'tag' => 'Giải thưởng lớn'
    ],
    // Thêm các hoạt động khác tại đây...
];
?>

<style>
    .activity-card:hover { transform: translateY(-10px); }
    .text-gradient-red-green { background: linear-gradient(to right, #e11d48, #10b981); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
</style>

<section class="py-12 md:py-16">
    <div class="container mx-auto px-4 max-w-[1400px]">
        
        <div class="text-center mb-12" data-aos="fade-up">
            <span class="px-4 py-1.5 rounded-full bg-rose-50 text-rose-600 text-[10px] font-black uppercase tracking-widest border border-rose-100">Học mà chơi - Chơi mà học</span>
            <h1 class="text-3xl md:text-4xl font-black text-slate-900 mt-5 mb-3 uppercase">
                Hoạt động <span class="text-gradient-red-green">Ngoại khóa</span>
            </h1>
            <p class="text-slate-500 max-w-xl mx-auto font-medium text-sm md:text-base">Khám phá thế giới, rèn luyện kỹ năng mềm và tự tin giao tiếp cùng bạn bè quốc tế.</p>
        </div>

        <div class="flex flex-wrap justify-center gap-3 mb-10">
            <button class="px-6 py-2.5 rounded-2xl bg-rose-600 text-white font-black text-[10px] uppercase shadow-lg shadow-rose-600/20">Tất cả</button>
            <button class="px-6 py-2.5 rounded-2xl bg-slate-50 text-slate-600 font-bold text-[10px] uppercase hover:bg-emerald-50 hover:text-emerald-600 transition-all">Dã ngoại</button>
            <button class="px-6 py-2.5 rounded-2xl bg-slate-50 text-slate-600 font-bold text-[10px] uppercase hover:bg-emerald-50 hover:text-emerald-600 transition-all">Kỹ năng sống</button>
            <button class="px-6 py-2.5 rounded-2xl bg-slate-50 text-slate-600 font-bold text-[10px] uppercase hover:bg-emerald-50 hover:text-emerald-600 transition-all">Từ thiện</button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach($activities as $act): ?>
            <a href="<?= e(page_url('activities-home-detail', ['id' => (int) $act['id']])); ?>" class="activity-card group block h-full bg-white rounded-[2rem] overflow-hidden border border-slate-100 shadow-xl shadow-slate-200/40 transition-all duration-500 hover:shadow-2xl focus:outline-none focus-visible:ring-4 focus-visible:ring-rose-100">
                <article>
                <div class="relative h-52 overflow-hidden">
                    <img src="<?= $act['image'] ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                    <div class="absolute top-4 left-4">
                        <span class="bg-white/90 backdrop-blur-md text-rose-600 px-3 py-1 rounded-full text-[10px] font-black uppercase shadow-sm">
                            <?= $act['category'] ?>
                        </span>
                    </div>
                </div>
                <div class="p-6">
                    <div class="flex items-center gap-3 text-emerald-600 text-[11px] font-bold mb-3">
                        <i class="fa-solid fa-calendar-day"></i> <?= $act['date'] ?>
                    </div>
                    <h3 class="text-lg font-black text-slate-800 mb-3 leading-tight group-hover:text-rose-600 transition-colors">
                        <?= $act['title'] ?>
                    </h3>
                    <p class="text-slate-400 text-xs font-medium mb-5 flex items-center gap-2">
                        <i class="fa-solid fa-location-dot"></i> <?= $act['location'] ?>
                    </p>
                    <div class="inline-flex items-center gap-2 font-black text-slate-900 text-sm">
                        Xem chi tiết
                        <span class="w-7 h-7 rounded-full border-2 border-slate-100 flex items-center justify-center group-hover:bg-emerald-500 group-hover:text-white group-hover:border-emerald-500 transition-all">
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </span>
                    </div>
                </div>
                </article>
            </a>
            <?php endforeach; ?>
        </div>

        <div class="mt-14 flex justify-center gap-2">
            <button class="w-11 h-11 rounded-2xl bg-rose-600 text-white font-black shadow-lg">1</button>
            <button class="w-11 h-11 rounded-2xl bg-white border border-slate-100 text-slate-400 font-bold hover:text-emerald-600 transition-all">2</button>
            <button class="w-11 h-11 rounded-2xl bg-white border border-slate-100 text-slate-400 font-bold hover:text-emerald-600 transition-all"><i class="fa-solid fa-chevron-right text-xs"></i></button>
        </div>
    </div>
</section>