USE english_center_db;

INSERT IGNORE INTO users (username, password, full_name, role_id, phone, email, status) VALUES
('teacher4@ec.local', '$2y$10$5luD5xfAGFeqHwRdPWq1ZezZW43r.qwE2wFcaXCanvh1O0DR8XYum', 'Nguyen Phuong Teacher', (SELECT id FROM roles WHERE role_name = 'teacher' LIMIT 1), '0900000017', 'teacher4@ec.local', 'active'),
('teacher5@ec.local', '$2y$10$5luD5xfAGFeqHwRdPWq1ZezZW43r.qwE2wFcaXCanvh1O0DR8XYum', 'Hoang My Teacher', (SELECT id FROM roles WHERE role_name = 'teacher' LIMIT 1), '0900000018', 'teacher5@ec.local', 'active'),
('teacher6@ec.local', '$2y$10$5luD5xfAGFeqHwRdPWq1ZezZW43r.qwE2wFcaXCanvh1O0DR8XYum', 'Tran Quynh Teacher', (SELECT id FROM roles WHERE role_name = 'teacher' LIMIT 1), '0900000019', 'teacher6@ec.local', 'active'),
('teacher7@ec.local', '$2y$10$5luD5xfAGFeqHwRdPWq1ZezZW43r.qwE2wFcaXCanvh1O0DR8XYum', 'Le Gia Teacher', (SELECT id FROM roles WHERE role_name = 'teacher' LIMIT 1), '0900000020', 'teacher7@ec.local', 'active'),
('teacher8@ec.local', '$2y$10$5luD5xfAGFeqHwRdPWq1ZezZW43r.qwE2wFcaXCanvh1O0DR8XYum', 'Vo Khanh Teacher', (SELECT id FROM roles WHERE role_name = 'teacher' LIMIT 1), '0900000021', 'teacher8@ec.local', 'active');

INSERT IGNORE INTO teacher_profiles (user_id, degree, experience_years, bio, intro_video_url) VALUES
((SELECT id FROM users WHERE username = 'teacher4@ec.local' LIMIT 1), 'Master of Applied Linguistics', 8, 'Teacher chuyen IELTS Writing, Speaking va phat trien tu duy hoc thuat cho hoc vien cap trung cap.', 'https://example.com/intro-teacher-4.mp4'),
((SELECT id FROM users WHERE username = 'teacher5@ec.local' LIMIT 1), 'CELTA Certificate', 7, 'Teacher tap trung vao giao tiep, pronunciation va phat am chuan ban dia.', 'https://example.com/intro-teacher-5.mp4'),
((SELECT id FROM users WHERE username = 'teacher6@ec.local' LIMIT 1), 'Bachelor of English Education', 5, 'Teacher phu trach Kids Speaking, phonics va lop nen tang cho tre em.', 'https://example.com/intro-teacher-6.mp4'),
((SELECT id FROM users WHERE username = 'teacher7@ec.local' LIMIT 1), 'Master of Education', 10, 'Teacher phu trach IELTS Reading, grammar nâng cao va coaching luyen thi.', 'https://example.com/intro-teacher-7.mp4'),
((SELECT id FROM users WHERE username = 'teacher8@ec.local' LIMIT 1), 'Bachelor of Linguistics', 6, 'Teacher day giao tiep cho nguoi moi bat dau va lop speaking khoa nen tang.', 'https://example.com/intro-teacher-8.mp4');

INSERT IGNORE INTO teacher_certificates (teacher_id, certificate_name, score, image_url) VALUES
((SELECT tp.id FROM teacher_profiles tp INNER JOIN users u ON u.id = tp.user_id WHERE u.username = 'teacher4@ec.local' LIMIT 1), 'IELTS Academic', '8.5 Overall', 'https://example.com/cert-ielts-academic-4.png'),
((SELECT tp.id FROM teacher_profiles tp INNER JOIN users u ON u.id = tp.user_id WHERE u.username = 'teacher4@ec.local' LIMIT 1), 'TESOL', 'Distinction', 'https://example.com/cert-tesol-4.png'),
((SELECT tp.id FROM teacher_profiles tp INNER JOIN users u ON u.id = tp.user_id WHERE u.username = 'teacher5@ec.local' LIMIT 1), 'CELTA', 'Pass B', 'https://example.com/cert-celta-5.png'),
((SELECT tp.id FROM teacher_profiles tp INNER JOIN users u ON u.id = tp.user_id WHERE u.username = 'teacher5@ec.local' LIMIT 1), 'Pronunciation Workshop', 'Completed', 'https://example.com/cert-pronunciation-5.png'),
((SELECT tp.id FROM teacher_profiles tp INNER JOIN users u ON u.id = tp.user_id WHERE u.username = 'teacher6@ec.local' LIMIT 1), 'Young Learners Teaching', 'Merit', 'https://example.com/cert-young-learners-6.png'),
((SELECT tp.id FROM teacher_profiles tp INNER JOIN users u ON u.id = tp.user_id WHERE u.username = 'teacher6@ec.local' LIMIT 1), 'Phonics Training', 'Completed', 'https://example.com/cert-phonics-6.png'),
((SELECT tp.id FROM teacher_profiles tp INNER JOIN users u ON u.id = tp.user_id WHERE u.username = 'teacher7@ec.local' LIMIT 1), 'IELTS Reading', '8.5', 'https://example.com/cert-ielts-reading-7.png'),
((SELECT tp.id FROM teacher_profiles tp INNER JOIN users u ON u.id = tp.user_id WHERE u.username = 'teacher7@ec.local' LIMIT 1), 'TESOL', 'A', 'https://example.com/cert-tesol-7.png'),
((SELECT tp.id FROM teacher_profiles tp INNER JOIN users u ON u.id = tp.user_id WHERE u.username = 'teacher8@ec.local' LIMIT 1), 'Business English', 'Pass', 'https://example.com/cert-business-8.png'),
((SELECT tp.id FROM teacher_profiles tp INNER JOIN users u ON u.id = tp.user_id WHERE u.username = 'teacher8@ec.local' LIMIT 1), 'Speaking Coaching', 'Completed', 'https://example.com/cert-speaking-coaching-8.png');