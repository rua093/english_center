<?php
declare(strict_types=1);

require_once __DIR__ . '/../../models/AcademicModel.php';

$academicModel = new AcademicModel();
$activityPage = max(1, (int) ($_GET['activity_page'] ?? 1));
$activityPerPage = ui_pagination_resolve_per_page('activity_per_page', 8);
$activityPerPageOptions = ui_pagination_per_page_options();
$activityStatusFilter = strtolower(trim((string) ($_GET['status'] ?? 'all')));

$activities = $academicModel->listActivities();

$activityStatuses = [
	'all' => t('activities.status.all'),
	'upcoming' => t('activities.status.upcoming'),
	'ongoing' => t('activities.status.ongoing'),
	'finished' => t('activities.status.finished'),
];
if (!array_key_exists($activityStatusFilter, $activityStatuses)) {
	$activityStatusFilter = 'all';
}

$resolveActivityImagePath = static function (string $imagePath): string {
    $imagePath = trim($imagePath);
    if ($imagePath === '') {
        return 'https://images.unsplash.com/photo-1533227268428-f9ed0900fb3b?w=600&q=80';
    }

    if (preg_match('#^(?:https?://|/)#i', $imagePath)) {
        return $imagePath;
    }

    return '/assets/uploads/' . ltrim($imagePath, '/');
};

$activities = array_values(array_filter($activities, static function (array $activity) use ($activityStatusFilter): bool {
	if ($activityStatusFilter === 'all') {
		return true;
	}

	return (string) ($activity['status'] ?? '') === $activityStatusFilter;
}));

$activityTotal = count($activities);
$activityTotalPages = max(1, (int) ceil($activityTotal / $activityPerPage));
if ($activityPage > $activityTotalPages) {
    $activityPage = $activityTotalPages;
}

$pageActivities = array_slice($activities, ($activityPage - 1) * $activityPerPage, $activityPerPage);
?>

