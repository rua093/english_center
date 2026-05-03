USE english_center_db;

SET @class_id := 1;
SET @teacher_id := 45;
SET @room_id := 1;

DELETE FROM submissions
WHERE assignment_id IN (
    SELECT assignment_id FROM (
        SELECT a.id AS assignment_id
        FROM assignments a
        INNER JOIN schedules s ON s.id = a.schedule_id
        WHERE s.class_id = @class_id
          AND (a.title LIKE 'Bài tập demo xuất file %' OR a.title LIKE 'Bai tap demo xuat file %')
    ) seeded_assignments
);

DELETE FROM attendance
WHERE schedule_id IN (
    SELECT schedule_id FROM (
        SELECT id AS schedule_id
        FROM schedules
        WHERE class_id = @class_id
          AND study_date IN ('2026-05-02', '2026-05-05', '2026-05-08', '2026-05-12')
    ) seeded_schedules
);

DELETE FROM assignments
WHERE (title LIKE 'Bài tập demo xuất file %' OR title LIKE 'Bai tap demo xuat file %')
  AND schedule_id IN (
      SELECT schedule_id FROM (
          SELECT id AS schedule_id
          FROM schedules
          WHERE class_id = @class_id
            AND study_date IN ('2026-05-02', '2026-05-05', '2026-05-08', '2026-05-12')
      ) seeded_schedules
  );

DELETE FROM exams
WHERE class_id = @class_id
  AND exam_name IN ('Kiểm tra giữa khóa demo', 'Kiểm tra cuối khóa demo', 'Kiem tra giua khoa demo', 'Kiem tra cuoi khoa demo');

DELETE FROM schedules
WHERE class_id = @class_id
  AND study_date IN ('2026-05-02', '2026-05-05', '2026-05-08', '2026-05-12');

INSERT IGNORE INTO class_students (class_id, student_id, enrollment_date)
VALUES
    (@class_id, 131, '2026-04-20'),
    (@class_id, 132, '2026-04-20'),
    (@class_id, 133, '2026-04-20'),
    (@class_id, 134, '2026-04-20'),
    (@class_id, 135, '2026-04-20'),
    (@class_id, 136, '2026-04-20');

INSERT INTO schedules (class_id, room_id, teacher_id, study_date, start_time, end_time)
VALUES
    (@class_id, @room_id, @teacher_id, '2026-05-02', '18:00:00', '20:00:00'),
    (@class_id, @room_id, @teacher_id, '2026-05-05', '18:00:00', '20:00:00'),
    (@class_id, @room_id, @teacher_id, '2026-05-08', '18:00:00', '20:00:00'),
    (@class_id, @room_id, @teacher_id, '2026-05-12', '18:00:00', '20:00:00');

SET @schedule_1 := (SELECT id FROM schedules WHERE class_id = @class_id AND study_date = '2026-05-02' LIMIT 1);
SET @schedule_2 := (SELECT id FROM schedules WHERE class_id = @class_id AND study_date = '2026-05-05' LIMIT 1);
SET @schedule_3 := (SELECT id FROM schedules WHERE class_id = @class_id AND study_date = '2026-05-08' LIMIT 1);
SET @schedule_4 := (SELECT id FROM schedules WHERE class_id = @class_id AND study_date = '2026-05-12' LIMIT 1);

INSERT INTO assignments (schedule_id, title, description, deadline, file_url)
VALUES
    (@schedule_1, 'Bai tap demo xuat file 01', 'Bai luyen viet doan van ngan ve chu de gia dinh.', '2026-05-03 21:00:00', NULL),
    (@schedule_2, 'Bai tap demo xuat file 02', 'Bai nghe va tra loi cau hoi theo doan hoi thoai.', '2026-05-06 21:00:00', NULL),
    (@schedule_3, 'Bai tap demo xuat file 03', 'Bai doc hieu voi cau hoi trac nghiem va tu luan.', '2026-05-09 21:00:00', NULL),
    (@schedule_4, 'Bai tap demo xuat file 04', 'Bai noi ghi am gioi thieu ban than va muc tieu hoc tap.', '2026-05-13 21:00:00', NULL);

SET @assignment_1 := (SELECT id FROM assignments WHERE schedule_id = @schedule_1 AND title = 'Bai tap demo xuat file 01' LIMIT 1);
SET @assignment_2 := (SELECT id FROM assignments WHERE schedule_id = @schedule_2 AND title = 'Bai tap demo xuat file 02' LIMIT 1);
SET @assignment_3 := (SELECT id FROM assignments WHERE schedule_id = @schedule_3 AND title = 'Bai tap demo xuat file 03' LIMIT 1);
SET @assignment_4 := (SELECT id FROM assignments WHERE schedule_id = @schedule_4 AND title = 'Bai tap demo xuat file 04' LIMIT 1);

