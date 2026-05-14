<?php
$academicModel = new AcademicModel();
$courseLeadSuccess = get_flash('home_success');
$courseLeadError = get_flash('home_error');
$coursesPerPage = 12;
$currentCoursePage = max(1, (int) ($_GET['courses_page'] ?? 1));
$courseTotal = $academicModel->countCourses();
$totalCoursePages = max(1, (int) ceil($courseTotal / $coursesPerPage));
$currentCoursePage = min($currentCoursePage, $totalCoursePages);
$courseRows = $courseTotal > 0
    ? $academicModel->listCoursesPage($currentCoursePage, $coursesPerPage)
    : [];
$buildCoursePageUrl = static function (int $page) : string {
    return page_url('courses', ['courses_page' => $page]) . '#danh-sach-khoa-hoc';
};

$studentTotal = $academicModel->dashboardStats()['student_count'] ?? 0;
$feedbackAverageRating = $academicModel->averageFeedbackRating();
$satisfactionPercent = max(0, min(100, (int) round(($feedbackAverageRating / 5) * 100)));

$buildCourseSlug = static function (string $value): string {
    $slug = strtolower(trim($value));
    $slug = preg_replace('/[^a-z0-9\s-]/u', '', $slug) ?? $slug;
    $slug = preg_replace('/[\s-]+/', '-', $slug) ?? $slug;
    return trim($slug, '-');
};

