<?php
$academicModel = new AcademicModel();
$teacherRows = $academicModel->listActiveTeachers();
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
        $teacherAvatar = 'https://ui-avatars.com/api/?name=' . urlencode((string) ($teacherUser['full_name'] ?? 'Teacher')) . '&background=10b981&color=fff&size=600&bold=true';
    } elseif (function_exists('normalize_public_file_url')) {
        $teacherAvatar = normalize_public_file_url($teacherAvatar);
    }

    $highlights = [];
    if ($teacherDegree !== '') {
        $highlights[] = $teacherDegree;
    }
    if ($teacherExperience > 0) {
        $highlights[] = $teacherExperience . ' năm kinh nghiệm';
    }
    if ($teacherBio !== '') {
        $highlights[] = mb_strimwidth($teacherBio, 0, 40, '...');
    }

    $teachers[] = [
        'id' => $teacherId,
        'name' => (string) ($teacherUser['full_name'] ?? 'Giáo viên'),
        'avatar' => $teacherAvatar,
        'role' => 'Giảng viên',
        'degree' => $teacherDegree !== '' ? $teacherDegree : 'Đang cập nhật',
        'experience' => $teacherExperience,
        'highlights' => $highlights !== [] ? array_slice($highlights, 0, 3) : ['Giảng viên'],
    ];
}
?>

<style>
    .teacher-card { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
    .teacher-card:hover { transform: translateY(-10px); box-shadow: 0 25px 50px -12px rgba(16, 185, 129, 0.25); border-color: #10b981; }
</style>

<section class="min-h-screen bg-slate-50 font-jakarta pb-24">
    <div class="relative bg-slate-900 pt-24 pb-32 overflow-hidden">
        <div class="absolute inset-0">
            <img src="https://images.unsplash.com/photo-1524178232363-1fb2b075b655?q=80&w=1600&auto=format&fit=crop" class="w-full h-full object-cover opacity-20 mix-blend-overlay">
            <div class="absolute inset-0 bg-gradient-to-t from-slate-900 to-transparent"></div>
        </div>
        <div class="container mx-auto px-4 max-w-6xl relative z-10 text-center">
            <span class="inline-block px-4 py-1.5 rounded-full bg-emerald-500/20 text-emerald-400 text-xs font-black uppercase tracking-widest border border-emerald-500/30 mb-6">Niềm tự hào của Nhuệ Minh</span>
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-black text-white tracking-tight mb-6">
                Đội ngũ Giảng viên <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-teal-300">Tinh hoa</span>
            </h1>
            <p class="text-slate-300 text-lg md:text-xl max-w-2xl mx-auto font-medium">100% Giảng viên sở hữu chứng chỉ giảng dạy quốc tế (TESOL, CELTA), tận tâm đồng hành cùng sự phát triển của học viên.</p>
        </div>
    </div>

    <div class="container mx-auto px-4 max-w-6xl relative z-20 -mt-16">
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach($teachers as $teacher): ?>
            <div class="teacher-card bg-white rounded-[2rem] p-4 border border-slate-100 flex flex-col group cursor-pointer relative" onclick="window.location.href='/teacher-detail?id=<?= $teacher['id'] ?>'">
                
                <div class="relative h-72 rounded-[1.5rem] overflow-hidden mb-5 bg-slate-100">
                    <img src="<?= e($teacher['avatar']) ?>" alt="<?= e($teacher['name']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-transparent to-transparent opacity-60 group-hover:opacity-80 transition-opacity"></div>
                    
                    <div class="absolute bottom-4 left-4 bg-white/90 backdrop-blur-sm px-3 py-1.5 rounded-xl flex items-center gap-2 shadow-sm">
                        <div class="w-6 h-6 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-[10px]"><i class="fa-solid fa-briefcase"></i></div>
                        <span class="text-xs font-black text-slate-800"><?= $teacher['experience'] ?> năm kinh nghiệm</span>
                    </div>
                </div>

                <div class="px-2 flex flex-col flex-1">
                    <p class="text-[10px] font-black uppercase tracking-widest text-emerald-600 mb-1"><?= e($teacher['role']) ?></p>
                    <h3 class="text-2xl font-black text-slate-800 mb-3 group-hover:text-emerald-600 transition-colors"><?= e($teacher['name']) ?></h3>
                    
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

                    <div class="w-full py-3.5 rounded-xl bg-slate-50 text-slate-600 font-black text-xs uppercase tracking-widest text-center group-hover:bg-emerald-500 group-hover:text-white transition-colors flex items-center justify-center gap-2">
                        Xem hồ sơ chi tiết <i class="fa-solid fa-arrow-right"></i>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>