-- Sample seed for student_portfolios (matches schema in db.sql)
-- Adjust student_id values if needed to match your users table.

INSERT INTO `student_portfolios` (student_id, type, media_url, description, is_public_web, created_at) VALUES
(45, 'progress_video', '/assets/uploads/portfolios/sample-student-1.mp4', 'Học viên đạt tiến bộ rõ rệt sau 3 tháng', 1, '2026-04-01 10:00:00'),
(46, 'activity_photo', '/assets/uploads/portfolios/sample-student-2.jpg', 'Bài tập ngoại khóa: thuyết trình nhóm', 1, '2026-04-03 11:15:00'),
(2,  'feedback', '/assets/uploads/portfolios/sample-student-3.mp4', 'Lời chia sẻ của phụ huynh về quá trình học', 1, '2026-04-05 09:30:00');
