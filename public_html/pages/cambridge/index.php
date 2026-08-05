<?php
declare(strict_types=1);

// Cambridge Qualifications Overview Hub
?>

<!-- Hero Banner -->
<div class="relative overflow-hidden bg-slate-950 py-16 sm:py-24 text-white">
    <!-- Ambient glowing backgrounds -->
    <div class="absolute -top-32 -left-32 h-96 w-96 rounded-full bg-blue-600/30 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 -right-32 h-96 w-96 rounded-full bg-amber-500/20 blur-3xl pointer-events-none"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 h-[500px] w-[500px] rounded-full bg-indigo-600/15 blur-3xl pointer-events-none"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto">
            <span class="inline-flex items-center gap-2 rounded-full bg-amber-400/10 px-4 py-1.5 text-xs font-bold text-amber-300 border border-amber-400/30 mb-6 backdrop-blur-md">
                🎓 Cambridge Assessment English • Chuẩn Quốc Tế
            </span>
            <h1 class="text-3xl font-black tracking-tight sm:text-5xl lg:text-6xl bg-gradient-to-r from-white via-slate-100 to-amber-200 bg-clip-text text-transparent leading-tight">
                Tất Tần Tật Về Chứng Chỉ Cambridge
            </h1>
            <p class="mt-4 text-base sm:text-lg text-slate-300 leading-relaxed">
                Hệ thống chứng chỉ tiếng Anh quốc tế Cambridge là thước đo chuẩn xác năng lực ngoại ngữ của học sinh từ 6 - 16+ tuổi, được công nhận bởi hơn 25.000 trường đại học, doanh nghiệp và tổ chức trên toàn cầu.
            </p>

            <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                <a href="<?= e(page_url('home') . '#dang-ky-tu-van'); ?>" class="rounded-2xl bg-gradient-to-r from-amber-400 to-amber-500 px-8 py-3.5 text-sm font-extrabold text-slate-950 shadow-xl shadow-amber-500/20 hover:from-amber-300 hover:to-amber-400 transition-all transform hover:-translate-y-0.5">
                    Đăng ký Test Trình Độ Miễn Phí 🚀
                </a>
                <a href="#lo-trinh-cambridge" class="rounded-2xl bg-white/10 px-7 py-3.5 text-sm font-bold text-white border border-white/20 hover:bg-white/20 backdrop-blur-md transition">
                    Khám phá Lộ trình CEFR &darr;
                </a>
            </div>
        </div>

        <!-- Quick Key Stats Chips -->
        <div class="mt-14 grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
            <div class="rounded-2xl bg-white/5 p-4 backdrop-blur-md border border-white/10">
                <div class="text-2xl sm:text-3xl font-black text-amber-400">100%</div>
                <div class="text-xs font-medium text-slate-300 mt-1">Giá trị Vĩnh viễn (Vô thời hạn)</div>
            </div>
            <div class="rounded-2xl bg-white/5 p-4 backdrop-blur-md border border-white/10">
                <div class="text-2xl sm:text-3xl font-black text-emerald-400">25.000+</div>
                <div class="text-xs font-medium text-slate-300 mt-1">Tổ chức toàn cầu công nhận</div>
            </div>
            <div class="rounded-2xl bg-white/5 p-4 backdrop-blur-md border border-white/10">
                <div class="text-2xl sm:text-3xl font-black text-sky-400">4 Kỹ Năng</div>
                <div class="text-xs font-medium text-slate-300 mt-1">Nghe - Nói - Đọc - Viết toàn diện</div>
            </div>
            <div class="rounded-2xl bg-white/5 p-4 backdrop-blur-md border border-white/10">
                <div class="text-2xl sm:text-3xl font-black text-purple-400">98%</div>
                <div class="text-xs font-medium text-slate-300 mt-1">Học viên Nhuệ Minh đạt 12-15 khiên</div>
            </div>
        </div>
    </div>
