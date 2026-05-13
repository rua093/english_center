<?php
declare(strict_types=1);

const APP_SUPPORTED_LOCALES = ['vi', 'en'];
const APP_DEFAULT_LOCALE = 'vi';

function normalize_locale(string $locale): string
{
	$normalized = strtolower(trim($locale));
	return in_array($normalized, APP_SUPPORTED_LOCALES, true) ? $normalized : APP_DEFAULT_LOCALE;
}

function current_locale(): string
{
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	return normalize_locale((string) ($_SESSION['locale'] ?? APP_DEFAULT_LOCALE));
}

function set_locale(string $locale): void
{
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	$_SESSION['locale'] = normalize_locale($locale);
}

function i18n_bootstrap(): void
{
	$queryLocale = (string) ($_GET['lang'] ?? '');
	if ($queryLocale !== '') {
		set_locale($queryLocale);
		return;
	}

	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	if (!isset($_SESSION['locale'])) {
		$_SESSION['locale'] = APP_DEFAULT_LOCALE;
	}
}

function i18n_dictionary(string $locale): array
{
	static $cache = [];
	$normalized = normalize_locale($locale);
	if (isset($cache[$normalized])) {
		return $cache[$normalized];
	}

	$file = __DIR__ . '/../lang/' . $normalized . '.php';
	$dictionary = is_file($file) ? require $file : [];
	$cache[$normalized] = is_array($dictionary) ? $dictionary : [];

	return $cache[$normalized];
}

function t(string $key, array $replace = []): string
{
	$currentLocale = current_locale();
	$dictionary = i18n_dictionary($currentLocale);
	$fallback = $currentLocale === APP_DEFAULT_LOCALE ? [] : i18n_dictionary(APP_DEFAULT_LOCALE);
	$value = $dictionary[$key] ?? $fallback[$key] ?? null;

	if ($value === null) {
		foreach (APP_SUPPORTED_LOCALES as $locale) {
			if ($locale === $currentLocale || $locale === APP_DEFAULT_LOCALE) {
				continue;
			}

			$altDictionary = i18n_dictionary($locale);
			if (array_key_exists($key, $altDictionary)) {
				$value = $altDictionary[$key];
				break;
			}
		}
	}

	$value = (string) ($value ?? $key);

	foreach ($replace as $name => $replacement) {
		$value = str_replace(':' . (string) $name, (string) $replacement, $value);
	}

	return $value;
}

