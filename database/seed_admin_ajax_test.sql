USE english_center_db;

INSERT IGNORE INTO roles (role_name, description) VALUES
('admin', 'Quan tri he thong'),
('staff', 'Giao vu va tu van'),
('teacher', 'Giao vien'),
('student', 'Hoc vien');

INSERT IGNORE INTO users (username, password, full_name, role_id, phone, email, status) VALUES
('admin@ec.local', '$2y$10$5luD5xfAGFeqHwRdPWq1ZezZW43r.qwE2wFcaXCanvh1O0DR8XYum', 'System Admin', (SELECT id FROM roles WHERE role_name = 'admin' LIMIT 1), '0900000001', 'admin@ec.local', 'active'),
('staff@ec.local', '$2y$10$5luD5xfAGFeqHwRdPWq1ZezZW43r.qwE2wFcaXCanvh1O0DR8XYum', 'Academic Staff', (SELECT id FROM roles WHERE role_name = 'staff' LIMIT 1), '0900000002', 'staff@ec.local', 'active'),
('teacher@ec.local', '$2y$10$5luD5xfAGFeqHwRdPWq1ZezZW43r.qwE2wFcaXCanvh1O0DR8XYum', 'Teacher Demo', (SELECT id FROM roles WHERE role_name = 'teacher' LIMIT 1), '0900000003', 'teacher@ec.local', 'active'),
('student@ec.local', '$2y$10$5luD5xfAGFeqHwRdPWq1ZezZW43r.qwE2wFcaXCanvh1O0DR8XYum', 'Nguyen Van Student', (SELECT id FROM roles WHERE role_name = 'student' LIMIT 1), '0900000004', 'student@ec.local', 'active');

SET @admin_id = (SELECT id FROM users WHERE username = 'admin@ec.local' LIMIT 1);
SET @staff_id = (SELECT id FROM users WHERE username = 'staff@ec.local' LIMIT 1);
SET @teacher_id = (SELECT id FROM users WHERE username = 'teacher@ec.local' LIMIT 1);
SET @student_id = (SELECT id FROM users WHERE username = 'student@ec.local' LIMIT 1);

DROP TEMPORARY TABLE IF EXISTS tmp_nums;
CREATE TEMPORARY TABLE tmp_nums (n INT PRIMARY KEY);
INSERT INTO tmp_nums (n) VALUES
(1),(2),(3),(4),(5),(6),(7),(8),(9),(10),(11),(12),(13),(14),(15);
INSERT INTO rooms (room_name)
SELECT CONCAT('Phong Test ', n)
FROM tmp_nums;

INSERT INTO courses (course_name, description, base_price, total_sessions, image_thumbnail)
SELECT CONCAT('Ajax Test Course ', n),
       CONCAT('Mo ta khoa hoc so ', n),
       3500000 + (n * 100000),
       20 + n,
       NULL
FROM tmp_nums;

INSERT INTO classes (course_id, class_name, teacher_id, start_date, end_date, status)
SELECT c.id,
       CONCAT('AJX-CLASS-', LPAD(n, 2, '0')),
       @teacher_id,
       DATE_ADD('2026-04-01', INTERVAL n DAY),
       DATE_ADD('2026-07-01', INTERVAL n DAY),
       CASE
           WHEN (n % 4) = 0 THEN 'cancelled'
           WHEN (n % 4) = 1 THEN 'upcoming'
           WHEN (n % 4) = 2 THEN 'active'
           ELSE 'graduated'
       END
FROM tmp_nums
JOIN courses c ON c.course_name = CONCAT('Ajax Test Course ', n);

INSERT INTO course_roadmaps (course_id, `order`, topic_title, outline_content)
SELECT c.id,
       n,
       CONCAT('Topic ', n),
       CONCAT('Outline demo ', n)
FROM tmp_nums
JOIN courses c ON c.course_name = CONCAT('Ajax Test Course ', n);

INSERT INTO schedules (class_id, room_id, teacher_id, study_date, start_time, end_time)
SELECT cl.id,
       r.id,
       @teacher_id,
       DATE_ADD('2026-05-01', INTERVAL n DAY),
       '18:30:00',
       '20:00:00'
FROM tmp_nums
JOIN classes cl ON cl.class_name = CONCAT('AJX-CLASS-', LPAD(n, 2, '0'))
JOIN rooms r ON r.room_name = CONCAT('Phong Test ', n);

INSERT INTO assignments (schedule_id, title, description, deadline, file_url)
SELECT s.id,
       CONCAT('Assignment ', n),
       CONCAT('Mo ta bai tap ', n),
       DATE_ADD('2026-06-01 23:59:00', INTERVAL n DAY),
       CONCAT('/assets/uploads/assignment-', n, '.pdf')
FROM tmp_nums
JOIN schedules s
  ON s.study_date = DATE_ADD('2026-05-01', INTERVAL n DAY)
 AND s.start_time = '18:30:00';

INSERT INTO materials (title, description, file_path)
SELECT CONCAT('Tai lieu ', n),
       CONCAT('Mo ta tai lieu ', n),
       CONCAT('/assets/uploads/material-', n, '.pdf')
FROM tmp_nums;

