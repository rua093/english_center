<?php
declare(strict_types=1);

// Cambridge A2 Flyers Page
?>

<!-- Cambridge Sub-level Navigation Bar -->
<div class="bg-slate-900 border-b border-slate-800 text-xs py-3 sticky top-[72px] z-40 backdrop-blur-md bg-slate-900/90">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between overflow-x-auto gap-4 scrollbar-none">
        <span class="text-slate-400 font-bold whitespace-nowrap">Các cấp độ Cambridge:</span>
        <div class="flex items-center gap-2">
            <a href="<?= e(page_url('cambridge')); ?>" class="px-3 py-1.5 rounded-full text-slate-300 hover:text-white hover:bg-slate-800 transition whitespace-nowrap font-medium">🎓 Overview</a>
            <a href="<?= e(page_url('cambridge-starters')); ?>" class="px-3 py-1.5 rounded-full text-slate-300 hover:text-amber-400 hover:bg-slate-800 transition whitespace-nowrap font-medium">🟡 Pre-A1 Starters</a>
            <a href="<?= e(page_url('cambridge-movers')); ?>" class="px-3 py-1.5 rounded-full text-slate-300 hover:text-emerald-400 hover:bg-slate-800 transition whitespace-nowrap font-medium">🟢 A1 Movers</a>
            <a href="<?= e(page_url('cambridge-flyers')); ?>" class="px-3 py-1.5 rounded-full bg-sky-500 text-slate-950 font-black shadow-sm transition whitespace-nowrap">🔵 A2 Flyers</a>
            <a href="<?= e(page_url('cambridge-ket')); ?>" class="px-3 py-1.5 rounded-full text-slate-300 hover:text-indigo-400 hover:bg-slate-800 transition whitespace-nowrap font-medium">🟣 A2 KET</a>
            <a href="<?= e(page_url('cambridge-pet')); ?>" class="px-3 py-1.5 rounded-full text-slate-300 hover:text-purple-400 hover:bg-slate-800 transition whitespace-nowrap font-medium">🔴 B1 PET</a>
        </div>
    </div>
</div>

