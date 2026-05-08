USE english_center_db;

INSERT IGNORE INTO users (username, password, full_name, role_id, phone, email, status) VALUES
('teacher4@ec.local', '$2y$10$5luD5xfAGFeqHwRdPWq1ZezZW43r.qwE2wFcaXCanvh1O0DR8XYum', 'Nguyen Phuong Teacher', (SELECT id FROM roles WHERE role_name = 'teacher' LIMIT 1), '0900000017', 'teacher4@ec.local', 'active'),
('teacher5@ec.local', '$2y$10$5luD5xfAGFeqHwRdPWq1ZezZW43r.qwE2wFcaXCanvh1O0DR8XYum', 'Hoang My Teacher', (SELECT id FROM roles WHERE role_name = 'teacher' LIMIT 1), '0900000018', 'teacher5@ec.local', 'active'),
('teacher6@ec.local', '$2y$10$5luD5xfAGFeqHwRdPWq1ZezZW43r.qwE2wFcaXCanvh1O0DR8XYum', 'Tran Quynh Teacher', (SELECT id FROM roles WHERE role_name = 'teacher' LIMIT 1), '0900000019', 'teacher6@ec.local', 'active'),
('teacher7@ec.local', '$2y$10$5luD5xfAGFeqHwRdPWq1ZezZW43r.qwE2wFcaXCanvh1O0DR8XYum', 'Le Gia Teacher', (SELECT id FROM roles WHERE role_name = 'teacher' LIMIT 1), '0900000020', 'teacher7@ec.local', 'active'),
('teacher8@ec.local', '$2y$10$5luD5xfAGFeqHwRdPWq1ZezZW43r.qwE2wFcaXCanvh1O0DR8XYum', 'Vo Khanh Teacher', (SELECT id FROM roles WHERE role_name = 'teacher' LIMIT 1), '0900000021', 'teacher8@ec.local', 'active');

INSERT INTO teacher_profiles (user_id, teacher_code, degree, experience_years, bio, intro_video_url)
SELECT u.id, 'GV10004', 'Master of Applied Linguistics', 8, 'Teacher chuyen IELTS Writing, Speaking va phat trien tu duy hoc thuat cho hoc vien cap trung cap.', 'https://example.com/intro-teacher-4.mp4'
FROM users u
WHERE u.username = 'teacher4@ec.local'
  AND NOT EXISTS (SELECT 1 FROM teacher_profiles tp WHERE tp.user_id = u.id);

INSERT INTO teacher_profiles (user_id, teacher_code, degree, experience_years, bio, intro_video_url)
SELECT u.id, 'GV10005', 'CELTA Certificate', 7, 'Teacher tap trung vao giao tiep, pronunciation va phat am chuan ban dia.', 'https://example.com/intro-teacher-5.mp4'
FROM users u
WHERE u.username = 'teacher5@ec.local'
  AND NOT EXISTS (SELECT 1 FROM teacher_profiles tp WHERE tp.user_id = u.id);

INSERT INTO teacher_profiles (user_id, teacher_code, degree, experience_years, bio, intro_video_url)
SELECT u.id, 'GV10006', 'Bachelor of English Education', 5, 'Teacher phu trach Kids Speaking, phonics va lop nen tang cho tre em.', 'https://example.com/intro-teacher-6.mp4'
FROM users u
WHERE u.username = 'teacher6@ec.local'
  AND NOT EXISTS (SELECT 1 FROM teacher_profiles tp WHERE tp.user_id = u.id);

INSERT INTO teacher_profiles (user_id, teacher_code, degree, experience_years, bio, intro_video_url)
SELECT u.id, 'GV10007', 'Master of Education', 10, 'Teacher phu trach IELTS Reading, grammar nang cao va coaching luyen thi.', 'https://example.com/intro-teacher-7.mp4'
FROM users u
WHERE u.username = 'teacher7@ec.local'
  AND NOT EXISTS (SELECT 1 FROM teacher_profiles tp WHERE tp.user_id = u.id);

