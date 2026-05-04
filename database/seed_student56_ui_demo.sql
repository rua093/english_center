USE english_center_db;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

START TRANSACTION;

-- Clean up previous demo rows so the script is repeatable.
DELETE pt
FROM payment_transactions pt
INNER JOIN tuition_fees tf ON tf.id = pt.tuition_fee_id
WHERE tf.student_id = 56
  AND tf.class_id = 2;

DELETE FROM submissions
WHERE student_id = 56
    AND assignment_id IN (
            SELECT id FROM assignments WHERE title IN (
                    'Demo Assignment - Student 56',
                    'Demo Assignment - Student 56 Writing'
            )
    );

DELETE FROM tuition_fees
WHERE student_id = 56
  AND class_id = 2;

DELETE FROM class_students
WHERE student_id = 56
  AND class_id = 2;

DELETE FROM assignments
WHERE title IN (
    'Demo Assignment - Student 56',
    'Demo Assignment - Student 56 Writing'
);

DELETE l
FROM lessons l
INNER JOIN schedules s ON s.id = l.schedule_id
WHERE s.class_id = 2
    AND s.study_date = '2026-05-08';

DELETE FROM schedules
WHERE class_id = 2
  AND study_date = '2026-05-08';

DELETE FROM student_profiles
WHERE user_id = 56;

-- Student profile so the UI can show the student code and related metadata.
INSERT INTO student_profiles (
    user_id,
    student_code,
    parent_name,
    parent_phone,
    school_name,
    target_score,
    entry_test_id
) VALUES (
    56,
    'HV00056',
    'Pham Van 56',
    '0905565566',
    'Truong THCS Demo 56',
    'IELTS 6.0',
    NULL
);

-- Enroll student 56 into class 2 so class/assignment UI can resolve the relationship.
INSERT INTO class_students (
    class_id,
    student_id,
    enrollment_date
) VALUES (
    2,
    56,
    '2026-05-03'
);

-- Create a schedule for class 2 so assignments can attach to it.
INSERT INTO schedules (
    class_id,
    room_id,
    teacher_id,
    study_date,
    start_time,
    end_time
) VALUES (
    2,
    1,
    45,
    '2026-05-08',
    '18:00:00',
    '20:00:00'
);
SET @demo_schedule_id := LAST_INSERT_ID();

-- Lesson content for hover details on the student timetable.
INSERT INTO lessons (
    class_id,
    actual_title,
    actual_content,
    attachment_file_path,
    schedule_id
) VALUES (
    2,
    'Demo Lesson - Student 56 Speaking Practice',
    'On tap chu de Travel, tu vung theo tinh huong va file tai lieu dung de xem nhanh khi hover lich hoc.',
    '/assets/uploads/lessons/demo-student56-speaking-practice.pdf',
    @demo_schedule_id
);

-- Create a couple of assignments for the new schedule.
INSERT INTO assignments (
    schedule_id,
    title,
    description,
    deadline,
    file_url
) VALUES
(
    @demo_schedule_id,
    'Demo Assignment - Student 56',
    'Bai tap mau de hien thi danh sach bai tap tren UI cho hoc vien 56.',
    '2026-05-12 23:59:00',
    '/assets/uploads/assignment-demo-student56.pdf'
),
(
    @demo_schedule_id,
    'Demo Assignment - Student 56 Writing',
    'Bai tap viet mau voi noi dung ngan de xem UI render trang thai bai tap.',
    '2026-05-15 23:59:00',
    '/assets/uploads/assignment-demo-student56-writing.docx'
);
SET @demo_assignment_id_1 := (
        SELECT id
        FROM assignments
        WHERE title = 'Demo Assignment - Student 56'
            AND schedule_id = @demo_schedule_id
        ORDER BY id ASC
        LIMIT 1
);

-- Seed one graded submission so the class detail page can show teacher comments and a file link.
INSERT INTO submissions (
    assignment_id,
    student_id,
    file_url,
    submitted_at,
    score,
    teacher_comment
) VALUES (
    @demo_assignment_id_1,
    56,
    '/assets/uploads/submissions/student56-speaking-answer.docx',
    '2026-05-09 21:05:00',
    8.5,
    'Bai lam tot, can them vi du va giu nhip noi tu nhien.'
);

-- Create tuition fee record for class 2.
INSERT INTO tuition_fees (
    student_id,
    class_id,
    package_id,
    base_amount,
    discount_type,
    discount_amount,
    total_amount,
    amount_paid,
    payment_plan,
    status
) VALUES (
    56,
    2,
    NULL,
    450000.00,
    NULL,
    0.00,
    450000.00,
    150000.00,
    'monthly',
    'debt'
);
SET @demo_tuition_id := LAST_INSERT_ID();

-- Add one payment transaction so the tuition UI has a payment history row.
INSERT INTO payment_transactions (
    tuition_fee_id,
    payment_method,
    amount,
    transaction_status
) VALUES (
    @demo_tuition_id,
    'cash',
    150000.00,
    'success'
);

COMMIT;

SET FOREIGN_KEY_CHECKS = 1;
