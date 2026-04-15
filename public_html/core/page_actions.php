<?php
declare(strict_types=1);

function queue_approval_request(string $type, string $content, array $payload = []): void
{
	$user = auth_user();
	if (!$user) {
		return;
	}

	$enumCompatibleType = in_array($type, ['tuition_discount', 'tuition_delete', 'finance_adjust', 'teacher_leave', 'schedule_change'], true)
		? $type
		: 'tuition_discount';

	$storedContent = $content;
	if (!empty($payload)) {
		$storedContent = (string) json_encode([
			'action' => $type,
			'message' => $content,
			'payload' => $payload,
		], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	}

	(new AcademicModel())->saveApproval([
		'requester_id' => (int) $user['id'],
		'approver_id' => null,
		'type' => $enumCompatibleType,
		'content' => $storedContent,
		'status' => 'pending',
	]);
}