INSERT INTO teacher_profiles (user_id, teacher_code, degree, experience_years, bio, intro_video_url)
SELECT u.id, 'GV10008', 'Bachelor of Linguistics', 6, 'Teacher day giao tiep cho nguoi moi bat dau va lop speaking khoa nen tang.', 'https://example.com/intro-teacher-8.mp4'
FROM users u
WHERE u.username = 'teacher8@ec.local'
  AND NOT EXISTS (SELECT 1 FROM teacher_profiles tp WHERE tp.user_id = u.id);

INSERT INTO teacher_certificates (teacher_id, certificate_name, score, image_url)
SELECT tp.id, 'IELTS Academic', '8.5 Overall', 'https://example.com/cert-ielts-academic-4.png'
FROM teacher_profiles tp
INNER JOIN users u ON u.id = tp.user_id
WHERE u.username = 'teacher4@ec.local'
  AND NOT EXISTS (
      SELECT 1 FROM teacher_certificates tc
      WHERE tc.teacher_id = tp.id AND tc.certificate_name = 'IELTS Academic'
  );

INSERT INTO teacher_certificates (teacher_id, certificate_name, score, image_url)
SELECT tp.id, 'TESOL', 'Distinction', 'https://example.com/cert-tesol-4.png'
FROM teacher_profiles tp
INNER JOIN users u ON u.id = tp.user_id
WHERE u.username = 'teacher4@ec.local'
  AND NOT EXISTS (
      SELECT 1 FROM teacher_certificates tc
      WHERE tc.teacher_id = tp.id AND tc.certificate_name = 'TESOL'
  );

INSERT INTO teacher_certificates (teacher_id, certificate_name, score, image_url)
SELECT tp.id, 'CELTA', 'Pass B', 'https://example.com/cert-celta-5.png'
FROM teacher_profiles tp
INNER JOIN users u ON u.id = tp.user_id
WHERE u.username = 'teacher5@ec.local'
  AND NOT EXISTS (
      SELECT 1 FROM teacher_certificates tc
      WHERE tc.teacher_id = tp.id AND tc.certificate_name = 'CELTA'
  );

INSERT INTO teacher_certificates (teacher_id, certificate_name, score, image_url)
SELECT tp.id, 'Pronunciation Workshop', 'Completed', 'https://example.com/cert-pronunciation-5.png'
FROM teacher_profiles tp
INNER JOIN users u ON u.id = tp.user_id
WHERE u.username = 'teacher5@ec.local'
  AND NOT EXISTS (
      SELECT 1 FROM teacher_certificates tc
      WHERE tc.teacher_id = tp.id AND tc.certificate_name = 'Pronunciation Workshop'
  );

INSERT INTO teacher_certificates (teacher_id, certificate_name, score, image_url)
SELECT tp.id, 'Young Learners Teaching', 'Merit', 'https://example.com/cert-young-learners-6.png'
FROM teacher_profiles tp
INNER JOIN users u ON u.id = tp.user_id
WHERE u.username = 'teacher6@ec.local'
  AND NOT EXISTS (
      SELECT 1 FROM teacher_certificates tc
      WHERE tc.teacher_id = tp.id AND tc.certificate_name = 'Young Learners Teaching'
  );

INSERT INTO teacher_certificates (teacher_id, certificate_name, score, image_url)
SELECT tp.id, 'Phonics Training', 'Completed', 'https://example.com/cert-phonics-6.png'
FROM teacher_profiles tp
INNER JOIN users u ON u.id = tp.user_id
WHERE u.username = 'teacher6@ec.local'
  AND NOT EXISTS (
      SELECT 1 FROM teacher_certificates tc
      WHERE tc.teacher_id = tp.id AND tc.certificate_name = 'Phonics Training'
  );

