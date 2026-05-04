START TRANSACTION;

INSERT INTO rooms (room_name)
SELECT demo.room_name
FROM (
    SELECT 'Phong demo AJAX 01' AS room_name
    UNION ALL SELECT 'Phong demo AJAX 02'
    UNION ALL SELECT 'Phong demo AJAX 03'
    UNION ALL SELECT 'Phong demo AJAX 04'
    UNION ALL SELECT 'Phong demo AJAX 05'
    UNION ALL SELECT 'Phong demo AJAX 06'
    UNION ALL SELECT 'Phong demo AJAX 07'
    UNION ALL SELECT 'Phong demo AJAX 08'
    UNION ALL SELECT 'Phong demo AJAX 09'
    UNION ALL SELECT 'Phong demo AJAX 10'
    UNION ALL SELECT 'Phong demo AJAX 11'
    UNION ALL SELECT 'Phong demo AJAX 12'
) AS demo
LEFT JOIN rooms r ON r.room_name = demo.room_name AND r.deleted_at IS NULL
WHERE r.id IS NULL;

INSERT INTO extracurricular_activities (title, description, content, location, fee, start_date, status)
SELECT demo.title, demo.description, demo.content, demo.location, demo.fee, demo.start_date, demo.status
FROM (
    SELECT 'Hoat dong demo AJAX 01' AS title, 'Workshop giao tiep co ban' AS description, 'Noi dung demo cho trang activities manage.' AS content, 'Co so A - Tang 2' AS location, 120000.00 AS fee, '2026-05-10' AS start_date, 'upcoming' AS status
    UNION ALL
    SELECT 'Hoat dong demo AJAX 02', 'Ngay hoi doc sach', 'Hoat dong ngoai khoa giup hoc vien giao luu va chia se sach.', 'Thu vien trung tam', 0.00, '2026-05-12', 'upcoming'
    UNION ALL
    SELECT 'Hoat dong demo AJAX 03', 'CLB phat am', 'Buoi thuc hanh phat am theo nhom nho.', 'Phong demo AJAX 01', 80000.00, '2026-05-03', 'ongoing'
    UNION ALL
    SELECT 'Hoat dong demo AJAX 04', 'English Game Night', 'Tro choi tu vung va phan xa tieng Anh.', 'Phong demo AJAX 02', 150000.00, '2026-04-28', 'finished'
    UNION ALL
    SELECT 'Hoat dong demo AJAX 05', 'Kiem tra nang luc tu nguyen', 'Danh gia tong quan de xep lop va tu van lo trinh.', 'Co so B - Phong 4', 50000.00, '2026-05-15', 'upcoming'
    UNION ALL
    SELECT 'Hoat dong demo AJAX 06', 'Tham quan ngoai troi', 'Hoat dong trainghiem ket hop nhiem vu ngôn ngu demo.', 'Cong vien trung tam', 200000.00, '2026-05-01', 'ongoing'
    UNION ALL
    SELECT 'Hoat dong demo AJAX 07', 'Speaking Sprint', 'Chuoi bai tap noi ngan trong 60 phut de tang phan xa.', 'Co so A - Phong 1', 90000.00, '2026-05-18', 'upcoming'
    UNION ALL
    SELECT 'Hoat dong demo AJAX 08', 'Movie Discussion', 'Xem doan phim ngan va thao luan bang tieng Anh.', 'Co so A - Phong 3', 110000.00, '2026-05-08', 'ongoing'
    UNION ALL
    SELECT 'Hoat dong demo AJAX 09', 'Presentation Day', 'Hoc vien thuyet trinh theo nhom va nhan phan hoi.', 'Hoi truong trung tam', 130000.00, '2026-04-20', 'finished'
    UNION ALL
    SELECT 'Hoat dong demo AJAX 10', 'Mini Debate', 'Hoat dong tranh bien nhe cho lop giao tiep.', 'Co so B - Tang 1', 70000.00, '2026-05-20', 'upcoming'
    UNION ALL
    SELECT 'Hoat dong demo AJAX 11', 'Pronunciation Clinic', 'Chua loi phat am theo nhom 5-6 hoc vien.', 'Phong demo AJAX 03', 60000.00, '2026-05-06', 'ongoing'
    UNION ALL
    SELECT 'Hoat dong demo AJAX 12', 'Reading Circle', 'Doc va tom tat bai ngan theo cap.', 'Thu vien trung tam', 40000.00, '2026-04-18', 'finished'
) AS demo
LEFT JOIN extracurricular_activities a ON a.title = demo.title AND a.deleted_at IS NULL
WHERE a.id IS NULL;

