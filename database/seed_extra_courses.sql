USE english_center_db;

INSERT INTO courses (course_name, description, base_price, total_sessions, image_thumbnail)
SELECT 'IELTS Advanced Booster', 'Tang toc band diem IELTS 6.5-7.5 voi reading, writing va speaking nang cao.', 6800000, 32, '/assets/images/student2.jpg'
WHERE NOT EXISTS (SELECT 1 FROM courses WHERE course_name = 'IELTS Advanced Booster');

INSERT INTO courses (course_name, description, base_price, total_sessions, image_thumbnail)
SELECT 'Business Communication Pro', 'Luyen giao tiep cong so, presentation va xu ly email chuyen nghiep.', 6400000, 28, '/assets/images/center.jpg'
WHERE NOT EXISTS (SELECT 1 FROM courses WHERE course_name = 'Business Communication Pro');

INSERT INTO courses (course_name, description, base_price, total_sessions, image_thumbnail)
SELECT 'TOEIC Speed Master', 'Khoa hoc tang toc lam bai TOEIC cho muc tieu 750+.', 4500000, 24, '/assets/images/student.jpg'
WHERE NOT EXISTS (SELECT 1 FROM courses WHERE course_name = 'TOEIC Speed Master');

INSERT INTO courses (course_name, description, base_price, total_sessions, image_thumbnail)
SELECT 'Kids Phonics Adventure', 'Xay nen tang phonics, phat am va tu vung cho tre em.', 3600000, 18, '/assets/images/student3.jpg'
WHERE NOT EXISTS (SELECT 1 FROM courses WHERE course_name = 'Kids Phonics Adventure');

INSERT INTO courses (course_name, description, base_price, total_sessions, image_thumbnail)
SELECT 'Junior Speaking Club', 'Lop noi tu tin danh cho hoc vien tieng Anh cap 1-2.', 3900000, 20, '/assets/images/student_girl.png'
WHERE NOT EXISTS (SELECT 1 FROM courses WHERE course_name = 'Junior Speaking Club');

INSERT INTO courses (course_name, description, base_price, total_sessions, image_thumbnail)
SELECT 'Preschool English Start', 'Lam quen tieng Anh qua bai hat, tro choi va hinh anh sinh dong cho tre mau giao.', 2800000, 16, '/assets/images/center.jpg'
WHERE NOT EXISTS (SELECT 1 FROM courses WHERE course_name = 'Preschool English Start');

INSERT INTO courses (course_name, description, base_price, total_sessions, image_thumbnail)
SELECT 'Primary English Explorer', 'Xay dung tu vung, ngu am va ky nang doc hieu cho hoc sinh tieu hoc.', 3200000, 18, '/assets/images/student.jpg'
WHERE NOT EXISTS (SELECT 1 FROM courses WHERE course_name = 'Primary English Explorer');

INSERT INTO courses (course_name, description, base_price, total_sessions, image_thumbnail)
SELECT 'Grammar Foundation', 'Cung co ngu phap, cau truc co ban va bai tap ung dung theo chu de.', 3000000, 20, '/assets/images/student2.jpg'
WHERE NOT EXISTS (SELECT 1 FROM courses WHERE course_name = 'Grammar Foundation');

INSERT INTO courses (course_name, description, base_price, total_sessions, image_thumbnail)
SELECT 'IELTS Writing Lab', 'Tap trung viet task 1, task 2 va chinh sua bai viet theo tung muc band.', 7200000, 30, '/assets/images/mission.jpg'
WHERE NOT EXISTS (SELECT 1 FROM courses WHERE course_name = 'IELTS Writing Lab');

INSERT INTO courses (course_name, description, base_price, total_sessions, image_thumbnail)
SELECT 'IELTS Speaking Boost', 'Luyen phan xa cau tra loi, phat am va tu vung theo chu de thong dung.', 6900000, 28, '/assets/images/student3.jpg'
WHERE NOT EXISTS (SELECT 1 FROM courses WHERE course_name = 'IELTS Speaking Boost');

INSERT INTO courses (course_name, description, base_price, total_sessions, image_thumbnail)
SELECT 'Teen Communication Plus', 'Phat trien giao tiep tu tin, tranh luan va lam viec nhom cho hoc sinh THCS-THPT.', 4200000, 22, '/assets/images/student_girl.png'
WHERE NOT EXISTS (SELECT 1 FROM courses WHERE course_name = 'Teen Communication Plus');

