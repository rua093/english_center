USE english_center_db;

SET @has_promotions_quantity_limit := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'promotions'
      AND column_name = 'quantity_limit'
);
SET @sql := IF(
    @has_promotions_quantity_limit = 0,
    "ALTER TABLE promotions ADD COLUMN quantity_limit INT UNSIGNED DEFAULT NULL AFTER end_date",
    "SELECT 'Skip: promotions.quantity_limit already exists' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_promotions_quantity_remaining := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'promotions'
      AND column_name = 'quantity_remaining'
);
SET @sql := IF(
    @has_promotions_quantity_remaining = 0,
    "ALTER TABLE promotions ADD COLUMN quantity_remaining INT UNSIGNED DEFAULT NULL AFTER quantity_limit",
    "SELECT 'Skip: promotions.quantity_remaining already exists' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := "UPDATE promotions SET quantity_remaining = quantity_limit WHERE quantity_limit IS NOT NULL AND quantity_remaining IS NULL";
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_tuition_monthly_months := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'tuition_fees'
      AND column_name = 'monthly_months'
);
SET @sql := IF(
    @has_tuition_monthly_months = 0,
    "ALTER TABLE tuition_fees ADD COLUMN monthly_months INT UNSIGNED DEFAULT NULL AFTER payment_plan",
    "SELECT 'Skip: tuition_fees.monthly_months already exists' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_tuition_monthly_start := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'tuition_fees'
      AND column_name = 'monthly_start_month'
);
SET @sql := IF(
    @has_tuition_monthly_start = 0,
    "ALTER TABLE tuition_fees ADD COLUMN monthly_start_month DATE DEFAULT NULL AFTER monthly_months",
    "SELECT 'Skip: tuition_fees.monthly_start_month already exists' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_tuition_monthly_end := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'tuition_fees'
      AND column_name = 'monthly_end_month'
);
SET @sql := IF(
    @has_tuition_monthly_end = 0,
    "ALTER TABLE tuition_fees ADD COLUMN monthly_end_month DATE DEFAULT NULL AFTER monthly_start_month",
    "SELECT 'Skip: tuition_fees.monthly_end_month already exists' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_tuition_monthly_day := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'tuition_fees'
      AND column_name = 'monthly_payment_day'
);
SET @sql := IF(
    @has_tuition_monthly_day = 0,
    "ALTER TABLE tuition_fees ADD COLUMN monthly_payment_day TINYINT UNSIGNED DEFAULT NULL AFTER monthly_end_month",
    "SELECT 'Skip: tuition_fees.monthly_payment_day already exists' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
