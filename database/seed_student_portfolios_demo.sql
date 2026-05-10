-- Seed sample student_portfolios data
-- Columns assumed: id, student_name, avatar_url, media_url, description, result, created_at, approved
-- Adjust column names/types to match your actual schema before running.

INSERT INTO student_portfolios (student_name, avatar_url, media_url, description, result, created_at, approved) VALUES
('Nguyễn Văn A', '', 'https://sample-videos.com/video123/mp4/720/big_buck_bunny_720p_1mb.mp4', 'Sau khóa học, điểm IELTS đã tăng 2.0 điểm, tự tin giao tiếp.', 'IELTS 7.0', NOW(), 1),
('Trần Thị B', '', 'https://images.unsplash.com/photo-1529626455594-4ff0802cfb7e', 'Được hướng dẫn tận tâm, con em tiến bộ rõ rệt.', 'Hoàn thành khóa học', NOW(), 1),
('Lê Văn C', '', 'https://sample-videos.com/video123/mp4/720/big_buck_bunny_720p_1mb.mp4', 'Phương pháp học giúp mình tự tin hơn rất nhiều.', 'Nâng cao kỹ năng giao tiếp', NOW(), 1);
