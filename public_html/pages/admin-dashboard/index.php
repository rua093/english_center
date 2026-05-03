<?php
require_role(['admin']);
require_permission('admin.dashboard.view');

$adminModel = new AdminModel();

$widgetPrefixes = [
    'revenue' => 'revenue',
    'growth' => 'growth',
    'conversion' => 'conversion',
    'feedback' => 'feedback',
    'class_status' => 'class_status',
    'tuition' => 'tuition',
    'population' => 'population',
    'class_size' => 'class_size',
    'course_popularity' => 'course_popularity',
];

$dashboardFilters = [];
foreach ($widgetPrefixes as $widgetKey => $prefix) {
    $dashboardFilters[$widgetKey] = [
        'start_month' => trim((string) ($_GET[$prefix . '_start'] ?? '')),
        'end_month' => trim((string) ($_GET[$prefix . '_end'] ?? '')),
    ];
}

$overview = $adminModel->dashboardOverviewData($dashboardFilters);

$module = 'dashboard';
$adminTitle = 'Bảng điều khiển';
$adminDescription = 'Toàn cảnh vận hành, tăng trưởng, tài chính và mức độ quan tâm của học viên.';

$success = get_flash('success');
$error = get_flash('error');

$hero = is_array($overview['hero'] ?? null) ? $overview['hero'] : [];
$periods = is_array($overview['periods'] ?? null) ? $overview['periods'] : [];
$growth = is_array($overview['growth'] ?? null) ? $overview['growth'] : [];
$leadConversion = is_array($overview['lead_conversion'] ?? null) ? $overview['lead_conversion'] : [];
$teacherConversion = is_array($overview['teacher_conversion'] ?? null) ? $overview['teacher_conversion'] : [];
$feedback = is_array($overview['feedback'] ?? null) ? $overview['feedback'] : [];
$classStatus = is_array($overview['class_status'] ?? null) ? $overview['class_status'] : [];
$revenueHistory = is_array($overview['revenue_history'] ?? null) ? $overview['revenue_history'] : [];
$tuition = is_array($overview['tuition'] ?? null) ? $overview['tuition'] : [];
$population = is_array($overview['population'] ?? null) ? $overview['population'] : [];
$classSize = is_array($overview['class_size_distribution'] ?? null) ? $overview['class_size_distribution'] : [];
$coursePopularity = is_array($overview['course_popularity'] ?? null) ? $overview['course_popularity'] : [];

$reportGeneratedAt = date('d/m/Y H:i');
$revenueLabels = is_array($revenueHistory['labels'] ?? null) ? $revenueHistory['labels'] : [];
$revenueValues = is_array($revenueHistory['values'] ?? null) ? $revenueHistory['values'] : [];
$revenueLatest = (float) ($revenueHistory['latest'] ?? 0);
$revenuePrevious = (float) ($revenueHistory['previous'] ?? 0);
$revenueDelta = $revenueLatest - $revenuePrevious;
$revenueDeltaPercent = $revenuePrevious > 0 ? round(($revenueDelta / $revenuePrevious) * 100, 1) : ($revenueLatest > 0 ? 100.0 : 0.0);
$collectionRate = (float) ($tuition['collection_rate'] ?? 0);
$classStatusLabels = ['Sắp mở', 'Đang học', 'Hoàn thành', 'Đã hủy'];
$classStatusValues = [
    (int) ($classStatus['upcoming'] ?? 0),
    (int) ($classStatus['active'] ?? 0),
    (int) ($classStatus['graduated'] ?? 0),
    (int) ($classStatus['cancelled'] ?? 0),
];

$queryParams = $_GET;
$renderHiddenParams = static function (array $excludeKeys) use ($queryParams): string {
    $html = '';
    foreach ($queryParams as $key => $value) {
        if (in_array((string) $key, $excludeKeys, true) || is_array($value)) {
            continue;
        }
        $html .= '<input type="hidden" name="' . e((string) $key) . '" value="' . e((string) $value) . '">';
    }
    return $html;
};

