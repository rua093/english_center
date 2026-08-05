<?php
declare(strict_types=1);

// Cambridge A2 Key (KET) Page
?>

<!-- Cambridge Sub-level Navigation Bar -->
<div class="bg-slate-900 border-b border-slate-800 text-xs py-3 sticky top-[72px] z-40 backdrop-blur-md bg-slate-900/90">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between overflow-x-auto gap-4 scrollbar-none">
        <span class="text-slate-400 font-bold whitespace-nowrap">Các cấp độ Cambridge:</span>
        <div class="flex items-center gap-2">
            <a href="<?= e(page_url('cambridge')); ?>" class="px-3 py-1.5 rounded-full text-slate-300 hover:text-white hover:bg-slate-800 transition whitespace-nowrap font-medium">🎓 Overview</a>
            <a href="<?= e(page_url('cambridge-starters')); ?>" class="px-3 py-1.5 rounded-full text-slate-300 hover:text-amber-400 hover:bg-slate-800 transition whitespace-nowrap font-medium">🟡 Pre-A1 Starters</a>
            <a href="<?= e(page_url('cambridge-movers')); ?>" class="px-3 py-1.5 rounded-full text-slate-300 hover:text-emerald-400 hover:bg-slate-800 transition whitespace-nowrap font-medium">🟢 A1 Movers</a>
            <a href="<?= e(page_url('cambridge-flyers')); ?>" class="px-3 py-1.5 rounded-full text-slate-300 hover:text-sky-400 hover:bg-slate-800 transition whitespace-nowrap font-medium">🔵 A2 Flyers</a>
            <a href="<?= e(page_url('cambridge-ket')); ?>" class="px-3 py-1.5 rounded-full bg-indigo-500 text-slate-950 font-black shadow-sm transition whitespace-nowrap">🟣 A2 KET</a>
            <a href="<?= e(page_url('cambridge-pet')); ?>" class="px-3 py-1.5 rounded-full text-slate-300 hover:text-purple-400 hover:bg-slate-800 transition whitespace-nowrap font-medium">🔴 B1 PET</a>
        </div>
    </div>
</div>

