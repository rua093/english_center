<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../models/AcademicModel.php';

$academicModel = new AcademicModel();
$activityId = (int) ($_GET['id'] ?? 0);
$actDetail = $activityId > 0 ? $academicModel->findActivity($activityId) : null;
$locale = current_locale();

if (!is_array($actDetail)) {
	http_response_code(404);
	echo '404 Not Found';
	exit;
}

$localizedField = static function (array $source, string $baseKey, string $locale): string {
	if ($locale === 'en') {
		$englishValue = trim((string) ($source[$baseKey . '_en'] ?? ''));
		if ($englishValue !== '') {
			return $englishValue;
		}
	}

	return trim((string) ($source[$baseKey] ?? ''));
};

$activityTitle = $localizedField($actDetail, 'activity_name', $locale);
$activityDescription = $localizedField($actDetail, 'description', $locale);
$activityContent = $localizedField($actDetail, 'content', $locale);
$activityLocation = $localizedField($actDetail, 'location', $locale);
$activityDate = !empty($actDetail['start_date']) ? date('d/m/Y', strtotime((string) $actDetail['start_date'])) : '---';
$activityTime = !empty($actDetail['start_date']) ? date('H:i', strtotime((string) $actDetail['start_date'])) : '--:--';
$activityFee = (float) ($actDetail['fee'] ?? 0);
$activityStatus = (string) ($actDetail['status'] ?? 'upcoming');
$activityStatusLabel = match ($activityStatus) {
	'ongoing' => t('activities.status.ongoing'),
	'finished' => t('activities.status.finished'),
	default => t('activities.status.upcoming'),
};

$currentUser = function_exists('auth_user') ? (auth_user() ?? []) : [];
$isLoggedIn = (int) ($currentUser['id'] ?? 0) > 0;
$canRegisterActivity = $isLoggedIn;

$resolveActivityImagePath = static function (string $imagePath): string {
    $imagePath = trim($imagePath);
    if ($imagePath === '') {
        return 'https://images.unsplash.com/photo-1533227268428-f9ed0900fb3b?w=1200&q=80';
    }

    return function_exists('normalize_public_file_url')
        ? normalize_public_file_url($imagePath)
        : $imagePath;
};

$activityImage = $resolveActivityImagePath((string) ($actDetail['image_thumbnail'] ?? ''));
$activityDescriptionHtml = $activityDescription !== '' ? ui_render_bbcode($activityDescription) : '';
$activityContentHtml = $activityContent !== '' ? ui_render_bbcode($activityContent) : '';
$registerOnsubmit = '';

if ($canRegisterActivity) {
	$registerOnsubmit = 'event.preventDefault(); showConfirm(\'success\', '
		. json_encode(t('activity.detail.register_confirm_title'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
		. ', '
		. json_encode(t('activity.detail.register_confirm_message'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
		. ', () => this.submit());';
}
?>

<style>
    .course-detail-bg {
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
    }
</style>

<main class="course-detail-bg pb-16">
<section class="relative h-[65vh] min-h-[500px] md:h-[75vh] md:min-h-[580px] lg:h-[80vh] overflow-hidden">    <img src="<?= e($activityImage); ?>" class="w-full h-full object-cover object-center">
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
                    <?= $activityDescriptionHtml !== ''
                        ? $activityDescriptionHtml
                        : e(t('activity.detail.hero_fallback')); ?>
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
                        <?= e(t('activity.detail.program_info')); ?>
                    </h2>
                    <p class="text-slate-600 leading-relaxed mb-6 font-medium text-sm md:text-base max-w-3xl">
                        <?= $activityContentHtml !== ''
                            ? $activityContentHtml
                            : ($activityDescriptionHtml !== ''
                                ? $activityDescriptionHtml
                                : e(t('activity.detail.content_fallback'))); ?>
                    </p>
                </div>

                <!-- <div class="rounded-[2rem] border border-slate-100 bg-white p-6 md:p-8 shadow-[0_18px_45px_rgba(15,23,42,0.08)]">
                    <h2 class="text-lg md:text-xl font-black text-slate-800 mb-5">Hình ảnh hoạt động</h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        <img src="https://images.unsplash.com/photo-1511632765486-a01980e01a18?w=300" class="rounded-2xl h-28 md:h-36 w-full object-cover hover:scale-[1.03] transition-all">
                        <img src="https://images.unsplash.com/photo-1526726533690-069a7974e643?w=300" class="rounded-2xl h-28 md:h-36 w-full object-cover hover:scale-[1.03] transition-all">
                        <img src="https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?w=300" class="rounded-2xl h-28 md:h-36 w-full object-cover hover:scale-[1.03] transition-all">
                    </div>
                </div> -->
            </div>

            <div class="lg:col-span-4">
                <div class="sticky top-24 rounded-[2rem] border border-slate-100 bg-white p-6 shadow-[0_18px_45px_rgba(15,23,42,0.08)] overflow-hidden">
                    <div class="absolute top-0 right-0 w-20 h-20 bg-rose-50 rounded-bl-full -z-10"></div>
                    
                    <div class="space-y-5">
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2"><?= e(t('activity.detail.time')); ?></p>
                            <div class="flex items-center gap-2.5 font-black text-slate-800 text-sm md:text-base">
                                <i class="fa-solid fa-clock text-rose-500 text-lg"></i>
                                <?= e($activityTime . ' - ' . $activityDate); ?>
                            </div>
                        </div>

                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2"><?= e(t('activity.detail.location')); ?></p>
                            <div class="flex items-center gap-2.5 font-black text-slate-800 leading-snug text-sm md:text-base">
                                <i class="fa-solid fa-location-dot text-rose-500 text-lg"></i>
                                <?= e($activityLocation !== '' ? $activityLocation : '---'); ?>
                            </div>
                        </div>

                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2"><?= e(t('activity.detail.cost')); ?></p>
                            <div class="text-xl md:text-2xl font-black text-emerald-600">
                                <?= $activityFee > 0 ? e(t('activity.detail.fee_per_student', ['fee' => number_format($activityFee)])) : e(t('activity.detail.free')); ?>
                            </div>
                        </div>

                        <form method="post" action="/api/index.php?action=do-register-activity" class="space-y-3"<?= $registerOnsubmit !== '' ? ' onsubmit="' . e($registerOnsubmit) . '"' : ''; ?>>
                            <?= csrf_input(); ?>
                            <input type="hidden" name="activity_id" value="<?= $activityId; ?>">
                            <button type="submit" class="w-full bg-rose-600 hover:bg-rose-700 text-white font-black py-4 rounded-2xl shadow-xl shadow-rose-600/20 transition-all hover:-translate-y-1 uppercase tracking-widest text-sm disabled:cursor-not-allowed disabled:opacity-50">
                                <?= e(t('activity.detail.register_now')); ?>
                            </button>
                        </form>

                        <?php if (!$isLoggedIn): ?>
                            <p class="text-center text-[10px] text-slate-400 font-bold uppercase"><?= e(t('activity.detail.login_notice')); ?></p>
                        <?php endif; ?>

                        <p class="text-center text-[10px] text-slate-400 font-bold uppercase"><?= e(t('activity.detail.group_offer')); ?></p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>

<?php require __DIR__ . '/../../notification/confirm_modal.php'; ?>
