-- ==========================================================
-- Migration: Create FAQs Table
-- Project: Nhuệ Minh English Center
-- File: database/migrate_faqs.sql
-- ==========================================================

CREATE TABLE IF NOT EXISTS `faqs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category` VARCHAR(100) NOT NULL DEFAULT 'Hỏi đáp chung',
    `question` VARCHAR(500) NOT NULL,
    `answer` TEXT NOT NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_faq_category` (`category`),
    INDEX `idx_faq_active` (`is_active`),
    INDEX `idx_faq_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
