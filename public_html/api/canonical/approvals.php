<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/api_helpers.php';
require_once __DIR__ . '/../../models/AcademicModel.php';

function api_approvals_is_admin(): bool
{
    $user = auth_user();
    return is_array($user) && (string) ($user['role'] ?? '') === 'admin';
}

function api_approvals_can_create(): bool
{
    return api_approvals_is_admin();
}

function api_approvals_can_update(): bool
{
    if (api_approvals_is_admin()) {
        return true;
    }

    return has_any_permission([
        'approval.update',
    ]);
}

function api_approvals_can_staff_update_type(string $type): bool
{
    return in_array(strtolower(trim($type)), ['schedule_change', 'teacher_leave'], true);
}

function api_approvals_can_delete(): bool
{
    if (api_approvals_is_admin()) {
        return true;
    }

    return has_any_permission([
        'approval.delete',
    ]);
}

function api_approvals_enum_types(): array
{
    return [
        'tuition_discount',
        'tuition_delete',
        'finance_adjust',
        'teacher_leave',
        'schedule_change',
    ];
}

function api_approvals_extract_message(string $content): string
{
    $decoded = json_decode($content, true);
    if (is_array($decoded) && array_key_exists('message', $decoded)) {
        return trim((string) ($decoded['message'] ?? ''));
    }

    return trim($content);
}