$renderPeriodForm = static function (string $widgetKey, string $prefix, array $period) use ($renderHiddenParams): string {
    $startMonth = (string) ($period['start_month'] ?? date('Y-m'));
    $endMonth = (string) ($period['end_month'] ?? date('Y-m'));
    $excludeKeys = [$prefix . '_start', $prefix . '_end'];
    ob_start();
    ?>
    <form class="js-dashboard-period-form mt-3 flex flex-wrap items-end gap-2" method="get" action="<?= e(page_url('dashboard-admin')); ?>">
        <?= $renderHiddenParams($excludeKeys); ?>
        <label class="flex min-w-0 flex-col gap-1 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">
            <span>Từ tháng</span>
            <input class="w-full min-w-[150px] rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100" type="month" name="<?= e($prefix . '_start'); ?>" value="<?= e($startMonth); ?>">
        </label>
        <label class="flex min-w-0 flex-col gap-1 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">
            <span>Đến tháng</span>
            <input class="w-full min-w-[150px] rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100" type="month" name="<?= e($prefix . '_end'); ?>" value="<?= e($endMonth); ?>">
        </label>
        <button class="inline-flex h-[42px] items-center justify-center rounded-xl bg-slate-900 px-4 text-sm font-black text-white transition hover:bg-cyan-600" type="submit">
            Xem
        </button>
    </form>
    <?php
    return (string) ob_get_clean();
};

$dashboardPayload = [
    'revenue' => [
        'labels' => $revenueLabels,
        'values' => $revenueValues,
    ],
    'growth' => [
        'labels' => is_array($growth['labels'] ?? null) ? $growth['labels'] : [],
        'students' => is_array($growth['students'] ?? null) ? $growth['students'] : [],
        'teachers' => is_array($growth['teachers'] ?? null) ? $growth['teachers'] : [],
    ],
    'conversion' => [
        'labels' => ['Học viên', 'Giáo viên'],
        'rates' => [
            (float) ($leadConversion['conversion_rate'] ?? 0),
            (float) ($teacherConversion['conversion_rate'] ?? 0),
        ],
    ],
    'feedback' => [
        'labels' => ['1 sao', '2 sao', '3 sao', '4 sao', '5 sao'],
        'values' => is_array($feedback['distribution'] ?? null) ? $feedback['distribution'] : [0, 0, 0, 0, 0],
    ],
    'classStatus' => [
        'labels' => $classStatusLabels,
        'values' => $classStatusValues,
    ],
    'tuition' => [
        'labels' => ['Đã thu', 'Còn nợ'],
        'values' => [
            (float) ($tuition['amount_paid'] ?? 0),
            (float) ($tuition['amount_debt'] ?? 0),
        ],
    ],
    'population' => [
        'labels' => ['Học viên', 'Giáo viên', 'Lớp học', 'Khóa học'],
        'values' => [
            (int) ($population['total_students'] ?? 0),
            (int) ($population['total_teachers'] ?? 0),
            (int) ($population['total_classes'] ?? 0),
            (int) ($population['total_courses'] ?? 0),
        ],
    ],
    'classSize' => [
        'labels' => is_array($classSize['labels'] ?? null) ? $classSize['labels'] : [],
        'values' => is_array($classSize['values'] ?? null) ? $classSize['values'] : [],
    ],
    'coursePopularity' => [
        'labels' => is_array($coursePopularity['labels'] ?? null) ? $coursePopularity['labels'] : [],
        'classCounts' => is_array($coursePopularity['class_counts'] ?? null) ? $coursePopularity['class_counts'] : [],
        'enrollmentCounts' => is_array($coursePopularity['enrollment_counts'] ?? null) ? $coursePopularity['enrollment_counts'] : [],
    ],
];

