USE english_center_db;

ALTER TABLE notifications
    ADD COLUMN IF NOT EXISTS action_url VARCHAR(500) NULL AFTER message;
