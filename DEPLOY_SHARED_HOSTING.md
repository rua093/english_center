# Checklist Deploy Shared Hosting

## 1. File config

- Sao chép `public_html/config/local.php.example` thành `public_html/config/local.php`.
- Cập nhật các giá trị sau:
  - `APP_ENV=production`
  - `APP_BASE_URL`
  - `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`
  - `APP_SECRET`
  - Cấu hình SMTP nếu cần gửi mail

## 2. Thư mục web public

- Trỏ document root của domain/subdomain vào `public_html/`.
- Hosting cần hỗ trợ rewrite rule bằng Apache `.htaccess`.

## 3. Các thư mục cần quyền ghi

Những thư mục dưới đây cần cho PHP quyền ghi:

- `public_html/storage/cache`
- `public_html/storage/exports`
- `public_html/storage/logs`
- `public_html/storage/tmp`
- `public_html/assets/uploads`
- `public_html/assets/uploads/homeworks`
- `public_html/assets/uploads/lessons`
- `public_html/assets/uploads/profile`
- `public_html/assets/uploads/teacher-videos`

Quyền thường dùng trên shared hosting:

- Thư mục: `755`
- Nếu upload hoặc ghi log bị lỗi, có thể thử tạm `775`
- Tránh dùng `777` trừ khi nhà cung cấp bắt buộc và bạn hiểu rõ rủi ro

Ví dụ trên Ubuntu VPS dùng Apache:

```bash
sudo chown -R www-data:www-data /var/www/html
sudo find /var/www/html/public_html/storage -type d -exec chmod 775 {} \;
sudo find /var/www/html/public_html/storage -type f -exec chmod 664 {} \;
sudo find /var/www/html/public_html/assets/uploads -type d -exec chmod 775 {} \;
sudo find /var/www/html/public_html/assets/uploads -type f -exec chmod 664 {} \;
```

Nếu thư mục gốc dự án của bạn là `/var/www/html/public_html` thì giữ nguyên. Nếu khác, hãy thay lại path cho đúng thực tế.

Tạo sẵn các thư mục còn thiếu trước khi nhận request đầu tiên:

```bash
sudo mkdir -p \
  /var/www/html/public_html/storage/cache/bbcode \
  /var/www/html/public_html/storage/exports \
  /var/www/html/public_html/storage/locks \
  /var/www/html/public_html/storage/logs \
  /var/www/html/public_html/storage/tmp \
  /var/www/html/public_html/assets/uploads/homeworks \
  /var/www/html/public_html/assets/uploads/lessons \
  /var/www/html/public_html/assets/uploads/profile \
  /var/www/html/public_html/assets/uploads/teacher-videos
sudo chown -R www-data:www-data /var/www/html/public_html/storage /var/www/html/public_html/assets/uploads
```

Có thể chạy kiểm tra runtime sau khi upload:

```bash
php /var/www/html/public_html/cron/prepare-runtime.php
```

## 4. Cơ sở dữ liệu

- Import `database/schema.sql`
- Chỉ import `database/seed.sql` nếu bạn muốn có dữ liệu demo
- Nếu DB hiện tại đã có dữ liệu cũ, hãy dùng các file migration cần thiết thay vì import lại toàn bộ

## 5. Bảo mật trước khi go-live

- Thay `APP_SECRET` mặc định
- Tắt tài khoản demo hoặc dữ liệu seed demo
- Kiểm tra lại cấu hình mail sender
- Đảm bảo `storage/logs/` có thể ghi nhưng không public trực tiếp ra ngoài

## 6. Smoke test nhanh sau khi upload

- Trang chủ mở được
- Đăng nhập hoạt động
- Chuyển ngôn ngữ hoạt động
- Tạo hoặc cập nhật hồ sơ hoạt động
- Upload avatar/bài tập/tài liệu hoạt động
- Trang thông báo mở được
- Các trang admin đọc và ghi DB bình thường