$heroCards = [
    [
        'label' => 'Học viên mới tháng này',
        'value' => (string) ($hero['students_new'] ?? 0),
        'note' => (($hero['students_delta'] ?? 0) >= 0 ? '+' : '') . (int) ($hero['students_delta'] ?? 0) . ' so với tháng trước',
        'tone' => 'from-rose-500/25 to-pink-500/10 border-rose-300/25',
    ],
    [
        'label' => 'Giáo viên mới tháng này',
        'value' => (string) ($hero['teachers_new'] ?? 0),
        'note' => (($hero['teachers_delta'] ?? 0) >= 0 ? '+' : '') . (int) ($hero['teachers_delta'] ?? 0) . ' so với tháng trước',
        'tone' => 'from-amber-400/25 to-orange-500/10 border-amber-300/25',
    ],
    [
        'label' => 'Chuyển đổi học viên',
        'value' => number_format((float) ($hero['lead_conversion_rate'] ?? 0), 1, ',', '.') . '%',
        'note' => 'Tỷ lệ tổng thể từ lead sang học viên',
        'tone' => 'from-emerald-400/25 to-lime-400/10 border-emerald-300/25',
    ],
    [
        'label' => 'Đánh giá trung bình',
        'value' => number_format((float) ($hero['avg_rating'] ?? 0), 1, ',', '.') . '/5',
        'note' => (int) ($hero['feedback_total'] ?? 0) . ' lượt đánh giá đã ghi nhận',
        'tone' => 'from-sky-400/25 to-cyan-400/10 border-sky-300/25',
    ],
];
?>

