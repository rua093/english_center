-- =======================================================
-- Migration: Create tables for Media Gallery & Topics
-- File: database/migrate_media_gallery.sql
-- =======================================================
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `media_categories` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(150) NOT NULL UNIQUE,
  `description` TEXT DEFAULT NULL,
  `display_order` INT DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `media_items` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `media_type` ENUM('image', 'video', 'youtube') NOT NULL DEFAULT 'image',
  `file_path_or_url` TEXT NOT NULL,
  `thumbnail_url` VARCHAR(500) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `is_featured` TINYINT(1) DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_by` BIGINT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_media_items_category` (`category_id`),
  KEY `idx_media_items_type` (`media_type`),
  KEY `idx_media_items_featured` (`is_featured`),
  CONSTRAINT `fk_media_items_category` FOREIGN KEY (`category_id`) REFERENCES `media_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
