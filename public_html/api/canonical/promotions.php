<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/api_helpers.php';
require_once __DIR__ . '/../../models/AcademicModel.php';

function api_promotions_is_admin(): bool
{
    $user = auth_user();
    return is_array($user) && (string) ($user['role'] ?? '') === 'admin';
}

function api_promotions_can_manage_directly(): bool
{
    if (api_promotions_is_admin()) {
        return true;
    }

    return has_any_permission([
        'finance.promotions.create',
        'finance.promotions.update',
        'finance.adjust.request',
    ]);
}

function api_promotions_can_delete_directly(): bool
{
    if (api_promotions_is_admin()) {
        return true;
    }

    return has_any_permission([
        'finance.promotions.delete',
        'finance.adjust.request',
    ]);
}

function api_promotions_is_valid_date(string $value): bool
{
    $date = DateTime::createFromFormat('Y-m-d', $value);
    return $date instanceof DateTime && $date->format('Y-m-d') === $value;
}

function api_promotions_save_action(): void
{
    api_guard_admin_or_staff();
    api_guard_permission('finance.promotions.view');
    api_require_post(page_url('promotions-manage'));

    if (!api_promotions_can_manage_directly()) {
        set_flash('error', 'Bạn không có quyền CRUD trực tiếp ưu đãi giảm giá.');
        redirect(page_url('promotions-manage'));
    }

    $promotionId = input_int($_POST, 'id');
    $courseId = max(0, input_int($_POST, 'course_id'));
    $name = input_string($_POST, 'name');
    $promoType = strtoupper(input_string($_POST, 'promo_type', 'DURATION'));
    $discountValue = input_float($_POST, 'discount_value');
    $startDate = input_string($_POST, 'start_date');
    $endDate = input_string($_POST, 'end_date');
    $quantityLimitRaw = trim((string) ($_POST['quantity_limit'] ?? ''));
    $academicModel = new AcademicModel();

    if ($name === '') {
        set_flash('error', 'Vui lòng nhập tên ưu đãi.');
        $query = $promotionId > 0 ? ['edit' => $promotionId] : [];
        redirect(page_url('promotions-manage', $query));
    }

    if (!in_array($promoType, ['DURATION', 'SOCIAL', 'EVENT', 'GROUP'], true)) {
        set_flash('error', 'Loại ưu đãi không hợp lệ.');
        $query = $promotionId > 0 ? ['edit' => $promotionId] : [];
        redirect(page_url('promotions-manage', $query));
    }

    if ($discountValue < 0 || $discountValue > 100) {
        set_flash('error', 'Mức giảm phải nằm trong khoảng từ 0 đến 100.');
        $query = $promotionId > 0 ? ['edit' => $promotionId] : [];
        redirect(page_url('promotions-manage', $query));
    }

    if ($startDate !== '' && !api_promotions_is_valid_date($startDate)) {
        set_flash('error', 'Ngày bắt đầu không đúng định dạng YYYY-MM-DD.');
        $query = $promotionId > 0 ? ['edit' => $promotionId] : [];
        redirect(page_url('promotions-manage', $query));
    }

    if ($endDate !== '' && !api_promotions_is_valid_date($endDate)) {
        set_flash('error', 'Ngày kết thúc không đúng định dạng YYYY-MM-DD.');
        $query = $promotionId > 0 ? ['edit' => $promotionId] : [];
        redirect(page_url('promotions-manage', $query));
    }

    if ($startDate !== '' && $endDate !== '' && $startDate > $endDate) {
        set_flash('error', 'Ngày bắt đầu phải nhỏ hơn hoặc bằng ngày kết thúc.');
        $query = $promotionId > 0 ? ['edit' => $promotionId] : [];
        redirect(page_url('promotions-manage', $query));
    }

    $quantityLimit = null;
    if ($quantityLimitRaw !== '') {
        $quantityLimit = max(0, (int) $quantityLimitRaw);
        if ($quantityLimit <= 0) {
            $quantityLimit = null;
        }
    }

    $quantityRemaining = null;
    if ($promotionId > 0) {
        $existingPromotion = $academicModel->findPromotion($promotionId) ?? [];
        $existingLimit = $existingPromotion['quantity_limit'] ?? null;
        $existingRemaining = $existingPromotion['quantity_remaining'] ?? null;

        if ($quantityLimit !== null) {
            if ($existingLimit === null) {
                $quantityRemaining = $quantityLimit;
            } else {
                $usedCount = max(0, (int) $existingLimit - (int) $existingRemaining);
                $quantityRemaining = max(0, $quantityLimit - $usedCount);
            }
        }
    } elseif ($quantityLimit !== null) {
        $quantityRemaining = $quantityLimit;
    }

    if (!$academicModel->usesPromotionSchema() && $courseId <= 0) {
        set_flash('error', 'Schema hiện tại chỉ hỗ trợ ưu đãi theo khóa học. Vui lòng chọn khóa học cụ thể.');
        $query = $promotionId > 0 ? ['edit' => $promotionId] : [];
        redirect(page_url('promotions-manage', $query));
    }

    try {
        $academicModel->savePromotion([
            'id' => $promotionId,
            'course_id' => $courseId,
            'name' => $name,
            'promo_type' => $promoType,
            'discount_value' => $discountValue,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'quantity_limit' => $quantityLimit,
            'quantity_remaining' => $quantityRemaining,
        ]);
    } catch (Throwable) {
        set_flash('error', 'Không thể lưu ưu đãi với schema dữ liệu hiện tại. Vui lòng kiểm tra phạm vi khóa học và thử lại.');
        $query = $promotionId > 0 ? ['edit' => $promotionId] : [];
        redirect(page_url('promotions-manage', $query));
    }

    set_flash('success', $promotionId > 0 ? 'Đã cập nhật ưu đãi thành công.' : 'Đã tạo ưu đãi thành công.');
    redirect(page_url('promotions-manage'));
}

function api_promotions_delete_action(): void
{
    api_guard_admin_or_staff();
    api_guard_permission('finance.promotions.view');
    api_require_post(page_url('promotions-manage'));

    if (!api_promotions_can_delete_directly()) {
        set_flash('error', 'Bạn không có quyền xóa ưu đãi giảm giá.');
        redirect(page_url('promotions-manage'));
    }

    $promotionId = max(0, (int) ($_GET['id'] ?? 0));
    if ($promotionId <= 0) {
        set_flash('error', 'Ưu đãi không hợp lệ.');
        redirect(page_url('promotions-manage'));
    }

    try {
        (new AcademicModel())->deletePromotion($promotionId);
    } catch (Throwable) {
        set_flash('error', 'Không thể xóa ưu đãi. Bản ghi có thể đang được tham chiếu bởi dữ liệu khác.');
        redirect(page_url('promotions-manage'));
    }

    set_flash('success', 'Đã chuyển ưu đãi vào trạng thái xóa mềm.');
    redirect(page_url('promotions-manage'));
}