INSERT INTO teacher_certificates (teacher_id, certificate_name, score, image_url)
SELECT tp.id, 'IELTS Reading', '8.5', 'https://example.com/cert-ielts-reading-7.png'
FROM teacher_profiles tp
INNER JOIN users u ON u.id = tp.user_id
WHERE u.username = 'teacher7@ec.local'
  AND NOT EXISTS (
      SELECT 1 FROM teacher_certificates tc
      WHERE tc.teacher_id = tp.id AND tc.certificate_name = 'IELTS Reading'
  );

INSERT INTO teacher_certificates (teacher_id, certificate_name, score, image_url)
SELECT tp.id, 'TESOL', 'A', 'https://example.com/cert-tesol-7.png'
FROM teacher_profiles tp
INNER JOIN users u ON u.id = tp.user_id
WHERE u.username = 'teacher7@ec.local'
  AND NOT EXISTS (
      SELECT 1 FROM teacher_certificates tc
      WHERE tc.teacher_id = tp.id AND tc.certificate_name = 'TESOL'
  );

INSERT INTO teacher_certificates (teacher_id, certificate_name, score, image_url)
SELECT tp.id, 'Business English', 'Pass', 'https://example.com/cert-business-8.png'
FROM teacher_profiles tp
INNER JOIN users u ON u.id = tp.user_id
WHERE u.username = 'teacher8@ec.local'
  AND NOT EXISTS (
      SELECT 1 FROM teacher_certificates tc
      WHERE tc.teacher_id = tp.id AND tc.certificate_name = 'Business English'
  );

INSERT INTO teacher_certificates (teacher_id, certificate_name, score, image_url)
SELECT tp.id, 'Speaking Coaching', 'Completed', 'https://example.com/cert-speaking-coaching-8.png'
FROM teacher_profiles tp
INNER JOIN users u ON u.id = tp.user_id
WHERE u.username = 'teacher8@ec.local'
  AND NOT EXISTS (
      SELECT 1 FROM teacher_certificates tc
      WHERE tc.teacher_id = tp.id AND tc.certificate_name = 'Speaking Coaching'
  );

UPDATE users SET avatar = '/assets/images/student_girl.png' WHERE username = 'teacher4@ec.local' AND (avatar IS NULL OR avatar = '');
UPDATE users SET avatar = '/assets/images/student.jpg' WHERE username = 'teacher5@ec.local' AND (avatar IS NULL OR avatar = '');
UPDATE users SET avatar = '/assets/images/student2.jpg' WHERE username = 'teacher6@ec.local' AND (avatar IS NULL OR avatar = '');
UPDATE users SET avatar = '/assets/images/student3.jpg' WHERE username = 'teacher7@ec.local' AND (avatar IS NULL OR avatar = '');
UPDATE users SET avatar = '/assets/images/center.jpg' WHERE username = 'teacher8@ec.local' AND (avatar IS NULL OR avatar = '');
UPDATE users SET avatar = '/assets/images/background.jpg' WHERE username = 'teacher9@ec.local' AND (avatar IS NULL OR avatar = '');
UPDATE users SET avatar = '/assets/images/background2.jpg' WHERE username = 'teacher10@ec.local' AND (avatar IS NULL OR avatar = '');
UPDATE users SET avatar = '/assets/images/mission.jpg' WHERE username = 'teacher11@ec.local' AND (avatar IS NULL OR avatar = '');
UPDATE users SET avatar = '/assets/images/student_girl.png' WHERE username = 'teacher12@ec.local' AND (avatar IS NULL OR avatar = '');
UPDATE users SET avatar = '/assets/images/student.jpg' WHERE username = 'teacher13@ec.local' AND (avatar IS NULL OR avatar = '');
UPDATE users SET avatar = '/assets/images/student2.jpg' WHERE username = 'teacher14@ec.local' AND (avatar IS NULL OR avatar = '');
UPDATE users SET avatar = '/assets/images/student3.jpg' WHERE username = 'teacher15@ec.local' AND (avatar IS NULL OR avatar = '');