function translate_legacy_flash_message(string $message): string
{
	$normalized = trim($message);
	if ($normalized === '') {
		return $message;
	}

	$locale = current_locale();
	$messages = [
		'vi' => [
			'Trang giao vien da duoc hop nhat vao khu vuc quan tri.' => 'Trang giáo viên đã được hợp nhất vào khu vực quản trị.',
			'Vui long nhap ho ten va thong tin lien he (email hoac so dien thoai).' => 'Vui lòng nhập họ tên và thông tin liên hệ (email hoặc số điện thoại).',
			'Vui long nhap ten hoc vien va thong tin lien he phu huynh (so dien thoai hoac email).' => 'Vui lòng nhập tên học viên và thông tin liên hệ phụ huynh (số điện thoại hoặc email).',
			'Vui long nhap ten hoc vien, ho ten phu huynh va it nhat mot so dien thoai lien he.' => 'Vui lòng nhập tên học viên, họ tên phụ huynh và ít nhất một số điện thoại liên hệ.',
			'Email khong hop le.' => 'Email không hợp lệ.',
			'Ho so ung tuyen da duoc ghi nhan. Trung tam se lien he de phong van.' => 'Hồ sơ ứng tuyển đã được ghi nhận. Trung tâm sẽ liên hệ để phỏng vấn.',
			'Khong the gui ho so ung tuyen. Vui long thu lai sau.' => 'Không thể gửi hồ sơ ứng tuyển. Vui lòng thử lại sau.',
			'Yeu cau tu van da duoc ghi nhan. Trung tam se lien he voi ban som nhat.' => 'Yêu cầu tư vấn đã được ghi nhận. Trung tâm sẽ liên hệ với bạn sớm nhất.',
			'Khong the gui yeu cau tu van. Vui long thu lai sau.' => 'Không thể gửi yêu cầu tư vấn. Vui lòng thử lại sau.',
		],
		'en' => [
			'Trang giao vien da duoc hop nhat vao khu vuc quan tri.' => 'The teacher dashboard has been merged into the admin area.',
			'Trang giáo viên đã được hợp nhất vào khu vực quản trị.' => 'The teacher dashboard has been merged into the admin area.',
			'Yêu cầu không hợp lệ. Vui lòng thử lại.' => 'Invalid request. Please try again.',
			'Vui lòng đăng nhập để tiếp tục.' => 'Please log in to continue.',
			'Vui lòng nhập đầy đủ thông tin đăng nhập.' => 'Please enter your login information.',
			'Thông tin đăng nhập không đúng hoặc tài khoản bị khóa.' => 'The login information is incorrect or the account is locked.',
			'Bạn đã đăng xuất thành công.' => 'You have logged out successfully.',
			'Chức năng quản lý tài khoản ngân hàng đã được gỡ khỏi hệ thống.' => 'Bank account management has been removed from the system.',
			'Vai trò không hợp lệ.' => 'Invalid role.',
			'Đã cập nhật phân quyền theo vai trò.' => 'Role permissions have been updated.',
			'Đã cập nhật trạng thái phê duyệt.' => 'Approval status has been updated.',
			'Yêu cầu nghỉ/dời lịch đã được gửi để phê duyệt.' => 'The leave/reschedule request has been submitted for approval.',
			'Đã cập nhật điểm bài nộp.' => 'Submission grade has been updated.',
			'Không có bài nộp hợp lệ để cập nhật.' => 'No valid submissions were found to update.',
			'Không thể tự xóa tài khoản đang đăng nhập.' => 'You cannot delete the account currently signed in.',
			'Đã khóa hoặc xóa mềm tài khoản người dùng.' => 'The user account has been locked or soft-deleted.',
			'Không thể tải lên ảnh thumbnail. Vui lòng thử lại với tệp hợp lệ.' => 'Could not upload the thumbnail. Please try again with a valid file.',
			'Đã lưu hoạt động ngoại khóa thành công.' => 'Extracurricular activity has been saved successfully.',
			'Đã chuyển hoạt động ngoại khóa vào trạng thái xóa mềm.' => 'The extracurricular activity has been moved to soft-deleted status.',
			'Không thể xóa hoạt động ngoại khóa. Vui lòng thử lại.' => 'Could not delete the extracurricular activity. Please try again.',
			'Dữ liệu học viên hoặc hoạt động không hợp lệ.' => 'Student or activity data is invalid.',
			'Đã xóa học viên khỏi danh sách đăng ký hoạt động.' => 'The student has been removed from the activity registration list.',
			'Không tìm thấy đăng ký để xóa.' => 'No registration was found to delete.',
			'Dữ liệu đăng ký hoạt động không hợp lệ.' => 'Activity registration data is invalid.',
			'Đã cập nhật thông tin thanh toán hoạt động.' => 'Activity payment information has been updated.',
			'Không tìm thấy đăng ký để cập nhật.' => 'No registration was found to update.',
			'Không tìm thấy hoạt động ngoại khóa.' => 'Extracurricular activity was not found.',
			'Không tìm thấy hoạt động ngoại khoá.' => 'Extracurricular activity was not found.',
			'Học viên không hợp lệ hoặc không còn hoạt động.' => 'The student is invalid or no longer active.',
			'Học viên này đã đăng ký hoạt động trước đó.' => 'This student has already registered for the activity.',
			'Đã đăng ký hoạt động cho học viên thành công.' => 'The student has been registered for the activity successfully.',
			'Dữ liệu đăng ký không hợp lệ.' => 'Registration data is invalid.',
			'Bạn đã đăng ký hoạt động này rồi.' => 'You have already registered for this activity.',
			'Đăng ký hoạt động ngoại khoá thành công.' => 'Extracurricular activity registration was successful.',
			'Thanh toán hoạt động ngoại khoá thành công.' => 'Extracurricular activity payment was successful.',
			'Thanh toán hiện chưa được tích hợp.' => 'Payment is not integrated yet.',
			'Vui lòng nhập đầy đủ thông tin người dùng bắt buộc.' => 'Please enter all required user information.',
			'Vui lòng chọn trạng thái người dùng.' => 'Please choose a user status.',
			'Vai trò người dùng không hợp lệ.' => 'Invalid user role.',
			'Vui lòng nhập vị trí công tác cho nhân viên.' => 'Please enter the staff position.',
			'Hồ sơ ứng tuyển không hợp lệ.' => 'Invalid job application profile.',
			'Đã cập nhật trạng thái hồ sơ ứng tuyển.' => 'Application status has been updated.',
			'Đã xóa hồ sơ ứng tuyển.' => 'Application has been deleted.',
			'Không thể xóa hồ sơ ứng tuyển này.' => 'Could not delete this application.',
			'Bạn không có quyền CRUD trực tiếp phiếu phê duyệt.' => 'You do not have permission to directly manage approval tickets.',
			'Không tìm thấy phiếu phê duyệt cần cập nhật.' => 'No approval ticket was found to update.',
			'Vui lòng nhập nội dung yêu cầu phê duyệt.' => 'Please enter the approval request content.',
			'Đã cập nhật phiếu phê duyệt.' => 'Approval ticket has been updated.',
			'Đã tạo phiếu phê duyệt mới.' => 'A new approval ticket has been created.',
			'Bạn không có quyền cập nhật trạng thái phê duyệt.' => 'You do not have permission to update approval status.',
			'Bạn không có quyền xóa phiếu phê duyệt.' => 'You do not have permission to delete approval tickets.',
			'Đã xóa phiếu phê duyệt.' => 'Approval ticket has been deleted.',
			'Không thể xóa phiếu phê duyệt. Vui lòng thử lại.' => 'Could not delete the approval ticket. Please try again.',
			'Vui lòng chọn lớp, buổi học và nhập đầy đủ tiêu đề, hạn nộp.' => 'Please choose a class, lesson, and enter the title and deadline.',
			'Buổi học không thuộc lớp đã chọn. Vui lòng chọn lại.' => 'The lesson does not belong to the selected class. Please choose again.',
			'Đã lưu bài tập thành công.' => 'Assignment has been saved successfully.',
			'Đã xóa bài tập.' => 'Assignment has been deleted.',
			'Không thể xóa bài tập. Bài tập này có thể đã có bài nộp của học viên.' => 'Could not delete the assignment. It may already have student submissions.',
			'Bạn không có quyền nộp bài tập.' => 'You do not have permission to submit assignments.',
			'Tải lên bài làm thất bại. Vui lòng thử lại.' => 'Assignment upload failed. Please try again.',
			'Đã gửi bài tập thành công.' => 'Assignment has been submitted successfully.',
			'Vui lòng tải lên file hoặc nhập đường dẫn bài làm.' => 'Please upload a file or enter a submission URL.',
			'Vui lòng nhập đầy đủ khóa học, tên lớp và giáo viên.' => 'Please enter the course, class name, and teacher.',
			'Đã lưu lớp học thành công.' => 'Class has been saved successfully.',
			'Đã xóa lớp học.' => 'Class has been deleted.',
			'Vui lòng nhập tên khóa học.' => 'Please enter the course name.',
			'Đã cập nhật khóa học.' => 'Course has been updated.',
			'Đã tạo khóa học mới.' => 'New course has been created.',
			'Khóa học không hợp lệ.' => 'Invalid course.',
			'Đã chuyển khóa học vào trạng thái xóa mềm.' => 'Course has been moved to soft-deleted status.',
			'Vui lòng nhập đánh giá từ 1 đến 5.' => 'Please enter a rating from 1 to 5.',
			'Không thể xác định người gửi đánh giá.' => 'Could not identify the feedback sender.',
			'Đã lưu đánh giá thành công.' => 'Feedback has been saved successfully.',
			'Đã xóa đánh giá.' => 'Feedback has been deleted.',
			'Không thể xóa đánh giá. Vui lòng thử lại.' => 'Could not delete feedback. Please try again.',
			'Vui long nhap ho ten va thong tin lien he (email hoac so dien thoai).' => 'Please enter your full name and contact information (email or phone).',
			'Email khong hop le.' => 'Invalid email.',
			'Ho so ung tuyen da duoc ghi nhan. Trung tam se lien he de phong van.' => 'Your application has been received. The center will contact you for an interview.',
			'Khong the gui ho so ung tuyen. Vui long thu lai sau.' => 'Could not submit the application. Please try again later.',
			'Vui long nhap ten hoc vien va thong tin lien he phu huynh (so dien thoai hoac email).' => 'Please enter the student name and parent contact information (phone or email).',
			'Vui long nhap ten hoc vien, ho ten phu huynh va it nhat mot so dien thoai lien he.' => 'Please enter the student name, parent name, and at least one contact phone number.',
			'Yeu cau tu van da duoc ghi nhan. Trung tam se lien he voi ban som nhat.' => 'Your consultation request has been received. The center will contact you soon.',
			'Khong the gui yeu cau tu van. Vui long thu lai sau.' => 'Could not submit the consultation request. Please try again later.',
			'Vui lòng chọn lớp học và nhập tiêu đề buổi học.' => 'Please choose a class and enter the lesson title.',
			'Lịch học được chọn không thuộc lớp học này.' => 'The selected schedule does not belong to this class.',
			'Đã cập nhật buổi học.' => 'Lesson has been updated.',
			'Đã tạo buổi học mới.' => 'New lesson has been created.',
			'Vui lòng nhập tiêu đề và tải lên file.' => 'Please enter a title and upload a file.',
			'Đã lưu tài liệu thành công.' => 'Material has been saved successfully.',
			'Đã xóa tài liệu.' => 'Material has been deleted.',
			'Không thể xóa tài liệu. Tài liệu này có thể đang được sử dụng hoặc dữ liệu không hợp lệ.' => 'Could not delete the material. It may be in use or invalid.',
			'Đã lưu thông báo thành công.' => 'Notification has been saved successfully.',
			'Thông báo không hợp lệ.' => 'Invalid notification.',
			'Đã xóa thông báo.' => 'Notification has been deleted.',
			'Không thể xóa thông báo. Vui lòng thử lại.' => 'Could not delete notification. Please try again.',
			'Đã cập nhật mật khẩu thành công.' => 'Password has been updated successfully.',
			'Đã cập nhật ảnh đại diện.' => 'Avatar has been updated.',
			'Vui lòng điền đầy đủ email và số điện thoại.' => 'Please enter both email and phone number.',
			'Đã cập nhật thông tin hồ sơ.' => 'Profile information has been updated.',
		],
	];

	$translated = $messages[$locale][$normalized] ?? null;
	if ($translated !== null) {
		return $translated;
	}

	if ($locale === 'en') {
		if (preg_match('/^Đã cập nhật (\d+) bài nộp\.$/u', $normalized, $matches) === 1) {
			return 'Updated ' . $matches[1] . ' submissions.';
		}

		$prefixes = [
			'Cập nhật phân quyền thất bại: ' => 'Failed to update permissions: ',
			'Lỗi xử lý file video: ' => 'Video file processing error: ',
			'Không thể lưu người dùng: ' => 'Could not save user: ',
			'Không thể cập nhật hồ sơ: ' => 'Could not update profile: ',
			'Không thể tạo tài khoản giáo viên: ' => 'Could not create teacher account: ',
			'Không thể tạo tài khoản học viên: ' => 'Could not create student account: ',
		];

		foreach ($prefixes as $source => $target) {
			if (str_starts_with($normalized, $source)) {
				return $target . substr($normalized, strlen($source));
			}
		}
	}

	return $messages['vi'][$normalized] ?? $message;
}

function localized_current_url(string $locale): string
{
	$parts = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'));
	$path = (string) ($parts['path'] ?? '/');
	parse_str((string) ($parts['query'] ?? ''), $query);
	$query['lang'] = normalize_locale($locale);

	return $path . '?' . http_build_query($query);
}