<div id="adminDashboardRoot" class="grid min-w-0 gap-5">
    <?php if ($success): ?>
        <div class="rounded-xl border-l-4 border-emerald-500 bg-emerald-50 p-3 text-sm text-emerald-700"><?= e($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="rounded-xl border-l-4 border-rose-500 bg-rose-50 p-3 text-sm text-rose-700"><?= e($error); ?></div>
    <?php endif; ?>

    <section class="overflow-hidden rounded-[2rem] border border-slate-200 bg-[radial-gradient(circle_at_top_left,_rgba(251,113,133,0.28),_transparent_35%),radial-gradient(circle_at_top_right,_rgba(34,211,238,0.24),_transparent_32%),linear-gradient(135deg,_#161032_0%,_#1d1b5f_42%,_#0f3b68_100%)] p-5 text-white shadow-[0_30px_80px_rgba(15,23,42,0.24)]">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
            <div class="min-w-0 flex-1">
                <span class="inline-flex items-center rounded-full border border-white/15 bg-white/10 px-3 py-1 text-[11px] font-black uppercase tracking-[0.24em] text-cyan-100">Điều hành trung tâm</span>
                <h2 class="mt-3 max-w-4xl text-3xl font-black tracking-tight text-white md:text-4xl">Toàn cảnh trung tâm dưới dạng biểu đồ, dễ nhìn và dễ kiểm soát theo từng giai đoạn.</h2>
                <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-100/90">Mỗi biểu đồ bên dưới có bộ chọn tháng riêng để bạn soi đúng khoảng thời gian cần xem, từ tài chính, tăng trưởng, chuyển đổi cho tới độ phổ biến của khóa học.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center rounded-full border border-white/15 bg-white/10 px-4 py-2 text-sm font-bold text-white/95">Cập nhật lúc <?= e($reportGeneratedAt); ?></span>
                <?php if (can_access_page('classes-academic')): ?>
                    <a class="inline-flex items-center rounded-full border border-white/15 bg-white px-4 py-2 text-sm font-black text-slate-900 transition hover:bg-cyan-200" href="<?= e(page_url('classes-academic')); ?>">Đi tới học vụ</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
            <?php foreach ($heroCards as $card): ?>
                <article class="rounded-[1.4rem] border bg-gradient-to-br <?= e((string) $card['tone']); ?> p-4 backdrop-blur-sm">
                    <p class="text-[11px] font-black uppercase tracking-[0.22em] text-white/70"><?= e((string) $card['label']); ?></p>
                    <p class="mt-2 text-3xl font-black text-white"><?= e((string) $card['value']); ?></p>
                    <p class="mt-2 text-xs leading-5 text-white/75"><?= e((string) $card['note']); ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="grid gap-5 xl:grid-cols-2">
        <article class="rounded-[1.8rem] border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] font-black uppercase tracking-[0.22em] text-cyan-600">Doanh thu</p>
                    <h3 class="mt-1 text-xl font-black text-slate-900">Lịch sử doanh thu học phí</h3>
                    <p class="mt-1 text-sm text-slate-500">Xem trung tâm đã thu vào bao nhiêu theo từng tháng trong khoảng bạn chọn.</p>
                </div>
                <span class="inline-flex items-center rounded-full bg-cyan-50 px-3 py-1 text-xs font-bold text-cyan-700"><?= e((string) ($periods['revenue']['label'] ?? '')); ?></span>
            </div>
            <?= $renderPeriodForm('revenue', 'revenue', $periods['revenue'] ?? []); ?>
            <div class="mt-4 grid gap-3 md:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-[11px] font-black uppercase tracking-[0.2em] text-slate-500">Tháng gần nhất</p>
                    <p class="mt-2 text-2xl font-black text-slate-900"><?= e(format_money($revenueLatest)); ?></p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-[11px] font-black uppercase tracking-[0.2em] text-slate-500">Biến động</p>
                    <p class="mt-2 text-2xl font-black <?= $revenueDelta >= 0 ? 'text-emerald-600' : 'text-rose-600'; ?>"><?= e(($revenueDelta >= 0 ? '+' : '-') . number_format(abs($revenueDeltaPercent), 1, ',', '.') . '%'); ?></p>
                </div>
            </div>
            <div class="mt-4 overflow-x-auto">
                <div class="h-[260px]" style="min-width: <?= max(680, count($revenueLabels) * 72); ?>px;">
                    <canvas id="dashboardRevenueChart"></canvas>
                </div>
            </div>
        </article>

        <article class="rounded-[1.8rem] border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] font-black uppercase tracking-[0.22em] text-emerald-600">Học phí</p>
                    <h3 class="mt-1 text-xl font-black text-slate-900">Đã thu và còn nợ</h3>
                    <p class="mt-1 text-sm text-slate-500">Nhìn nhanh sức khỏe tài chính của học phí trong giai đoạn đã chọn.</p>
                </div>
                <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700"><?= e((string) ($periods['tuition']['label'] ?? '')); ?></span>
            </div>
            <?= $renderPeriodForm('tuition', 'tuition', $periods['tuition'] ?? []); ?>
            <div class="mt-4 grid gap-3 md:grid-cols-2">
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                    <p class="text-[11px] font-black uppercase tracking-[0.2em] text-emerald-700">Đã thu</p>
                    <p class="mt-2 text-2xl font-black text-emerald-950"><?= e(format_money((float) ($tuition['amount_paid'] ?? 0))); ?></p>
                </div>
                <div class="rounded-2xl border border-orange-200 bg-orange-50 p-4">
                    <p class="text-[11px] font-black uppercase tracking-[0.2em] text-orange-700">Còn nợ</p>
                    <p class="mt-2 text-2xl font-black text-orange-950"><?= e(format_money((float) ($tuition['amount_debt'] ?? 0))); ?></p>
                </div>
            </div>
            <div class="mt-4 h-[260px]">
                <canvas id="dashboardTuitionChart"></canvas>
            </div>
            <p class="mt-3 text-sm font-semibold text-slate-600">Tỷ lệ thu học phí: <span class="font-black text-slate-900"><?= e(number_format($collectionRate, 1, ',', '.')); ?>%</span></p>
        </article>
    </section>

    <section class="grid gap-5 xl:grid-cols-2">
        <article class="rounded-[1.8rem] border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] font-black uppercase tracking-[0.22em] text-fuchsia-600">Tăng trưởng</p>
                    <h3 class="mt-1 text-xl font-black text-slate-900">Học viên và giáo viên mới</h3>
                    <p class="mt-1 text-sm text-slate-500">So sánh nhịp tăng trưởng giữa hai nhóm theo từng tháng.</p>
                </div>
                <span class="inline-flex items-center rounded-full bg-fuchsia-50 px-3 py-1 text-xs font-bold text-fuchsia-700"><?= e((string) ($periods['growth']['label'] ?? '')); ?></span>
            </div>
            <?= $renderPeriodForm('growth', 'growth', $periods['growth'] ?? []); ?>
            <div class="mt-4 h-[280px]">
                <canvas id="dashboardGrowthChart"></canvas>
            </div>
        </article>

        <article class="rounded-[1.8rem] border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] font-black uppercase tracking-[0.22em] text-violet-600">Chuyển đổi</p>
                    <h3 class="mt-1 text-xl font-black text-slate-900">Học viên và giáo viên đăng ký</h3>
                    <p class="mt-1 text-sm text-slate-500">Đo mức hiệu quả chuyển đổi của trung tâm ở đầu vào.</p>
                </div>
                <span class="inline-flex items-center rounded-full bg-violet-50 px-3 py-1 text-xs font-bold text-violet-700"><?= e((string) ($periods['conversion']['label'] ?? '')); ?></span>
            </div>
            <?= $renderPeriodForm('conversion', 'conversion', $periods['conversion'] ?? []); ?>
            <div class="mt-4 h-[280px]">
                <canvas id="dashboardConversionChart"></canvas>
            </div>
            <div class="mt-4 grid gap-3 md:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">
                    Tỉ lệ chuyển đổi thành học viên chính thức: <strong><?= (int) ($leadConversion['converted'] ?? 0); ?></strong> / <?= (int) ($leadConversion['total'] ?? 0); ?>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">
                    Tỉ lệ chuyển đổi thành giáo viên chính thức: <strong><?= (int) ($teacherConversion['converted'] ?? 0); ?></strong> / <?= (int) ($teacherConversion['total'] ?? 0); ?>
                </div>
            </div>
        </article>
    </section>

    <section class="grid gap-5 xl:grid-cols-2">
        <article class="rounded-[1.8rem] border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] font-black uppercase tracking-[0.22em] text-sky-600">Đánh giá học viên</p>
                    <h3 class="mt-1 text-xl font-black text-slate-900">Phân bố mức sao</h3>
                    <p class="mt-1 text-sm text-slate-500">Nhìn cảm nhận chung của học viên về trung tâm trong khoảng tháng đã chọn.</p>
                </div>
                <span class="inline-flex items-center rounded-full bg-sky-50 px-3 py-1 text-xs font-bold text-sky-700"><?= e((string) ($periods['feedback']['label'] ?? '')); ?></span>
            </div>
            <?= $renderPeriodForm('feedback', 'feedback', $periods['feedback'] ?? []); ?>
            <div class="mt-4 h-[280px]">
                <canvas id="dashboardFeedbackChart"></canvas>
            </div>
            <p class="mt-3 text-sm font-semibold text-slate-600">Điểm trung bình: <span class="font-black text-slate-900"><?= e(number_format((float) ($feedback['avg_rating'] ?? 0), 1, ',', '.')); ?>/5</span></p>
        </article>

        <article class="rounded-[1.8rem] border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] font-black uppercase tracking-[0.22em] text-indigo-600">Lớp học</p>
                    <h3 class="mt-1 text-xl font-black text-slate-900">Trạng thái lớp học</h3>
                    <p class="mt-1 text-sm text-slate-500">Cho biết các lớp mở trong giai đoạn đang nằm ở trạng thái nào.</p>
                </div>
                <span class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-bold text-indigo-700"><?= e((string) ($periods['class_status']['label'] ?? '')); ?></span>
            </div>
            <?= $renderPeriodForm('class_status', 'class_status', $periods['class_status'] ?? []); ?>
            <div class="mt-4 h-[280px]">
                <canvas id="dashboardClassStatusChart"></canvas>
            </div>
            <p class="mt-3 text-sm font-semibold text-slate-600">Tổng số lớp trong giai đoạn: <span class="font-black text-slate-900"><?= (int) ($classStatus['total_classes'] ?? array_sum($classStatusValues)); ?></span></p>
        </article>
    </section>

    <section class="grid gap-5 xl:grid-cols-2">
        <article class="rounded-[1.8rem] border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] font-black uppercase tracking-[0.22em] text-orange-600">Quy mô</p>
                    <h3 class="mt-1 text-xl font-black text-slate-900">Học viên, giáo viên, lớp và khóa học</h3>
                    <p class="mt-1 text-sm text-slate-500">Ảnh chụp số lượng phát sinh trong khoảng tháng đang được xem.</p>
                </div>
                <span class="inline-flex items-center rounded-full bg-orange-50 px-3 py-1 text-xs font-bold text-orange-700"><?= e((string) ($periods['population']['label'] ?? '')); ?></span>
            </div>
            <?= $renderPeriodForm('population', 'population', $periods['population'] ?? []); ?>
            <div class="mt-4 h-[280px]">
                <canvas id="dashboardPopulationChart"></canvas>
            </div>
            <p class="mt-3 text-sm font-semibold text-slate-600">Sĩ số trung bình: <span class="font-black text-slate-900"><?= e(number_format((float) ($population['avg_students_per_class'] ?? 0), 1, ',', '.')); ?></span> học viên/lớp</p>
        </article>

        <article class="rounded-[1.8rem] border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] font-black uppercase tracking-[0.22em] text-rose-600">Sĩ số lớp</p>
                    <h3 class="mt-1 text-xl font-black text-slate-900">Phân bố lớp theo ngưỡng sĩ số</h3>
                    <p class="mt-1 text-sm text-slate-500">Giúp thấy ngay lớp nào còn mỏng và lớp nào đã đông trong giai đoạn đó.</p>
                </div>
                <span class="inline-flex items-center rounded-full bg-rose-50 px-3 py-1 text-xs font-bold text-rose-700"><?= e((string) ($periods['class_size']['label'] ?? '')); ?></span>
            </div>
            <?= $renderPeriodForm('class_size', 'class_size', $periods['class_size'] ?? []); ?>
            <div class="mt-4 h-[280px]">
                <canvas id="dashboardClassSizeChart"></canvas>
            </div>
        </article>
    </section>

    <section class="rounded-[1.8rem] border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0 flex-1">
                <p class="text-[11px] font-black uppercase tracking-[0.22em] text-teal-600">Độ phổ biến khóa học</p>
                <h3 class="mt-1 text-xl font-black text-slate-900">Khóa học nào đang được ưa chuộng nhất</h3>
                <p class="mt-1 text-sm text-slate-500">So sánh số lớp mở ra và số học viên đăng ký theo từng khóa học trong cùng một khoảng xem.</p>
            </div>
            <span class="inline-flex items-center rounded-full bg-teal-50 px-3 py-1 text-xs font-bold text-teal-700"><?= e((string) ($periods['course_popularity']['label'] ?? '')); ?></span>
        </div>
        <?= $renderPeriodForm('course_popularity', 'course_popularity', $periods['course_popularity'] ?? []); ?>
        <div class="mt-4 overflow-x-auto">
            <div class="h-[340px]" style="min-width: <?= max(780, count((array) ($coursePopularity['labels'] ?? [])) * 90); ?>px;">
                <canvas id="dashboardCoursePopularityChart"></canvas>
            </div>
        </div>
    </section>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    (function () {
        const payload = <?= json_encode($dashboardPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
        if (typeof Chart === 'undefined') {
            return;
        }

        Chart.defaults.font.family = 'Manrope, system-ui, sans-serif';
        Chart.defaults.color = '#475569';

        function formatMoney(value) {
            return Number(value || 0).toLocaleString('vi-VN') + ' đ';
        }

        function createChart(canvasId, config) {
            const canvas = document.getElementById(canvasId);
            if (!(canvas instanceof HTMLCanvasElement)) {
                return null;
            }
            return new Chart(canvas, config);
        }

        createChart('dashboardRevenueChart', {
            type: 'bar',
            data: {
                labels: payload.revenue.labels || [],
                datasets: [{
                    label: 'Doanh thu',
                    data: payload.revenue.values || [],
                    borderRadius: 12,
                    borderSkipped: false,
                    backgroundColor: Array.from({ length: Math.max(1, (payload.revenue.values || []).length) }, function (_, index) {
                        const palette = ['#f43f5e', '#fb7185', '#f97316', '#f59e0b', '#14b8a6', '#06b6d4', '#3b82f6', '#8b5cf6'];
                        return palette[index % palette.length];
                    }),
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label(context) {
                                return 'Doanh thu: ' + formatMoney(context.parsed.y);
                            },
                        },
                    },
                },
                scales: {
                    x: { grid: { display: false } },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback(value) {
                                return Number(value).toLocaleString('vi-VN');
                            },
                        },
                    },
                },
            },
        });

        createChart('dashboardTuitionChart', {
            type: 'doughnut',
            data: {
                labels: payload.tuition.labels || [],
                datasets: [{
                    data: payload.tuition.values || [],
                    backgroundColor: ['#14b8a6', '#fb923c'],
                    borderColor: '#ffffff',
                    borderWidth: 4,
                    hoverOffset: 8,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            pointStyle: 'circle',
                            padding: 16,
                        },
                    },
                    tooltip: {
                        callbacks: {
                            label(context) {
                                return context.label + ': ' + formatMoney(context.raw);
                            },
                        },
                    },
                },
            },
        });

        createChart('dashboardGrowthChart', {
            type: 'line',
            data: {
                labels: payload.growth.labels || [],
                datasets: [
                    {
                        label: 'Học viên mới',
                        data: payload.growth.students || [],
                        borderColor: '#ec4899',
                        backgroundColor: 'rgba(236,72,153,0.18)',
                        pointBackgroundColor: '#ec4899',
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        tension: 0.35,
                        fill: true,
                    },
                    {
                        label: 'Giáo viên mới',
                        data: payload.growth.teachers || [],
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245,158,11,0.12)',
                        pointBackgroundColor: '#f59e0b',
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        tension: 0.35,
                        fill: true,
                    }
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            pointStyle: 'circle',
                            padding: 16,
                        },
                    },
                },
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true, ticks: { precision: 0 } },
                },
            },
        });

        createChart('dashboardConversionChart', {
            type: 'bar',
            data: {
                labels: payload.conversion.labels || [],
                datasets: [{
                    label: 'Tỷ lệ chuyển đổi (%)',
                    data: payload.conversion.rates || [],
                    backgroundColor: ['#8b5cf6', '#06b6d4'],
                    borderRadius: 14,
                    borderSkipped: false,
                    maxBarThickness: 56,
                }],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label(context) {
                                return 'Tỷ lệ: ' + Number(context.parsed.x || 0).toLocaleString('vi-VN') + '%';
                            },
                        },
                    },
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback(value) {
                                return Number(value).toLocaleString('vi-VN') + '%';
                            },
                        },
                    },
                    y: { grid: { display: false } },
                },
            },
        });

        createChart('dashboardFeedbackChart', {
            type: 'doughnut',
            data: {
                labels: payload.feedback.labels || [],
                datasets: [{
                    data: payload.feedback.values || [],
                    backgroundColor: ['#fb7185', '#fb923c', '#facc15', '#38bdf8', '#34d399'],
                    borderColor: '#ffffff',
                    borderWidth: 4,
                    hoverOffset: 8,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '58%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            pointStyle: 'circle',
                            padding: 16,
                        },
                    },
                    tooltip: {
                        callbacks: {
                            label(context) {
                                return context.label + ': ' + Number(context.raw || 0).toLocaleString('vi-VN') + ' lượt';
                            },
                        },
                    },
                },
            },
        });

        createChart('dashboardClassStatusChart', {
            type: 'doughnut',
            data: {
                labels: payload.classStatus.labels || [],
                datasets: [{
                    data: payload.classStatus.values || [],
                    backgroundColor: ['#38bdf8', '#22c55e', '#8b5cf6', '#fb7185'],
                    borderColor: '#ffffff',
                    borderWidth: 4,
                    hoverOffset: 8,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            pointStyle: 'circle',
                            padding: 16,
                        },
                    },
                },
            },
        });

        createChart('dashboardPopulationChart', {
            type: 'bar',
            data: {
                labels: payload.population.labels || [],
                datasets: [{
                    label: 'Số lượng',
                    data: payload.population.values || [],
                    backgroundColor: ['#ec4899', '#f59e0b', '#14b8a6', '#6366f1'],
                    borderRadius: 14,
                    borderSkipped: false,
                    maxBarThickness: 60,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                },
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true, ticks: { precision: 0 } },
                },
            },
        });

        createChart('dashboardClassSizeChart', {
            type: 'bar',
            data: {
                labels: payload.classSize.labels || [],
                datasets: [{
                    label: 'Số lớp',
                    data: payload.classSize.values || [],
                    backgroundColor: ['#fb7185', '#f59e0b', '#22c55e', '#6366f1'],
                    borderRadius: 14,
                    borderSkipped: false,
                    maxBarThickness: 58,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label(context) {
                                return 'Số lớp: ' + Number(context.raw || 0).toLocaleString('vi-VN');
                            },
                        },
                    },
                },
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true, ticks: { precision: 0 } },
                },
            },
        });

        createChart('dashboardCoursePopularityChart', {
            type: 'bar',
            data: {
                labels: payload.coursePopularity.labels || [],
                datasets: [
                    {
                        label: 'Số lớp',
                        data: payload.coursePopularity.classCounts || [],
                        backgroundColor: '#8b5cf6',
                        borderRadius: 12,
                        borderSkipped: false,
                    },
                    {
                        label: 'Số học viên',
                        data: payload.coursePopularity.enrollmentCounts || [],
                        backgroundColor: '#06b6d4',
                        borderRadius: 12,
                        borderSkipped: false,
                    }
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            pointStyle: 'circle',
                            padding: 16,
                        },
                    },
                },
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true, ticks: { precision: 0 } },
                },
            },
        });
    })();
