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
    ['value' => '12+', 'label' => 'Chương trình học'],
    ['value' => '1.500+', 'label' => 'Học viên đang theo học'],
    ['value' => '98%', 'label' => 'Hài lòng sau khóa học'],
    ['value' => '100%', 'label' => 'Lộ trình được cá nhân hóa'],
];

$highlights = [
    ['icon' => 'fa-solid fa-users', 'title' => 'Lớp học nhỏ', 'desc' => 'Tối ưu tương tác, giáo viên theo sát từng học viên.'],
    ['icon' => 'fa-solid fa-chalkboard-user', 'title' => 'Phương pháp thực hành', 'desc' => 'Giảm lý thuyết khô cứng, tăng luyện tập và phản xạ.'],
    ['icon' => 'fa-solid fa-medal', 'title' => 'Lộ trình rõ ràng', 'desc' => 'Có mục tiêu đầu ra và mốc tiến độ từng giai đoạn.'],
];
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
                        <h1 class="text-4xl md:text-5xl xl:text-6xl font-black leading-[1.05] text-slate-950">
                            Khóa học phù hợp cho <br>
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-red-600 via-rose-500 to-lime-600">mọi độ tuổi và mục tiêu</span>
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
                                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">Tư vấn nhanh</p>
                                    <h3 class="mt-1 text-lg font-black text-slate-950">Chọn khóa học phù hợp trong 1 phút</h3>
                                </div>
                                <span class="hidden sm:inline-flex h-12 w-12 items-center justify-center rounded-full bg-slate-900 text-white shadow-md">
                                    <i class="fa-solid fa-arrow-right"></i>
                                </span>
                            </div>
                            </div>
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
    </section>

    <section id="danh-sach-khoa-hoc" class="py-12 md:py-20">
        <div class="mx-auto max-w-[1450px] px-4 sm:px-6">
            <div class="mb-10 flex flex-col gap-4 md:flex-row md:items-end md:justify-between" data-aos="fade-up">
                <div class="max-w-2xl">
                    <h2 class="mt-4 text-3xl md:text-4xl font-black text-slate-950">Nhiều khóa học, chia theo từng nhu cầu học tập</h2>
                    <p class="mt-3 text-slate-600 leading-relaxed">
                        Chọn một nhóm khóa học để xây nền, tăng tốc IELTS hoặc học giao tiếp ứng dụng cho học sinh và người đi làm.
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <span class="rounded-full border border-red-200 bg-white px-4 py-2 text-sm font-bold text-rose-600 shadow-sm">Mầm non</span>
                    <span class="rounded-full border border-lime-200 bg-white px-4 py-2 text-sm font-bold text-emerald-600 shadow-sm">Tiểu học</span>
                    <span class="rounded-full border border-red-200 bg-white px-4 py-2 text-sm font-bold text-rose-600 shadow-sm">IELTS</span>
                    <span class="rounded-full border border-lime-200 bg-white px-4 py-2 text-sm font-bold text-emerald-600 shadow-sm">Doanh nghiệp</span>
                </div>
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
        </div>
    </section>

    <section id="dang-ky-tu-van" class="py-14 md:py-20">
        <div class="mx-auto max-w-[1450px] px-4 sm:px-6">
            <div class="grid gap-8 lg:grid-cols-[0.95fr_1.05fr] items-stretch">
                <div class="rounded-[2.5rem] bg-gradient-to-br from-red-600 to-rose-500 p-8 md:p-10 text-white shadow-[0_24px_60px_rgba(225,29,72,0.25)] transition-transform duration-300 hover:-translate-y-1" data-aos="fade-right" data-aos-duration="700">
                    <div class="morph-content">
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/15 px-4 py-2 text-xs font-black uppercase tracking-[0.2em]">
                        <span class="h-2 w-2 rounded-full bg-lime-300"></span>
                        Tư vấn nhanh
                    </span>
                    <h2 class="mt-6 text-3xl md:text-4xl font-black leading-tight">Nhận gợi ý khóa học phù hợp với trình độ hiện tại</h2>
                    <p class="mt-4 max-w-xl text-rose-50/95 leading-relaxed">
                        Gửi thông tin cho trung tâm, đội ngũ tư vấn sẽ phản hồi lịch học, cấp độ và lộ trình phù hợp nhất cho học viên.
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
                </div>

                <div class="rounded-[2.5rem] border border-white bg-white p-8 md:p-10 shadow-[0_24px_60px_rgba(15,23,42,0.08)] transition-transform duration-300 hover:-translate-y-1" data-aos="fade-left" data-aos-duration="700" data-aos-delay="100">
                    <div class="morph-content">
                    <div class="mb-8">
                        <h3 class="text-2xl font-black text-slate-950">Đăng ký nhận tư vấn</h3>
                        <p class="mt-2 text-slate-600">Để lại thông tin, trung tâm sẽ liên hệ sớm nhất.</p>
                    </div>

                    <form class="grid gap-4 sm:grid-cols-2">
                        <input type="text" placeholder="Họ và tên" class="sm:col-span-2 rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-slate-900 outline-none transition focus:border-rose-300 focus:bg-white">
                        <input type="tel" placeholder="Số điện thoại" class="rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-slate-900 outline-none transition focus:border-rose-300 focus:bg-white">
                        <input type="email" placeholder="Email" class="rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-slate-900 outline-none transition focus:border-rose-300 focus:bg-white">
                        <select class="sm:col-span-2 rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-slate-900 outline-none transition focus:border-rose-300 focus:bg-white">
                            <option>Chọn chương trình quan tâm</option>
                            <option>Tiếng Anh Mầm non</option>
                            <option>Tiếng Anh Tiểu học</option>
                            <option>Giao tiếp phản xạ</option>
                            <option>IELTS</option>
                            <option>Tiếng Anh Doanh nghiệp</option>
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
