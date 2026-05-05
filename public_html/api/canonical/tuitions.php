<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/api_helpers.php';
require_once __DIR__ . '/../../core/page_actions.php';
require_once __DIR__ . '/../../models/AcademicModel.php';

function api_tuitions_is_admin(): bool
{
	$user = auth_user();
	return is_array($user) && (string) ($user['role'] ?? '') === 'admin';
}

function api_tuitions_can_manage_directly(): bool
{
	if (api_tuitions_is_admin()) {
		return true;
	}

	return has_any_permission([
		'finance.tuition.create',
		'finance.tuition.update',
	]);
}

function api_tuitions_can_delete_directly(): bool
{
	if (api_tuitions_is_admin()) {
		return true;
	}

	return has_any_permission([
		'finance.tuition.delete',
	]);
}

function api_tuitions_fail_or_redirect(string $message, string $redirectPath, int $httpCode = 400): never
{
	if (api_expects_json()) {
		api_error($message, [], $httpCode);
	}

	set_flash('error', $message);
	redirect($redirectPath);
}

function api_tuitions_save_action(): void
{
	api_guard_admin_or_staff();
	api_guard_permission('finance.tuition.update');
	api_require_post(page_url('tuition-finance'));

	if (!api_tuitions_can_manage_directly()) {
		set_flash('error', 'Bạn không có quyền CRUD trực tiếp học phí. Vui lòng gửi yêu cầu phê duyệt.');
		redirect(page_url('tuition-finance'));
	}

	$id = input_int($_POST, 'id');
	$paymentPlan = input_string($_POST, 'payment_plan', 'full');
	$packageId = max(0, input_int($_POST, 'package_id'));
	$monthlyMonths = input_int($_POST, 'monthly_months');
	$monthlyStartMonth = input_string($_POST, 'monthly_start_month');
	$monthlyEndMonth = input_string($_POST, 'monthly_end_month');
	$monthlyPaymentDay = input_int($_POST, 'monthly_payment_day');
	$academicModel = new AcademicModel();

	if ($id <= 0) {
		set_flash('error', 'Vui lòng tạo học phí từ trang Đăng ký khóa học. Trang này chỉ hỗ trợ chỉnh sửa hóa đơn đã có.');
		redirect(page_url('registration-finance'));
	}

	$existingFee = $academicModel->findTuitionFeeForEdit($id);
	if (!$existingFee) {
		set_flash('error', 'Không tìm thấy hóa đơn học phí cần cập nhật.');
		$query = $id > 0 ? ['edit' => $id] : [];
		redirect(page_url('tuition-finance', $query));
	}

	$classId = (int) ($existingFee['class_id'] ?? 0);
	$class = $classId > 0 ? $academicModel->findClass($classId) : null;
	if (!$class) {
		set_flash('error', 'Không tìm thấy lớp học gốc của hóa đơn.');
		redirect(page_url('tuition-finance', ['edit' => $id]));
	}

	$courseId = (int) ($class['course_id'] ?? 0);
	$baseAmount = max(0, (float) ($existingFee['base_amount'] ?? 0));
	if ($baseAmount <= 0) {
		$baseAmount = max(0, (float) ($existingFee['total_amount'] ?? 0));
	}

	$discountType = 'none';
	$discountPercent = 0.0;

	if ($packageId > 0) {
		$selectedPackage = $academicModel->findCoursePackage($packageId);
		if (!$selectedPackage) {
			set_flash('error', 'Ưu đãi đã chọn không tồn tại hoặc đã bị xóa.');
			redirect(page_url('tuition-finance', ['edit' => $id]));
		}

		$packageCourseId = (int) ($selectedPackage['course_id'] ?? 0);
		if ($packageCourseId > 0 && $packageCourseId !== $courseId) {
			set_flash('error', 'Ưu đãi đã chọn không áp dụng cho lớp học này.');
			redirect(page_url('tuition-finance', ['edit' => $id]));
		}

		$today = date('Y-m-d');
		$startDate = trim((string) ($selectedPackage['start_date'] ?? ''));
		$endDate = trim((string) ($selectedPackage['end_date'] ?? ''));
		if ($startDate !== '' && $today < $startDate) {
			set_flash('error', 'Ưu đãi đã chọn chưa đến ngày áp dụng.');
			redirect(page_url('tuition-finance', ['edit' => $id]));
		}

		if ($endDate !== '' && $today > $endDate) {
			set_flash('error', 'Ưu đãi đã chọn đã hết hạn áp dụng.');
			redirect(page_url('tuition-finance', ['edit' => $id]));
		}

		$discountType = strtoupper(trim((string) ($selectedPackage['promo_type'] ?? '')));
		$discountPercent = max(0, min(100, (float) ($selectedPackage['discount_value'] ?? 0)));
	}

	$academicModel->saveTuitionFee([
		'id' => $id,
		'payment_plan' => $paymentPlan,
		'package_id' => $packageId,
		'base_amount' => $baseAmount,
		'discount_type' => $discountType,
		'discount_amount' => $discountPercent,
		'monthly_months' => $monthlyMonths,
		'monthly_start_month' => $monthlyStartMonth,
		'monthly_end_month' => $monthlyEndMonth,
		'monthly_payment_day' => $monthlyPaymentDay,
	]);

	set_flash('success', 'Đã cập nhật học phí thành công.');
	redirect(page_url('tuition-finance'));
}