INSERT INTO attendance (schedule_id, student_id, status, note)
VALUES
    (2, 46, 'present', 'Di hoc day du'),
    (2, 131, 'present', 'Tap trung tot'),
    (2, 132, 'late', 'Den muon 10 phut'),
    (2, 133, 'present', NULL),
    (2, 134, 'absent', 'Xin nghi co phep'),
    (2, 135, 'present', NULL),
    (2, 136, 'late', 'Ket xe'),
    (@schedule_1, 46, 'present', NULL),
    (@schedule_1, 131, 'present', NULL),
    (@schedule_1, 132, 'present', NULL),
    (@schedule_1, 133, 'late', 'Vao lop muon'),
    (@schedule_1, 134, 'present', NULL),
    (@schedule_1, 135, 'absent', 'Om'),
    (@schedule_1, 136, 'present', NULL),
    (@schedule_2, 46, 'late', 'Tan hoc o truong muon'),
    (@schedule_2, 131, 'present', NULL),
    (@schedule_2, 132, 'absent', 'Nghi khong phep'),
    (@schedule_2, 133, 'present', NULL),
    (@schedule_2, 134, 'present', NULL),
    (@schedule_2, 135, 'late', 'Den tre 5 phut'),
    (@schedule_2, 136, 'present', NULL),
    (@schedule_3, 46, 'present', NULL),
    (@schedule_3, 131, 'late', 'Mua lon'),
    (@schedule_3, 132, 'present', NULL),
    (@schedule_3, 133, 'present', NULL),
    (@schedule_3, 134, 'present', NULL),
    (@schedule_3, 135, 'present', NULL),
    (@schedule_3, 136, 'absent', 'Ban viec gia dinh'),
    (@schedule_4, 46, 'present', NULL),
    (@schedule_4, 131, 'present', NULL),
    (@schedule_4, 132, 'present', NULL),
    (@schedule_4, 133, 'absent', 'Sot nhe'),
    (@schedule_4, 134, 'late', 'Ket xe'),
    (@schedule_4, 135, 'present', NULL),
    (@schedule_4, 136, 'present', NULL)
ON DUPLICATE KEY UPDATE
    status = VALUES(status),
    note = VALUES(note);

INSERT INTO submissions (assignment_id, student_id, file_url, submitted_at, score, teacher_comment)
VALUES
    (2, 131, '/assets/uploads/homeworks/demo-a0-131.docx', '2026-05-01 10:00:00', 8.50, 'Noi dung kha tot, can sua chinh ta vai cho'),
    (2, 132, '/assets/uploads/homeworks/demo-a0-132.docx', '2026-05-01 10:20:00', 7.25, 'Du y nhung dien dat con ngan'),
    (2, 133, '/assets/uploads/homeworks/demo-a0-133.docx', '2026-05-01 09:45:00', 9.00, 'Lam tot, co vi du minh hoa'),
    (2, 135, '/assets/uploads/homeworks/demo-a0-135.docx', '2026-05-01 11:05:00', 6.75, 'Can bo sung phan ket luan'),
    (2, 136, '/assets/uploads/homeworks/demo-a0-136.docx', '2026-05-01 10:40:00', 8.00, 'On, chu y dung tu tu nhien hon'),
    (@assignment_1, 46, '/assets/uploads/homeworks/demo-a1-46.docx', '2026-05-03 19:10:00', 8.75, 'Bo cuc ro, can da dang mau cau hon'),
    (@assignment_1, 131, '/assets/uploads/homeworks/demo-a1-131.docx', '2026-05-03 18:40:00', 9.25, 'Viet tot, y mach lac'),
    (@assignment_1, 132, '/assets/uploads/homeworks/demo-a1-132.docx', '2026-05-03 20:55:00', 7.50, 'Dung yeu cau, can cai thien ngu phap'),
    (@assignment_1, 134, '/assets/uploads/homeworks/demo-a1-134.docx', '2026-05-04 08:15:00', 6.80, 'Nop tre, noi dung tam on'),
    (@assignment_1, 136, '/assets/uploads/homeworks/demo-a1-136.docx', '2026-05-03 19:30:00', 8.10, 'Co tien bo so voi bai truoc'),
    (@assignment_2, 46, '/assets/uploads/homeworks/demo-a2-46.mp3', '2026-05-06 20:10:00', 8.40, 'Phan xa kha nhanh'),
    (@assignment_2, 131, '/assets/uploads/homeworks/demo-a2-131.mp3', '2026-05-06 20:20:00', 8.90, 'Phat am on va kha tu nhien'),
    (@assignment_2, 133, '/assets/uploads/homeworks/demo-a2-133.mp3', '2026-05-06 20:40:00', 7.90, 'Can noi ro hon o phan cuoi'),
    (@assignment_2, 135, '/assets/uploads/homeworks/demo-a2-135.mp3', '2026-05-06 22:10:00', 6.95, 'Nop tre, am luong hoi nho'),
    (@assignment_2, 136, '/assets/uploads/homeworks/demo-a2-136.mp3', '2026-05-06 20:05:00', 8.35, 'Hoan thanh kha tot'),
    (@assignment_3, 46, '/assets/uploads/homeworks/demo-a3-46.pdf', '2026-05-09 18:50:00', 9.10, 'Lap luan tot, tra loi day du'),
    (@assignment_3, 131, '/assets/uploads/homeworks/demo-a3-131.pdf', '2026-05-09 19:20:00', 8.80, 'On, can chu y toc do doc'),
    (@assignment_3, 132, '/assets/uploads/homeworks/demo-a3-132.pdf', '2026-05-09 18:40:00', 7.10, 'Sai vai cau chi tiet'),
    (@assignment_3, 133, '/assets/uploads/homeworks/demo-a3-133.pdf', '2026-05-09 21:25:00', 6.50, 'Nop tre, noi dung thieu mot y'),
    (@assignment_3, 134, '/assets/uploads/homeworks/demo-a3-134.pdf', '2026-05-09 19:45:00', 8.00, 'Lam kha chac'),
    (@assignment_4, 46, '/assets/uploads/homeworks/demo-a4-46.mp4', '2026-05-13 20:10:00', 8.65, 'Phong thai tu tin'),
    (@assignment_4, 131, '/assets/uploads/homeworks/demo-a4-131.mp4', '2026-05-13 19:40:00', 9.30, 'Noi luu loat, phat am tot'),
    (@assignment_4, 132, '/assets/uploads/homeworks/demo-a4-132.mp4', '2026-05-13 20:30:00', 7.85, 'Can tang phan xa cau hoi phu'),
    (@assignment_4, 135, '/assets/uploads/homeworks/demo-a4-135.mp4', '2026-05-13 20:55:00', 7.40, 'Hoan thanh du, can ro rang hon'),
    (@assignment_4, 136, '/assets/uploads/homeworks/demo-a4-136.mp4', '2026-05-14 08:30:00', 6.90, 'Nop tre, thieu tu tin')
