<?php
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
        'image' => '',
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

    <section class="py-12 md:py-20">
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
    </section>

    <section id="dang-ky-tu-van" class="py-14 md:py-20">
        <div class="mx-auto max-w-[1450px] px-4 sm:px-6">
            <div class="grid gap-8 lg:grid-cols-[0.95fr_1.05fr] items-stretch">
                <div class="rounded-[2.5rem] bg-gradient-to-br from-red-600 to-rose-500 p-8 md:p-10 text-white shadow-[0_24px_60px_rgba(225,29,72,0.25)]" data-aos="fade-right" data-aos-duration="700">
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/15 px-4 py-2 text-xs font-black uppercase tracking-[0.2em]">
                        <span class="h-2 w-2 rounded-full bg-lime-300"></span>
                        Tư vấn nhanh
                    </span>
                    <h2 class="mt-6 text-3xl md:text-4xl font-black leading-tight">Nhận gợi ý lộ trình phù hợp với trình độ hiện tại</h2>
                    <p class="mt-4 max-w-xl text-rose-50/95 leading-relaxed">
                        Gửi thông tin để trung tâm đề xuất lịch học, cấp độ và nhịp độ phù hợp nhất cho học viên.
                    </p>

                    <div class="mt-8 grid gap-4 sm:grid-cols-3">
                        <div class="rounded-3xl bg-white/15 p-4 backdrop-blur-sm">
                            <p class="text-2xl font-black">15'</p>
                            <p class="mt-1 text-xs font-bold uppercase tracking-wider text-white/80">Phản hồi</p>
                        </div>
                        <div class="rounded-3xl bg-white/15 p-4 backdrop-blur-sm">
                            <p class="text-2xl font-black">1:1</p>
                            <p class="mt-1 text-xs font-bold uppercase tracking-wider text-white/80">Tư vấn</p>
                        </div>
                        <div class="rounded-3xl bg-white/15 p-4 backdrop-blur-sm">
                            <p class="text-2xl font-black">100%</p>
                            <p class="mt-1 text-xs font-bold uppercase tracking-wider text-white/80">Cá nhân hóa</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-[2.5rem] border border-white bg-white p-8 md:p-10 shadow-[0_24px_60px_rgba(15,23,42,0.08)]" data-aos="fade-left" data-aos-duration="700" data-aos-delay="100">
                    <div class="mb-8">
                        <h3 class="text-2xl font-black text-slate-950">Đăng ký nhận tư vấn</h3>
                        <p class="mt-2 text-slate-600">Để lại thông tin, trung tâm sẽ liên hệ sớm nhất.</p>
                    </div>

                    <form class="grid gap-4 sm:grid-cols-2">
                        <input type="text" placeholder="Họ và tên" class="sm:col-span-2 rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-slate-900 outline-none transition focus:border-rose-300 focus:bg-white">
                        <input type="tel" placeholder="Số điện thoại" class="rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-slate-900 outline-none transition focus:border-rose-300 focus:bg-white">
                        <input type="email" placeholder="Email" class="rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-slate-900 outline-none transition focus:border-rose-300 focus:bg-white">
                        <select class="sm:col-span-2 rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-slate-900 outline-none transition focus:border-rose-300 focus:bg-white">
                            <option><?= e($course['title']); ?></option>
                            <?php foreach ($dbCourses as $item): ?>
                                <option><?= e($item['title']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <textarea rows="4" placeholder="Ghi chú thêm" class="sm:col-span-2 rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-slate-900 outline-none transition focus:border-rose-300 focus:bg-white"></textarea>
                        <button type="submit" class="sm:col-span-2 inline-flex items-center justify-center gap-3 rounded-full bg-slate-950 px-8 py-4 font-black text-white transition-all hover:-translate-y-1 hover:bg-emerald-600">
                            Gửi yêu cầu tư vấn
                            <i class="fa-solid fa-paper-plane"></i>
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
</main>