$resolveCourseImage = static function (?string $value): string {
    $value = trim((string) $value);
    if ($value === '') {
        return '/assets/images/center.jpg';
    }

    if (preg_match('#^(?:https?:)?//#i', $value) === 1) {
        return $value;
    }

    return str_starts_with($value, '/') ? $value : '/' . ltrim($value, '/');
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

$courses = [];
foreach ($courseRows as $row) {
    $courseName = trim((string) ($row['course_name'] ?? ''));
    if ($courseName === '') {
        continue;
    }

    $slug = $buildCourseSlug($courseName);
    $image = $resolveCourseImage((string) ($row['image_thumbnail'] ?? ''));
    $priceValue = number_format((float) ($row['base_price'] ?? 0), 0, ',', '.') . 'đ';

    $courses[] = [
        'slug' => $slug,
        'title' => $courseName,
        'tag' => t('courses.default_tag'),
        'short_desc' => (string) ($row['description'] ?? t('courses.default_desc')),
        'price' => $priceValue,
        'original_price' => $priceValue,
        'duration' => t('courses.sessions', ['count' => (int) ($row['total_sessions'] ?? 0)]),
        'level' => t('courses.updating'),
        'lessons_count' => (int) ($row['total_sessions'] ?? 0),
        'rating' => 5.0,
        'students' => 0,
        'image' => $image,
        'instructor' => [
            'name' => t('courses.instructor_team'),
            'role' => 'Academic Team',
        ],
        'benefits' => [],
        'outline' => [],
        'suitable_for' => [],
        'outcomes' => [],
    ];
}

$stats = [
    ['value' => number_format($courseTotal, 0, ',', '.') . '+', 'label' => t('courses.stats_programs')],
    ['value' => '100+', 'label' => t('courses.stats_students')],
    ['value' => '99%', 'label' => t('courses.stats_satisfaction')],
    ['value' => '100%', 'label' => t('courses.stats_personalized')],
];

// $highlights = [
//     ['icon' => 'fa-solid fa-users', 'title' => 'Lớp học nhỏ', 'desc' => 'Tối ưu tương tác, giáo viên theo sát từng học viên.'],
//     ['icon' => 'fa-solid fa-chalkboard-user', 'title' => 'Phương pháp thực hành', 'desc' => 'Giảm lý thuyết khô cứng, tăng luyện tập và phản xạ.'],
//     ['icon' => 'fa-solid fa-medal', 'title' => 'Lộ trình rõ ràng', 'desc' => 'Có mục tiêu đầu ra và mốc tiến độ từng giai đoạn.'],
// ];
?>

<link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<style>
    .course-card:hover .course-img {
        transform: scale(1.08);
    }

    .course-gradient {
        background: radial-gradient(circle at top left, rgba(255,255,255,0.65), transparent 34%), linear-gradient(135deg, rgba(255,255,255,0.95), rgba(255,255,255,0.8));
    }

    .badge-soft-red {
        background: linear-gradient(135deg, rgba(225,29,72,0.14), rgba(225,29,72,0.08));
    }

    .badge-soft-green {
        background: linear-gradient(135deg, rgba(16,185,129,0.16), rgba(16,185,129,0.08));
    }

    @media (prefers-reduced-motion: reduce) {
        .course-card:hover .course-img {
            transition: none !important;
            animation: none !important;
            transform: none !important;
        }
    }
</style>

<main class="relative bg-lime-100 text-slate-800 overflow-hidden">
    <div class="absolute inset-0 z-0 pointer-events-none opacity-[0.08]" style="background-image: radial-gradient(#475569 1.5px, transparent 1.5px); background-size: 24px 24px;"></div>
    <div class="absolute inset-x-0 top-0 z-0 h-72 pointer-events-none bg-gradient-to-b from-lime-200/75 via-lime-100/45 to-transparent"></div>
    <section id="gioi-thieu" class="relative py-20 lg:py-32 overflow-hidden">
        <div class="absolute inset-0 z-0">
            <img src="/assets/images/course3.jpg" alt="<?= e(t('courses.image_alt')); ?>" class="h-full w-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-r from-slate-950/95 via-slate-900/80 to-transparent"></div>
        </div>

        <div class="mx-auto max-w-[1450px] px-4 sm:px-6 relative z-10">
            <div class="max-w-3xl" data-aos="fade-right" data-aos-duration="800">
                
                <span class="inline-flex items-center gap-2 rounded-full border border-rose-400/30 bg-rose-500/20 px-4 py-2 text-[10px] font-black uppercase tracking-[0.2em] text-rose-200 backdrop-blur-md mb-6 shadow-sm">
                    <span class="h-2 w-2 rounded-full bg-rose-400 animate-pulse"></span>
                    <?= e(t('courses.kicker')); ?>
                </span>

                <h1 class="text-4xl md:text-5xl lg:text-6xl font-black leading-tight text-white mb-6 tracking-tight">
                    <?= e(t('courses.hero_title')); ?> <br class="hidden md:block">
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-rose-400 to-lime-300"><?= e(t('courses.hero_highlight')); ?></span>
                </h1>

                <p class="text-base md:text-lg text-slate-300 leading-relaxed font-medium mb-10 max-w-2xl">
                    <?= e(t('courses.hero_copy')); ?>
                </p>

                <div class="flex flex-wrap gap-4 mb-14">
                    <a href="#danh-sach-khoa-hoc" class="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-rose-500 to-rose-600 px-8 py-4 text-sm font-black uppercase tracking-widest text-white shadow-lg shadow-rose-500/30 transition-transform hover:-translate-y-1">
                        <?= e(t('courses.view_courses')); ?> <i class="fa-solid fa-arrow-down"></i>
                    </a>
                    <a href="#dang-ky-tu-van" class="inline-flex items-center gap-2 rounded-full border border-white/30 bg-white/10 px-8 py-4 text-sm font-black uppercase tracking-widest text-white backdrop-blur-md transition-all hover:-translate-y-1 hover:bg-white/20">
                        <?= e(t('public.common.consultation')); ?> <i class="fa-solid fa-calendar-check"></i>
                    </a>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <?php foreach ($stats as $stat): ?>
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur-md transition-colors hover:bg-white/10">
                            <div class="text-2xl font-black text-white"><?= e($stat['value']); ?></div>
                            <div class="mt-1 text-[10px] font-bold uppercase tracking-wider text-slate-400"><?= e($stat['label']); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>

            </div>
        </div>
    </section>

    <!-- <section class="py-10 md:py-14">
        <div class="mx-auto max-w-[1450px] px-4 sm:px-6">
            <div class="grid gap-4 md:grid-cols-3">
                <?php $highlightDelay = 0; ?>
                <?php foreach ($highlights as $highlight): ?>
                    <div class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_12px_30px_rgba(15,23,42,0.05)] backdrop-blur-md transition-transform duration-300 hover:-translate-y-1" data-aos="fade-up" data-aos-delay="<?= $highlightDelay; ?>" data-aos-duration="600">
                        <div class="morph-content">
                        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl badge-soft-green text-emerald-600">
                            <i class="<?= e($highlight['icon']); ?>"></i>
                        </div>
                        <h3 class="text-lg font-black text-slate-950"><?= e($highlight['title']); ?></h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600"><?= e($highlight['desc']); ?></p>
                        </div>
                    </div>
                    <?php $highlightDelay += 100; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section> -->

    <section id="danh-sach-khoa-hoc" class="py-12 md:py-20">
        <div class="mx-auto max-w-[1450px] px-4 sm:px-6">
            <div class="mb-10 flex flex-col gap-4 md:flex-row md:items-end md:justify-between" data-aos="fade-up">
                <div class="max-w-2xl">
                    <h2 class="mt-4 text-3xl md:text-4xl font-black text-slate-950"><?= e(t('courses.list_title')); ?></h2>
                    <p class="mt-3 text-slate-600 leading-relaxed">
                        <?= e(t('courses.list_copy')); ?>
                    </p>
                </div>

                <!-- <div class="flex flex-wrap gap-3">
                    <span class="rounded-full border border-red-200 bg-white px-4 py-2 text-sm font-bold text-rose-600 shadow-sm">Mầm non</span>
                    <span class="rounded-full border border-lime-200 bg-white px-4 py-2 text-sm font-bold text-emerald-600 shadow-sm">Tiểu học</span>
                    <span class="rounded-full border border-red-200 bg-white px-4 py-2 text-sm font-bold text-rose-600 shadow-sm">IELTS</span>
                    <span class="rounded-full border border-lime-200 bg-white px-4 py-2 text-sm font-bold text-emerald-600 shadow-sm">Doanh nghiệp</span>
                </div> -->
            </div>

            <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
                <?php $courseDelay = 0; ?>
                <?php foreach ($courses as $course): ?>
                    <?php $courseDescription = trim((string) ($course['short_desc'] ?? '')); ?>
                    <article class="course-card group overflow-hidden rounded-[2rem] border border-white bg-white/95 shadow-[0_14px_40px_rgba(15,23,42,0.08)] transition-all duration-500 hover:-translate-y-2 hover:shadow-[0_24px_60px_rgba(15,23,42,0.14)]" data-aos="fade-up" data-aos-delay="<?= $courseDelay; ?>" data-aos-duration="700">
                        <div class="morph-content">
                        <div class="relative h-56 overflow-hidden">
                            <img src="<?= e($course['image']); ?>" alt="<?= e($course['title']); ?>" class="course-img h-full w-full object-cover transition-transform duration-700">
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/45 via-slate-950/0 to-transparent"></div>
                            <div class="absolute left-4 top-4">
                                <span class="inline-flex items-center rounded-full bg-white/90 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-rose-600 shadow-sm backdrop-blur">
                                    <?= e($course['tag']); ?>
                                </span>
                            </div>
                            <div class="absolute bottom-4 left-4 right-4 flex items-center justify-between gap-3 text-white">
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-white/75"><?= e(t('courses.level')); ?></p>
                                    <p class="text-sm font-black"><?= e($course['level']); ?></p>
                                </div>
                                <div class="rounded-2xl bg-white/20 px-3 py-2 text-right backdrop-blur-sm">
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-white/70"><?= e(t('courses.duration')); ?></p>
                                    <p class="text-sm font-black"><?= e($course['duration']); ?></p>
                                </div>
                            </div>
                        </div>
                        </div>

                        <div class="p-6">
                            <h3 class="text-xl font-black leading-tight text-slate-950 transition-colors group-hover:text-rose-600"><?= e($course['title']); ?></h3>
                            <div class="mt-3 text-sm leading-relaxed text-slate-600 [&_a]:text-emerald-600 [&_a]:underline [&_a]:underline-offset-2 [&_blockquote]:border-l-4 [&_blockquote]:border-emerald-200 [&_blockquote]:pl-3 [&_blockquote]:italic [&_code]:rounded-lg [&_code]:bg-slate-100 [&_code]:px-1.5 [&_code]:py-0.5 [&_code]:font-mono [&_code]:text-[0.92em]">
                                <?= $courseDescription !== '' ? $renderBbcode($courseDescription) : e(t('courses.card_desc')); ?>
                            </div>

                            <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4">
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400"><?= e(t('courses.price_from')); ?></p>
                                    <p class="text-xl font-black text-slate-950"><?= e($course['price']); ?></p>
                                </div>
                                <a href="<?= e(page_url('course-detail', ['course' => $course['slug']])); ?>" class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-4 py-2 text-sm font-black text-emerald-600 transition-all hover:bg-emerald-600 hover:text-white">
                                    <?= e(t('public.common.view_detail')); ?>
                                    <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </article>
                    <?php $courseDelay += 100; ?>
                <?php endforeach; ?>
            </div>

            <?php if ($totalCoursePages > 1): ?>
                <div class="mt-10 flex flex-col gap-4 rounded-[2rem] border border-white bg-white/80 p-4 shadow-[0_12px_30px_rgba(15,23,42,0.05)] backdrop-blur-md sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm font-medium text-slate-600">
                        <?= e(t('courses.showing', ['shown' => count($courses), 'total' => number_format($courseTotal, 0, ',', '.')])); ?>
                    </p>

                    <nav class="flex flex-wrap items-center justify-center gap-2" aria-label="<?= e(t('courses.pagination_label')); ?>">
                        <a href="<?= e($buildCoursePageUrl(max(1, $currentCoursePage - 1))); ?>" class="inline-flex h-11 items-center justify-center rounded-full border border-slate-200 bg-white px-4 text-sm font-black text-slate-700 shadow-sm transition-all hover:-translate-y-0.5 hover:border-rose-300 hover:text-rose-600 <?= $currentCoursePage === 1 ? 'pointer-events-none opacity-40' : ''; ?>">
                            <i class="fa-solid fa-chevron-left"></i>
                        </a>

                        <?php
                        $pageStart = max(1, $currentCoursePage - 2);
                        $pageEnd = min($totalCoursePages, $currentCoursePage + 2);
                        if ($pageStart > 1) {
                            echo '<a href="' . e($buildCoursePageUrl(1)) . '" class="inline-flex h-11 min-w-11 items-center justify-center rounded-full border border-slate-200 bg-white px-4 text-sm font-black text-slate-700 shadow-sm transition-all hover:-translate-y-0.5 hover:border-rose-300 hover:text-rose-600">1</a>';
                            if ($pageStart > 2) {
                                echo '<span class="px-1 text-slate-400">...</span>';
                            }
                        }

                        for ($page = $pageStart; $page <= $pageEnd; $page++) {
                            $isCurrentPage = $page === $currentCoursePage;
                            $pageClasses = $isCurrentPage
                                ? 'border-rose-600 bg-rose-600 text-white shadow-md'
                                : 'border-slate-200 bg-white text-slate-700 shadow-sm hover:-translate-y-0.5 hover:border-rose-300 hover:text-rose-600';

                            echo '<a href="' . e($buildCoursePageUrl($page)) . '" class="inline-flex h-11 min-w-11 items-center justify-center rounded-full border px-4 text-sm font-black transition-all ' . $pageClasses . '"' . ($isCurrentPage ? ' aria-current="page"' : '') . '>' . $page . '</a>';
                        }

                        if ($pageEnd < $totalCoursePages) {
                            if ($pageEnd < $totalCoursePages - 1) {
                                echo '<span class="px-1 text-slate-400">...</span>';
                            }
                            echo '<a href="' . e($buildCoursePageUrl($totalCoursePages)) . '" class="inline-flex h-11 min-w-11 items-center justify-center rounded-full border border-slate-200 bg-white px-4 text-sm font-black text-slate-700 shadow-sm transition-all hover:-translate-y-0.5 hover:border-rose-300 hover:text-rose-600">' . $totalCoursePages . '</a>';
                        }
                        ?>

                        <a href="<?= e($buildCoursePageUrl(min($totalCoursePages, $currentCoursePage + 1))); ?>" class="inline-flex h-11 items-center justify-center rounded-full border border-slate-200 bg-white px-4 text-sm font-black text-slate-700 shadow-sm transition-all hover:-translate-y-0.5 hover:border-rose-300 hover:text-rose-600 <?= $currentCoursePage >= $totalCoursePages ? 'pointer-events-none opacity-40' : ''; ?>">
                            <i class="fa-solid fa-chevron-right"></i>
                        </a>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section id="dang-ky-tu-van" class="relative py-20 md:py-32 overflow-hidden">
        <!-- Background image hero banner -->
        <div class="absolute inset-0">
            <img src="/assets/images/consult2.jpg" alt="<?= e(t('courses.image_alt')); ?>" class="h-full w-full object-cover brightness-110 contrast-105 saturate-105">
            <!-- Lighter overlay so the banner stays bright and open -->
            <div class="absolute inset-0 bg-gradient-to-r from-slate-900/35 via-slate-900/12 to-transparent"></div>
            <!-- Vertical edge fade: soften the top and bottom edges like the side fade reference -->
            <div class="absolute inset-0 bg-gradient-to-b from-slate-50/15 via-transparent to-slate-50/15"></div>
            <!-- Extra softening at the very top and bottom so the image blends into the page background -->
            <div class="absolute inset-0" style="background: linear-gradient(to bottom, rgba(248,250,252,0.25) 0%, rgba(248,250,252,0) 10%, rgba(248,250,252,0) 90%, rgba(248,250,252,0.2) 100%);"></div>
            <!-- Subtle light reflection (white psychology - cleanliness, trust) -->
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(255,255,255,0.16),_transparent_45%)]"></div>
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
                    
                    <p class="mt-6 max-w-xl text-base md:text-lg leading-relaxed text-white/85">
                        <?= e(t('public.common.consultation_copy')); ?>
                    </p>

                    <div class="mt-10 grid gap-4 sm:grid-cols-3 max-w-lg">
                        <div class="rounded-[1.5rem] border border-white/18 bg-white/10 p-5 backdrop-blur-sm shadow-lg">
                            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-white/15 text-white">
                                <i class="fa-regular fa-clock text-sm"></i>
                            </div>
                            <p class="text-2xl font-black text-white">15'</p>
                            <p class="mt-1 text-[9px] font-bold uppercase tracking-widest text-white/70"><?= e(t('public.common.contact_now')); ?></p>
                        </div>
                        <div class="rounded-[1.5rem] border border-white/18 bg-white/10 p-5 backdrop-blur-sm shadow-lg">
                            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-white/15 text-white">
                                <i class="fa-solid fa-user-group text-sm"></i>
                            </div>
                            <p class="text-2xl font-black text-white">1:1</p>
                            <p class="mt-1 text-[9px] font-bold uppercase tracking-widest text-white/70"><?= e(t('public.common.expert')); ?></p>
                        </div>
                        <div class="rounded-[1.5rem] border border-white/18 bg-white/10 p-5 backdrop-blur-sm shadow-lg">
                            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-white/15 text-white">
                                <i class="fa-solid fa-wand-magic-sparkles text-sm"></i>
                            </div>
                            <p class="text-2xl font-black text-white">100%</p>
                            <p class="mt-1 text-[9px] font-bold uppercase tracking-widest text-white/70"><?= e(t('public.common.personalized')); ?></p>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-[2.75rem] border border-white/32 bg-slate-950/18 p-8 md:p-10 shadow-[0_28px_80px_rgba(15,23,42,0.34)] backdrop-blur-2xl" data-aos="fade-left" data-aos-duration="700" data-aos-delay="100">

                <!-- Right side: Form panel overlay - Psychology: White (trust/cleanliness) + Rose (action) + Emerald (growth) -->
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
                            <span class="ml-2 text-transparent bg-clip-text bg-gradient-to-r from-rose-400 to-rose-600" style="text-shadow: 
                                2px 2px 0 rgba(244, 63, 94, 0.2),
                                4px 4px 0 rgba(244, 63, 94, 0.15),
                                0 6px 12px rgba(244, 63, 94, 0.2);
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
                        <input type="hidden" name="redirect_to" value="<?= e(page_url('courses') . '#dang-ky-tu-van'); ?>">
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

    <?php $notifyShowTestButtons = false; require __DIR__ . '/../notification/notification.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof AOS !== 'undefined') {
                AOS.init({
                    duration: 650,
                    once: true,
                    offset: 0,
                    easing: 'ease-out-cubic'
                });
            }
        });

        <?php if (!empty($courseLeadSuccess)): ?>
        if (typeof showNotify === 'function') {
            showNotify('success', <?= json_encode($courseLeadSuccess, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>);
        }
        <?php endif; ?>

        <?php if (!empty($courseLeadError)): ?>
        if (typeof showNotify === 'function') {
            showNotify('error', <?= json_encode($courseLeadError, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>);
        }
        <?php endif; ?>
    </script>

    <?php include __DIR__ . '/../partials/social_contact.php'; ?>
</main>
