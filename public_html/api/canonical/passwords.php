<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/api_helpers.php';
require_once __DIR__ . '/../../models/MailModel.php';

function api_passwords_request_reset_action(): void
{
    api_require_post(page_url('forgot-password'));

    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $requestedIp = trim((string) ($_SERVER['REMOTE_ADDR'] ?? ''));
    $requestedUserAgent = trim((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''));

    $mailModel = new MailModel();
    $result = $mailModel->requestPasswordReset($email, $requestedIp, $requestedUserAgent);

    if (api_expects_json()) {
        api_success('Nếu email tồn tại, hệ thống đã gửi mã xác nhận.', [
            'flow_token' => (string) ($result['flow_token'] ?? ''),
        ]);
    }

    set_flash('success', 'Nếu email tồn tại, hệ thống đã gửi mã xác nhận.');
    redirect(page_url('forgot-password'));
}

function api_passwords_verify_otp_action(): void
{
    api_require_post(page_url('forgot-password'));

    $flowToken = trim((string) ($_POST['flow_token'] ?? ''));
    $otpCode = trim((string) ($_POST['otp_code'] ?? ''));

    if ($flowToken === '' || $otpCode === '') {
        if (api_expects_json()) {
            api_error('Vui lòng nhập đầy đủ mã xác nhận.', ['code' => 'OTP_REQUIRED'], 422);
        }

        set_flash('error', 'Vui lòng nhập đầy đủ mã xác nhận.');
        redirect(page_url('forgot-password'));
    }

    $verified = (new MailModel())->verifyPasswordResetOtp($flowToken, $otpCode);
    if (!$verified) {
        if (api_expects_json()) {
            api_error('Mã xác nhận không hợp lệ hoặc đã hết hạn.', ['code' => 'OTP_INVALID'], 422);
        }

        set_flash('error', 'Mã xác nhận không hợp lệ hoặc đã hết hạn.');
        redirect(page_url('forgot-password'));
    }

    if (api_expects_json()) {
        api_success('Xác thực thành công.');
    }

    set_flash('success', 'Xác thực thành công. Bạn có thể đặt mật khẩu mới.');
    redirect(page_url('forgot-password'));
}

function api_passwords_confirm_reset_action(): void
{
    api_require_post(page_url('forgot-password'));

    $flowToken = trim((string) ($_POST['flow_token'] ?? ''));
    $newPassword = (string) ($_POST['new_password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    if ($flowToken === '' || $newPassword === '' || $confirmPassword === '') {
        if (api_expects_json()) {
            api_error('Vui lòng nhập đầy đủ thông tin đặt lại mật khẩu.', ['code' => 'RESET_REQUIRED'], 422);
        }

        set_flash('error', 'Vui lòng nhập đầy đủ thông tin đặt lại mật khẩu.');
        redirect(page_url('forgot-password'));
    }

    if ($newPassword !== $confirmPassword) {
        if (api_expects_json()) {
            api_error('Mật khẩu xác nhận không khớp.', ['code' => 'PASSWORD_CONFIRM_MISMATCH'], 422);
        }

        set_flash('error', 'Mật khẩu xác nhận không khớp.');
        redirect(page_url('forgot-password'));
    }

    if (strlen($newPassword) < 8) {
        if (api_expects_json()) {
            api_error('Mật khẩu mới phải có ít nhất 8 ký tự.', ['code' => 'PASSWORD_TOO_SHORT'], 422);
        }

        set_flash('error', 'Mật khẩu mới phải có ít nhất 8 ký tự.');
        redirect(page_url('forgot-password'));
    }

    $completed = (new MailModel())->completePasswordReset($flowToken, $newPassword);
    if (!$completed) {
        if (api_expects_json()) {
            api_error('Yêu cầu đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.', ['code' => 'RESET_INVALID'], 422);
        }

        set_flash('error', 'Yêu cầu đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.');
        redirect(page_url('forgot-password'));
    }

    if (api_expects_json()) {
        api_success('Mật khẩu đã được cập nhật thành công.', [
            'redirect_url' => page_url('login'),
        ]);
    }

    set_flash('success', 'Mật khẩu đã được cập nhật. Bạn có thể đăng nhập lại.');
    redirect(page_url('login'));
}
