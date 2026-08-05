<?php
declare(strict_types=1);

// Cambridge Pre-A1 Starters Page
?>

<!-- Cambridge Sub-level Navigation Bar -->
<div class="bg-slate-900 border-b border-slate-800 text-xs py-3 sticky top-[72px] z-40 backdrop-blur-md bg-slate-900/90">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between overflow-x-auto gap-4 scrollbar-none">
        <span class="text-slate-400 font-bold whitespace-nowrap">Các cấp độ Cambridge:</span>
        <div class="flex items-center gap-2">
            <a href="<?= e(page_url('cambridge')); ?>" class="px-3 py-1.5 rounded-full text-slate-300 hover:text-white hover:bg-slate-800 transition whitespace-nowrap font-medium">🎓 Overview</a>
            <a href="<?= e(page_url('cambridge-starters')); ?>" class="px-3 py-1.5 rounded-full bg-amber-500 text-slate-950 font-black shadow-sm transition whitespace-nowrap">🟡 Pre-A1 Starters</a>
            <a href="<?= e(page_url('cambridge-movers')); ?>" class="px-3 py-1.5 rounded-full text-slate-300 hover:text-emerald-400 hover:bg-slate-800 transition whitespace-nowrap font-medium">🟢 A1 Movers</a>
            <a href="<?= e(page_url('cambridge-flyers')); ?>" class="px-3 py-1.5 rounded-full text-slate-300 hover:text-sky-400 hover:bg-slate-800 transition whitespace-nowrap font-medium">🔵 A2 Flyers</a>
            <a href="<?= e(page_url('cambridge-ket')); ?>" class="px-3 py-1.5 rounded-full text-slate-300 hover:text-indigo-400 hover:bg-slate-800 transition whitespace-nowrap font-medium">🟣 A2 KET</a>
            <a href="<?= e(page_url('cambridge-pet')); ?>" class="px-3 py-1.5 rounded-full text-slate-300 hover:text-purple-400 hover:bg-slate-800 transition whitespace-nowrap font-medium">🔴 B1 PET</a>
        </div>
    </div>
</div>

