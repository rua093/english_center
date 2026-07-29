<?php
$homeUser = auth_user();
$studentProgress = $homeWidgets['student_progress'] ?? null;
$teacherSchedules = $homeWidgets['teacher_schedules'] ?? [];
$homeCourses = $homeCourses ?? [];
$homePromotions = $homePromotions ?? [];
$homeLeadSuccess = get_flash('home_success');
$homeLeadError = get_flash('home_error');
$homeIntroVideoUrl = custom_ui_media_resolve_url('home_intro_video');
$homeIntroVideoType = 'video/mp4';
$homeIntroVideoPath = (string) (parse_url($homeIntroVideoUrl, PHP_URL_PATH) ?: $homeIntroVideoUrl);
$homeIntroVideoExtension = strtolower((string) pathinfo($homeIntroVideoPath, PATHINFO_EXTENSION));
if ($homeIntroVideoExtension === 'webm') {
    $homeIntroVideoType = 'video/webm';
} elseif ($homeIntroVideoExtension === 'mov') {
    $homeIntroVideoType = 'video/quicktime';
}

$homeFormatFeedbackDate = static function (?string $value): string {
    $value = trim((string) $value);
    if ($value === '') {
        return t('home.time_unknown');
    }

    $timestamp = strtotime($value);
    if ($timestamp === false) {
        return $value;
    }

    return date('d/m/Y H:i', $timestamp);
};

$renderBbcode = static function (string $text): string {
    $text = trim($text);
    if ($text === '') {
        return '';
    }

    if (function_exists('ui_render_bbcode')) {
        return ui_render_bbcode($text);
    }

    if (function_exists('bbcode_to_html')) {
        return bbcode_to_html($text);
    }

    return nl2br(e($text), false);
};

