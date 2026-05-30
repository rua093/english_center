<?php
declare(strict_types=1);

require_once __DIR__ . '/mail.php';

function mail_template_render(string $templateKey, array $data = []): array
{
    return match ($templateKey) {
        'password_reset_otp' => mail_template_password_reset_otp($data),
        'password_reset_success' => mail_template_password_reset_success($data),
        'lead_confirmation' => mail_template_lead_confirmation($data),
        'lead_internal_notification' => mail_template_lead_internal_notification($data),
        'user_welcome_account' => mail_template_user_welcome_account($data),
        'user_password_changed' => mail_template_user_password_changed($data),
        'tuition_overdue' => mail_template_tuition_overdue($data),
        'system_notification' => mail_template_system_notification($data),
        default => throw new InvalidArgumentException('Unknown mail template: ' . $templateKey),
    };
}

function mail_template_shell(string $headline, string $introHtml, string $bodyHtml, string $footerHtml = ''): string
{
    $appName = mail_html_escape((string) APP_NAME);

    return '<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light only">
    <meta name="supported-color-schemes" content="light only">
    <title>' . $headline . '</title>
</head>
<body style="margin:0;background:#eef4ff;font-family:Arial,Helvetica,sans-serif;color:#0f172a;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#eef4ff;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:680px;background:#ffffff;border-radius:20px;overflow:hidden;border:1px solid #dbe7ff;box-shadow:0 10px 30px rgba(15,23,42,0.08);">
                    <tr>
                        <td style="background:#f4f8ff;padding:28px 32px;border-bottom:1px solid #dbe7ff;">
                            <div style="display:inline-block;padding:7px 12px;border-radius:999px;background:#dbeafe;color:#1d4ed8;font-size:12px;letter-spacing:0.14em;text-transform:uppercase;font-weight:800;">' . $appName . '</div>
                            <div style="margin-top:14px;font-size:30px;line-height:1.22;font-weight:800;color:#0f172a;">' . $headline . '</div>
                            <div style="margin-top:12px;font-size:15px;line-height:1.8;color:#334155;">' . $introHtml . '</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px 32px;background:#ffffff;color:#0f172a;">
                            ' . $bodyHtml . '
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 32px;background:#f8fafc;border-top:1px solid #e2e8f0;font-size:12px;line-height:1.8;color:#64748b;">
                            ' . ($footerHtml !== '' ? $footerHtml : mail_template_default_footer_html()) . '
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
}

function mail_template_brand_name(): string
{
    $brand = trim(mail_from_name());
    if ($brand !== '') {
        return $brand;
    }

    return trim((string) APP_NAME);
}

function mail_template_brand_label(): string
{
    return mail_html_escape(mail_template_brand_name());
}

function mail_template_base_url(): string
{
    return mail_abs_url('/');
}

function mail_template_support_email(): string
{
    return mail_html_escape(mail_reply_to_address());
}

function mail_template_default_footer_html(): string
{
    $brand = mail_template_brand_label();
    $supportEmail = mail_template_support_email();
    $websiteUrl = mail_html_escape(mail_template_base_url());

    return '<strong>' . $brand . '</strong><br>'
        . 'Email giao dịch được gửi tự động từ hệ thống quản lý trung tâm.<br>'
        . 'Hỗ trợ: <a href="mailto:' . $supportEmail . '" style="color:#2563eb;text-decoration:none;word-break:break-word;">' . $supportEmail . '</a><br>'
        . 'Website: <a href="' . $websiteUrl . '" style="color:#2563eb;text-decoration:none;word-break:break-word;">' . $websiteUrl . '</a>';
}

function mail_template_fact_item_html(string $label, string $value, bool $emphasis = false): string
{
    $valueStyle = $emphasis
        ? 'font-size:24px;line-height:1.3;font-weight:800;color:#0f172a;'
        : 'font-size:18px;line-height:1.5;font-weight:700;color:#0f172a;';

    return '<div style="margin:0 0 12px;padding:16px 18px;border-radius:16px;background:#f8fafc;border:1px solid #e2e8f0;">'
        . '<div style="margin:0 0 6px;font-size:12px;letter-spacing:0.08em;text-transform:uppercase;font-weight:800;color:#64748b;">' . $label . '</div>'
        . '<div style="' . $valueStyle . '">' . $value . '</div>'
        . '</div>';
}

