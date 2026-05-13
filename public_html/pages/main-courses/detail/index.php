<?php
$courseLeadSuccess = get_flash('home_success');
$courseLeadError = get_flash('home_error');
$academicModel = new AcademicModel();
$courseTotal = $academicModel->countCourses();
$courseRows = $courseTotal > 0
    ? $academicModel->listCoursesPage(1, $courseTotal)
    : [];

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

$dbCourses = [];
foreach ($courseRows as $row) {
    $courseName = trim((string) ($row['course_name'] ?? ''));
    if ($courseName === '') {
        continue;
    }

    $slug = $buildCourseSlug($courseName);
    $priceValue = number_format((float) ($row['base_price'] ?? 0), 0, ',', '.') . 'đ';

    $dbCourses[$slug] = [
        'slug' => $slug,
        'title' => $courseName,
        'tag' => '',
        'short_desc' => (string) ($row['description'] ?? ''),
        'price' => $priceValue,
        'original_price' => '',
        'duration' => ((int) ($row['total_sessions'] ?? 0)) . ' buổi',
        'level' => '',
        'lessons_count' => (int) ($row['total_sessions'] ?? 0),
        'rating' => 0.0,
        'students' => 0,
        'image' => $resolveCourseImage((string) ($row['image_thumbnail'] ?? '')),
        'instructor' => [
            'name' => '',
            'role' => '',
        ],
        'benefits' => [],
        'outline' => [],
        'suitable_for' => [],
        'outcomes' => [],
    ];
}

$requestedSlug = strtolower(trim((string) ($_GET['course'] ?? '')));
if ($requestedSlug === '') {
    $requestedSlug = array_key_first($dbCourses) ?: '';
}

if ($requestedSlug !== '' && isset($dbCourses[$requestedSlug])) {
    $course = $dbCourses[$requestedSlug];
} else {
    http_response_code(404);
    $requestedSlug = array_key_first($dbCourses) ?: '';
    if ($requestedSlug === '') {
        echo '404 Not Found';
        return;
    }
    $course = $dbCourses[$requestedSlug];
}

$relatedCourses = array_values(array_filter($dbCourses, static fn(array $item): bool => ($item['slug'] ?? '') !== ($course['slug'] ?? '')));
$relatedCourses = array_slice($relatedCourses, 0, 4);
?>