UPDATE teacher_profiles tp
INNER JOIN users u ON u.id = tp.user_id
SET tp.bio = '[b]Chuyên môn:[/b] IELTS Writing, Speaking và phát triển tư duy học thuật.[br][b]Phương pháp:[/b] Luyện phản xạ qua [i]task-based learning[/i], sửa bài theo từng vòng.'
WHERE u.username = 'teacher4@ec.local';

UPDATE teacher_profiles tp
INNER JOIN users u ON u.id = tp.user_id
SET tp.bio = '[b]Chuyên môn:[/b] Giao tiếp, pronunciation và phát âm chuẩn bản địa.[br][b]Điểm mạnh:[/b] Sửa âm chi tiết, nói tự nhiên và giao tiếp thực tế.'
WHERE u.username = 'teacher5@ec.local';

UPDATE teacher_profiles tp
INNER JOIN users u ON u.id = tp.user_id
SET tp.bio = '[b]Chuyên môn:[/b] Kids Speaking, phonics và nền tảng cho trẻ em.[br][b]Hoạt động lớp:[/b] Học qua trò chơi, hình ảnh và câu chuyện ngắn.'
WHERE u.username = 'teacher6@ec.local';

UPDATE teacher_profiles tp
INNER JOIN users u ON u.id = tp.user_id
SET tp.bio = '[b]Chuyên môn:[/b] IELTS Reading, grammar nâng cao và coaching luyện thi.[br][b]Cách dạy:[/b] Phân tích chiến lược làm bài, hệ thống lỗi sai, luyện tốc độ đọc.'
WHERE u.username = 'teacher7@ec.local';

UPDATE teacher_profiles tp
INNER JOIN users u ON u.id = tp.user_id
SET tp.bio = '[b]Chuyên môn:[/b] Giao tiếp cho người mới bắt đầu và lớp speaking nền tảng.[br][b]Mạnh về:[/b] Khơi gợi sự tự tin, tạo phản xạ nói tự nhiên.[br][url=https://example.com/teacher8]Xem thêm hồ sơ mẫu[/url]'
WHERE u.username = 'teacher8@ec.local';

UPDATE teacher_profiles tp
INNER JOIN users u ON u.id = tp.user_id
SET tp.bio = '[b]Chuyên môn:[/b] Giao tiếp cơ bản, phát âm và từ vựng ứng dụng.[br][b]Lộ trình:[/b][ul][li]Ôn âm và từ vựng[/li][li]Luyện mẫu câu giao tiếp[/li][li]Thực hành phản xạ nhanh[/li][/ul]'
WHERE u.username = 'teacher9@ec.local';

UPDATE teacher_profiles tp
INNER JOIN users u ON u.id = tp.user_id
SET tp.bio = '[b]Chuyên môn:[/b] Lớp giao tiếp cho người đi làm và học viên cần phát triển nói tự tin.[br][b]Phong cách:[/b] Tập trung vào hội thoại, email và tình huống công sở.'
WHERE u.username = 'teacher10@ec.local';

UPDATE teacher_profiles tp
INNER JOIN users u ON u.id = tp.user_id
SET tp.bio = '[b]Chuyên môn:[/b] IELTS Writing, review bài và xây dựng tư duy học thuật.[br][b]Cấu trúc lớp:[/b] Phân tích đề, lập dàn ý, viết nháp, chỉnh sửa final.'
WHERE u.username = 'teacher11@ec.local';

UPDATE teacher_profiles tp
INNER JOIN users u ON u.id = tp.user_id
SET tp.bio = '[b]Chuyên môn:[/b] Lớp phát âm, ngữ điệu và giao tiếp cấp tốc.[br][b]Thực hành:[/b] Nhấn trọng âm, nối âm, ngắt câu và luyện nói theo ngữ cảnh.'
WHERE u.username = 'teacher12@ec.local';

