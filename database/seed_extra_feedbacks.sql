USE english_center_db;

INSERT IGNORE INTO users (username, password, full_name, role_id, phone, email, status) VALUES
('student5@ec.local', '$2y$10$5luD5xfAGFeqHwRdPWq1ZezZW43r.qwE2wFcaXCanvh1O0DR8XYum', 'Tran My Student', (SELECT id FROM roles WHERE role_name = 'student' LIMIT 1), '0900000022', 'student5@ec.local', 'active'),
('student6@ec.local', '$2y$10$5luD5xfAGFeqHwRdPWq1ZezZW43r.qwE2wFcaXCanvh1O0DR8XYum', 'Le Gia Student', (SELECT id FROM roles WHERE role_name = 'student' LIMIT 1), '0900000023', 'student6@ec.local', 'active'),
('student7@ec.local', '$2y$10$5luD5xfAGFeqHwRdPWq1ZezZW43r.qwE2wFcaXCanvh1O0DR8XYum', 'Pham An Student', (SELECT id FROM roles WHERE role_name = 'student' LIMIT 1), '0900000024', 'student7@ec.local', 'active'),
('student8@ec.local', '$2y$10$5luD5xfAGFeqHwRdPWq1ZezZW43r.qwE2wFcaXCanvh1O0DR8XYum', 'Vu Ngoc Student', (SELECT id FROM roles WHERE role_name = 'student' LIMIT 1), '0900000025', 'student8@ec.local', 'active'),
('student9@ec.local', '$2y$10$5luD5xfAGFeqHwRdPWq1ZezZW43r.qwE2wFcaXCanvh1O0DR8XYum', 'Do Khanh Student', (SELECT id FROM roles WHERE role_name = 'student' LIMIT 1), '0900000026', 'student9@ec.local', 'active');

INSERT INTO feedbacks (sender_id, class_id, teacher_id, rating, content, status)
SELECT
    (SELECT id FROM users WHERE username = 'student5@ec.local' LIMIT 1),
    (SELECT id FROM classes WHERE class_name = 'IELTS-K20-Toi-2-4' LIMIT 1),
    (SELECT id FROM users WHERE username = 'teacher@ec.local' LIMIT 1),
    5,
    'Moi buoi hoc deu co muc tieu ro rang, trung tam theo sat hoc vien rat ky.',
    'reviewed'
WHERE NOT EXISTS (
    SELECT 1 FROM feedbacks f
    WHERE f.sender_id = (SELECT id FROM users WHERE username = 'student5@ec.local' LIMIT 1)
      AND f.class_id = (SELECT id FROM classes WHERE class_name = 'IELTS-K20-Toi-2-4' LIMIT 1)
      AND f.teacher_id = (SELECT id FROM users WHERE username = 'teacher@ec.local' LIMIT 1)
      AND f.content = 'Moi buoi hoc deu co muc tieu ro rang, trung tam theo sat hoc vien rat ky.'
);

INSERT INTO feedbacks (sender_id, class_id, teacher_id, rating, content, status)
SELECT
    (SELECT id FROM users WHERE username = 'student6@ec.local' LIMIT 1),
    (SELECT id FROM classes WHERE class_name = 'IELTS-K20-Toi-2-4' LIMIT 1),
    (SELECT id FROM users WHERE username = 'teacher@ec.local' LIMIT 1),
    4,
    'Con toi tu tin hon sau 2 thang hoc, giao vien giup chinh phat am rat tot.',
    'reviewed'
WHERE NOT EXISTS (
    SELECT 1 FROM feedbacks f
    WHERE f.sender_id = (SELECT id FROM users WHERE username = 'student6@ec.local' LIMIT 1)
      AND f.class_id = (SELECT id FROM classes WHERE class_name = 'IELTS-K20-Toi-2-4' LIMIT 1)
      AND f.teacher_id = (SELECT id FROM users WHERE username = 'teacher@ec.local' LIMIT 1)
      AND f.content = 'Con toi tu tin hon sau 2 thang hoc, giao vien giup chinh phat am rat tot.'
);