<!-- Hero Banner -->
<div class="relative overflow-hidden bg-gradient-to-br from-sky-600 via-sky-700 to-slate-950 py-16 sm:py-24 text-white">
    <div class="absolute -top-40 -left-40 h-96 w-96 rounded-full bg-sky-400/20 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 h-96 w-96 rounded-full bg-sky-500/10 blur-3xl pointer-events-none"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row items-center justify-between gap-10">
            <div class="max-w-2xl text-left">
                <div class="flex items-center gap-3 mb-4 flex-wrap">
                    <span class="rounded-full bg-sky-400/20 px-3.5 py-1 text-xs font-black text-sky-300 border border-sky-300/30 backdrop-blur-md">
                        🚀 A2 Flyers (Young Learners)
                    </span>
                    <span class="text-xs font-semibold text-sky-100 bg-white/10 px-3 py-1 rounded-full border border-white/10">Dành cho trẻ 10 - 12 tuổi (Lớp 5 - 6)</span>
                </div>
                <h1 class="text-3xl font-black sm:text-5xl lg:text-6xl tracking-tight leading-tight">
                    Chứng Chỉ Cambridge Flyers
                </h1>
                <p class="mt-4 text-base sm:text-lg text-sky-100/90 leading-relaxed">
                    Cấp độ đỉnh cao thuộc hệ thống Young Learners (YLE). Giúp học sinh thành thạo tiếng Anh tương đương trình độ KET (A2), chuẩn bị hành trang vững chắc tuyển sinh trường chuyên, lớp chọn THCS.
                </p>

                <div class="mt-8 flex flex-wrap gap-4">
                    <a href="<?= e(page_url('home') . '#dang-ky-tu-van'); ?>" class="rounded-2xl bg-sky-400 px-7 py-3.5 text-xs sm:text-sm font-extrabold text-slate-950 shadow-xl shadow-sky-500/20 hover:bg-sky-300 transition-all transform hover:-translate-y-0.5">
                        Đăng ký thi thử Flyers miễn phí 🚀
                    </a>
                    <a href="<?= e(page_url('cambridge')); ?>" class="rounded-2xl bg-white/10 px-6 py-3.5 text-xs sm:text-sm font-bold text-white border border-white/20 hover:bg-white/20 backdrop-blur-md transition">
                        &larr; Xem lộ trình tổng quan
                    </a>
                </div>
            </div>

            <!-- Stats Badge Glass Card -->
            <div class="w-full lg:w-96 rounded-3xl bg-white/10 p-8 backdrop-blur-xl border border-white/20 text-center shadow-2xl space-y-6">
                <div class="inline-flex h-20 w-20 items-center justify-center rounded-3xl bg-sky-400 text-slate-950 font-black text-3xl shadow-lg">
                    🛡️ 15
                </div>
                <div>
                    <div class="text-xl font-black text-white">Tối Đa 15 Khiên Cambridge</div>
                    <p class="mt-2 text-xs text-sky-100/80 leading-relaxed">
                        Cấp độ Flyers tương đương với trình độ A2 trên Khung chiếu Châu Âu (CEFR).
                    </p>
                </div>
                <div class="pt-4 border-t border-white/15 grid grid-cols-2 gap-3 text-xs">
                    <div class="bg-white/10 p-2.5 rounded-xl">
                        <div class="text-sky-300 font-bold">Khung CEFR</div>
                        <div class="text-white font-extrabold">Trình độ A2</div>
                    </div>
                    <div class="bg-white/10 p-2.5 rounded-xl">
                        <div class="text-sky-300 font-bold">Thời gian học</div>
                        <div class="text-white font-extrabold">~250 Giờ</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 space-y-16">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
        <!-- Main Content (2 Cols) -->
        <div class="lg:col-span-2 space-y-12">
            
            <!-- 1. Đối tượng & Mục tiêu -->
            <section class="rounded-3xl bg-white p-8 sm:p-10 shadow-sm border border-slate-100">
                <div class="flex items-center gap-3.5 mb-6">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-100 text-sky-600 font-extrabold text-xl">🎯</span>
                    <div>
                        <span class="text-xs font-bold text-sky-600 uppercase tracking-wider">Mục Tiêu Khóa Học</span>
                        <h2 class="text-xl sm:text-2xl font-black text-slate-900">1. Đối Tượng & Năng Lực Đạt Được</h2>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    <div class="p-4 rounded-2xl bg-sky-50/60 border border-sky-100">
                        <div class="text-xs font-bold text-sky-700">👶 Độ tuổi phù hợp</div>
                        <div class="text-sm font-extrabold text-slate-900 mt-1">10 - 12 tuổi (Học sinh Lớp 5 - 6)</div>
                    </div>
                    <div class="p-4 rounded-2xl bg-sky-50/60 border border-sky-100">
                        <div class="text-xs font-bold text-sky-700">⏱️ Thời gian tích lũy</div>
                        <div class="text-sm font-extrabold text-slate-900 mt-1">~250 giờ học chuyên sâu</div>
                    </div>
                </div>

                <div class="space-y-3 text-xs sm:text-sm text-slate-700">
                    <p class="font-bold text-slate-900">Sau khi hoàn thành cấp độ Flyers, học sinh có khả năng:</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="flex items-start gap-2.5 p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-emerald-500 font-bold text-base">✓</span>
                            <span>Hiểu đoạn hội thoại chậm, rõ ràng và giao tiếp tự nhiên trong nhiều bối cảnh.</span>
                        </div>
                        <div class="flex items-start gap-2.5 p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-emerald-500 font-bold text-base">✓</span>
                            <span>Đọc hiểu thông báo, mốc thời gian, đoạn văn ngụ ngôn và tài liệu thực tế.</span>
                        </div>
                        <div class="flex items-start gap-2.5 p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-emerald-500 font-bold text-base">✓</span>
                            <span>Viết đoạn văn ngắn miêu tả trải nghiệm cá nhân, ý kiến và lý do đơn giản.</span>
                        </div>
                        <div class="flex items-start gap-2.5 p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-emerald-500 font-bold text-base">✓</span>
                            <span>Tự tin trình bày quan điểm trực tiếp với người nước ngoài.</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 2. Cấu trúc bài thi 3 Kỹ Năng -->
            <section class="rounded-3xl bg-white p-8 sm:p-10 shadow-sm border border-slate-100">
                <div class="flex items-center gap-3.5 mb-8">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-100 text-sky-600 font-extrabold text-xl">📝</span>
                    <div>
                        <span class="text-xs font-bold text-sky-600 uppercase tracking-wider">Cấu Trúc Chi Tiết</span>
                        <h2 class="text-xl sm:text-2xl font-black text-slate-900">2. Cấu Trúc Bài Thi Cambridge Flyers</h2>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="rounded-3xl bg-slate-50 p-6 border border-slate-200 hover:border-sky-400 hover:shadow-md transition">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black text-sky-700 bg-sky-100 px-3 py-1 rounded-full uppercase">Kỹ Năng Nghe</span>
                            <span class="text-xs font-bold text-slate-400">Part 1 - 5</span>
                        </div>
                        <div class="mt-4 text-3xl font-black text-slate-900">25 phút</div>
                        <div class="text-xs font-bold text-slate-500 mt-1">25 Câu hỏi ghi chép & trắc nghiệm</div>
                        <p class="mt-3 text-xs text-slate-600 leading-relaxed">
                            Nghe nối tên, ghi chép thông tin chi tiết, ghép hình ảnh tương ứng và tô màu bức tranh phức tạp.
                        </p>
                        <div class="mt-5 pt-3 border-t border-slate-200 flex items-center justify-between text-xs">
                            <span class="text-slate-500 font-medium">Thang điểm:</span>
                            <span class="font-black text-sky-600">5 Khiên 🛡️</span>
                        </div>
                    </div>

                    <div class="rounded-3xl bg-slate-50 p-6 border border-slate-200 hover:border-sky-400 hover:shadow-md transition">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black text-sky-700 bg-sky-100 px-3 py-1 rounded-full uppercase">Đọc & Viết</span>
                            <span class="text-xs font-bold text-slate-400">Part 1 - 7</span>
                        </div>
                        <div class="mt-4 text-3xl font-black text-slate-900">40 phút</div>
                        <div class="text-xs font-bold text-slate-500 mt-1">44 Câu hỏi đọc hiểu văn bản</div>
                        <p class="mt-3 text-xs text-slate-600 leading-relaxed">
                            Ghép từ với định nghĩa, hoàn thành bài báo ngắn, điền từ vào nhật ký và viết câu chuyện từ tranh.
                        </p>
                        <div class="mt-5 pt-3 border-t border-slate-200 flex items-center justify-between text-xs">
                            <span class="text-slate-500 font-medium">Thang điểm:</span>
                            <span class="font-black text-sky-600">5 Khiên 🛡️</span>
                        </div>
                    </div>

                    <div class="rounded-3xl bg-slate-50 p-6 border border-slate-200 hover:border-sky-400 hover:shadow-md transition">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black text-sky-700 bg-sky-100 px-3 py-1 rounded-full uppercase">Kỹ Năng Nói</span>
                            <span class="text-xs font-bold text-slate-400">Phần thi 1:1</span>
                        </div>
                        <div class="mt-4 text-3xl font-black text-slate-900">7 - 9 phút</div>
                        <div class="text-xs font-bold text-slate-500 mt-1">4 Phần tương tác nâng cao</div>
                        <p class="mt-3 text-xs text-slate-600 leading-relaxed">
                            Tìm điểm khác biệt giữa 2 bức tranh, hỏi đáp thông tin theo bảng dữ liệu và kể chuyện logic.
                        </p>
                        <div class="mt-5 pt-3 border-t border-slate-200 flex items-center justify-between text-xs">
                            <span class="text-slate-500 font-medium">Thang điểm:</span>
                            <span class="font-black text-sky-600">5 Khiên 🛡️</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 3. Lợi ích -->
            <section class="rounded-3xl bg-white p-8 sm:p-10 shadow-sm border border-slate-100">
                <div class="flex items-center gap-3.5 mb-6">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-100 text-sky-600 font-extrabold text-xl">💡</span>
                    <div>
                        <span class="text-xs font-bold text-sky-600 uppercase tracking-wider">Lợi Ích Cốt Lõi</span>
                        <h2 class="text-xl sm:text-2xl font-black text-slate-900">3. Lợi Ích Của Chứng Chỉ Flyers</h2>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs sm:text-sm text-slate-700">
                    <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 flex items-start gap-3.5">
                        <span class="text-2xl">🏆</span>
                        <div>
                            <strong class="text-slate-900 block mb-1">Ưu thế tuyển sinh lớp chọn THCS</strong>
                            <span>Điểm cộng lớn khi đăng ký xét tuyển trường chuyên, các lớp tiếng Anh chất lượng cao.</span>
                        </div>
                    </div>
                    <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 flex items-start gap-3.5">
                        <span class="text-2xl">🔗</span>
                        <div>
                            <strong class="text-slate-900 block mb-1">Bản lề sang KET & PET</strong>
                            <span>Dễ dàng chuyển tiếp sang các chứng chỉ tiếng Anh tổng quát dành cho THCS & THPT.</span>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <!-- Sidebar / CTA Registration Form -->
        <div class="space-y-6">
            <div class="sticky top-28 rounded-3xl bg-gradient-to-br from-sky-600 via-sky-700 to-sky-800 p-8 text-white shadow-2xl">
                <span class="inline-block rounded-full bg-white/20 px-3 py-1 text-[11px] font-bold text-sky-100 backdrop-blur-md mb-3">
                    🎁 Miễn Phí Test Trình Độ 4 Kỹ Năng
                </span>
                <h3 class="text-xl sm:text-2xl font-black">Đăng Ký Tư Vấn Flyers</h3>
                <p class="mt-2 text-xs text-sky-100 leading-relaxed">
                    Đánh giá năng lực chuẩn Cambridge và nhận lộ trình cam kết đầu ra Flyers từ 12 - 15 Khiên cho con.
                </p>

                <form class="mt-6 space-y-4" method="post" action="/api/index.php?resource=leads&method=submit">
                    <?= csrf_input(); ?>
                    <input type="hidden" name="redirect_to" value="<?= e($_SERVER['REQUEST_URI'] ?? page_url('cambridge-flyers')); ?>">
                    <input type="hidden" name="interests" value="Cambridge A2 Flyers">
                    <div>
                        <label class="block text-[11px] font-bold text-sky-100 uppercase mb-1">Họ tên phụ huynh / Học sinh</label>
                        <input type="text" name="student_name" required placeholder="Nguyễn Văn A" class="w-full rounded-xl bg-white/15 px-4 py-2.5 text-xs text-white placeholder-sky-100/60 border border-white/20 focus:outline-none focus:ring-2 focus:ring-white">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-sky-100 uppercase mb-1">Số điện thoại / Zalo</label>
                        <input type="tel" name="phone" required placeholder="0901 234 567" class="w-full rounded-xl bg-white/15 px-4 py-2.5 text-xs text-white placeholder-sky-100/60 border border-white/20 focus:outline-none focus:ring-2 focus:ring-white">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-sky-100 uppercase mb-1">Độ tuổi của bé</label>
                        <select name="current_grade" class="w-full rounded-xl bg-slate-900 px-4 py-2.5 text-xs text-white border border-white/20 focus:outline-none focus:ring-2 focus:ring-white">
                            <option value="10">10 tuổi (Học sinh Lớp 5)</option>
                            <option value="11">11 tuổi (Học sinh Lớp 6)</option>
                            <option value="12">12 tuổi (Học sinh Lớp 7)</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full rounded-xl bg-white py-3.5 text-xs font-black text-sky-950 shadow-xl hover:bg-sky-50 transition transform hover:-translate-y-0.5">
                        GỬI ĐĂNG KÝ TƯ VẤN NGAY &rarr;
                    </button>
                    <a href="<?= e(page_url('register-consultation')); ?>" class="block text-center text-[11px] font-bold text-sky-100/80 hover:text-white underline mt-2">Hoặc điền form tư vấn đầy đủ &rarr;</a>
                </form>
            </div>
        </div>
    </div>
</main>