UPDATE teacher_profiles tp
INNER JOIN users u ON u.id = tp.user_id
SET tp.bio = '[b]Chuyên môn:[/b] Lớp trẻ em, phonics và củng cố nền tảng từ vựng.[br][b]Điểm nhấn:[/b] Học vui, nhiều tương tác, tăng phản xạ qua hình ảnh và âm thanh.'
WHERE u.username = 'teacher13@ec.local';

UPDATE teacher_profiles tp
INNER JOIN users u ON u.id = tp.user_id
SET tp.bio = '[b]Chuyên môn:[/b] IELTS Reading và lộ trình theo mục tiêu band điểm.[br][b]Chiến lược:[/b][ol][li]Skimming nhanh[/li][li]Scanning chính xác[/li][li]Xử lý câu hỏi theo keyword[/li][/ol]'
WHERE u.username = 'teacher14@ec.local';

UPDATE teacher_profiles tp
INNER JOIN users u ON u.id = tp.user_id
SET tp.bio = '[b]Chuyên môn:[/b] Giao tiếp thực hành, role-play và phản xạ nhanh.[br][b]Cách học:[/b] Tình huống thực tế, sửa lỗi trực tiếp, tăng sự tự tin khi nói.'
WHERE u.username = 'teacher15@ec.local';

INSERT INTO users (username, password, full_name, role_id, phone, email, status)
SELECT 'teacher9@ec.local', '$2y$10$5luD5xfAGFeqHwRdPWq1ZezZW43r.qwE2wFcaXCanvh1O0DR8XYum', 'Pham Thanh Teacher', r.id, '0900000022', 'teacher9@ec.local', 'active'
FROM roles r
WHERE r.role_name = 'teacher'
  AND NOT EXISTS (SELECT 1 FROM users u WHERE u.username = 'teacher9@ec.local');

INSERT INTO users (username, password, full_name, role_id, phone, email, status)
SELECT 'teacher10@ec.local', '$2y$10$5luD5xfAGFeqHwRdPWq1ZezZW43r.qwE2wFcaXCanvh1O0DR8XYum', 'Doan Thu Teacher', r.id, '0900000023', 'teacher10@ec.local', 'active'
FROM roles r
WHERE r.role_name = 'teacher'
  AND NOT EXISTS (SELECT 1 FROM users u WHERE u.username = 'teacher10@ec.local');

INSERT INTO users (username, password, full_name, role_id, phone, email, status)
SELECT 'teacher11@ec.local', '$2y$10$5luD5xfAGFeqHwRdPWq1ZezZW43r.qwE2wFcaXCanvh1O0DR8XYum', 'Bui An Teacher', r.id, '0900000024', 'teacher11@ec.local', 'active'
FROM roles r
WHERE r.role_name = 'teacher'
  AND NOT EXISTS (SELECT 1 FROM users u WHERE u.username = 'teacher11@ec.local');

INSERT INTO users (username, password, full_name, role_id, phone, email, status)
SELECT 'teacher12@ec.local', '$2y$10$5luD5xfAGFeqHwRdPWq1ZezZW43r.qwE2wFcaXCanvh1O0DR8XYum', 'Ngo Mai Teacher', r.id, '0900000025', 'teacher12@ec.local', 'active'
FROM roles r
WHERE r.role_name = 'teacher'
  AND NOT EXISTS (SELECT 1 FROM users u WHERE u.username = 'teacher12@ec.local');

INSERT INTO users (username, password, full_name, role_id, phone, email, status)
SELECT 'teacher13@ec.local', '$2y$10$5luD5xfAGFeqHwRdPWq1ZezZW43r.qwE2wFcaXCanvh1O0DR8XYum', 'Le Nhi Teacher', r.id, '0900000026', 'teacher13@ec.local', 'active'
FROM roles r
WHERE r.role_name = 'teacher'
  AND NOT EXISTS (SELECT 1 FROM users u WHERE u.username = 'teacher13@ec.local');

