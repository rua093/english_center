USE english_center_db;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

START TRANSACTION;

-- Repeatable demo cleanup.
DELETE FROM submissions
WHERE assignment_id IN (
    SELECT id FROM (
        SELECT a.id
        FROM assignments a
        INNER JOIN schedules s ON s.id = a.schedule_id
        WHERE s.class_id = 2
          AND s.study_date = '2026-05-08'
          AND a.title = 'Demo Assignment - Student 56 Overdue'
    ) AS x
);

DELETE FROM assignments
WHERE id IN (
    SELECT id FROM (
        SELECT a.id
        FROM assignments a
        INNER JOIN schedules s ON s.id = a.schedule_id
        WHERE s.class_id = 2
          AND s.study_date = '2026-05-08'
          AND a.title = 'Demo Assignment - Student 56 Overdue'
    ) AS x
);

-- Ensure the demo schedule exists. Reuse the existing one if present, otherwise create it.
SET @demo_schedule_id := (
    SELECT s.id
    FROM schedules s
    WHERE s.class_id = 2
      AND s.study_date = '2026-05-08'
    ORDER BY s.id DESC
    LIMIT 1
);

INSERT INTO schedules (
    class_id,
    room_id,
    teacher_id,
    study_date,
    start_time,
    end_time
)
SELECT
    2,
    1,
    45,
    '2026-05-08',
    '18:00:00',
    '20:00:00'
WHERE @demo_schedule_id IS NULL;

SET @demo_schedule_id := COALESCE(@demo_schedule_id, LAST_INSERT_ID());

-- Create an overdue assignment with no submission.
INSERT INTO assignments (
    schedule_id,
    title,
    description,
    deadline,
    file_url
) VALUES (
    @demo_schedule_id,
    'Demo Assignment - Student 56 Overdue',
    'Bài tập mẫu đã quá hạn để kiểm tra trạng thái nút nộp bị vô hiệu hóa.',
    DATE_SUB(NOW(), INTERVAL 3 DAY),
    '/assets/uploads/assignment-demo-student56-overdue.pdf'
);

COMMIT;

SET FOREIGN_KEY_CHECKS = 1;