INSERT INTO promotions (course_id, name, promo_type, discount_value, start_date, end_date)
SELECT CASE WHEN (n % 3) = 0 THEN NULL ELSE c.id END,
       CONCAT('Uu dai ', n),
       CASE
           WHEN (n % 4) = 0 THEN 'EVENT'
           WHEN (n % 4) = 1 THEN 'DURATION'
           WHEN (n % 4) = 2 THEN 'SOCIAL'
           ELSE 'GROUP'
       END,
       5 + (n % 10),
       CASE WHEN (n % 2) = 0 THEN DATE_ADD('2026-05-01', INTERVAL n DAY) ELSE NULL END,
       CASE WHEN (n % 2) = 0 THEN DATE_ADD('2026-06-01', INTERVAL n DAY) ELSE NULL END
FROM tmp_nums
JOIN courses c ON c.course_name = CONCAT('Ajax Test Course ', n);

INSERT INTO extracurricular_activities (title, description, content, location, image_thumbnail, fee, start_date, status)
SELECT CONCAT('Activity ', n),
       CONCAT('Mo ta hoat dong ', n),
       CONCAT('<p>Noi dung hoat dong ', n, '</p>'),
       CONCAT('Co so ', n),
       CONCAT('/assets/uploads/activity-', n, '.jpg'),
       100000 + (n * 5000),
       DATE_ADD('2026-05-10', INTERVAL n DAY),
       CASE
           WHEN (n % 3) = 0 THEN 'upcoming'
           WHEN (n % 3) = 1 THEN 'ongoing'
           ELSE 'finished'
       END
FROM tmp_nums;

INSERT INTO student_portfolios (student_id, type, media_url, description, is_public_web)
SELECT @student_id,
       CASE
           WHEN (n % 3) = 0 THEN 'progress_video'
           WHEN (n % 3) = 1 THEN 'activity_photo'
           ELSE 'feedback'
       END,
       CONCAT('https://example.com/media-', n, '.mp4'),
       CONCAT('Mo ta portfolio ', n),
       CASE WHEN (n % 2) = 0 THEN 1 ELSE 0 END
FROM tmp_nums;

INSERT INTO feedbacks (sender_id, rating, content, is_public_web)
SELECT @student_id,
       (n % 5) + 1,
       CONCAT('Nhan xet so ', n),
       CASE WHEN (n % 2) = 0 THEN 1 ELSE 0 END
FROM tmp_nums;

INSERT INTO notifications (user_id, title, message, is_read)
SELECT CASE
           WHEN (n % 3) = 0 THEN @teacher_id
           WHEN (n % 3) = 1 THEN @student_id
           ELSE @staff_id
       END,
       CONCAT('Thong bao ', n),
       CONCAT('Noi dung thong bao ', n),
       CASE WHEN (n % 2) = 0 THEN 1 ELSE 0 END
FROM tmp_nums;

INSERT INTO approvals (requester_id, approver_id, type, content, status)
SELECT CASE WHEN (n % 2) = 0 THEN @staff_id ELSE @teacher_id END,
       @admin_id,
       CASE
           WHEN (n % 5) = 0 THEN 'tuition_discount'
           WHEN (n % 5) = 1 THEN 'tuition_delete'
           WHEN (n % 5) = 2 THEN 'finance_adjust'
           WHEN (n % 5) = 3 THEN 'teacher_leave'
           ELSE 'schedule_change'
       END,
       CONCAT('Noi dung phieu ', n),
       CASE
           WHEN (n % 3) = 0 THEN 'pending'
           WHEN (n % 3) = 1 THEN 'approved'
           ELSE 'rejected'
       END
FROM tmp_nums;

INSERT INTO tuition_fees (student_id, class_id, package_id, base_amount, discount_type, discount_amount, total_amount, amount_paid, payment_plan, status)
SELECT @student_id,
       cl.id,
       NULL,
       3000000 + (n * 10000),
       CASE WHEN (n % 2) = 0 THEN 'DURATION' ELSE NULL END,
       CASE WHEN (n % 2) = 0 THEN 50000 ELSE 0 END,
       (3000000 + (n * 10000)) - CASE WHEN (n % 2) = 0 THEN 50000 ELSE 0 END,
       CASE WHEN (n % 2) = 0 THEN 1500000 ELSE 0 END,
       CASE WHEN (n % 2) = 0 THEN 'monthly' ELSE 'full' END,
       CASE WHEN (n % 2) = 0 THEN 'debt' ELSE 'paid' END
FROM tmp_nums
JOIN classes cl ON cl.class_name = CONCAT('AJX-CLASS-', LPAD(n, 2, '0'));

SET @rownum := 0;
INSERT INTO payment_transactions (tuition_fee_id, payment_method, amount, transaction_status)
SELECT tf.id,
       CASE
           WHEN (tf.rn % 3) = 0 THEN 'bank_transfer'
           WHEN (tf.rn % 3) = 1 THEN 'cash'
           ELSE 'card'
       END,
       CASE
           WHEN (tf.rn % 2) = 0 THEN tf.amount_paid
           ELSE tf.total_amount
       END,
       CASE
           WHEN (tf.rn % 3) = 0 THEN 'pending'
           WHEN (tf.rn % 3) = 1 THEN 'success'
           ELSE 'failed'
       END
FROM (
    SELECT t.id, t.amount_paid, t.total_amount, (@rownum := @rownum + 1) AS rn
    FROM tuition_fees t
    WHERE t.base_amount BETWEEN 3000000 AND 3150000
    ORDER BY t.id ASC
    LIMIT 15
) tf;

DROP TEMPORARY TABLE IF EXISTS tmp_nums;
