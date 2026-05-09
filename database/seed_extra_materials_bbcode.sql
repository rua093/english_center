USE english_center_db;
SET NAMES utf8mb4 COLLATE utf8mb4_0900_ai_ci;

DELETE FROM materials WHERE title IN ('Listening Warmup Pack', 'Listening Warm-up Pack');

INSERT INTO materials (title, description, file_path)
SELECT 'Listening Warm-up Pack',
    '[b]Mục tiêu:[/b] Khởi động kỹ năng nghe với bài ngắn, chậm và rõ.[br][b]Nội dung:[/b][ul][li]Nghe nhận diện từ khóa[/li][li]Nghe chọn đáp án đúng[/li][li]Kiểm tra lại đáp án[/li][/ul][br][b]Phù hợp:[/b] Học viên mới bắt đầu luyện [i]Listening[/i].',
       '/assets/uploads/lessons/lesson_attachment-1777542438-db_english_center2.docx'
WHERE NOT EXISTS (SELECT 1 FROM materials WHERE title = 'Listening Warm-up Pack');

INSERT INTO materials (title, description, file_path)
SELECT 'Business Email Writing Guide',
    '[b]Mục tiêu:[/b] Tạo phản xạ viết email công việc ngắn gọn, lịch sự và đúng mục đích.[br][b]Gợi ý cấu trúc:[/b][ol][li]Mở đầu[/li][li]Nội dung chính[/li][li]Lời kết[/li][/ol][br][quote]Viết rõ ý, ngắn gọn và đúng ngữ cảnh.[/quote]',
       '/assets/uploads/lessons/lesson_attachment-1777996238-assignment-1776362295-db_english_center2.docx'
WHERE NOT EXISTS (SELECT 1 FROM materials WHERE title = 'Business Email Writing Guide');

INSERT INTO materials (title, description, file_path)
SELECT 'Pronunciation Drill Video',
    '[b]Mục tiêu:[/b] Cải thiện phát âm, ngữ điệu và nối âm qua video mẫu.[br][b]Cách học:[/b][ul][li]Xem một lượt để nắm nội dung[/li][li]Lặp lại theo từng câu[/li][li]Ghi chú lỗi phát âm[/li][/ul]',
       '/assets/uploads/teacher-videos/teacher-video-1777208497-iilavideo.mp4'
WHERE NOT EXISTS (SELECT 1 FROM materials WHERE title = 'Pronunciation Drill Video');

INSERT INTO materials (title, description, file_path)
SELECT 'Reading Strategy Notes',
    '[b]Mục tiêu:[/b] Tăng tốc đọc hiểu với chiến lược [i]skimming[/i] và [i]scanning[/i].[br][b]Nội dung:[/b] Tóm tắt mẹo làm bài, từ khóa và cách xác định ý chính.[br][url=https://example.com/reading-strategy]Xem tài liệu tham khảo[/url]',
       '/assets/uploads/lessons/lesson_attachment-1777995517-lesson_attachment-1777634497-submission-46-2-1777542965-db_english_center2.docx'
WHERE NOT EXISTS (SELECT 1 FROM materials WHERE title = 'Reading Strategy Notes');

INSERT INTO materials (title, description, file_path)
SELECT 'Kids Flashcards Set',
    '[b]Mục tiêu:[/b] Học từ vựng qua hình ảnh cho học viên nhỏ tuổi.[br][b]Hoạt động:[/b][ul][li]Nhìn tranh - đọc từ[/li][li]Nghe và nhắc lại[/li][li]Ghép cặp từ - hình[/li][/ul]',
       '/assets/uploads/avatar-1777050401-z6991217903693_c89af11b89482b5580205806c17a67db.jpg'
WHERE NOT EXISTS (SELECT 1 FROM materials WHERE title = 'Kids Flashcards Set');
