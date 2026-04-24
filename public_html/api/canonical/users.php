<?php
declare(strict_types=1);

function api_users_update_action(): void
{
	require_login();

	if (!validate_csrf_token(request_csrf_token())) {
		api_fail_invalid_csrf(page_url('profile'));
	}

	$currentUser = auth_user();
	$userId = (int) ($currentUser['id'] ?? 0);
	if ($userId <= 0) {
		set_flash('error', 'Vui lòng đăng nhập lại để cập nhật hồ sơ.');
		redirect(page_url('login'));
	}

	$usersTable = new UsersTableModel();
	$existingProfile = $usersTable->findActiveById($userId);
	if (!$existingProfile) {
		set_flash('error', 'Không tìm thấy hồ sơ hiện tại.');
		redirect(page_url('profile'));
	}

	$updateMode = strtolower(trim((string) ($_POST['update_mode'] ?? 'profile')));
	$email = trim((string) ($_POST['email'] ?? ''));
	$phone = trim((string) ($_POST['phone'] ?? ''));

	$avatarPath = null;
	if (isset($_FILES['avatar']) && (int) ($_FILES['avatar']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
		$avatarError = (int) ($_FILES['avatar']['error'] ?? UPLOAD_ERR_NO_FILE);
		if ($avatarError !== UPLOAD_ERR_OK) {
			$errorMessage = match ($avatarError) {
				UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Ảnh đại diện quá lớn. Vui lòng chọn tệp nhỏ hơn 10MB.',
				UPLOAD_ERR_PARTIAL => 'Tệp ảnh đại diện bị tải lên dang dở. Vui lòng thử lại.',
				UPLOAD_ERR_NO_TMP_DIR => 'Máy chủ đang thiếu thư mục tạm để xử lý upload ảnh.',
				UPLOAD_ERR_CANT_WRITE => 'Máy chủ không thể ghi ảnh đại diện lên đĩa.',
				UPLOAD_ERR_EXTENSION => 'Một tiện ích mở rộng của máy chủ đã chặn ảnh đại diện này.',
				default => 'Tải ảnh đại diện thất bại. Vui lòng thử lại với tệp ảnh hợp lệ.',
			};
			set_flash('error', $errorMessage);
			redirect(page_url('profile'));
		}

		$avatarPath = store_uploaded_file($_FILES['avatar'], 'avatar', 'profile');
		if ($avatarPath === null) {
			set_flash('error', 'Tải ảnh đại diện thất bại. Vui lòng thử lại với tệp ảnh hợp lệ.');
			redirect(page_url('profile'));
		}
	}

	if ($updateMode === 'avatar') {
		if ($avatarPath === null) {
			set_flash('error', 'Vui lòng chọn ảnh đại diện để tải lên.');
			redirect(page_url('profile'));
		}

		$usersTable->updateProfile($userId, [
			'email' => (string) ($existingProfile['email'] ?? ''),
			'phone' => (string) ($existingProfile['phone'] ?? ''),
			'avatar' => $avatarPath,
		]);

		if (isset($_SESSION['auth_user']) && is_array($_SESSION['auth_user'])) {
			$_SESSION['auth_user']['avatar'] = $avatarPath;
		}

		set_flash('success', 'Đã cập nhật ảnh đại diện.');
		redirect(page_url('profile'));
	}

	if ($email === '' || $phone === '') {
		set_flash('error', 'Vui lòng điền đầy đủ email và số điện thoại.');
		redirect(page_url('profile'));
	}

	$usersTable->updateProfile($userId, [
		'email' => $email,
		'phone' => $phone,
		'avatar' => $avatarPath,
	]);

	if (isset($_SESSION['auth_user']) && is_array($_SESSION['auth_user'])) {
		$_SESSION['auth_user']['email'] = $email;
		$_SESSION['auth_user']['phone'] = $phone;
		if ($avatarPath !== null) {
			$_SESSION['auth_user']['avatar'] = $avatarPath;
		}
	}

	set_flash('success', 'Đã cập nhật thông tin hồ sơ.');
	redirect(page_url('profile'));
}