function api_tuitions_register_course_action(): void
{
	api_guard_admin_or_staff();
	api_guard_permission('finance.registration.create');
	api_require_post(page_url('registration-finance'));

	if (!api_tuitions_can_manage_directly()) {
		set_flash('error', 'Bạn không có quyền đăng ký khóa học và tạo học phí trực tiếp.');
		redirect(page_url('registration-finance'));
	}

	$studentId = input_int($_POST, 'student_id');
	$courseId = input_int($_POST, 'course_id');
	$classId = input_int($_POST, 'class_id');
	$packageId = input_int($_POST, 'package_id');
	$paymentPlan = input_string($_POST, 'payment_plan', 'full');
	$monthlyMonths = input_int($_POST, 'monthly_months');
	$monthlyStartMonth = input_string($_POST, 'monthly_start_month');
	$monthlyEndMonth = input_string($_POST, 'monthly_end_month');
	$monthlyPaymentDay = input_int($_POST, 'monthly_payment_day');
	$registrationFormPayload = [
		'student_id' => $studentId,
		'course_id' => $courseId,
		'class_id' => $classId,
		'package_id' => $packageId,
		'payment_plan' => $paymentPlan,
		'monthly_months' => $monthlyMonths,
		'monthly_start_month' => $monthlyStartMonth,
		'monthly_end_month' => $monthlyEndMonth,
		'monthly_payment_day' => $monthlyPaymentDay,
	];

	$redirectWithError = static function (string $message, array $payload): void {
		set_flash('error', $message);
		set_flash('registration_form_old', (string) json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
		redirect(page_url('registration-finance'));
	};

	$academicModel = new AcademicModel();

	if ($studentId <= 0 || $courseId <= 0 || $classId <= 0) {
		$redirectWithError('Vui lòng chọn đầy đủ học viên, khóa học và lớp học hợp lệ.', $registrationFormPayload);
	}

	if (!in_array($paymentPlan, ['full', 'monthly'], true)) {
		$paymentPlan = 'full';
	}

	$normalizedMonthlyStart = null;
	$normalizedMonthlyEnd = null;
	$normalizedMonthlyMonths = null;
	$normalizedMonthlyPaymentDay = null;

	if ($paymentPlan === 'monthly') {
		if ($monthlyMonths <= 0) {
			$redirectWithError('Vui lòng nhập số tháng đóng học phí hợp lệ.', $registrationFormPayload);
		}

		if (!preg_match('/^\d{4}-\d{2}$/', $monthlyStartMonth)) {
			$redirectWithError('Vui lòng chọn tháng bắt đầu hợp lệ (YYYY-MM).', $registrationFormPayload);
		}

		[$startYear, $startMonth] = array_map('intval', explode('-', $monthlyStartMonth));
		if ($startMonth < 1 || $startMonth > 12) {
			$redirectWithError('Tháng bắt đầu không hợp lệ.', $registrationFormPayload);
		}

		$startDate = DateTime::createFromFormat('Y-m-d', sprintf('%04d-%02d-01', $startYear, $startMonth));
		if (!$startDate) {
			$redirectWithError('Tháng bắt đầu không hợp lệ.', $registrationFormPayload);
		}

		$endDate = (clone $startDate)->modify('+' . ($monthlyMonths - 1) . ' months');
		$normalizedMonthlyStart = $startDate->format('Y-m');
		$normalizedMonthlyEnd = $endDate->format('Y-m');
		$normalizedMonthlyMonths = $monthlyMonths;

		if ($monthlyEndMonth !== '' && preg_match('/^\d{4}-\d{2}$/', $monthlyEndMonth)) {
			$expectedEnd = $endDate->format('Y-m');
			if ($monthlyEndMonth !== $expectedEnd) {
				$redirectWithError('Tháng kết thúc không khớp với số tháng đăng ký.', $registrationFormPayload);
			}
		}

		if ($monthlyPaymentDay < 1 || $monthlyPaymentDay > 31) {
			$redirectWithError('Vui lòng nhập ngày đóng hàng tháng trong khoảng 1-31.', $registrationFormPayload);
		}
		$normalizedMonthlyPaymentDay = $monthlyPaymentDay;
		$registrationFormPayload['monthly_months'] = $normalizedMonthlyMonths;
		$registrationFormPayload['monthly_start_month'] = $normalizedMonthlyStart;
		$registrationFormPayload['monthly_end_month'] = $normalizedMonthlyEnd;
		$registrationFormPayload['monthly_payment_day'] = $normalizedMonthlyPaymentDay;
	}

	$student = $academicModel->findActiveUser($studentId);
	if (!$student || (string) ($student['role_name'] ?? '') !== 'student') {
		$redirectWithError('Học viên không hợp lệ hoặc đang không hoạt động.', $registrationFormPayload);
	}

	$course = $academicModel->findCourse($courseId);
	if (!$course) {
		$redirectWithError('Khóa học không tồn tại.', $registrationFormPayload);
	}

	$class = $academicModel->findClass($classId);
	if (!$class) {
		$redirectWithError('Lớp học không tồn tại.', $registrationFormPayload);
	}

	if ((int) ($class['course_id'] ?? 0) !== $courseId) {
		$redirectWithError('Lớp học đã chọn không thuộc khóa học tương ứng.', $registrationFormPayload);
	}

	$classStatus = (string) ($class['status'] ?? '');
	if (in_array($classStatus, ['cancelled', 'graduated'], true)) {
		$redirectWithError('Không thể đăng ký vào lớp đã kết thúc hoặc đã hủy.', $registrationFormPayload);
	}

	if ($academicModel->hasTuitionFeeForStudentClass($studentId, $classId)) {
		$redirectWithError('Học viên đã có hóa đơn học phí cho lớp này. Vui lòng kiểm tra danh sách học phí.', $registrationFormPayload);
	}

	$discountType = 'none';
	$discountPercent = 0.0;
	$discountLabel = '';

	if ($packageId > 0) {
		$selectedPackage = $academicModel->findCoursePackage($packageId);
		if (!$selectedPackage) {
			$redirectWithError('Ưu đãi đã chọn không tồn tại hoặc đã bị xóa.', $registrationFormPayload);
		}

		if (array_key_exists('quantity_limit', $selectedPackage) && $selectedPackage['quantity_limit'] !== null) {
			$remaining = (int) ($selectedPackage['quantity_remaining'] ?? 0);
			if ($remaining <= 0) {
				$redirectWithError('Ưu đãi đã hết lượt sử dụng. Vui lòng chọn ưu đãi khác.', $registrationFormPayload);
			}
		}

		$packageCourseId = (int) ($selectedPackage['course_id'] ?? 0);
		if ($packageCourseId > 0 && $packageCourseId !== $courseId) {
			$redirectWithError('Ưu đãi đã chọn không áp dụng cho khóa học này.', $registrationFormPayload);
		}

		$today = date('Y-m-d');
		$startDate = trim((string) ($selectedPackage['start_date'] ?? ''));
		$endDate = trim((string) ($selectedPackage['end_date'] ?? ''));

		if ($startDate !== '' && $today < $startDate) {
			$redirectWithError('Ưu đãi đã chọn chưa đến ngày áp dụng.', $registrationFormPayload);
		}

		if ($endDate !== '' && $today > $endDate) {
			$redirectWithError('Ưu đãi đã chọn đã hết hạn áp dụng.', $registrationFormPayload);
		}

		$promoType = strtoupper(trim((string) ($selectedPackage['promo_type'] ?? '')));
		if (!in_array($promoType, ['DURATION', 'SOCIAL', 'EVENT', 'GROUP'], true)) {
			$redirectWithError('Loại ưu đãi không hợp lệ.', $registrationFormPayload);
		}

		$discountType = $promoType;
		$discountPercent = max(0, min(100, (float) ($selectedPackage['discount_value'] ?? 0)));
		$discountLabel = trim((string) ($selectedPackage['name'] ?? ''));
	}

	$baseAmount = max(0, (float) ($course['base_price'] ?? 0));
	$discountApplied = round(($baseAmount * $discountPercent) / 100, 2);
	$totalAmount = max(0, $baseAmount - $discountApplied);

	try {
		$result = $academicModel->registerCourseAndCreateDebtTuition([
			'student_id' => $studentId,
			'class_id' => $classId,
			'package_id' => $packageId,
			'base_amount' => $baseAmount,
			'discount_type' => $discountType,
			'discount_amount' => $discountPercent,
			'payment_plan' => $paymentPlan,
			'monthly_months' => $normalizedMonthlyMonths,
			'monthly_start_month' => $normalizedMonthlyStart,
			'monthly_end_month' => $normalizedMonthlyEnd,
			'monthly_payment_day' => $normalizedMonthlyPaymentDay,
			'enrollment_date' => date('Y-m-d'),
		]);
	} catch (RuntimeException $exception) {
		$redirectWithError($exception->getMessage(), $registrationFormPayload);
	}

	$tuitionId = (int) ($result['tuition_id'] ?? 0);
	$alreadyEnrolled = (bool) ($result['already_enrolled'] ?? false);
	$successMessage = $alreadyEnrolled
		? 'Đăng ký thành công và đã tạo khoản học phí mới ở trạng thái debt cho học viên đã có trong lớp.'
		: 'Đăng ký thành công: đã thêm học viên vào lớp và tạo khoản học phí trạng thái debt.';

	if ($tuitionId > 0) {
		$successMessage .= ' Mã học phí #' . $tuitionId . ' | Tổng cần thu: ' . format_money($totalAmount) . '.';
		set_flash('registration_success_tuition_id', (string) $tuitionId);
	}

	if ($packageId > 0) {
		$discountPercentText = rtrim(rtrim(number_format($discountPercent, 2, '.', ''), '0'), '.');
		$successMessage .= ' Ưu đãi áp dụng: ' . ($discountLabel !== '' ? $discountLabel : ('#' . $packageId)) . ' (-' . $discountPercentText . '%).';
	}

	set_flash('success', $successMessage);
	redirect(page_url('registration-finance'));
}

function api_tuitions_update_learning_status_action(): void
{
	api_tuitions_fail_or_redirect('Trạng thái học viên trong lớp hiện đã được chuẩn hóa mặc định là chính thức.', page_url('registration-finance'), 410);
}

function api_tuitions_delete_action(): void
{
	api_guard_admin_or_staff();
	api_require_post(page_url('tuition-finance'));

	$user = auth_user();
	$tuitionId = input_int($_POST, 'tuition_id');

	if (!api_tuitions_can_delete_directly() && $user && (string) $user['role'] === 'staff') {
		if ($tuitionId > 0) {
			$fee = (new AcademicModel())->findTuitionFee($tuitionId);
			if ($fee) {
				queue_approval_request(
					'tuition_delete',
					sprintf(
						'Yêu cầu xóa học phí #%d | Học viên: %s | Lớp: %s | Lý do: Thao tác xóa trực tiếp của Staff cần duyệt.',
						$tuitionId,
						(string) ($fee['student_name'] ?? 'N/A'),
						(string) ($fee['class_name'] ?? 'N/A')
					),
					[
						'tuition_id' => $tuitionId,
					]
				);
			}
		}

		set_flash('success', 'Yêu cầu xóa học phí đã được chuyển sang luồng phê duyệt.');
		redirect(page_url('tuition-finance'));
	}

	if (!api_tuitions_can_delete_directly()) {
		api_guard_permission('finance.tuition.delete');
	}

	if ($tuitionId > 0) {
		try {
			(new AcademicModel())->deleteTuitionFee($tuitionId);
			set_flash('success', 'Đã xóa học phí thành công.');
		} catch (Throwable) {
			set_flash('error', 'Không thể xóa học phí. Học phí này có thể đã có giao dịch thanh toán liên quan.');
		}
	}

	redirect(page_url('tuition-finance'));
}

function api_tuitions_update_action(): void
{
	api_guard_permission('student.tuition.update');
	api_require_post(page_url('dashboard-student'));

	$tuitionId = input_int($_POST, 'tuition_id');
	$amount = input_float($_POST, 'amount');
	$user = auth_user();

	if ($tuitionId > 0 && $amount > 0 && $user) {
		$success = (new AcademicModel())->recordStudentWebPayment((int) $user['id'], $tuitionId, $amount, 'bank_transfer');
		if ($success) {
			set_flash('success', 'Đã ghi nhận thanh toán và tự động tạo biên lai thành công.');
		} else {
			set_flash('error', 'Không tìm thấy học phí hợp lệ để cập nhật thanh toán.');
		}
	}

	redirect(page_url('dashboard-student'));
}

function api_tuitions_request_adjust_action(): void
{
	api_guard_admin_or_staff();
	api_guard_permission('finance.tuition.view');
	api_require_post(page_url('tuition-finance'));

	$tuitionId = input_int($_POST, 'tuition_id');
	$requestedPaid = input_float($_POST, 'requested_amount_paid');
	$reason = input_string($_POST, 'reason', 'Yêu cầu chỉnh sửa số tiền đã thu.');

	$academicModel = new AcademicModel();
	$fee = $academicModel->findTuitionFee($tuitionId);
	if ($fee) {
		$content = sprintf(
			'Yêu cầu chỉnh sửa tài chính học phí #%d | Học viên: %s | Lớp: %s | Số đã thu hiện tại: %s | Số đề xuất: %s | Lý do: %s',
			$tuitionId,
			(string) ($fee['student_name'] ?? 'N/A'),
			(string) ($fee['class_name'] ?? 'N/A'),
			format_money((float) ($fee['amount_paid'] ?? 0)),
			format_money($requestedPaid),
			$reason !== '' ? $reason : 'Không có ghi chú'
		);

		queue_approval_request('finance_adjust', $content, [
			'tuition_id' => $tuitionId,
			'requested_amount_paid' => $requestedPaid,
		]);

		set_flash('success', 'Đã gửi yêu cầu chỉnh sửa tài chính để Admin phê duyệt.');
	}

	redirect(page_url('tuition-finance'));
}

function api_tuitions_request_delete_action(): void
{
	api_guard_admin_or_staff();
	api_guard_permission('finance.tuition.view');
	api_require_post(page_url('tuition-finance'));

	$tuitionId = input_int($_POST, 'tuition_id');
	$reason = input_string($_POST, 'reason', 'Yêu cầu chỉnh sửa/xóa dữ liệu học phí.');
	$academicModel = new AcademicModel();
	$fee = $academicModel->findTuitionFee($tuitionId);

	if ($fee) {
		$content = sprintf(
			'Yêu cầu xóa học phí #%d | Học viên: %s | Lớp: %s | Lý do: %s',
			$tuitionId,
			(string) ($fee['student_name'] ?? 'N/A'),
			(string) ($fee['class_name'] ?? 'N/A'),
			$reason !== '' ? $reason : 'Không có ghi chú'
		);

		queue_approval_request('tuition_delete', $content, ['tuition_id' => $tuitionId]);
		set_flash('success', 'Đã gửi yêu cầu xóa học phí để Admin phê duyệt.');
	}

	redirect(page_url('tuition-finance'));
}