INSERT INTO notifications (user_id, title, message, is_read)
SELECT demo.user_id, demo.title, demo.message, demo.is_read
FROM (
    SELECT 131 AS user_id, 'Thong bao demo AJAX 01' AS title, 'Hoc vien can kiem tra lai lich hoc tuan nay.' AS message, 0 AS is_read
    UNION ALL SELECT 132, 'Thong bao demo AJAX 02', 'Ban co mot nhan xet moi tu giao vien phu trach lop.', 1
    UNION ALL SELECT 133, 'Thong bao demo AJAX 03', 'Vui long cap nhat thong tin lien he truoc ngay 10/05.', 0
    UNION ALL SELECT 134, 'Thong bao demo AJAX 04', 'Trung tam da mo them lop moi cho khung gio toi.', 1
    UNION ALL SELECT 135, 'Thong bao demo AJAX 05', 'Ban co mot hoa don hoc phi can doi chieu.', 0
    UNION ALL SELECT 136, 'Thong bao demo AJAX 06', 'Hoat dong ngoai khoa moi da mo dang ky.', 1
    UNION ALL SELECT 137, 'Thong bao demo AJAX 07', 'Thong bao cap nhat noi quy phong hoc.', 0
    UNION ALL SELECT 138, 'Thong bao demo AJAX 08', 'Hay danh gia trai nghiem hoc tap cua ban.', 1
    UNION ALL SELECT 139, 'Thong bao demo AJAX 09', 'Da co ket qua bai tap moi tren he thong.', 0
    UNION ALL SELECT 140, 'Thong bao demo AJAX 10', 'Nhac nho den som 10 phut truoc gio hoc.', 0
) AS demo
LEFT JOIN notifications n ON n.user_id = demo.user_id AND n.title = demo.title
WHERE n.id IS NULL;

INSERT INTO feedbacks (sender_id, rating, content, is_public_web)
SELECT demo.sender_id, demo.rating, demo.content, demo.is_public_web
FROM (
    SELECT 131 AS sender_id, 5 AS rating, 'Feedback demo AJAX 01: hoc vien danh gia rat tot ve giao vien va lop hoc.' AS content, 1 AS is_public_web
    UNION ALL SELECT 132, 4, 'Feedback demo AJAX 02: can bo sung them bai luyen nghe nhung nhin chung rat on.', 0
    UNION ALL SELECT 133, 3, 'Feedback demo AJAX 03: co mot vai buoi hoc thay doi lich nen muon duoc bao som hon.', 0
    UNION ALL SELECT 134, 5, 'Feedback demo AJAX 04: trung tam ho tro nhiet tinh va moi truong hoc than thien.', 1
    UNION ALL SELECT 135, 2, 'Feedback demo AJAX 05: phong hoc hoi dong vao buoi chieu cuoi tuan.', 0
    UNION ALL SELECT 136, 4, 'Feedback demo AJAX 06: thich cac hoat dong ngoai khoa va bai tap thuc hanh.', 1
    UNION ALL SELECT 137, 5, 'Feedback demo AJAX 07: lo trinh hoc ro rang va theo doi tien bo tot.', 1
    UNION ALL SELECT 138, 3, 'Feedback demo AJAX 08: can them nhac nho deadline bai tap tren he thong.', 0
    UNION ALL SELECT 139, 4, 'Feedback demo AJAX 09: hoc phi minh bach va giao vien phan hoi nhanh.', 1
    UNION ALL SELECT 140, 5, 'Feedback demo AJAX 10: hai long voi chat luong giang day hien tai.', 1
) AS demo
LEFT JOIN feedbacks f ON f.sender_id = demo.sender_id AND f.content = demo.content
WHERE f.id IS NULL;

