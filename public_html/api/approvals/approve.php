<?php
declare(strict_types=1);

require_admin_or_staff();
require_role(['admin']);
require_permission('approval.update');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
redirect('/?page=manage-approvals');
}

$approvalId = (int) ($_POST['id'] ?? 0);
$status = (string) ($_POST['status'] ?? 'pending');
$note = trim((string) ($_POST['decision_note'] ?? ''));
$user = auth_user();
(new AcademicModel())->decideApproval($approvalId, (int) ($user['id'] ?? 0), $status, $note);
set_flash('success', 'Đã cập nhật trạng thái phê duyệt.');

redirect('/?page=manage-approvals');