</script>
</div>
<script>
    (function () {
        if (window.__adminDashboardAjaxBound) {
            return;
        }
        window.__adminDashboardAjaxBound = true;

        async function refreshDashboard(form) {
            const root = document.getElementById('adminDashboardRoot');
            if (!root) {
                form.submit();
                return;
            }

            const params = new URLSearchParams(new FormData(form));
            const action = form.getAttribute('action') || window.location.pathname;
            const requestUrl = action + (action.indexOf('?') >= 0 ? '&' : '?') + params.toString();

            root.classList.add('opacity-60', 'pointer-events-none');

            try {
                const response = await fetch(requestUrl, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                if (!response.ok) {
                    window.location.href = requestUrl;
                    return;
                }

                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const nextRoot = doc.getElementById('adminDashboardRoot');

                if (!nextRoot) {
                    window.location.href = requestUrl;
                    return;
                }

                root.replaceWith(nextRoot);
                history.replaceState({}, '', requestUrl);

                nextRoot.querySelectorAll('script').forEach(function (oldScript) {
                    const newScript = document.createElement('script');
                    Array.from(oldScript.attributes).forEach(function (attribute) {
                        newScript.setAttribute(attribute.name, attribute.value);
                    });
                    if (oldScript.src) {
                        newScript.src = oldScript.src;
                    } else {
                        newScript.textContent = oldScript.textContent;
                    }
                    oldScript.replaceWith(newScript);
                });
            } catch (error) {
                window.location.href = requestUrl;
            }
        }

        document.addEventListener('submit', function (event) {
            const form = event.target;
            if (!(form instanceof HTMLFormElement) || !form.classList.contains('js-dashboard-period-form')) {
                return;
            }

            event.preventDefault();
            refreshDashboard(form);
        });
    })();
</script>
