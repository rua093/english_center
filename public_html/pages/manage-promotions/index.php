<?php
require_admin_or_staff();
require_any_permission(['finance.promotions.view']);

$academicModel = new AcademicModel();
$lookups = $academicModel->registrationLookups();
$courses = is_array($lookups['courses'] ?? null) ? $lookups['courses'] : [];

$promotionPage = max(1, (int) ($_GET['promotion_page'] ?? 1));
$promotionPerPage = ui_pagination_resolve_per_page('promotion_per_page', 10);
$promotionTotal = $academicModel->countPromotions();
$promotionTotalPages = max(1, (int) ceil($promotionTotal / $promotionPerPage));
if ($promotionPage > $promotionTotalPages) {
    $promotionPage = $promotionTotalPages;
}
$promotions = $academicModel->listPromotionsPage($promotionPage, $promotionPerPage);
$promotionPerPageOptions = ui_pagination_per_page_options();

$editingPromotion = null;
if (!empty($_GET['edit'])) {
    $editingPromotion = $academicModel->findPromotion((int) $_GET['edit']);
}

$module = 'promotions';
$adminTitle = 'Quản lý ưu đãi';

$viewer = auth_user();
$isAdmin = (($viewer['role'] ?? '') === 'admin');
$canManagePromotion = $isAdmin;
$canDeletePromotion = $isAdmin;
$usesPromotionSchema = $academicModel->usesPromotionSchema();

$success = get_flash('success');
$error = get_flash('error');

$promoTypeOptions = [
    'DURATION' => 'Ưu đãi đăng ký lâu dài',    // Thường Duration gắn liền với việc đăng ký sớm/đúng hạn
    'SOCIAL'   => 'Ưu đãi hỗ trợ hộ khó khăn',  // Thay cho "truyền thông" nghe quá vĩ mô
    'EVENT'    => 'Ưu đãi dịp lễ/sự kiện',
    'GROUP'    => 'Ưu đãi đăng ký nhóm',
];
$selectedCourseId = max(0, (int) ($editingPromotion['course_id'] ?? 0));
$selectedName = trim((string) ($editingPromotion['name'] ?? ''));
$selectedPromoType = strtoupper(trim((string) ($editingPromotion['promo_type'] ?? 'DURATION')));
if (!isset($promoTypeOptions[$selectedPromoType])) {
    $selectedPromoType = 'DURATION';
}
$selectedDiscountValue = (float) ($editingPromotion['discount_value'] ?? 0);
$selectedStartDate = trim((string) ($editingPromotion['start_date'] ?? ''));
$selectedEndDate = trim((string) ($editingPromotion['end_date'] ?? ''));