INSERT INTO courses (course_name, description, base_price, total_sessions, image_thumbnail)
SELECT 'Adult Conversation Class', 'Thuc hanh giao tiep ung dung, hoi dap va tinh huong doi thoai doi song.', 4600000, 24, '/assets/images/center.jpg'
WHERE NOT EXISTS (SELECT 1 FROM courses WHERE course_name = 'Adult Conversation Class');

INSERT INTO courses (course_name, description, base_price, total_sessions, image_thumbnail)
SELECT 'Business Writing Essentials', 'Ren email, bao cao va van ban cong so ro rang, chuyen nghiep.', 5200000, 20, '/assets/images/student2.jpg'
WHERE NOT EXISTS (SELECT 1 FROM courses WHERE course_name = 'Business Writing Essentials');

INSERT INTO courses (course_name, description, base_price, total_sessions, image_thumbnail)
SELECT 'TOEIC Foundation', 'Khoi dong tu vung, ngu phap va chien luoc lam bai TOEIC cap co ban.', 4300000, 20, '/assets/images/student.jpg'
WHERE NOT EXISTS (SELECT 1 FROM courses WHERE course_name = 'TOEIC Foundation');

INSERT INTO courses (course_name, description, base_price, total_sessions, image_thumbnail)
SELECT 'Pronunciation Clinic', 'Chinh phat am, trong am va ngon dieu de noi tu tin hon.', 3500000, 18, '/assets/images/mission.jpg'
WHERE NOT EXISTS (SELECT 1 FROM courses WHERE course_name = 'Pronunciation Clinic');

INSERT INTO courses (course_name, description, base_price, total_sessions, image_thumbnail)
SELECT 'Reading Power Track', 'Tang toc doc hieu, skimming va scanning qua cac dang bai da dang.', 4800000, 22, '/assets/images/student3.jpg'
WHERE NOT EXISTS (SELECT 1 FROM courses WHERE course_name = 'Reading Power Track');

INSERT INTO courses (course_name, description, base_price, total_sessions, image_thumbnail)
SELECT 'Speaking Confidence Workshop', 'Xay dung su tu tin khi noi, nhan vien y va phan hoi nhanh trong lop hoc.', 4100000, 18, '/assets/images/student_girl.png'
WHERE NOT EXISTS (SELECT 1 FROM courses WHERE course_name = 'Speaking Confidence Workshop');

UPDATE courses
SET image_thumbnail = '/assets/images/mission.jpg'
WHERE course_name = 'hehe' AND (image_thumbnail IS NULL OR image_thumbnail = '');

UPDATE courses
SET image_thumbnail = '/assets/images/student2.jpg'
WHERE course_name = 'IELTS Advanced Booster' AND (image_thumbnail IS NULL OR image_thumbnail = '');

UPDATE courses
SET image_thumbnail = '/assets/images/center.jpg'
WHERE course_name = 'Business Communication Pro' AND (image_thumbnail IS NULL OR image_thumbnail = '');

UPDATE courses
SET image_thumbnail = '/assets/images/student.jpg'
WHERE course_name = 'TOEIC Speed Master' AND (image_thumbnail IS NULL OR image_thumbnail = '');

UPDATE courses
SET image_thumbnail = '/assets/images/student3.jpg'
WHERE course_name = 'Kids Phonics Adventure' AND (image_thumbnail IS NULL OR image_thumbnail = '');

UPDATE courses
SET image_thumbnail = '/assets/images/student_girl.png'
WHERE course_name = 'Junior Speaking Club' AND (image_thumbnail IS NULL OR image_thumbnail = '');

INSERT INTO course_roadmaps (course_id, `order`, topic_title, outline_content)
SELECT c.id, 1, 'IELTS Diagnostic & Planning', 'Danh gia dau vao va lap loi hoc rieng cho hoc vien.'
FROM courses c
WHERE c.course_name = 'IELTS Advanced Booster'
  AND NOT EXISTS (
      SELECT 1 FROM course_roadmaps cr
      WHERE cr.course_id = c.id AND cr.topic_title = 'IELTS Diagnostic & Planning'
  );

INSERT INTO course_roadmaps (course_id, `order`, topic_title, outline_content)
SELECT c.id, 2, 'IELTS Writing Task 2 Clinic', 'Phan tich de, lap y va viet bai mau theo form band cao.'
FROM courses c
WHERE c.course_name = 'IELTS Advanced Booster'
  AND NOT EXISTS (
      SELECT 1 FROM course_roadmaps cr
      WHERE cr.course_id = c.id AND cr.topic_title = 'IELTS Writing Task 2 Clinic'
  );

