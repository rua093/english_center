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

	try {
		$spec = app_direct_upload_build_spec($preset, $filename, $contentType, $fileSize);
	} catch (Throwable $exception) {
		app_log('error', 'Direct upload spec generation failed', [
			'preset' => $preset,
			'filename' => $filename,
			'content_type' => $contentType,
			'file_size' => $fileSize,
			'error' => $exception->getMessage(),
			'file' => $exception->getFile(),
			'line' => $exception->getLine(),
			'user_id' => (int) ((auth_user()['id'] ?? 0)),
		]);

		api_error('Không thể chuẩn bị tải file lên lúc này. Vui lòng thử lại.', [
			'code' => 'DIRECT_UPLOAD_EXCEPTION',
		], 500);
	}

	if ($spec === null) {
		$storageError = app_file_storage_last_error_message();
		$storageContext = app_file_storage_last_error_context();
		app_log('error', 'Direct upload prepare failed', [
			'preset' => $preset,
			'filename' => $filename,
			'content_type' => $contentType,
			'file_size' => $fileSize,
			'storage_error' => $storageError,
			'storage_context' => $storageContext,
			'user_id' => (int) ((auth_user()['id'] ?? 0)),
		]);

		api_error('Không thể tải file lên lúc này. Vui lòng thử lại.', [
			'code' => 'DIRECT_UPLOAD_PREPARE_FAILED',
			'storage_error' => $storageError,
		], 422);
	}

	api_success('Direct upload prepared.', $spec);
}