INSERT INTO activity_registrations (activity_id, user_id, payment_status, amount_paid, payment_date, registration_date)
SELECT a.id, demo.user_id, demo.payment_status, demo.amount_paid, demo.payment_date, demo.registration_date
FROM (
    SELECT 'Hoat dong demo AJAX 01' AS activity_title, 131 AS user_id, 'paid' AS payment_status, 120000.00 AS amount_paid, '2026-05-02 09:15:00' AS payment_date, '2026-05-02 09:15:00' AS registration_date
    UNION ALL SELECT 'Hoat dong demo AJAX 01', 132, 'unpaid', 0.00, NULL, '2026-05-02 09:30:00'
    UNION ALL SELECT 'Hoat dong demo AJAX 01', 133, 'paid', 120000.00, '2026-05-03 14:00:00', '2026-05-03 14:00:00'
    UNION ALL SELECT 'Hoat dong demo AJAX 03', 134, 'paid', 80000.00, '2026-05-01 10:00:00', '2026-05-01 10:00:00'
    UNION ALL SELECT 'Hoat dong demo AJAX 03', 135, 'unpaid', 20000.00, '2026-05-01 11:30:00', '2026-05-01 11:30:00'
    UNION ALL SELECT 'Hoat dong demo AJAX 04', 136, 'paid', 150000.00, '2026-04-25 16:00:00', '2026-04-25 16:00:00'
    UNION ALL SELECT 'Hoat dong demo AJAX 05', 137, 'unpaid', 0.00, NULL, '2026-05-04 08:20:00'
    UNION ALL SELECT 'Hoat dong demo AJAX 06', 138, 'paid', 200000.00, '2026-05-01 07:45:00', '2026-05-01 07:45:00'
    UNION ALL SELECT 'Hoat dong demo AJAX 06', 139, 'unpaid', 50000.00, '2026-05-02 18:10:00', '2026-05-02 18:10:00'
    UNION ALL SELECT 'Hoat dong demo AJAX 06', 140, 'unpaid', 0.00, NULL, '2026-05-03 12:00:00'
    UNION ALL SELECT 'Hoat dong demo AJAX 07', 141, 'paid', 90000.00, '2026-05-04 09:00:00', '2026-05-04 09:00:00'
    UNION ALL SELECT 'Hoat dong demo AJAX 08', 142, 'unpaid', 30000.00, '2026-05-04 10:15:00', '2026-05-04 10:15:00'
    UNION ALL SELECT 'Hoat dong demo AJAX 09', 143, 'paid', 130000.00, '2026-04-18 15:00:00', '2026-04-18 15:00:00'
    UNION ALL SELECT 'Hoat dong demo AJAX 10', 144, 'unpaid', 0.00, NULL, '2026-05-04 11:20:00'
    UNION ALL SELECT 'Hoat dong demo AJAX 11', 145, 'paid', 60000.00, '2026-05-04 12:10:00', '2026-05-04 12:10:00'
    UNION ALL SELECT 'Hoat dong demo AJAX 12', 146, 'unpaid', 0.00, NULL, '2026-04-15 08:05:00'
) AS demo
INNER JOIN extracurricular_activities a ON a.title = demo.activity_title AND a.deleted_at IS NULL
LEFT JOIN activity_registrations r ON r.activity_id = a.id AND r.user_id = demo.user_id
WHERE r.id IS NULL;

INSERT INTO payment_transactions (tuition_fee_id, payment_method, amount, transaction_status, created_at, updated_at)
SELECT demo.tuition_fee_id, demo.payment_method, demo.amount, demo.transaction_status, demo.created_at, demo.created_at
FROM (
    SELECT 334 AS tuition_fee_id, 'bank_transfer' AS payment_method, 300000.00 AS amount, 'pending' AS transaction_status, '2026-05-04 09:10:00' AS created_at
    UNION ALL SELECT 333, 'ewallet', 250000.00, 'failed', '2026-05-04 09:25:00'
    UNION ALL SELECT 331, 'card', 500000.00, 'pending', '2026-05-04 10:05:00'
    UNION ALL SELECT 330, 'other', 120000.00, 'failed', '2026-05-04 10:40:00'
    UNION ALL SELECT 328, 'cash', 450000.00, 'pending', '2026-05-04 11:15:00'
    UNION ALL SELECT 327, 'ewallet', 180000.00, 'failed', '2026-05-04 13:20:00'
    UNION ALL SELECT 326, 'bank_transfer', 320000.00, 'pending', '2026-05-04 14:00:00'
    UNION ALL SELECT 324, 'card', 210000.00, 'failed', '2026-05-04 14:35:00'
    UNION ALL SELECT 323, 'cash', 150000.00, 'pending', '2026-05-04 15:05:00'
    UNION ALL SELECT 321, 'other', 90000.00, 'failed', '2026-05-04 15:30:00'
) AS demo
LEFT JOIN payment_transactions pt
    ON pt.tuition_fee_id = demo.tuition_fee_id
   AND pt.payment_method = demo.payment_method
   AND pt.amount = demo.amount
   AND pt.transaction_status = demo.transaction_status
   AND pt.created_at = demo.created_at
WHERE pt.id IS NULL;

COMMIT;
