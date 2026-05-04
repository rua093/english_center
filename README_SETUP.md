# English Center - Setup nhanh

## 1) Khoi dong Docker

```powershell
docker compose up -d --build
```

## 1.1) Tailwind CDN (mac dinh)

- Du an su dung Tailwind CDN trong layout header (`public_html/pages/partials/tailwind_cdn.php`).
- Khong can `npm install` hoac build CSS local de app hien thi.
- Toan bo class giao dien duoc viet truc tiep bang utility class.

## 2) Import CSDL

```powershell
docker exec -i mysql_db mysql --default-character-set=utf8mb4 -uuser -puser_password english_center_db < database/schema.sql
docker exec -i mysql_db mysql --default-character-set=utf8mb4 -uuser -puser_password english_center_db < database/seed.sql
```

## 2.1) Nang cap DB dang chay (khong reset du lieu)

```powershell
docker exec -i mysql_db mysql --default-character-set=utf8mb4 -uuser -puser_password english_center_db < database/migrate_existing_db.sql
docker exec -i mysql_db mysql --default-character-set=utf8mb4 -uuser -puser_password english_center_db < database/rbac_matrix_seed.sql
```

- Dung buoc nay khi he thong da co du lieu cu va ban chi muon cap nhat RBAC + approval workflow moi.

## 3) Mo ung dung

- Landing page: http://localhost:8080/
- Login: http://localhost:8080/?page=login
- Student dashboard: http://localhost:8080/?page=student-dashboard

## 4) Tai khoan demo

- student@ec.local / 123456
- admin@ec.local / 123456

## 5) Ghi chu

- Mat khau trong seed da duoc hash bcrypt san, dang nhap truc tiep bang thong tin demo.
- Tren production, tiep tuc su dung password hash va dat APP_ENV=production.