<link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<style>
    .course-detail-bg {
        background:
            radial-gradient(circle at top left, rgba(225, 29, 72, 0.16), transparent 28%),
            radial-gradient(circle at top right, rgba(163, 230, 53, 0.18), transparent 26%),
            linear-gradient(180deg, #fbfcfa 0%, #f8fafc 100%);
    }

    .detail-card {
        box-shadow: 0 24px 80px rgba(15, 23, 42, 0.08);
    }

    .detail-chip {
        background: linear-gradient(135deg, rgba(225, 29, 72, 0.12), rgba(163, 230, 53, 0.12));
    }

    @media (prefers-reduced-motion: reduce) {
        .detail-card,
        .detail-card * {
            transition: none !important;
            animation: none !important;
            scroll-behavior: auto !important;
        }
    }
</style>

<main class="course-detail-bg overflow-hidden text-slate-800">
    <section class="relative overflow-hidden pt-16 pb-14 md:pt-24 md:pb-20">
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute -top-20 right-[-10%] h-72 w-72 rounded-full bg-red-200/40 blur-3xl"></div>
            <div class="absolute top-16 left-[-8%] h-80 w-80 rounded-full bg-lime-200/40 blur-3xl"></div>
            <div class="absolute bottom-[-12%] right-1/3 h-64 w-64 rounded-full bg-emerald-200/25 blur-3xl"></div>
        </div>

        <div class="mx-auto max-w-[1450px] px-4 sm:px-6 relative z-10">
            <div class="grid gap-8 lg:grid-cols-[1.15fr_0.85fr] items-center">
                <div class="space-y-7" data-aos="fade-right" data-aos-duration="700">
                    <div class="inline-flex items-center gap-2 rounded-full border border-red-200 bg-white/80 px-4 py-2 text-xs font-black uppercase tracking-[0.2em] text-rose-600 shadow-sm backdrop-blur">
                        <span class="h-2 w-2 rounded-full bg-lime-400"></span>
                        Khóa học chi tiết
                    </div>

                    <div class="space-y-5 max-w-3xl">
                        <nav class="flex flex-wrap items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-slate-500">
                            <a href="/" class="hover:text-rose-600 transition-colors">Trang chủ</a>
                            <span>/</span>
                            <a href="<?= e(page_url('courses')); ?>" class="hover:text-rose-600 transition-colors">Chương trình học</a>
                            <span>/</span>
                            <span class="text-slate-800"><?= e($course['title']); ?></span>
                        </nav>

                        <h1 class="text-4xl md:text-5xl xl:text-6xl font-black leading-[1.04] text-slate-950">
                            <?= e($course['title']); ?>
                        </h1>

                        <p class="text-base md:text-lg leading-relaxed text-slate-600 max-w-2xl font-medium">
                            <?= e($course['short_desc']); ?>
                        </p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        <div class="rounded-3xl border border-white bg-white/85 p-5 shadow-[0_12px_30px_rgba(15,23,42,0.05)] backdrop-blur-md" data-aos="fade-up" data-aos-delay="0">
                            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">Học phí</p>
                            <p class="mt-2 text-2xl font-black text-slate-950"><?= e($course['price']); ?></p>
                            <p class="mt-1 text-xs text-slate-400 line-through"><?= e($course['original_price']); ?></p>
                        </div>
                        <div class="rounded-3xl border border-white bg-white/85 p-5 shadow-[0_12px_30px_rgba(15,23,42,0.05)] backdrop-blur-md" data-aos="fade-up" data-aos-delay="100">
                            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">Cấp độ</p>
                            <p class="mt-2 text-2xl font-black text-slate-950"><?= e($course['level']); ?></p>
                        </div>
                        <div class="rounded-3xl border border-white bg-white/85 p-5 shadow-[0_12px_30px_rgba(15,23,42,0.05)] backdrop-blur-md" data-aos="fade-up" data-aos-delay="200">
                            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">Thời lượng</p>
                            <p class="mt-2 text-2xl font-black text-slate-950"><?= e($course['duration']); ?></p>
                        </div>
                        <div class="rounded-3xl border border-white bg-white/85 p-5 shadow-[0_12px_30px_rgba(15,23,42,0.05)] backdrop-blur-md" data-aos="fade-up" data-aos-delay="300">
                            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">Bài học</p>
                            <p class="mt-2 text-2xl font-black text-slate-950"><?= e((string) $course['lessons_count']); ?> buổi</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3" data-aos="fade-up" data-aos-delay="350">
                        <a href="#dang-ky-tu-van" class="inline-flex items-center gap-3 rounded-full bg-rose-600 px-7 py-3.5 text-sm font-black text-white shadow-lg shadow-rose-600/25 transition-transform hover:-translate-y-1">
                            Đăng ký tư vấn
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                        <a href="<?= e(page_url('courses')); ?>" class="inline-flex items-center gap-3 rounded-full border border-lime-300 bg-white/85 px-7 py-3.5 text-sm font-black text-emerald-700 shadow-sm transition-transform hover:-translate-y-1">
                            Quay lại danh sách
                            <i class="fa-solid fa-arrow-left"></i>
                        </a>
                    </div>
                </div>

                <div class="relative" data-aos="fade-left" data-aos-duration="800">
                    <div class="absolute inset-0 translate-x-6 translate-y-6 rounded-[2.5rem] bg-gradient-to-br from-red-200/50 to-lime-200/50 blur-2xl"></div>
                    <div class="detail-card relative overflow-hidden rounded-[2.5rem] border border-white bg-white/85 p-4 backdrop-blur-md">
                        <div class="relative h-[380px] overflow-hidden rounded-[2rem]">
                            <img src="<?= e($course['image']); ?>" alt="<?= e($course['title']); ?>" class="h-full w-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/70 via-slate-950/20 to-transparent"></div>
                            <div class="absolute left-5 top-5 flex flex-wrap gap-2">
                                <span class="detail-chip rounded-full px-4 py-2 text-[10px] font-black uppercase tracking-[0.22em] text-rose-600 backdrop-blur">
                                    <?= e($course['tag']); ?>
                                </span>
                                <span class="rounded-full bg-white/90 px-4 py-2 text-[10px] font-black uppercase tracking-[0.22em] text-slate-700 backdrop-blur">
                                    <?= e($course['students']); ?> học viên
                                </span>
                            </div>
                            <div class="absolute inset-x-0 bottom-0 p-5 text-white">
                                <div class="flex items-center gap-3 text-sm font-bold text-amber-300">
                                    <i class="fa-solid fa-star"></i>
                                    <span><?= e((string) $course['rating']); ?>/5.0</span>
                                    <span class="text-white/60">•</span>
                                    <span><?= e($course['instructor']['name']); ?></span>
                                </div>
                                <p class="mt-2 max-w-xl text-sm leading-relaxed text-white/85">
                                    <?= e($course['instructor']['role']); ?>
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-3 p-5 sm:grid-cols-3">
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Đối tượng</p>
                                <p class="mt-1 text-sm font-black text-slate-900"><?= e($course['level']); ?></p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Nhịp học</p>
                                <p class="mt-1 text-sm font-black text-slate-900"><?= e($course['duration']); ?></p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Lộ trình</p>
                                <p class="mt-1 text-sm font-black text-slate-900"><?= e((string) count($course['outline'])); ?> giai đoạn</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-10 md:py-14">
        <div class="mx-auto max-w-[1450px] px-4 sm:px-6">
            <div class="grid gap-4 md:grid-cols-3">
                <?php foreach ($course['benefits'] as $index => $benefit): ?>
                    <div class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_12px_30px_rgba(15,23,42,0.05)] backdrop-blur-md" data-aos="fade-up" data-aos-delay="<?= $index * 100; ?>" data-aos-duration="600">
                        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-red-500 to-lime-400 text-white shadow-sm">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <h3 class="text-lg font-black text-slate-950">Giá trị nổi bật</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600"><?= e($benefit); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="py-8 md:py-12">
        <div class="mx-auto max-w-[1450px] px-4 sm:px-6">
            <div class="grid gap-8 lg:grid-cols-[1.05fr_0.95fr] items-start">
                <div class="space-y-8">
                    <div class="detail-card rounded-[2.5rem] border border-white bg-white p-8 md:p-10" data-aos="fade-up">
                        <div class="flex items-center gap-3">
                            <span class="h-8 w-2 rounded-full bg-rose-500"></span>
                            <h2 class="text-2xl md:text-3xl font-black text-slate-950">Lộ trình học tập</h2>
                        </div>

                        <div class="mt-8 space-y-4">
                            <?php foreach ($course['outline'] as $stepIndex => $step): ?>
                                <div class="group rounded-2xl border border-slate-100 bg-slate-50/80 p-5 transition-all hover:border-lime-300 hover:bg-white">
                                    <div class="flex items-start gap-4">
                                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white font-black text-slate-400 shadow-sm transition-colors group-hover:bg-lime-50 group-hover:text-lime-600">
                                            <?= str_pad((string) ($stepIndex + 1), 2, '0', STR_PAD_LEFT); ?>
                                        </span>
                                        <div class="min-w-0 flex-1">
                                            <h3 class="text-base font-black text-slate-950"><?= e($step['title']); ?></h3>
                                            <p class="mt-1 text-sm leading-relaxed text-slate-600"><?= e($step['desc']); ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="detail-card rounded-[2.5rem] border border-white bg-white p-8 md:p-10" data-aos="fade-up">
                        <div class="flex items-center gap-3">
                            <span class="h-8 w-2 rounded-full bg-lime-500"></span>
                            <h2 class="text-2xl md:text-3xl font-black text-slate-950">Kết quả học tập</h2>
                        </div>

                        <div class="mt-6 grid gap-4 md:grid-cols-2">
                            <?php foreach ($course['outcomes'] as $outcome): ?>
                                <div class="rounded-2xl border border-slate-100 bg-gradient-to-br from-white to-slate-50 p-5">
                                    <p class="text-sm font-bold text-slate-700"><?= e($outcome); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="space-y-8">
                    <div class="detail-card sticky top-28 rounded-[2.5rem] border border-white bg-white p-8 md:p-10" data-aos="fade-left">
                        <div class="flex items-center gap-3">
                            <span class="h-8 w-2 rounded-full bg-emerald-500"></span>
                            <h2 class="text-2xl font-black text-slate-950">Tư vấn nhanh</h2>
                        </div>

                        <div class="mt-6 flex items-center gap-4 rounded-[2rem] bg-slate-50 p-4">
                            <img src="https://i.pravatar.cc/160?u=<?= e($course['slug']); ?>" alt="<?= e($course['instructor']['name']); ?>" class="h-16 w-16 rounded-2xl object-cover ring-4 ring-white">
                            <div>
                                <p class="text-lg font-black text-slate-950"><?= e($course['instructor']['name']); ?></p>
                                <p class="text-sm text-slate-500"><?= e($course['instructor']['role']); ?></p>
                            </div>
                        </div>

                        <div class="mt-6 space-y-4 text-sm text-slate-600">
                            <p>Khóa học này phù hợp cho:</p>
                            <div class="flex flex-wrap gap-2">
                                <?php foreach ($course['suitable_for'] as $item): ?>
                                    <span class="rounded-full border border-slate-200 bg-white px-4 py-2 font-bold text-slate-700 shadow-sm">
                                        <?= e($item); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="mt-8 rounded-[2rem] bg-gradient-to-br from-slate-950 to-slate-800 p-6 text-white">
                            <p class="text-xs font-black uppercase tracking-[0.2em] text-white/60">Học phí</p>
                            <div class="mt-2 flex items-end gap-3">
                                <span class="text-3xl font-black"><?= e($course['price']); ?></span>
                                <span class="text-sm text-white/45 line-through"><?= e($course['original_price']); ?></span>
                            </div>
                            <p class="mt-3 text-sm leading-relaxed text-white/70">Liên hệ để nhận tư vấn lộ trình, lịch học và ưu đãi hiện hành.</p>
                            <a href="#dang-ky-tu-van" class="mt-5 inline-flex w-full items-center justify-center gap-3 rounded-full bg-lime-400 px-6 py-3.5 font-black text-slate-950 transition-transform hover:-translate-y-1">
                                Nhận tư vấn ngay
                                <i class="fa-solid fa-paper-plane"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- <section class="py-12 md:py-20">
        <div class="mx-auto max-w-[1450px] px-4 sm:px-6">
            <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between" data-aos="fade-up">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.22em] text-rose-600">Khám phá thêm</p>
                    <h2 class="mt-3 text-3xl md:text-4xl font-black text-slate-950">Các khóa học khác</h2>
                </div>
                <a href="<?= e(page_url('courses')); ?>" class="inline-flex items-center gap-3 rounded-full border border-lime-300 bg-white px-5 py-3 text-sm font-black text-emerald-700 shadow-sm transition-transform hover:-translate-y-1">
                    Xem toàn bộ danh sách
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
                <?php foreach ($relatedCourses as $index => $relatedCourse): ?>
                    <article class="detail-card overflow-hidden rounded-[2rem] border border-white bg-white/95 shadow-[0_14px_40px_rgba(15,23,42,0.08)]" data-aos="fade-up" data-aos-delay="<?= $index * 100; ?>">
                        <div class="relative h-48 overflow-hidden">
                            <img src="<?= e($relatedCourse['image']); ?>" alt="<?= e($relatedCourse['title']); ?>" class="h-full w-full object-cover transition-transform duration-700 hover:scale-105">
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/45 to-transparent"></div>
                            <div class="absolute left-4 top-4 rounded-full bg-white/90 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-rose-600">
                                <?= e($relatedCourse['tag']); ?>
                            </div>
                        </div>
                        <div class="p-5">
                            <h3 class="text-lg font-black leading-tight text-slate-950"><?= e($relatedCourse['title']); ?></h3>
                            <p class="mt-2 text-sm leading-relaxed text-slate-600"><?= e($relatedCourse['short_desc']); ?></p>
                            <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4">
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Học phí</p>
                                    <p class="text-lg font-black text-slate-950"><?= e($relatedCourse['price']); ?></p>
                                </div>
                                <a href="<?= e(page_url('course-detail', ['course' => $relatedCourse['slug']])); ?>" class="inline-flex items-center gap-2 rounded-full bg-slate-950 px-4 py-2 text-sm font-black text-white transition-transform hover:-translate-y-1 hover:bg-emerald-600">
                                    Chi tiết
                                    <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section> -->

    <section id="dang-ky-tu-van" class="relative py-20 md:py-32 overflow-hidden">
        <!-- Background image hero banner -->
        <div class="absolute inset-0">
            <img src="/assets/images/consult3.jpg" alt="Sinh viên học tập" class="h-full w-full object-cover brightness-110 contrast-105 saturate-105">
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
                
                <div class="relative overflow-hidden rounded-[2.75rem] border border-white/32 bg-slate-950/18 p-8 md:p-10 shadow-[0_28px_80px_rgba(15,23,42,0.34)] backdrop-blur-2xl" data-aos="fade-left" data-aos-duration="700" data-aos-delay="100">

                <!-- Right side: Form panel overlay - Psychology: White (trust/cleanliness) + Rose (action) + Emerald (growth) -->
                <!-- <div class="relative overflow-hidden rounded-[2.75rem] border border-white/20 bg-transparent p-8 md:p-10 shadow-none backdrop-blur-none" data-aos="fade-left" data-aos-duration="700" data-aos-delay="100"> -->
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

                    <form action="/api/index.php?resource=leads&method=submit" method="POST" class="relative z-10 grid gap-6 sm:grid-cols-2">
                        <?= csrf_input(); ?>
                        <input type="hidden" name="redirect_to" value="<?= e(page_url('course-detail', ['course' => (string) ($course['slug'] ?? '')]) . '#dang-ky-tu-van'); ?>">
                        <!-- Name field: Rose psychology (action/engagement) -->
                        <div class="sm:col-span-2 group">
                            <label class="mb-3 flex items-center gap-2 text-[11px] font-black uppercase tracking-[0.16em] text-white group-focus-within:text-rose-300 transition-colors">
                                <i class="fa-solid fa-user text-rose-500"></i>
                                Họ và tên <span class="text-rose-500 text-base">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-5 top-1/2 -translate-y-1/2 text-rose-400 group-focus-within:text-rose-500 transition-colors"><i class="fa-regular fa-user"></i></span>
                                <input type="text" name="full_name" required placeholder="Nhập họ và tên của bạn" class="w-full rounded-2xl border border-slate-200 bg-white py-4 pl-14 pr-5 text-sm font-bold text-slate-900 shadow-sm outline-none transition-all placeholder:text-slate-400 placeholder:font-medium focus:border-rose-400 focus:ring-4 focus:ring-rose-500/15 focus:shadow-lg focus:shadow-rose-500/10">
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
                                <input type="tel" name="phone" required placeholder="09xx xxx xxx" class="w-full rounded-2xl border border-slate-200 bg-white py-4 pl-14 pr-5 text-sm font-bold text-slate-900 shadow-sm outline-none transition-all placeholder:text-slate-400 placeholder:font-medium focus:border-rose-400 focus:ring-4 focus:ring-rose-500/15 focus:shadow-lg focus:shadow-rose-500/10">
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
                                <input type="date" name="dob" class="w-full rounded-2xl border border-slate-200 bg-white py-4 pl-14 pr-5 text-sm font-bold text-slate-900 shadow-sm outline-none transition-all focus:border-emerald-400 focus:ring-4 focus:ring-emerald-500/15 focus:shadow-lg focus:shadow-emerald-500/10">
                            </div>
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

            // (no custom date placeholder) 
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
</main>
