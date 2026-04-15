<?php
declare(strict_types=1);

function store_uploaded_file(array $file, string $prefix): ?string
{
	if (empty($file['name']) || (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
		return null;
	}

	if ((int) ($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
		return null;
	}

	$uploadDir = __DIR__ . '/../assets/uploads';
	if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
		return null;
	}

	$originalName = basename((string) $file['name']);
	$safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', $originalName) ?: 'upload.bin';
	$storedName = sprintf('%s-%d-%s', $prefix, time(), $safeName);
	$targetPath = $uploadDir . '/' . $storedName;

	if (!is_uploaded_file((string) ($file['tmp_name'] ?? '')) || !move_uploaded_file((string) $file['tmp_name'], $targetPath)) {
		return null;
	}

	return '/assets/uploads/' . $storedName;
}

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
