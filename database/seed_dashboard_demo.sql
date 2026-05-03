USE english_center_db;

DELIMITER $$

DROP PROCEDURE IF EXISTS seed_dashboard_demo $$
CREATE PROCEDURE seed_dashboard_demo()
BEGIN
    DECLARE i INT DEFAULT 1;
    DECLARE j INT DEFAULT 1;
    DECLARE teacherRoleId BIGINT UNSIGNED DEFAULT 0;
    DECLARE studentRoleId BIGINT UNSIGNED DEFAULT 0;
    DECLARE teacherUserId BIGINT UNSIGNED DEFAULT 0;
    DECLARE studentUserId BIGINT UNSIGNED DEFAULT 0;
    DECLARE classId BIGINT UNSIGNED DEFAULT 0;
    DECLARE courseId BIGINT UNSIGNED DEFAULT 0;
    DECLARE tuitionId BIGINT UNSIGNED DEFAULT 0;
    DECLARE enrollCount INT DEFAULT 0;
    DECLARE studentIndex INT DEFAULT 0;
    DECLARE baseAmount DECIMAL(12,2) DEFAULT 0;
    DECLARE discountAmount DECIMAL(12,2) DEFAULT 0;
    DECLARE totalAmount DECIMAL(12,2) DEFAULT 0;
    DECLARE amountPaid DECIMAL(12,2) DEFAULT 0;
    DECLARE createdStamp DATETIME;
    DECLARE classStartDate DATE;
    DECLARE classEndDate DATE;
    DECLARE leadStatus VARCHAR(30);
    DECLARE applicationStatus VARCHAR(30);

    SELECT id INTO teacherRoleId FROM roles WHERE role_name = 'teacher' LIMIT 1;
    SELECT id INTO studentRoleId FROM roles WHERE role_name = 'student' LIMIT 1;

    DELETE pt
    FROM payment_transactions pt
    INNER JOIN tuition_fees tf ON tf.id = pt.tuition_fee_id
    INNER JOIN users u ON u.id = tf.student_id
    WHERE u.username LIKE 'demo_student_%';

    DELETE tf
    FROM tuition_fees tf
    INNER JOIN users u ON u.id = tf.student_id
    WHERE u.username LIKE 'demo_student_%';

    DELETE fb
    FROM feedbacks fb
    INNER JOIN users u ON u.id = fb.sender_id
    WHERE u.username LIKE 'demo_student_%';

    DELETE cs
    FROM class_students cs
    INNER JOIN users u ON u.id = cs.student_id
    WHERE u.username LIKE 'demo_student_%';

    DELETE FROM student_profiles WHERE student_code LIKE 'HVDEMO%';
    DELETE FROM teacher_profiles WHERE teacher_code LIKE 'GVDEMO%';
    DELETE FROM classes WHERE class_name LIKE 'Lop demo %' OR class_name LIKE 'L% demo %';
    DELETE FROM courses WHERE course_name LIKE 'Khoa demo %' OR course_name LIKE 'Kh% demo %';
    DELETE FROM student_leads WHERE student_name LIKE 'Lead demo %';
    DELETE FROM job_applications WHERE full_name LIKE 'Ung vien demo %' OR full_name LIKE 'Ứng viên demo %';
    DELETE FROM users WHERE username LIKE 'demo_student_%' OR username LIKE 'demo_teacher_%';

    SET i = 1;
    WHILE i <= 6 DO
        SET createdStamp = DATE_ADD(DATE_SUB(CURDATE(), INTERVAL (14 - i) MONTH), INTERVAL (8 + (i MOD 4)) HOUR);
        INSERT INTO courses (course_name, description, base_price, total_sessions, image_thumbnail, created_at, updated_at, deleted_at)
        VALUES (
            CONCAT('Khoa demo ', LPAD(i, 2, '0')),
            CONCAT('Khoa hoc mau so ', i, ' de truc quan hoa dashboard.'),
            2200000 + (i * 250000),
            24 + (i * 4),
            NULL,
            createdStamp,
            createdStamp,
            NULL
        );
        SET i = i + 1;
    END WHILE;

    SET i = 1;
    WHILE i <= 10 DO
        SET createdStamp = DATE_ADD(DATE_SUB(CURDATE(), INTERVAL (12 - (i MOD 10)) MONTH), INTERVAL (8 + (i MOD 5)) HOUR);
        INSERT INTO users (username, password, full_name, role_id, phone, email, status, created_at, updated_at, deleted_at)
        VALUES (
            CONCAT('demo_teacher_', LPAD(i, 3, '0')),
            '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/.AyhQYQW0ZxW.',
            CONCAT('Giao vien demo ', LPAD(i, 2, '0')),
            teacherRoleId,
            CONCAT('0907', LPAD(i, 6, '0')),
            CONCAT('demo_teacher_', LPAD(i, 3, '0'), '@example.com'),
            'active',
            createdStamp,
            createdStamp,
            NULL
        );
        SET teacherUserId = LAST_INSERT_ID();
        INSERT INTO teacher_profiles (user_id, teacher_code, degree, experience_years, bio, intro_video_url, created_at, updated_at)
        VALUES (
            teacherUserId,
            CONCAT('GVDEMO', LPAD(i, 3, '0')),
            'Cu nhan Ngon ngu Anh',
            1 + (i MOD 6),
            CONCAT('Giao vien demo ', i, ' chuyen giao tiep va luyen phan xa.'),
            NULL,
            createdStamp,
            createdStamp
        );
        SET i = i + 1;
    END WHILE;

    SET i = 1;
    WHILE i <= 64 DO
        SET createdStamp = DATE_ADD(DATE_SUB(CURDATE(), INTERVAL (14 - (i MOD 12)) MONTH), INTERVAL (9 + (i MOD 6)) HOUR);
        INSERT INTO users (username, password, full_name, role_id, phone, email, status, created_at, updated_at, deleted_at)
        VALUES (
            CONCAT('demo_student_', LPAD(i, 3, '0')),
            '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/.AyhQYQW0ZxW.',
            CONCAT('Hoc vien demo ', LPAD(i, 3, '0')),
            studentRoleId,
            CONCAT('0918', LPAD(i, 6, '0')),
            CONCAT('demo_student_', LPAD(i, 3, '0'), '@example.com'),
            'active',
            createdStamp,
            createdStamp,
            NULL
        );
        SET studentUserId = LAST_INSERT_ID();
        INSERT INTO student_profiles (user_id, student_code, parent_name, parent_phone, school_name, target_score, entry_test_id, created_at, updated_at)
        VALUES (
            studentUserId,
            CONCAT('HVDEMO', LPAD(i, 3, '0')),
            CONCAT('Phu huynh demo ', LPAD(i, 3, '0')),
            CONCAT('0933', LPAD(i, 6, '0')),
            CONCAT('Truong demo ', 1 + (i MOD 9)),
            CASE WHEN MOD(i, 2) = 0 THEN 'IELTS 6.5' ELSE 'Giao tiep tu tin' END,
            NULL,
            createdStamp,
            createdStamp
        );
        SET i = i + 1;
    END WHILE;

    SET i = 1;
    WHILE i <= 18 DO
        SELECT id INTO courseId FROM courses WHERE course_name = CONCAT('Khoa demo ', LPAD(((i - 1) MOD 6) + 1, 2, '0')) LIMIT 1;
        SELECT id INTO teacherUserId FROM users WHERE username = CONCAT('demo_teacher_', LPAD(((i - 1) MOD 10) + 1, 3, '0')) LIMIT 1;
        SET classStartDate = DATE_ADD(DATE_SUB(CURDATE(), INTERVAL (16 - i) MONTH), INTERVAL (i MOD 17) DAY);
        SET classEndDate = DATE_ADD(classStartDate, INTERVAL 90 DAY);

        INSERT INTO classes (course_id, class_name, teacher_id, start_date, end_date, status, created_at, updated_at)
        VALUES (
            courseId,
            CONCAT('Lop demo ', LPAD(i, 2, '0')),
            teacherUserId,
            classStartDate,
            classEndDate,
            CASE MOD(i, 4)
                WHEN 0 THEN 'upcoming'
                WHEN 1 THEN 'active'
                WHEN 2 THEN 'graduated'
                ELSE 'cancelled'
            END,
            DATE_ADD(classStartDate, INTERVAL 8 HOUR),
            DATE_ADD(classStartDate, INTERVAL 8 HOUR)
        );
        SET i = i + 1;
    END WHILE;

    SET i = 1;
    WHILE i <= 18 DO
        SELECT id INTO classId FROM classes WHERE class_name = CONCAT('Lop demo ', LPAD(i, 2, '0')) LIMIT 1;
        SET enrollCount = 6 + MOD(i, 8);
        SET j = 1;

        WHILE j <= enrollCount DO
            SET studentIndex = ((i - 1) * 4) + j;
            WHILE studentIndex > 64 DO
                SET studentIndex = studentIndex - 64;
            END WHILE;

            SELECT id INTO studentUserId FROM users WHERE username = CONCAT('demo_student_', LPAD(studentIndex, 3, '0')) LIMIT 1;
            SET classStartDate = DATE_ADD(DATE_SUB(CURDATE(), INTERVAL (16 - i) MONTH), INTERVAL (j MOD 20) DAY);

            INSERT IGNORE INTO class_students (class_id, student_id, enrollment_date, created_at, updated_at)
            VALUES (
                classId,
                studentUserId,
                classStartDate,
                DATE_ADD(classStartDate, INTERVAL 9 HOUR),
                DATE_ADD(classStartDate, INTERVAL 9 HOUR)
            );

            SET baseAmount = 2200000 + (MOD(i, 5) * 250000);
            SET discountAmount = CASE WHEN MOD(j, 4) = 0 THEN 200000 ELSE 0 END;
            SET totalAmount = baseAmount - discountAmount;
            SET amountPaid = CASE MOD(j, 3)
                WHEN 0 THEN totalAmount
                WHEN 1 THEN ROUND(totalAmount * 0.55, 2)
                ELSE 0
            END;

            INSERT INTO tuition_fees (
                student_id, class_id, package_id, base_amount, discount_type, discount_amount,
                total_amount, amount_paid, payment_plan, status, created_at, updated_at
            ) VALUES (
                studentUserId,
                classId,
                NULL,
                baseAmount,
                CASE WHEN discountAmount > 0 THEN 'demo' ELSE NULL END,
                discountAmount,
                totalAmount,
                amountPaid,
                CASE WHEN MOD(j, 2) = 0 THEN 'monthly' ELSE 'full' END,
                CASE WHEN amountPaid >= totalAmount THEN 'paid' ELSE 'debt' END,
                DATE_ADD(classStartDate, INTERVAL 10 HOUR),
                DATE_ADD(classStartDate, INTERVAL 10 HOUR)
            );
            SET tuitionId = LAST_INSERT_ID();

            IF amountPaid > 0 THEN
                INSERT INTO payment_transactions (tuition_fee_id, payment_method, amount, transaction_status, created_at, updated_at)
                VALUES (
                    tuitionId,
                    CASE WHEN MOD(j, 2) = 0 THEN 'bank_transfer' ELSE 'cash' END,
                    ROUND(amountPaid / CASE WHEN MOD(j, 5) = 0 THEN 2 ELSE 1 END, 2),
                    'success',
                    DATE_ADD(classStartDate, INTERVAL (11 + (j MOD 3)) HOUR),
                    DATE_ADD(classStartDate, INTERVAL (11 + (j MOD 3)) HOUR)
                );

                IF MOD(j, 5) = 0 THEN
                    INSERT INTO payment_transactions (tuition_fee_id, payment_method, amount, transaction_status, created_at, updated_at)
                    VALUES (
                        tuitionId,
                        'bank_transfer',
                        amountPaid - ROUND(amountPaid / 2, 2),
                        'success',
                        DATE_ADD(classStartDate, INTERVAL (14 + (j MOD 4)) HOUR),
                        DATE_ADD(classStartDate, INTERVAL (14 + (j MOD 4)) HOUR)
                    );
                END IF;
            END IF;

            IF MOD(j, 7) = 0 THEN
                INSERT INTO payment_transactions (tuition_fee_id, payment_method, amount, transaction_status, created_at, updated_at)
                VALUES (
                    tuitionId,
                    'bank_transfer',
                    ROUND(totalAmount * 0.2, 2),
                    'failed',
                    DATE_ADD(classStartDate, INTERVAL 16 HOUR),
                    DATE_ADD(classStartDate, INTERVAL 16 HOUR)
                );
            END IF;

            SET j = j + 1;
        END WHILE;

        SET i = i + 1;
    END WHILE;

    SET i = 1;
    WHILE i <= 48 DO
        SELECT id INTO studentUserId FROM users WHERE username = CONCAT('demo_student_', LPAD(((i - 1) MOD 64) + 1, 3, '0')) LIMIT 1;
        SET createdStamp = DATE_ADD(DATE_SUB(CURDATE(), INTERVAL (14 - (i MOD 12)) MONTH), INTERVAL (10 + (i MOD 6)) HOUR);
        INSERT INTO feedbacks (sender_id, rating, content, is_public_web, created_at, updated_at)
        VALUES (
            studentUserId,
            1 + MOD(i, 5),
            CONCAT('Phan hoi demo so ', i, ': trai nghiem hoc tap duoc dung de kiem tra dashboard.'),
            CASE WHEN MOD(i, 3) = 0 THEN 1 ELSE 0 END,
            createdStamp,
            createdStamp
        );
        SET i = i + 1;
    END WHILE;

    SET i = 1;
    WHILE i <= 32 DO
        SET createdStamp = DATE_ADD(DATE_SUB(CURDATE(), INTERVAL (13 - (i MOD 12)) MONTH), INTERVAL (8 + (i MOD 7)) HOUR);
        SET leadStatus = CASE MOD(i, 5)
            WHEN 0 THEN 'new'
            WHEN 1 THEN 'entry_tested'
            WHEN 2 THEN 'trial_completed'
            WHEN 3 THEN 'official'
            ELSE 'cancelled'
        END;
        IF leadStatus IN ('trial_completed', 'official') THEN
            SELECT id INTO studentUserId FROM users WHERE username = CONCAT('demo_student_', LPAD(((i - 1) MOD 20) + 1, 3, '0')) LIMIT 1;
        ELSE
            SET studentUserId = NULL;
        END IF;

        INSERT INTO student_leads (
            student_name, gender, dob, interests, personality, parent_name, parent_phone, school_name,
            current_grade, referral_source, current_level, study_time, parent_expectation, status,
            admin_note, converted_user_id, converted_at, created_at, updated_at
        ) VALUES (
            CONCAT('Lead demo ', LPAD(i, 2, '0')),
            CASE WHEN MOD(i, 2) = 0 THEN 'female' ELSE 'male' END,
            DATE_SUB(CURDATE(), INTERVAL (11 + i) YEAR),
            'Nghe, nói, phản xạ',
            'Năng động',
            CONCAT('Phu huynh lead demo ', LPAD(i, 2, '0')),
            CONCAT('0944', LPAD(i, 6, '0')),
            CONCAT('Truong lead demo ', 1 + MOD(i, 8)),
            CONCAT('Grade ', 3 + MOD(i, 7)),
            CASE MOD(i, 4) WHEN 0 THEN 'Facebook' WHEN 1 THEN 'Zalo' WHEN 2 THEN 'Walk-in' ELSE 'Referral' END,
            CASE WHEN MOD(i, 2) = 0 THEN 'A1' ELSE 'A2' END,
            CASE WHEN MOD(i, 2) = 0 THEN 'Toi' ELSE 'Cuoi tuan' END,
            'Muon theo duoc chuong trinh va giao tiep tot hon',
            leadStatus,
            'Du lieu demo cho bieu do chuyen doi hoc vien',
            studentUserId,
            CASE WHEN studentUserId IS NULL THEN NULL ELSE createdStamp END,
            createdStamp,
            createdStamp
        );
        SET i = i + 1;
    END WHILE;

    SET i = 1;
    WHILE i <= 18 DO
        SET createdStamp = DATE_ADD(DATE_SUB(CURDATE(), INTERVAL (12 - (i MOD 12)) MONTH), INTERVAL (9 + (i MOD 5)) HOUR);
        SET applicationStatus = CASE MOD(i, 4)
            WHEN 0 THEN 'PENDING'
            WHEN 1 THEN 'INTERVIEWING'
            WHEN 2 THEN 'PASSED'
            ELSE 'REJECTED'
        END;

        IF applicationStatus IN ('INTERVIEWING', 'PASSED') AND MOD(i, 2) = 0 THEN
            SELECT id INTO teacherUserId FROM users WHERE username = CONCAT('demo_teacher_', LPAD(((i - 1) MOD 10) + 1, 3, '0')) LIMIT 1;
        ELSE
            SET teacherUserId = NULL;
        END IF;

        INSERT INTO job_applications (
            full_name, email, phone, address, position_applied, work_mode, highest_degree, experience_years,
            education_detail, work_history, skills_set, bio_summary, start_date, salary_expectation, cv_file_url,
            status, hr_note, converted_user_id, converted_at, created_at, updated_at
        ) VALUES (
            CONCAT('Ung vien demo ', LPAD(i, 2, '0')),
            CONCAT('ungvien_demo_', LPAD(i, 2, '0'), '@example.com'),
            CONCAT('0977', LPAD(i, 6, '0')),
            CONCAT('Địa chỉ demo số ', i),
            CASE WHEN MOD(i, 2) = 0 THEN 'IELTS Teacher' ELSE 'Speaking Coach' END,
            CASE WHEN MOD(i, 3) = 0 THEN 'Part-time' ELSE 'Full-time' END,
            'Cu nhan tieng Anh',
            1 + MOD(i, 6),
            'Tot nghiep nganh ngon ngu Anh',
            'Co kinh nghiem tro giang va day lop nho',
            'Teaching, Speaking, Classroom management',
            'Ung vien demo de kiem tra bieu do chuyen doi giao vien',
            DATE_ADD(CURDATE(), INTERVAL (i MOD 30) DAY),
            CONCAT(12 + MOD(i, 5), ' trieu'),
            NULL,
            applicationStatus,
            'Du lieu demo cho dashboard',
            teacherUserId,
            CASE WHEN teacherUserId IS NULL THEN NULL ELSE createdStamp END,
            createdStamp,
            createdStamp
        );
        SET i = i + 1;
    END WHILE;
END $$

CALL seed_dashboard_demo() $$
DROP PROCEDURE seed_dashboard_demo $$

DELIMITER ;