INSERT INTO users (username, password, full_name, role_id, phone, email, status)
SELECT 'teacher14@ec.local', '$2y$10$5luD5xfAGFeqHwRdPWq1ZezZW43r.qwE2wFcaXCanvh1O0DR8XYum', 'Tran Hien Teacher', r.id, '0900000027', 'teacher14@ec.local', 'active'
FROM roles r
WHERE r.role_name = 'teacher'
  AND NOT EXISTS (SELECT 1 FROM users u WHERE u.username = 'teacher14@ec.local');

INSERT INTO users (username, password, full_name, role_id, phone, email, status)
SELECT 'teacher15@ec.local', '$2y$10$5luD5xfAGFeqHwRdPWq1ZezZW43r.qwE2wFcaXCanvh1O0DR8XYum', 'Vu Khoa Teacher', r.id, '0900000028', 'teacher15@ec.local', 'active'
FROM roles r
WHERE r.role_name = 'teacher'
  AND NOT EXISTS (SELECT 1 FROM users u WHERE u.username = 'teacher15@ec.local');

INSERT INTO teacher_profiles (user_id, teacher_code, degree, experience_years, bio, intro_video_url)
SELECT u.id, 'GV10009', 'TESOL Certificate', 4, 'Teacher tap trung vao giao tiep co ban, phat am va tu vung ung dung.', 'https://example.com/intro-teacher-9.mp4'
FROM users u
WHERE u.username = 'teacher9@ec.local'
  AND NOT EXISTS (SELECT 1 FROM teacher_profiles tp WHERE tp.user_id = u.id);

INSERT INTO teacher_profiles (user_id, teacher_code, degree, experience_years, bio, intro_video_url)
SELECT u.id, 'GV10010', 'Bachelor of English', 6, 'Teacher day lop giao tiep cho nguoi di lam va hoc vien can phat trien noi tu tin.', 'https://example.com/intro-teacher-10.mp4'
FROM users u
WHERE u.username = 'teacher10@ec.local'
  AND NOT EXISTS (SELECT 1 FROM teacher_profiles tp WHERE tp.user_id = u.id);

INSERT INTO teacher_profiles (user_id, teacher_code, degree, experience_years, bio, intro_video_url)
SELECT u.id, 'GV10011', 'Master of TESOL', 9, 'Teacher phu trach IELTS Writing, review bai va xay dung tu duy hoc thuat.', 'https://example.com/intro-teacher-11.mp4'
FROM users u
WHERE u.username = 'teacher11@ec.local'
  AND NOT EXISTS (SELECT 1 FROM teacher_profiles tp WHERE tp.user_id = u.id);

INSERT INTO teacher_profiles (user_id, teacher_code, degree, experience_years, bio, intro_video_url)
SELECT u.id, 'GV10012', 'CELTA Certificate', 7, 'Teacher phu trach lop phat am, ngu dieu va giao tiep cap toc.', 'https://example.com/intro-teacher-12.mp4'
FROM users u
WHERE u.username = 'teacher12@ec.local'
  AND NOT EXISTS (SELECT 1 FROM teacher_profiles tp WHERE tp.user_id = u.id);

INSERT INTO teacher_profiles (user_id, teacher_code, degree, experience_years, bio, intro_video_url)
SELECT u.id, 'GV10013', 'Bachelor of Education', 5, 'Teacher gioi ve lop tre em, phonics va cung co nen tang tu vung.', 'https://example.com/intro-teacher-13.mp4'
FROM users u
WHERE u.username = 'teacher13@ec.local'
  AND NOT EXISTS (SELECT 1 FROM teacher_profiles tp WHERE tp.user_id = u.id);

