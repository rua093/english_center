<?php
$homeUser = auth_user();
$studentProgress = $homeWidgets['student_progress'] ?? null;
$teacherSchedules = $homeWidgets['teacher_schedules'] ?? [];
$homeCourses = $homeCourses ?? [];

$homeFormatFeedbackDate = static function (?string $value): string {
    $value = trim((string) $value);
    if ($value === '') {
        return 'Không rõ thời gian';
    }

    $timestamp = strtotime($value);
    if ($timestamp === false) {
        return $value;
    }

    return date('d/m/Y H:i', $timestamp);
};
?>

<main class="font-jakarta relative overflow-hidden">

    <div id="dynamic-scroll-bg" class="fixed top-0 left-0 w-full h-[400vh] z-[-2] pointer-events-none will-change-transform" 
         style="background: linear-gradient(to bottom, 
            #ffffff 0%,      /* Trắng */
            #e0f2fe 25%,     /* Xanh biển nhạt (Sky 100) */
            #ecfccb 50%,     /* Xanh lá chuối non (Lime 100) */
            #fee2e2 75%,     /* Đỏ nhạt / Hồng phấn (Red 100) */
            #ffffff 100%     /* Về lại Trắng */
         );">
    </div>
    
    <div class="fixed inset-0 z-[-1] pointer-events-none opacity-[0.06]" 
         style="background-image: radial-gradient(#1e3a8a 2px, transparent 2px); background-size: 30px 30px;">
    </div>

    <section id="hero-video" class="relative w-full h-[72vh] sm:h-[80vh] min-h-[420px] sm:min-h-[500px] md:min-h-[600px] flex items-center justify-center mb-16 sm:mb-20 md:mb-24">
        <div class="absolute inset-0 z-0 overflow-hidden bg-black">
            <video autoplay loop muted playsinline class="absolute top-1/2 left-1/2 min-w-full min-h-full w-auto h-auto -translate-x-1/2 -translate-y-1/2 object-cover">
                <source src="assets/videodemo/iilavideo.mp4" type="video/mp4">
            </video>
            <div class="absolute inset-0 bg-gradient-to-r from-blue-950/80 md:from-blue-950/70 via-blue-950/40 md:via-blue-950/20 to-transparent w-full"></div>
        </div>

        <div class="relative z-10 w-full max-w-[1450px] mx-auto px-4 sm:px-6 md:px-10 flex flex-col -mt-16 md:-mt-20">
            <div class="max-w-2xl" data-aos="fade-right">
                <h1 class="text-3xl sm:text-4xl md:text-5xl font-black text-white leading-tight uppercase drop-shadow-lg">
                    GREATER YOU EVERYDAY <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-300 to-white">TRƯỞNG THÀNH HƠN</span> MỖI NGÀY
                </h1>
                <p class="mt-3 sm:mt-4 text-sm sm:text-base md:text-lg text-sky-100 font-medium max-w-lg drop-shadow-md">
                    Đồng hành cùng mỗi học viên để khơi dậy tiềm năng và đam mê trên hành trình học tập trọn đời.
                </p>
            </div>
        </div>

       <div class="absolute bottom-0 left-0 right-0 translate-y-0 sm:translate-y-1/2 z-30 flex flex-col items-center px-4 sm:px-6">
    
        <div class="w-full max-w-[1024px] bg-gradient-to-br from-red-200 via-rose-200 to-lime-200 rounded-2xl sm:rounded-[2rem] shadow-[0_15px_40px_rgba(0,0,0,0.15)] overflow-hidden border border-lime-400/35 ring-1 ring-white/20 relative">

            <div class="h-1.5 w-full bg-gradient-to-r from-amber-400 via-cyan-300 to-sky-400"></div>

            <div class="relative p-6 sm:p-8 flex flex-col md:flex-row items-center justify-between gap-6 md:gap-8">
                
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(255,255,255,0.6),transparent_60%)]"></div>
                
                <div class="relative z-10 w-full md:flex-1 text-center md:text-left">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/90 border border-white text-[10px] font-black uppercase tracking-widest text-slate-800 mb-3 shadow-sm">
                        <span class="w-2 h-2 rounded-full bg-red-600 animate-pulse"></span>
                        Trung tâm Ngoại ngữ <span class="text-red-600">Nhuệ Minh</span>
                    </div>

                    <h3 class="text-xl sm:text-2xl md:text-3xl font-black text-slate-900 leading-tight tracking-tight uppercase mb-2.5">
                        Bứt Phá Giới Hạn <span class="hidden md:inline">-</span> <br class="md:hidden">
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-red-600 to-rose-600">
                            Chinh Phục Ngôn Ngữ
                        </span>
                    </h3>

                    <p class="text-slate-700 text-xs sm:text-sm leading-relaxed font-semibold max-w-2xl mx-auto md:mx-0">
                        <strong class="text-slate-900">Xóa bỏ</strong> rào cản. <strong class="text-slate-900">Khai phóng</strong> tiềm năng. Là Trung tâm Ngoại ngữ sở hữu hệ sinh thái <strong class="text-slate-900">độc quyền</strong>, chúng tôi giúp bạn <strong class="text-slate-900">làm chủ</strong> ngôn ngữ ở tầm vóc cao nhất.
                    </p>
                </div>

                <div class="relative z-10 shrink-0 w-full md:w-auto flex justify-center mt-2 md:mt-0">
                    <a href="<?= e(page_url('register-consultation')); ?>" class="group inline-flex items-center justify-center gap-2 rounded-full bg-gradient-to-r from-amber-400 to-yellow-400 hover:scale-105 transition-all duration-300 text-blue-950 font-black uppercase text-[11px] sm:text-xs px-8 py-3.5 shadow-lg border-[3px] border-white whitespace-nowrap w-full md:w-auto">
                        <i class="fa-solid fa-bolt text-red-600"></i>
                        Đăng ký Test Trình Độ
                    </a>
                </div>

            </div>
        </div>
    </div>
    </section>

    <section id="trang-chu" class="relative bg-transparent pt-12 lg:pt-16 lg:pb-8 overflow-hidden border-b border-blue-100/50">
        <!-- Background Blur -->
        <div class="absolute inset-0 z-0 pointer-events-none">
            <div class="absolute w-[300px] md:w-[600px] h-[300px] md:h-[600px] bg-blue-400/20 blur-[80px] md:blur-[120px] rounded-full -top-20 md:-top-40 -left-20 md:-left-40"></div>
            <div class="absolute w-[250px] md:w-[500px] h-[250px] md:h-[500px] bg-cyan-400/20 blur-[80px] md:blur-[120px] rounded-full bottom-[-80px] md:bottom-[-150px] right-[-80px] md:right-[-150px]"></div>
        </div>

        <div class="relative z-10 max-w-[1450px] mx-auto px-4 sm:px-6 flex flex-col lg:flex-row gap-8 lg:gap-14 items-center lg:items-stretch">

            <!-- LEFT IMAGE -->
            <div class="hidden lg:flex lg:w-5/12 relative items-center justify-center lg:-mt-20" data-aos="fade-right" data-aos-duration="1200">

                <div class="absolute bottom-10 left-1/2 -translate-x-1/2 w-[90%] h-[80%] bg-gradient-to-t from-blue-300/40 to-transparent rounded-[3rem] blur-[60px] -z-10"></div>

                <img src="assets/images/student_girl.png"
                    alt="Học sinh tiêu biểu"
                    class="w-full max-w-[540px] object-contain relative z-10 drop-shadow-[0_20px_40px_rgba(30,58,138,0.25)]">

                <!-- Floating Badge -->
                <div class="absolute top-1/4 -left-4 bg-white/95 backdrop-blur-md px-5 py-4 rounded-2xl shadow-xl border border-blue-50 z-20 animate-bounce"
                    style="animation-duration:2s;">

                    <div class="flex items-center gap-3">

                        <div class="w-10 h-10 rounded-full bg-yellow-100 text-yellow-500 flex items-center justify-center text-xl shadow-inner">
                            <i class="fa-solid fa-star"></i>
                        </div>

                        <div>
                            <h4 class="text-sm font-black text-blue-950 uppercase">
                                Chất lượng
                            </h4>

                            <p class="text-[11px] font-bold text-slate-500">
                                Chuẩn quốc tế
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT CONTENT -->
            <div class="w-full lg:w-7/12 flex flex-col gap-5 sm:gap-6 pb-12 lg:py-6 z-20">

                <!-- HERO QUOTE -->
                <div class="relative min-h-[360px] sm:min-h-[420px]">

                    <!-- MAIN LONG BANNER -->
                    <div class="group relative rounded-[2rem] sm:rounded-[2.5rem]
                                p-8 sm:p-10 md:p-12
                                bg-gradient-to-br from-sky-400 via-blue-500 to-blue-700
                                shadow-[0_20px_60px_rgba(37,99,235,0.25)]
                                overflow-visible h-auto sm:min-h-[320px] flex items-start pr-56 sm:pr-72 md:pr-80">

                        <!-- Glow -->
                        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(255,255,255,0.25),transparent_40%)]"></div>

                        <!-- Decorative -->
                        <i class="fa-solid fa-rocket 
                                absolute -right-8 sm:-right-10 bottom-0
                                text-[8rem] sm:text-[11rem]
                                opacity-10 text-white
                                group-hover:scale-110 transition-transform duration-700"></i>

                        <!-- Text -->
                        <div class="relative z-10 max-w-xl flex-1">

                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full
                                        bg-white/15 border border-white/20 backdrop-blur-md mb-5">

                                <span class="w-2 h-2 rounded-full bg-cyan-200 animate-pulse"></span>

                                <span class="text-[10px] sm:text-xs uppercase tracking-[0.2em] font-bold text-blue-50">
                                    Nhuệ Minh Edu
                                </span>
                            </div>

                            <h1 class="text-3xl sm:text-4xl md:text-5xl
                                    font-black leading-[1.08]
                                    text-white drop-shadow-lg">

                                Khát Vọng <br>
                                Là Khởi Đầu
                            </h1>
                        </div>
                        <!-- FLOATING SMALL CARD - Inside Banner -->
                        <div class="absolute
                                    top-1/2 -translate-y-1/2
                                    right-2 sm:right-4 md:right-6
                                    w-[252px] sm:w-[324px] md:w-[378px]
                                    h-[90%]
                                    z-20">

                            <div class="group relative rounded-[1.8rem] sm:rounded-[2rem]
                                        bg-white/95 backdrop-blur-xl
                                        border border-blue-100
                                        shadow-[0_15px_40px_rgba(15,23,42,0.12)]
                                        overflow-hidden p-5 sm:p-7 md:p-9
                                        h-full flex flex-col justify-center">

                                <!-- Gradient -->
                                <div class="absolute inset-0 bg-gradient-to-br from-white via-blue-50/40 to-cyan-50/50"></div>

                                <!-- Decorative Blur -->
                                <div class="absolute -top-4 -right-4 w-20 h-20 rounded-full bg-blue-100/50 blur-2xl"></div>

                                <div class="relative z-10 flex items-start gap-4 sm:gap-5">

                                    <!-- Icon -->
                                    <div class="w-14 h-14 sm:w-16 sm:h-16 md:w-20 md:h-20 rounded-2xl
                                                bg-gradient-to-br from-amber-300 to-yellow-400
                                                text-amber-900
                                                flex items-center justify-center
                                                text-2xl sm:text-3xl
                                                shadow-inner shrink-0">

                                        <i class="fa-solid fa-trophy"></i>
                                    </div>

                                    <!-- Text -->
                                    <div class="flex-1">

                                        <div class="text-xs uppercase tracking-[0.18em]
                                                    text-slate-500 font-black mb-2">
                                            Thành Tựu
                                        </div>

                                        <h2 class="text-xl sm:text-2xl md:text-3xl font-black leading-tight text-blue-950">
                                            Của Mọi <br>
                                            Thành Công
                                        </h2>

                                        <p class="mt-2 text-xs sm:text-sm text-slate-600 leading-relaxed font-medium">
                                            Mỗi bước tiến hôm nay sẽ mở ra cơ hội tương lai.
                                        </p>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                <!-- FEATURE CARDS -->
                <div class="grid sm:grid-cols-2 gap-4 sm:gap-5 md:gap-6 pt-4 sm:pt-5 md:pt-6 pb-4 sm:pb-5 md:pb-6">

                    <!-- Card 1 -->
                    <div class="group relative rounded-[2rem] p-8
                                bg-gradient-to-br from-rose-600 to-red-500
                                shadow-[0_15px_30px_rgba(225,29,72,0.2)]
                                overflow-hidden h-48 flex flex-col justify-end">

                        <div class="absolute top-5 left-5 bg-white text-red-600
                                    text-xs font-black px-4 py-1.5
                                    rounded-full uppercase shadow-md">

                            Đánh Giá
                        </div>

                        <i class="fa-solid fa-chalkboard-user
                                absolute -right-6 -top-6
                                text-[9rem] opacity-15"></i>

                        <div class="relative z-10">

                            <h3 class="text-2xl font-black text-white mb-2">
                                Kiểm Tra Năng Lực
                            </h3>

                            <p class="text-sm text-rose-100 leading-relaxed">
                                Đánh giá chính xác trình độ tiếng Anh theo chuẩn quốc tế hiện đại.
                            </p>
                        </div>
                    </div>

                    <!-- Card 2 -->
                    <div class="group relative rounded-[2rem] p-8
                                bg-gradient-to-br from-blue-600 to-sky-500
                                shadow-[0_15px_30px_rgba(37,99,235,0.2)]
                                overflow-hidden h-48 flex flex-col justify-end">

                        <i class="fa-solid fa-book-open
                                absolute -right-6 -bottom-6
                                text-[9rem] opacity-15"></i>

                        <div class="relative z-10">

                            <h3 class="text-2xl font-black text-white mb-2">
                                Học Liệu Chuyên Sâu
                            </h3>

                            <p class="text-sm text-blue-100 leading-relaxed">
                                Giáo trình chọn lọc và cập nhật liên tục theo xu hướng giáo dục toàn cầu.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- VALUE BLOCKS -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 md:gap-5 pt-4 sm:pt-5 md:pt-6">

                    <!-- Block -->
                    <div class="group rounded-2xl p-5 bg-white/90 backdrop-blur-xl border border-blue-100 shadow-sm text-center">
                        <div class="w-12 h-12 mx-auto mb-3 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                            <i class="fa-solid fa-comments"></i>
                        </div>

                        <h4 class="font-extrabold text-blue-950 text-sm">
                            Giao Tiếp Tự Tin
                        </h4>

                        <span class="text-[10px] text-slate-500 font-semibold">
                            Phản xạ tiếng Anh tự nhiên
                        </span>
                    </div>

                    <!-- Block -->
                    <div class="group rounded-2xl p-5 bg-white/90 backdrop-blur-xl border border-emerald-100 shadow-sm text-center">
                        <div class="w-12 h-12 mx-auto mb-3 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <i class="fa-solid fa-earth-asia"></i>
                        </div>

                        <h4 class="font-extrabold text-blue-950 text-sm">
                            Hội Nhập Quốc Tế
                        </h4>

                        <span class="text-[10px] text-slate-500 font-semibold">
                            Tư duy công dân toàn cầu
                        </span>
                    </div>

                    <!-- Block -->
                    <div class="group rounded-2xl p-5 bg-white/90 backdrop-blur-xl border border-amber-100 shadow-sm text-center">
                        <div class="w-12 h-12 mx-auto mb-3 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                            <i class="fa-solid fa-lightbulb"></i>
                        </div>

                        <h4 class="font-extrabold text-blue-950 text-sm">
                            Tư Duy Hiện Đại
                        </h4>

                        <span class="text-[10px] text-slate-500 font-semibold">
                            Học tập chủ động
                        </span>
                    </div>

                    <!-- Block -->
                    <div class="group rounded-2xl p-5 bg-white/90 backdrop-blur-xl border border-rose-100 shadow-sm text-center">
                        <div class="w-12 h-12 mx-auto mb-3 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                            <i class="fa-solid fa-rocket"></i>
                        </div>

                        <h4 class="font-extrabold text-blue-950 text-sm">
                            Bứt Phá Tương Lai
                        </h4>

                        <span class="text-[10px] text-slate-500 font-semibold">
                            Sẵn sàng cho mọi hành trình
                        </span>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <?php include __DIR__ . '/../partials/social_contact.php'; ?>

    <section id="khoa-hoc" class="pt-6 pb-14 md:pt-8 md:pb-18 relative overflow-hidden bg-transparent">
        <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 relative z-10">
            <div class="mb-10 sm:mb-14 text-center" data-aos="fade-up">
                <h2 class="text-2xl sm:text-3xl md:text-5xl font-black text-[#2e3192] uppercase tracking-tight">
                    KHOÁ HỌC DÀNH CHO <span class="inline-block mt-2 md:mt-0 rounded-full bg-red-600 px-4 sm:px-6 py-1.5 sm:py-2 text-white shadow-lg transform -rotate-2">MỌI MỤC TIÊU</span>
                </h2>
                <p class="mt-4 sm:mt-6 text-sm sm:text-base md:text-lg text-slate-700 max-w-3xl mx-auto font-semibold">
                    Dễ dàng lựa chọn khóa học tiếng Anh phù hợp cho riêng mình với chương trình học đa dạng, được thiết kế phù hợp với nhu cầu và trình độ thực tế.
                </p>
            </div>

            <div class="rounded-[2rem] sm:rounded-[3rem] bg-white/40 backdrop-blur-md border border-white p-4 sm:p-6 md:p-8 lg:p-10 shadow-[0_15px_40px_rgba(30,58,138,0.06)] overflow-hidden" data-aos="zoom-in">
                <?php if (empty($homeCourses)): ?>
                    <div class="rounded-[2rem] border border-dashed border-slate-300 bg-white/70 px-6 py-14 text-center text-slate-500 font-medium">
                        Hiện chưa có khóa học nào trong hệ thống.
                    </div>
                <?php else: ?>
                    <div class="grid gap-5 sm:gap-6 sm:grid-cols-2 lg:grid-cols-4 mobile-swipe-track">
                        <?php foreach ($homeCourses as $course): ?>
                            <?php
                            $courseTitle = (string) ($course['title'] ?? '');
                            $courseSlug = (string) ($course['slug'] ?? '');
                            $courseImage = (string) ($course['image'] ?? '');
                            $courseDesc = trim((string) ($course['short_desc'] ?? ''));
                            $courseLink = page_url('courses', ['course' => $courseSlug]);
                            ?>
                            <article class="mobile-swipe-card group flex flex-col overflow-hidden rounded-[1.5rem] sm:rounded-[2rem] bg-white/90 shadow-lg border border-rose-100/70 transition-all duration-300 hover:-translate-y-3 hover:shadow-xl hover:shadow-rose-100/50">
                                <div class="relative h-48 sm:h-56 overflow-hidden bg-gradient-to-br from-rose-50 via-white to-emerald-50">
                                    <?php if ($courseImage !== ''): ?>
                                        <img src="<?= e($courseImage); ?>" alt="<?= e($courseTitle); ?>" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                                        <div class="absolute inset-0 bg-gradient-to-t from-red-500/35 via-rose-400/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                    <?php else: ?>
                                        <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-rose-50 via-white to-emerald-50">
                                            <div class="text-center text-red-500">
                                                <div class="mx-auto mb-3 flex h-16 w-16 sm:h-20 sm:w-20 items-center justify-center rounded-full bg-white/90 shadow-lg ring-1 ring-rose-100">
                                                    <i class="fa-solid fa-book-open text-2xl sm:text-3xl text-red-400"></i>
                                                </div>
                                                <div class="text-[10px] sm:text-xs font-black uppercase tracking-[0.22em] text-slate-700">Ảnh khóa học</div>
                                            </div>
                                        </div>
                                        <div class="absolute inset-0 bg-gradient-to-t from-red-500/12 via-rose-400/8 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                    <?php endif; ?>
                                    <div class="absolute top-3 sm:top-4 right-3 sm:right-4 bg-gradient-to-r from-red-500 to-emerald-500 text-white rounded-xl sm:rounded-2xl px-2 sm:px-3 py-1.5 sm:py-2 text-center shadow-md backdrop-blur-sm ring-1 ring-white/70">
                                        <span class="block text-[8px] sm:text-[10px] uppercase font-bold opacity-90">Buổi học</span>
                                        <span class="block text-xl sm:text-2xl font-black leading-none"><?= (int) ($course['total_sessions'] ?? 0); ?></span>
                                    </div>
                                </div>
                                <div class="flex flex-1 flex-col p-5 sm:p-6">
                                    <div class="inline-flex w-fit rounded-full bg-gradient-to-r from-emerald-50 to-rose-50 px-2.5 py-1 text-[9px] sm:text-[10px] font-black uppercase tracking-[0.18em] text-emerald-700 ring-1 ring-emerald-100/80">
                                        <?= e((string) ($course['level'] ?? 'Khóa học')); ?>
                                    </div>
                                    <h3 class="mt-2 sm:mt-3 text-lg sm:text-xl font-extrabold uppercase leading-tight text-transparent bg-clip-text bg-gradient-to-r from-red-500 via-rose-500 to-emerald-500 transition-colors"><?= e($courseTitle); ?></h3>
                                    <p class="mt-2 sm:mt-3 text-xs sm:text-sm font-semibold text-slate-700 flex-1 leading-relaxed">
                                        <?= e($courseDesc !== '' ? $courseDesc : 'Chương trình học được xây dựng theo lộ trình rõ ràng, phù hợp cho từng học viên.'); ?>
                                    </p>
                                    <div class="mt-4 sm:mt-5 pt-3 sm:pt-4 border-t-2 border-slate-100 flex flex-col gap-3 sm:gap-4">
                                        <div class="flex items-end justify-between gap-3 sm:gap-4">
                                            <div>
                                                <span class="block text-[10px] sm:text-xs font-bold text-slate-700 uppercase tracking-wide">Học phí từ</span>
                                                <span class="text-lg sm:text-xl font-black text-transparent bg-clip-text bg-gradient-to-r from-red-500 to-emerald-500"><?= e((string) ($course['price'] ?? '0đ')); ?></span>
                                            </div>
                                            <div class="text-right text-[10px] sm:text-xs font-semibold text-slate-700">
                                                <div><?= (int) ($course['roadmap_count'] ?? 0); ?> lộ trình</div>
                                                <div><?= (int) ($course['class_count'] ?? 0); ?> lớp học</div>
                                            </div>
                                        </div>
                                        <a href="<?= e($courseLink); ?>" class="inline-flex items-center justify-center gap-2 rounded-full bg-gradient-to-r from-red-500 via-rose-500 to-emerald-500 px-4 py-2.5 sm:py-3 text-xs sm:text-sm font-bold text-white shadow-md transition-all hover:-translate-y-0.5 hover:from-red-600 hover:to-emerald-600 hover:shadow-lg">
                                            Xem chi tiết <i class="fa-solid fa-arrow-right text-[10px] sm:text-xs"></i>
                                        </a>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section id="dang-ky-tu-van" class="relative py-20 md:py-32 overflow-hidden">
        <!-- Background image hero banner -->
        <div class="absolute inset-0">
            <img src="/assets/images/consult.jpg" alt="Sinh viên học tập" class="h-full w-full object-cover brightness-110 contrast-105 saturate-105">
            <!-- Slight horizontal darkening to keep text legible -->
            <div class="absolute inset-0 bg-gradient-to-r from-slate-900/28 via-slate-900/8 to-transparent"></div>
            <!-- Top fade: blend the top edge into the page background for smooth transition -->
            <div class="absolute inset-x-0 top-0 h-28 md:h-36 bg-gradient-to-b from-white/95 to-transparent pointer-events-none"></div>
            <!-- Bottom fade: blend the bottom edge into the page background for smooth transition -->
            <div class="absolute inset-x-0 bottom-0 h-28 md:h-36 bg-gradient-to-t from-white/95 to-transparent pointer-events-none"></div>
            <!-- Subtle light reflection (white psychology - cleanliness, trust) -->
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(255,255,255,0.12),_transparent_45%)]"></div>
        </div>

        <!-- Content overlay -->
        <div class="relative z-10 mx-auto max-w-[1450px] px-4 sm:px-6">
            <div class="grid gap-8 lg:gap-12 lg:grid-cols-2 items-center">
                <!-- Left side: Text content -->
                <div class="max-w-2xl" data-aos="fade-right" data-aos-duration="700">
                    <span class="inline-flex items-center gap-2 rounded-full border border-rose-300/40 bg-gradient-to-r from-rose-600 to-rose-500 px-4 py-2 text-[10px] font-black uppercase tracking-[0.2em] text-white shadow-lg shadow-rose-500/25 backdrop-blur-sm transition-transform hover:-translate-y-0.5">
                        <span class="h-2 w-2 rounded-full bg-white animate-pulse"></span>
                        Tư vấn nhanh 1:1
                    </span>

                    <h2 class="mt-8 text-4xl md:text-5xl lg:text-6xl font-black leading-tight tracking-tight text-white">
                        Bắt đầu hành trình <br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-rose-200 to-orange-200">chinh phục Anh ngữ</span>
                    </h2>
                    
                    <p class="mt-6 max-w-xl text-base md:text-lg leading-relaxed text-white/85">
                        Hãy để lại thông tin, đội ngũ học thuật của chúng tôi sẽ thiết kế riêng một lộ trình tối ưu nhất dựa trên mục tiêu và năng lực của bạn.
                    </p>

                    <div class="mt-10 grid gap-4 sm:grid-cols-3 max-w-lg">
                        <div class="rounded-[1.5rem] border border-white/18 bg-white/10 p-5 backdrop-blur-sm shadow-lg">
                            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-white/15 text-white">
                                <i class="fa-regular fa-clock text-sm"></i>
                            </div>
                            <p class="text-2xl font-black text-white">15'</p>
                            <p class="mt-1 text-[9px] font-bold uppercase tracking-widest text-white/70">Liên hệ ngay</p>
                        </div>
                        <div class="rounded-[1.5rem] border border-white/18 bg-white/10 p-5 backdrop-blur-sm shadow-lg">
                            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-white/15 text-white">
                                <i class="fa-solid fa-user-group text-sm"></i>
                            </div>
                            <p class="text-2xl font-black text-white">1:1</p>
                            <p class="mt-1 text-[9px] font-bold uppercase tracking-widest text-white/70">Chuyên gia</p>
                        </div>
                        <div class="rounded-[1.5rem] border border-white/18 bg-white/10 p-5 backdrop-blur-sm shadow-lg">
                            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-white/15 text-white">
                                <i class="fa-solid fa-wand-magic-sparkles text-sm"></i>
                            </div>
                            <p class="text-2xl font-black text-white">100%</p>
                            <p class="mt-1 text-[9px] font-bold uppercase tracking-widest text-white/70">Cá nhân hóa</p>
                        </div>
                    </div>
                </div>

                <!-- Right side: Form panel overlay - Psychology: White (trust/cleanliness) + Rose (action) + Emerald (growth) -->
                <div class="relative overflow-hidden rounded-[2.75rem] border border-white/20 bg-transparent p-8 md:p-10 shadow-none backdrop-blur-none" data-aos="fade-left" data-aos-duration="700" data-aos-delay="100">
                    <!-- Subtle emerald tint (trust, growth psychology) -->
                    <div class="absolute right-[-10%] top-[-10%] h-56 w-56 rounded-full bg-gradient-to-br from-rose-50/90 to-pink-50/70 blur-3xl pointer-events-none"></div>
                    <!-- Emerald for confidence/growth psychology -->
                    <div class="absolute bottom-[-10%] left-[-10%] h-44 w-44 rounded-full bg-emerald-50/85 blur-3xl pointer-events-none"></div>

                    <div class="relative z-10 mb-10 border-b border-white/15 pb-8">
                        <!-- Heading: White with 3D shadow effect + Rose accent -->
                        <h3 class="text-3xl md:text-[2rem] font-black text-white tracking-tight mb-3" style="text-shadow: 
                            2px 2px 0 rgba(15, 23, 42, 0.15),
                            4px 4px 0 rgba(15, 23, 42, 0.12),
                            6px 6px 0 rgba(15, 23, 42, 0.08),
                            0 8px 16px rgba(15, 23, 42, 0.25),
                            0 0 1px rgba(255, 255, 255, 0.8);
                        ">
                            Đăng ký tư vấn
                            <span class="ml-2 text-transparent bg-clip-text bg-gradient-to-r from-rose-400 to-rose-600" style="text-shadow: 
                                2px 2px 0 rgba(244, 63, 94, 0.2),
                                4px 4px 0 rgba(244, 63, 94, 0.15),
                                0 6px 12px rgba(244, 63, 94, 0.2);
                            ">miễn phí</span>
                        </h3>
                        <!-- Subheading: Trust messaging (emerald psychology) -->
                        <p class="text-sm font-semibold text-white/85">
                            <i class="fa-solid fa-check-circle text-emerald-500 mr-2"></i>
                            Chuyên gia sẽ thiết kế lộ trình phù hợp cho bạn
                        </p>
                    </div>

                    <form class="relative z-10 grid gap-6 sm:grid-cols-2">
                        <!-- Name field: Rose psychology (action/engagement) -->
                        <div class="sm:col-span-2 group">
                            <label class="mb-3 flex items-center gap-2 text-[11px] font-black uppercase tracking-[0.16em] text-white group-focus-within:text-rose-300 transition-colors">
                                <i class="fa-solid fa-user text-rose-500"></i>
                                Họ và tên <span class="text-rose-500 text-base">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-5 top-1/2 -translate-y-1/2 text-rose-400 group-focus-within:text-rose-500 transition-colors"><i class="fa-regular fa-user"></i></span>
                                <input type="text" required placeholder="Nhập họ và tên của bạn" class="w-full rounded-2xl border border-slate-200 bg-white py-4 pl-14 pr-5 text-sm font-bold text-slate-900 shadow-sm outline-none transition-all placeholder:text-slate-400 placeholder:font-medium focus:border-rose-400 focus:ring-4 focus:ring-rose-500/15 focus:shadow-lg focus:shadow-rose-500/10">
                            </div>
                        </div>

                        <!-- Phone field: Rose for action/contact -->
                        <div class="group">
                            <label class="mb-3 flex items-center gap-2 text-[11px] font-black uppercase tracking-[0.16em] text-white group-focus-within:text-rose-300 transition-colors">
                                <i class="fa-solid fa-phone text-rose-500"></i>
                                Số điện thoại <span class="text-rose-500 text-base">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-5 top-1/2 -translate-y-1/2 text-rose-400 group-focus-within:text-rose-500 transition-colors"><i class="fa-solid fa-phone"></i></span>
                                <input type="tel" required placeholder="09xx xxx xxx" class="w-full rounded-2xl border border-slate-200 bg-white py-4 pl-14 pr-5 text-sm font-bold text-slate-900 shadow-sm outline-none transition-all placeholder:text-slate-400 placeholder:font-medium focus:border-rose-400 focus:ring-4 focus:ring-rose-500/15 focus:shadow-lg focus:shadow-rose-500/10">
                            </div>
                        </div>

                        <!-- Date field: Emerald for info/optional (growth psychology) -->
                        <div class="group">
                            <label class="mb-3 flex items-center gap-2 text-[11px] font-black uppercase tracking-[0.16em] text-white group-focus-within:text-emerald-300 transition-colors">
                                <i class="fa-solid fa-calendar text-emerald-500"></i>
                                Ngày sinh
                            </label>
                            <div class="relative">
                                <span class="absolute left-5 top-1/2 -translate-y-1/2 text-emerald-400 group-focus-within:text-emerald-500 transition-colors"><i class="fa-regular fa-calendar"></i></span>
                                <input type="date" class="w-full rounded-2xl border border-slate-200 bg-white py-4 pl-14 pr-5 text-sm font-bold text-slate-900 shadow-sm outline-none transition-all focus:border-emerald-400 focus:ring-4 focus:ring-emerald-500/15 focus:shadow-lg focus:shadow-emerald-500/10">
                            </div>
                        </div>

                        <!-- Notes field: Emerald for feedback (confidence in sharing) -->
                        <div class="sm:col-span-2 group">
                            <label class="mb-3 flex items-center gap-2 text-[11px] font-black uppercase tracking-[0.16em] text-white group-focus-within:text-emerald-300 transition-colors">
                                <i class="fa-solid fa-message text-emerald-500"></i>
                                Ghi chú mong muốn
                            </label>
                            <textarea rows="3" placeholder="Bạn muốn học khóa nào, hoặc khung giờ rảnh của bạn là gì?..." class="w-full rounded-2xl border border-slate-200 bg-white p-5 text-sm font-bold text-slate-900 shadow-sm outline-none transition-all placeholder:text-slate-400 placeholder:font-medium focus:border-emerald-400 focus:ring-4 focus:ring-emerald-500/15 focus:shadow-lg focus:shadow-emerald-500/10 resize-none"></textarea>
                        </div>

                        <!-- CTA Button: Rose (urgency/action psychology) + Emerald accent (trust) -->
                        <button type="submit" class="sm:col-span-2 mt-2 group relative inline-flex items-center justify-center gap-3 rounded-2xl bg-gradient-to-r from-rose-500 to-rose-600 px-8 py-4 text-sm font-black uppercase tracking-widest text-white shadow-lg shadow-rose-500/30 transition-all duration-300 hover:-translate-y-1.5 hover:from-rose-600 hover:to-rose-700 hover:shadow-rose-600/50 active:translate-y-0 active:shadow-rose-500/20">
                            <span class="flex items-center gap-2">
                                Gửi yêu cầu ngay
                                <i class="fa-solid fa-arrow-right transition-transform group-hover:translate-x-1"></i>
                            </span>
                            <!-- Subtle success indicator (emerald) -->
                            <span class="absolute -top-1 -right-1 hidden h-3 w-3 rounded-full bg-emerald-400 animate-pulse group-hover:block"></span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- <section id="lien-he" class="relative py-10 sm:py-14 md:py-20 overflow-hidden bg-transparent z-10">
        <div class="mx-auto w-full max-w-[1400px] flex flex-col lg:flex-row">

            <div class="w-full lg:w-1/2 flex flex-col justify-center px-4 sm:px-6 py-10 sm:py-16 lg:px-16 xl:px-32 z-10" data-aos="fade-right">
                
                <h2 class="text-2xl sm:text-[28px] md:text-[36px] font-bold text-[#185b9d] mb-4 sm:mb-5 tracking-tight">
                    Tư vấn và kiểm tra miễn phí
                </h2>

                <p class="max-w-xl text-slate-600 text-sm sm:text-base md:text-lg font-medium leading-relaxed mb-6 sm:mb-8">
                    Đăng ký ngay để được đội ngũ tư vấn hỗ trợ lộ trình học phù hợp, kiểm tra trình độ và nhận gợi ý khóa học tối ưu nhất.
                </p>

                <div class="inline-flex flex-col items-start gap-4 max-w-[420px]">
                    <a href="<?= e(page_url('register-consultation')); ?>" class="group relative inline-flex items-center justify-center rounded-full sm:rounded-[1.5rem] bg-[#2e3192] px-6 sm:px-8 py-4 sm:py-5 text-white font-black uppercase tracking-[0.18em] shadow-[0_18px_40px_rgba(46,49,146,0.28)] transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_24px_50px_rgba(46,49,146,0.38)] active:translate-y-0 focus:outline-none focus:ring-4 focus:ring-blue-400/30 w-full sm:w-auto">
                        <span class="absolute inset-x-4 top-0 h-2 rounded-full bg-white/25 blur-sm"></span>
                        <span class="relative flex items-center gap-2 sm:gap-3 text-sm sm:text-base md:text-lg">
                            <i class="fa-solid fa-pen-to-square transition-transform duration-300 group-hover:rotate-[-8deg]"></i>
                            Đăng ký ngay
                        </span>
                    </a>

                    <div class="flex items-center gap-2 sm:gap-3 text-xs sm:text-sm text-slate-500 font-medium bg-white/70 backdrop-blur-sm border border-slate-200 rounded-xl sm:rounded-2xl px-3 sm:px-4 py-2 sm:py-3 shadow-sm">
                        <div class="w-7 h-7 sm:w-9 sm:h-9 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <span>Chỉ mất vài giây để mở form đăng ký tư vấn.</span>
                    </div>
                </div>
            </div>

            <div class="w-full lg:w-1/2 relative min-h-[300px] sm:min-h-[400px] lg:min-h-[600px] mt-8 lg:mt-0" data-aos="fade-left">
                
                <img src="assets/images/tu_van_student.jpg" alt="Học sinh" class="absolute inset-0 w-full h-full object-cover object-top lg:object-center rounded-3xl lg:rounded-none">

                <div class="absolute inset-x-0 top-0 h-24 bg-gradient-to-b from-[#f4f7fb] to-transparent pointer-events-none lg:hidden"></div>

                <div class="absolute inset-y-0 left-0 w-24 md:w-32 bg-gradient-to-r from-[#f4f7fb] to-transparent pointer-events-none hidden lg:block"></div>

                <div class="absolute inset-y-0 right-0 w-24 md:w-32 bg-gradient-to-l from-[#f4f7fb] to-transparent pointer-events-none hidden lg:block"></div>

            </div>

        </div>
    </section> -->

    <section id="gioi-thieu" class="relative py-16 sm:py-20 md:py-28 overflow-hidden bg-transparent">
        <div class="absolute top-[-5%] right-[-5%] w-[250px] sm:w-[400px] lg:w-[500px] h-[250px] sm:h-[400px] lg:h-[500px] bg-gradient-to-br from-blue-300/40 to-sky-200/40 rounded-full blur-2xl sm:blur-3xl mix-blend-multiply pointer-events-none"></div>
        <div class="absolute bottom-[-5%] left-[-5%] w-[200px] sm:w-[300px] lg:w-[400px] h-[200px] sm:h-[300px] lg:h-[400px] bg-gradient-to-tr from-cyan-200/40 to-blue-200/40 rounded-full blur-2xl sm:blur-3xl mix-blend-multiply pointer-events-none"></div>

        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 grid lg:grid-cols-2 gap-10 sm:gap-16 md:gap-24 items-center">
            <div class="relative px-4 sm:px-0" data-aos="fade-right">
                <div class="absolute -bottom-4 -right-4 sm:-bottom-6 sm:-right-6 w-full h-full rounded-[2rem] sm:rounded-[2.5rem] bg-gradient-to-br from-blue-600/10 to-cyan-500/10 border border-blue-900/5"></div>
                <div class="relative rounded-[2rem] sm:rounded-[2.5rem] overflow-hidden shadow-[0_20px_50px_rgba(30,58,138,0.15)] group border-[4px] sm:border-[6px] border-white/80">
                    <img src="/assets/images/center2.jpg" alt="Vị trí trung tâm" class="w-full h-[300px] sm:h-[400px] lg:h-[500px] object-cover transform group-hover:scale-105 transition duration-700 ease-in-out">
                    <div class="absolute inset-0 bg-gradient-to-t from-blue-950/60 via-blue-950/20 to-transparent opacity-80"></div>
                </div>
                <div class="absolute -bottom-6 right-2 sm:-bottom-8 sm:right-4 md:-right-4 bg-white/95 backdrop-blur-md px-4 sm:px-6 py-3 sm:py-4 rounded-2xl shadow-[0_15px_40px_rgba(30,58,138,0.15)] border border-blue-50 hover:-translate-y-1 transition-transform cursor-default z-20">
                    <div class="flex items-center gap-3 sm:gap-4">
                        <div class="relative flex h-10 w-10 sm:h-12 sm:w-12 items-center justify-center">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-30"></span>
                            <div class="relative w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center text-white shadow-md">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </div>
                        </div>
                        <div>
                            <p class="text-[9px] sm:text-[10px] uppercase tracking-widest text-blue-600 font-black mb-0.5 flex items-center gap-1.5">Vị trí trung tâm</p>
                            <h4 class="text-xs sm:text-sm font-black text-blue-950">Quảng Phú – Đà Nẵng</h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-5 sm:space-y-7 mt-8 lg:mt-0" data-aos="fade-left">
                <div class="inline-flex items-center gap-2 sm:gap-3 px-4 sm:px-5 py-2 sm:py-3 rounded-full bg-gradient-to-r from-white to-blue-50 border border-blue-200 shadow-md shadow-blue-100/60 ring-1 ring-white/70">
                    <span class="w-2 h-2 sm:w-3 sm:h-3 rounded-full bg-blue-500 animate-pulse shadow-[0_0_0_6px_rgba(59,130,246,0.12)]"></span>
                    <span class="text-blue-900 text-xs sm:text-sm md:text-base font-black uppercase tracking-[0.22em]">Về <span class="text-red-600">Nhuệ Minh </span>Edu</span>
                </div>
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-extrabold leading-[1.15] text-blue-950">Nâng tầm ngoại ngữ,<br><span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-cyan-500">Khơi nguồn tự tin</span></h2>
                <p class="text-base sm:text-lg text-slate-600 leading-relaxed font-medium">Trung tâm Ngoại ngữ hiện đại với không gian học tập truyền cảm hứng, cam kết mang lại giá trị thực tế, giúp học viên phát triển toàn diện 4 kỹ năng và sẵn sàng hội nhập.</p>

                <div class="grid sm:grid-cols-2 gap-4 sm:gap-5 mt-4 sm:mt-6">
                    <div class="bg-white/80 backdrop-blur-sm p-5 sm:p-6 rounded-2xl shadow-sm border border-blue-100 hover:-translate-y-1 hover:shadow-lg transition-all duration-300 group">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br from-blue-400 to-blue-600 text-white flex items-center justify-center mb-3 sm:mb-4 shadow-sm group-hover:scale-110 transition-transform"><i class="fa-solid fa-map-location-dot"></i></div>
                        <h4 class="font-extrabold text-blue-950 mb-1 text-sm sm:text-base">Vị trí thuận lợi</h4>
                        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">Dễ dàng di chuyển, gần khu dân cư & trường học.</p>
                    </div>
                    <div class="bg-white/80 backdrop-blur-sm p-5 sm:p-6 rounded-2xl shadow-sm border border-teal-100 hover:-translate-y-1 hover:shadow-lg transition-all duration-300 group">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br from-teal-400 to-teal-600 text-white flex items-center justify-center mb-3 sm:mb-4 shadow-sm group-hover:scale-110 transition-transform"><i class="fa-solid fa-shield-halved"></i></div>
                        <h4 class="font-extrabold text-teal-950 mb-1 text-sm sm:text-base">Môi trường an toàn</h4>
                        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">Không gian học tập hiện đại, thân thiện và an ninh.</p>
                    </div>
                </div>
                <div class="pt-2 sm:pt-4">
                    <a href="#lien-he" class="inline-flex items-center justify-center gap-2 sm:gap-3 px-6 sm:px-8 py-3 sm:py-4 rounded-full bg-gradient-to-r from-blue-600 to-sky-500 text-white font-bold text-sm sm:text-base shadow-[0_10px_20px_rgba(37,99,235,0.3)] transition-all hover:-translate-y-1 hover:shadow-[0_15px_25px_rgba(37,99,235,0.4)]">
                        Khám phá ngay <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section id="SU-menh" class="pt-12 pb-8 md:pt-20 md:pb-10 relative overflow-hidden bg-transparent z-10">
        <div class="absolute inset-0 pointer-events-none z-0 overflow-hidden hidden sm:block">
            <div class="absolute top-6 left-4 sm:top-10 sm:left-8 md:top-12 md:left-12 flex flex-col gap-4 text-slate-300 rotate-[-8deg]">
                <i class="fa-solid fa-paper-plane text-4xl md:text-5xl opacity-55"></i>
                <i class="fa-solid fa-earth-americas text-5xl md:text-6xl ml-6 opacity-55"></i>
                <i class="fa-solid fa-book-open text-4xl md:text-5xl ml-10 opacity-55"></i>
            </div>
            <div class="absolute top-6 right-4 sm:top-10 sm:right-8 md:top-12 md:right-12 flex flex-col gap-4 items-end text-slate-300 rotate-[10deg]">
                <i class="fa-solid fa-graduation-cap text-4xl md:text-5xl opacity-55"></i>
                <i class="fa-solid fa-lightbulb text-5xl md:text-6xl mr-6 opacity-55"></i>
                <i class="fa-solid fa-rocket text-4xl md:text-5xl mr-10 opacity-55"></i>
            </div>
            <div class="absolute bottom-6 left-4 sm:bottom-10 sm:left-8 md:bottom-12 md:left-12 flex flex-col gap-4 text-slate-300 rotate-[8deg]">
                <i class="fa-solid fa-comments text-4xl md:text-5xl opacity-55"></i>
                <i class="fa-solid fa-pen-nib text-5xl md:text-6xl ml-6 opacity-55"></i>
                <i class="fa-solid fa-book text-4xl md:text-5xl ml-10 opacity-55"></i>
            </div>
            <div class="absolute bottom-6 right-4 sm:bottom-10 sm:right-8 md:bottom-12 md:right-12 flex flex-col gap-4 items-end text-slate-300 rotate-[-10deg]">
                <i class="fa-solid fa-users text-4xl md:text-5xl opacity-55"></i>
                <i class="fa-solid fa-star text-5xl md:text-6xl mr-6 opacity-55"></i>
                <i class="fa-solid fa-compass text-4xl md:text-5xl mr-10 opacity-55"></i>
            </div>
            <i class="absolute top-[18%] left-[18%] fa-solid fa-laptop-code text-slate-300 text-3xl md:text-5xl opacity-[0.40] rotate-[-14deg]"></i>
            <i class="absolute top-[28%] right-[22%] fa-solid fa-book text-slate-300 text-3xl md:text-5xl opacity-[0.40] rotate-[12deg]"></i>
            <i class="absolute top-[42%] left-[9%] fa-solid fa-comments text-slate-300 text-4xl md:text-6xl opacity-[0.40] rotate-[-6deg]"></i>
            <i class="absolute top-[46%] right-[10%] fa-solid fa-globe text-slate-300 text-4xl md:text-6xl opacity-[0.40] rotate-[8deg]"></i>
            <i class="absolute top-[56%] left-[26%] fa-solid fa-lightbulb text-slate-300 text-3xl md:text-5xl opacity-[0.40] rotate-[-18deg]"></i>
            <i class="absolute top-[60%] right-[28%] fa-solid fa-paper-plane text-slate-300 text-3xl md:text-5xl opacity-[0.40] rotate-[16deg]"></i>
            <i class="absolute bottom-[28%] left-[14%] fa-solid fa-pen-nib text-slate-300 text-3xl md:text-5xl opacity-[0.40] rotate-[10deg]"></i>
            <i class="absolute bottom-[24%] right-[16%] fa-solid fa-graduation-cap text-slate-300 text-4xl md:text-6xl opacity-[0.40] rotate-[-12deg]"></i>
            <i class="absolute bottom-[40%] left-[42%] fa-solid fa-star text-slate-300 text-2xl md:text-4xl opacity-[0.40] rotate-[20deg]"></i>
            <i class="absolute top-[34%] left-[46%] fa-solid fa-earth-americas text-slate-300 text-3xl md:text-5xl opacity-[0.36] rotate-[-10deg]"></i>
        </div>
        
        <div class="mx-auto max-w-7xl px-4 sm:px-6 relative z-10">
            <div class="text-center mb-8 md:mb-10" data-aos="fade-up">
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-black uppercase tracking-tight text-[#2e3192]">Sứ Mệnh <span class="text-red-600">Toàn Cầu</span></h2>
                <p class="mt-3 sm:mt-4 text-slate-600 font-medium max-w-2xl mx-auto text-base sm:text-lg">Kiến tạo thế hệ công dân làm chủ tương lai thông qua ngôn ngữ và kỹ năng toàn diện.</p>
            </div>
            
            <div class="relative w-full max-w-[1260px] mx-auto min-h-[400px] sm:min-h-[620px] md:min-h-[940px] flex items-center justify-center orbit-wrapper mt-6 sm:mt-10 md:mt-2" style="transform: scale(0.95);">
                <div class="absolute w-[320px] h-[320px] sm:w-[430px] sm:h-[430px] md:w-[770px] md:h-[770px] rounded-full border-2 border-dashed border-blue-400/50 orbit-spin z-10">
                    
                    <div class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 z-50">
                        <div class="orbit-reverse-spin group cursor-pointer">
                            <div class="flex items-center gap-2 sm:gap-4 bg-white/95 backdrop-blur-md p-2 sm:p-3 pr-4 sm:pr-6 rounded-full shadow-lg border border-slate-200 transition-all duration-300 hover:shadow-2xl hover:border-amber-300">
                                <div class="w-10 h-10 sm:w-14 sm:h-14 md:w-16 md:h-16 rounded-full flex items-center justify-center text-xl sm:text-3xl shrink-0 bg-amber-50 text-amber-500 shadow-inner">💡</div>
                                <div class="overflow-hidden transition-[max-width,max-height] duration-500 ease-in-out max-w-[100px] sm:max-w-[140px] max-h-[28px] sm:max-h-[36px] group-hover:max-w-[280px] sm:group-hover:max-w-[340px] group-hover:max-h-[140px] sm:group-hover:max-h-[170px]">
                                    <h4 class="font-black text-[#2e3192] text-xs sm:text-sm md:text-lg whitespace-nowrap">Sáng Tạo</h4>
                                    <p class="text-[10px] sm:text-xs md:text-sm text-slate-600 font-medium mt-1 w-[200px] sm:w-[260px] opacity-0 group-hover:opacity-100 transition-opacity duration-500 delay-100 leading-relaxed">
                                        Xây dựng không gian học ngoại ngữ thân thiện, hiệu quả và đầy cảm hứng.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="absolute top-1/2 right-0 translate-x-1/2 -translate-y-1/2 z-50">
                        <div class="orbit-reverse-spin group cursor-pointer">
                            <div class="flex items-center gap-2 sm:gap-4 bg-white/95 backdrop-blur-md p-2 sm:p-3 pr-4 sm:pr-6 rounded-full shadow-lg border border-slate-200 transition-all duration-300 hover:shadow-2xl hover:border-blue-300">
                                <div class="w-10 h-10 sm:w-14 sm:h-14 md:w-16 md:h-16 rounded-full flex items-center justify-center text-xl sm:text-3xl shrink-0 bg-blue-50 text-blue-500 shadow-inner">🗣️</div>
                                <div class="overflow-hidden transition-[max-width,max-height] duration-500 ease-in-out max-w-[100px] sm:max-w-[140px] max-h-[28px] sm:max-h-[36px] group-hover:max-w-[280px] sm:group-hover:max-w-[340px] group-hover:max-h-[140px] sm:group-hover:max-h-[170px]">
                                    <h4 class="font-black text-[#2e3192] text-xs sm:text-sm md:text-lg whitespace-nowrap">Tự Tin</h4>
                                    <p class="text-[10px] sm:text-xs md:text-sm text-slate-600 font-medium mt-1 w-[200px] sm:w-[260px] opacity-0 group-hover:opacity-100 transition-opacity duration-500 delay-100 leading-relaxed">
                                        Làm chủ tiếng Anh từ những câu đơn giản đến hội thoại thực tế đời sống.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="absolute bottom-0 left-1/2 -translate-x-1/2 translate-y-1/2 z-50">
                        <div class="orbit-reverse-spin group cursor-pointer">
                            <div class="flex items-center gap-2 sm:gap-4 bg-white/95 backdrop-blur-md p-2 sm:p-3 pr-4 sm:pr-6 rounded-full shadow-lg border border-slate-200 transition-all duration-300 hover:shadow-2xl hover:border-emerald-300">
                                <div class="w-10 h-10 sm:w-14 sm:h-14 md:w-16 md:h-16 rounded-full flex items-center justify-center text-xl sm:text-3xl shrink-0 bg-emerald-50 text-emerald-500 shadow-inner">🎯</div>
                                <div class="overflow-hidden transition-[max-width,max-height] duration-500 ease-in-out max-w-[100px] sm:max-w-[140px] max-h-[28px] sm:max-h-[36px] group-hover:max-w-[280px] sm:group-hover:max-w-[340px] group-hover:max-h-[140px] sm:group-hover:max-h-[170px]">
                                    <h4 class="font-black text-[#2e3192] text-xs sm:text-sm md:text-lg whitespace-nowrap">Toàn Diện</h4>
                                    <p class="text-[10px] sm:text-xs md:text-sm text-slate-600 font-medium mt-1 w-[200px] sm:w-[260px] opacity-0 group-hover:opacity-100 transition-opacity duration-500 delay-100 leading-relaxed">
                                        Đào tạo bài bản 4 kỹ năng Nghe – Nói – Đọc – Viết cho mọi lứa tuổi.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="absolute top-1/2 left-0 -translate-x-1/2 -translate-y-1/2 z-50">
                        <div class="orbit-reverse-spin group cursor-pointer">
                            <div class="flex items-center gap-2 sm:gap-4 bg-white/95 backdrop-blur-md p-2 sm:p-3 pr-4 sm:pr-6 rounded-full shadow-lg border border-slate-200 transition-all duration-300 hover:shadow-2xl hover:border-purple-300">
                                <div class="w-10 h-10 sm:w-14 sm:h-14 md:w-16 md:h-16 rounded-full flex items-center justify-center text-xl sm:text-3xl shrink-0 bg-purple-50 text-purple-500 shadow-inner">🤝</div>
                                <div class="overflow-hidden transition-[max-width,max-height] duration-500 ease-in-out max-w-[100px] sm:max-w-[140px] max-h-[28px] sm:max-h-[36px] group-hover:max-w-[280px] sm:group-hover:max-w-[340px] group-hover:max-h-[140px] sm:group-hover:max-h-[170px]">
                                    <h4 class="font-black text-[#2e3192] text-xs sm:text-sm md:text-lg whitespace-nowrap">Cam Kết</h4>
                                    <p class="text-[10px] sm:text-xs md:text-sm text-slate-600 font-medium mt-1 w-[200px] sm:w-[260px] opacity-0 group-hover:opacity-100 transition-opacity duration-500 delay-100 leading-relaxed">
                                        Theo sát lộ trình, khơi dậy niềm yêu thích với phương châm "Dám nói".
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative z-20 w-44 h-44 sm:w-64 sm:h-64 md:w-[450px] md:h-[450px] rounded-full border-[6px] sm:border-[10px] md:border-[14px] border-white shadow-[0_20px_60px_rgba(30,58,138,0.2)] overflow-hidden bg-white flex items-center justify-center group" data-aos="zoom-in">
                    <img src="assets/images/mission2.jpg" alt="Trung tâm" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                    <div class="absolute inset-0 bg-blue-900/10 group-hover:bg-transparent transition-colors"></div>
                </div>

            </div>
        </div>
    </section>                            
								
    <section id="ngoai-khoa" class="py-12 sm:py-16 bg-transparent relative overflow-hidden">
        <div class="mx-auto max-w-[1400px] px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="mb-10 sm:mb-12 text-center md:text-left" data-aos="fade-up">
                <h2 class="text-2xl md:text-3xl lg:text-4xl font-extrabold tracking-tight text-slate-800 mb-2">
                    Có những mùa hè trôi qua, có những mùa hè con mang theo mãi mãi...
                </h2>
                <p class="text-slate-600 font-medium text-sm md:text-base">Khám phá các hoạt động ngoại khóa nổi bật trong tháng này.</p>
            </div>

            <?php $activities = $homeActivities ?? []; ?>
            <div class="overflow-hidden pb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="grid gap-5 sm:gap-6 lg:gap-8 sm:grid-cols-2 lg:grid-cols-4 mobile-swipe-track">
                    <?php if (empty($activities)): ?>
                        <div class="sm:col-span-2 lg:col-span-4 rounded-[2rem] border border-dashed border-slate-300 bg-white/70 px-6 py-14 text-center text-slate-500 font-medium">
                            Hiện chưa có hoạt động ngoại khoá nào trong hệ thống.
                        </div>
                    <?php else: ?>
                        <?php foreach ($activities as $act): ?>
                            <?php
                            $activityTitle = (string) ($act['activity_name'] ?? '');
                            $activityDesc = trim((string) ($act['description'] ?? ''));
                            $activityImage = trim((string) ($act['image_thumbnail'] ?? ''));
                            $activityLink = page_url('activities-home-detail', ['id' => (int) ($act['id'] ?? 0)]);
                            ?>
                            <a href="<?= e($activityLink); ?>" class="mobile-swipe-card group bg-white/80 backdrop-blur-md hover:bg-white rounded-2xl sm:rounded-3xl p-3 border border-white shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-2 cursor-pointer flex flex-col">
                                <div class="relative w-full aspect-[4/3] rounded-xl sm:rounded-2xl overflow-hidden mb-3 sm:mb-4 bg-slate-100">
                                    <?php if ($activityImage !== ''): ?>
                                        <img src="<?= e($activityImage); ?>" alt="<?= e($activityTitle); ?>" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
                                    <?php else: ?>
                                        <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-sky-100 via-white to-lime-100 text-slate-400">
                                            <i class="fa-solid fa-rocket text-2xl sm:text-3xl"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="px-2 pb-2 sm:pb-3 text-center flex-1 flex flex-col justify-start">
                                    <h3 class="text-[#0d3b66] font-bold text-base sm:text-lg leading-tight mb-1.5 sm:mb-2 group-hover:text-blue-600 transition-colors"><?= e($activityTitle); ?></h3>
                                    <p class="text-slate-500 text-xs sm:text-sm font-medium leading-snug px-1 line-clamp-2">
                                        <?= e($activityDesc !== '' ? $activityDesc : 'Khám phá hoạt động ngoại khoá hấp dẫn dành cho học viên.'); ?>
                                    </p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mt-6 sm:mt-8 flex justify-center" data-aos="fade-up">
                <a href="<?= e(page_url('activities-home')); ?>" class="inline-flex items-center justify-center gap-2 rounded-full bg-[#2e3192] px-6 py-3 sm:py-3.5 text-xs sm:text-sm font-bold text-white shadow-md transition-transform hover:-translate-y-0.5 hover:bg-blue-600">
                    Xem thêm <i class="fa-solid fa-arrow-right text-[10px] sm:text-xs"></i>
                </a>
            </div>
        </div>
    </section>
	
    <section id="giao-vien" class="py-12 sm:py-14 md:py-20 relative overflow-hidden bg-transparent z-10">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 relative z-10">
        
        <div class="mb-10 sm:mb-16 flex flex-col lg:flex-row gap-6 sm:gap-8 items-start lg:items-center justify-between" data-aos="fade-up">
            
            <div class="lg:w-1/2 max-w-2xl">
                <h2 class="text-2xl sm:text-3xl md:text-5xl font-black text-blue-800 leading-[1.18] md:leading-[1.12] tracking-tight max-w-xl">
                    Hơn 3.100 Giáo viên và trợ giảng <br>
                    <span class="text-blue-600">Chuẩn quốc tế</span>
                </h2>
            </div>
            
            <div class="lg:w-1/2 flex items-start sm:items-center gap-4 sm:gap-6">
                <p class="text-slate-600 font-medium text-xs sm:text-sm leading-relaxed border-l-2 border-slate-300 pl-3 sm:pl-4 md:pl-6 text-left">
                    100% giáo viên nước ngoài được đảm bảo bởi International House - tổ chức uy tín hàng đầu thế giới về chuẩn đào tạo giáo viên nghiêm ngặt (như CELTA, DELTA)
                </p>
                <div class="w-12 h-12 sm:w-14 sm:h-14 md:w-16 md:h-16 shrink-0 bg-[#2e3192] rounded-full flex items-center justify-center text-white font-bold text-lg sm:text-xl md:text-2xl shadow-md">
                    ih
                </div>
            </div>
        </div>

        <div class="w-full" data-aos="fade-up" data-aos-delay="100">
            <div class="swiper teacherSwiper pb-12 sm:pb-16">
                <div class="swiper-wrapper">
                    <?php
                    $teachers = array_slice($homeTeachers ?? [], 0, 5);
                    if (empty($teachers)):
                    ?>
                    <div class="swiper-slide h-auto">
                        <div class="rounded-2xl sm:rounded-3xl border border-dashed border-slate-200 bg-white/80 p-6 sm:p-8 text-center text-slate-500 font-medium text-sm sm:text-base">
                            Hiện chưa có giáo viên nào trong hệ thống.
                        </div>
                    </div>
                    <?php else: ?>
                    <?php foreach ($teachers as $teacher): ?>
                    <?php
                    $teacherName = (string) ($teacher['full_name'] ?? '');
                    $teacherRole = 'Giáo viên';
                    $teacherAvatar = trim((string) ($teacher['avatar'] ?? ''));
                    if ($teacherAvatar !== '' && function_exists('normalize_public_file_url')) {
                        $teacherAvatar = normalize_public_file_url($teacherAvatar);
                    }
                    if ($teacherAvatar === '') {
                        $teacherAvatar = 'https://ui-avatars.com/api/?name=' . urlencode($teacherName !== '' ? $teacherName : 'Teacher') . '&background=2e3192&color=fff&size=600&bold=true';
                    }
                    ?>
                    <div class="swiper-slide h-auto">
                        <article class="flex flex-col gap-3 sm:gap-4 group cursor-pointer">
                            <div class="relative w-full aspect-[4/5] rounded-2xl sm:rounded-3xl overflow-hidden bg-slate-200 shadow-[0_10px_30px_rgba(0,0,0,0.05)]">
                                <img src="<?= e($teacherAvatar) ?>" alt="<?= e($teacherName) ?>" class="w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-105">
                                <div class="absolute inset-0 bg-blue-900/0 group-hover:bg-blue-900/10 transition-colors duration-300"></div>
                            </div>
                            <div class="px-1 text-left">
                                <h4 class="text-base sm:text-lg md:text-xl font-extrabold text-slate-800 group-hover:text-blue-600 transition-colors"><?= e($teacherName) ?></h4>
                                <p class="text-xs sm:text-sm font-medium text-slate-500 mt-0.5 sm:mt-1"><?= e($teacherRole) ?></p>
                            </div>
                        </article>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <div class="swiper-pagination-teacher mt-6 sm:mt-8 flex justify-center"></div>
            </div>
        </div>

        <div class="mt-6 sm:mt-8 flex justify-center">
            <a href="<?= e(page_url('dashboard-teacher')); ?>" class="inline-flex items-center justify-center gap-2 rounded-full bg-[#2e3192] px-5 sm:px-6 py-3 sm:py-3.5 text-xs sm:text-sm font-bold text-white shadow-md transition-transform hover:-translate-y-0.5 hover:bg-blue-600">
                Xem thêm <i class="fa-solid fa-arrow-right text-[10px] sm:text-xs"></i>
            </a>
        </div>

    </div>
</section>
 <section id="danh-gia" class="relative py-12 sm:py-14 md:py-20 overflow-hidden bg-transparent">
        <div class="absolute inset-0 pointer-events-none hidden sm:block">
            <div class="absolute -top-24 -right-24 h-80 w-80 rounded-full bg-cyan-200/30 blur-3xl"></div>
            <div class="absolute -bottom-24 -left-24 h-80 w-80 rounded-full bg-rose-200/30 blur-3xl"></div>
        </div>

        <div class="mx-auto w-full max-w-[1400px] px-4 sm:px-6 relative z-10">
            <div class="mb-8 sm:mb-10" data-aos="fade-up">
                <div class="max-w-2xl">
                    <div class="inline-flex items-center gap-1.5 sm:gap-2 rounded-full border border-emerald-100 bg-white/80 px-3 sm:px-4 py-1.5 sm:py-2 text-[9px] sm:text-[10px] font-black uppercase tracking-[0.35em] text-emerald-600 shadow-sm">
                        <i class="fa-regular fa-comment-dots"></i> Đánh giá từ người dùng
                    </div>
                    <h2 class="mt-3 sm:mt-4 text-2xl sm:text-3xl md:text-5xl font-black tracking-tight text-slate-900">Học viên nói gì về <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-cyan-500">trung tâm</span></h2>
                    <p class="mt-3 sm:mt-4 text-slate-600 font-medium text-sm sm:text-base md:text-lg leading-relaxed">Những phản hồi thật từ học viên và phụ huynh là thước đo rõ nhất cho chất lượng đào tạo và trải nghiệm học tập tại trung tâm.</p>
                </div>
            </div>

            <div class="swiper feedbackSwiper" data-aos="fade-up" data-aos-delay="120">
                <div class="swiper-wrapper pb-10 sm:pb-14">
                    <?php if (empty($homeFeedbacks)): ?>
                        <div class="swiper-slide h-auto">
                            <div class="rounded-2xl sm:rounded-[2rem] border border-dashed border-slate-300 bg-white/80 p-6 sm:p-8 text-center text-slate-500 font-medium text-sm sm:text-base">
                                Hiện chưa có đánh giá nào được duyệt để hiển thị.
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($homeFeedbacks as $feedback): ?>
                            <?php
                            $feedbackName = (string) ($feedback['full_name'] ?? 'Học viên');
                            $feedbackClass = (string) ($feedback['course_name'] ?? '');
                            $feedbackTeacher = (string) ($feedback['teacher_name'] ?? '');
                            $feedbackContent = trim((string) ($feedback['comment'] ?? ''));
                            $feedbackRating = max(0, min(5, (int) ($feedback['rating'] ?? 0)));
                            $avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($feedbackName !== '' ? $feedbackName : 'User') . '&background=0f766e&color=fff&size=256&bold=true';
                            ?>
                            <div class="swiper-slide h-auto">
                                <article class="flex h-full min-h-[120px] flex-col rounded-[1.5rem] sm:rounded-[2rem] border border-white bg-white/90 p-4 sm:p-5 md:p-6 shadow-[0_15px_40px_rgba(15,23,42,0.08)] transition-all hover:-translate-y-1 md:min-h-[180px]">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                                            <img src="<?= e($avatarUrl); ?>" alt="<?= e($feedbackName); ?>" class="h-10 w-10 sm:h-12 sm:w-12 rounded-xl object-cover ring-2 ring-emerald-50 shrink-0">
                                            <div class="min-w-0">
                                                <h3 class="truncate text-sm sm:text-base font-black text-slate-900"><?= e($feedbackName); ?></h3>
                                                <p class="text-[9px] sm:text-[10px] md:text-[11px] font-semibold uppercase tracking-[0.2em] text-emerald-600">Học viên / Phụ huynh</p>
                                            </div>
                                        </div>
                                        <div class="flex flex-col items-end gap-1 shrink-0">
                                            <div class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-[8px] sm:text-[9px] md:text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500">
                                                <i class="fa-regular fa-clock text-[8px] sm:text-[9px]"></i>
                                                <?= e($homeFormatFeedbackDate((string) ($feedback['created_at'] ?? ''))); ?>
                                            </div>
                                            <div class="flex items-center gap-0.5 sm:gap-1 text-amber-400">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="<?= $i <= $feedbackRating ? 'fa-solid' : 'fa-regular'; ?> fa-star text-[9px] sm:text-[10px] md:text-sm"></i>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <p class="mt-3 sm:mt-4 flex-1 text-xs sm:text-sm md:text-base leading-relaxed text-slate-600 line-clamp-3">
                                        “<?= e($feedbackContent !== '' ? $feedbackContent : 'Trải nghiệm học tập tại trung tâm rất tốt.'); ?>”
                                    </p>

                                    <div class="mt-3 sm:mt-4 flex flex-wrap items-center gap-1.5 sm:gap-2 text-[9px] sm:text-[10px] md:text-[11px] font-bold uppercase tracking-widest text-slate-500">
                                        <?php if ($feedbackClass !== ''): ?>
                                            <span class="rounded-full bg-emerald-50 px-2 sm:px-3 py-0.5 sm:py-1 text-emerald-700"><?= e($feedbackClass); ?></span>
                                        <?php endif; ?>
                                        <?php if ($feedbackTeacher !== ''): ?>
                                            <span class="rounded-full bg-cyan-50 px-2 sm:px-3 py-0.5 sm:py-1 text-cyan-700">GV: <?= e($feedbackTeacher); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </article>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="swiper-pagination-feedback flex justify-center"></div>
            </div>
        </div>
    </section>

    <section id="feed-back-student" class="relative py-12 sm:py-14 md:py-20 overflow-hidden bg-transparent">
        <div class="absolute inset-0 pointer-events-none hidden sm:block">
            <div class="absolute -top-24 -right-24 h-80 w-80 rounded-full bg-lime-200/30 blur-3xl"></div>
            <div class="absolute -bottom-24 -left-24 h-80 w-80 rounded-full bg-amber-200/30 blur-3xl"></div>
        </div>

        <div class="mx-auto w-full max-w-[1400px] px-4 sm:px-6 relative z-10">
            <div class="mb-8 sm:mb-10" data-aos="fade-up">
                <div class="max-w-2xl">
                    <div class="inline-flex items-center gap-1.5 sm:gap-2 rounded-full border border-lime-100 bg-white/80 px-3 sm:px-4 py-1.5 sm:py-2 text-[9px] sm:text-[10px] font-black uppercase tracking-[0.35em] text-lime-600 shadow-sm">
                        <i class="fa-solid fa-video"></i> Kết quả đạt được
                    </div>
                    <h2 class="mt-3 sm:mt-4 text-2xl sm:text-3xl md:text-5xl font-black tracking-tight text-slate-900">Kết quả nhận được <span class="text-transparent bg-clip-text bg-gradient-to-r from-lime-600 to-emerald-500">học viên</span></h2>
                    <p class="mt-3 sm:mt-4 text-slate-600 font-medium text-sm sm:text-base md:text-lg leading-relaxed">Những video kết quả nhận được thực tế từ học viên về những kết quả tuyệt vời họ đã đạt được sau khi học tập tại trung tâm.</p>
                </div>
            </div>

            <div class="swiper studentPortfolioSwiper" data-aos="fade-up" data-aos-delay="120">
                <div class="swiper-wrapper pb-10 sm:pb-14">
                    <?php 
                    $studentPortfolios = $studentPortfolios ?? [];
                    if (empty($studentPortfolios)): 
                    ?>
                        <div class="swiper-slide h-auto">
                            <div class="rounded-2xl sm:rounded-[2rem] border border-dashed border-slate-300 bg-white/80 p-6 sm:p-8 text-center text-slate-500 font-medium text-sm sm:text-base">
                                Hiện chưa có phản hồi video nào được duyệt để hiển thị.
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($studentPortfolios as $portfolio): ?>
                            <?php
                            $portfolioName = (string) ($portfolio['full_name'] ?? $portfolio['student_name'] ?? 'Học viên');
                            $portfolioAvatar = (string) ($portfolio['avatar_url'] ?? $portfolio['avatar'] ?? '');
                            $portfolioMedia = (string) ($portfolio['media_url'] ?? '');
                            $portfolioDescription = trim((string) ($portfolio['description'] ?? ''));
                            $portfolioResult = trim((string) ($portfolio['result'] ?? 'Kết quả đạt được'));
                            
                            // Nếu không có avatar, dùng avatar mặc định
                            if (empty($portfolioAvatar)) {
                                $portfolioAvatar = 'https://ui-avatars.com/api/?name=' . urlencode($portfolioName !== '' ? $portfolioName : 'Student') . '&background=16a34a&color=fff&size=256&bold=true';
                            }
                            
                            // Kiểm tra loại media (video hay image)
                            $isVideo = preg_match('/(mp4|webm|ogg|avi|mov|mkv)$/i', $portfolioMedia);
                            ?>
                            <div class="swiper-slide h-auto">
                                <article class="flex h-full flex-col rounded-[1.5rem] sm:rounded-[2rem] border border-white bg-white/90 overflow-hidden shadow-[0_15px_40px_rgba(15,23,42,0.08)] transition-all hover:-translate-y-1">
                                    <!-- Media Container (reduced height ~70%) -->
                                    <div class="relative w-full bg-slate-100 overflow-hidden portfolio-media" style="aspect-ratio: 2.54;" data-media="<?= e($portfolioMedia); ?>" data-is-video="<?= $isVideo ? '1' : '0' ?>">
                                        <?php if ($isVideo): ?>
                                            <video class="w-full h-full object-cover" muted playsinline preload="metadata">
                                                <source src="<?= e($portfolioMedia); ?>" type="video/mp4">
                                            </video>
                                        <?php else: ?>
                                            <img src="<?= e($portfolioMedia); ?>" alt="<?= e($portfolioName); ?>" class="w-full h-full object-cover">
                                        <?php endif; ?>
                                    </div>

                                    <!-- Info Container -->
                                    <div class="flex flex-col p-4 sm:p-5 md:p-6">
                                        <div class="flex items-center gap-2.5 sm:gap-3 mb-3 sm:mb-4">
                                            <img src="<?= e($portfolioAvatar); ?>" alt="<?= e($portfolioName); ?>" class="h-10 w-10 sm:h-12 sm:w-12 rounded-lg object-cover ring-2 ring-lime-50 shrink-0">
                                            <div class="min-w-0">
                                                <h3 class="truncate text-sm sm:text-base font-black text-slate-900"><?= e($portfolioName); ?></h3>
                                                <p class="text-[9px] sm:text-[10px] md:text-[11px] font-semibold uppercase tracking-[0.2em] text-lime-600">Học viên</p>
                                            </div>
                                        </div>

                                        <div class="inline-flex items-center gap-1.5 mb-3 sm:mb-4">
                                            <span class="rounded-full bg-lime-50 px-2 sm:px-3 py-0.5 sm:py-1 text-[9px] sm:text-[10px] md:text-[11px] font-bold uppercase tracking-widest text-lime-700">
                                                <i class="fa-solid fa-trophy text-amber-500 mr-1"></i><?= e($portfolioResult); ?>
                                            </span>
                                        </div>

                                        <?php if ($portfolioDescription !== ''): ?>
                                            <p class="text-xs sm:text-sm md:text-base leading-relaxed text-slate-600 line-clamp-2">
                                                "<?= e($portfolioDescription); ?>"
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </article>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="swiper-pagination-portfolio flex justify-center"></div>
            </div>
        </div>
    </section>

    <!-- Portfolio video modal -->
    <div id="portfolioVideoModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60">
        <div class="relative w-full max-w-3xl mx-4">
            <button id="portfolioModalClose" class="absolute right-0 top-0 m-2 text-white text-3xl leading-none">&times;</button>
            <video id="portfolioModalVideo" class="w-full h-auto rounded-lg bg-black" controls playsinline></video>
        </div>
    </div>

    <section id="dang-ky-tu-van" class="relative py-20 md:py-32 overflow-hidden">
        <!-- Background image hero banner -->
        <div class="absolute inset-0">
            <img src="/assets/images/consult.jpg" alt="Sinh viên học tập" class="h-full w-full object-cover brightness-110 contrast-105 saturate-105">
            <!-- Slight horizontal darkening to keep text legible -->
            <div class="absolute inset-0 bg-gradient-to-r from-slate-900/28 via-slate-900/8 to-transparent"></div>
            <!-- Top fade: blend the top edge into the page background for smooth transition -->
            <div class="absolute inset-x-0 top-0 h-28 md:h-36 bg-gradient-to-b from-white/95 to-transparent pointer-events-none"></div>
            <!-- Bottom fade: blend the bottom edge into the page background for smooth transition -->
            <div class="absolute inset-x-0 bottom-0 h-28 md:h-36 bg-gradient-to-t from-white/95 to-transparent pointer-events-none"></div>
            <!-- Subtle light reflection (white psychology - cleanliness, trust) -->
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(255,255,255,0.12),_transparent_45%)]"></div>
        </div>

        <!-- Content overlay -->
        <div class="relative z-10 mx-auto max-w-[1450px] px-4 sm:px-6">
            <div class="grid gap-8 lg:gap-12 lg:grid-cols-2 items-center">
                <!-- Left side: Text content -->
                <div class="max-w-2xl" data-aos="fade-right" data-aos-duration="700">
                    <span class="inline-flex items-center gap-2 rounded-full border border-rose-300/40 bg-gradient-to-r from-rose-600 to-rose-500 px-4 py-2 text-[10px] font-black uppercase tracking-[0.2em] text-white shadow-lg shadow-rose-500/25 backdrop-blur-sm transition-transform hover:-translate-y-0.5">
                        <span class="h-2 w-2 rounded-full bg-white animate-pulse"></span>
                        Tư vấn nhanh 1:1
                    </span>

                    <h2 class="mt-8 text-4xl md:text-5xl lg:text-6xl font-black leading-tight tracking-tight text-white">
                        Bắt đầu hành trình <br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-rose-200 to-orange-200">chinh phục Anh ngữ</span>
                    </h2>
                    
                    <p class="mt-6 max-w-xl text-base md:text-lg leading-relaxed text-white/85">
                        Hãy để lại thông tin, đội ngũ học thuật của chúng tôi sẽ thiết kế riêng một lộ trình tối ưu nhất dựa trên mục tiêu và năng lực của bạn.
                    </p>

                    <div class="mt-10 grid gap-4 sm:grid-cols-3 max-w-lg">
                        <div class="rounded-[1.5rem] border border-white/18 bg-white/10 p-5 backdrop-blur-sm shadow-lg">
                            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-white/15 text-white">
                                <i class="fa-regular fa-clock text-sm"></i>
                            </div>
                            <p class="text-2xl font-black text-white">15'</p>
                            <p class="mt-1 text-[9px] font-bold uppercase tracking-widest text-white/70">Liên hệ ngay</p>
                        </div>
                        <div class="rounded-[1.5rem] border border-white/18 bg-white/10 p-5 backdrop-blur-sm shadow-lg">
                            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-white/15 text-white">
                                <i class="fa-solid fa-user-group text-sm"></i>
                            </div>
                            <p class="text-2xl font-black text-white">1:1</p>
                            <p class="mt-1 text-[9px] font-bold uppercase tracking-widest text-white/70">Chuyên gia</p>
                        </div>
                        <div class="rounded-[1.5rem] border border-white/18 bg-white/10 p-5 backdrop-blur-sm shadow-lg">
                            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-white/15 text-white">
                                <i class="fa-solid fa-wand-magic-sparkles text-sm"></i>
                            </div>
                            <p class="text-2xl font-black text-white">100%</p>
                            <p class="mt-1 text-[9px] font-bold uppercase tracking-widest text-white/70">Cá nhân hóa</p>
                        </div>
                    </div>
                </div>

                <!-- Right side: Form panel overlay - Psychology: White (trust/cleanliness) + Rose (action) + Emerald (growth) -->
                <div class="relative overflow-hidden rounded-[2.75rem] border border-white/20 bg-transparent p-8 md:p-10 shadow-none backdrop-blur-none" data-aos="fade-left" data-aos-duration="700" data-aos-delay="100">
                    <!-- Subtle emerald tint (trust, growth psychology) -->
                    <div class="absolute right-[-10%] top-[-10%] h-56 w-56 rounded-full bg-gradient-to-br from-rose-50/90 to-pink-50/70 blur-3xl pointer-events-none"></div>
                    <!-- Emerald for confidence/growth psychology -->
                    <div class="absolute bottom-[-10%] left-[-10%] h-44 w-44 rounded-full bg-emerald-50/85 blur-3xl pointer-events-none"></div>

                    <div class="relative z-10 mb-10 border-b border-white/15 pb-8">
                        <!-- Heading: White with 3D shadow effect + Rose accent -->
                        <h3 class="text-3xl md:text-[2rem] font-black text-white tracking-tight mb-3" style="text-shadow: 
                            2px 2px 0 rgba(15, 23, 42, 0.15),
                            4px 4px 0 rgba(15, 23, 42, 0.12),
                            6px 6px 0 rgba(15, 23, 42, 0.08),
                            0 8px 16px rgba(15, 23, 42, 0.25),
                            0 0 1px rgba(255, 255, 255, 0.8);
                        ">
                            Đăng ký tư vấn
                            <span class="ml-2 text-transparent bg-clip-text bg-gradient-to-r from-rose-400 to-rose-600" style="text-shadow: 
                                2px 2px 0 rgba(244, 63, 94, 0.2),
                                4px 4px 0 rgba(244, 63, 94, 0.15),
                                0 6px 12px rgba(244, 63, 94, 0.2);
                            ">miễn phí</span>
                        </h3>
                        <!-- Subheading: Trust messaging (emerald psychology) -->
                        <p class="text-sm font-semibold text-white/85">
                            <i class="fa-solid fa-check-circle text-emerald-500 mr-2"></i>
                            Chuyên gia sẽ thiết kế lộ trình phù hợp cho bạn
                        </p>
                    </div>

                    <form class="relative z-10 grid gap-6 sm:grid-cols-2">
                        <!-- Name field: Rose psychology (action/engagement) -->
                        <div class="sm:col-span-2 group">
                            <label class="mb-3 flex items-center gap-2 text-[11px] font-black uppercase tracking-[0.16em] text-white group-focus-within:text-rose-300 transition-colors">
                                <i class="fa-solid fa-user text-rose-500"></i>
                                Họ và tên <span class="text-rose-500 text-base">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-5 top-1/2 -translate-y-1/2 text-rose-400 group-focus-within:text-rose-500 transition-colors"><i class="fa-regular fa-user"></i></span>
                                <input type="text" required placeholder="Nhập họ và tên của bạn" class="w-full rounded-2xl border border-slate-200 bg-white py-4 pl-14 pr-5 text-sm font-bold text-slate-900 shadow-sm outline-none transition-all placeholder:text-slate-400 placeholder:font-medium focus:border-rose-400 focus:ring-4 focus:ring-rose-500/15 focus:shadow-lg focus:shadow-rose-500/10">
                            </div>
                        </div>

                        <!-- Phone field: Rose for action/contact -->
                        <div class="group">
                            <label class="mb-3 flex items-center gap-2 text-[11px] font-black uppercase tracking-[0.16em] text-white group-focus-within:text-rose-300 transition-colors">
                                <i class="fa-solid fa-phone text-rose-500"></i>
                                Số điện thoại <span class="text-rose-500 text-base">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-5 top-1/2 -translate-y-1/2 text-rose-400 group-focus-within:text-rose-500 transition-colors"><i class="fa-solid fa-phone"></i></span>
                                <input type="tel" required placeholder="09xx xxx xxx" class="w-full rounded-2xl border border-slate-200 bg-white py-4 pl-14 pr-5 text-sm font-bold text-slate-900 shadow-sm outline-none transition-all placeholder:text-slate-400 placeholder:font-medium focus:border-rose-400 focus:ring-4 focus:ring-rose-500/15 focus:shadow-lg focus:shadow-rose-500/10">
                            </div>
                        </div>

                        <!-- Date field: Emerald for info/optional (growth psychology) -->
                        <div class="group">
                            <label class="mb-3 flex items-center gap-2 text-[11px] font-black uppercase tracking-[0.16em] text-white group-focus-within:text-emerald-300 transition-colors">
                                <i class="fa-solid fa-calendar text-emerald-500"></i>
                                Ngày sinh
                            </label>
                            <div class="relative">
                                <span class="absolute left-5 top-1/2 -translate-y-1/2 text-emerald-400 group-focus-within:text-emerald-500 transition-colors"><i class="fa-regular fa-calendar"></i></span>
                                <input type="date" class="w-full rounded-2xl border border-slate-200 bg-white py-4 pl-14 pr-5 text-sm font-bold text-slate-900 shadow-sm outline-none transition-all focus:border-emerald-400 focus:ring-4 focus:ring-emerald-500/15 focus:shadow-lg focus:shadow-emerald-500/10">
                            </div>
                        </div>

                        <!-- Notes field: Emerald for feedback (confidence in sharing) -->
                        <div class="sm:col-span-2 group">
                            <label class="mb-3 flex items-center gap-2 text-[11px] font-black uppercase tracking-[0.16em] text-white group-focus-within:text-emerald-300 transition-colors">
                                <i class="fa-solid fa-message text-emerald-500"></i>
                                Ghi chú mong muốn
                            </label>
                            <textarea rows="3" placeholder="Bạn muốn học khóa nào, hoặc khung giờ rảnh của bạn là gì?..." class="w-full rounded-2xl border border-slate-200 bg-white p-5 text-sm font-bold text-slate-900 shadow-sm outline-none transition-all placeholder:text-slate-400 placeholder:font-medium focus:border-emerald-400 focus:ring-4 focus:ring-emerald-500/15 focus:shadow-lg focus:shadow-emerald-500/10 resize-none"></textarea>
                        </div>

                        <!-- CTA Button: Rose (urgency/action psychology) + Emerald accent (trust) -->
                        <button type="submit" class="sm:col-span-2 mt-2 group relative inline-flex items-center justify-center gap-3 rounded-2xl bg-gradient-to-r from-rose-500 to-rose-600 px-8 py-4 text-sm font-black uppercase tracking-widest text-white shadow-lg shadow-rose-500/30 transition-all duration-300 hover:-translate-y-1.5 hover:from-rose-600 hover:to-rose-700 hover:shadow-rose-600/50 active:translate-y-0 active:shadow-rose-500/20">
                            <span class="flex items-center gap-2">
                                Gửi yêu cầu ngay
                                <i class="fa-solid fa-arrow-right transition-transform group-hover:translate-x-1"></i>
                            </span>
                            <!-- Subtle success indicator (emerald) -->
                            <span class="absolute -top-1 -right-1 hidden h-3 w-3 rounded-full bg-emerald-400 animate-pulse group-hover:block"></span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- <section id="lien-he" class="relative py-10 sm:py-14 md:py-20 overflow-hidden bg-transparent z-10">
        <div class="mx-auto w-full max-w-[1400px] flex flex-col lg:flex-row">

            <div class="w-full lg:w-1/2 flex flex-col justify-center px-4 sm:px-6 py-10 sm:py-16 lg:px-16 xl:px-32 z-10" data-aos="fade-right">
                
                <h2 class="text-2xl sm:text-[28px] md:text-[36px] font-bold text-[#185b9d] mb-4 sm:mb-5 tracking-tight">
                    Tư vấn và kiểm tra miễn phí
                </h2>

                <p class="max-w-xl text-slate-600 text-sm sm:text-base md:text-lg font-medium leading-relaxed mb-6 sm:mb-8">
                    Đăng ký ngay để được đội ngũ tư vấn hỗ trợ lộ trình học phù hợp, kiểm tra trình độ và nhận gợi ý khóa học tối ưu nhất.
                </p>

                <div class="inline-flex flex-col items-start gap-4 max-w-[420px]">
                    <a href="<?= e(page_url('register-consultation')); ?>" class="group relative inline-flex items-center justify-center rounded-full sm:rounded-[1.5rem] bg-[#2e3192] px-6 sm:px-8 py-4 sm:py-5 text-white font-black uppercase tracking-[0.18em] shadow-[0_18px_40px_rgba(46,49,146,0.28)] transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_24px_50px_rgba(46,49,146,0.38)] active:translate-y-0 focus:outline-none focus:ring-4 focus:ring-blue-400/30 w-full sm:w-auto">
                        <span class="absolute inset-x-4 top-0 h-2 rounded-full bg-white/25 blur-sm"></span>
                        <span class="relative flex items-center gap-2 sm:gap-3 text-sm sm:text-base md:text-lg">
                            <i class="fa-solid fa-pen-to-square transition-transform duration-300 group-hover:rotate-[-8deg]"></i>
                            Đăng ký ngay
                        </span>
                    </a>

                    <div class="flex items-center gap-2 sm:gap-3 text-xs sm:text-sm text-slate-500 font-medium bg-white/70 backdrop-blur-sm border border-slate-200 rounded-xl sm:rounded-2xl px-3 sm:px-4 py-2 sm:py-3 shadow-sm">
                        <div class="w-7 h-7 sm:w-9 sm:h-9 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <span>Chỉ mất vài giây để mở form đăng ký tư vấn.</span>
                    </div>
                </div>
            </div>

            <div class="w-full lg:w-1/2 relative min-h-[300px] sm:min-h-[400px] lg:min-h-[600px] mt-8 lg:mt-0" data-aos="fade-left">
                
                <img src="assets/images/tu_van_student.jpg" alt="Học sinh" class="absolute inset-0 w-full h-full object-cover object-top lg:object-center rounded-3xl lg:rounded-none">

                <div class="absolute inset-x-0 top-0 h-24 bg-gradient-to-b from-[#f4f7fb] to-transparent pointer-events-none lg:hidden"></div>

                <div class="absolute inset-y-0 left-0 w-24 md:w-32 bg-gradient-to-r from-[#f4f7fb] to-transparent pointer-events-none hidden lg:block"></div>

                <div class="absolute inset-y-0 right-0 w-24 md:w-32 bg-gradient-to-l from-[#f4f7fb] to-transparent pointer-events-none hidden lg:block"></div>

            </div>

        </div>
    </section> -->

</main>