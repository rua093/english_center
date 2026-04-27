USE english_center_db;

-- Test data for student assignment flow
-- Default seeded student: user_id = 4 (Nguyen Van Student)
-- Default seeded class: class_id = 1 (IELTS-K20-Toi-2-4)
-- Adjust the IDs below if your local database uses different values.

SET @student_id := 4;
SET @class_id := 1;

-- Create sample assignments on existing schedules so the student dashboard can show real data.
INSERT INTO assignments (schedule_id, title, description, deadline, file_url)
VALUES
(
    (SELECT s.id FROM schedules s WHERE s.class_id = @class_id AND s.study_date = '2026-04-14' ORDER BY s.id ASC LIMIT 1),
    'Writing Task 1 - Line Graph',
    'Viet doan mo ta bieu do duong 180-220 tu.',
    '2026-04-15 23:59:00',
    '/assets/uploads/assignment-writing-task-1.pdf'
),
(
    (SELECT s.id FROM schedules s WHERE s.class_id = @class_id AND s.study_date = '2026-04-16' ORDER BY s.id ASC LIMIT 1),
    'Reading Practice Test 1',
    'Lam bai doc practice test de luyen toc do va tu vung.',
    '2026-04-25 23:59:00',
    '/assets/uploads/assignment-reading-practice-1.pdf'
),
(
    (SELECT s.id FROM schedules s WHERE s.class_id = @class_id ORDER BY s.id DESC LIMIT 1),
    'Speaking Part 2 Recording',
    'Ghi am phan noi Part 2 trong 2 phut va nop file audio.',
    '2026-05-10 23:59:00',
    '/assets/uploads/assignment-speaking-part-2.pdf'
);

-- A submitted assignment with score so the UI can show "Đã chấm".
INSERT INTO submissions (assignment_id, student_id, file_url, submitted_at, score, teacher_comment)
VALUES
(
    (SELECT a.id FROM assignments a WHERE a.title = 'Writing Task 1 - Line Graph' AND a.schedule_id IN (SELECT s.id FROM schedules s WHERE s.class_id = @class_id) ORDER BY a.id DESC LIMIT 1),
    @student_id,
    '/assets/uploads/submission-writing-task-1.docx',
    '2026-04-12 20:30:00',
    7.5,
    'Bai viet on, can sua mot so cau phuc va tu noi chu de.'
);

-- A submitted assignment without score so the UI can show "Chờ chấm".
INSERT INTO submissions (assignment_id, student_id, file_url, submitted_at, score, teacher_comment)
VALUES
(
    (SELECT a.id FROM assignments a WHERE a.title = 'Reading Practice Test 1' AND a.schedule_id IN (SELECT s.id FROM schedules s WHERE s.class_id = @class_id) ORDER BY a.id DESC LIMIT 1),
    @student_id,
    '/assets/uploads/submission-reading-practice-1.pdf',
    '2026-04-20 21:10:00',
    NULL,
    NULL
);

-- Optional exam rows so the detail page has test data too.
INSERT INTO exams (class_id, student_id, exam_name, exam_type, exam_date, score_listening, score_speaking, score_reading, score_writing, result, teacher_comment, level_suggested)
VALUES
(
    @class_id,
    @student_id,
    'Kiểm tra Đầu vào',
    'entry',
    '2026-04-01',
    5.0,
    4.5,
    5.5,
    5.0,
    '5.0',
    'Can tang cuong speaking va vocab.',
    'IELTS Foundation'
),
(
    @class_id,
    @student_id,
    'Kiểm tra Giữa kỳ',
    'periodic',
    '2026-05-15',
    6.0,
    5.5,
    6.5,
    6.0,
    '6.0',
    'Tien bo tot, can on tap them writing.',
    'IELTS 6.0+'
),
(
    @class_id,
    @student_id,
    'Thi thử Định kỳ (Mock Test)',
    'periodic',
    '2026-06-10',
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL
);