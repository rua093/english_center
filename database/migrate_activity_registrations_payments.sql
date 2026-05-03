-- Add payment tracking fields to activity_registrations.
SET @has_activity_registrations := (
    SELECT COUNT(*)
    FROM information_schema.tables
    WHERE table_schema = DATABASE()
      AND table_name = 'activity_registrations'
);

SET @has_activity_reg_amount_paid := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'activity_registrations'
      AND column_name = 'amount_paid'
);

SET @has_activity_reg_payment_date := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'activity_registrations'
      AND column_name = 'payment_date'
);

SET @sql := IF(
    @has_activity_registrations = 1 AND @has_activity_reg_amount_paid = 0,
    "ALTER TABLE activity_registrations ADD COLUMN amount_paid DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER payment_status",
    "SELECT 'Skip: activity_registrations.amount_paid exists or table missing' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    @has_activity_registrations = 1 AND @has_activity_reg_payment_date = 0,
    "ALTER TABLE activity_registrations ADD COLUMN payment_date DATETIME NULL AFTER amount_paid",
    "SELECT 'Skip: activity_registrations.payment_date exists or table missing' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    @has_activity_registrations = 1,
    "UPDATE activity_registrations ar INNER JOIN extracurricular_activities a ON a.id = ar.activity_id SET ar.amount_paid = CASE WHEN ar.payment_status = 'paid' AND ar.amount_paid = 0 THEN COALESCE(a.fee, 0) ELSE ar.amount_paid END, ar.payment_date = CASE WHEN ar.payment_date IS NULL AND ar.payment_status = 'paid' THEN ar.registration_date ELSE ar.payment_date END",
    "SELECT 'Skip: activity_registrations backfill not required' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