function mail_template_password_reset_otp(array $data): array
{
    $userName = mail_html_escape(trim((string) ($data['user_name'] ?? 'bạn')));
    $otpCode = mail_html_escape(trim((string) ($data['otp_code'] ?? '')));
    $expiresInMinutes = max(1, (int) ($data['expires_in_minutes'] ?? 10));
    $resetUrl = mail_html_escape((string) ($data['reset_url'] ?? mail_forgot_password_url()));
    $brand = mail_template_brand_name();

    $subject = '[' . $brand . '] Mã xác nhận đặt lại mật khẩu';
    $html = mail_template_shell(
        'Khôi phục mật khẩu',
        'Xin chào <strong>' . $userName . '</strong>, hệ thống ' . mail_template_brand_label() . ' vừa nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.',
        '<p style="margin:0 0 16px;font-size:15px;line-height:1.8;color:#334155;">Sử dụng mã xác nhận bên dưới để tiếp tục. Mã có hiệu lực trong <strong>' . $expiresInMinutes . ' phút</strong>.</p>
        <div style="margin:22px 0;padding:18px 22px;border-radius:18px;background:#eff6ff;border:1px solid #bfdbfe;text-align:center;">
            <div style="font-size:12px;letter-spacing:0.18em;text-transform:uppercase;color:#2563eb;font-weight:800;">Mã xác nhận</div>
            <div style="margin-top:12px;font-size:34px;letter-spacing:0.32em;font-weight:900;color:#0f172a;">' . $otpCode . '</div>
        </div>
        <p style="margin:0 0 16px;font-size:15px;line-height:1.8;color:#334155;">Bạn cũng có thể quay lại màn hình khôi phục mật khẩu tại liên kết sau:</p>
        <p style="margin:0;"><a href="' . $resetUrl . '" style="display:inline-block;padding:12px 18px;border-radius:999px;background:#1d4ed8;color:#ffffff;text-decoration:none;font-weight:700;">Mở trang khôi phục mật khẩu</a></p>
        <p style="margin:18px 0 0;font-size:14px;line-height:1.7;color:#64748b;">Nếu bạn không thực hiện yêu cầu này, hãy bỏ qua email. Mật khẩu hiện tại của bạn vẫn an toàn.</p>'
    );

    $text = mail_text_lines([
        'Khoi phuc mat khau',
        'Xin chao ' . trim((string) ($data['user_name'] ?? 'ban')) . ',',
        'Ma xac nhan cua ban la: ' . trim((string) ($data['otp_code'] ?? '')),
        'Ma co hieu luc trong ' . $expiresInMinutes . ' phut.',
        'Trang khoi phuc: ' . (string) ($data['reset_url'] ?? mail_forgot_password_url()),
        'Neu ban khong yeu cau dat lai mat khau, hay bo qua email nay.',
    ]);

    return compact('subject', 'html', 'text');
}

function mail_template_password_reset_success(array $data): array
{
    $userName = mail_html_escape(trim((string) ($data['user_name'] ?? 'bạn')));
    $loginUrl = mail_html_escape((string) ($data['login_url'] ?? mail_login_url()));
    $brand = mail_template_brand_name();

    $subject = '[' . $brand . '] Mật khẩu tài khoản đã được cập nhật';
    $html = mail_template_shell(
        'Mật khẩu đã được cập nhật',
        'Xin chào <strong>' . $userName . '</strong>, mật khẩu tài khoản tại ' . mail_template_brand_label() . ' vừa được thay đổi thành công.',
        '<p style="margin:0 0 16px;font-size:15px;line-height:1.8;color:#334155;">Bạn có thể đăng nhập lại ngay bây giờ bằng mật khẩu mới.</p>
        <p style="margin:0 0 16px;"><a href="' . $loginUrl . '" style="display:inline-block;padding:12px 18px;border-radius:999px;background:#1d4ed8;color:#ffffff;text-decoration:none;font-weight:700;">Đăng nhập</a></p>
        <p style="margin:0;font-size:14px;line-height:1.7;color:#64748b;">Nếu bạn không thực hiện thay đổi này, hãy liên hệ trung tâm ngay để được hỗ trợ bảo mật tài khoản.</p>'
    );

    $text = mail_text_lines([
        'Mat khau da duoc cap nhat thanh cong.',
        'Dang nhap tai: ' . (string) ($data['login_url'] ?? mail_login_url()),
        'Neu ban khong thuc hien thay doi nay, hay lien he trung tam ngay.',
    ]);

    return compact('subject', 'html', 'text');
}