ON DUPLICATE KEY UPDATE
    file_url = VALUES(file_url),
    submitted_at = VALUES(submitted_at),
    score = VALUES(score),
    teacher_comment = VALUES(teacher_comment);

INSERT INTO exams (
    class_id, student_id, exam_name, exam_type, exam_date,
    score_listening, score_speaking, score_reading, score_writing, result, teacher_comment
)
VALUES
    (@class_id, 46, 'Kiem tra giua khoa demo', 'periodic', '2026-05-10', 8.5, 8.0, 8.5, 8.0, '8.25', 'Giu phong do on dinh'),
    (@class_id, 131, 'Kiem tra giua khoa demo', 'periodic', '2026-05-10', 9.0, 8.5, 8.5, 8.5, '8.63', 'Noi bat o phan doc'),
    (@class_id, 132, 'Kiem tra giua khoa demo', 'periodic', '2026-05-10', 7.0, 7.5, 7.0, 7.5, '7.25', 'Can luyen them tu vung'),
    (@class_id, 133, 'Kiem tra giua khoa demo', 'periodic', '2026-05-10', 8.0, 7.0, 7.5, 7.0, '7.38', 'Phan noi con thieu tu tin'),
    (@class_id, 134, 'Kiem tra giua khoa demo', 'periodic', '2026-05-10', 7.5, 8.0, 7.5, 8.0, '7.75', 'Tien bo deu o 4 ky nang'),
    (@class_id, 135, 'Kiem tra giua khoa demo', 'periodic', '2026-05-10', 6.5, 7.0, 6.5, 7.0, '6.75', 'Can on ky phan nghe'),
    (@class_id, 136, 'Kiem tra giua khoa demo', 'periodic', '2026-05-10', 8.0, 8.0, 8.0, 7.5, '7.88', 'Kha dong deu'),
    (@class_id, 46, 'Kiem tra cuoi khoa demo', 'final', '2026-05-20', 8.8, 8.6, 8.7, 8.5, '8.65', 'Dat muc tieu cuoi khoa'),
    (@class_id, 131, 'Kiem tra cuoi khoa demo', 'final', '2026-05-20', 9.2, 8.9, 9.1, 8.8, '9.00', 'Ket qua rat tot'),
    (@class_id, 132, 'Kiem tra cuoi khoa demo', 'final', '2026-05-20', 7.4, 7.6, 7.3, 7.8, '7.53', 'Co cai thien so voi giua khoa'),
    (@class_id, 133, 'Kiem tra cuoi khoa demo', 'final', '2026-05-20', 8.1, 7.8, 7.9, 7.7, '7.88', 'On dinh, can tang do chinh xac'),
    (@class_id, 134, 'Kiem tra cuoi khoa demo', 'final', '2026-05-20', 8.0, 8.2, 8.1, 8.0, '8.08', 'Dat chuan mong doi'),
    (@class_id, 135, 'Kiem tra cuoi khoa demo', 'final', '2026-05-20', 6.9, 7.2, 6.8, 7.1, '7.00', 'Can on them de but toc'),
    (@class_id, 136, 'Kiem tra cuoi khoa demo', 'final', '2026-05-20', 8.2, 8.3, 8.1, 8.0, '8.15', 'Hoan thanh tot');