<link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<style>
    .activity-card:hover { transform: translateY(-10px); }
    .text-gradient-red-green { background: linear-gradient(to right, #e11d48, #10b981); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
</style>


<section class="relative overflow-hidden py-12 md:py-16 bg-lime-100">
    <div class="absolute inset-0 z-0 pointer-events-none opacity-[0.08]" style="background-image: radial-gradient(#475569 1.5px, transparent 1.5px); background-size: 24px 24px;"></div>
    <div class="absolute inset-x-0 top-0 z-0 h-64 pointer-events-none bg-gradient-to-b from-lime-200/75 via-lime-100/45 to-transparent"></div>
    <div class="relative z-10">
    <div class="container mx-auto px-4 max-w-[1400px]">
        
        <div class="text-center mb-12" data-aos="fade-up">
            <span class="px-4 py-1.5 rounded-full bg-rose-50 text-rose-600 text-[10px] font-black uppercase tracking-widest border border-rose-100"><?= e(t('activities.kicker')); ?></span>
            <h1 class="text-3xl md:text-4xl font-black text-slate-900 mt-5 mb-3 uppercase">
                <?= e(t('activities.title')); ?> <span class="text-gradient-red-green"><?= e(t('activities.highlight')); ?></span>
            </h1>
            <p class="text-slate-500 max-w-xl mx-auto font-medium text-sm md:text-base"><?= e(t('activities.copy')); ?></p>
        </div>

        <div class="mb-10 flex flex-wrap justify-center gap-3" data-aos="fade-up" data-aos-delay="100">
            <?php foreach ($activityStatuses as $statusKey => $statusLabel): ?>
                <a href="<?= e(page_url('activities-home', ['status' => $statusKey, 'activity_page' => 1, 'activity_per_page' => $activityPerPage])); ?>" class="px-6 py-2.5 rounded-2xl font-black text-[10px] uppercase shadow-lg transition-all <?= $activityStatusFilter === $statusKey ? 'bg-rose-600 text-white shadow-rose-600/20' : 'bg-slate-50 text-slate-600 hover:bg-emerald-50 hover:text-emerald-600'; ?>">
                    <?= e($statusLabel); ?>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if ($pageActivities === []): ?>
            <div class="rounded-[2rem] border border-dashed border-slate-200 bg-white p-10 text-center text-sm font-semibold text-slate-500 shadow-sm" data-aos="fade-up" data-aos-delay="150">
                <?= e(t('activities.empty')); ?>
            </div>
        <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach($pageActivities as $index => $act): ?>
                <?php
                    $activityTitle = (string) ($act['activity_name'] ?? '');
                    $activityDate = !empty($act['start_date']) ? date('d/m/Y', strtotime((string) $act['start_date'])) : '---';
                    $activityLocation = (string) ($act['location'] ?? '');
                    $activityStatus = (string) ($act['status'] ?? 'upcoming');
                    $activityTag = match ($activityStatus) {
                        'ongoing' => t('activities.status.ongoing'),
                        'finished' => t('activities.status.finished'),
                        default => t('activities.status.upcoming'),
                    };
                    $activityImage = $resolveActivityImagePath((string) ($act['image_thumbnail'] ?? ''));
                    $activityFee = (float) ($act['fee'] ?? 0);
                    $activityDelay = $index * 100;
                ?>
            <a href="<?= e(page_url('activities-home-detail', ['id' => (int) $act['id']])); ?>" class="activity-card group block h-full bg-white rounded-[2rem] overflow-hidden border border-slate-100 shadow-xl shadow-slate-200/40 transition-all duration-500 hover:shadow-2xl focus:outline-none focus-visible:ring-4 focus-visible:ring-rose-100" data-aos="fade-up" data-aos-delay="<?= $activityDelay; ?>" data-aos-duration="700">
                <article>
                <div class="relative h-52 overflow-hidden">
                    <img src="<?= e($activityImage); ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                    <div class="absolute top-4 left-4">
                        <span class="bg-white/90 backdrop-blur-md text-rose-600 px-3 py-1 rounded-full text-[10px] font-black uppercase shadow-sm">
                            <?= e($activityTag); ?>
                        </span>
                    </div>
                </div>
                <div class="p-6">
                    <div class="flex items-center gap-3 text-emerald-600 text-[11px] font-bold mb-3">
                        <i class="fa-solid fa-calendar-day"></i> <?= e($activityDate); ?>
                    </div>
                    <h3 class="text-lg font-black text-slate-800 mb-3 leading-tight group-hover:text-rose-600 transition-colors">
                        <?= e($activityTitle); ?>
                    </h3>
                    <p class="text-slate-400 text-xs font-medium mb-5 flex items-center gap-2">
                        <i class="fa-solid fa-location-dot"></i> <?= e($activityLocation !== '' ? $activityLocation : '---'); ?>
                    </p>
                    <p class="mb-5 text-xs font-semibold text-slate-500">
                        <?= e(t('activities.fee')); ?>: <?= $activityFee > 0 ? e(number_format($activityFee) . ' đ') : e(t('activities.free_fee')); ?>
                    </p>
                    <div class="inline-flex items-center gap-2 font-black text-slate-900 text-sm">
                        <?= e(t('public.common.view_detail')); ?>
                        <span class="w-7 h-7 rounded-full border-2 border-slate-100 flex items-center justify-center group-hover:bg-emerald-500 group-hover:text-white group-hover:border-emerald-500 transition-all">
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </span>
                    </div>
                </div>
                </article>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <?php if ($activityTotalPages > 1): ?>
            <div class="mt-14 flex flex-wrap items-center justify-center gap-2" data-aos="fade-up" data-aos-delay="100">
                <?php if ($activityPage > 1): ?>
                    <a class="flex h-11 items-center justify-center rounded-2xl bg-white border border-slate-100 px-4 text-sm font-bold text-slate-500 hover:text-rose-600 transition-all" href="<?= e(page_url('activities-home', ['status' => $activityStatusFilter, 'activity_page' => $activityPage - 1, 'activity_per_page' => $activityPerPage])); ?>"><?= e(t('activities.previous')); ?></a>
                <?php endif; ?>

                <?php for ($page = max(1, $activityPage - 1); $page <= min($activityTotalPages, $activityPage + 1); $page++): ?>
                    <a class="w-11 h-11 rounded-2xl font-black shadow-lg flex items-center justify-center <?= $page === $activityPage ? 'bg-rose-600 text-white' : 'bg-white border border-slate-100 text-slate-400 hover:text-emerald-600'; ?> transition-all" href="<?= e(page_url('activities-home', ['status' => $activityStatusFilter, 'activity_page' => $page, 'activity_per_page' => $activityPerPage])); ?>">
                        <?= (int) $page; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($activityPage < $activityTotalPages): ?>
                    <a class="flex h-11 items-center justify-center rounded-2xl bg-white border border-slate-100 px-4 text-sm font-bold text-slate-500 hover:text-rose-600 transition-all" href="<?= e(page_url('activities-home', ['status' => $activityStatusFilter, 'activity_page' => $activityPage + 1, 'activity_per_page' => $activityPerPage])); ?>"><?= e(t('activities.next')); ?></a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    </div>
</section>

<?php include __DIR__ . '/../partials/social_contact.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof AOS === 'undefined') {
        return;
    }

    AOS.init({
        duration: 700,
        once: true,
        offset: 80
    });
});

window.addEventListener('load', function () {
    if (typeof AOS === 'undefined') {
        return;
    }

    AOS.refresh();
});
</script>