function mail_template_lead_confirmation(array $data): array
{
    $studentName = mail_html_escape(trim((string) ($data['student_name'] ?? 'Học viên')));
    $parentName = mail_html_escape(trim((string) ($data['parent_name'] ?? 'Quý phụ huynh')));
    $phone = mail_html_escape(trim((string) ($data['parent_phone'] ?? '')));
    $studyTime = mail_html_escape(trim((string) ($data['study_time'] ?? '')));
    $subject = '[' . mail_template_brand_name() . '] Trung tâm đã nhận yêu cầu tư vấn';

    $detailItems = '';
    if ($phone !== '') {
        $detailItems .= '<li style="margin-bottom:8px;"><strong>Số điện thoại:</strong> ' . $phone . '</li>';
    }
    if ($studyTime !== '') {
        $detailItems .= '<li style="margin-bottom:8px;"><strong>Khung giờ mong muốn:</strong> ' . $studyTime . '</li>';
    }

    $html = mail_template_shell(
        'Đã nhận yêu cầu tư vấn',
        'Xin chào <strong>' . $parentName . '</strong>, trung tâm đã ghi nhận yêu cầu tư vấn cho học viên <strong>' . $studentName . '</strong>.',
        '<p style="margin:0 0 16px;font-size:15px;line-height:1.8;color:#334155;">Bộ phận tư vấn sẽ chủ động liên hệ lại trong thời gian sớm nhất để xác nhận nhu cầu học tập và đề xuất lộ trình phù hợp.</p>
        <ul style="margin:0 0 16px 18px;padding:0;font-size:15px;line-height:1.8;color:#334155;">' . $detailItems . '</ul>
        <p style="margin:0;font-size:14px;line-height:1.7;color:#64748b;">Vui lòng theo dõi email hoặc điện thoại trong thời gian tới. Cảm ơn bạn đã quan tâm đến chương trình học của trung tâm.</p>'
    );

    $text = mail_text_lines([
        'Trung tam da nhan yeu cau tu van.',
        'Hoc vien: ' . trim((string) ($data['student_name'] ?? 'Hoc vien')),
        'Phu huynh: ' . trim((string) ($data['parent_name'] ?? 'Quy phu huynh')),
        'So dien thoai: ' . trim((string) ($data['parent_phone'] ?? '')),
        'Khung gio mong muon: ' . trim((string) ($data['study_time'] ?? '')),
        'Bo phan tu van se lien he lai som nhat.',
    ]);

    return compact('subject', 'html', 'text');
}

function mail_template_lead_internal_notification(array $data): array
{
    $studentName = mail_html_escape(trim((string) ($data['student_name'] ?? 'Học viên')));
    $parentName = mail_html_escape(trim((string) ($data['parent_name'] ?? '')));
    $parentPhone = mail_html_escape(trim((string) ($data['parent_phone'] ?? '')));
    $parentEmail = mail_html_escape(trim((string) ($data['parent_email'] ?? '')));
    $studyTime = mail_html_escape(trim((string) ($data['study_time'] ?? '')));
    $source = mail_html_escape(trim((string) ($data['referral_source'] ?? 'website')));

    $subject = '[' . mail_template_brand_name() . '] Lead tư vấn mới từ website';
    $html = mail_template_shell(
        'Lead tư vấn mới',
        'Hệ thống vừa ghi nhận một lead mới từ website.',
        '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;">
            <tr><td style="padding:8px 0;border-bottom:1px solid #e2e8f0;"><strong>Học viên</strong></td><td style="padding:8px 0;border-bottom:1px solid #e2e8f0;">' . $studentName . '</td></tr>
            <tr><td style="padding:8px 0;border-bottom:1px solid #e2e8f0;"><strong>Phụ huynh</strong></td><td style="padding:8px 0;border-bottom:1px solid #e2e8f0;">' . $parentName . '</td></tr>
            <tr><td style="padding:8px 0;border-bottom:1px solid #e2e8f0;"><strong>Điện thoại</strong></td><td style="padding:8px 0;border-bottom:1px solid #e2e8f0;">' . $parentPhone . '</td></tr>
            <tr><td style="padding:8px 0;border-bottom:1px solid #e2e8f0;"><strong>Email</strong></td><td style="padding:8px 0;border-bottom:1px solid #e2e8f0;">' . ($parentEmail !== '' ? $parentEmail : '—') . '</td></tr>
            <tr><td style="padding:8px 0;border-bottom:1px solid #e2e8f0;"><strong>Nguồn</strong></td><td style="padding:8px 0;border-bottom:1px solid #e2e8f0;">' . $source . '</td></tr>
            <tr><td style="padding:8px 0;"><strong>Khung giờ</strong></td><td style="padding:8px 0;">' . ($studyTime !== '' ? $studyTime : '—') . '</td></tr>
        </table>'
    );

    $text = mail_text_lines([
        'Lead tu van moi tu website.',
        'Hoc vien: ' . trim((string) ($data['student_name'] ?? 'Hoc vien')),
        'Phu huynh: ' . trim((string) ($data['parent_name'] ?? '')),
        'Dien thoai: ' . trim((string) ($data['parent_phone'] ?? '')),
        'Email: ' . trim((string) ($data['parent_email'] ?? '')),
        'Nguon: ' . trim((string) ($data['referral_source'] ?? 'website')),
        'Khung gio: ' . trim((string) ($data['study_time'] ?? '')),
    ]);

    return compact('subject', 'html', 'text');
}

