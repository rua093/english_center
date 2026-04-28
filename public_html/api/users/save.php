<?php
declare(strict_types=1);

require_admin_or_staff();

// Bước 1: Import thư viện xử lý file
require_once __DIR__ . '/../../core/file_storage.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(page_url('users-admin'));
}

$targetUserId = (int) ($_POST['id'] ?? 0);
if ($targetUserId > 0) {
    require_any_permission(['admin.user.update']);
} else {
    require_any_permission(['admin.user.create']);
}

// Bước 2: Xử lý Upload Video trước khi tạo payload
// Lấy URL cũ từ trường hidden
$videoUrl = trim((string) ($_POST['teacher_intro_video_url_hidden'] ?? ''));

// Kiểm tra chi tiết mảng $_FILES
if (isset($_FILES['teacher_intro_video_file'])) {
    $uploadErrorCode = (int) $_FILES['teacher_intro_video_file']['error'];
    
    if ($uploadErrorCode === UPLOAD_ERR_OK) {
        // Tải lên thành công tới thư mục tạm của server, tiến hành lưu vật lý
        try {
            $uploadedPath = file_storage_save_from_upload($_FILES['teacher_intro_video_file'], 'teacher_videos');
            if ($uploadedPath) {
                $videoUrl = $uploadedPath;
            }
        } catch (Throwable $e) {
            set_flash('error', 'Lỗi xử lý file video: ' . $e->getMessage());
            redirect(page_url('users-admin'));
        }
    } elseif ($uploadErrorCode !== UPLOAD_ERR_NO_FILE) {
        // BẮT CÁC LỖI TẢI LÊN ẨN CỦA PHP
        $uploadErrorMessages = [
            UPLOAD_ERR_INI_SIZE   => 'Dung lượng video vượt quá giới hạn cho phép của máy chủ (upload_max_filesize).',
            UPLOAD_ERR_FORM_SIZE  => 'Dung lượng video quá lớn.',
            UPLOAD_ERR_PARTIAL    => 'Video chỉ được tải lên một phần, do mạng chập chờn.',
            UPLOAD_ERR_NO_TMP_DIR => 'Thiếu thư mục tạm trên máy chủ (Server error).',
            UPLOAD_ERR_CANT_WRITE => 'Không có quyền ghi file lên đĩa (Permission denied).',
            UPLOAD_ERR_EXTENSION  => 'Một extension PHP đã chặn tiến trình tải lên.',
        ];
        $errorMessage = $uploadErrorMessages[$uploadErrorCode] ?? 'Lỗi tải video không xác định (Mã lỗi: ' . $uploadErrorCode . ')';
        
        // Báo lỗi ra màn hình
        set_flash('error', $errorMessage);
        redirect(page_url('users-admin'));
    }
}

$payload = [
    'id' => $targetUserId,
    'username' => trim((string) ($_POST['username'] ?? '')),
    'full_name' => trim((string) ($_POST['full_name'] ?? '')),
    'role_id' => (int) ($_POST['role_id'] ?? 0),
    'phone' => normalize_phone_string((string) ($_POST['phone'] ?? '')),
    'email' => trim((string) ($_POST['email'] ?? '')),
    'status' => (string) ($_POST['status'] ?? 'active'),
    'password' => (string) ($_POST['password'] ?? ''),
    'staff_position' => trim((string) ($_POST['staff_position'] ?? '')),
    'staff_approval_limit' => (float) ($_POST['staff_approval_limit'] ?? 0),
    'teacher_degree' => trim((string) ($_POST['teacher_degree'] ?? '')),
    'teacher_experience_years' => (int) ($_POST['teacher_experience_years'] ?? 0),
    'teacher_bio' => trim((string) ($_POST['teacher_bio'] ?? '')),
    // Sử dụng biến $videoUrl đã xử lý ở trên
    'teacher_intro_video_url' => $videoUrl,
    'student_parent_name' => trim((string) ($_POST['student_parent_name'] ?? '')),
    'student_parent_phone' => normalize_phone_string((string) ($_POST['student_parent_phone'] ?? '')),
    'student_school_name' => trim((string) ($_POST['student_school_name'] ?? '')),
    'student_target_score' => trim((string) ($_POST['student_target_score'] ?? '')),
    'student_entry_test_id' => (int) ($_POST['student_entry_test_id'] ?? 0),
];

if ($payload['username'] === '' || $payload['full_name'] === '' || $payload['role_id'] <= 0) {
    set_flash('error', 'Vui lòng nhập đầy đủ thông tin người dùng bắt buộc.');
    redirect(page_url('users-admin'));
}

if (!in_array($payload['status'], ['active', 'inactive'], true)) {
    set_flash('error', 'Vui lòng chọn trạng thái người dùng.');
    redirect(page_url('users-admin'));
}

$payload['staff_approval_limit'] = max(0, (float) $payload['staff_approval_limit']);
$payload['teacher_experience_years'] = max(0, (int) $payload['teacher_experience_years']);
$payload['student_entry_test_id'] = max(0, (int) $payload['student_entry_test_id']);

$adminModel = new AdminModel();
$role = $adminModel->findRoleById((int) $payload['role_id']);
if (!$role) {
    set_flash('error', 'Vai trò người dùng không hợp lệ.');
    redirect(page_url('users-admin'));
}

$roleName = (string) ($role['role_name'] ?? '');
if ($roleName === 'staff' && $payload['staff_position'] === '') {
    set_flash('error', 'Vui lòng nhập vị trí công tác cho nhân viên.');
    redirect(page_url('users-admin'));
}

try {
    // Sử dụng phương thức chung `saveUser` để xử lý cả create và update
    $adminModel->saveUser($payload);
    if ($payload['id'] > 0) {
        set_flash('success', 'Đã cập nhật thông tin người dùng: ' . $payload['username']);
    } else {
        set_flash('success', 'Đã tạo người dùng mới: ' . $payload['username']);
    }
} catch (Throwable $exception) {
    set_flash('error', 'Không thể lưu người dùng: ' . $exception->getMessage());
}

redirect(page_url('users-admin'));
