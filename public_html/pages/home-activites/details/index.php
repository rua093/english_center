<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../models/AcademicModel.php';

$academicModel = new AcademicModel();
$activityId = (int) ($_GET['id'] ?? 0);
$actDetail = $activityId > 0 ? $academicModel->findActivity($activityId) : null;

if (!is_array($actDetail)) {
	http_response_code(404);
	echo '404 Not Found';
	exit;
}

$activityTitle = (string) ($actDetail['activity_name'] ?? '');
$activityDescription = trim((string) ($actDetail['description'] ?? ''));
$activityContent = trim((string) ($actDetail['content'] ?? ''));
$activityLocation = trim((string) ($actDetail['location'] ?? ''));
$activityDate = !empty($actDetail['start_date']) ? date('d/m/Y', strtotime((string) $actDetail['start_date'])) : '---';
$activityTime = !empty($actDetail['start_date']) ? date('H:i', strtotime((string) $actDetail['start_date'])) : '--:--';
$activityFee = (float) ($actDetail['fee'] ?? 0);
$activityStatus = (string) ($actDetail['status'] ?? 'upcoming');
$activityStatusLabel = match ($activityStatus) {
	'ongoing' => 'Đang diễn ra',
	'finished' => 'Đã kết thúc',
	default => 'Sắp diễn ra',
};

$resolveActivityImagePath = static function (string $imagePath): string {
    $imagePath = trim($imagePath);
    if ($imagePath === '') {
        return 'https://images.unsplash.com/photo-1533227268428-f9ed0900fb3b?w=1200&q=80';
    }

    if (preg_match('#^(?:https?://|/)#i', $imagePath)) {
        return $imagePath;
    }

    return '/assets/uploads/' . ltrim($imagePath, '/');
};

$benefitItems = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $activityContent !== '' ? $activityContent : $activityDescription) ?: [])));
if ($benefitItems === []) {
	$benefitItems = [$activityDescription !== '' ? $activityDescription : 'Chưa có mô tả chi tiết từ database.'];
}
$activityImage = $resolveActivityImagePath((string) ($actDetail['image_thumbnail'] ?? ''));
?>