<!-- Hero Banner -->
<div class="relative overflow-hidden bg-gradient-to-br from-indigo-700 via-indigo-800 to-slate-950 py-16 sm:py-24 text-white">
    <div class="absolute -top-40 -left-40 h-96 w-96 rounded-full bg-indigo-400/20 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 h-96 w-96 rounded-full bg-indigo-500/10 blur-3xl pointer-events-none"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row items-center justify-between gap-10">
            <div class="max-w-2xl text-left">
                <div class="flex items-center gap-3 mb-4 flex-wrap">
                    <span class="rounded-full bg-indigo-400/20 px-3.5 py-1 text-xs font-black text-indigo-300 border border-indigo-300/30 backdrop-blur-md">
                        📘 A2 Key for Schools (KET)
                    </span>
                    <span class="text-xs font-semibold text-indigo-100 bg-white/10 px-3 py-1 rounded-full border border-white/10">Dành cho học sinh 12 - 14 tuổi (THCS)</span>
                </div>
                <h1 class="text-3xl font-black sm:text-5xl lg:text-6xl tracking-tight leading-tight">
                    Chứng Chỉ Cambridge KET
                </h1>
                <p class="mt-4 text-base sm:text-lg text-indigo-100/90 leading-relaxed">
                    Chứng chỉ tiếng Anh tổng quát khẳng định khả năng giao tiếp độc lập, đọc hiểu thông tin thực tế và phản biện bằng văn phong chính thức. Cột mốc hoàn hảo chuyển tiếp lên PET và IELTS.
                </p>

                <div class="mt-8 flex flex-wrap gap-4">
                    <a href="<?= e(page_url('home') . '#dang-ky-tu-van'); ?>" class="rounded-2xl bg-indigo-400 px-7 py-3.5 text-xs sm:text-sm font-extrabold text-slate-950 shadow-xl shadow-indigo-500/20 hover:bg-indigo-300 transition-all transform hover:-translate-y-0.5">
                        Đăng ký thi thử KET miễn phí 🚀
                    </a>
                    <a href="<?= e(page_url('cambridge')); ?>" class="rounded-2xl bg-white/10 px-6 py-3.5 text-xs sm:text-sm font-bold text-white border border-white/20 hover:bg-white/20 backdrop-blur-md transition">
                        &larr; Xem lộ trình tổng quan
                    </a>
                </div>
            </div>

            <!-- Stats Badge Glass Card -->
            <div class="w-full lg:w-96 rounded-3xl bg-white/10 p-8 backdrop-blur-xl border border-white/20 text-center shadow-2xl space-y-6">
                <div class="inline-flex h-20 w-20 items-center justify-center rounded-3xl bg-indigo-400 text-slate-950 font-black text-2xl shadow-lg">
                    100-150
                </div>
                <div>
                    <div class="text-xl font-black text-white">Thang Điểm Cambridge English Scale</div>
                    <p class="mt-2 text-xs text-indigo-100/80 leading-relaxed">
                        Đánh giá kết quả minh bạch từ 100 đến 150 điểm, tương đương Khung tham chiếu CEFR A2.
                    </p>
                </div>
                <div class="pt-4 border-t border-white/15 grid grid-cols-2 gap-3 text-xs">
                    <div class="bg-white/10 p-2.5 rounded-xl">
                        <div class="text-indigo-300 font-bold">Khung CEFR</div>
                        <div class="text-white font-extrabold">Trình độ A2</div>
                    </div>
                    <div class="bg-white/10 p-2.5 rounded-xl">
                        <div class="text-indigo-300 font-bold">Thời gian học</div>
                        <div class="text-white font-extrabold">~300 Giờ</div>
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
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-600 font-extrabold text-xl">🎯</span>
                    <div>
                        <span class="text-xs font-bold text-indigo-600 uppercase tracking-wider">Mục Tiêu Khóa Học</span>
                        <h2 class="text-xl sm:text-2xl font-black text-slate-900">1. Đối Tượng & Năng Lực Đạt Được</h2>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    <div class="p-4 rounded-2xl bg-indigo-50/60 border border-indigo-100">
                        <div class="text-xs font-bold text-indigo-700">🧑‍🎓 Độ tuổi phù hợp</div>
                        <div class="text-sm font-extrabold text-slate-900 mt-1">12 - 14 tuổi (Học sinh THCS Lớp 7 - 8)</div>
                    </div>
                    <div class="p-4 rounded-2xl bg-indigo-50/60 border border-indigo-100">
                        <div class="text-xs font-bold text-indigo-700">⏱️ Thời gian tích lũy</div>
                        <div class="text-sm font-extrabold text-slate-900 mt-1">~300 giờ học chuyên sâu</div>
                    </div>
                </div>

                <div class="space-y-3 text-xs sm:text-sm text-slate-700">
                    <p class="font-bold text-slate-900">Sau khi đạt chứng chỉ KET, học sinh có khả năng:</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="flex items-start gap-2.5 p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-emerald-500 font-bold text-base">✓</span>
                            <span>Hiểu bài nói tốc độ tự nhiên về các chủ đề học tập, du lịch, công việc.</span>
                        </div>
                        <div class="flex items-start gap-2.5 p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-emerald-500 font-bold text-base">✓</span>
                            <span>Đọc hiểu thông tin cốt lõi trong sách, tạp chí, biển báo và email chính thức.</span>
                        </div>
                        <div class="flex items-start gap-2.5 p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-emerald-500 font-bold text-base">✓</span>
                            <span>Viết email, mẩu tin nhắn hoặc truyện ngắn ngắn gọn đúng chuẩn văn phong.</span>
                        </div>
                        <div class="flex items-start gap-2.5 p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-emerald-500 font-bold text-base">✓</span>
                            <span>Thảo luận cặp (Pair Work) và trả lời câu hỏi phản biện của giám khảo bản ngữ.</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 2. Cấu trúc bài thi 3 Phần Thi -->
            <section class="rounded-3xl bg-white p-8 sm:p-10 shadow-sm border border-slate-100">
                <div class="flex items-center gap-3.5 mb-8">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-600 font-extrabold text-xl">📝</span>
                    <div>
                        <span class="text-xs font-bold text-indigo-600 uppercase tracking-wider">Cấu Trúc Bài Thi</span>
                        <h2 class="text-xl sm:text-2xl font-black text-slate-900">2. Cấu Trúc Bài Thi Cambridge KET</h2>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="rounded-3xl bg-slate-50 p-6 border border-slate-200 hover:border-indigo-400 hover:shadow-md transition">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black text-indigo-700 bg-indigo-100 px-3 py-1 rounded-full uppercase">Đọc & Viết</span>
                            <span class="text-xs font-bold text-slate-400">Chiếm 50% tổng điểm</span>
                        </div>
                        <div class="mt-4 text-3xl font-black text-slate-900">60 phút</div>
                        <div class="text-xs font-bold text-slate-500 mt-1">7 Phần / 32 Câu hỏi</div>
                        <p class="mt-3 text-xs text-slate-600 leading-relaxed">
                            Đọc thông báo, bài viết dài, điền từ vào chỗ trống và viết 2 bài văn (Viết Email & Kể chuyện theo tranh).
                        </p>
                    </div>

                    <div class="rounded-3xl bg-slate-50 p-6 border border-slate-200 hover:border-indigo-400 hover:shadow-md transition">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black text-indigo-700 bg-indigo-100 px-3 py-1 rounded-full uppercase">Kỹ Năng Nghe</span>
                            <span class="text-xs font-bold text-slate-400">Chiếm 25% tổng điểm</span>
                        </div>
                        <div class="mt-4 text-3xl font-black text-slate-900">30 phút</div>
                        <div class="text-xs font-bold text-slate-500 mt-1">5 Phần / 25 Câu hỏi</div>
                        <p class="mt-3 text-xs text-slate-600 leading-relaxed">
                            Nghe ghi chép giá tiền, thời gian, tên người, lựa chọn đáp án trắc nghiệm A/B/C theo đoạn hội thoại thực tế.
                        </p>
                    </div>

                    <div class="rounded-3xl bg-slate-50 p-6 border border-slate-200 hover:border-indigo-400 hover:shadow-md transition">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black text-indigo-700 bg-indigo-100 px-3 py-1 rounded-full uppercase">Kỹ Năng Nói</span>
                            <span class="text-xs font-bold text-slate-400">Thi cặp 2 thí sinh</span>
                        </div>
                        <div class="mt-4 text-3xl font-black text-slate-900">8 - 10 phút</div>
                        <div class="text-xs font-bold text-slate-500 mt-1">2 Phần tương tác</div>
                        <p class="mt-3 text-xs text-slate-600 leading-relaxed">
                            Trả lời câu hỏi cá nhân và trực tiếp thảo luận ý kiến với thí sinh cùng thi dưới sự giám sát của 2 giám khảo.
                        </p>
                    </div>
                </div>
            </section>

            <!-- 3. Lợi ích -->
            <section class="rounded-3xl bg-white p-8 sm:p-10 shadow-sm border border-slate-100">
                <div class="flex items-center gap-3.5 mb-6">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-600 font-extrabold text-xl">💡</span>
                    <div>
                        <span class="text-xs font-bold text-indigo-600 uppercase tracking-wider">Giá Trị Đem Lại</span>
                        <h2 class="text-xl sm:text-2xl font-black text-slate-900">3. Lợi Ích Của Chứng Chỉ KET</h2>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs sm:text-sm text-slate-700">
                    <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 flex items-start gap-3.5">
                        <span class="text-2xl">🌍</span>
                        <div>
                            <strong class="text-slate-900 block mb-1">Giá trị vĩnh viễn toàn cầu</strong>
                            <span>Chứng chỉ được cấp từ Cambridge Assessment English và không bao giờ hết hạn.</span>
                        </div>
                    </div>
                    <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 flex items-start gap-3.5">
                        <span class="text-2xl">🎓</span>
                        <div>
                            <strong class="text-slate-900 block mb-1">Bứt phá sang IELTS 5.0+</strong>
                            <span>Học sinh hoàn thành KET có thể tự tin bước vào lộ trình ôn thi PET & IELTS cấp THPT.</span>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <!-- Sidebar / CTA Registration Form -->
        <div class="space-y-6">
            <div class="sticky top-28 rounded-3xl bg-gradient-to-br from-indigo-700 via-indigo-800 to-indigo-900 p-8 text-white shadow-2xl">
                <span class="inline-block rounded-full bg-white/20 px-3 py-1 text-[11px] font-bold text-indigo-100 backdrop-blur-md mb-3">
                    🎁 Miễn Phí Test Trình Độ 4 Kỹ Năng
                </span>
                <h3 class="text-xl sm:text-2xl font-black">Đăng Ký Tư Vấn KET</h3>
                <p class="mt-2 text-xs text-indigo-100 leading-relaxed">
                    Thi thử chuẩn định dạng bài thi KET mới nhất và nhận phân tích điểm chi tiết từ chuyên gia Nhuệ Minh.
                </p>

                <form class="mt-6 space-y-4" method="post" action="/api/index.php?resource=leads&method=submit">
                    <?= csrf_input(); ?>
                    <input type="hidden" name="redirect_to" value="<?= e($_SERVER['REQUEST_URI'] ?? page_url('cambridge-ket')); ?>">
                    <input type="hidden" name="interests" value="Cambridge A2 KET">
                    <div>
                        <label class="block text-[11px] font-bold text-indigo-100 uppercase mb-1">Họ tên phụ huynh / Học sinh</label>
                        <input type="text" name="student_name" required placeholder="Nguyễn Văn A" class="w-full rounded-xl bg-white/15 px-4 py-2.5 text-xs text-white placeholder-indigo-100/60 border border-white/20 focus:outline-none focus:ring-2 focus:ring-white">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-indigo-100 uppercase mb-1">Số điện thoại / Zalo</label>
                        <input type="tel" name="phone" required placeholder="0901 234 567" class="w-full rounded-xl bg-white/15 px-4 py-2.5 text-xs text-white placeholder-indigo-100/60 border border-white/20 focus:outline-none focus:ring-2 focus:ring-white">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-indigo-100 uppercase mb-1">Lớp đang học</label>
                        <select name="current_grade" class="w-full rounded-xl bg-slate-900 px-4 py-2.5 text-xs text-white border border-white/20 focus:outline-none focus:ring-2 focus:ring-white">
                            <option value="Lớp 6 - Lớp 7">Lớp 6 - Lớp 7</option>
                            <option value="Lớp 8">Lớp 8</option>
                            <option value="Lớp 9">Lớp 9</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full rounded-xl bg-white py-3.5 text-xs font-black text-indigo-950 shadow-xl hover:bg-indigo-50 transition transform hover:-translate-y-0.5">
                        GỬI ĐĂNG KÝ TƯ VẤN NGAY &rarr;
                    </button>
                    <a href="<?= e(page_url('register-consultation')); ?>" class="block text-center text-[11px] font-bold text-indigo-100/80 hover:text-white underline mt-2">Hoặc điền form tư vấn đầy đủ &rarr;</a>
                </form>
            </div>
        </div>
    </div>
</main>
