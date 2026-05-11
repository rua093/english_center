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
	if ($updateMode === 'password') {
		$currentPassword = (string) ($_POST['current_password'] ?? '');
		$newPassword = (string) ($_POST['new_password'] ?? '');
		$confirmPassword = (string) ($_POST['confirm_password'] ?? '');

		if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
			set_flash('error', 'Vui lòng nhập đầy đủ mật khẩu hiện tại, mật khẩu mới và xác nhận mật khẩu.');
			redirect(page_url('profile'));
		}

		if ($newPassword !== $confirmPassword) {
			set_flash('error', 'Mật khẩu mới và xác nhận mật khẩu không khớp.');
			redirect(page_url('profile'));
		}

		if (mb_strlen($newPassword) < 6) {
			set_flash('error', 'Mật khẩu mới phải có ít nhất 6 ký tự.');
			redirect(page_url('profile'));
		}

		$passwordHash = $usersTable->findPasswordHashById($userId);
		if ($passwordHash === null || !password_verify($currentPassword, $passwordHash)) {
			set_flash('error', 'Mật khẩu hiện tại không đúng.');
			redirect(page_url('profile'));
		}

		$usersTable->updatePassword($userId, $newPassword);
		set_flash('success', 'Đã cập nhật mật khẩu thành công.');
		redirect(page_url('profile'));
	}
	$email = trim((string) ($_POST['email'] ?? ''));
	$phone = normalize_phone_string((string) ($_POST['phone'] ?? ''));
	$teacherIntroVideoUrl = trim((string) ($_POST['teacher_intro_video_url_hidden'] ?? ''));
	$studentFatherName = trim((string) ($_POST['student_father_name'] ?? ''));
	$studentFatherPhone = normalize_phone_string((string) ($_POST['student_father_phone'] ?? ''));
	$studentFatherIdCard = trim((string) ($_POST['student_father_id_card'] ?? ''));
	$studentMotherName = trim((string) ($_POST['student_mother_name'] ?? ''));
	$studentMotherPhone = normalize_phone_string((string) ($_POST['student_mother_phone'] ?? ''));
	$studentMotherIdCard = trim((string) ($_POST['student_mother_id_card'] ?? ''));
	$studentParentSocialLinks = trim((string) ($_POST['student_parent_social_links'] ?? ''));

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

	if ($existingProfile && (string) ($existingProfile['role_name'] ?? '') === 'teacher') {
		if (isset($_FILES['teacher_intro_video_file']) && (int) ($_FILES['teacher_intro_video_file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
			$teacherVideoError = (int) ($_FILES['teacher_intro_video_file']['error'] ?? UPLOAD_ERR_NO_FILE);
			if ($teacherVideoError !== UPLOAD_ERR_OK) {
				$errorMessage = match ($teacherVideoError) {
					UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Video giới thiệu quá lớn. Vui lòng chọn tệp nhỏ hơn giới hạn cho phép.',
					UPLOAD_ERR_PARTIAL => 'Video giới thiệu chỉ được tải lên một phần. Vui lòng thử lại.',
					UPLOAD_ERR_NO_TMP_DIR => 'Máy chủ đang thiếu thư mục tạm để xử lý video.',
					UPLOAD_ERR_CANT_WRITE => 'Máy chủ không thể ghi video lên đĩa.',
					UPLOAD_ERR_EXTENSION => 'Một tiện ích mở rộng của máy chủ đã chặn video này.',
					default => 'Tải video giới thiệu thất bại. Vui lòng thử lại.',
				};
				set_flash('error', $errorMessage);
				redirect(page_url('profile'));
			}

			$teacherVideoPath = store_uploaded_file($_FILES['teacher_intro_video_file'], 'teacher-video', 'teacher-videos');
			if ($teacherVideoPath === null) {
				set_flash('error', 'Tải video giới thiệu thất bại. Vui lòng thử lại với tệp video hợp lệ.');
				redirect(page_url('profile'));
			}
			$teacherIntroVideoUrl = $teacherVideoPath;
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

	if ((string) ($existingProfile['role_name'] ?? '') === 'teacher') {
		$usersTable->updateTeacherProfile($userId, [
			'teacher_intro_video_url' => $teacherIntroVideoUrl,
		]);
	}

	if ((string) ($existingProfile['role_name'] ?? '') === 'student') {
		$usersTable->saveRoleProfile($userId, 'student', [
			'student_father_name' => $studentFatherName,
			'student_father_phone' => $studentFatherPhone,
			'student_father_id_card' => $studentFatherIdCard,
			'student_mother_name' => $studentMotherName,
			'student_mother_phone' => $studentMotherPhone,
			'student_mother_id_card' => $studentMotherIdCard,
			'student_parent_social_links' => $studentParentSocialLinks,
			'student_school_name' => (string) (($existingProfile['role_profile']['student_school_name'] ?? '') ?: ($existingProfile['student_school_name'] ?? '')),
			'student_target_score' => (string) (($existingProfile['role_profile']['student_target_score'] ?? '') ?: ($existingProfile['student_target_score'] ?? '')),
		]);
	}

	$refreshedProfile = $usersTable->findActiveById($userId);

	if (isset($_SESSION['auth_user']) && is_array($_SESSION['auth_user'])) {
		$_SESSION['auth_user']['email'] = $email;
		$_SESSION['auth_user']['phone'] = $phone;
		if ($avatarPath !== null) {
			$_SESSION['auth_user']['avatar'] = $avatarPath;
		}
		$_SESSION['auth_user']['role_profile'] = is_array($refreshedProfile['role_profile'] ?? null)
			? $refreshedProfile['role_profile']
			: [];
		if ((string) ($existingProfile['role_name'] ?? '') === 'teacher') {
			$_SESSION['auth_user']['role_profile']['teacher_intro_video_url'] = $teacherIntroVideoUrl;
		}
	}

	set_flash('success', 'Đã cập nhật thông tin hồ sơ.');
	redirect(page_url('profile'));
}
