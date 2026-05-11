<?php
$academicModel = new AcademicModel();
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
        'tag' => 'Khóa học',
        'short_desc' => (string) ($row['description'] ?? 'Chương trình học được xây dựng theo lộ trình rõ ràng, phù hợp cho từng học viên.'),
        'price' => $priceValue,
        'original_price' => $priceValue,
        'duration' => ((int) ($row['total_sessions'] ?? 0)) . ' buổi',
        'level' => 'Đang cập nhật',
        'lessons_count' => (int) ($row['total_sessions'] ?? 0),
        'rating' => 5.0,
        'students' => 0,
        'image' => $image,
        'instructor' => [
            'name' => 'Đội ngũ giáo viên',
            'role' => 'Academic Team',
        ],
        'benefits' => [],
        'outline' => [],
        'suitable_for' => [],
        'outcomes' => [],
    ];
}

$stats = [
    ['value' => number_format($courseTotal, 0, ',', '.') . '+', 'label' => 'Chương trình học'],
    ['value' => '100+', 'label' => 'Học viên đang theo học'],
    ['value' => '99%', 'label' => 'Hài lòng sau khóa học'],
    ['value' => '100%', 'label' => 'Lộ trình được cá nhân hóa'],
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

<main class="bg-[#fbfcfa] text-slate-800 overflow-hidden">
    <section class="relative overflow-hidden pt-16 pb-14 md:pt-24 md:pb-20">
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute -top-20 right-[-8%] h-72 w-72 rounded-full bg-red-200/35 blur-3xl"></div>
            <div class="absolute top-24 left-[-10%] h-80 w-80 rounded-full bg-lime-200/40 blur-3xl"></div>
            <div class="absolute bottom-[-12%] right-1/3 h-64 w-64 rounded-full bg-emerald-200/25 blur-3xl"></div>
        </div>

        <div class="mx-auto max-w-[1450px] px-4 sm:px-6 relative z-10">
            <div class="grid gap-8 lg:grid-cols-[1.1fr_0.9fr] items-center">
                <div class="space-y-7 rounded-[2.5rem] p-2 md:p-3 bg-white/20 backdrop-blur-sm border border-white/40 shadow-[0_12px_40px_rgba(15,23,42,0.03)]" data-aos="fade-right" data-aos-duration="700">
                    <div class="morph-content space-y-7 rounded-[2.2rem] bg-white/55 p-5 md:p-6 backdrop-blur-md border border-white/60 shadow-[0_12px_40px_rgba(15,23,42,0.04)]">
                    <span class="inline-flex items-center gap-2 rounded-full border border-red-200 bg-white/80 px-4 py-2 text-xs font-black uppercase tracking-[0.2em] text-rose-600 shadow-sm backdrop-blur">
                        <span class="h-2 w-2 rounded-full bg-lime-400"></span>
                        Chương trình học
                    </span>
                    <div class="space-y-5 max-w-3xl">
                        <h1 class="text-4xl md:text-5xl xl:text-6xl font-black leading-[1.15] md:leading-[1.1] xl:leading-[1.08] text-slate-950">
                            Khóa học phù hợp cho <br>
                            <span class="inline-block text-transparent bg-clip-text bg-gradient-to-r from-red-600 via-rose-500 to-lime-600">mọi độ tuổi và mục tiêu</span>
                        </h1>
                        <p class="text-base md:text-lg text-slate-600 leading-relaxed max-w-2xl font-medium">
                            Từ mầm non, tiểu học đến IELTS và tiếng Anh doanh nghiệp, mỗi chương trình đều được thiết kế theo lộ trình rõ ràng, dễ theo dõi và có thể cá nhân hóa theo năng lực học viên.
                        </p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        <?php $statDelay = 0; ?>
                        <?php foreach ($stats as $stat): ?>
                            <div class="rounded-3xl border border-white bg-white/80 p-5 shadow-[0_12px_30px_rgba(15,23,42,0.05)] backdrop-blur-md" data-aos="fade-up" data-aos-delay="<?= $statDelay; ?>" data-aos-duration="600">
                                <div class="text-2xl md:text-3xl font-black text-slate-950"><?= e($stat['value']); ?></div>
                                <div class="mt-1 text-xs font-bold uppercase tracking-wider text-slate-500"><?= e($stat['label']); ?></div>
                            </div>
                            <?php $statDelay += 100; ?>
                        <?php endforeach; ?>
                    </div>

                    <div class="flex flex-wrap gap-3 pt-2">
                        <a href="#danh-sach-khoa-hoc" class="inline-flex items-center gap-3 rounded-full bg-rose-600 px-7 py-3.5 text-sm font-black text-white shadow-lg shadow-rose-600/25 transition-transform hover:-translate-y-1">
                            Xem danh sách khóa học
                            <i class="fa-solid fa-arrow-down"></i>
                        </a>
                        <a href="#dang-ky-tu-van" class="inline-flex items-center gap-3 rounded-full border border-lime-300 bg-white/80 px-7 py-3.5 text-sm font-black text-emerald-700 shadow-sm transition-transform hover:-translate-y-1">
                            Đăng ký tư vấn
                            <i class="fa-solid fa-calendar-check"></i>
                        </a>
                    </div>
                    </div>
                </div>

                <div class="relative" data-aos="fade-left" data-aos-duration="700">
                    <div class="absolute inset-0 translate-x-6 translate-y-6 rounded-[2.5rem] bg-gradient-to-br from-red-200/50 to-lime-200/50 blur-2xl"></div>
                    <div class="relative overflow-hidden rounded-[2.5rem] border border-white bg-white/80 p-4 shadow-[0_24px_80px_rgba(15,23,42,0.12)] backdrop-blur-md" data-aos="zoom-in" data-aos-duration="800">
                        <div class="morph-content">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="rounded-[2rem] bg-gradient-to-br from-red-500 to-rose-500 p-6 text-white shadow-lg transition-transform duration-300 hover:-translate-y-1" data-aos="zoom-in" data-aos-delay="100">
                                <div class="morph-content">
                                <div class="mb-10 flex items-center justify-between">
                                    <span class="rounded-full bg-white/20 px-3 py-1 text-[10px] font-black uppercase tracking-widest">Ưu tiên</span>
                                    <i class="fa-solid fa-star text-white/70"></i>
                                </div>
                                <h2 class="text-2xl font-black leading-tight">Lộ trình rõ ràng, đầu ra dễ kiểm soát.</h2>
                                </div>
                            </div>
                            
                            <div class="rounded-[2rem] bg-gradient-to-br from-lime-200 to-lime-100 p-6 text-slate-900 shadow-lg transition-transform duration-300 hover:-translate-y-1" data-aos="zoom-in" data-aos-delay="200">
                                <div class="morph-content">
                                <div class="mb-8 flex items-center gap-3">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-emerald-600 shadow-sm">
                                        <i class="fa-solid fa-seedling text-xl"></i>
                                    </div>
                                    <div>
                                        <p class="text-[11px] font-bold uppercase tracking-widest text-emerald-700">Định hướng cốt lõi</p>
                                        <p class="text-sm font-black text-slate-900">Ươm mầm & Phát triển</p>
                                    </div>
                                </div>
                                <p class="text-sm leading-relaxed text-slate-700 font-medium">
                                    Chú trọng xây dựng nền tảng tư duy vững chắc, kết hợp kiến thức chuyên sâu và kỹ năng thực chiến giúp học viên bứt phá tiềm năng.
                                </p>
                                </div>
                            </div>

                        </div>

                        <div class="mt-4 rounded-[2rem] course-gradient border border-white p-5" data-aos="fade-up" data-aos-delay="250">
                            <div class="morph-content">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">Đăng ký tư vấn nhanh</p>
                                    <h3 class="mt-1 text-lg font-black text-slate-950">Nhanh chóng chọn chương trình phù hợp</h3>
                                </div>
                                <a href="<?= e(page_url('register-consultation')); ?>" class="hidden sm:inline-flex h-12 w-12 items-center justify-center rounded-full bg-slate-900 text-white shadow-md transition-all duration-300 hover:-translate-y-0.5 hover:bg-rose-600 hover:shadow-lg focus:outline-none focus:ring-4 focus:ring-rose-300" aria-label="Đăng ký tư vấn">
                                    <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            </div>
                            </div>
                        </div>
                        </div>
                    </div>
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
                    <h2 class="mt-4 text-3xl md:text-4xl font-black text-slate-950">Nhiều khóa học, chia theo từng nhu cầu học tập</h2>
                    <p class="mt-3 text-slate-600 leading-relaxed">
                        Chọn một nhóm khóa học để xây nền, tăng tốc IELTS hoặc học giao tiếp ứng dụng cho học sinh và người đi làm.
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
                                    <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-white/75">Mức học</p>
                                    <p class="text-sm font-black"><?= e($course['level']); ?></p>
                                </div>
                                <div class="rounded-2xl bg-white/20 px-3 py-2 text-right backdrop-blur-sm">
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-white/70">Thời lượng</p>
                                    <p class="text-sm font-black"><?= e($course['duration']); ?></p>
                                </div>
                            </div>
                        </div>
                        </div>

                        <div class="p-6">
                            <h3 class="text-xl font-black leading-tight text-slate-950 transition-colors group-hover:text-rose-600"><?= e($course['title']); ?></h3>
                            <p class="mt-3 text-sm leading-relaxed text-slate-600">
                                Giáo trình cô đọng, thực hành nhiều và có kiểm tra định kỳ để theo dõi tiến bộ rõ ràng.
                            </p>

                            <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4">
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Học phí từ</p>
                                    <p class="text-xl font-black text-slate-950"><?= e($course['price']); ?></p>
                                </div>
                                <a href="<?= e(page_url('course-detail', ['course' => $course['slug']])); ?>" class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-4 py-2 text-sm font-black text-emerald-600 transition-all hover:bg-emerald-600 hover:text-white">
                                    Xem chi tiết
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
                        Đang hiển thị <?= count($courses); ?> / <?= number_format($courseTotal, 0, ',', '.'); ?> khóa học
                    </p>

                    <nav class="flex flex-wrap items-center justify-center gap-2" aria-label="Phân trang khóa học">
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
            <img src="/assets/images/consult2.jpg" alt="Sinh viên học tập" class="h-full w-full object-cover brightness-110 contrast-105 saturate-105">
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
    </script>

    <?php include __DIR__ . '/../partials/social_contact.php'; ?>
</main>
