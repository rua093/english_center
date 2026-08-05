-- =======================================================
-- Seed: Populate Media Categories and Sample Media Items
-- File: database/seed_media_gallery.sql
-- =======================================================
SET NAMES utf8mb4;


INSERT INTO `media_categories` (`id`, `name`, `slug`, `description`, `display_order`, `is_active`) VALUES
(1, 'Hoạt động lớp học', 'hoat-dong-lop-hoc', 'Hình ảnh & video các buổi học hằng ngày, làm việc nhóm, thực hành nói tiếng Anh.', 1, 1),
(2, 'Ngoại khóa', 'ngoai-khoa', 'Các chuyến tham quan, câu lạc bộ tiếng Anh và hoạt động ngoài trời.', 2, 1),
(3, 'Cuộc thi', 'cuoc-thi', 'Hình ảnh các cuộc thi hùng biện, Rung Chuông Vàng, English Speaking Contest.', 3, 1),
(4, 'Lễ trao chứng chỉ', 'le-trao-chung-chi', 'Khoảnh khắc vinh danh và nhận chứng chỉ Cambridge, IELTS của các học viên.', 4, 1),
(5, 'Summer Camp', 'summer-camp', 'Trại hè trải nghiệm tiếng Anh toàn diện với nhiều hoạt động bổ ích.', 5, 1),
(6, 'Halloween', 'halloween', 'Lễ hội hóa trang Halloween vui nhộn tại trung tâm.', 6, 1),
(7, 'Noel', 'noel', 'Không khí Giáng Sinh ấm áp và các phần quà ý nghĩa cho học viên.', 7, 1),
(8, 'Tết', 'tet', 'Các hoạt động chào đón Tết cổ truyền, khai xuân đầu năm.', 8, 1)
ON DUPLICATE KEY UPDATE 
  `name` = VALUES(`name`),
  `description` = VALUES(`description`),
  `display_order` = VALUES(`display_order`);

INSERT INTO `media_items` (`category_id`, `title`, `media_type`, `file_path_or_url`, `thumbnail_url`, `description`, `is_featured`, `is_active`) VALUES
(1, 'Buổi học thuyết trình tiếng Anh sôi nổi của lớp IELTS Master', 'image', 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?q=80&w=1200&auto=format&fit=crop', NULL, 'Các bạn học viên cùng nhau tương tác bài giảng và thuyết trình nhóm sôi nổi.', 1, 1),
(1, 'Thực hành giao tiếp 1-1 cùng giáo viên bản ngữ', 'image', 'https://images.unsplash.com/photo-1577896851231-70ef18881754?q=80&w=1200&auto=format&fit=crop', NULL, 'Hình ảnh giờ học rèn luyện phản xạ phát âm với thầy giáo bản xứ.', 0, 1),
(2, 'Chuyến picnic học tiếng Anh ngoài trời tại công viên', 'image', 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?q=80&w=1200&auto=format&fit=crop', NULL, 'Hoạt động dã ngoại kết hợp các trò chơi team building nói tiếng Anh.', 1, 1),
(3, 'Chung kết cuộc thi English Speaking Contest 2026', 'youtube', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'https://images.unsplash.com/photo-1475721027785-f74eccf877e2?q=80&w=1200&auto=format&fit=crop', 'Highlights ấn tượng từ vòng chung kết hùng biện tiếng Anh toàn trung tâm.', 1, 1),
(4, 'Lễ trao chứng chỉ Cambridge KET/PET đợt 1 năm 2026', 'image', 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?q=80&w=1200&auto=format&fit=crop', NULL, 'Chúc mừng các em học viên đã xuất sắc đạt thành tích cao trong kỳ thi Cambridge vừa qua.', 1, 1),
(5, 'Trại hè Summer Camp 2026 - Hành trình khám phá thiên nhiên', 'image', 'https://images.unsplash.com/photo-1517486808906-6ca8b3f04846?q=80&w=1200&auto=format&fit=crop', NULL, 'Trải nghiệm mùa hè đáng nhớ với chuỗi hoạt động kỹ năng sống và tiếng Anh nhập vai.', 1, 1),
(6, 'Đêm hội hóa trang Halloween Spooky Night', 'image', 'https://images.unsplash.com/photo-1509557965875-b88c97052f0e?q=80&w=1200&auto=format&fit=crop', NULL, 'Các bé học viên hóa trang thành những nhân vật huyền bí ấn tượng.', 0, 1),
(7, 'Giáng Sinh ấm áp Merry Christmas 2025', 'image', 'https://images.unsplash.com/photo-1512389142860-9c449e58a543?q=80&w=1200&auto=format&fit=crop', NULL, 'Trang trí Noel lung linh và hoạt động nhận quà từ Ông già Noel.', 0, 1),
(8, 'Khai xuân đón Tết Ất Tỵ - Nhận lì xì may mắn', 'image', 'https://images.unsplash.com/photo-1544717305-2782549b5136?q=80&w=1200&auto=format&fit=crop', NULL, 'Hoạt động viết thư pháp tiếng Anh và nhận lì xì khai xuân tràn ngập niềm vui.', 0, 1);