INSERT INTO teacher_profiles (user_id, teacher_code, degree, experience_years, bio, intro_video_url)
SELECT u.id, 'GV10014', 'Master of Applied Linguistics', 11, 'Teacher day IELTS Reading va chi dao loi hoc theo muc tieu band diem.', 'https://example.com/intro-teacher-14.mp4'
FROM users u
WHERE u.username = 'teacher14@ec.local'
  AND NOT EXISTS (SELECT 1 FROM teacher_profiles tp WHERE tp.user_id = u.id);

INSERT INTO teacher_profiles (user_id, teacher_code, degree, experience_years, bio, intro_video_url)
SELECT u.id, 'GV10015', 'TEFL Certificate', 8, 'Teacher day giao tiep thuc hanh, role-play va phan xa nhanh trong lop.', 'https://example.com/intro-teacher-15.mp4'
FROM users u
WHERE u.username = 'teacher15@ec.local'
  AND NOT EXISTS (SELECT 1 FROM teacher_profiles tp WHERE tp.user_id = u.id);

UPDATE teacher_profiles tp
INNER JOIN users u ON u.id = tp.user_id
SET tp.bio = '[b]Chuyên môn:[/b] Giao tiếp cơ bản, phát âm và từ vựng ứng dụng.[br][b]Lộ trình:[/b][ul][li]Ôn âm và từ vựng[/li][li]Luyện mẫu câu giao tiếp[/li][li]Thực hành phản xạ nhanh[/li][/ul]'
WHERE u.username = 'teacher9@ec.local';

UPDATE teacher_profiles tp
INNER JOIN users u ON u.id = tp.user_id
SET tp.bio = '[b]Chuyên môn:[/b] Lớp giao tiếp cho người đi làm và học viên cần phát triển nói tự tin.[br][b]Phong cách:[/b] Tập trung vào hội thoại, email và tình huống công sở.'
WHERE u.username = 'teacher10@ec.local';

UPDATE teacher_profiles tp
INNER JOIN users u ON u.id = tp.user_id
SET tp.bio = '[b]Chuyên môn:[/b] IELTS Writing, review bài và xây dựng tư duy học thuật.[br][b]Cấu trúc lớp:[/b] Phân tích đề, lập dàn ý, viết nháp, chỉnh sửa final.'
WHERE u.username = 'teacher11@ec.local';

UPDATE teacher_profiles tp
INNER JOIN users u ON u.id = tp.user_id
SET tp.bio = '[b]Chuyên môn:[/b] Lớp phát âm, ngữ điệu và giao tiếp cấp tốc.[br][b]Thực hành:[/b] Nhấn trọng âm, nối âm, ngắt câu và luyện nói theo ngữ cảnh.'
WHERE u.username = 'teacher12@ec.local';

UPDATE teacher_profiles tp
INNER JOIN users u ON u.id = tp.user_id
SET tp.bio = '[b]Chuyên môn:[/b] Lớp trẻ em, phonics và củng cố nền tảng từ vựng.[br][b]Điểm nhấn:[/b] Học vui, nhiều tương tác, tăng phản xạ qua hình ảnh và âm thanh.'
WHERE u.username = 'teacher13@ec.local';

UPDATE teacher_profiles tp
INNER JOIN users u ON u.id = tp.user_id
SET tp.bio = '[b]Chuyên môn:[/b] IELTS Reading và lộ trình theo mục tiêu band điểm.[br][b]Chiến lược:[/b][ol][li]Skimming nhanh[/li][li]Scanning chính xác[/li][li]Xử lý câu hỏi theo keyword[/li][/ol]'
WHERE u.username = 'teacher14@ec.local';

UPDATE teacher_profiles tp
INNER JOIN users u ON u.id = tp.user_id
SET tp.bio = '[b]Chuyên môn:[/b] Giao tiếp thực hành, role-play và phản xạ nhanh.[br][b]Cách học:[/b] Tình huống thực tế, sửa lỗi trực tiếp, tăng sự tự tin khi nói.'
WHERE u.username = 'teacher15@ec.local';

