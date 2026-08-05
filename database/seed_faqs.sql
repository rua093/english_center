-- ==========================================================
-- Seed Data: Initial FAQs
-- Project: Nhuệ Minh English Center
-- File: database/seed_faqs.sql
-- ==========================================================

INSERT INTO `faqs` (`category`, `question`, `answer`, `sort_order`, `is_active`) VALUES
('Độ tuổi & Đầu vào', 
'Bao nhiêu tuổi học được?', 
'Chương trình học tại Nhuệ Minh được thiết kế chuyên biệt dành riêng cho các bé từ 4 đến 16+ tuổi (từ Mầm nông, Tiểu học, THCS đến THPT). Các bé từ 4 - 6 tuổi sẽ bắt đầu với lộ trình Tiếng Anh Mầm Non trải nghiệm phản xạ tự nhiên; từ 6 - 12 tuổi theo học lộ trình Chứng chỉ Cambridge (Starters, Movers, Flyers); từ 12 - 16+ tuổi luyện thi KET, PET và IELTS học thuật.', 
1, 1),

('Môi trường học tập', 
'Một lớp bao nhiêu học viên?', 
'Để đảm bảo chất lượng tương tác 1:1 tối đa và giúp giáo viên theo sát từng bé, mỗi lớp học tại Nhuệ Minh duy trì sĩ số vàng chỉ từ 8 đến 12 học viên. Sĩ số nhỏ giúp giáo viên bản ngữ và trợ giảng chỉnh sửa phát âm chuẩn xác cho từng học sinh trong suốt 100% thời lượng buổi học.', 
2, 1),

('Đăng ký & Trải nghiệm', 
'Có học thử không?', 
'Có! Nhuệ Minh hỗ trợ 100% MIỄN PHÍ buổi Test trình độ 4 kỹ năng (Nghe, Nói, Đọc, Viết) chuẩn Cambridge/IELTS cùng buổi học trải nghiệm thực tế tại trung tâm. Phụ huynh sẽ nhận được bảng đánh giá năng lực chi tiết và tư vấn lộ trình học phù hợp nhất trước khi quyết định đăng ký.', 
3, 1),

('Khóa học & Học phí', 
'Học phí thế nào?', 
'Mức học phí tại Nhuệ Minh được xây dựng rất tối ưu và linh hoạt dựa trên lộ trình học mà bé tham gia (Tiếng Anh mầm non, Cambrige YLE, KET/PET hay IELTS). Trung tâm thường xuyên có các chính sách ưu đãi học phí đăng ký sớm, ưu đãi đăng ký theo nhóm hoặc học bổng tài năng. Quý phụ huynh vui lòng để lại thông tin để nhận bảng phí chi tiết cùng ưu đãi giảm tới 15% mới nhất.', 
4, 1),

('Lịch học & Chính sách', 
'Nghỉ học có học bù không?', 
'Có! Nhuệ Minh cam kết đảm bảo quyền lợi tối đa cho học viên. Khi bé nghỉ học có lý do (được phụ huynh xin phép trước buổi học), trung tâm sẽ bố trí giáo viên bổ trợ 1:1 hoặc xếp lịch học bù vào các lớp tương đương trình độ hoàn toàn miễn phí để bé không bị hổng kiến thức.', 
5, 1),

('Góc phụ huynh', 
'Cách đồng hành cùng con học tiếng Anh.', 
'Phụ huynh không nhất thiết phải giỏi tiếng Anh mới có thể đồng hành cùng con! Điều quan trọng nhất là tạo tâm lý yêu thích và thói quen tích cực. Ba mẹ nên:\n- Khen ngợi và động viên con ngay khi bé nói được từ/câu tiếng Anh mới.\n- Lắng nghe con chia sẻ về buổi học trên lớp và nhờ con "dạy lại" ba mẹ từ vựng hôm nay.\n- Phối hợp chặt chẽ với giáo viên chủ nhiệm thông qua báo cáo học tập định kỳ của Nhuệ Minh.', 
6, 1),

('Góc phụ huynh', 
'Bí quyết tạo môi trường tiếng Anh tại nhà.', 
'Để giúp con "tắm" trong ngôn ngữ tự nhiên mỗi ngày, ba mẹ có thể áp dụng 4 bí quyết đơn giản:\n1. Cho con nghe nhạc thiếu nhi tiếng Anh (Super Simple Songs, Cocomelon) hoặc bật sách nói 15-20 phút trước khi đi ngủ.\n2. Chuyển ngôn ngữ giao diện TV, iPad hoặc điện thoại gia đình sang tiếng Anh.\n3. Đặt thẻ từ vựng (flashcards) hoặc dán nhãn tiếng Anh lên các vật dụng quen thuộc trong nhà.\n4. Duy trì thói quen đọc truyện tranh song ngữ cùng con mỗi tuần.', 
7, 1),

('Giải đáp các câu hỏi thường gặp', 
'Giải đáp các câu hỏi thường gặp về chứng chỉ Cambridge & IELTS', 
'Chứng chỉ Cambridge (Starters, Movers, Flyers, KET, PET) có giá trị vô thời hạn toàn cầu. Học sinh đạt chứng chỉ B1 PET hoặc IELTS 4.0+ sẽ được Bộ GD&ĐT miễn thi bài thi môn Tiếng Anh trong kỳ thi Tốt nghiệp THPT và ưu tiên tuyển sinh vào các trường THCS/THPT chuyên và đại học top đầu.', 
8, 1)
ON DUPLICATE KEY UPDATE `question` = VALUES(`question`), `answer` = VALUES(`answer`), `category` = VALUES(`category`);