function api_approvals_merge_content_with_existing(string $newMessage, array $existingApproval): string
{
    $trimmedMessage = trim($newMessage);
    $existingContent = (string) ($existingApproval['content'] ?? '');
    $decoded = json_decode($existingContent, true);
    if (!is_array($decoded)) {
        return $trimmedMessage;
    }

    $hasMetadata = array_key_exists('action', $decoded)
        || array_key_exists('payload', $decoded)
        || array_key_exists('message', $decoded);
    if (!$hasMetadata) {
        return $trimmedMessage;
    }

    $decoded['message'] = $trimmedMessage;
    if (!isset($decoded['action']) || trim((string) $decoded['action']) === '') {
        $decoded['action'] = (string) ($existingApproval['type'] ?? 'schedule_change');
    }
    if (!isset($decoded['payload']) || !is_array($decoded['payload'])) {
        $decoded['payload'] = [];
    }

    return (string) json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function api_approvals_build_create_payload(string $requestedType, string $content): array
{
    $normalizedType = strtolower(trim($requestedType));
    $enumTypes = api_approvals_enum_types();

    if (in_array($normalizedType, $enumTypes, true)) {
        return [
            'type' => $normalizedType,
            'content' => $content,
        ];
    }

    if ($normalizedType === 'other') {
        return [
            'type' => 'tuition_discount',
            'content' => (string) json_encode([
                'action' => 'other',
                'message' => $content,
                'payload' => [],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ];
    }

    return [
        'type' => 'schedule_change',
        'content' => $content,
    ];
}

function api_approvals_save_action(): void
{
    api_guard_admin_or_staff();
    api_guard_permission('approval.view');
    api_require_post(page_url('approvals-manage'));

    $user = auth_user();
    $id = input_int($_POST, 'id');
    $isUpdate = $id > 0;

    if (($isUpdate && !api_approvals_can_update()) || (!$isUpdate && !api_approvals_can_create())) {
        set_flash('error', 'Bạn không có quyền CRUD trực tiếp phiếu phê duyệt.');
        $query = $id > 0 ? ['edit' => $id] : [];
        redirect(page_url('approvals-manage', $query));
    }

    $academicModel = new AcademicModel();

    if ($isUpdate) {
        $existing = $academicModel->findApproval($id);
        if (!$existing) {
            set_flash('error', 'Không tìm thấy phiếu phê duyệt cần cập nhật.');
            redirect(page_url('approvals-manage'));
        }

        if (!api_approvals_is_admin() && !api_approvals_can_staff_update_type((string) ($existing['type'] ?? ''))) {
            set_flash('error', 'Bạn chỉ có thể chỉnh sửa yêu cầu Giáo viên xin nghỉ hoặc Thay đổi lịch học.');
            redirect(page_url('approvals-manage'));
        }

        $oldStatus = (string) ($existing['status'] ?? 'pending');
        $allowedStatus = ['pending', 'approved', 'rejected'];
        $requestedStatus = input_string($_POST, 'status', $oldStatus);
        if (!in_array($requestedStatus, $allowedStatus, true)) {
            $requestedStatus = $oldStatus;
        }

        $defaultContent = api_approvals_extract_message((string) ($existing['content'] ?? ''));
        $content = input_string($_POST, 'content', $defaultContent);
        if ($content === '') {
            set_flash('error', 'Vui lòng nhập nội dung yêu cầu phê duyệt.');
            redirect(page_url('approvals-manage', ['edit' => $id]));
        }

        $storedContent = api_approvals_merge_content_with_existing($content, $existing);

        $academicModel->saveApproval([
            'id' => $id,
            'approver_id' => (int) ($existing['approver_id'] ?? 0),
            'status' => $oldStatus,
            'content' => $storedContent,
        ]);

        if ($requestedStatus !== $oldStatus) {
            $currentUserId = (int) ($user['id'] ?? 0);
            $academicModel->decideApproval($id, $currentUserId, $requestedStatus);
        }

        set_flash('success', 'Đã cập nhật phiếu phê duyệt.');
        redirect(page_url('approvals-manage'));
    }

    $content = input_string($_POST, 'content');
    if ($content === '') {
        set_flash('error', 'Vui lòng nhập nội dung yêu cầu phê duyệt.');
        redirect(page_url('approvals-manage'));
    }

    $requesterId = (int) ($user['id'] ?? 0);
    if ($requesterId <= 0) {
        set_flash('error', 'Không xác định được người tạo phiếu từ phiên đăng nhập.');
        redirect(page_url('approvals-manage'));
    }

    $createPayload = api_approvals_build_create_payload(
        input_string($_POST, 'type', 'schedule_change'),
        $content
    );

    $academicModel->saveApproval([
        'requester_id' => $requesterId,
        'approver_id' => null,
        'type' => (string) ($createPayload['type'] ?? 'schedule_change'),
        'content' => (string) ($createPayload['content'] ?? $content),
        'status' => 'pending',
    ]);

    set_flash('success', 'Đã tạo phiếu phê duyệt mới.');
    redirect(page_url('approvals-manage'));
}

function api_approvals_approve_action(): void
{
    api_guard_admin_or_staff();
    api_guard_permission('approval.update');
    api_require_post(page_url('approvals-manage'));

    if (!api_approvals_can_update()) {
        set_flash('error', 'Bạn không có quyền cập nhật trạng thái phê duyệt.');
        redirect(page_url('approvals-manage'));
    }

    $approvalId = input_int($_POST, 'id');
    $status = input_string($_POST, 'status', 'pending');
    $note = trim(input_string($_POST, 'decision_note'));
    $user = auth_user();

    (new AcademicModel())->decideApproval($approvalId, (int) ($user['id'] ?? 0), $status, $note);
    set_flash('success', 'Đã cập nhật trạng thái phê duyệt.');

    redirect(page_url('approvals-manage'));
}

function api_approvals_delete_action(): void
{
    api_guard_admin_or_staff();
    api_guard_permission('approval.view');
    api_require_post(page_url('approvals-manage'));

    if (!api_approvals_can_delete()) {
        set_flash('error', 'Bạn không có quyền xóa phiếu phê duyệt.');
        redirect(page_url('approvals-manage'));
    }

    $id = input_int($_POST, 'id', input_int($_GET, 'id'));
    if ($id > 0) {
        (new AcademicModel())->deleteApproval($id);
        set_flash('success', 'Đã xóa phiếu phê duyệt.');
    }

    redirect(page_url('approvals-manage'));
}