INSERT INTO feedbacks (sender_id, class_id, teacher_id, rating, content, status)
SELECT
    (SELECT id FROM users WHERE username = 'student7@ec.local' LIMIT 1),
    (SELECT id FROM classes WHERE class_name = 'IELTS-K20-Toi-2-4' LIMIT 1),
    (SELECT id FROM users WHERE username = 'teacher@ec.local' LIMIT 1),
    5,
    'Co tai lieu on tap sau moi buoi hoc va phan hoi bai tap rat nhanh.',
    'reviewed'
WHERE NOT EXISTS (
    SELECT 1 FROM feedbacks f
    WHERE f.sender_id = (SELECT id FROM users WHERE username = 'student7@ec.local' LIMIT 1)
      AND f.class_id = (SELECT id FROM classes WHERE class_name = 'IELTS-K20-Toi-2-4' LIMIT 1)
      AND f.teacher_id = (SELECT id FROM users WHERE username = 'teacher@ec.local' LIMIT 1)
      AND f.content = 'Co tai lieu on tap sau moi buoi hoc va phan hoi bai tap rat nhanh.'
);

INSERT INTO feedbacks (sender_id, class_id, teacher_id, rating, content, status)
SELECT
    (SELECT id FROM users WHERE username = 'student8@ec.local' LIMIT 1),
    (SELECT id FROM classes WHERE class_name = 'IELTS-K20-Toi-2-4' LIMIT 1),
    (SELECT id FROM users WHERE username = 'teacher@ec.local' LIMIT 1),
    4,
    'Khong khi lop hoc than thien, phu huynh cung duoc cap nhat tien bo thuong xuyen.',
    'reviewed'
WHERE NOT EXISTS (
    SELECT 1 FROM feedbacks f
    WHERE f.sender_id = (SELECT id FROM users WHERE username = 'student8@ec.local' LIMIT 1)
      AND f.class_id = (SELECT id FROM classes WHERE class_name = 'IELTS-K20-Toi-2-4' LIMIT 1)
      AND f.teacher_id = (SELECT id FROM users WHERE username = 'teacher@ec.local' LIMIT 1)
      AND f.content = 'Khong khi lop hoc than thien, phu huynh cung duoc cap nhat tien bo thuong xuyen.'
);

INSERT INTO feedbacks (sender_id, class_id, teacher_id, rating, content, status)
SELECT
    (SELECT id FROM users WHERE username = 'student9@ec.local' LIMIT 1),
    (SELECT id FROM classes WHERE class_name = 'IELTS-K20-Toi-2-4' LIMIT 1),
    (SELECT id FROM users WHERE username = 'teacher@ec.local' LIMIT 1),
    5,
    'Trung tam co lo trinh ro rang, hoc den dau thay tien bo den do.',
    'reviewed'
WHERE NOT EXISTS (
    SELECT 1 FROM feedbacks f
    WHERE f.sender_id = (SELECT id FROM users WHERE username = 'student9@ec.local' LIMIT 1)
      AND f.class_id = (SELECT id FROM classes WHERE class_name = 'IELTS-K20-Toi-2-4' LIMIT 1)
      AND f.teacher_id = (SELECT id FROM users WHERE username = 'teacher@ec.local' LIMIT 1)
      AND f.content = 'Trung tam co lo trinh ro rang, hoc den dau thay tien bo den do.'
);

INSERT INTO feedbacks (sender_id, class_id, teacher_id, rating, content, status)
SELECT
    (SELECT id FROM users WHERE username = 'student@ec.local' LIMIT 1),
    (SELECT id FROM classes WHERE class_name = 'IELTS-K20-Toi-2-4' LIMIT 1),
    (SELECT id FROM users WHERE username = 'teacher@ec.local' LIMIT 1),
    5,
    'Buoi hoc sinh dong, giao vien luon giai thich ky nen minh danh gia rat cao.',
    'reviewed'
WHERE NOT EXISTS (
    SELECT 1 FROM feedbacks f
    WHERE f.sender_id = (SELECT id FROM users WHERE username = 'student@ec.local' LIMIT 1)
      AND f.class_id = (SELECT id FROM classes WHERE class_name = 'IELTS-K20-Toi-2-4' LIMIT 1)
      AND f.teacher_id = (SELECT id FROM users WHERE username = 'teacher@ec.local' LIMIT 1)
      AND f.content = 'Buoi hoc sinh dong, giao vien luon giai thich ky nen minh danh gia rat cao.'
);