</div>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 space-y-20">

    <!-- 1. VISUAL ROADMAP STEPPER SECTION -->
    <section id="lo-trinh-cambridge" class="scroll-mt-24">
        <div class="text-center max-w-3xl mx-auto mb-12">
            <span class="rounded-full bg-blue-50 px-3.5 py-1 text-xs font-bold text-blue-600 border border-blue-100">
                📍 Lộ Trình Phát Triển Ngôn Ngữ Chuẩn CEFR
            </span>
            <h2 class="mt-3 text-2xl sm:text-4xl font-extrabold text-slate-900">
                Các Cấp Độ Chứng Chỉ Cambridge Theo Độ Tuổi
            </h2>
            <p class="mt-2 text-sm sm:text-base text-slate-600">
                Mỗi chứng chỉ là một cột mốc tự nhiên giúp học sinh tự tin nâng cao trình độ từng bước một.
            </p>
        </div>

        <!-- Desktop Visual Timeline / Stepper -->
        <div class="relative">
            <div class="hidden lg:block absolute top-1/2 left-0 right-0 h-1.5 bg-slate-200 -translate-y-1/2 z-0 rounded-full"></div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6 relative z-10">
                <!-- Starters -->
                <a href="<?= e(page_url('cambridge-starters')); ?>" class="group rounded-3xl bg-white p-6 shadow-sm border border-amber-200 hover:border-amber-400 hover:shadow-xl transition-all duration-300 text-center flex flex-col justify-between">
                    <div>
                        <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-100 text-amber-600 font-black text-sm mb-3 group-hover:scale-110 transition-transform">
                            PreA1
                        </div>
                        <h3 class="text-lg font-black text-slate-900 group-hover:text-amber-600 transition">Starters</h3>
                        <p class="text-xs font-bold text-amber-600 mt-1">6 - 8 tuổi (Lớp 1 - 2)</p>
                        <p class="text-xs text-slate-500 mt-2 leading-relaxed">Khởi động đam mê, làm quen từ vựng & câu ngắn đơn giản.</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 text-xs font-extrabold text-amber-600">
                        15 Khiên 🛡️ &rarr;
                    </div>
                </a>

                <!-- Movers -->
                <a href="<?= e(page_url('cambridge-movers')); ?>" class="group rounded-3xl bg-white p-6 shadow-sm border border-emerald-200 hover:border-emerald-400 hover:shadow-xl transition-all duration-300 text-center flex flex-col justify-between">
                    <div>
                        <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600 font-black text-sm mb-3 group-hover:scale-110 transition-transform">
                            A1
                        </div>
                        <h3 class="text-lg font-black text-slate-900 group-hover:text-emerald-600 transition">Movers</h3>
                        <p class="text-xs font-bold text-emerald-600 mt-1">8 - 10 tuổi (Lớp 3 - 4)</p>
                        <p class="text-xs text-slate-500 mt-2 leading-relaxed">Tự tin giao tiếp chủ đề quen thuộc, hiểu hội thoại ngắn.</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 text-xs font-extrabold text-emerald-600">
                        15 Khiên 🛡️ &rarr;
                    </div>
                </a>

                <!-- Flyers -->
                <a href="<?= e(page_url('cambridge-flyers')); ?>" class="group rounded-3xl bg-white p-6 shadow-sm border border-sky-200 hover:border-sky-400 hover:shadow-xl transition-all duration-300 text-center flex flex-col justify-between">
                    <div>
                        <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-100 text-sky-600 font-black text-sm mb-3 group-hover:scale-110 transition-transform">
                            A2
                        </div>
                        <h3 class="text-lg font-black text-slate-900 group-hover:text-sky-600 transition">Flyers</h3>
                        <p class="text-xs font-bold text-sky-600 mt-1">10 - 12 tuổi (Lớp 5 - 6)</p>
                        <p class="text-xs text-slate-500 mt-2 leading-relaxed">Cấp độ cao nhất tiểu học, giao tiếp thành thạo & đọc hiểu văn bản.</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 text-xs font-extrabold text-sky-600">
                        15 Khiên 🛡️ &rarr;
                    </div>
                </a>

                <!-- KET -->
                <a href="<?= e(page_url('cambridge-ket')); ?>" class="group rounded-3xl bg-white p-6 shadow-sm border border-indigo-200 hover:border-indigo-400 hover:shadow-xl transition-all duration-300 text-center flex flex-col justify-between">
                    <div>
                        <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-600 font-black text-sm mb-3 group-hover:scale-110 transition-transform">
                            A2 Key
                        </div>
                        <h3 class="text-lg font-black text-slate-900 group-hover:text-indigo-600 transition">KET</h3>
                        <p class="text-xs font-bold text-indigo-600 mt-1">12 - 14 tuổi (Lớp 7 - 8)</p>
                        <p class="text-xs text-slate-500 mt-2 leading-relaxed">Tiếng Anh tổng quát THCS, làm chủ văn phong viết & thảo luận cặp.</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 text-xs font-extrabold text-indigo-600">
                        100-150 Điểm 📊 &rarr;
                    </div>
                </a>

                <!-- PET -->
                <a href="<?= e(page_url('cambridge-pet')); ?>" class="group rounded-3xl bg-white p-6 shadow-sm border border-purple-200 hover:border-purple-400 hover:shadow-xl transition-all duration-300 text-center flex flex-col justify-between">
                    <div>
                        <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-purple-100 text-purple-600 font-black text-sm mb-3 group-hover:scale-110 transition-transform">
                            B1 Prelim
                        </div>
                        <h3 class="text-lg font-black text-slate-900 group-hover:text-purple-600 transition">PET</h3>
                        <p class="text-xs font-bold text-purple-600 mt-1">14 - 16+ tuổi (THCS-THPT)</p>
                        <p class="text-xs text-slate-500 mt-2 leading-relaxed">Trình độ B1 độc lập, nền tảng bứt phá IELTS 6.0+ vững chắc.</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 text-xs font-extrabold text-purple-600">
                        120-170 Điểm 📊 &rarr;
                    </div>
                </a>
            </div>
        </div>
    </section>

    <!-- 2. DETAILED COMPARISON MATRIX TABLE -->
    <section class="rounded-3xl bg-white p-8 sm:p-10 shadow-lg border border-slate-100 overflow-hidden">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
            <div>
                <span class="rounded-full bg-amber-100 px-3.5 py-1 text-xs font-bold text-amber-700">
                    📊 Bảng So Sánh Chi Tiết
                </span>
                <h2 class="mt-2 text-2xl sm:text-3xl font-extrabold text-slate-900">
                    So Sánh Tổng Quan 5 Kỳ Thi Cambridge
                </h2>
            </div>
            <a href="<?= e(page_url('home') . '#dang-ky-tu-van'); ?>" class="rounded-xl bg-blue-600 px-5 py-2.5 text-xs font-bold text-white shadow-md hover:bg-blue-700 transition">
                Tư vấn chọn kỳ thi thi thử &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[700px]">
                <thead>
                    <tr class="border-b border-slate-200 text-xs font-black uppercase tracking-wider text-slate-500 bg-slate-50/80">
                        <th class="py-4 px-4 rounded-l-xl">Cấp độ</th>
                        <th class="py-4 px-4">Độ tuổi</th>
                        <th class="py-4 px-4">Khung CEFR</th>
                        <th class="py-4 px-4">Thời gian bài thi</th>
                        <th class="py-4 px-4">Thang đánh giá</th>
                        <th class="py-4 px-4 text-right rounded-r-xl">Chi tiết</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                    <tr class="hover:bg-amber-50/40 transition">
                        <td class="py-4 px-4 font-extrabold text-amber-700 flex items-center gap-2">
                            <span class="h-3 w-3 rounded-full bg-amber-400"></span> Starters
                        </td>
                        <td class="py-4 px-4 font-medium">6 - 8 tuổi</td>
                        <td class="py-4 px-4"><span class="font-bold text-slate-900 bg-amber-100 px-2 py-0.5 rounded">Pre-A1</span></td>
                        <td class="py-4 px-4">~45 phút (Nghe + Đọc/Viết + Nói)</td>
                        <td class="py-4 px-4 font-bold text-amber-600">Tối đa 15 Khiên 🛡️</td>
                        <td class="py-4 px-4 text-right">
                            <a href="<?= e(page_url('cambridge-starters')); ?>" class="inline-flex items-center gap-1 font-bold text-amber-600 hover:underline">
                                Xem trang &rarr;
                            </a>
                        </td>
                    </tr>
                    <tr class="hover:bg-emerald-50/40 transition">
                        <td class="py-4 px-4 font-extrabold text-emerald-700 flex items-center gap-2">
                            <span class="h-3 w-3 rounded-full bg-emerald-500"></span> Movers
                        </td>
                        <td class="py-4 px-4 font-medium">8 - 10 tuổi</td>
                        <td class="py-4 px-4"><span class="font-bold text-slate-900 bg-emerald-100 px-2 py-0.5 rounded">A1</span></td>
                        <td class="py-4 px-4">~60 phút (Nghe + Đọc/Viết + Nói)</td>
                        <td class="py-4 px-4 font-bold text-emerald-600">Tối đa 15 Khiên 🛡️</td>
                        <td class="py-4 px-4 text-right">
                            <a href="<?= e(page_url('cambridge-movers')); ?>" class="inline-flex items-center gap-1 font-bold text-emerald-600 hover:underline">
                                Xem trang &rarr;
                            </a>
                        </td>
                    </tr>
                    <tr class="hover:bg-sky-50/40 transition">
                        <td class="py-4 px-4 font-extrabold text-sky-700 flex items-center gap-2">
                            <span class="h-3 w-3 rounded-full bg-sky-500"></span> Flyers
                        </td>
                        <td class="py-4 px-4 font-medium">10 - 12 tuổi</td>
                        <td class="py-4 px-4"><span class="font-bold text-slate-900 bg-sky-100 px-2 py-0.5 rounded">A2</span></td>
                        <td class="py-4 px-4">~75 phút (Nghe + Đọc/Viết + Nói)</td>
                        <td class="py-4 px-4 font-bold text-sky-600">Tối đa 15 Khiên 🛡️</td>
                        <td class="py-4 px-4 text-right">
                            <a href="<?= e(page_url('cambridge-flyers')); ?>" class="inline-flex items-center gap-1 font-bold text-sky-600 hover:underline">
                                Xem trang &rarr;
                            </a>
                        </td>
                    </tr>
                    <tr class="hover:bg-indigo-50/40 transition">
                        <td class="py-4 px-4 font-extrabold text-indigo-700 flex items-center gap-2">
                            <span class="h-3 w-3 rounded-full bg-indigo-500"></span> KET (A2 Key)
                        </td>
                        <td class="py-4 px-4 font-medium">12 - 14 tuổi</td>
                        <td class="py-4 px-4"><span class="font-bold text-slate-900 bg-indigo-100 px-2 py-0.5 rounded">A2</span></td>
                        <td class="py-4 px-4">~100 phút (Đọc/Viết + Nghe + Nói)</td>
                        <td class="py-4 px-4 font-bold text-indigo-600">100 - 150 Điểm 📊</td>
                        <td class="py-4 px-4 text-right">
                            <a href="<?= e(page_url('cambridge-ket')); ?>" class="inline-flex items-center gap-1 font-bold text-indigo-600 hover:underline">
                                Xem trang &rarr;
                            </a>
                        </td>
                    </tr>
                    <tr class="hover:bg-purple-50/40 transition">
                        <td class="py-4 px-4 font-extrabold text-purple-700 flex items-center gap-2">
                            <span class="h-3 w-3 rounded-full bg-purple-500"></span> PET (B1 Prelim)
                        </td>
                        <td class="py-4 px-4 font-medium">14 - 16+ tuổi</td>
                        <td class="py-4 px-4"><span class="font-bold text-slate-900 bg-purple-100 px-2 py-0.5 rounded">B1</span></td>
                        <td class="py-4 px-4">~130 phút (Đọc + Viết + Nghe + Nói)</td>
                        <td class="py-4 px-4 font-bold text-purple-600">120 - 170 Điểm 📊</td>
                        <td class="py-4 px-4 text-right">
                            <a href="<?= e(page_url('cambridge-pet')); ?>" class="inline-flex items-center gap-1 font-bold text-purple-600 hover:underline">
                                Xem trang &rarr;
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <!-- 3. WHY CAMBRIDGE AT NHUE MINH CENTER -->
    <section>
        <div class="text-center max-w-3xl mx-auto mb-12">
            <span class="rounded-full bg-emerald-50 px-3.5 py-1 text-xs font-bold text-emerald-600 border border-emerald-100">
                💎 Ưu Thế Khi Luyện Thi Tại Nhuệ Minh
            </span>
            <h2 class="mt-3 text-2xl sm:text-4xl font-extrabold text-slate-900">
                Vì Sao 2.000+ Phụ Huynh Lựa Chọn Trung Tâm Nhuệ Minh?
            </h2>
            <p class="mt-2 text-sm text-slate-600">
                Chương trình thiết kế chuẩn khung Cambridge với phương pháp giảng dạy tương tác sinh động.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="rounded-3xl bg-white p-7 shadow-sm border border-slate-100 hover:shadow-md transition">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-100 text-amber-600 text-2xl font-black mb-4">
                    🎯
                </div>
                <h3 class="text-base font-bold text-slate-900">Cam Kết Đầu Ra Khiên</h3>
                <p class="mt-2 text-xs text-slate-600 leading-relaxed">
                    Học viên được kiểm tra định kỳ 4 kỹ năng và cam kết đạt từ 12 - 15 khiên ngay trong kỳ thi chính thức đầu tiên.
                </p>
            </div>

            <div class="rounded-3xl bg-white p-7 shadow-sm border border-slate-100 hover:shadow-md transition">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-100 text-blue-600 text-2xl font-black mb-4">
                    🗣️
                </div>
                <h3 class="text-base font-bold text-slate-900">Giáo Viên Bản Ngữ 1:1</h3>
                <p class="mt-2 text-xs text-slate-600 leading-relaxed">
                    Luyện thi phần Nói (Speaking) 1:1 với giáo viên Anh, Mỹ, Úc giúp học sinh phản xạ tự nhiên không rụt rè.
                </p>
            </div>

            <div class="rounded-3xl bg-white p-7 shadow-sm border border-slate-100 hover:shadow-md transition">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600 text-2xl font-black mb-4">
                    📝
                </div>
                <h3 class="text-base font-bold text-slate-900">Thi Thử Như Thi Thật</h3>
                <p class="mt-2 text-xs text-slate-600 leading-relaxed">
                    Tổ chức Mock Test chuẩn áp lực thời gian thực tế giúp học sinh quen không khí phòng thi và kiểm soát tâm lý.
                </p>
            </div>

            <div class="rounded-3xl bg-white p-7 shadow-sm border border-slate-100 hover:shadow-md transition">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-purple-100 text-purple-600 text-2xl font-black mb-4">
                    📲
                </div>
                <h3 class="text-base font-bold text-slate-900">Báo Cáo Tiến Độ Hàng Tuần</h3>
                <p class="mt-2 text-xs text-slate-600 leading-relaxed">
                    Phụ huynh theo dõi sát sao điểm số từng kỹ năng của con qua Portal học viên minh bạch và tiện lợi.
                </p>
            </div>
        </div>
    </section>

    <!-- 4. FAQ ACCORDION SECTION -->
    <section class="max-w-4xl mx-auto rounded-3xl bg-slate-50 p-8 sm:p-12 border border-slate-200">
        <div class="text-center mb-8">
            <span class="rounded-full bg-indigo-100 px-3.5 py-1 text-xs font-bold text-indigo-700">
                ❓ Giải Đáp Thắc Mắc
            </span>
            <h2 class="mt-3 text-2xl sm:text-3xl font-extrabold text-slate-900">
                Câu Hỏi Thường Gặp Về Kỳ Thi Cambridge
            </h2>
        </div>

        <div class="space-y-4">
            <details class="group rounded-2xl bg-white p-5 border border-slate-200 cursor-pointer transition">
                <summary class="flex items-center justify-between font-bold text-slate-900 text-sm sm:text-base list-none">
                    <span>1. Thi Cambridge Starters, Movers, Flyers có đỗ hay trượt không?</span>
                    <span class="text-slate-400 group-open:rotate-180 transition-transform">▼</span>
                </summary>
                <p class="mt-3 text-xs sm:text-sm text-slate-600 leading-relaxed">
                    Không. Cả 3 kỳ thi YLE này không có khái niệm Đỗ hay Trượt. Tất cả học sinh tham gia đều nhận được chứng chỉ chính thức từ Hội đồng Khảo thí Cambridge với kết quả từ 1 đến 15 Khiên (Shields) chia đều cho 3 phần thi Nghe, Đọc/Viết và Nói. Kết quả từ 10 khiên trở lên được coi là đạt yêu cầu cấp độ.
                </p>
            </details>

            <details class="group rounded-2xl bg-white p-5 border border-slate-200 cursor-pointer transition">
                <summary class="flex items-center justify-between font-bold text-slate-900 text-sm sm:text-base list-none">
                    <span>2. Chứng chỉ Cambridge có thời hạn sử dụng bao lâu?</span>
                    <span class="text-slate-400 group-open:rotate-180 transition-transform">▼</span>
                </summary>
                <p class="mt-3 text-xs sm:text-sm text-slate-600 leading-relaxed">
                    Các chứng chỉ tiếng Anh Cambridge (Starters, Movers, Flyers, KET, PET...) có <strong>giá trị vĩnh viễn</strong> (vô thời hạn). Học sinh chỉ cần thi 1 lần duy nhất để lưu lại hồ sơ chứng nhận năng lực quốc tế.
                </p>
            </details>

            <details class="group rounded-2xl bg-white p-5 border border-slate-200 cursor-pointer transition">
                <summary class="flex items-center justify-between font-bold text-slate-900 text-sm sm:text-base list-none">
                    <span>3. Con tôi bao nhiêu tuổi thì bắt đầu học & thi Starters?</span>
                    <span class="text-slate-400 group-open:rotate-180 transition-transform">▼</span>
                </summary>
                <p class="mt-3 text-xs sm:text-sm text-slate-600 leading-relaxed">
                    Độ tuổi thích hợp nhất để thi Starters là từ 6 - 8 tuổi (khoảng Lớp 1 - Lớp 2). Tuy nhiên, phụ huynh nên cho con làm bài Test trình độ tại Nhuệ Minh để xếp lớp học chính xác theo năng lực thực tế chứ không chỉ dựa vào độ tuổi.
                </p>
            </details>

            <details class="group rounded-2xl bg-white p-5 border border-slate-200 cursor-pointer transition">
                <summary class="flex items-center justify-between font-bold text-slate-900 text-sm sm:text-base list-none">
                    <span>4. Đăng ký thi thử & tư vấn lớp học tại Nhuệ Minh như thế nào?</span>
                    <span class="text-slate-400 group-open:rotate-180 transition-transform">▼</span>
                </summary>
                <p class="mt-3 text-xs sm:text-sm text-slate-600 leading-relaxed">
                    Phụ huynh có thể điền thông tin vào form tư vấn trên website hoặc liên hệ hotline trung tâm. Đội ngũ tư vấn sẽ hỗ trợ đặt lịch test trình độ 4 kỹ năng miễn phí cho con ngay tại trung tâm hoặc online.
                </p>
            </details>
        </div>
    </section>

    <!-- 5. BOTTOM CTA BANNER -->
    <section class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-blue-600 via-indigo-700 to-purple-800 p-8 sm:p-14 text-white shadow-2xl">
        <div class="relative z-10 max-w-3xl">
            <span class="inline-flex items-center gap-2 rounded-full bg-white/20 px-4 py-1 text-xs font-bold text-white backdrop-blur-md mb-4">
                🚀 Khai Phá Tối Đa Tiềm Năng Ngôn Ngữ Của Trẻ
            </span>
            <h2 class="text-2xl sm:text-4xl font-extrabold tracking-tight">
                Sẵn Sàng Cho Con Chinh Phục Chứng Chỉ Quốc Tế Cambridge?
            </h2>
            <p class="mt-3 text-sm sm:text-base text-blue-100 leading-relaxed">
                Hãy đăng ký ngay hôm nay để nhận ưu đãi bài Test đánh giá năng lực 4 kỹ năng chuẩn Cambridge hoàn toàn miễn phí từ các chuyên gia Nhuệ Minh.
            </p>
            <div class="mt-8 flex flex-wrap gap-4">
                <a href="<?= e(page_url('home') . '#dang-ky-tu-van'); ?>" class="rounded-2xl bg-white px-8 py-3.5 text-xs sm:text-sm font-extrabold text-blue-800 shadow-lg hover:bg-blue-50 transition">
                    Đăng Ký Đánh Giá Năng Lực Miễn Phí &rarr;
                </a>
            </div>
        </div>
    </section>

</main>
