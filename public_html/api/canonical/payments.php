<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/api_helpers.php';
require_once __DIR__ . '/../../models/AcademicModel.php';

function api_payments_is_admin(): bool
{
    $user = auth_user();
    return is_array($user) && (string) ($user['role'] ?? '') === 'admin';
}

function api_payments_can_create(): bool
{
    if (api_payments_is_admin()) {
        return true;
    }

    return has_any_permission([
        'finance.payment.manage',
        'finance.payment.create',
    ]);
}

function api_payments_can_update(): bool
{
    if (api_payments_is_admin()) {
        return true;
    }

    return has_any_permission([
        'finance.payment.manage',
        'finance.payment.update',
    ]);
}

function api_payments_can_delete(): bool
{
    if (api_payments_is_admin()) {
        return true;
    }

    return has_any_permission([
        'finance.payment.manage',
        'finance.payment.delete',
    ]);
}

function api_payments_save_action(): void
{
    api_guard_admin_or_staff();
    api_guard_permission('finance.payment.view');
    api_require_post(page_url('payments-finance'));

    $id = input_int($_POST, 'id');
    $isUpdate = $id > 0;

    if (($isUpdate && !api_payments_can_update()) || (!$isUpdate && !api_payments_can_create())) {
        set_flash('error', 'Bạn không có quyền CRUD trực tiếp giao dịch thanh toán.');
        $query = $id > 0 ? ['edit' => $id] : [];
        redirect(page_url('payments-finance', $query));
    }

    $tuitionFeeId = input_int($_POST, 'tuition_fee_id');
    $transactionNo = input_string($_POST, 'transaction_no');
    $paymentMethod = input_string($_POST, 'payment_method', 'bank_transfer');
    $amount = input_float($_POST, 'amount');
    $status = input_string($_POST, 'transaction_status', 'pending');

    $allowedMethods = ['bank_transfer', 'cash', 'ewallet', 'card', 'other'];
    if (!in_array($paymentMethod, $allowedMethods, true)) {
        $paymentMethod = 'bank_transfer';
    }

    if ($tuitionFeeId <= 0 || $amount <= 0) {
        set_flash('error', 'Vui lòng nhập đầy đủ hóa đơn học phí và số tiền hợp lệ.');
        $query = $id > 0 ? ['edit' => $id] : [];
        redirect(page_url('payments-finance', $query));
    }

    (new AcademicModel())->savePaymentTransaction([
        'id' => $id,
        'tuition_fee_id' => $tuitionFeeId,
        'transaction_no' => $transactionNo,
        'payment_method' => $paymentMethod,
        'amount' => $amount,
        'transaction_status' => $status,
    ]);

    set_flash('success', $isUpdate ? 'Đã cập nhật giao dịch thanh toán.' : 'Đã tạo giao dịch thanh toán.');
    redirect(page_url('payments-finance'));
}

function api_payments_delete_action(): void
{
    api_guard_admin_or_staff();
    api_guard_permission('finance.payment.view');
    api_require_post(page_url('payments-finance'));

    if (!api_payments_can_delete()) {
        set_flash('error', 'Bạn không có quyền xóa giao dịch thanh toán.');
        redirect(page_url('payments-finance'));
    }

    $id = input_int($_POST, 'id', input_int($_GET, 'id'));
    if ($id > 0) {
        (new AcademicModel())->deletePaymentTransaction($id);
        set_flash('success', 'Đã xóa giao dịch thanh toán.');
    }

    redirect(page_url('payments-finance'));
}