<!-- Hero Banner -->
<div class="relative overflow-hidden bg-gradient-to-br from-amber-600 via-amber-700 to-slate-950 py-16 sm:py-24 text-white">
    <div class="absolute -top-40 -left-40 h-96 w-96 rounded-full bg-amber-400/20 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 h-96 w-96 rounded-full bg-amber-500/10 blur-3xl pointer-events-none"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row items-center justify-between gap-10">
            <div class="max-w-2xl text-left">
                <div class="flex items-center gap-3 mb-4 flex-wrap">
                    <span class="rounded-full bg-amber-400/20 px-3.5 py-1 text-xs font-black text-amber-300 border border-amber-300/30 backdrop-blur-md">
                        ⭐ Pre-A1 Starters (Young Learners)
                    </span>
                    <span class="text-xs font-semibold text-amber-100 bg-white/10 px-3 py-1 rounded-full border border-white/10">Dành cho trẻ 6 - 8 tuổi (Lớp 1 - 2)</span>
                </div>
                <h1 class="text-3xl font-black sm:text-5xl lg:text-6xl tracking-tight leading-tight">
                    Chứng Chỉ Cambridge Starters
                </h1>
                <p class="mt-4 text-base sm:text-lg text-amber-100/90 leading-relaxed">
                    Khởi đầu niềm yêu thích tiếng Anh tự nhiên cho học sinh tiểu học. Bài thi giúp trẻ làm quen với các hội thoại hàng ngày qua bài hát, hình ảnh màu sắc và tương tác vui vẻ mà không hề có áp lực thi cử.
                </p>

                <div class="mt-8 flex flex-wrap gap-4">
                    <a href="<?= e(page_url('home') . '#dang-ky-tu-van'); ?>" class="rounded-2xl bg-amber-400 px-7 py-3.5 text-xs sm:text-sm font-extrabold text-slate-950 shadow-xl shadow-amber-500/20 hover:bg-amber-300 transition-all transform hover:-translate-y-0.5">
                        Đăng ký thi thử Starters miễn phí 🚀
                    </a>
                    <a href="<?= e(page_url('cambridge')); ?>" class="rounded-2xl bg-white/10 px-6 py-3.5 text-xs sm:text-sm font-bold text-white border border-white/20 hover:bg-white/20 backdrop-blur-md transition">
                        &larr; Xem lộ trình tổng quan
                    </a>
                </div>
            </div>

            <!-- Stats Badge Glass Card -->
            <div class="w-full lg:w-96 rounded-3xl bg-white/10 p-8 backdrop-blur-xl border border-white/20 text-center shadow-2xl space-y-6">
                <div class="inline-flex h-20 w-20 items-center justify-center rounded-3xl bg-amber-400 text-slate-950 font-black text-3xl shadow-lg">
                    🛡️ 15
                </div>
                <div>
                    <div class="text-xl font-black text-white">Tối Đa 15 Khiên Cambridge</div>
                    <p class="mt-2 text-xs text-amber-100/80 leading-relaxed">
                        Mỗi kỹ năng (Nghe - Đọc/Viết - Nói) tối đa 5 Khiên. Chứng chỉ ghi nhận kết quả chính thức từ Cambridge Assessment English.
                    </p>
                </div>
                <div class="pt-4 border-t border-white/15 grid grid-cols-2 gap-3 text-xs">
                    <div class="bg-white/10 p-2.5 rounded-xl">
                        <div class="text-amber-300 font-bold">Khung CEFR</div>
                        <div class="text-white font-extrabold">Pre-A1 Level</div>
                    </div>
                    <div class="bg-white/10 p-2.5 rounded-xl">
                        <div class="text-amber-300 font-bold">Thời gian học</div>
                        <div class="text-white font-extrabold">~100 Giờ</div>
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
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-100 text-amber-600 font-extrabold text-xl">🎯</span>
                    <div>
                        <span class="text-xs font-bold text-amber-600 uppercase tracking-wider">Mục Tiêu Khóa Học</span>
                        <h2 class="text-xl sm:text-2xl font-black text-slate-900">1. Đối Tượng & Năng Lực Đạt Được</h2>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    <div class="p-4 rounded-2xl bg-amber-50/60 border border-amber-100">
                        <div class="text-xs font-bold text-amber-700">👶 Độ tuổi phù hợp</div>
                        <div class="text-sm font-extrabold text-slate-900 mt-1">6 - 8 tuổi (Học sinh Lớp 1 - 2)</div>
                    </div>
                    <div class="p-4 rounded-2xl bg-amber-50/60 border border-amber-100">
                        <div class="text-xs font-bold text-amber-700">⏱️ Thời gian tích lũy</div>
                        <div class="text-sm font-extrabold text-slate-900 mt-1">~100 giờ học tiếng Anh chuẩn</div>
                    </div>
                </div>

                <div class="space-y-3 text-xs sm:text-sm text-slate-700">
                    <p class="font-bold text-slate-900">Sau khi hoàn thành cấp độ Starters, học sinh có khả năng:</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="flex items-start gap-2.5 p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-emerald-500 font-bold text-base">✓</span>
                            <span>Hiểu & phản xạ các câu hỏi giao tiếp cơ bản (tên, tuổi, màu sắc, đồ chơi).</span>
                        </div>
                        <div class="flex items-start gap-2.5 p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-emerald-500 font-bold text-base">✓</span>
                            <span>Hiểu các câu lệnh chỉ dẫn của giáo viên trong phòng học chuẩn quốc tế.</span>
                        </div>
                        <div class="flex items-start gap-2.5 p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-emerald-500 font-bold text-base">✓</span>
                            <span>Đọc và viết chính xác từ vựng thuộc 20+ chủ đề quen thuộc hàng ngày.</span>
                        </div>
                        <div class="flex items-start gap-2.5 p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-emerald-500 font-bold text-base">✓</span>
                            <span>Tự tin trao đổi trực tiếp 1:1 với giám khảo bản ngữ mà không sợ sệt.</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 2. Cấu trúc bài thi 3 Kỹ Năng -->
            <section class="rounded-3xl bg-white p-8 sm:p-10 shadow-sm border border-slate-100">
                <div class="flex items-center gap-3.5 mb-8">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-100 text-amber-600 font-extrabold text-xl">📝</span>
                    <div>
                        <span class="text-xs font-bold text-amber-600 uppercase tracking-wider">Cấu Trúc Chi Tiết</span>
                        <h2 class="text-xl sm:text-2xl font-black text-slate-900">2. Cấu Trúc Bài Thi Cambridge Starters</h2>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Nghe -->
                    <div class="rounded-3xl bg-slate-50 p-6 border border-slate-200 hover:border-amber-400 hover:shadow-md transition">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black text-amber-700 bg-amber-100 px-3 py-1 rounded-full uppercase">Kỹ Năng Nghe</span>
                            <span class="text-xs font-bold text-slate-400">Part 1 - 4</span>
                        </div>
                        <div class="mt-4 text-3xl font-black text-slate-900">20 phút</div>
                        <div class="text-xs font-bold text-slate-500 mt-1">20 Câu hỏi hình ảnh</div>
                        <p class="mt-3 text-xs text-slate-600 leading-relaxed">
                            Nghe đoạn hội thoại ngắn, nối tên nhân vật, tô màu bức tranh và điền chữ cái còn thiếu.
                        </p>
                        <div class="mt-5 pt-3 border-t border-slate-200 flex items-center justify-between text-xs">
                            <span class="text-slate-500 font-medium">Thang điểm:</span>
                            <span class="font-black text-amber-600">5 Khiên 🛡️</span>
                        </div>
                    </div>

                    <!-- Đọc & Viết -->
                    <div class="rounded-3xl bg-slate-50 p-6 border border-slate-200 hover:border-amber-400 hover:shadow-md transition">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black text-amber-700 bg-amber-100 px-3 py-1 rounded-full uppercase">Đọc & Viết</span>
                            <span class="text-xs font-bold text-slate-400">Part 1 - 5</span>
                        </div>
                        <div class="mt-4 text-3xl font-black text-slate-900">20 phút</div>
                        <div class="text-xs font-bold text-slate-500 mt-1">25 Câu hỏi tương tác</div>
                        <p class="mt-3 text-xs text-slate-600 leading-relaxed">
                            Đánh dấu Đúng/Sai theo tranh, sắp xếp ký tự thành từ đúng, chọn từ điền câu chuyện.
                        </p>
                        <div class="mt-5 pt-3 border-t border-slate-200 flex items-center justify-between text-xs">
                            <span class="text-slate-500 font-medium">Thang điểm:</span>
                            <span class="font-black text-amber-600">5 Khiên 🛡️</span>
                        </div>
                    </div>

                    <!-- Nói -->
                    <div class="rounded-3xl bg-slate-50 p-6 border border-slate-200 hover:border-amber-400 hover:shadow-md transition">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black text-amber-700 bg-amber-100 px-3 py-1 rounded-full uppercase">Kỹ Năng Nói</span>
                            <span class="text-xs font-bold text-slate-400">Phần thi 1:1</span>
                        </div>
                        <div class="mt-4 text-3xl font-black text-slate-900">3 - 5 phút</div>
                        <div class="text-xs font-bold text-slate-500 mt-1">4 Phần hội thoại</div>
                        <p class="mt-3 text-xs text-slate-600 leading-relaxed">
                            Trò chuyện 1:1 trực tiếp cùng giám khảo bản ngữ, chỉ đồ vật trong bức tranh và trả lời câu cá nhân.
                        </p>
                        <div class="mt-5 pt-3 border-t border-slate-200 flex items-center justify-between text-xs">
                            <span class="text-slate-500 font-medium">Thang điểm:</span>
                            <span class="font-black text-amber-600">5 Khiên 🛡️</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 3. Lợi ích & Đánh giá -->
            <section class="rounded-3xl bg-white p-8 sm:p-10 shadow-sm border border-slate-100">
                <div class="flex items-center gap-3.5 mb-6">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-100 text-amber-600 font-extrabold text-xl">💡</span>
                    <div>
                        <span class="text-xs font-bold text-amber-600 uppercase tracking-wider">Giá Trị Đem Lại</span>
                        <h2 class="text-xl sm:text-2xl font-black text-slate-900">3. Lợi Ích Vượt Trội Cho Học Sinh</h2>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs sm:text-sm text-slate-700">
                    <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 flex items-start gap-3.5">
                        <span class="text-2xl">🌟</span>
                        <div>
                            <strong class="text-slate-900 block mb-1">Không áp lực điểm số thi cử</strong>
                            <span>Kỳ thi hoàn toàn không xếp loại Đậu/Rớt, tạo trải nghiệm chinh phục niềm vui tự hào cho trẻ.</span>
                        </div>
                    </div>
                    <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 flex items-start gap-3.5">
                        <span class="text-2xl">🏆</span>
                        <div>
                            <strong class="text-slate-900 block mb-1">Chứng chỉ chuẩn Quốc tế</strong>
                            <span>Được cấp trực tiếp từ Hội đồng Khảo thí Cambridge Assessment English (Anh Quốc).</span>
                        </div>
                    </div>
                    <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 flex items-start gap-3.5">
                        <span class="text-2xl">🗣️</span>
                        <div>
                            <strong class="text-slate-900 block mb-1">Hình thành thói quen phản xạ</strong>
                            <span>Góp phần giúp trẻ tự tin phát âm chuẩn và giao tiếp tự nhiên với người nước ngoài.</span>
                        </div>
                    </div>
                    <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 flex items-start gap-3.5">
                        <span class="text-2xl">📈</span>
                        <div>
                            <strong class="text-slate-900 block mb-1">Nền tảng Movers & Flyers</strong>
                            <span>Tạo đà vững chắc sẵn sàng chinh phục các kỳ thi nâng cao tiếp theo.</span>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <!-- Sidebar / CTA Registration Form (1 Col) -->
        <div class="space-y-6">
            <div class="sticky top-28 rounded-3xl bg-gradient-to-br from-amber-500 via-amber-600 to-amber-700 p-8 text-white shadow-2xl">
                <span class="inline-block rounded-full bg-white/20 px-3 py-1 text-[11px] font-bold text-amber-100 backdrop-blur-md mb-3">
                    🎁 Miễn Phí Test Trình Độ 4 Kỹ Năng
                </span>
                <h3 class="text-xl sm:text-2xl font-black">Đăng Ký Tư Vấn Starters</h3>
                <p class="mt-2 text-xs text-amber-100 leading-relaxed">
                    Đánh giá năng lực chuẩn Cambridge và nhận lộ trình cam kết đầu ra Starters từ 10 - 15 Khiên cho con.
                </p>

                <form class="mt-6 space-y-4" method="post" action="/api/index.php?resource=leads&method=submit">
                    <?= csrf_input(); ?>
                    <input type="hidden" name="redirect_to" value="<?= e($_SERVER['REQUEST_URI'] ?? page_url('cambridge-starters')); ?>">
                    <input type="hidden" name="interests" value="Cambridge Pre-A1 Starters">
                    <div>
                        <label class="block text-[11px] font-bold text-amber-100 uppercase mb-1">Họ tên phụ huynh / Học sinh</label>
                        <input type="text" name="student_name" required placeholder="Nguyễn Văn A" class="w-full rounded-xl bg-white/15 px-4 py-2.5 text-xs text-white placeholder-amber-100/60 border border-white/20 focus:outline-none focus:ring-2 focus:ring-white">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-amber-100 uppercase mb-1">Số điện thoại / Zalo</label>
                        <input type="tel" name="phone" required placeholder="0901 234 567" class="w-full rounded-xl bg-white/15 px-4 py-2.5 text-xs text-white placeholder-amber-100/60 border border-white/20 focus:outline-none focus:ring-2 focus:ring-white">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-amber-100 uppercase mb-1">Độ tuổi của bé</label>
                        <select name="current_grade" class="w-full rounded-xl bg-slate-900 px-4 py-2.5 text-xs text-white border border-white/20 focus:outline-none focus:ring-2 focus:ring-white">
                            <option value="6">6 tuổi (Học sinh Lớp 1)</option>
                            <option value="7">7 tuổi (Học sinh Lớp 2)</option>
                            <option value="8">8 tuổi (Học sinh Lớp 3)</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full rounded-xl bg-white py-3.5 text-xs font-black text-amber-900 shadow-xl hover:bg-amber-50 transition transform hover:-translate-y-0.5">
                        GỬI ĐĂNG KÝ TƯ VẤN NGAY &rarr;
                    </button>
                    <a href="<?= e(page_url('register-consultation')); ?>" class="block text-center text-[11px] font-bold text-amber-100/80 hover:text-white underline mt-2">Hoặc điền form tư vấn đầy đủ &rarr;</a>
                </form>
            </div>
        </div>
    </div>
</main>