function mail_template_user_welcome_account(array $data): array
{
    $fullName = mail_html_escape(trim((string) ($data['full_name'] ?? 'bạn')));
    $username = mail_html_escape(trim((string) ($data['username'] ?? '')));
    $roleLabel = mail_html_escape(trim((string) ($data['role_label'] ?? 'người dùng')));
    $loginUrl = mail_html_escape((string) ($data['login_url'] ?? mail_login_url()));
    $forgotPasswordUrl = mail_html_escape(mail_forgot_password_url());
    $brand = mail_template_brand_name();

    $subject = '[' . $brand . '] Tài khoản ' . trim((string) ($data['role_label'] ?? 'người dùng')) . ' của bạn đã được tạo';
    $html = mail_template_shell(
        'Tài khoản đã được tạo',
        'Xin chào <strong>' . $fullName . '</strong>, ' . mail_template_brand_label() . ' vừa tạo tài khoản ' . $roleLabel . ' cho bạn.',
        '<p style="margin:0 0 16px;font-size:15px;line-height:1.8;color:#334155;">Để bắt đầu sử dụng tài khoản, vui lòng tạo mật khẩu của riêng bạn theo liên kết bên dưới.</p>
        <div style="margin:0 0 20px;padding:18px 20px;border-radius:18px;background:#f8fafc;border:1px solid #e2e8f0;">
            <div style="margin-bottom:8px;"><strong>Tên đăng nhập:</strong> ' . $username . '</div>
            <div><strong>Bước tiếp theo:</strong> Chọn "Quên mật khẩu" để tạo mật khẩu mới cho lần đăng nhập đầu tiên.</div>
        </div>
        <p style="margin:0 0 16px;"><a href="' . $forgotPasswordUrl . '" style="display:inline-block;padding:12px 18px;border-radius:999px;background:#1d4ed8;color:#ffffff;text-decoration:none;font-weight:700;">Tạo mật khẩu để đăng nhập</a></p>
        <p style="margin:0 0 10px;font-size:14px;line-height:1.7;color:#64748b;">Sau khi tạo mật khẩu xong, bạn có thể đăng nhập tại: <a href="' . $loginUrl . '" style="color:#2563eb;text-decoration:none;">' . $loginUrl . '</a></p>
        <p style="margin:0;font-size:14px;line-height:1.7;color:#64748b;">Vì lý do bảo mật, hệ thống không gửi mật khẩu qua email.</p>'
    );

    $text = mail_text_lines([
        'Tai khoan cua ban da duoc tao tai ' . $brand . '.',
        'Ten dang nhap: ' . trim((string) ($data['username'] ?? '')),
        'Tao mat khau moi tai: ' . mail_forgot_password_url(),
        'Dang nhap tai: ' . (string) ($data['login_url'] ?? mail_login_url()),
        'Vi ly do bao mat, he thong khong gui mat khau qua email.',
    ]);

    return compact('subject', 'html', 'text');
}

