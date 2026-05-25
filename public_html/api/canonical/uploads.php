<?php
declare(strict_types=1);

function api_uploads_direct_sign_action(): void
{
	require_login();
	api_require_post(page_url('profile'));

	if (!app_direct_upload_is_available()) {
		api_error('Direct upload is not available in the current environment.', [
			'code' => 'DIRECT_UPLOAD_UNAVAILABLE',
		], 409);
	}

	$preset = trim((string) ($_POST['preset'] ?? ''));
	$filename = trim((string) ($_POST['filename'] ?? ''));
	$contentType = trim((string) ($_POST['content_type'] ?? ''));
	$fileSize = max(0, (int) ($_POST['file_size'] ?? 0));

	$spec = app_direct_upload_build_spec($preset, $filename, $contentType, $fileSize);
	if ($spec === null) {
		api_error('Không thể tải file lên lúc này. Vui lòng thử lại.', [
			'code' => 'DIRECT_UPLOAD_PREPARE_FAILED',
		], 422);
	}

	api_success('Direct upload prepared.', $spec);
}
