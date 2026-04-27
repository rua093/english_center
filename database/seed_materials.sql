INSERT INTO materials (course_id, title, description, file_path) VALUES
((SELECT id FROM courses WHERE id = 1 LIMIT 1), 'Listening Practice Set 01', 'Bo bai nghe co dap an cho hoc vien moi bat dau.', '/assets/uploads/material-listening-1.pdf'),
((SELECT id FROM courses WHERE id = 1 LIMIT 1), 'Negotiation Roleplay Video', 'Video thuc hanh dam phan trong boi canh cong viec.', '/assets/uploads/material-business-negotiation.mp4'),
((SELECT id FROM courses WHERE id = 1 LIMIT 1), 'Email Writing Templates', 'Mau email chuyen nghiep cho moi tinh huong cong viec.', '/assets/uploads/material-business-email-writing.pdf'),
((SELECT id FROM courses WHERE id = 1 LIMIT 1), 'Meeting Phrases Cheat Sheet', 'Cum tu dung nhanh khi hop va trao doi cong viec.', '/assets/uploads/material-meeting-phrases.pdf'),
((SELECT id FROM courses WHERE id = 1 LIMIT 1), 'TOEIC Listening Part 2 Audio', 'File nghe luyen dang cau hoi dap ngan.', '/assets/uploads/material-toeic-part2.mp3'),
((SELECT id FROM courses WHERE id = 1 LIMIT 1), 'TOEIC Vocabulary Pack', 'Danh sach tu vung TOEIC theo chu de va bai tap on luuyen.', '/assets/uploads/material-toeic-vocab-pack.pdf'),
((SELECT id FROM courses WHERE id = 1 LIMIT 1), 'Reading Strategy Notes', 'Ghi chu chien luoc doc hieu va tim y chinh.', '/assets/uploads/material-reading-strategy.pdf'),
((SELECT id FROM courses WHERE id = 1 LIMIT 1), 'Kids Color Flashcards', 'Bo the mau sac ho tro tu vung cho tre em.', '/assets/uploads/material-kids-flashcards.pdf'),
((SELECT id FROM courses WHERE id = 1 LIMIT 1), 'Kids Pronunciation Warmup', 'Hoat dong khop am va lop noi cho hoc vien nhi dong.', '/assets/uploads/material-kids-pronunciation.mp4'),
((SELECT id FROM courses WHERE id = 1 LIMIT 1), 'Story Time Audio', 'File nghe ke chuyen ngan danh cho hoc vien nhi.', '/assets/uploads/material-story-time.mp3');