INSERT INTO course_roadmaps (course_id, `order`, topic_title, outline_content)
SELECT c.id, 1, 'Business Meeting Basics', 'Mau cau hop, trao doi y kien va giao viec trong cong ty.'
FROM courses c
WHERE c.course_name = 'Business Communication Pro'
  AND NOT EXISTS (
      SELECT 1 FROM course_roadmaps cr
      WHERE cr.course_id = c.id AND cr.topic_title = 'Business Meeting Basics'
  );

INSERT INTO course_roadmaps (course_id, `order`, topic_title, outline_content)
SELECT c.id, 2, 'Presentation & Q&A', 'Luyen phat bieu va tra loi cau hoi trong phieu trinh bay.'
FROM courses c
WHERE c.course_name = 'Business Communication Pro'
  AND NOT EXISTS (
      SELECT 1 FROM course_roadmaps cr
      WHERE cr.course_id = c.id AND cr.topic_title = 'Presentation & Q&A'
  );

INSERT INTO course_roadmaps (course_id, `order`, topic_title, outline_content)
SELECT c.id, 1, 'Listening Sprint', 'Luyen nghe part 1-4 voi chien luoc nghe chu dong.'
FROM courses c
WHERE c.course_name = 'TOEIC Speed Master'
  AND NOT EXISTS (
      SELECT 1 FROM course_roadmaps cr
      WHERE cr.course_id = c.id AND cr.topic_title = 'Listening Sprint'
  );

INSERT INTO course_roadmaps (course_id, `order`, topic_title, outline_content)
SELECT c.id, 2, 'Reading Time Attack', 'Ren toc do doc, keyword scanning va doan van.'
FROM courses c
WHERE c.course_name = 'TOEIC Speed Master'
  AND NOT EXISTS (
      SELECT 1 FROM course_roadmaps cr
      WHERE cr.course_id = c.id AND cr.topic_title = 'Reading Time Attack'
  );

INSERT INTO course_roadmaps (course_id, `order`, topic_title, outline_content)
SELECT c.id, 1, 'Phonics Foundations', 'Hoc am, nhan dien chu cai va phat am co ban.'
FROM courses c
WHERE c.course_name = 'Kids Phonics Adventure'
  AND NOT EXISTS (
      SELECT 1 FROM course_roadmaps cr
      WHERE cr.course_id = c.id AND cr.topic_title = 'Phonics Foundations'
  );

INSERT INTO course_roadmaps (course_id, `order`, topic_title, outline_content)
SELECT c.id, 2, 'Storytelling with Pictures', 'Roi am, noi cau don gian va ke chuyen bang hinh anh.'
FROM courses c
WHERE c.course_name = 'Kids Phonics Adventure'
  AND NOT EXISTS (
      SELECT 1 FROM course_roadmaps cr
      WHERE cr.course_id = c.id AND cr.topic_title = 'Storytelling with Pictures'
  );

INSERT INTO course_roadmaps (course_id, `order`, topic_title, outline_content)
SELECT c.id, 1, 'Conversation Starters', 'Chao hoi, gioi thieu ban than va phan xa giao tiep.'
FROM courses c
WHERE c.course_name = 'Junior Speaking Club'
  AND NOT EXISTS (
      SELECT 1 FROM course_roadmaps cr
      WHERE cr.course_id = c.id AND cr.topic_title = 'Conversation Starters'
  );

INSERT INTO course_roadmaps (course_id, `order`, topic_title, outline_content)
SELECT c.id, 2, 'Mini Role-play Sessions', 'Dong vai cac tinh huong doi thoai don gian hang ngay.'
FROM courses c
WHERE c.course_name = 'Junior Speaking Club'
  AND NOT EXISTS (
      SELECT 1 FROM course_roadmaps cr
      WHERE cr.course_id = c.id AND cr.topic_title = 'Mini Role-play Sessions'
  );

INSERT INTO promotions (course_id, name, promo_type, discount_value, start_date, end_date)
SELECT c.id, 'Goi 12 tuan', 'DURATION', 5.00, NULL, NULL
FROM courses c
WHERE c.course_name = 'IELTS Advanced Booster'
  AND NOT EXISTS (
      SELECT 1 FROM promotions p
      WHERE p.course_id = c.id AND p.name = 'Goi 12 tuan'
  );