function mail_template_user_password_changed(array $data): array
{
    $fullName = mail_html_escape(trim((string) ($data['full_name'] ?? 'bạn')));
    $loginUrl = mail_html_escape((string) ($data['login_url'] ?? mail_login_url()));
    $brand = mail_template_brand_name();

    $subject = '[' . $brand . '] Mật khẩu tài khoản đã được thay đổi';
    $html = mail_template_shell(
        'Mật khẩu tài khoản đã thay đổi',
        'Xin chào <strong>' . $fullName . '</strong>, quản trị viên tại ' . mail_template_brand_label() . ' vừa cập nhật mật khẩu cho tài khoản của bạn.',
        '<p style="margin:0 0 16px;font-size:15px;line-height:1.8;color:#334155;">Nếu thay đổi này là do bạn yêu cầu, vui lòng đăng nhập lại để tiếp tục sử dụng hệ thống.</p>
        <p style="margin:0 0 16px;"><a href="' . $loginUrl . '" style="display:inline-block;padding:12px 18px;border-radius:999px;background:#1d4ed8;color:#ffffff;text-decoration:none;font-weight:700;">Đăng nhập</a></p>
        <p style="margin:0;font-size:14px;line-height:1.7;color:#64748b;">Nếu bạn không yêu cầu thay đổi mật khẩu, hãy liên hệ quản trị viên hoặc trung tâm ngay.</p>'
    );

    $text = mail_text_lines([
        'Mat khau tai khoan cua ban vua duoc cap nhat.',
        'Dang nhap tai: ' . (string) ($data['login_url'] ?? mail_login_url()),
        'Neu ban khong yeu cau thay doi nay, hay lien he trung tam ngay.',
    ]);

    return compact('subject', 'html', 'text');
}

function mail_template_tuition_overdue(array $data): array
{
    $studentName = mail_html_escape(trim((string) ($data['student_name'] ?? 'Học viên')));
    $className = mail_html_escape(trim((string) ($data['class_name'] ?? 'Chưa xác định')));
    $courseName = mail_html_escape(trim((string) ($data['course_name'] ?? '')));
    $dueDate = mail_html_escape(trim((string) ($data['due_date_label'] ?? '')));
    $remainingAmount = mail_html_escape(trim((string) ($data['remaining_amount_label'] ?? '0 VNĐ')));

    $subject = '[' . mail_template_brand_name() . '] Nhắc học phí cần thanh toán';
    $html = mail_template_shell(
        'Nhắc học phí quá hạn',
        'Xin chào <strong>' . $studentName . '</strong>, hệ thống ghi nhận khoản học phí theo tháng của bạn đang quá hạn thanh toán.',
        '<div style="margin:0 0 18px;">'
            . mail_template_fact_item_html('Lớp', $className)
            . mail_template_fact_item_html('Khóa học', $courseName !== '' ? $courseName : '—')
            . mail_template_fact_item_html('Hạn đóng', $dueDate)
            . mail_template_fact_item_html('Số tiền còn thiếu', $remainingAmount, true)
        . '</div>
        <p style="margin:18px 0 0;font-size:14px;line-height:1.7;color:#64748b;">Vui lòng liên hệ bộ phận tài chính nếu bạn cần hỗ trợ kiểm tra công nợ hoặc xác nhận giao dịch.</p>'
    );

    $text = mail_text_lines([
        'Nhac hoc phi qua han.',
        'Hoc vien: ' . trim((string) ($data['student_name'] ?? 'Hoc vien')),
        'Lop: ' . trim((string) ($data['class_name'] ?? 'Chua xac dinh')),
        'Khoa hoc: ' . trim((string) ($data['course_name'] ?? '')),
        'Han dong: ' . trim((string) ($data['due_date_label'] ?? '')),
        'So tien con thieu: ' . trim((string) ($data['remaining_amount_label'] ?? '0 VNĐ')),
    ]);

    return compact('subject', 'html', 'text');
}

function mail_template_system_notification(array $data): array
{
    $title = mail_html_escape(trim((string) ($data['title'] ?? 'Thông báo')));
    $messageHtml = trim((string) ($data['message_html'] ?? ''));
    if ($messageHtml === '') {
        $messageHtml = nl2br(mail_html_escape(trim((string) ($data['message'] ?? ''))));
    }

    $subject = '[' . mail_template_brand_name() . '] Thông báo mới: ' . trim((string) ($data['title'] ?? 'Thông báo'));
    $html = mail_template_shell(
        'Thông báo mới',
        'Bạn vừa nhận được một thông báo mới từ hệ thống.',
        '<div style="margin:0 0 16px;padding:18px 20px;border-radius:18px;background:#f8fafc;border:1px solid #e2e8f0;">
            <div style="font-size:18px;font-weight:800;color:#0f172a;">' . $title . '</div>
            <div style="margin-top:12px;font-size:15px;line-height:1.8;color:#334155;">' . $messageHtml . '</div>
        </div>'
    );

    $text = mail_text_lines([
        'Thong bao moi tu he thong',
        trim((string) ($data['title'] ?? 'Thong bao')),
        trim((string) ($data['message'] ?? '')),
    ]);

    return compact('subject', 'html', 'text');
}
