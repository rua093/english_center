# Shared Hosting Checklist

## 1. File config

- Copy `public_html/config/local.php.example` to `public_html/config/local.php`.
- Update:
  - `APP_ENV=production`
  - `APP_BASE_URL`
  - `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`
  - `APP_SECRET`
  - SMTP settings if you need mail

## 2. Public web root

- Point the domain/subdomain document root to `public_html/`.
- Hosting must support Apache `.htaccess` rewrite rules.

## 3. Writable directories

The following directories must be writable by PHP:

- `public_html/storage/cache`
- `public_html/storage/exports`
- `public_html/storage/logs`
- `public_html/storage/tmp`
- `public_html/assets/uploads`
- `public_html/assets/uploads/homeworks`
- `public_html/assets/uploads/lessons`
- `public_html/assets/uploads/profile`
- `public_html/assets/uploads/teacher-videos`

Typical permission on shared hosting:

- Directories: `755`
- If upload/log write fails: temporarily try `775`
- Avoid `777` unless the host requires it and you understand the risk

## 4. Database

- Import `database/schema.sql`
- Import `database/seed.sql` only if you want demo data
- If you already have an older DB, use the needed migration files instead

## 5. Security before go-live

- Replace the default `APP_SECRET`
- Disable demo accounts / demo seed data
- Review mail sender settings
- Check `storage/logs/` is writable but not publicly accessible

## 6. Quick smoke test after upload

- Home page loads
- Login works
- Language switch works
- Create/update profile works
- Upload avatar/homework/material works
- Notifications page opens
- Admin pages can read/write DB