$homeFormatPromotionDate = static function (?string $value): string {
    $value = trim((string) $value);
    if ($value === '') {
        return '';
    }

    $timestamp = strtotime($value);
    if ($timestamp === false) {
        return $value;
    }

    return date('d/m/Y', $timestamp);
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
                <source src="<?= e($homeIntroVideoUrl); ?>" type="<?= e($homeIntroVideoType); ?>">
            </video>
            <div class="absolute inset-0 bg-gradient-to-r from-blue-950/80 md:from-blue-950/70 via-blue-950/40 md:via-blue-950/20 to-transparent w-full"></div>
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(255,214,10,0.14),transparent_28%),radial-gradient(circle_at_30%_22%,rgba(244,114,182,0.12),transparent_24%),radial-gradient(circle_at_78%_18%,rgba(34,211,238,0.12),transparent_22%)]"></div>
        </div>

        <div class="relative z-10 w-full max-w-[1450px] mx-auto px-4 sm:px-6 md:px-10 flex flex-col -mt-16 md:-mt-20">
            <div class="max-w-2xl" data-aos="fade-right">
                <div class="pointer-events-none absolute -left-2 top-10 hidden h-14 w-14 rounded-full bg-gradient-to-br from-yellow-300/35 to-orange-400/20 blur-xl md:block"></div>
                <div class="pointer-events-none absolute left-[26rem] top-24 hidden h-10 w-10 rounded-full bg-gradient-to-br from-cyan-300/35 to-sky-400/20 blur-lg lg:block"></div>

                <h1 class="relative text-3xl sm:text-4xl md:text-5xl lg:text-[3.8rem] font-black leading-[0.96] tracking-[-0.03em] drop-shadow-lg">
                    <span class="block text-white [text-shadow:0_4px_0_rgba(14,116,144,0.22),0_16px_36px_rgba(15,23,42,0.28)]">Inspire Learning,</span>
                    <span class="mt-1 block text-transparent bg-clip-text bg-gradient-to-r from-yellow-300 via-rose-300 to-cyan-300 [text-shadow:0_10px_28px_rgba(15,23,42,0.16)]">Empower Success</span>
                </h1>

                <p class="mt-4 max-w-[34rem] rounded-[1.6rem] border border-white/12 bg-white/10 px-5 py-4 text-sm sm:text-base md:text-lg text-sky-50 font-semibold leading-relaxed shadow-[0_18px_40px_rgba(15,23,42,0.16)] backdrop-blur-md">
                    <span class="bg-gradient-to-r from-pink-200 via-yellow-100 to-cyan-100 bg-clip-text text-transparent">Khơi nguồn học tập, kiến tạo thành công</span>
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
                        <?= e(t('home.center_badge')); ?> <span class="text-red-600"><?= e(t('home.center_name')); ?></span>
                    </div>

                    <h3 class="text-xl sm:text-2xl md:text-3xl font-black text-slate-900 leading-tight tracking-tight uppercase mb-2.5">
                        <?= e(t('home.test_title')); ?> <span class="hidden md:inline">-</span> <br class="md:hidden">
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-red-600 to-rose-600">
                            <?= e(t('home.test_highlight')); ?>
                        </span>
                    </h3>

                    <p class="text-slate-700 text-xs sm:text-sm leading-relaxed font-semibold max-w-2xl mx-auto md:mx-0">
                        <strong class="text-slate-900"><?= e(t('home.test_copy_prefix_1')); ?></strong> <?= e(t('home.test_copy_1')); ?> <strong class="text-slate-900"><?= e(t('home.test_copy_prefix_2')); ?></strong> <?= e(t('home.test_copy_2')); ?> <strong class="text-slate-900"><?= e(t('home.test_copy_prefix_3')); ?></strong><?= e(t('home.test_copy_3')); ?> <strong class="text-slate-900"><?= e(t('home.test_copy_prefix_4')); ?></strong> <?= e(t('home.test_copy_4')); ?>
                    </p>
                </div>

                <div class="relative z-10 shrink-0 w-full md:w-auto flex justify-center mt-2 md:mt-0">
                    <a href="#dang-ky-tu-van" class="group inline-flex items-center justify-center gap-2 rounded-full bg-gradient-to-r from-amber-400 to-yellow-400 hover:scale-105 transition-all duration-300 text-blue-950 font-black uppercase text-[11px] sm:text-xs px-8 py-3.5 shadow-lg border-[3px] border-white whitespace-nowrap w-full md:w-auto">
                        <i class="fa-solid fa-bolt text-red-600"></i>
                        <?= e(t('home.test_cta')); ?>
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
                    alt="<?= e(t('home.student_alt')); ?>"
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
                                <?= e(t('home.quality')); ?>
                            </h4>

                            <p class="text-[11px] font-bold text-slate-500">
                                <?= e(t('home.international_standard')); ?>
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
                                overflow-visible h-auto sm:min-h-[320px] flex items-start pr-48 sm:pr-64 md:pr-[24rem]">

                        <!-- Glow -->
                        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(255,255,255,0.25),transparent_40%)]"></div>

                        <!-- Decorative -->
                        <i class="fa-solid fa-rocket 
                                absolute -right-8 sm:-right-10 bottom-0
                                text-[8rem] sm:text-[11rem]
                                opacity-10 text-white
                                group-hover:scale-110 transition-transform duration-700"></i>

                        <!-- Text -->
                        <div class="relative z-10 max-w-[28rem] md:max-w-[30rem] flex-1">

                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full
                                        bg-white/15 border border-white/20 backdrop-blur-md mb-5">

                                <span class="w-2 h-2 rounded-full bg-cyan-200 animate-pulse"></span>

                                <span class="text-[10px] sm:text-xs tracking-[0.18em] font-bold text-blue-50">
                                    Phương pháp học tập
                                </span>
                            </div>

                            <h2 class="text-[2rem] sm:text-[2.5rem] md:text-[3.2rem]
                                    font-black leading-[1.02] tracking-[-0.03em]
                                    text-white drop-shadow-lg">

                                Học tự nhiên, <br>
                                dùng tiếng Anh tự tin
                            </h2>

                            <p class="mt-4 max-w-[25rem] text-sm sm:text-[15px] md:text-base leading-relaxed text-blue-50/95 font-medium">
                                Lộ trình học chú trọng phản xạ, thực hành và cá nhân hóa để học viên tiếp thu dễ hơn và dùng tiếng Anh tự tin hơn mỗi ngày.
                            </p>
                        </div>
                        <!-- FLOATING SMALL CARD - Inside Banner -->
                        <div class="absolute
                                    top-1/2 -translate-y-1/2
                                    right-2 sm:right-4 md:right-6
                                    w-[240px] sm:w-[300px] md:w-[350px]
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

                                        <div class="text-[11px] tracking-[0.14em]
                                                    text-slate-500 font-black mb-2">
                                            Nổi bật
                                        </div>

                                        <h3 class="text-base sm:text-lg md:text-[1.75rem] font-black leading-[1.12] tracking-[-0.02em] text-blue-950">
                                            Nghe - Nói phản xạ tự nhiên
                                        </h3>

                                        <p class="mt-2 text-[11px] sm:text-xs md:text-sm text-sky-600 font-bold leading-snug">
                                            Natural Listening &amp; Speaking Practice
                                        </p>

                                        <p class="mt-3 text-xs sm:text-sm text-slate-600 leading-relaxed font-medium max-w-[15rem] sm:max-w-[16rem]">
                                            Rèn phản xạ giao tiếp từ sớm để học viên nghe nhanh hơn và nói tự nhiên hơn qua từng buổi học.
                                        </p>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                <!-- FEATURE CARDS -->
                <div class="grid sm:grid-cols-2 gap-4 sm:gap-5 md:gap-6 pt-4 sm:pt-5 md:pt-6 pb-4 sm:pb-5 md:pb-6">

                    <!-- Card 1 -->
                    <div class="group relative rounded-[2rem] p-7 sm:p-8
                                bg-gradient-to-br from-rose-600 to-red-500
                                shadow-[0_15px_30px_rgba(225,29,72,0.2)]
                                overflow-hidden min-h-[220px] flex flex-col justify-start">

                        <div class="absolute top-5 left-5 bg-white text-red-600
                                    text-xs font-black px-4 py-1.5
                                    rounded-full shadow-md">

                            Thực hành
                        </div>

                        <i class="fa-solid fa-chalkboard-user
                                absolute -right-6 -top-6
                                text-[9rem] opacity-15"></i>

                        <div class="relative z-10 mt-12 max-w-[21rem]">

                            <h3 class="text-xl sm:text-[1.7rem] font-black leading-tight text-white mb-2">
                                Học qua tình huống thực tế
                            </h3>

                            <p class="text-xs sm:text-sm text-rose-100 font-bold mb-2 leading-snug">
                                Learn Through Real-Life Situations
                            </p>

                            <p class="text-sm text-rose-100 leading-relaxed max-w-[18rem]">
                                Áp dụng tiếng Anh trong đời sống hằng ngày để học viên hiểu nhanh, nhớ lâu và dùng đúng ngữ cảnh.
                            </p>
                        </div>
                    </div>

                    <!-- Card 2 -->
                    <div class="group relative rounded-[2rem] p-7 sm:p-8
                                bg-gradient-to-br from-blue-600 to-sky-500
                                shadow-[0_15px_30px_rgba(37,99,235,0.2)]
                                overflow-hidden min-h-[220px] flex flex-col justify-start">

                        <i class="fa-solid fa-book-open
                                absolute -right-6 -bottom-6
                                text-[9rem] opacity-15"></i>

                        <div class="relative z-10 mt-10 max-w-[20rem]">

                            <h3 class="text-xl sm:text-[1.7rem] font-black leading-tight text-white mb-2">
                                Học theo cụm từ
                            </h3>

                            <p class="text-xs sm:text-sm text-blue-100 font-bold mb-2 leading-snug">
                                Phrase-Based Learning
                            </p>

                            <p class="text-sm text-blue-100 leading-relaxed max-w-[18rem]">
                                Tập trung theo cụm từ để học viên nói trôi chảy, tự nhiên và chính xác hơn thay vì ghép từng từ rời rạc.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- VALUE BLOCKS -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 md:gap-5 pt-4 sm:pt-5 md:pt-6">

                    <!-- Block -->
                    <div class="group rounded-2xl p-4 sm:p-5 bg-white/90 backdrop-blur-xl border border-blue-100 shadow-sm text-center">
                        <div class="w-12 h-12 mx-auto mb-3 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                            <i class="fa-solid fa-headphones"></i>
                        </div>

                        <h4 class="font-extrabold text-blue-950 text-sm leading-snug">
                            Luyện nói phản xạ nhanh
                        </h4>

                        <span class="mt-1 block text-[10px] text-blue-600 font-bold leading-snug">
                            Shadowing Practice
                        </span>

                        <span class="mt-2 block text-[10px] text-slate-500 font-semibold leading-relaxed max-w-[10rem] mx-auto">
                            Nghe và lặp lại ngay để cải thiện phát âm, ngữ điệu và tốc độ phản xạ.
                        </span>
                    </div>

                    <!-- Block -->
                    <div class="group rounded-2xl p-4 sm:p-5 bg-white/90 backdrop-blur-xl border border-emerald-100 shadow-sm text-center">
                        <div class="w-12 h-12 mx-auto mb-3 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <i class="fa-solid fa-photo-film"></i>
                        </div>

                        <h4 class="font-extrabold text-blue-950 text-sm leading-snug">
                            Học qua hình ảnh, video, trò chơi
                        </h4>

                        <span class="mt-1 block text-[10px] text-emerald-600 font-bold leading-snug">
                            Visuals, Videos &amp; Games
                        </span>

                        <span class="mt-2 block text-[10px] text-slate-500 font-semibold leading-relaxed max-w-[10rem] mx-auto">
                            Sinh động, vừa học vừa trải nghiệm, rất hợp với học viên nhỏ tuổi.
                        </span>
                    </div>

                    <!-- Block -->
                    <div class="group rounded-2xl p-4 sm:p-5 bg-white/90 backdrop-blur-xl border border-amber-100 shadow-sm text-center">
                        <div class="w-12 h-12 mx-auto mb-3 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                            <i class="fa-solid fa-people-group"></i>
                        </div>

                        <h4 class="font-extrabold text-blue-950 text-sm leading-snug">
                            Hoạt động tương tác trên lớp
                        </h4>

                        <span class="mt-1 block text-[10px] text-amber-600 font-bold leading-snug">
                            Interactive Classroom Activities
                        </span>

                        <span class="mt-2 block text-[10px] text-slate-500 font-semibold leading-relaxed max-w-[10rem] mx-auto">
                            Thảo luận, trình bày và làm việc nhóm để tăng sự tự tin giao tiếp.
                        </span>
                    </div>

                    <!-- Block -->
                    <div class="group rounded-2xl p-4 sm:p-5 bg-white/90 backdrop-blur-xl border border-rose-100 shadow-sm text-center">
                        <div class="w-12 h-12 mx-auto mb-3 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                            <i class="fa-solid fa-sliders"></i>
                        </div>

                        <h4 class="font-extrabold text-blue-950 text-sm leading-snug">
                            Kết hợp linh hoạt phương pháp khác
                        </h4>

                        <span class="mt-1 block text-[10px] text-rose-600 font-bold leading-snug">
                            Other Methods as Needed
                        </span>

                        <span class="mt-2 block text-[10px] text-slate-500 font-semibold leading-relaxed max-w-[10rem] mx-auto">
                            Điều chỉnh theo nhu cầu và trình độ để lộ trình học thật sự phù hợp.
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
                    <?= e(t('home.courses_title')); ?> <span class="inline-block mt-2 md:mt-0 rounded-full bg-red-600 px-4 sm:px-6 py-1.5 sm:py-2 text-white shadow-lg transform -rotate-2"><?= e(t('home.courses_highlight')); ?></span>
                </h2>
                <p class="mt-4 sm:mt-6 text-sm sm:text-base md:text-lg text-slate-700 max-w-3xl mx-auto font-semibold">
                    <?= e(t('home.courses_copy')); ?>
                </p>
            </div>

            <div class="rounded-[2rem] sm:rounded-[3rem] bg-white/40 backdrop-blur-md border border-white p-4 sm:p-6 md:p-8 lg:p-10 shadow-[0_15px_40px_rgba(30,58,138,0.06)] overflow-hidden" data-aos="zoom-in">
                <?php if (empty($homeCourses)): ?>
                    <div class="rounded-[2rem] border border-dashed border-slate-300 bg-white/70 px-6 py-14 text-center text-slate-500 font-medium">
                        <?= e(t('home.courses_empty')); ?>
                    </div>
                <?php else: ?>
                    <div class="grid gap-5 sm:gap-6 sm:grid-cols-2 lg:grid-cols-4 mobile-swipe-track">
                        <?php foreach ($homeCourses as $course): ?>
                            <?php
                            $courseTitle = (string) ($course['title'] ?? '');
                            $courseSlug = (string) ($course['slug'] ?? '');
                            $courseImage = (string) ($course['image'] ?? '');
                            $courseLink = page_url('courses', ['course' => $courseSlug]);
                            ?>
                            <article class="mobile-swipe-card group flex flex-col overflow-hidden rounded-[1.5rem] sm:rounded-[2rem] bg-white/90 shadow-lg border border-rose-100/70 transition-all duration-300 hover:-translate-y-3 hover:shadow-xl hover:shadow-rose-100/50">
                                <div class="relative h-40 sm:h-44 overflow-hidden bg-gradient-to-br from-rose-50 via-white to-emerald-50">
                                    <?php if ($courseImage !== ''): ?>
                                        <img src="<?= e($courseImage); ?>" alt="<?= e($courseTitle); ?>" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                                        <div class="absolute inset-0 bg-gradient-to-t from-red-500/35 via-rose-400/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                    <?php else: ?>
                                        <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-rose-50 via-white to-emerald-50">
                                            <div class="text-center text-red-500">
                                                <div class="mx-auto mb-3 flex h-16 w-16 sm:h-20 sm:w-20 items-center justify-center rounded-full bg-white/90 shadow-lg ring-1 ring-rose-100">
                                                    <i class="fa-solid fa-book-open text-2xl sm:text-3xl text-red-400"></i>
                                                </div>
                                                <div class="text-[10px] sm:text-xs font-black uppercase tracking-[0.22em] text-slate-700"><?= e(t('home.course_image')); ?></div>
                                            </div>
                                        </div>
                                        <div class="absolute inset-0 bg-gradient-to-t from-red-500/12 via-rose-400/8 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                    <?php endif; ?>
                                    <div class="absolute top-3 sm:top-4 right-3 sm:right-4 bg-gradient-to-r from-[#0b5d1e] via-[#159f2e] to-[#8dff1a] text-white rounded-xl sm:rounded-2xl px-2 sm:px-3 py-1.5 sm:py-2 text-center shadow-md shadow-emerald-500/30 backdrop-blur-sm ring-1 ring-white/70">
                                        <span class="block text-[8px] sm:text-[10px] uppercase font-bold opacity-90"><?= e(t('home.course_sessions')); ?></span>
                                        <span class="block text-xl sm:text-2xl font-black leading-none"><?= (int) ($course['total_sessions'] ?? 0); ?></span>
                                    </div>
                                </div>
                                <div class="flex flex-1 flex-col p-4 sm:p-5">
                                    <div class="inline-flex w-fit rounded-full bg-gradient-to-r from-emerald-50 to-rose-50 px-2.5 py-1 text-[9px] sm:text-[10px] font-black uppercase tracking-[0.18em] text-emerald-700 ring-1 ring-emerald-100/80">
                                        <?= e((string) ($course['level'] ?? t('courses.default_tag'))); ?>
                                    </div>
                                    <h3 class="mt-2 sm:mt-3 text-lg sm:text-xl font-extrabold uppercase leading-tight text-transparent bg-clip-text bg-gradient-to-r from-red-500 via-rose-500 to-emerald-500 transition-colors line-clamp-2 min-h-[3.5rem]"><?= e($courseTitle); ?></h3>
                                    <div class="mt-3 grid grid-cols-2 gap-2 text-[10px] sm:text-xs font-semibold text-slate-700">
                                        <div class="rounded-2xl bg-rose-50 px-3 py-2 ring-1 ring-rose-100">
                                            <div class="uppercase tracking-wide text-slate-500"><?= e(t('home.course_sessions')); ?></div>
                                            <div class="mt-1 font-black text-slate-900"><?= (int) ($course['total_sessions'] ?? 0); ?> <?= e(t('home.course_sessions')); ?></div>
                                        </div>
                                        <div class="rounded-2xl bg-emerald-50 px-3 py-2 ring-1 ring-emerald-100">
                                            <div class="uppercase tracking-wide text-slate-500"><?= e(t('admin.courses.roadmap')); ?></div>
                                            <div class="mt-1 font-black text-slate-900"><?= e(t('home.roadmaps_count', ['count' => (int) ($course['roadmap_count'] ?? 0)])); ?></div>
                                        </div>
                                    </div>
                                    <div class="mt-3 sm:mt-4 pt-3 border-t-2 border-slate-100 flex flex-col gap-3">
                                        <div class="flex items-end justify-between gap-3 sm:gap-4">
                                            <div>
                                                <span class="block text-[10px] sm:text-xs font-bold text-slate-700 uppercase tracking-wide"><?= e(t('courses.price_from')); ?></span>
                                                <span class="text-lg sm:text-xl font-black text-transparent bg-clip-text bg-gradient-to-r from-red-500 to-emerald-500"><?= e((string) ($course['price'] ?? '0đ')); ?></span>
                                            </div>
                                            <div class="text-right text-[10px] sm:text-xs font-semibold text-slate-700">
                                                <div><?= e(t('home.classes_count', ['count' => (int) ($course['class_count'] ?? 0)])); ?></div>
                                            </div>
                                        </div>
                                        <a href="<?= e($courseLink); ?>" class="inline-flex items-center justify-center gap-2 rounded-full bg-gradient-to-r from-[#0b5d1e] via-[#159f2e] to-[#8dff1a] px-4 py-2.5 text-xs sm:text-sm font-bold text-white shadow-md shadow-emerald-500/30 transition-all hover:-translate-y-0.5 hover:from-[#084916] hover:via-[#118427] hover:to-[#74e414] hover:shadow-lg hover:shadow-emerald-500/40">
                                            <?= e(t('public.common.view_detail')); ?> <i class="fa-solid fa-arrow-right text-[10px] sm:text-xs"></i>
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

    <?php if (!empty($homePromotions)): ?>
        <section id="uu-dai" class="relative overflow-hidden bg-transparent py-12 md:py-16">
            <div class="absolute inset-x-0 top-0 h-24 bg-gradient-to-b from-transparent via-white/28 to-transparent pointer-events-none"></div>
            <div class="absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-transparent via-white/22 to-transparent pointer-events-none"></div>
            <div class="relative z-10 mx-auto w-full max-w-7xl px-4 sm:px-6">
                <div class="rounded-[2rem] border border-amber-100/80 bg-gradient-to-br from-white via-amber-50/88 to-rose-50/82 p-4 shadow-[0_22px_60px_rgba(245,158,11,0.14)] backdrop-blur-sm sm:p-6 md:p-8" data-aos="fade-up">
                    <div class="mb-7 flex flex-col gap-5 md:flex-row md:items-end md:justify-between">
                        <div class="max-w-3xl">
                            <span class="inline-flex items-center gap-2 rounded-full border border-rose-200 bg-white px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.18em] text-rose-600 shadow-sm">
                                <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                                <?= e(t('home.promotions_badge')); ?>
                            </span>
                            <h2 class="mt-4 pt-2 text-[2.2rem] font-black uppercase leading-[1.14] text-slate-950 sm:text-[2.6rem] md:text-[2.9rem] lg:text-[3.2rem]">
                                <span class="block"><?= e(t('home.promotions_title')); ?></span>
                                <span class="mt-1 block text-transparent bg-clip-text bg-gradient-to-r from-rose-600 to-amber-500 md:whitespace-nowrap"><?= e(t('home.promotions_highlight')); ?></span>
                            </h2>
                            <p class="mt-4 max-w-3xl text-base font-bold leading-relaxed text-slate-600 md:text-lg">
                                <?= e(t('home.promotions_copy')); ?>
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <a href="#dang-ky-tu-van" class="inline-flex items-center justify-center gap-2 rounded-full bg-slate-950 px-5 py-3 text-xs font-black uppercase tracking-widest text-white shadow-md transition-all hover:-translate-y-0.5 hover:bg-rose-600">
                                <?= e(t('home.promotions_cta')); ?>
                                <i class="fa-solid fa-arrow-right text-[11px]"></i>
                            </a>
                            <div class="inline-flex items-center justify-center gap-2">
                                <button type="button" class="promotion-swiper-prev flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600" aria-label="Previous promotion">
                                    <i class="fa-solid fa-chevron-left text-xs"></i>
                                </button>
                                <button type="button" class="promotion-swiper-next flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600" aria-label="Next promotion">
                                    <i class="fa-solid fa-chevron-right text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="promotionSwiper swiper !overflow-y-visible overflow-x-hidden rounded-[1.35rem] px-1 pt-5 pb-9">
                        <div class="swiper-wrapper !overflow-visible">
                            <?php foreach ($homePromotions as $index => $promotion): ?>
                                <?php
                                $discountText = (string) ($promotion['discount_text'] ?? '0');
                                $promotionName = (string) ($promotion['name'] ?? '');
                                $courseName = trim((string) ($promotion['course_name'] ?? ''));
                                $scopeLabel = ((int) ($promotion['course_id'] ?? 0)) > 0 && $courseName !== ''
                                    ? $courseName
                                    : t('home.promotions_scope_all_courses');
                                $endDateText = $homeFormatPromotionDate((string) ($promotion['end_date'] ?? ''));
                                $quantityLimit = $promotion['quantity_limit'] ?? null;
                                $quantityRemaining = $promotion['quantity_remaining'] ?? null;
                                $quantityLabel = $quantityLimit === null
                                    ? t('admin.promotions.quantity_unlimited')
                                    : t('admin.promotions.quantity_remaining', [
                                        'remaining' => (int) max(0, (int) $quantityRemaining),
                                        'limit' => (int) $quantityLimit,
                                    ]);
                                $durationLabel = $endDateText !== ''
                                    ? 'Thời hạn: ' . t('home.promotions_until', ['date' => $endDateText])
                                    : 'Thời hạn: ' . t('home.promotions_unlimited_duration');
                                $stockLabel = 'Số lượng: ' . $quantityLabel;
                                $accentClasses = [
                                    'border-rose-200 bg-rose-50 text-rose-700',
                                    'border-amber-200 bg-amber-50 text-amber-700',
                                    'border-emerald-200 bg-emerald-50 text-emerald-700',
                                ][$index % 3];
                                ?>
                                <div class="swiper-slide h-auto pb-2">
                                    <article class="group flex h-[238px] min-h-[238px] flex-col rounded-[1.35rem] border-2 border-slate-300 bg-white p-5 shadow-[0_14px_34px_rgba(15,23,42,0.08)] ring-0 ring-rose-200/0 transition-all duration-300 hover:border-rose-400 hover:shadow-[0_24px_52px_rgba(225,29,72,0.20)] hover:ring-4 hover:ring-rose-200/80">
                                        <div class="flex items-start justify-between gap-4">
                                            <div class="min-w-0 flex-1">
                                                <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[10px] font-black uppercase tracking-[0.14em] <?= e($accentClasses); ?>">
                                                    <i class="fa-solid fa-tags text-[10px]"></i>
                                                    <?= e($scopeLabel); ?>
                                                </span>
                                                <h3 class="mt-3 h-[2.42rem] overflow-hidden pr-2 text-[1.05rem] font-black leading-[1.15] tracking-tight text-slate-950" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;word-break:break-word;overflow-wrap:anywhere;max-height:2.42rem;"><?= e($promotionName); ?></h3>
                                            </div>
                                            <div class="shrink-0 rounded-2xl bg-rose-600 px-4 py-3 text-right text-white shadow-md shadow-rose-500/20">
                                                <p class="text-[9px] font-black uppercase tracking-[0.18em] text-white/70"><?= e(t('home.promotions_discount_prefix')); ?></p>
                                                <div class="mt-1 flex items-baseline justify-end gap-1">
                                                    <span class="text-3xl font-black leading-none"><?= e($discountText); ?></span>
                                                    <span class="text-sm font-black">%</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-auto flex flex-col items-start gap-2 pt-3 text-[11px] font-bold">
                                            <span class="inline-flex min-h-[36px] items-center gap-1.5 rounded-full border border-amber-100 bg-amber-50 px-3 py-1.5 text-amber-700">
                                                <i class="fa-regular fa-clock"></i>
                                                <?= e($durationLabel); ?>
                                            </span>
                                            <span class="inline-flex min-h-[36px] items-center gap-1.5 rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1.5 text-emerald-700">
                                                <i class="fa-solid fa-ticket"></i>
                                                <?= e($stockLabel); ?>
                                            </span>
                                        </div>
                                    </article>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="swiper-pagination-promotion mt-6 flex justify-center"></div>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <section id="dang-ky-tu-van" class="relative py-20 md:py-32 overflow-hidden">
        <!-- Background image hero banner -->
        <div class="absolute inset-0">
            <img src="/assets/images/consult.jpg" alt="<?= e(t('courses.image_alt')); ?>" class="h-full w-full object-cover brightness-110 contrast-105 saturate-105">
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
                        <?= e(t('public.common.quick_consultation')); ?>
                    </span>

                    <h2 class="mt-8 text-4xl md:text-5xl lg:text-6xl font-black leading-tight tracking-tight text-white">
                        <?= e(t('public.common.start_english_journey')); ?> <br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-rose-200 to-orange-200"><?= e(t('public.common.conquer_english')); ?></span>
                    </h2>
                    
                    <p class="mt-6 max-w-xl text-base md:text-lg leading-relaxed text-white/95 drop-shadow-[0_2px_8px_rgba(15,23,42,0.25)]">
                        <?= e(t('public.common.consultation_copy')); ?>
                    </p>

                    <div class="mt-10 grid gap-4 sm:grid-cols-3 max-w-lg">
                        <div class="rounded-[1.5rem] border border-white/28 bg-white/22 p-5 shadow-[0_12px_30px_rgba(15,23,42,0.18)] backdrop-blur-sm">
                            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-white/25 text-white shadow-sm ring-1 ring-white/20">
                                <i class="fa-regular fa-clock text-sm"></i>
                            </div>
                            <p class="text-2xl font-black text-white drop-shadow-[0_2px_6px_rgba(15,23,42,0.28)]">15'</p>
                            <p class="mt-1 text-[9px] font-bold uppercase tracking-widest text-white/88"><?= e(t('public.common.contact_now')); ?></p>
                        </div>
                        <div class="rounded-[1.5rem] border border-white/28 bg-white/22 p-5 shadow-[0_12px_30px_rgba(15,23,42,0.18)] backdrop-blur-sm">
                            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-white/25 text-white shadow-sm ring-1 ring-white/20">
                                <i class="fa-solid fa-user-group text-sm"></i>
                            </div>
                            <p class="text-2xl font-black text-white drop-shadow-[0_2px_6px_rgba(15,23,42,0.28)]">1:1</p>
                            <p class="mt-1 text-[9px] font-bold uppercase tracking-widest text-white/88"><?= e(t('public.common.expert')); ?></p>
                        </div>
                        <div class="rounded-[1.5rem] border border-white/28 bg-white/22 p-5 shadow-[0_12px_30px_rgba(15,23,42,0.18)] backdrop-blur-sm">
                            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-white/25 text-white shadow-sm ring-1 ring-white/20">
                                <i class="fa-solid fa-wand-magic-sparkles text-sm"></i>
                            </div>
                            <p class="text-2xl font-black text-white drop-shadow-[0_2px_6px_rgba(15,23,42,0.28)]">100%</p>
                            <p class="mt-1 text-[9px] font-bold uppercase tracking-widest text-white/88"><?= e(t('public.common.personalized')); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Right side: Form panel overlay - Psychology: White (trust/cleanliness) + Rose (action) + Emerald (growth) -->
                <div class="relative overflow-hidden rounded-[2.75rem] border border-white/32 bg-slate-950/18 p-8 md:p-10 shadow-[0_28px_80px_rgba(15,23,42,0.34)] backdrop-blur-2xl" data-aos="fade-left" data-aos-duration="700" data-aos-delay="100">
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
                            <?= e(t('public.common.free_consultation')); ?>
                            <span class="ml-2 text-transparent bg-clip-text bg-gradient-to-r from-lime-300 to-emerald-300" style="text-shadow: 
                                2px 2px 0 rgba(190, 242, 100, 0.22),
                                4px 4px 0 rgba(132, 204, 22, 0.16),
                                0 6px 12px rgba(101, 163, 13, 0.18);
                            "><?= e(t('public.common.free')); ?></span>
                        </h3>
                        <!-- Subheading: Trust messaging (emerald psychology) -->
                        <p class="text-sm font-semibold text-white/85">
                            <i class="fa-solid fa-check-circle text-emerald-500 mr-2"></i>
                            <?= e(t('public.common.route_for_you')); ?>
                        </p>
                    </div>

                    <form action="/api/index.php?resource=leads&method=submit" method="POST" class="relative z-10 grid gap-6 sm:grid-cols-2">
                        <?= csrf_input(); ?>
                        <input type="hidden" name="redirect_to" value="<?= e(page_url('home') . '#dang-ky-tu-van'); ?>">
                        <!-- Name field: Rose psychology (action/engagement) -->
                        <div class="sm:col-span-2 group">
                            <label class="mb-3 flex items-center gap-2 text-[11px] font-black uppercase tracking-[0.16em] text-white group-focus-within:text-rose-300 transition-colors">
                                <i class="fa-solid fa-user text-rose-500"></i>
                                <?= e(t('public.common.full_name')); ?> <span class="text-rose-500 text-base">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-5 top-1/2 -translate-y-1/2 text-rose-400 group-focus-within:text-rose-500 transition-colors"><i class="fa-regular fa-user"></i></span>
                                <input type="text" name="full_name" required placeholder="<?= e(t('public.common.full_name_placeholder')); ?>" class="w-full rounded-2xl border border-slate-200 bg-white py-4 pl-14 pr-5 text-sm font-bold text-slate-900 shadow-sm outline-none transition-all placeholder:text-slate-400 placeholder:font-medium focus:border-rose-400 focus:ring-4 focus:ring-rose-500/15 focus:shadow-lg focus:shadow-rose-500/10">
                            </div>
                        </div>

                        <!-- Phone field: Rose for action/contact -->
                        <div class="group">
                            <label class="mb-3 flex items-center gap-2 text-[11px] font-black uppercase tracking-[0.16em] text-white group-focus-within:text-rose-300 transition-colors">
                                <i class="fa-solid fa-phone text-rose-500"></i>
                                <?= e(t('public.common.phone')); ?> <span class="text-rose-500 text-base">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-5 top-1/2 -translate-y-1/2 text-rose-400 group-focus-within:text-rose-500 transition-colors"><i class="fa-solid fa-phone"></i></span>
                                <input type="tel" name="phone" required placeholder="09xx xxx xxx" class="w-full rounded-2xl border border-slate-200 bg-white py-4 pl-14 pr-5 text-sm font-bold text-slate-900 shadow-sm outline-none transition-all placeholder:text-slate-400 placeholder:font-medium focus:border-rose-400 focus:ring-4 focus:ring-rose-500/15 focus:shadow-lg focus:shadow-rose-500/10">
                            </div>
                        </div>

                        <!-- Date field: Emerald for info/optional (growth psychology) -->
                        <div class="group">
                            <label class="mb-3 flex items-center gap-2 text-[11px] font-black uppercase tracking-[0.16em] text-white group-focus-within:text-emerald-300 transition-colors">
                                <i class="fa-solid fa-calendar text-emerald-500"></i>
                                <?= e(t('public.common.birthdate')); ?>
                            </label>
                            <div class="relative">
                                <span class="absolute left-5 top-1/2 -translate-y-1/2 text-emerald-400 group-focus-within:text-emerald-500 transition-colors"><i class="fa-regular fa-calendar"></i></span>
                                <input type="date" name="dob" class="w-full rounded-2xl border border-slate-200 bg-white py-4 pl-14 pr-5 text-sm font-bold text-slate-900 shadow-sm outline-none transition-all focus:border-emerald-400 focus:ring-4 focus:ring-emerald-500/15 focus:shadow-lg focus:shadow-emerald-500/10">
                            </div>
                        </div>

                        <!-- CTA Button: Rose (urgency/action psychology) + Emerald accent (trust) -->
                        <button type="submit" class="sm:col-span-2 mt-2 group relative inline-flex items-center justify-center gap-3 rounded-2xl bg-gradient-to-r from-rose-500 to-rose-600 px-8 py-4 text-sm font-black uppercase tracking-widest text-white shadow-lg shadow-rose-500/30 transition-all duration-300 hover:-translate-y-1.5 hover:from-rose-600 hover:to-rose-700 hover:shadow-rose-600/50 active:translate-y-0 active:shadow-rose-500/20">
                            <span class="flex items-center gap-2">
                                <?= e(t('public.common.send_request')); ?>
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
                    <img src="/assets/images/center2.jpg" alt="<?= e(t('home.center_location_alt')); ?>" class="w-full h-[300px] sm:h-[400px] lg:h-[500px] object-cover transform group-hover:scale-105 transition duration-700 ease-in-out">
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
                            <p class="text-[9px] sm:text-[10px] uppercase tracking-widest text-blue-600 font-black mb-0.5 flex items-center gap-1.5"><?= e(t('home.location_label')); ?></p>
                            <h4 class="text-xs sm:text-sm font-black text-blue-950"><?= e(t('home.location_name')); ?></h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-5 sm:space-y-7 mt-8 lg:mt-0" data-aos="fade-left">
                <div class="inline-flex items-center gap-2 sm:gap-3 px-4 sm:px-5 py-2 sm:py-3 rounded-full bg-gradient-to-r from-white to-blue-50 border border-blue-200 shadow-md shadow-blue-100/60 ring-1 ring-white/70">
                    <span class="w-2 h-2 sm:w-3 sm:h-3 rounded-full bg-blue-500 animate-pulse shadow-[0_0_0_6px_rgba(59,130,246,0.12)]"></span>
                    <span class="text-blue-900 text-xs sm:text-sm md:text-base font-black uppercase tracking-[0.22em]"><?= e(t('home.about_badge')); ?> <span class="text-red-600"><?= e(t('home.center_name')); ?> </span>Edu</span>
                </div>
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-extrabold leading-[1.15] text-blue-950"><?= e(t('home.about_title')); ?><br><span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-cyan-500"><?= e(t('home.about_highlight')); ?></span></h2>
                <p class="text-base sm:text-lg text-slate-600 leading-relaxed font-medium"><?= e(t('home.about_copy')); ?></p>

                <div class="grid sm:grid-cols-2 gap-4 sm:gap-5 mt-4 sm:mt-6">
                    <div class="bg-white/80 backdrop-blur-sm p-5 sm:p-6 rounded-2xl shadow-sm border border-blue-100 hover:-translate-y-1 hover:shadow-lg transition-all duration-300 group">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br from-blue-400 to-blue-600 text-white flex items-center justify-center mb-3 sm:mb-4 shadow-sm group-hover:scale-110 transition-transform"><i class="fa-solid fa-map-location-dot"></i></div>
                        <h4 class="font-extrabold text-blue-950 mb-1 text-sm sm:text-base"><?= e(t('home.about_location_title')); ?></h4>
                        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed"><?= e(t('home.about_location_copy')); ?></p>
                    </div>
                    <div class="bg-white/80 backdrop-blur-sm p-5 sm:p-6 rounded-2xl shadow-sm border border-teal-100 hover:-translate-y-1 hover:shadow-lg transition-all duration-300 group">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br from-teal-400 to-teal-600 text-white flex items-center justify-center mb-3 sm:mb-4 shadow-sm group-hover:scale-110 transition-transform"><i class="fa-solid fa-shield-halved"></i></div>
                        <h4 class="font-extrabold text-teal-950 mb-1 text-sm sm:text-base"><?= e(t('home.about_safe_title')); ?></h4>
                        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed"><?= e(t('home.about_safe_copy')); ?></p>
                    </div>
                </div>
                <div class="pt-2 sm:pt-4">
                    <a href="#lien-he" class="inline-flex items-center justify-center gap-2 sm:gap-3 px-6 sm:px-8 py-3 sm:py-4 rounded-full bg-gradient-to-r from-blue-600 to-sky-500 text-white font-bold text-sm sm:text-base shadow-[0_10px_20px_rgba(37,99,235,0.3)] transition-all hover:-translate-y-1 hover:shadow-[0_15px_25px_rgba(37,99,235,0.4)]">
                        <?= e(t('home.discover_now')); ?> <i class="fa-solid fa-arrow-right"></i>
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
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-black uppercase tracking-tight text-[#2e3192]"><?= e(t('home.mission_title')); ?> <span class="text-red-600"><?= e(t('home.mission_highlight')); ?></span></h2>
                <p class="mt-3 sm:mt-4 text-slate-600 font-medium max-w-2xl mx-auto text-base sm:text-lg"><?= e(t('home.mission_copy')); ?></p>
            </div>
            
            <div class="relative w-full max-w-[1260px] mx-auto min-h-[400px] sm:min-h-[620px] md:min-h-[940px] flex items-center justify-center orbit-wrapper mt-6 sm:mt-10 md:mt-2" style="transform: scale(0.95);">
                <div class="absolute w-[320px] h-[320px] sm:w-[430px] sm:h-[430px] md:w-[770px] md:h-[770px] rounded-full border-2 border-dashed border-blue-400/50 orbit-spin z-10">
                    
                    <div class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 z-50">
                        <div class="orbit-reverse-spin group cursor-pointer">
                            <div class="flex items-center gap-2 sm:gap-4 bg-white/95 backdrop-blur-md p-2 sm:p-3 pr-4 sm:pr-6 rounded-full shadow-lg border border-slate-200 transition-all duration-300 hover:shadow-2xl hover:border-amber-300">
                                <div class="w-10 h-10 sm:w-14 sm:h-14 md:w-16 md:h-16 rounded-full flex items-center justify-center text-xl sm:text-3xl shrink-0 bg-amber-50 text-amber-500 shadow-inner">💡</div>
                                <div class="overflow-hidden transition-[max-width,max-height] duration-500 ease-in-out max-w-[100px] sm:max-w-[140px] max-h-[28px] sm:max-h-[36px] group-hover:max-w-[280px] sm:group-hover:max-w-[340px] group-hover:max-h-[140px] sm:group-hover:max-h-[170px]">
                                    <h4 class="font-black text-[#2e3192] text-xs sm:text-sm md:text-lg whitespace-nowrap"><?= e(t('home.mission_creative')); ?></h4>
                                    <p class="text-[10px] sm:text-xs md:text-sm text-slate-600 font-medium mt-1 w-[200px] sm:w-[260px] opacity-0 group-hover:opacity-100 transition-opacity duration-500 delay-100 leading-relaxed">
                                        <?= e(t('home.mission_creative_copy')); ?>
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
                                    <h4 class="font-black text-[#2e3192] text-xs sm:text-sm md:text-lg whitespace-nowrap"><?= e(t('home.mission_confident')); ?></h4>
                                    <p class="text-[10px] sm:text-xs md:text-sm text-slate-600 font-medium mt-1 w-[200px] sm:w-[260px] opacity-0 group-hover:opacity-100 transition-opacity duration-500 delay-100 leading-relaxed">
                                        <?= e(t('home.mission_confident_copy')); ?>
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
                                    <h4 class="font-black text-[#2e3192] text-xs sm:text-sm md:text-lg whitespace-nowrap"><?= e(t('home.mission_complete')); ?></h4>
                                    <p class="text-[10px] sm:text-xs md:text-sm text-slate-600 font-medium mt-1 w-[200px] sm:w-[260px] opacity-0 group-hover:opacity-100 transition-opacity duration-500 delay-100 leading-relaxed">
                                        <?= e(t('home.mission_complete_copy')); ?>
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
                                    <h4 class="font-black text-[#2e3192] text-xs sm:text-sm md:text-lg whitespace-nowrap"><?= e(t('home.mission_commitment')); ?></h4>
                                    <p class="text-[10px] sm:text-xs md:text-sm text-slate-600 font-medium mt-1 w-[200px] sm:w-[260px] opacity-0 group-hover:opacity-100 transition-opacity duration-500 delay-100 leading-relaxed">
                                        <?= e(t('home.mission_commitment_copy')); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative z-20 w-44 h-44 sm:w-64 sm:h-64 md:w-[450px] md:h-[450px] rounded-full border-[6px] sm:border-[10px] md:border-[14px] border-white shadow-[0_20px_60px_rgba(30,58,138,0.2)] overflow-hidden bg-white flex items-center justify-center group" data-aos="zoom-in">
                    <img src="assets/images/mission4.png" alt="<?= e(t('home.center_alt')); ?>" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                    <div class="absolute inset-0 bg-blue-900/10 group-hover:bg-transparent transition-colors"></div>
                </div>

            </div>
        </div>
    </section>                            
								
    <section id="ngoai-khoa" class="py-12 sm:py-16 bg-transparent relative overflow-hidden">
        <div class="mx-auto max-w-[1400px] px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="mb-10 sm:mb-12 text-center md:text-left" data-aos="fade-up">
                <h2 class="text-2xl md:text-3xl lg:text-4xl font-extrabold tracking-tight text-slate-800 mb-2">
                    <?= e(t('home.activities_title')); ?>
                </h2>
                <p class="text-slate-600 font-medium text-sm md:text-base"><?= e(t('home.activities_copy')); ?></p>
            </div>

            <?php $activities = $homeActivities ?? []; ?>
            <div class="overflow-hidden pb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="grid gap-5 sm:gap-6 lg:gap-8 sm:grid-cols-2 lg:grid-cols-4 mobile-swipe-track">
                    <?php if (empty($activities)): ?>
                        <div class="sm:col-span-2 lg:col-span-4 rounded-[2rem] border border-dashed border-slate-300 bg-white/70 px-6 py-14 text-center text-slate-500 font-medium">
                            <?= e(t('home.activities_empty')); ?>
                        </div>
                    <?php else: ?>
                        <?php foreach ($activities as $act): ?>
                            <?php
                            $activityTitle = (string) ($act['activity_name'] ?? '');
                            $activityImage = trim((string) ($act['image_thumbnail'] ?? ''));
                            $activityLink = page_url('activities-home-detail', ['id' => (int) ($act['id'] ?? 0)]);
                            $activityDate = !empty($act['start_date']) ? date('d/m/Y', strtotime((string) $act['start_date'])) : '---';
                            $activityLocation = trim((string) ($act['location'] ?? ''));
                            $activityFee = (float) ($act['fee'] ?? 0);
                            $activityStatus = (string) ($act['status'] ?? 'upcoming');
                            $activityStatusLabel = match ($activityStatus) {
                                'ongoing' => t('activities.status.ongoing'),
                                'finished' => t('activities.status.finished'),
                                default => t('activities.status.upcoming'),
                            };
                            ?>
                            <a href="<?= e($activityLink); ?>" class="mobile-swipe-card group bg-white/95 rounded-2xl sm:rounded-3xl p-3 border border-white shadow-[0_18px_40px_rgba(15,23,42,0.08)] hover:shadow-xl transition-all duration-300 hover:-translate-y-2 cursor-pointer flex flex-col">
                                <div class="relative w-full aspect-[4/3] rounded-xl sm:rounded-2xl overflow-hidden mb-3 sm:mb-4 bg-slate-100">
                                    <?php if ($activityImage !== ''): ?>
                                        <img src="<?= e($activityImage); ?>" alt="<?= e($activityTitle); ?>" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
                                    <?php else: ?>
                                        <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-sky-100 via-white to-lime-100 text-slate-400">
                                            <i class="fa-solid fa-rocket text-2xl sm:text-3xl"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="absolute top-4 left-4">
                                        <span class="bg-white/90 backdrop-blur-md text-rose-600 px-3 py-1 rounded-full text-[10px] font-black uppercase shadow-sm">
                                            <?= e($activityStatusLabel); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="px-2 pb-2 sm:pb-3 flex-1 flex flex-col">
                                    <div class="flex items-center gap-3 text-emerald-600 text-[11px] font-bold mb-3">
                                        <i class="fa-solid fa-calendar-day"></i> <?= e($activityDate); ?>
                                    </div>
                                    <h3 class="text-[#0d3b66] font-bold text-base sm:text-lg leading-tight mb-3 group-hover:text-blue-600 transition-colors"><?= e($activityTitle); ?></h3>
                                    <p class="text-slate-400 text-xs font-medium mb-4 flex items-center gap-2">
                                        <i class="fa-solid fa-location-dot"></i> <?= e($activityLocation !== '' ? $activityLocation : '---'); ?>
                                    </p>
                                    <div class="mt-auto flex items-end justify-between gap-3">
                                        <p class="text-xs font-semibold text-slate-500">
                                            <?= e(t('activities.fee')); ?>: <?= $activityFee > 0 ? e(number_format($activityFee) . ' đ') : e(t('activities.free_fee')); ?>
                                        </p>
                                        <div class="inline-flex items-center gap-2 font-black text-slate-900 text-sm">
                                            <?= e(t('public.common.view_detail')); ?>
                                            <span class="w-7 h-7 rounded-full border-2 border-slate-100 flex items-center justify-center group-hover:bg-emerald-500 group-hover:text-white group-hover:border-emerald-500 transition-all">
                                                <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mt-6 sm:mt-8 flex justify-center" data-aos="fade-up">
                <a href="<?= e(page_url('activities-home')); ?>" class="inline-flex items-center justify-center gap-2 rounded-full bg-[#2e3192] px-6 py-3 sm:py-3.5 text-xs sm:text-sm font-bold text-white shadow-md transition-transform hover:-translate-y-0.5 hover:bg-blue-600">
                    <?= e(t('home.view_more')); ?> <i class="fa-solid fa-arrow-right text-[10px] sm:text-xs"></i>
                </a>
            </div>
        </div>
    </section>

    <section id="danh-gia" class="relative pt-6 pb-12 sm:pt-8 sm:pb-14 md:pt-10 md:pb-20 overflow-hidden bg-transparent">
        <div class="absolute inset-0 pointer-events-none hidden sm:block">
            <div class="absolute -top-24 -right-24 h-80 w-80 rounded-full bg-cyan-200/30 blur-3xl"></div>
            <div class="absolute -bottom-24 -left-24 h-80 w-80 rounded-full bg-rose-200/30 blur-3xl"></div>
        </div>

        <div class="mx-auto w-full max-w-[1400px] px-4 sm:px-6 relative z-10">
            <div class="mb-8 sm:mb-10" data-aos="fade-up">
                <div class="max-w-2xl">
                    <div class="inline-flex items-center gap-1.5 sm:gap-2 rounded-full border border-emerald-100 bg-white/80 px-3 sm:px-4 py-1.5 sm:py-2 text-[9px] sm:text-[10px] font-black uppercase tracking-[0.35em] text-emerald-600 shadow-sm">
                        <i class="fa-regular fa-comment-dots"></i> <?= e(t('home.feedback_badge')); ?>
                    </div>
                    <h2 class="mt-3 sm:mt-4 text-2xl sm:text-3xl md:text-5xl font-black tracking-tight text-slate-900"><?= e(t('home.feedback_title')); ?> <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-cyan-500"><?= e(t('home.feedback_highlight')); ?></span></h2>
                    <p class="mt-3 sm:mt-4 text-slate-600 font-medium text-sm sm:text-base md:text-lg leading-relaxed"><?= e(t('home.feedback_copy')); ?></p>
                </div>
            </div>

            <div class="swiper feedbackSwiper" data-aos="fade-up" data-aos-delay="120">
                <div class="swiper-wrapper pb-10 sm:pb-14">
                    <?php if (empty($homeFeedbacks)): ?>
                        <div class="swiper-slide h-auto">
                            <div class="rounded-2xl sm:rounded-[2rem] border border-dashed border-slate-300 bg-white/80 p-6 sm:p-8 text-center text-slate-500 font-medium text-sm sm:text-base">
                                <?= e(t('home.feedback_empty')); ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($homeFeedbacks as $feedback): ?>
                            <?php
                            $feedbackName = (string) ($feedback['full_name'] ?? t('home.student'));
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
                                                <p class="text-[9px] sm:text-[10px] md:text-[11px] font-semibold uppercase tracking-[0.2em] text-emerald-600"><?= e(t('home.student_parent')); ?></p>
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
                                        “<?= e($feedbackContent !== '' ? $feedbackContent : t('home.feedback_default')); ?>”
                                    </p>

                                    <div class="mt-3 sm:mt-4 flex flex-wrap items-center gap-1.5 sm:gap-2 text-[9px] sm:text-[10px] md:text-[11px] font-bold uppercase tracking-widest text-slate-500">
                                        <?php if ($feedbackClass !== ''): ?>
                                            <span class="rounded-full bg-emerald-50 px-2 sm:px-3 py-0.5 sm:py-1 text-emerald-700"><?= e($feedbackClass); ?></span>
                                        <?php endif; ?>
                                        <?php if ($feedbackTeacher !== ''): ?>
                                            <span class="rounded-full bg-cyan-50 px-2 sm:px-3 py-0.5 sm:py-1 text-cyan-700"><?= e(t('home.teacher_abbr')); ?>: <?= e($feedbackTeacher); ?></span>
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
                        <i class="fa-solid fa-video"></i> <?= e(t('home.portfolio_badge')); ?>
                    </div>
                    <h2 class="mt-3 sm:mt-4 text-2xl sm:text-3xl md:text-5xl font-black tracking-tight text-slate-900"><?= e(t('home.portfolio_title')); ?> <span class="text-transparent bg-clip-text bg-gradient-to-r from-lime-600 to-emerald-500"><?= e(t('home.portfolio_highlight')); ?></span></h2>
                    <p class="mt-3 sm:mt-4 text-slate-600 font-medium text-sm sm:text-base md:text-lg leading-relaxed"><?= e(t('home.portfolio_copy')); ?></p>
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
                                <?= e(t('home.portfolio_empty')); ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($studentPortfolios as $portfolio): ?>
                            <?php
                            $portfolioName = (string) ($portfolio['full_name'] ?? $portfolio['student_name'] ?? t('home.student'));
                            $portfolioAvatar = (string) ($portfolio['avatar_url'] ?? $portfolio['avatar'] ?? '');
                            $portfolioMedia = (string) ($portfolio['media_url'] ?? '');
                            $portfolioDescription = trim((string) ($portfolio['description'] ?? ''));
                            $portfolioDescriptionHtml = $renderBbcode($portfolioDescription);
                            $portfolioResult = trim((string) ($portfolio['result'] ?? t('home.portfolio_badge')));
                            
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
                                                <p class="text-[9px] sm:text-[10px] md:text-[11px] font-semibold uppercase tracking-[0.2em] text-lime-600"><?= e(t('home.student')); ?></p>
                                            </div>
                                        </div>

                                        <div class="inline-flex items-center gap-1.5 mb-3 sm:mb-4">
                                            <span class="rounded-full bg-lime-50 px-2 sm:px-3 py-0.5 sm:py-1 text-[9px] sm:text-[10px] md:text-[11px] font-bold uppercase tracking-widest text-lime-700">
                                                <i class="fa-solid fa-trophy text-amber-500 mr-1"></i><?= e($portfolioResult); ?>
                                            </span>
                                        </div>

                                        <?php if ($portfolioDescriptionHtml !== ''): ?>
                                            <div class="text-xs sm:text-sm md:text-base leading-relaxed text-slate-600 line-clamp-2 [&_a]:text-emerald-600 [&_a]:underline [&_a]:underline-offset-2 [&_blockquote]:border-l-4 [&_blockquote]:border-lime-200 [&_blockquote]:pl-3 [&_blockquote]:italic [&_code]:rounded-lg [&_code]:bg-slate-100 [&_code]:px-1.5 [&_code]:py-0.5 [&_code]:font-mono [&_code]:text-[0.92em]">
                                                "<?= $portfolioDescriptionHtml; ?>"
                                            </div>
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
            <img src="/assets/images/consult.jpg" alt="<?= e(t('courses.image_alt')); ?>" class="h-full w-full object-cover brightness-110 contrast-105 saturate-105">
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
                        <?= e(t('public.common.quick_consultation')); ?>
                    </span>

                    <h2 class="mt-8 text-4xl md:text-5xl lg:text-6xl font-black leading-tight tracking-tight text-white">
                        <?= e(t('public.common.start_english_journey')); ?> <br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-rose-200 to-orange-200"><?= e(t('public.common.conquer_english')); ?></span>
                    </h2>
                    
                    <p class="mt-6 max-w-xl text-base md:text-lg leading-relaxed text-white/95 drop-shadow-[0_2px_8px_rgba(15,23,42,0.25)]">
                        <?= e(t('public.common.consultation_copy')); ?>
                    </p>

                    <div class="mt-10 grid gap-4 sm:grid-cols-3 max-w-lg">
                        <div class="rounded-[1.5rem] border border-white/18 bg-white/14 p-5 shadow-lg">
                            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-white/15 text-white">
                                <i class="fa-regular fa-clock text-sm"></i>
                            </div>
                            <p class="text-2xl font-black text-white">15'</p>
                            <p class="mt-1 text-[9px] font-bold uppercase tracking-widest text-white/70"><?= e(t('public.common.contact_now')); ?></p>
                        </div>
                        <div class="rounded-[1.5rem] border border-white/18 bg-white/14 p-5 shadow-lg">
                            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-white/15 text-white">
                                <i class="fa-solid fa-user-group text-sm"></i>
                            </div>
                            <p class="text-2xl font-black text-white">1:1</p>
                            <p class="mt-1 text-[9px] font-bold uppercase tracking-widest text-white/70"><?= e(t('public.common.expert')); ?></p>
                        </div>
                        <div class="rounded-[1.5rem] border border-white/18 bg-white/14 p-5 shadow-lg">
                            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-white/15 text-white">
                                <i class="fa-solid fa-wand-magic-sparkles text-sm"></i>
                            </div>
                            <p class="text-2xl font-black text-white">100%</p>
                            <p class="mt-1 text-[9px] font-bold uppercase tracking-widest text-white/70"><?= e(t('public.common.personalized')); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Right side: Form panel overlay - Psychology: White (trust/cleanliness) + Rose (action) + Emerald (growth) -->
                <div class="relative overflow-hidden rounded-[2.75rem] border border-white/32 bg-slate-950/18 p-8 md:p-10 shadow-[0_28px_80px_rgba(15,23,42,0.34)] backdrop-blur-2xl" data-aos="fade-left" data-aos-duration="700" data-aos-delay="100">

                <!-- <div class="relative overflow-hidden rounded-[2.75rem] border border-white/32 bg-slate-950/30 p-8 md:p-10 shadow-[0_28px_80px_rgba(15,23,42,0.38)] backdrop-blur-none" data-aos="fade-left" data-aos-duration="700" data-aos-delay="100"> -->
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
                            <?= e(t('public.common.free_consultation')); ?>
                            <span class="ml-2 text-transparent bg-clip-text bg-gradient-to-r from-lime-300 to-emerald-300" style="text-shadow: 
                                2px 2px 0 rgba(190, 242, 100, 0.22),
                                4px 4px 0 rgba(132, 204, 22, 0.16),
                                0 6px 12px rgba(101, 163, 13, 0.18);
                            "><?= e(t('public.common.free')); ?></span>
                        </h3>
                        <!-- Subheading: Trust messaging (emerald psychology) -->
                        <p class="text-sm font-semibold text-white/95 drop-shadow-[0_2px_8px_rgba(15,23,42,0.22)]">
                            <i class="fa-solid fa-check-circle text-emerald-500 mr-2"></i>
                            <?= e(t('public.common.route_for_you')); ?>
                        </p>
                    </div>

                    <form action="/api/index.php?resource=leads&method=submit" method="POST" class="relative z-10 grid gap-6 sm:grid-cols-2">
                        <?= csrf_input(); ?>
                        <input type="hidden" name="redirect_to" value="<?= e(page_url('home') . '#lien-he'); ?>">
                        <!-- Name field: Rose psychology (action/engagement) -->
                        <div class="sm:col-span-2 group">
                            <label class="mb-3 flex items-center gap-2 text-[11px] font-black uppercase tracking-[0.16em] text-white group-focus-within:text-rose-300 transition-colors">
                                <i class="fa-solid fa-user text-rose-500"></i>
                                <?= e(t('public.common.full_name')); ?> <span class="text-rose-500 text-base">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-5 top-1/2 -translate-y-1/2 text-rose-400 group-focus-within:text-rose-500 transition-colors"><i class="fa-regular fa-user"></i></span>
                                <input type="text" name="full_name" required placeholder="<?= e(t('public.common.full_name_placeholder')); ?>" class="w-full rounded-2xl border border-slate-200 bg-white py-4 pl-14 pr-5 text-sm font-bold text-slate-900 shadow-sm outline-none transition-all placeholder:text-slate-400 placeholder:font-medium focus:border-rose-400 focus:ring-4 focus:ring-rose-500/15 focus:shadow-lg focus:shadow-rose-500/10">
                            </div>
                        </div>

                        <!-- Phone field: Rose for action/contact -->
                        <div class="group">
                            <label class="mb-3 flex items-center gap-2 text-[11px] font-black uppercase tracking-[0.16em] text-white group-focus-within:text-rose-300 transition-colors">
                                <i class="fa-solid fa-phone text-rose-500"></i>
                                <?= e(t('public.common.phone')); ?> <span class="text-rose-500 text-base">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-5 top-1/2 -translate-y-1/2 text-rose-400 group-focus-within:text-rose-500 transition-colors"><i class="fa-solid fa-phone"></i></span>
                                <input type="tel" name="phone" required placeholder="09xx xxx xxx" class="w-full rounded-2xl border border-slate-200 bg-white py-4 pl-14 pr-5 text-sm font-bold text-slate-900 shadow-sm outline-none transition-all placeholder:text-slate-400 placeholder:font-medium focus:border-rose-400 focus:ring-4 focus:ring-rose-500/15 focus:shadow-lg focus:shadow-rose-500/10">
                            </div>
                        </div>

                        <!-- Date field: Emerald for info/optional (growth psychology) -->
                        <div class="group">
                            <label class="mb-3 flex items-center gap-2 text-[11px] font-black uppercase tracking-[0.16em] text-white group-focus-within:text-emerald-300 transition-colors">
                                <i class="fa-solid fa-calendar text-emerald-500"></i>
                                <?= e(t('public.common.birthdate')); ?>
                            </label>
                            <div class="relative">
                                <span class="absolute left-5 top-1/2 -translate-y-1/2 text-emerald-400 group-focus-within:text-emerald-500 transition-colors"><i class="fa-regular fa-calendar"></i></span>
                                <input type="date" name="dob" class="w-full rounded-2xl border border-slate-200 bg-white py-4 pl-14 pr-5 text-sm font-bold text-slate-900 shadow-sm outline-none transition-all focus:border-emerald-400 focus:ring-4 focus:ring-emerald-500/15 focus:shadow-lg focus:shadow-emerald-500/10">
                            </div>
                        </div>

                        <!-- CTA Button: Rose (urgency/action psychology) + Emerald accent (trust) -->
                        <button type="submit" class="sm:col-span-2 mt-2 group relative inline-flex items-center justify-center gap-3 rounded-2xl bg-gradient-to-r from-rose-500 to-rose-600 px-8 py-4 text-sm font-black uppercase tracking-widest text-white shadow-lg shadow-rose-500/30 transition-all duration-300 hover:-translate-y-1.5 hover:from-rose-600 hover:to-rose-700 hover:shadow-rose-600/50 active:translate-y-0 active:shadow-rose-500/20">
                            <span class="flex items-center gap-2">
                                <?= e(t('public.common.send_request')); ?>
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

<?php $notifyShowTestButtons = false; require __DIR__ . '/../notification/notification.php'; ?>

<script>
    <?php if (!empty($homeLeadSuccess)): ?>
    if (typeof showNotify === 'function') {
        showNotify('success', <?= json_encode($homeLeadSuccess, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>);
    }
    <?php endif; ?>

    <?php if (!empty($homeLeadError)): ?>
    if (typeof showNotify === 'function') {
        showNotify('error', <?= json_encode($homeLeadError, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>);
    }
    <?php endif; ?>
</script>

</main>
