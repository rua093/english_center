<?php
declare(strict_types=1);

// Cambridge B1 Preliminary (PET) Page
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
            <a href="<?= e(page_url('cambridge-ket')); ?>" class="px-3 py-1.5 rounded-full text-slate-300 hover:text-indigo-400 hover:bg-slate-800 transition whitespace-nowrap font-medium">🟣 A2 KET</a>
            <a href="<?= e(page_url('cambridge-pet')); ?>" class="px-3 py-1.5 rounded-full bg-purple-600 text-white font-black shadow-sm transition whitespace-nowrap">🔴 B1 PET</a>
        </div>
    </div>
</div>

<!-- Hero Banner -->
<div class="relative overflow-hidden bg-gradient-to-br from-purple-700 via-purple-800 to-slate-950 py-16 sm:py-24 text-white">
    <div class="absolute -top-40 -left-40 h-96 w-96 rounded-full bg-purple-400/20 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 h-96 w-96 rounded-full bg-purple-500/10 blur-3xl pointer-events-none"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row items-center justify-between gap-10">
            <div class="max-w-2xl text-left">
                <div class="flex items-center gap-3 mb-4 flex-wrap">
                    <span class="rounded-full bg-purple-400/20 px-3.5 py-1 text-xs font-black text-purple-300 border border-purple-300/30 backdrop-blur-md">
                        📕 B1 Preliminary for Schools (PET)
                    </span>
                    <span class="text-xs font-semibold text-purple-100 bg-white/10 px-3 py-1 rounded-full border border-white/10">Dành cho học sinh 14 - 16+ tuổi (THCS-THPT)</span>
                </div>
                <h1 class="text-3xl font-black sm:text-5xl lg:text-6xl tracking-tight leading-tight">
                    Chứng Chỉ Cambridge PET
                </h1>
                <p class="mt-4 text-base sm:text-lg text-purple-100/90 leading-relaxed">
                    Trình độ trung cấp (Intermediate B1). Chứng chỉ khẳng định khả năng sử dụng tiếng Anh thành thạo trong học tập, giao tiếp công việc và bước đệm bứt phá sang IELTS 6.0 - 7.5+.
                </p>

                <div class="mt-8 flex flex-wrap gap-4">
                    <a href="<?= e(page_url('home') . '#dang-ky-tu-van'); ?>" class="rounded-2xl bg-purple-400 px-7 py-3.5 text-xs sm:text-sm font-extrabold text-slate-950 shadow-xl shadow-purple-500/20 hover:bg-purple-300 transition-all transform hover:-translate-y-0.5">
                        Đăng ký thi thử PET miễn phí 🚀
                    </a>
                    <a href="<?= e(page_url('cambridge')); ?>" class="rounded-2xl bg-white/10 px-6 py-3.5 text-xs sm:text-sm font-bold text-white border border-white/20 hover:bg-white/20 backdrop-blur-md transition">
                        &larr; Xem lộ trình tổng quan
                    </a>
                </div>
            </div>

            <!-- Stats Badge Glass Card -->
            <div class="w-full lg:w-96 rounded-3xl bg-white/10 p-8 backdrop-blur-xl border border-white/20 text-center shadow-2xl space-y-6">
                <div class="inline-flex h-20 w-20 items-center justify-center rounded-3xl bg-purple-400 text-slate-950 font-black text-2xl shadow-lg">
                    120-170
                </div>
                <div>
                    <div class="text-xl font-black text-white">Thang Điểm Cambridge English Scale</div>
                    <p class="mt-2 text-xs text-purple-100/80 leading-relaxed">
                        Đánh giá trình độ B1 trung cấp chuyên sâu với kết quả vô thời hạn toàn cầu.
                    </p>
                </div>
                <div class="pt-4 border-t border-white/15 grid grid-cols-2 gap-3 text-xs">
                    <div class="bg-white/10 p-2.5 rounded-xl">
                        <div class="text-purple-300 font-bold">Khung CEFR</div>
                        <div class="text-white font-extrabold">Trình độ B1</div>
                    </div>
                    <div class="bg-white/10 p-2.5 rounded-xl">
                        <div class="text-purple-300 font-bold">Thời gian học</div>
                        <div class="text-white font-extrabold">~400 Giờ</div>
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
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-purple-100 text-purple-600 font-extrabold text-xl">🎯</span>
                    <div>
                        <span class="text-xs font-bold text-purple-600 uppercase tracking-wider">Mục Tiêu Khóa Học</span>
                        <h2 class="text-xl sm:text-2xl font-black text-slate-900">1. Đối Tượng & Năng Lực Đạt Được</h2>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    <div class="p-4 rounded-2xl bg-purple-50/60 border border-purple-100">
                        <div class="text-xs font-bold text-purple-700">🧑‍🎓 Độ tuổi phù hợp</div>
                        <div class="text-sm font-extrabold text-slate-900 mt-1">14 - 16+ tuổi (Học sinh THCS - THPT)</div>
                    </div>
                    <div class="p-4 rounded-2xl bg-purple-50/60 border border-purple-100">
                        <div class="text-xs font-bold text-purple-700">⏱️ Thời gian tích lũy</div>
                        <div class="text-sm font-extrabold text-slate-900 mt-1">~400 giờ học chuyên sâu</div>
                    </div>
                </div>

                <div class="space-y-3 text-xs sm:text-sm text-slate-700">
                    <p class="font-bold text-slate-900">Sau khi hoàn thành trình độ B1 PET, học sinh có khả năng:</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="flex items-start gap-2.5 p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-emerald-500 font-bold text-base">✓</span>
                            <span>Hiểu điểm chính trong các bài giảng, chương trình truyền hình và tin tức.</span>
                        </div>
                        <div class="flex items-start gap-2.5 p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-emerald-500 font-bold text-base">✓</span>
                            <span>Xử lý tự tin hầu hết các tình huống giao tiếp bất ngờ khi du lịch hoặc giao lưu.</span>
                        </div>
                        <div class="flex items-start gap-2.5 p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-emerald-500 font-bold text-base">✓</span>
                            <span>Viết bài luận ngắn, email phản hồi, bài đánh giá quan điểm dài khoảng 100 từ.</span>
                        </div>
                        <div class="flex items-start gap-2.5 p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-emerald-500 font-bold text-base">✓</span>
                            <span>Thuyết trình và phản biện tự tin chủ đề cá nhân thích du lịch, phim ảnh, công nghệ.</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 2. Cấu trúc bài thi 4 Kỹ Năng -->
            <section class="rounded-3xl bg-white p-8 sm:p-10 shadow-sm border border-slate-100">
                <div class="flex items-center gap-3.5 mb-8">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-purple-100 text-purple-600 font-extrabold text-xl">📝</span>
                    <div>
                        <span class="text-xs font-bold text-purple-600 uppercase tracking-wider">Cấu Trúc Bài Thi</span>
                        <h2 class="text-xl sm:text-2xl font-black text-slate-900">2. Cấu Trúc Bài Thi Cambridge PET</h2>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="rounded-3xl bg-slate-50 p-6 border border-slate-200 hover:border-purple-400 hover:shadow-md transition">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black text-purple-700 bg-purple-100 px-3 py-1 rounded-full uppercase">Kỹ Năng Đọc</span>
                            <span class="text-xs font-bold text-slate-400">Chiếm 25% tổng điểm</span>
                        </div>
                        <div class="mt-4 text-3xl font-black text-slate-900">45 phút</div>
                        <div class="text-xs font-bold text-slate-500 mt-1">6 Phần / 32 Câu hỏi</div>
                        <p class="mt-3 text-xs text-slate-600 leading-relaxed">
                            Đọc hiểu thông báo, bài viết báo chí, nối thông tin theo yêu cầu và chọn từ đúng ngữ cảnh.
                        </p>
                    </div>

                    <div class="rounded-3xl bg-slate-50 p-6 border border-slate-200 hover:border-purple-400 hover:shadow-md transition">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black text-purple-700 bg-purple-100 px-3 py-1 rounded-full uppercase">Kỹ Năng Viết</span>
                            <span class="text-xs font-bold text-slate-400">Chiếm 25% tổng điểm</span>
                        </div>
                        <div class="mt-4 text-3xl font-black text-slate-900">45 phút</div>
                        <div class="text-xs font-bold text-slate-500 mt-1">2 Phần thi luận</div>
                        <p class="mt-3 text-xs text-slate-600 leading-relaxed">
                            Viết 1 Email phản hồi (khoảng 100 từ) và 1 bài viết sáng tạo (Truyện ngắn hoặc bài luận cá nhân).
                        </p>
                    </div>

                    <div class="rounded-3xl bg-slate-50 p-6 border border-slate-200 hover:border-purple-400 hover:shadow-md transition">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black text-purple-700 bg-purple-100 px-3 py-1 rounded-full uppercase">Kỹ Năng Nghe</span>
                            <span class="text-xs font-bold text-slate-400">Chiếm 25% tổng điểm</span>
                        </div>
                        <div class="mt-4 text-3xl font-black text-slate-900">30 phút</div>
                        <div class="text-xs font-bold text-slate-500 mt-1">4 Phần / 25 Câu hỏi</div>
                        <p class="mt-3 text-xs text-slate-600 leading-relaxed">
                            Nghe trắc nghiệm hình ảnh, thông báo tin tức ngắn và ghi chép thông tin bài phỏng vấn dài.
                        </p>
                    </div>

                    <div class="rounded-3xl bg-slate-50 p-6 border border-slate-200 hover:border-purple-400 hover:shadow-md transition">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black text-purple-700 bg-purple-100 px-3 py-1 rounded-full uppercase">Kỹ Năng Nói</span>
                            <span class="text-xs font-bold text-slate-400">Thi cặp 2 thí sinh</span>
                        </div>
                        <div class="mt-4 text-3xl font-black text-slate-900">12 phút</div>
                        <div class="text-xs font-bold text-slate-500 mt-1">4 Phần tương tác luận</div>
                        <p class="mt-3 text-xs text-slate-600 leading-relaxed">
                            Trả lời phỏng vấn cá nhân, miêu tả bức tranh màu và trực tiếp lập kế hoạch/thảo luận cùng thí sinh khác.
                        </p>
                    </div>
                </div>
            </section>

            <!-- 3. Lợi ích -->
            <section class="rounded-3xl bg-white p-8 sm:p-10 shadow-sm border border-slate-100">
                <div class="flex items-center gap-3.5 mb-6">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-purple-100 text-purple-600 font-extrabold text-xl">💡</span>
                    <div>
                        <span class="text-xs font-bold text-purple-600 uppercase tracking-wider">Lợi Ích Đỉnh Cao</span>
                        <h2 class="text-xl sm:text-2xl font-black text-slate-900">3. Lợi Ích Của Chứng Chỉ PET</h2>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs sm:text-sm text-slate-700">
                    <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 flex items-start gap-3.5">
                        <span class="text-2xl">🎓</span>
                        <div>
                            <strong class="text-slate-900 block mb-1">Miễn thi tốt nghiệp THPT môn Anh</strong>
                            <span>Đạt chứng chỉ PET B1 được miễn thi môn tiếng Anh tốt nghiệp THPT theo quy định Bộ GD&ĐT.</span>
                        </div>
                    </div>
                    <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 flex items-start gap-3.5">
                        <span class="text-2xl">🚀</span>
                        <div>
                            <strong class="text-slate-900 block mb-1">Bứt phá sang IELTS 6.5+</strong>
                            <span>Chuẩn bị vững chắc để chinh phục tấm vé du học và tuyển thẳng Đại học hàng đầu.</span>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <!-- Sidebar / CTA Registration Form -->
        <div class="space-y-6">
            <div class="sticky top-28 rounded-3xl bg-gradient-to-br from-purple-700 via-purple-800 to-purple-900 p-8 text-white shadow-2xl">
                <span class="inline-block rounded-full bg-white/20 px-3 py-1 text-[11px] font-bold text-purple-100 backdrop-blur-md mb-3">
                    🎁 Miễn Phí Test Trình Độ 4 Kỹ Năng
                </span>
                <h3 class="text-xl sm:text-2xl font-black">Đăng Ký Tư Vấn PET</h3>
                <p class="mt-2 text-xs text-purple-100 leading-relaxed">
                    Thi thử chuẩn định dạng bài thi B1 PET mới nhất và nhận phân tích điểm chi tiết từ chuyên gia Nhuệ Minh.
                </p>

                <form class="mt-6 space-y-4" method="post" action="/api/index.php?resource=leads&method=submit">
                    <?= csrf_input(); ?>
                    <input type="hidden" name="redirect_to" value="<?= e($_SERVER['REQUEST_URI'] ?? page_url('cambridge-pet')); ?>">
                    <input type="hidden" name="interests" value="Cambridge B1 PET">
                    <div>
                        <label class="block text-[11px] font-bold text-purple-100 uppercase mb-1">Họ tên phụ huynh / Học sinh</label>
                        <input type="text" name="student_name" required placeholder="Nguyễn Văn A" class="w-full rounded-xl bg-white/15 px-4 py-2.5 text-xs text-white placeholder-purple-100/60 border border-white/20 focus:outline-none focus:ring-2 focus:ring-white">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-purple-100 uppercase mb-1">Số điện thoại / Zalo</label>
                        <input type="tel" name="phone" required placeholder="0901 234 567" class="w-full rounded-xl bg-white/15 px-4 py-2.5 text-xs text-white placeholder-purple-100/60 border border-white/20 focus:outline-none focus:ring-2 focus:ring-white">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-purple-100 uppercase mb-1">Lớp đang học</label>
                        <select name="current_grade" class="w-full rounded-xl bg-slate-900 px-4 py-2.5 text-xs text-white border border-white/20 focus:outline-none focus:ring-2 focus:ring-white">
                            <option value="Lớp 8 - Lớp 9">Lớp 8 - Lớp 9</option>
                            <option value="Lớp 10">Lớp 10</option>
                            <option value="Lớp 11 - 12">Lớp 11 - 12</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full rounded-xl bg-white py-3.5 text-xs font-black text-purple-950 shadow-xl hover:bg-purple-50 transition transform hover:-translate-y-0.5">
                        GỬI ĐĂNG KÝ TƯ VẤN NGAY &rarr;
                    </button>
                    <a href="<?= e(page_url('register-consultation')); ?>" class="block text-center text-[11px] font-bold text-purple-100/80 hover:text-white underline mt-2">Hoặc điền form tư vấn đầy đủ &rarr;</a>
                </form>
            </div>
        </div>
    </div>
</main>