INSERT INTO promotions (course_id, name, promo_type, discount_value, start_date, end_date)
SELECT c.id, 'Goi thuong huu doanh nghiep', 'SOCIAL', 3.00, NULL, NULL
FROM courses c
WHERE c.course_name = 'Business Communication Pro'
  AND NOT EXISTS (
      SELECT 1 FROM promotions p
      WHERE p.course_id = c.id AND p.name = 'Goi thuong huu doanh nghiep'
  );

INSERT INTO promotions (course_id, name, promo_type, discount_value, start_date, end_date)
SELECT c.id, 'Goi chinh phuc 750+', 'EVENT', 0.00, '2026-01-01', '2026-12-31'
FROM courses c
WHERE c.course_name = 'TOEIC Speed Master'
  AND NOT EXISTS (
      SELECT 1 FROM promotions p
      WHERE p.course_id = c.id AND p.name = 'Goi chinh phuc 750+'
  );

INSERT INTO promotions (course_id, name, promo_type, discount_value, start_date, end_date)
SELECT c.id, 'Goi gia dinh', 'GROUP', 5.00, NULL, NULL
FROM courses c
WHERE c.course_name = 'Kids Phonics Adventure'
  AND NOT EXISTS (
      SELECT 1 FROM promotions p
      WHERE p.course_id = c.id AND p.name = 'Goi gia dinh'
  );

INSERT INTO promotions (course_id, name, promo_type, discount_value, start_date, end_date)
SELECT c.id, 'Goi hoc nhom nho', 'GROUP', 4.00, NULL, NULL
FROM courses c
WHERE c.course_name = 'Junior Speaking Club'
  AND NOT EXISTS (
      SELECT 1 FROM promotions p
      WHERE p.course_id = c.id AND p.name = 'Goi hoc nhom nho'
  );

INSERT INTO classes (course_id, class_name, teacher_id, start_date, end_date, status)
SELECT c.id, 'IELTS-ADV-K01', (SELECT id FROM users WHERE username = 'teacher4@ec.local' LIMIT 1), '2026-05-12', '2026-09-12', 'upcoming'
FROM courses c
WHERE c.course_name = 'IELTS Advanced Booster'
  AND NOT EXISTS (SELECT 1 FROM classes cl WHERE cl.class_name = 'IELTS-ADV-K01');

INSERT INTO classes (course_id, class_name, teacher_id, start_date, end_date, status)
SELECT c.id, 'BUS-COM-K01', (SELECT id FROM users WHERE username = 'teacher5@ec.local' LIMIT 1), '2026-05-20', '2026-08-20', 'upcoming'
FROM courses c
WHERE c.course_name = 'Business Communication Pro'
  AND NOT EXISTS (SELECT 1 FROM classes cl WHERE cl.class_name = 'BUS-COM-K01');

INSERT INTO classes (course_id, class_name, teacher_id, start_date, end_date, status)
SELECT c.id, 'TOEIC-SPD-K01', (SELECT id FROM users WHERE username = 'teacher6@ec.local' LIMIT 1), '2026-04-30', '2026-07-30', 'active'
FROM courses c
WHERE c.course_name = 'TOEIC Speed Master'
  AND NOT EXISTS (SELECT 1 FROM classes cl WHERE cl.class_name = 'TOEIC-SPD-K01');

INSERT INTO classes (course_id, class_name, teacher_id, start_date, end_date, status)
SELECT c.id, 'KIDS-PHONICS-K01', (SELECT id FROM users WHERE username = 'teacher6@ec.local' LIMIT 1), '2026-05-08', '2026-08-08', 'active'
FROM courses c
WHERE c.course_name = 'Kids Phonics Adventure'
  AND NOT EXISTS (SELECT 1 FROM classes cl WHERE cl.class_name = 'KIDS-PHONICS-K01');

INSERT INTO classes (course_id, class_name, teacher_id, start_date, end_date, status)
SELECT c.id, 'JUNIOR-SPEAK-K01', (SELECT id FROM users WHERE username = 'teacher7@ec.local' LIMIT 1), '2026-05-15', '2026-08-15', 'upcoming'
FROM courses c
WHERE c.course_name = 'Junior Speaking Club'
  AND NOT EXISTS (SELECT 1 FROM classes cl WHERE cl.class_name = 'JUNIOR-SPEAK-K01');