<main class="pb-16 bg-slate-50">
    <section class="relative h-[52vh] min-h-[420px] overflow-hidden">
        <img src="<?= e($activityImage); ?>" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-900/35 to-transparent"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(16,185,129,0.18),transparent_42%),radial-gradient(circle_at_top_right,rgba(244,63,94,0.18),transparent_38%)]"></div>
        <div class="absolute bottom-0 left-0 w-full p-6 md:p-10 lg:p-14">
            <div class="container mx-auto px-4 max-w-5xl relative z-10">
                <div class="inline-flex items-center gap-2 rounded-full bg-white/15 px-4 py-2 text-[10px] font-black uppercase tracking-[0.25em] text-white backdrop-blur-md border border-white/15 mb-4">
                    <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                    <?= e($activityStatusLabel); ?>
                </div>
                <h1 class="max-w-4xl text-3xl md:text-5xl lg:text-6xl font-black text-white leading-[1.05] uppercase drop-shadow-[0_10px_25px_rgba(0,0,0,0.35)]">
                    <?= e($activityTitle); ?>
                </h1>
                <p class="mt-4 max-w-2xl text-sm md:text-base text-slate-100/95 font-medium leading-relaxed">
                    <?= e($activityDescription !== '' ? $activityDescription : 'Trải nghiệm kết hợp học thuật và vận động ngoài trời, giúp học viên rèn tiếng Anh tự nhiên trong một không gian an toàn và giàu cảm hứng.'); ?>
                </p>
            </div>
        </div>
    </section>

    <div class="container mx-auto px-4 max-w-5xl -mt-8 md:-mt-12 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <div class="lg:col-span-8 space-y-6">
                <div class="rounded-[2rem] border border-white/60 bg-white/95 p-6 md:p-8 shadow-[0_20px_50px_rgba(15,23,42,0.08)] backdrop-blur-sm">
                    <h2 class="text-lg md:text-xl font-black text-slate-800 mb-5 flex items-center gap-3">
                        <span class="w-2 h-6 bg-rose-500 rounded-full"></span>
                        Thông tin chương trình
                    </h2>
                    <p class="text-slate-600 leading-relaxed mb-6 font-medium text-sm md:text-base max-w-3xl">
                        <?= e($activityDescription !== '' ? $activityDescription : ($activityContent !== '' ? $activityContent : 'Chưa có mô tả chi tiết từ database.')); ?>
                    </p>
                    
                    <h3 class="text-base md:text-lg font-black text-slate-800 mb-4">Bạn sẽ nhận được gì?</h3>
                    <div class="grid md:grid-cols-1 gap-3">
                        <?php foreach($benefitItems as $b): ?>
                        <div class="flex items-center gap-3 bg-emerald-50 p-4 rounded-2xl border border-emerald-100">
                            <div class="w-7 h-7 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-sm">
                                <i class="fa-solid fa-check text-xs"></i>
                            </div>
                            <span class="font-bold text-emerald-800 text-xs md:text-sm"><?= e($b); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="rounded-[2rem] border border-slate-100 bg-white p-6 md:p-8 shadow-[0_18px_45px_rgba(15,23,42,0.08)]">
                    <h2 class="text-lg md:text-xl font-black text-slate-800 mb-5">Hình ảnh hoạt động</h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        <img src="https://images.unsplash.com/photo-1511632765486-a01980e01a18?w=300" class="rounded-2xl h-28 md:h-36 w-full object-cover hover:scale-[1.03] transition-all">
                        <img src="https://images.unsplash.com/photo-1526726533690-069a7974e643?w=300" class="rounded-2xl h-28 md:h-36 w-full object-cover hover:scale-[1.03] transition-all">
                        <img src="https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?w=300" class="rounded-2xl h-28 md:h-36 w-full object-cover hover:scale-[1.03] transition-all">
                    </div>
                </div>
            </div>

            <div class="lg:col-span-4">
                <div class="sticky top-24 rounded-[2rem] border border-slate-100 bg-white p-6 shadow-[0_18px_45px_rgba(15,23,42,0.08)] overflow-hidden">
                    <div class="absolute top-0 right-0 w-20 h-20 bg-rose-50 rounded-bl-full -z-10"></div>
                    
                    <div class="space-y-5">
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Thời gian</p>
                            <div class="flex items-center gap-2.5 font-black text-slate-800 text-sm md:text-base">
                                <i class="fa-solid fa-clock text-rose-500 text-lg"></i>
                                <?= e($activityTime . ' - ' . $activityDate); ?>
                            </div>
                        </div>

                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Địa điểm</p>
                            <div class="flex items-center gap-2.5 font-black text-slate-800 leading-snug text-sm md:text-base">
                                <i class="fa-solid fa-location-dot text-rose-500 text-lg"></i>
                                <?= e($activityLocation !== '' ? $activityLocation : '---'); ?>
                            </div>
                        </div>

                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Chi phí</p>
                            <div class="text-xl md:text-2xl font-black text-emerald-600">
                                <?= $activityFee > 0 ? number_format($activityFee) . ' VNĐ / học viên' : 'Miễn phí'; ?>
                            </div>
                        </div>

                        <button class="w-full bg-rose-600 hover:bg-rose-700 text-white font-black py-4 rounded-2xl shadow-xl shadow-rose-600/20 transition-all hover:-translate-y-1 uppercase tracking-widest text-sm">
                            Đăng ký tham gia ngay
                        </button>
                        
                        <p class="text-center text-[10px] text-slate-400 font-bold uppercase">Ưu đãi 10% khi đăng ký nhóm 3 người</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>