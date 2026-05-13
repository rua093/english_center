<?php
$academicModel = new AcademicModel();
$teachersPerPage = 12;
$currentTeacherPage = max(1, (int) ($_GET['teachers_page'] ?? 1));
$teacherTotal = $academicModel->countActiveTeachers();
$totalTeacherPages = max(1, (int) ceil($teacherTotal / $teachersPerPage));
$currentTeacherPage = min($currentTeacherPage, $totalTeacherPages);
$teacherRows = $teacherTotal > 0
    ? $academicModel->listActiveTeachersPage($currentTeacherPage, $teachersPerPage)
    : [];
$buildTeacherPageUrl = static function (int $page) : string {
    return page_url('teacher-introduce', ['teachers_page' => $page]) . '#danh-sach-giang-vien';
};
$teachers = [];

foreach ($teacherRows as $teacherRow) {
    $teacherId = (int) ($teacherRow['id'] ?? 0);
    if ($teacherId <= 0) {
        continue;
    }

    $teacherUser = $academicModel->findActiveUser($teacherId);
    if (!$teacherUser || (string) ($teacherUser['role_name'] ?? '') !== 'teacher') {
        continue;
    }

    $teacherProfile = is_array($teacherUser['role_profile'] ?? null) ? $teacherUser['role_profile'] : [];
    $teacherDegree = trim((string) ($teacherProfile['teacher_degree'] ?? ''));
    $teacherExperience = max(0, (int) ($teacherProfile['teacher_experience_years'] ?? 0));
    $teacherBio = trim((string) ($teacherProfile['teacher_bio'] ?? ''));
    $teacherAvatar = trim((string) ($teacherUser['avatar'] ?? ''));

    if ($teacherAvatar === '') {
        $teacherAvatar = 'https://ui-avatars.com/api/?name=' . urlencode((string) ($teacherUser['full_name'] ?? t('teachers.default_name'))) . '&background=10b981&color=fff&size=600&bold=true';
    } elseif (function_exists('normalize_public_file_url')) {
        $teacherAvatar = normalize_public_file_url($teacherAvatar);
    }

    $highlights = [];
    if ($teacherDegree !== '') {
        $highlights[] = $teacherDegree;
    }
    if ($teacherExperience > 0) {
        $highlights[] = t('teachers.years_experience', ['count' => $teacherExperience]);
    }
    if ($teacherBio !== '') {
        $highlights[] = mb_strimwidth($teacherBio, 0, 40, '...');
    }

    $teachers[] = [
        'id' => $teacherId,
        'name' => (string) ($teacherUser['full_name'] ?? t('teachers.default_name')),
        'avatar' => $teacherAvatar,
        'role' => t('teachers.default_role'),
        'degree' => $teacherDegree !== '' ? $teacherDegree : t('courses.updating'),
        'experience' => $teacherExperience,
        'highlights' => $highlights !== [] ? array_slice($highlights, 0, 3) : [t('teachers.default_highlight')],
    ];
}
?>

<link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<style>
    .teacher-card:hover .teacher-card-img {
        transform: scale(1.08);
    }

    .teacher-card {
        transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .teacher-card:hover {
        transform: translateY(-0.5rem);
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.14);
        border-color: rgba(16, 185, 129, 0.28);
    }
</style>

<section class="relative min-h-screen overflow-hidden bg-lime-100 font-jakarta pb-24">
    <div class="absolute inset-0 z-0 pointer-events-none opacity-[0.08]" style="background-image: radial-gradient(#475569 1.5px, transparent 1.5px); background-size: 24px 24px;"></div>
    <div class="absolute inset-x-0 top-0 z-0 h-72 pointer-events-none bg-gradient-to-b from-lime-200/75 via-lime-100/45 to-transparent"></div>
    <div class="relative z-10 bg-lime-100 pt-24 pb-32 overflow-hidden">
    <div class="absolute inset-0">
        <img src="<?= e('/assets/images/teacher_page_banner.jpg'); ?>" alt="<?= e(t('teachers.banner_alt')); ?>" class="w-full h-full object-cover object-center opacity-100">
    </div>
    
    <div class="container mx-auto px-4 max-w-6xl relative z-10 text-left">
        
        <span class="inline-block px-4 py-1.5 rounded-full bg-black/30 text-white text-xs font-bold uppercase tracking-widest border border-white/40 shadow-sm mb-6 backdrop-blur-md"><?= e(t('teachers.kicker')); ?></span>
        
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-black text-white uppercase tracking-tight mb-6">
            <span class="inline-block [text-shadow:0px_5px_15px_rgba(0,0,0,0.85)]"><?= e(t('teachers.hero_line_1')); ?></span>
            <span class="block mt-2 [text-shadow:0px_5px_15px_rgba(0,0,0,0.85)]"><?= e(t('teachers.hero_line_2')); ?></span>
        </h1>
        
        <p class="text-white text-lg md:text-xl max-w-2xl font-medium leading-relaxed [text-shadow:0px_3px_8px_rgba(0,0,0,0.9)]">
            <?= e(t('teachers.hero_copy')); ?>
        </p>
        
    </div>