INSERT INTO teacher_certificates (teacher_id, certificate_name, score, image_url)
SELECT tp.id, 'TESOL', 'Pass', 'https://example.com/cert-tesol-9.png'
FROM teacher_profiles tp
INNER JOIN users u ON u.id = tp.user_id
WHERE u.username = 'teacher9@ec.local'
  AND NOT EXISTS (
      SELECT 1 FROM teacher_certificates tc
      WHERE tc.teacher_id = tp.id AND tc.certificate_name = 'TESOL'
  );

INSERT INTO teacher_certificates (teacher_id, certificate_name, score, image_url)
SELECT tp.id, 'Pronunciation', 'Completed', 'https://example.com/cert-pronunciation-10.png'
FROM teacher_profiles tp
INNER JOIN users u ON u.id = tp.user_id
WHERE u.username = 'teacher10@ec.local'
  AND NOT EXISTS (
      SELECT 1 FROM teacher_certificates tc
      WHERE tc.teacher_id = tp.id AND tc.certificate_name = 'Pronunciation'
  );

INSERT INTO teacher_certificates (teacher_id, certificate_name, score, image_url)
SELECT tp.id, 'IELTS Writing', 'Band 8', 'https://example.com/cert-ielts-writing-11.png'
FROM teacher_profiles tp
INNER JOIN users u ON u.id = tp.user_id
WHERE u.username = 'teacher11@ec.local'
  AND NOT EXISTS (
      SELECT 1 FROM teacher_certificates tc
      WHERE tc.teacher_id = tp.id AND tc.certificate_name = 'IELTS Writing'
  );

INSERT INTO teacher_certificates (teacher_id, certificate_name, score, image_url)
SELECT tp.id, 'CELTA', 'A', 'https://example.com/cert-celta-12.png'
FROM teacher_profiles tp
INNER JOIN users u ON u.id = tp.user_id
WHERE u.username = 'teacher12@ec.local'
  AND NOT EXISTS (
      SELECT 1 FROM teacher_certificates tc
      WHERE tc.teacher_id = tp.id AND tc.certificate_name = 'CELTA'
  );

INSERT INTO teacher_certificates (teacher_id, certificate_name, score, image_url)
SELECT tp.id, 'Young Learners', 'Completed', 'https://example.com/cert-young-learners-13.png'
FROM teacher_profiles tp
INNER JOIN users u ON u.id = tp.user_id
WHERE u.username = 'teacher13@ec.local'
  AND NOT EXISTS (
      SELECT 1 FROM teacher_certificates tc
      WHERE tc.teacher_id = tp.id AND tc.certificate_name = 'Young Learners'
  );

INSERT INTO teacher_certificates (teacher_id, certificate_name, score, image_url)
SELECT tp.id, 'Academic Coaching', 'Pass', 'https://example.com/cert-academic-coaching-14.png'
FROM teacher_profiles tp
INNER JOIN users u ON u.id = tp.user_id
WHERE u.username = 'teacher14@ec.local'
  AND NOT EXISTS (
      SELECT 1 FROM teacher_certificates tc
      WHERE tc.teacher_id = tp.id AND tc.certificate_name = 'Academic Coaching'
  );

INSERT INTO teacher_certificates (teacher_id, certificate_name, score, image_url)
SELECT tp.id, 'Speaking Workshop', 'Completed', 'https://example.com/cert-speaking-workshop-15.png'
FROM teacher_profiles tp
INNER JOIN users u ON u.id = tp.user_id
WHERE u.username = 'teacher15@ec.local'
  AND NOT EXISTS (
      SELECT 1 FROM teacher_certificates tc
      WHERE tc.teacher_id = tp.id AND tc.certificate_name = 'Speaking Workshop'
  );
