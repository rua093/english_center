# Mail System Setup

## Mục tiêu

Repo hiện đã có:

- SMTP transport thuần PHP trong `public_html/core/mailer.php`
- Mail template renderer trong `public_html/core/mail_templates.php`
- Outbox + retry queue
- Password reset bằng OTP qua email
- Tích hợp email cho lead, tạo user, đổi mật khẩu, nhắc học phí, notification thủ công

## Bước triển khai

1. Chạy migration SQL:

```sql
SOURCE database/migrate_mail_system_2026_05_10.sql;
```

2. Cấu hình SMTP trong `public_html/config/mail.php` hoặc override bằng cách sửa các hằng:

```php
define('MAIL_ENABLED', true);
define('MAIL_HOST', 'smtp.your-provider.com');
define('MAIL_PORT', 587);
define('MAIL_ENCRYPTION', 'tls'); // tls | ssl | none
define('MAIL_USERNAME', 'smtp-user');
define('MAIL_PASSWORD', 'smtp-password');
define('MAIL_FROM_ADDRESS', 'no-reply@your-domain.com');
define('MAIL_FROM_NAME', 'English Center Platform');
define('MAIL_REPLY_TO_ADDRESS', 'support@your-domain.com');
define('MAIL_REPLY_TO_NAME', 'English Center Support');
define('APP_BASE_URL', 'https://your-domain.com');
define('MAIL_INTERNAL_NOTIFICATION_RECIPIENTS', 'admissions@your-domain.com,staff@your-domain.com');
```

3. Thiết lập cron:

```bash
php public_html/cron/process-email-outbox.php
php public_html/cron/purge-expired-password-resets.php
php public_html/cron/sync-overdue-tuition-notifications.php
```

Khuyến nghị:

- `process-email-outbox.php`: mỗi 1 phút
- `purge-expired-password-resets.php`: mỗi ngày 1 lần
- `sync-overdue-tuition-notifications.php`: mỗi ngày 1 lần hoặc mỗi 6 giờ

## Luồng đã tích hợp

- Quên mật khẩu: `/?page=forgot-password`
- Lead công khai / đăng ký tư vấn: queue email xác nhận + email nội bộ
- Tạo user mới từ admin: queue email tài khoản
- Đổi mật khẩu bởi admin: queue email thông báo
- Chuyển lead/job application thành user: queue email tài khoản
- Thông báo nội bộ: có thể chọn gửi thêm email
- Học phí quá hạn: queue email nhắc học phí khi cron tạo notification

## Lưu ý vận hành

- Nếu `MAIL_ENABLED = false`, hệ thống vẫn queue logic ở mức code nhưng worker sẽ không gửi mail.
- Nếu `MAIL_ENABLED = true` mà SMTP chưa đủ cấu hình, worker sẽ báo lỗi rõ ràng.
- Nên dùng tài khoản SMTP chuyên dụng, không dùng mật khẩu tài khoản cá nhân chính.
- Với Gmail/Workspace nên dùng app password hoặc SMTP relay.
