<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/api_helpers.php';
require_once __DIR__ . '/../../models/FaqModel.php';

function api_faqs_save_action(): void
{
    api_guard_admin();
    $redirectTarget = page_url('manage-faqs');
    api_require_post($redirectTarget);

    $id = input_int($_POST, 'id');
    $category = input_string($_POST, 'category');
    $question = input_string($_POST, 'question');
    $answer = input_string($_POST, 'answer');
    $sortOrder = input_int($_POST, 'sort_order');
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    if ($question === '' || $answer === '') {
        set_flash('error', 'Vui lòng nhập đầy đủ Câu hỏi và Câu trả lời.');
        redirect($redirectTarget);
    }

    try {
        $faqModel = new FaqModel();
        $faqModel->saveFaq([
            'id' => $id,
            'category' => $category !== '' ? $category : 'Hỏi đáp chung',
            'question' => $question,
            'answer' => $answer,
            'sort_order' => $sortOrder,
            'is_active' => $isActive,
        ]);

        set_flash('success', $id > 0 ? 'Cập nhật câu hỏi FAQ thành công!' : 'Thêm mới câu hỏi FAQ thành công!');
    } catch (Throwable $exception) {
        set_flash('error', 'Không thể lưu câu hỏi FAQ: ' . $exception->getMessage());
    }

    redirect($redirectTarget);
}

function api_faqs_toggle_active_action(): void
{
    api_guard_admin();
    $redirectTarget = page_url('manage-faqs');
    api_require_post($redirectTarget);

    $id = input_int($_POST, 'id');
    if ($id <= 0) {
        set_flash('error', 'Câu hỏi FAQ không hợp lệ.');
        redirect($redirectTarget);
    }

    try {
        $faqModel = new FaqModel();
        $faqModel->toggleFaqActive($id);
        set_flash('success', 'Đã thay đổi trạng thái hiển thị câu hỏi.');
    } catch (Throwable $exception) {
        set_flash('error', 'Lỗi khi thay đổi trạng thái câu hỏi.');
    }

    redirect($redirectTarget);
}

function api_faqs_delete_action(): void
{
    api_guard_admin();
    $redirectTarget = page_url('manage-faqs');
    api_require_post($redirectTarget);

    $id = input_int($_POST, 'id');
    if ($id <= 0) {
        set_flash('error', 'Câu hỏi FAQ không hợp lệ.');
        redirect($redirectTarget);
    }

    try {
        $faqModel = new FaqModel();
        $faqModel->deleteFaq($id);
        set_flash('success', 'Đã xóa câu hỏi FAQ thành công!');
    } catch (Throwable $exception) {
        set_flash('error', 'Không thể xóa câu hỏi FAQ.');
    }

    redirect($redirectTarget);
}
