<?php
$teacherId = max(0, (int) ($_GET['id'] ?? 0));
$academicModel = new AcademicModel();
$teacherUser = $teacherId > 0 ? $academicModel->findUser($teacherId) : null;
$locale = current_locale();

if (!$teacherUser || (string) ($teacherUser['role_name'] ?? '') !== 'teacher') {
    http_response_code(404);
    echo '404 Not Found';
    return;
}

$teacherProfile = is_array($teacherUser['role_profile'] ?? null) ? $teacherUser['role_profile'] : [];
$localizedField = static function (array $source, string $baseKey, string $locale): string {
    if ($locale === 'en') {
        $englishValue = trim((string) ($source[$baseKey . '_en'] ?? ''));
        if ($englishValue !== '') {
            return $englishValue;
        }
    }

    return trim((string) ($source[$baseKey] ?? ''));
};

$teacherName = $localizedField($teacherUser, 'full_name', $locale);
$teacherName = $teacherName !== '' ? $teacherName : t('teachers.default_name');
$teacherAvatar = trim((string) ($teacherUser['avatar'] ?? ''));
if ($teacherAvatar === '') {
    $teacherAvatar = 'https://ui-avatars.com/api/?name=' . urlencode($teacherName !== '' ? $teacherName : 'Teacher') . '&background=10b981&color=fff&size=256&bold=true';
} elseif (function_exists('normalize_public_file_url')) {
    $teacherAvatar = normalize_public_file_url($teacherAvatar);
}

$teacherDegree = $localizedField($teacherProfile, 'teacher_degree', $locale);
$teacherExperienceYears = max(0, (int) ($teacherProfile['teacher_experience_years'] ?? 0));
$teacherBio = $localizedField($teacherProfile, 'teacher_bio', $locale);
$teacherIntroVideoUrl = trim((string) ($teacherProfile['teacher_intro_video_url'] ?? ''));
if ($teacherIntroVideoUrl !== '' && function_exists('normalize_public_file_url')) {
    $teacherIntroVideoUrl = normalize_public_file_url($teacherIntroVideoUrl);
}

$teacherRoleLabel = t('teacher.detail.role');
$teacherIntroPoster = $teacherAvatar;
$teacherCertificates = $academicModel->listTeacherCertificatesByUserId($teacherId);
?>

<style>
    /* .course-detail-bg {
        background:
            radial-gradient(circle 1400px at 0% 0%, rgba(244, 63, 94, 0.28) 0%, rgba(244, 63, 94, 0.12) 60%, transparent 100%),
            radial-gradient(circle 1200px at 100% 0%, rgba(132, 204, 22, 0.25) 0%, rgba(132, 204, 22, 0.1) 60%, transparent 100%),
            radial-gradient(circle 900px at 50% 50%, rgba(6, 182, 212, 0.14) 0%, rgba(56, 189, 248, 0.04) 55%, transparent 100%),
            radial-gradient(circle 900px at 100% 55%, rgba(93, 199, 245, 0.08) 0%, transparent 100%),
            radial-gradient(circle 900px at 50% 100%, rgba(78, 143, 247, 0.12) 0%, transparent 100%),
            linear-gradient(180deg,
                #fff3f0 0%,
                #c2e4f6 70%,
                #cdf8dc 80%,
                #f4fbf7 90%,
                #ffffff 100%
            );
    } */

    .teacher-detail-page {
        font-family: 'Be Vietnam Pro', ui-sans-serif, system-ui, sans-serif;
    }

    .teacher-bio-content blockquote {
        margin: 1rem 0;
        padding: 1rem 1.25rem;
        border-left: 4px solid rgb(16 185 129);
        background: rgb(236 253 245);
        border-radius: 1rem;
        font-style: italic;
    }

    .teacher-bio-content ul,
    .teacher-bio-content ol {
        margin: 1rem 0;
        padding-left: 1.5rem;
    }

    .teacher-bio-content li {
        margin: 0.35rem 0;
    }

    .teacher-bio-content a {
        color: rgb(5 150 105);
        text-decoration: underline;
        text-underline-offset: 0.2em;
    }

    .teacher-bio-content code {
        padding: 0.15rem 0.4rem;
        border-radius: 0.5rem;
        background: rgb(241 245 249);
        font-size: 0.95em;
    }
</style>