</div>

    <div class="container mx-auto px-4 max-w-6xl relative z-20 -mt-16">
        <div id="danh-sach-giang-vien" class="grid grid-cols-1 gap-8 md:grid-cols-2 xl:grid-cols-3">
            <?php $teacherDelay = 0; ?>
            <?php foreach($teachers as $teacher): ?>
            <div class="teacher-card bg-white/95 rounded-[2rem] p-4 border border-white shadow-[0_14px_40px_rgba(15,23,42,0.08)] flex flex-col group cursor-pointer relative overflow-hidden transition-all duration-500 hover:-translate-y-2 hover:shadow-[0_24px_60px_rgba(15,23,42,0.14)]" onclick="window.location.href='/teacher-detail?id=<?= $teacher['id'] ?>'" data-aos="fade-up" data-aos-delay="<?= $teacherDelay; ?>" data-aos-duration="700">
                <div class="mb-5 overflow-hidden rounded-[1.5rem] bg-slate-100">
                    <div class="relative aspect-[4/3] w-full">
                        <img src="<?= e($teacher['avatar']) ?>" alt="<?= e($teacher['name']) ?>" class="teacher-card-img h-full w-full object-cover transition-transform duration-700">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/75 via-slate-950/10 to-transparent opacity-80 transition-opacity duration-500 group-hover:opacity-90"></div>

                        <div class="absolute left-4 top-4 rounded-full bg-white/90 px-3 py-1.5 text-[10px] font-black uppercase tracking-widest text-emerald-600 shadow-sm backdrop-blur-sm">
                            <?= e(t('teachers.profile_badge')); ?>
                        </div>

                        <div class="absolute bottom-4 left-4 right-4 flex items-end justify-between gap-3">
                            <div class="rounded-2xl bg-white/90 px-3 py-2 backdrop-blur-sm shadow-sm">
                                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400"><?= e(t('teachers.experience')); ?></p>
                                <p class="text-sm font-black text-slate-900"><?= e(t('teachers.years_short', ['count' => $teacher['experience']])); ?></p>
                            </div>
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-500 text-white shadow-lg shadow-emerald-500/25 transition-transform duration-300 group-hover:-translate-y-1">
                                <i class="fa-solid fa-arrow-right"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-2 flex flex-col flex-1">
                    <p class="text-[10px] font-black uppercase tracking-widest text-emerald-600 mb-1"><?= e($teacher['role']) ?></p>
                    <h3 class="text-2xl font-black text-slate-800 mb-3 group-hover:text-emerald-600 transition-colors duration-300"><?= e($teacher['name']) ?></h3>
                    
                    <div class="flex items-start gap-2 text-sm font-bold text-slate-500 mb-5">
                        <i class="fa-solid fa-graduation-cap text-slate-400 mt-1"></i>
                        <span><?= e($teacher['degree']) ?></span>
                    </div>

                    <div class="flex flex-wrap gap-2 mb-6 mt-auto">
                        <?php foreach($teacher['highlights'] as $tag): ?>
                            <span class="px-3 py-1 rounded-lg bg-slate-50 border border-slate-200 text-[10px] font-black text-slate-600 uppercase">
                                <?= e($tag) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>

                    <div class="w-full py-3.5 rounded-xl bg-slate-50 text-slate-600 font-black text-xs uppercase tracking-widest text-center group-hover:bg-emerald-500 group-hover:text-white transition-all duration-300 flex items-center justify-center gap-2">
                        <?= e(t('teachers.view_profile')); ?> <i class="fa-solid fa-arrow-right"></i>
                    </div>
                </div>
            </div>
            <?php $teacherDelay += 100; ?>
            <?php endforeach; ?>
        </div>

        <?php if ($totalTeacherPages > 1): ?>
            <div class="mt-10 rounded-[2rem] border border-white bg-white/85 p-4 shadow-[0_12px_30px_rgba(15,23,42,0.06)] backdrop-blur-md">
                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm md:text-base font-medium text-slate-600">
                        <?= e(t('teachers.showing', ['shown' => count($teachers), 'total' => number_format($teacherTotal, 0, ',', '.')])); ?>
                    </p>
                    <p class="text-xs md:text-sm font-bold uppercase tracking-[0.18em] text-emerald-600">
                        <?= e(t('teachers.page_status', ['current' => $currentTeacherPage, 'total' => $totalTeacherPages])); ?>
                    </p>
                </div>

                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
             

                <nav class="flex flex-wrap items-center justify-center gap-2" aria-label="<?= e(t('teachers.pagination_label')); ?>">
                    <a href="<?= e($buildTeacherPageUrl(max(1, $currentTeacherPage - 1))); ?>" class="inline-flex h-11 items-center justify-center rounded-full border border-slate-200 bg-white px-4 text-sm font-black text-slate-700 shadow-sm transition-all hover:-translate-y-0.5 hover:border-emerald-300 hover:text-emerald-600 <?= $currentTeacherPage === 1 ? 'pointer-events-none opacity-40' : ''; ?>">
                        <i class="fa-solid fa-chevron-left"></i>
                    </a>

                    <?php
                    $pageStart = max(1, $currentTeacherPage - 2);
                    $pageEnd = min($totalTeacherPages, $currentTeacherPage + 2);
                    if ($pageStart > 1) {
                        echo '<a href="' . e($buildTeacherPageUrl(1)) . '" class="inline-flex h-11 min-w-11 items-center justify-center rounded-full border border-slate-200 bg-white px-4 text-sm font-black text-slate-700 shadow-sm transition-all hover:-translate-y-0.5 hover:border-emerald-300 hover:text-emerald-600">1</a>';
                        if ($pageStart > 2) {
                            echo '<span class="px-1 text-slate-400">...</span>';
                        }
                    }

                    for ($page = $pageStart; $page <= $pageEnd; $page++) {
                        $isCurrentPage = $page === $currentTeacherPage;
                        $pageClasses = $isCurrentPage
                            ? 'border-emerald-600 bg-emerald-600 text-white shadow-md'
                            : 'border-slate-200 bg-white text-slate-700 shadow-sm hover:-translate-y-0.5 hover:border-emerald-300 hover:text-emerald-600';

                        echo '<a href="' . e($buildTeacherPageUrl($page)) . '" class="inline-flex h-11 min-w-11 items-center justify-center rounded-full border px-4 text-sm font-black transition-all ' . $pageClasses . '"' . ($isCurrentPage ? ' aria-current="page"' : '') . '>' . $page . '</a>';
                    }

                    if ($pageEnd < $totalTeacherPages) {
                        if ($pageEnd < $totalTeacherPages - 1) {
                            echo '<span class="px-1 text-slate-400">...</span>';
                        }
                        echo '<a href="' . e($buildTeacherPageUrl($totalTeacherPages)) . '" class="inline-flex h-11 min-w-11 items-center justify-center rounded-full border border-slate-200 bg-white px-4 text-sm font-black text-slate-700 shadow-sm transition-all hover:-translate-y-0.5 hover:border-emerald-300 hover:text-emerald-600">' . $totalTeacherPages . '</a>';
                    }
                    ?>

                    <a href="<?= e($buildTeacherPageUrl(min($totalTeacherPages, $currentTeacherPage + 1))); ?>" class="inline-flex h-11 items-center justify-center rounded-full border border-slate-200 bg-white px-4 text-sm font-black text-slate-700 shadow-sm transition-all hover:-translate-y-0.5 hover:border-emerald-300 hover:text-emerald-600 <?= $currentTeacherPage >= $totalTeacherPages ? 'pointer-events-none opacity-40' : ''; ?>">
                        <i class="fa-solid fa-chevron-right"></i>
                    </a>
                </nav>
                </div>
            </div>
        <?php endif; ?>

    </div>
</section>

<?php include __DIR__ . '/../partials/social_contact.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 350,
            once: true,
            offset: 0
        });
    }
});

window.addEventListener('load', function () {
    if (typeof AOS !== 'undefined') {
        AOS.refresh();
    }
});
</script>
