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
	$avatarDirectUrl = trim((string) ($_POST['avatar_uploaded_url'] ?? ''));
	$studentFatherName = trim((string) ($_POST['student_father_name'] ?? ''));
	$studentFatherPhone = normalize_phone_string((string) ($_POST['student_father_phone'] ?? ''));
	$studentFatherIdCard = trim((string) ($_POST['student_father_id_card'] ?? ''));
	$studentMotherName = trim((string) ($_POST['student_mother_name'] ?? ''));
	$studentMotherPhone = normalize_phone_string((string) ($_POST['student_mother_phone'] ?? ''));
	$studentMotherIdCard = trim((string) ($_POST['student_mother_id_card'] ?? ''));
	$studentParentSocialLinks = trim((string) ($_POST['student_parent_social_links'] ?? ''));
	$existingAvatarPath = trim((string) ($existingProfile['avatar'] ?? ''));
	$existingTeacherIntroVideoUrl = trim((string) (($existingProfile['role_profile']['teacher_intro_video_url'] ?? '') ?: ($existingProfile['teacher_intro_video_url'] ?? '')));

	$avatarPath = null;
	if ($avatarDirectUrl !== '') {
		if (!is_trusted_uploaded_file_url($avatarDirectUrl)) {
			set_flash('error', 'Ảnh đại diện tải lên chưa hợp lệ. Vui lòng thử lại.');
			redirect(page_url('profile'));
		}

		$avatarPath = normalize_public_file_url($avatarDirectUrl);
	}
	if (isset($_FILES['avatar']) && (int) ($_FILES['avatar']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
		$storedAvatarPath = store_uploaded_file_for_preset($_FILES['avatar'], 'avatar');
		if ($storedAvatarPath === null) {
			set_flash('error', 'Không thể tải ảnh đại diện lên. Vui lòng thử lại.');
			redirect(page_url('profile'));
		}

		$avatarPath = $storedAvatarPath;
	}

	if ($existingProfile && (string) ($existingProfile['role_name'] ?? '') === 'teacher') {
		if ($teacherIntroVideoUrl !== '' && !is_trusted_uploaded_file_url($teacherIntroVideoUrl)) {
			set_flash('error', 'Video giới thiệu tải lên chưa hợp lệ. Vui lòng thử lại.');
			redirect(page_url('profile'));
		}

		if (isset($_FILES['teacher_intro_video_file']) && (int) ($_FILES['teacher_intro_video_file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
			set_flash('error', 'Video giới thiệu chưa được tải lên. Vui lòng thử lại.');
			redirect(page_url('profile'));
		}

		if ($teacherIntroVideoUrl !== '') {
			$teacherIntroVideoUrl = normalize_public_file_url($teacherIntroVideoUrl);
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
		app_uploaded_object_manifest_mark_attached($avatarPath, ['entity' => 'avatar']);
		app_cleanup_replaced_uploaded_file($existingAvatarPath, $avatarPath);

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
	app_uploaded_object_manifest_mark_attached($avatarPath, ['entity' => 'avatar']);

	if ((string) ($existingProfile['role_name'] ?? '') === 'teacher') {
		$usersTable->updateTeacherProfile($userId, [
			'teacher_intro_video_url' => $teacherIntroVideoUrl,
		]);
		app_uploaded_object_manifest_mark_attached($teacherIntroVideoUrl, ['entity' => 'teacher_intro_video']);
		app_cleanup_replaced_uploaded_file($existingTeacherIntroVideoUrl, $teacherIntroVideoUrl);
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