$today = date('Y-m-d');
?>
<div class="grid gap-4">
    <?php if ($success): ?>
        <div class="rounded-xl border-l-4 border-emerald-500 bg-emerald-50 p-3 text-sm text-emerald-700"><?= e($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="rounded-xl border-l-4 border-rose-500 bg-rose-50 p-3 text-sm text-rose-700"><?= e($error); ?></div>
    <?php endif; ?>

    <?php if (!$usesPromotionSchema): ?>
        <div class="rounded-xl border-l-4 border-amber-500 bg-amber-50 p-3 text-sm text-amber-800">
            Hệ thống đang chạy trên schema ưu đãi cũ. Bạn vẫn có thể CRUD cơ bản, nhưng thời gian hiệu lực và một số thuộc tính nâng cao chỉ hoạt động đầy đủ sau khi chạy migration mới.
        </div>
    <?php endif; ?>

    <?php if ($canManagePromotion): ?>
        <article class="order-2 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3><?= $editingPromotion ? 'Sửa ưu đãi' : 'Thêm ưu đãi'; ?></h3>
            <form class="grid gap-3 md:grid-cols-2" method="post" action="/api/promotions/save">
                <?= csrf_input(); ?>
                <input type="hidden" name="id" value="<?= (int) ($editingPromotion['id'] ?? 0); ?>">

                <label>
                    Phạm vi áp dụng
                    <select name="course_id" <?= $usesPromotionSchema ? '' : 'required'; ?>>
                        <?php if ($usesPromotionSchema): ?>
                            <option value="0" <?= $selectedCourseId === 0 ? 'selected' : ''; ?>>Toàn trung tâm</option>
                        <?php else: ?>
                            <option value="" <?= $selectedCourseId <= 0 ? 'selected' : ''; ?>>-- Chọn khóa học --</option>
                        <?php endif; ?>
                        <?php foreach ($courses as $course): ?>
                            <?php $courseId = (int) ($course['id'] ?? 0); ?>
                            <option value="<?= $courseId; ?>" <?= $selectedCourseId === $courseId ? 'selected' : ''; ?>>
                                <?= e((string) ($course['course_name'] ?? ('Khóa #' . $courseId))); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!$usesPromotionSchema): ?>
                        <small class="mt-1 block text-xs text-slate-500">Schema hiện tại chỉ hỗ trợ ưu đãi theo từng khóa học, chưa hỗ trợ phạm vi toàn trung tâm.</small>
                    <?php endif; ?>
                </label>

                <label>
                    Tên ưu đãi
                    <input type="text" name="name" value="<?= e($selectedName); ?>" required>
                </label>

                <label>
                    Loại ưu đãi
                    <select name="promo_type" required>
                        <?php foreach ($promoTypeOptions as $promoTypeValue => $promoTypeLabel): ?>
                            <option value="<?= e($promoTypeValue); ?>" <?= $selectedPromoType === $promoTypeValue ? 'selected' : ''; ?>><?= e($promoTypeLabel); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    Mức giảm (%)
                    <input type="number" step="0.01" min="0" max="100" name="discount_value" value="<?= e(rtrim(rtrim(number_format($selectedDiscountValue, 2, '.', ''), '0'), '.')); ?>" required>
                </label>

                <label>
                    Bắt đầu hiệu lực
                    <input type="date" name="start_date" value="<?= e($selectedStartDate); ?>">
                </label>

                <label>
                    Kết thúc hiệu lực
                    <input type="date" name="end_date" value="<?= e($selectedEndDate); ?>">
                </label>

                <div class="md:col-span-2 inline-flex flex-wrap items-center gap-2">
                    <button class="<?= ui_btn_primary_classes(); ?>" type="submit"><?= $editingPromotion ? 'Cập nhật ưu đãi' : 'Tạo ưu đãi'; ?></button>
                    <?php if ($editingPromotion): ?>
                        <a class="<?= ui_btn_secondary_classes(); ?>" href="<?= e(page_url('promotions-manage')); ?>">Hủy chỉnh sửa</a>
                    <?php endif; ?>
                </div>
            </form>
        </article>
    <?php endif; ?>

    <article class="order-1 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3>Danh sách ưu đãi</h3>
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full border-collapse text-sm">
                <thead>
                    <tr>
                        <th>Tên ưu đãi</th>
                        <th>Phạm vi</th>
                        <th>Loại</th>
                        <th>Giảm (%)</th>
                        <th>Hiệu lực</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($promotions)): ?>
                        <tr>
                            <td colspan="7">
                                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">Chưa có ưu đãi nào.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($promotions as $promotion): ?>
                            <?php
                            $promotionId = (int) ($promotion['id'] ?? 0);
                            $courseId = max(0, (int) ($promotion['course_id'] ?? 0));
                            $courseName = trim((string) ($promotion['course_name'] ?? ''));
                            $scopeLabel = $courseId > 0
                                ? ($courseName !== '' ? $courseName : ('Khóa #' . $courseId))
                                : 'Toàn trung tâm';

                            $promoType = strtoupper(trim((string) ($promotion['promo_type'] ?? 'DURATION')));
                            $promoTypeLabel = (string) ($promoTypeOptions[$promoType] ?? $promoType);

                            $discountValue = max(0, min(100, (float) ($promotion['discount_value'] ?? 0)));
                            $discountText = rtrim(rtrim(number_format($discountValue, 2, '.', ''), '0'), '.');

                            $startDate = trim((string) ($promotion['start_date'] ?? ''));
                            $endDate = trim((string) ($promotion['end_date'] ?? ''));

                            if ($startDate !== '' && $endDate !== '') {
                                $effectiveText = $startDate . ' - ' . $endDate;
                            } elseif ($startDate !== '') {
                                $effectiveText = 'Từ ' . $startDate;
                            } elseif ($endDate !== '') {
                                $effectiveText = 'Đến ' . $endDate;
                            } else {
                                $effectiveText = 'Không giới hạn';
                            }

                            $isActive = ($startDate === '' || $startDate <= $today)
                                && ($endDate === '' || $endDate >= $today);
                            ?>
                            <tr>
                                <td><?= e((string) ($promotion['name'] ?? ('Ưu đãi #' . $promotionId))); ?></td>
                                <td><?= e($scopeLabel); ?></td>
                                <td><?= e($promoTypeLabel); ?></td>
                                <td><?= e($discountText); ?>%</td>
                                <td><?= e($effectiveText); ?></td>
                                <td>
                                    <?php if ($isActive): ?>
                                        <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">Đang hiệu lực</span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">Ngoài hiệu lực</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="inline-flex flex-wrap items-center gap-2">
                                        <?php if ($canManagePromotion): ?>
                                            <a href="<?= e(page_url('promotions-manage', ['edit' => $promotionId, 'promotion_page' => $promotionPage, 'promotion_per_page' => $promotionPerPage])); ?>">Sửa</a>
                                        <?php endif; ?>
                                        <?php if ($canDeletePromotion): ?>
                                            <form class="inline-block" method="post" action="/api/promotions/delete?id=<?= $promotionId; ?>" onsubmit="return confirm('Có chắc không?')">
                                                <?= csrf_input(); ?>
                                                <button class="<?= ui_btn_danger_classes('sm'); ?>" type="submit">Xóa</button>
                                            </form>
                                        <?php endif; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <?php if ($promotionTotal > 0): ?>
                <div class="border-t border-slate-200 bg-slate-50/80 px-3 py-2">
                    <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-slate-600">
                        <span class="font-medium">Trang <?= (int) $promotionPage; ?>/<?= (int) $promotionTotalPages; ?> - Tổng <?= (int) $promotionTotal; ?> ưu đãi</span>
                        <div class="inline-flex items-center gap-1.5">
                            <form class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2 py-1" method="get" action="<?= e(page_url('promotions-manage')); ?>">
                                <input type="hidden" name="page" value="promotions-manage">
                                <label class="text-[11px] font-semibold text-slate-500" for="promotion-per-page">Số dòng</label>
                                <select id="promotion-per-page" name="promotion_per_page" class="h-7 rounded-md border border-slate-200 bg-white px-2 text-xs font-semibold text-slate-700" onchange="this.form.submit()">
                                    <?php foreach ($promotionPerPageOptions as $option): ?>
                                        <option value="<?= (int) $option; ?>" <?= $promotionPerPage === (int) $option ? 'selected' : ''; ?>><?= (int) $option; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                            <?php if ($promotionPage > 1): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('promotions-manage', ['promotion_page' => $promotionPage - 1, 'promotion_per_page' => $promotionPerPage])); ?>">Trước</a>
                            <?php else: ?>
                                <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400">Trước</span>
                            <?php endif; ?>

                            <?php if ($promotionPage < $promotionTotalPages): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('promotions-manage', ['promotion_page' => $promotionPage + 1, 'promotion_per_page' => $promotionPerPage])); ?>">Sau</a>
                            <?php else: ?>
                                <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400">Sau</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </article>
</div>