<section class="teacher-detail-page course-detail-bg min-h-screen font-jakarta pb-24 relative">
    
    <div class="absolute inset-0 overflow-hidden pointer-events-none -z-10">
        <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-emerald-200/30 rounded-full blur-[100px]"></div>
        <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-rose-200/20 rounded-full blur-[100px]"></div>
    </div>

    <div class="bg-white border-b border-slate-200/60 pt-8 pb-12 shadow-sm">
        <div class="container mx-auto px-4 sm:px-6 max-w-6xl">
            <a href="/teachers" class="inline-flex items-center gap-2 text-xs font-bold text-slate-400 hover:text-emerald-600 transition-colors mb-8 uppercase tracking-widest">
                <i class="fa-solid fa-arrow-left-long"></i> <?= e(t('teacher.detail.back_to_team')); ?>
            </a>

            <div class="flex flex-col md:flex-row items-center md:items-start gap-8 text-center md:text-left">
                <div class="relative w-40 h-40 md:w-48 md:h-48 rounded-full border-[8px] border-emerald-50 shadow-xl shrink-0 overflow-hidden bg-slate-100">
                        <img src="<?= e($teacherAvatar) ?>" class="w-full h-full object-cover">
                </div>

                <div class="flex-1">
                    <span class="inline-block px-3 py-1 rounded-lg bg-emerald-50 border border-emerald-100 text-emerald-600 text-[10px] font-black uppercase tracking-widest mb-3">
                        <?= e($teacherRoleLabel) ?>
                    </span>
                    <h1 class="text-3xl md:text-5xl font-black text-slate-800 mb-3 tracking-tight"><?= e($teacherName) ?></h1>
                    
                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-4 text-sm font-bold text-slate-600">
                        <div class="flex items-center gap-2"><i class="fa-solid fa-graduation-cap text-slate-400"></i> <?= e($teacherDegree !== '' ? $teacherDegree : t('teacher.detail.degree_updating')) ?></div>
                        <div class="hidden md:block text-slate-300">|</div>
                        <div class="flex items-center gap-2"><i class="fa-solid fa-briefcase text-slate-400"></i> <?= e(t('teacher.detail.teaching_experience_full', ['count' => (string) $teacherExperienceYears])); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 sm:px-6 max-w-6xl mt-12">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
            
            <div class="lg:col-span-8 space-y-10">
                
                <div class="bg-white rounded-[2.5rem] p-3 shadow-xl shadow-slate-200/50 border border-slate-100">
                    <div class="relative rounded-[2rem] overflow-hidden aspect-video bg-slate-900 group cursor-pointer">
                        <?php if ($teacherIntroVideoUrl !== ''): ?>
                            <video class="w-full h-full object-cover opacity-90" controls playsinline preload="metadata" poster="<?= e($teacherIntroPoster) ?>">
                                <source src="<?= e($teacherIntroVideoUrl) ?>" type="video/mp4">
                            </video>
                        <?php else: ?>
                            <img src="<?= e($teacherIntroPoster) ?>" class="w-full h-full object-cover opacity-60 group-hover:opacity-50 transition-opacity duration-500">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="w-20 h-20 rounded-full bg-emerald-500 text-white flex items-center justify-center text-2xl shadow-[0_0_30px_rgba(16,185,129,0.5)] group-hover:scale-110 transition-transform duration-300">
                                    <i class="fa-solid fa-play ml-1"></i>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="bg-white rounded-[2rem] p-8 md:p-10 shadow-sm border border-slate-100 relative overflow-hidden">
                    <i class="fa-solid fa-quote-left absolute top-8 right-8 text-6xl text-slate-50 opacity-50"></i>
                    <h2 class="text-2xl font-black text-slate-800 mb-6 flex items-center gap-3">
                        <span class="w-2 h-8 bg-emerald-500 rounded-full"></span> <?= e(t('teacher.detail.about')); ?>
                    </h2>
                    <div class="teacher-bio-content text-slate-600 font-medium leading-loose text-[15px] text-justify">
                        <?= $teacherBio !== '' ? ui_render_bbcode($teacherBio) : e(t('teacher.detail.about_fallback')) ?>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-4 space-y-8">
                
                <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-slate-100 relative overflow-hidden">
                    <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-emerald-500 via-teal-400 to-sky-500"></div>
                    <div class="flex items-start justify-between gap-4 mb-6">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.35em] text-emerald-500 mb-3"><?= e(t('teacher.detail.expertise')); ?></p>
                            <h2 class="text-2xl font-black text-slate-800 leading-tight"><?= e(t('teacher.detail.certificates_title')); ?></h2>
                            <p class="text-sm font-medium text-slate-500 mt-2"><?= e(t('teacher.detail.certificates_copy')); ?></p>
                        </div>
                        <div class="hidden sm:flex items-center gap-2 px-4 py-2 rounded-2xl bg-slate-50 border border-slate-100 text-slate-600 text-xs font-bold">
                            <i class="fa-solid fa-award text-emerald-500"></i>
                            <span><?= e(t('teacher.detail.items_count', ['count' => (string) count($teacherCertificates)])); ?></span>
                        </div>
                    </div>

                    <div class="flex flex-col gap-4">
                        <div class="rounded-[1.5rem] p-5 bg-gradient-to-br from-slate-900 to-emerald-900 text-white shadow-xl shadow-emerald-900/10 relative overflow-hidden">
                            <div class="absolute -right-8 -top-8 w-28 h-28 rounded-full bg-white/10 blur-2xl"></div>
                            <div class="relative flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <p class="text-[10px] font-black uppercase tracking-[0.35em] text-emerald-200 mb-2"><?= e(t('teacher.detail.summary')); ?></p>
                                    <h3 class="text-2xl font-black leading-tight"><?= e(t('teacher.detail.teaching_experience_full', ['count' => (string) $teacherExperienceYears])); ?></h3>
                                    <p class="text-sm text-emerald-50/80 mt-2 max-w-md"><?= e(t('teacher.detail.achievements_copy')); ?></p>
                                </div>
                                <div class="w-14 h-14 rounded-2xl bg-white/10 border border-white/10 flex items-center justify-center text-2xl shrink-0">
                                    <i class="fa-solid fa-briefcase"></i>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-[1.5rem] p-5 bg-slate-50 border border-slate-100 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 mb-2"><?= e(t('teacher.detail.degree')); ?></p>
                                    <h4 class="text-lg font-black text-slate-800"><?= e($teacherDegree !== '' ? $teacherDegree : t('teacher.detail.degree_updating')) ?></h4>
                                </div>
                                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center text-xl shrink-0">
                                    <i class="fa-solid fa-graduation-cap"></i>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-[1.5rem] p-5 bg-slate-50 border border-slate-100 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 mb-2"><?= e(t('teacher.detail.experience_label')); ?></p>
                                    <h4 class="text-lg font-black text-slate-800"><?= e(t('teachers.years_short', ['count' => (string) $teacherExperienceYears])); ?></h4>
                                </div>
                                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center text-xl shrink-0">
                                    <i class="fa-solid fa-briefcase"></i>
                                </div>
                            </div>
                        </div>

                        <?php if (empty($teacherCertificates)): ?>
                            <div class="rounded-[1.5rem] border border-dashed border-slate-200 bg-slate-50/70 p-6 text-sm font-medium text-slate-500">
                                <?= e(t('teacher.detail.no_certificates')); ?>
                            </div>
                        <?php else: ?>
                            <?php foreach ($teacherCertificates as $certificate): ?>
                                <div class="rounded-[1.5rem] p-5 bg-white border border-slate-100 shadow-[0_12px_40px_rgba(15,23,42,0.05)] hover:shadow-[0_18px_50px_rgba(15,23,42,0.08)] hover:-translate-y-1 transition-all duration-300">
                                    <div class="flex items-start gap-4">
                                        <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 border border-rose-100 flex items-center justify-center text-xl shrink-0">
                                            <i class="fa-solid fa-certificate"></i>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 mb-2"><?= e(t('teacher.detail.certificate')); ?></p>
                                            <h4 class="text-base font-black text-slate-800 leading-snug">
                                                <?= e($localizedField($certificate, 'certificate_name', $locale) !== '' ? $localizedField($certificate, 'certificate_name', $locale) : t('teacher.detail.certificate')) ?>
                                            </h4>
                                            <?php if (trim((string) ($certificate['score'] ?? '')) !== ''): ?>
                                                <div class="mt-3 inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-700 text-xs font-black border border-emerald-100">
                                                    <i class="fa-solid fa-star"></i>
                                                    <span><?= e((string) ($certificate['score'] ?? '')) ?></span>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (trim((string) ($certificate['image_url'] ?? '')) !== ''): ?>
                                                <a href="<?= e((string) $certificate['image_url']) ?>" target="_blank" rel="noopener noreferrer" class="mt-4 inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-emerald-600 transition-colors">
                                                    <i class="fa-regular fa-image"></i>
                                                    <?= e(t('teacher.detail.view_proof')); ?>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

               

            </div>

        </div>
    </div>
</section>

<script>
    // Giả lập click vào video demo
    const videoCard = document.querySelector('.aspect-video');
    if(videoCard) {
        videoCard.addEventListener('click', function() {
            alert(<?= json_encode(t('teacher.detail.watch_intro'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>);
            // Trong thực tế, bạn sẽ mở ra một Modal chứa <iframe src="youtube_link"></iframe>
        });
    }
</script>
