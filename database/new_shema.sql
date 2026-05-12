-- MySQL dump 10.13  Distrib 8.0.36, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: english_center_db
-- ------------------------------------------------------
-- Server version	8.0.45

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `activity_registrations`
--

DROP TABLE IF EXISTS `activity_registrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_registrations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `activity_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `payment_status` enum('paid','unpaid') NOT NULL DEFAULT 'unpaid',
  `amount_paid` decimal(12,2) NOT NULL DEFAULT '0.00',
  `payment_date` datetime DEFAULT NULL,
  `registration_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_activity_reg_activity` (`activity_id`),
  KEY `fk_activity_reg_user` (`user_id`),
  CONSTRAINT `fk_activity_reg_activity` FOREIGN KEY (`activity_id`) REFERENCES `extracurricular_activities` (`id`),
  CONSTRAINT `fk_activity_reg_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_registrations`
--

LOCK TABLES `activity_registrations` WRITE;
/*!40000 ALTER TABLE `activity_registrations` DISABLE KEYS */;
/*!40000 ALTER TABLE `activity_registrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `approvals`
--

DROP TABLE IF EXISTS `approvals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `approvals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `requester_id` bigint unsigned NOT NULL,
  `approver_id` bigint unsigned DEFAULT NULL,
  `type` enum('tuition_discount','tuition_delete','finance_adjust','teacher_leave','schedule_change') NOT NULL,
  `content` text NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_approvals_requester` (`requester_id`),
  KEY `fk_approvals_approver` (`approver_id`),
  CONSTRAINT `fk_approvals_approver` FOREIGN KEY (`approver_id`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_approvals_requester` FOREIGN KEY (`requester_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `approvals`
--

LOCK TABLES `approvals` WRITE;
/*!40000 ALTER TABLE `approvals` DISABLE KEYS */;
/*!40000 ALTER TABLE `approvals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `assignments`
--

DROP TABLE IF EXISTS `assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assignments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `schedule_id` bigint unsigned NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text,
  `deadline` datetime NOT NULL,
  `file_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_assignments_schedule` (`schedule_id`),
  CONSTRAINT `fk_assignments_schedule` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assignments`
--

LOCK TABLES `assignments` WRITE;
/*!40000 ALTER TABLE `assignments` DISABLE KEYS */;
INSERT INTO `assignments` VALUES (2,2,'Bài tập - cười mỉm','cười đẹp','2026-05-09 16:52:00','/assets/uploads/assignment-1777542567-db_english_center2.docx','2026-04-30 09:49:27','2026-04-30 09:55:18');
/*!40000 ALTER TABLE `assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attendance`
--

DROP TABLE IF EXISTS `attendance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attendance` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `schedule_id` bigint unsigned NOT NULL,
  `student_id` bigint unsigned NOT NULL,
  `status` enum('present','absent','late') NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_attendance_student_schedule` (`schedule_id`,`student_id`),
  KEY `fk_attendance_student` (`student_id`),
  CONSTRAINT `fk_attendance_schedule` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`),
  CONSTRAINT `fk_attendance_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance`
--

LOCK TABLES `attendance` WRITE;
/*!40000 ALTER TABLE `attendance` DISABLE KEYS */;
INSERT INTO `attendance` VALUES (1,2,46,'present',NULL,'2026-04-30 09:47:44','2026-04-30 09:47:44');
/*!40000 ALTER TABLE `attendance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `class_students`
--

DROP TABLE IF EXISTS `class_students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `class_students` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `class_id` bigint unsigned NOT NULL,
  `student_id` bigint unsigned NOT NULL,
  `enrollment_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_class_student` (`class_id`,`student_id`),
  KEY `fk_class_students_student` (`student_id`),
  CONSTRAINT `fk_class_students_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`),
  CONSTRAINT `fk_class_students_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `class_students`
--

LOCK TABLES `class_students` WRITE;
/*!40000 ALTER TABLE `class_students` DISABLE KEYS */;
INSERT INTO `class_students` VALUES (1,1,46,'2026-04-30','2026-04-30 09:45:58','2026-04-30 09:45:58'),(2,1,48,'2026-05-03','2026-05-03 09:00:00','2026-05-03 09:00:00');
/*!40000 ALTER TABLE `class_students` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `classes`
--

DROP TABLE IF EXISTS `classes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `classes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `course_id` bigint unsigned NOT NULL,
  `class_name` varchar(150) NOT NULL,
  `teacher_id` bigint unsigned NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('upcoming','active','graduated','cancelled') NOT NULL DEFAULT 'upcoming',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_classes_course` (`course_id`),
  KEY `fk_classes_teacher_user` (`teacher_id`),
  CONSTRAINT `fk_classes_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`),
  CONSTRAINT `fk_classes_teacher_user` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `classes`
--

LOCK TABLES `classes` WRITE;
/*!40000 ALTER TABLE `classes` DISABLE KEYS */;
INSERT INTO `classes` VALUES (1,1,'smile',45,'2026-04-21','2026-05-27','active','2026-04-30 09:41:27','2026-04-30 09:41:27');
/*!40000 ALTER TABLE `classes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `course_roadmaps`
--

DROP TABLE IF EXISTS `course_roadmaps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `course_roadmaps` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `course_id` bigint unsigned NOT NULL,
  `order` int NOT NULL,
  `topic_title` varchar(200) NOT NULL,
  `outline_content` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_roadmap_course` (`course_id`),
  CONSTRAINT `fk_roadmap_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `course_roadmaps`
--

LOCK TABLES `course_roadmaps` WRITE;
/*!40000 ALTER TABLE `course_roadmaps` DISABLE KEYS */;
/*!40000 ALTER TABLE `course_roadmaps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `courses`
--

DROP TABLE IF EXISTS `courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `courses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `course_name` varchar(180) NOT NULL,
  `description` text,
  `base_price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_sessions` int NOT NULL DEFAULT '0',
  `image_thumbnail` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_courses_deleted_at` (`deleted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `courses`
--

LOCK TABLES `courses` WRITE;
/*!40000 ALTER TABLE `courses` DISABLE KEYS */;
INSERT INTO `courses` VALUES (1,'hehe','hehe',300000.00,50,'/assets/uploads/course_thumb-1777541983-202797319_561637371508410_6889235054617734369_n.jpg','2026-04-30 09:39:43','2026-04-30 09:39:43',NULL);
/*!40000 ALTER TABLE `courses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exams`
--

DROP TABLE IF EXISTS `exams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `exams` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `class_id` bigint unsigned DEFAULT NULL,
  `student_id` bigint unsigned NOT NULL,
  `exam_name` varchar(150) NOT NULL,
  `exam_type` enum('entry','periodic','final') NOT NULL,
  `exam_date` date NOT NULL,
  `score_listening` decimal(5,2) DEFAULT NULL,
  `score_speaking` decimal(5,2) DEFAULT NULL,
  `score_reading` decimal(5,2) DEFAULT NULL,
  `score_writing` decimal(5,2) DEFAULT NULL,
  `result` varchar(50) DEFAULT NULL,
  `teacher_comment` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_exams_class` (`class_id`),
  KEY `fk_exams_student` (`student_id`),
  CONSTRAINT `fk_exams_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`),
  CONSTRAINT `fk_exams_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exams`
--

LOCK TABLES `exams` WRITE;
/*!40000 ALTER TABLE `exams` DISABLE KEYS */;
/*!40000 ALTER TABLE `exams` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `extracurricular_activities`
--

DROP TABLE IF EXISTS `extracurricular_activities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `extracurricular_activities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(180) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `content` text,
  `location` varchar(180) DEFAULT NULL,
  `image_thumbnail` varchar(255) DEFAULT NULL,
  `fee` decimal(12,2) NOT NULL DEFAULT '0.00',
  `start_date` date DEFAULT NULL,
  `status` enum('upcoming','ongoing','finished') NOT NULL DEFAULT 'upcoming',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_extracurricular_activities_deleted_at` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `extracurricular_activities`
--

LOCK TABLES `extracurricular_activities` WRITE;
/*!40000 ALTER TABLE `extracurricular_activities` DISABLE KEYS */;
/*!40000 ALTER TABLE `extracurricular_activities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `feedbacks`
--

DROP TABLE IF EXISTS `feedbacks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `feedbacks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sender_id` bigint unsigned NOT NULL,
  `rating` tinyint unsigned NOT NULL,
  `content` text,
  `is_public_web` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_feedbacks_sender` (`sender_id`),
  CONSTRAINT `fk_feedbacks_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `feedbacks`
--

LOCK TABLES `feedbacks` WRITE;
/*!40000 ALTER TABLE `feedbacks` DISABLE KEYS */;
/*!40000 ALTER TABLE `feedbacks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_applications`
--

DROP TABLE IF EXISTS `job_applications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_applications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `position_applied` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `work_mode` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `highest_degree` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `experience_years` int DEFAULT NULL,
  `education_detail` text COLLATE utf8mb4_unicode_ci,
  `work_history` text COLLATE utf8mb4_unicode_ci,
  `skills_set` text COLLATE utf8mb4_unicode_ci,
  `bio_summary` text COLLATE utf8mb4_unicode_ci,
  `start_date` date DEFAULT NULL,
  `salary_expectation` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cv_file_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('PENDING','INTERVIEWING','PASSED','REJECTED') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDING',
  `hr_note` text COLLATE utf8mb4_unicode_ci,
  `converted_user_id` bigint unsigned DEFAULT NULL,
  `converted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ux_job_applications_email` (`email`),
  KEY `idx_converted_user_id` (`converted_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_applications`
--

LOCK TABLES `job_applications` WRITE;
/*!40000 ALTER TABLE `job_applications` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_applications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lessons`
--

DROP TABLE IF EXISTS `lessons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lessons` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `class_id` bigint unsigned NOT NULL,
  `roadmap_id` bigint unsigned DEFAULT NULL,
  `actual_title` varchar(200) NOT NULL,
  `actual_content` text,
  `attachment_file_path` varchar(255) DEFAULT NULL,
  `schedule_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_lessons_roadmap` (`roadmap_id`),
  KEY `idx_lessons_class_schedule` (`class_id`,`schedule_id`),
  KEY `fk_lessons_schedule` (`schedule_id`),
  CONSTRAINT `fk_lessons_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`),
  CONSTRAINT `fk_lessons_roadmap` FOREIGN KEY (`roadmap_id`) REFERENCES `course_roadmaps` (`id`),
  CONSTRAINT `fk_lessons_schedule` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lessons`
--

LOCK TABLES `lessons` WRITE;
/*!40000 ALTER TABLE `lessons` DISABLE KEYS */;
INSERT INTO `lessons` VALUES (1,1,NULL,'Học cách cười','cười nhiều lên',NULL,2,'2026-04-30 09:47:18','2026-04-30 09:47:33'),(2,1,NULL,'Học cười to','cười sảng khoái','/assets/uploads/lessons/lesson_attachment-1777545805-db_english_center2.docx',NULL,'2026-04-30 10:43:25','2026-04-30 10:43:25');
/*!40000 ALTER TABLE `lessons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `materials`
--

DROP TABLE IF EXISTS `materials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `materials` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(180) NOT NULL,
  `description` text,
  `file_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `materials`
--

LOCK TABLES `materials` WRITE;
/*!40000 ALTER TABLE `materials` DISABLE KEYS */;
/*!40000 ALTER TABLE `materials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `title` varchar(180) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_notifications_user` (`user_id`),
  CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (1,46,'bts','trung love bts',0,'2026-04-30 10:09:10','2026-04-30 10:09:10');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_transactions`
--

DROP TABLE IF EXISTS `payment_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tuition_fee_id` bigint unsigned NOT NULL,
  `payment_method` varchar(80) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `transaction_status` enum('success','failed','pending') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_payment_tx_tuition` (`tuition_fee_id`),
  CONSTRAINT `fk_payment_tx_tuition` FOREIGN KEY (`tuition_fee_id`) REFERENCES `tuition_fees` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_transactions`
--

LOCK TABLES `payment_transactions` WRITE;
/*!40000 ALTER TABLE `payment_transactions` DISABLE KEYS */;
INSERT INTO `payment_transactions` VALUES (1,1,'bank_transfer',300000.00,'success','2026-04-30 10:07:41','2026-04-30 10:07:41');
/*!40000 ALTER TABLE `payment_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `permission_name` varchar(120) NOT NULL,
  `slug` varchar(120) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=203 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (4,'Nop bai tap tu portal','student.assignment.submit'),(5,'Cap nhat hoc phi tu portal','student.tuition.update'),(9,'Cham diem bai nop','academic.submissions.grade'),(11,'Xem dashboard quan tri','admin.dashboard.view'),(19,'Xem lop hoc','academic.classes.view'),(20,'Tao lop hoc','academic.classes.create'),(21,'Cap nhat lop hoc','academic.classes.update'),(22,'Xoa lop hoc','academic.classes.delete'),(23,'Xem lich hoc','academic.schedules.view'),(24,'Tao lich hoc','academic.schedules.create'),(25,'Cap nhat lich hoc','academic.schedules.update'),(26,'Xoa lich hoc','academic.schedules.delete'),(27,'Xem bai tap','academic.assignments.view'),(28,'Tao bai tap','academic.assignments.create'),(29,'Cap nhat bai tap','academic.assignments.update'),(30,'Xoa bai tap','academic.assignments.delete'),(31,'Xem bai nop','academic.submissions.view'),(32,'Xem tai lieu','materials.view'),(33,'Tao tai lieu','materials.create'),(34,'Cap nhat tai lieu','materials.update'),(35,'Xoa tai lieu','materials.delete'),(36,'Xem hoc phi','finance.tuition.view'),(38,'Xem danh gia','feedback.view'),(40,'Cap nhat danh gia','feedback.update'),(41,'Xoa danh gia','feedback.delete'),(42,'Xem phe duyet','approval.view'),(43,'Cap nhat phe duyet','approval.update'),(44,'Xem hoat dong','activity.view'),(45,'Tao hoat dong','activity.create'),(46,'Cap nhat hoat dong','activity.update'),(47,'Xoa hoat dong','activity.delete'),(48,'Xem tai khoan ngan hang','bank.view'),(49,'Tao tai khoan ngan hang','bank.create'),(50,'Cap nhat tai khoan ngan hang','bank.update'),(51,'Xoa tai khoan ngan hang','bank.delete'),(58,'Xoa hoc phi','finance.tuition.delete'),(59,'Yêu cầu chỉnh sửa tài chính','finance.adjust.request'),(60,'Tạo yêu cầu phê duyệt','approval.request'),(62,'Xem khoa hoc','academic.courses.view'),(63,'Tao khoa hoc','academic.courses.create'),(64,'Cap nhat khoa hoc','academic.courses.update'),(65,'Xoa khoa hoc','academic.courses.delete'),(66,'Xem roadmap khoa hoc','academic.roadmaps.view'),(67,'Tao roadmap khoa hoc','academic.roadmaps.create'),(68,'Cap nhat roadmap khoa hoc','academic.roadmaps.update'),(69,'Xoa roadmap khoa hoc','academic.roadmaps.delete'),(157,'Cap nhat phan quyen vai tro','admin.role_permission.update'),(158,'Xem phan quyen vai tro','admin.role_permission.view'),(159,'Tao nguoi dung','admin.user.create'),(160,'Xoa nguoi dung','admin.user.delete'),(161,'Cap nhat nguoi dung','admin.user.update'),(162,'Xem nguoi dung','admin.user.view'),(163,'Tao phe duyet','approval.create'),(164,'Xoa phe duyet','approval.delete'),(165,'Tao giao dich thanh toan','finance.payments.create'),(166,'Xoa giao dich thanh toan','finance.payments.delete'),(167,'Cap nhat giao dich thanh toan','finance.payments.update'),(168,'Xem giao dich thanh toan chi tiet','finance.payments.view'),(169,'Tao khuyen mai','finance.promotions.create'),(170,'Xoa khuyen mai','finance.promotions.delete'),(171,'Cap nhat khuyen mai','finance.promotions.update'),(172,'Xem khuyen mai','finance.promotions.view'),(173,'Tao hoc phi','finance.tuition.create'),(174,'Cap nhat hoc phi','finance.tuition.update'),(176,'Xoa ho so ung tuyen giao vien','job_application.delete'),(177,'Cap nhat ho so ung tuyen giao vien','job_application.update'),(178,'Xem ho so ung tuyen giao vien','job_application.view'),(180,'Xoa dau moi hoc vien','student_lead.delete'),(181,'Cap nhat dau moi hoc vien','student_lead.update'),(182,'Xem dau moi hoc vien','student_lead.view'),(183,'Xem phòng học','academic.rooms.view'),(184,'Tạo phòng học','academic.rooms.create'),(185,'Cập nhật phòng học','academic.rooms.update'),(186,'Xóa phòng học','academic.rooms.delete'),(187,'Xem thông báo','notifications.view'),(188,'Tạo thông báo','notifications.create'),(189,'Cập nhật thông báo','notifications.update'),(190,'Xóa thông báo','notifications.delete');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `promotions`
--

DROP TABLE IF EXISTS `promotions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `promotions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `course_id` bigint unsigned DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `promo_type` enum('DURATION','SOCIAL','EVENT','GROUP') NOT NULL,
  `discount_value` decimal(5,2) NOT NULL DEFAULT '0.00',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_promotions_scope_dates` (`course_id`,`start_date`,`end_date`),
  KEY `idx_promotions_promo_type` (`promo_type`),
  KEY `idx_promotions_deleted_at` (`deleted_at`),
  CONSTRAINT `fk_packages_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `promotions`
--

LOCK TABLES `promotions` WRITE;
/*!40000 ALTER TABLE `promotions` DISABLE KEYS */;
/*!40000 ALTER TABLE `promotions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_permissions`
--

DROP TABLE IF EXISTS `role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_permissions` (
  `role_id` bigint unsigned NOT NULL,
  `permission_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `fk_role_permissions_permission` (`permission_id`),
  CONSTRAINT `fk_role_permissions_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`),
  CONSTRAINT `fk_role_permissions_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_permissions`
--

LOCK TABLES `role_permissions` WRITE;
/*!40000 ALTER TABLE `role_permissions` DISABLE KEYS */;
INSERT INTO `role_permissions` VALUES (1,4),(4,4),(1,5),(1,9),(3,9),(1,11),(2,11),(3,11),(1,19),(2,19),(1,20),(2,20),(1,21),(2,21),(1,22),(1,23),(2,23),(3,23),(1,24),(2,24),(1,25),(2,25),(1,26),(1,27),(2,27),(3,27),(1,28),(2,28),(3,28),(1,29),(2,29),(3,29),(1,30),(1,31),(2,31),(3,31),(1,32),(2,32),(3,32),(4,32),(1,33),(2,33),(3,33),(1,34),(2,34),(3,34),(1,35),(1,36),(2,36),(1,38),(2,38),(1,40),(2,40),(1,41),(1,42),(2,42),(1,43),(2,43),(1,44),(2,44),(4,44),(1,45),(2,45),(1,46),(2,46),(1,47),(1,48),(1,49),(1,50),(1,51),(1,58),(1,59),(2,59),(1,60),(2,60),(3,60),(1,62),(2,62),(1,63),(2,63),(1,64),(2,64),(1,65),(1,66),(2,66),(1,67),(2,67),(1,68),(2,68),(1,69),(1,157),(1,158),(1,159),(1,160),(1,161),(1,162),(2,162),(1,163),(1,164),(1,165),(2,165),(1,166),(1,167),(2,167),(1,168),(2,168),(1,169),(1,170),(1,171),(1,172),(2,172),(1,173),(2,173),(1,174),(2,174),(1,176),(1,177),(2,177),(1,178),(2,178),(1,180),(1,181),(2,181),(1,182),(2,182),(1,183),(2,183),(1,184),(2,184),(1,185),(2,185),(1,186),(1,187),(2,187),(1,188),(2,188),(1,189),(2,189),(1,190);
/*!40000 ALTER TABLE `role_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `role_name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_name` (`role_name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'admin','Quan tri he thong','2026-04-29 17:20:48','2026-04-29 17:20:48'),(2,'staff','Giao vu va tu van','2026-04-29 17:20:48','2026-04-29 17:20:48'),(3,'teacher','Giao vien','2026-04-29 17:20:48','2026-04-29 17:20:48'),(4,'student','Hoc vien','2026-04-29 17:20:48','2026-04-29 17:20:48');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rooms`
--

DROP TABLE IF EXISTS `rooms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rooms` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `room_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_rooms_deleted_at` (`deleted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rooms`
--

LOCK TABLES `rooms` WRITE;
/*!40000 ALTER TABLE `rooms` DISABLE KEYS */;
INSERT INTO `rooms` VALUES (1,'Phòng 1','2026-04-30 09:41:46','2026-04-30 09:41:46',NULL);
/*!40000 ALTER TABLE `rooms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schedules`
--

DROP TABLE IF EXISTS `schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `schedules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `class_id` bigint unsigned NOT NULL,
  `room_id` bigint unsigned DEFAULT NULL,
  `teacher_id` bigint unsigned NOT NULL,
  `study_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_schedules_class_date_time` (`class_id`,`study_date`,`start_time`,`end_time`),
  KEY `idx_schedules_teacher_date_time` (`teacher_id`,`study_date`,`start_time`,`end_time`),
  KEY `idx_schedules_room_date_time` (`room_id`,`study_date`,`start_time`,`end_time`),
  CONSTRAINT `fk_schedules_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`),
  CONSTRAINT `fk_schedules_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`),
  CONSTRAINT `fk_schedules_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schedules`
--

LOCK TABLES `schedules` WRITE;
/*!40000 ALTER TABLE `schedules` DISABLE KEYS */;
INSERT INTO `schedules` VALUES (2,1,1,45,'2026-04-30','17:43:00','22:47:00','2026-04-30 09:42:28','2026-04-30 09:42:28');
/*!40000 ALTER TABLE `schedules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staff_profiles`
--

DROP TABLE IF EXISTS `staff_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `staff_profiles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `position` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `fk_staff_profiles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staff_profiles`
--

LOCK TABLES `staff_profiles` WRITE;
/*!40000 ALTER TABLE `staff_profiles` DISABLE KEYS */;
INSERT INTO `staff_profiles` VALUES (3,2,'Academic Coordinator','2026-04-29 17:21:28','2026-04-29 17:21:28'),(4,12,'Finance Officer','2026-04-29 17:21:28','2026-04-29 17:21:28');
/*!40000 ALTER TABLE `staff_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_leads`
--

DROP TABLE IF EXISTS `student_leads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `student_leads` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `student_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gender` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `interests` text COLLATE utf8mb4_unicode_ci,
  `personality` text COLLATE utf8mb4_unicode_ci,
  `parent_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parent_phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `school_name` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_grade` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referral_source` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_level` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `study_time` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parent_expectation` text COLLATE utf8mb4_unicode_ci,
  `status` enum('new','entry_tested','trial_completed','official','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new',
  `admin_note` text COLLATE utf8mb4_unicode_ci,
  `converted_user_id` bigint unsigned DEFAULT NULL,
  `converted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_student_leads_user` (`converted_user_id`),
  KEY `idx_student_leads_status_created` (`status`,`created_at`),
  KEY `idx_student_leads_parent_phone` (`parent_phone`),
  CONSTRAINT `fk_student_leads_user` FOREIGN KEY (`converted_user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_leads`
--

LOCK TABLES `student_leads` WRITE;
/*!40000 ALTER TABLE `student_leads` DISABLE KEYS */;
INSERT INTO `student_leads` VALUES (1,'Trần Minh Khoa',NULL,NULL,NULL,NULL,'Nguyễn Thị Lan','0987654321','THPT Trần Phú','10','Facebook','IELTS 6.5','Tối 2-4-6','Mong con giao tiếp tốt hơn','official','Đã chuyển sang học viên',48,'2026-05-03 09:05:00','2026-05-03 09:00:00');
/*!40000 ALTER TABLE `student_leads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_portfolios`
--

DROP TABLE IF EXISTS `student_portfolios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `student_portfolios` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint unsigned NOT NULL,
  `type` enum('progress_video','activity_photo','feedback') NOT NULL,
  `media_url` varchar(255) NOT NULL,
  `description` text,
  `is_public_web` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_portfolio_student` (`student_id`),
  CONSTRAINT `fk_portfolio_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_portfolios`
--

LOCK TABLES `student_portfolios` WRITE;
/*!40000 ALTER TABLE `student_portfolios` DISABLE KEYS */;
/*!40000 ALTER TABLE `student_portfolios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `parents`
--

DROP TABLE IF EXISTS `parents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `parents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `father_name` varchar(150) DEFAULT NULL,
  `father_phone` varchar(20) DEFAULT NULL,
  `father_id_card` varchar(30) DEFAULT NULL,
  `mother_name` varchar(150) DEFAULT NULL,
  `mother_phone` varchar(20) DEFAULT NULL,
  `mother_id_card` varchar(30) DEFAULT NULL,
  `social_links` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `parents`
--

LOCK TABLES `parents` WRITE;
/*!40000 ALTER TABLE `parents` DISABLE KEYS */;
INSERT INTO `parents` VALUES (1,'Bùi Văn Minh','0123456789',NULL,NULL,NULL,NULL,NULL,'2026-04-30 09:45:25','2026-04-30 09:45:25'),(2,'Nguyễn Thị Lan','0987654321',NULL,NULL,NULL,NULL,NULL,'2026-05-03 09:00:00','2026-05-03 09:00:00');
/*!40000 ALTER TABLE `parents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_profiles`
--

DROP TABLE IF EXISTS `student_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `student_profiles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `student_code` varchar(30) NOT NULL,
  `school_name` varchar(180) DEFAULT NULL,
  `target_score` varchar(50) DEFAULT NULL,
  `entry_test_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  UNIQUE KEY `student_code` (`student_code`),
  KEY `idx_student_profiles_parent_id` (`parent_id`),
  KEY `fk_student_profiles_entry_test` (`entry_test_id`),
  CONSTRAINT `fk_student_profiles_parent` FOREIGN KEY (`parent_id`) REFERENCES `parents` (`id`),
  CONSTRAINT `fk_student_profiles_entry_test` FOREIGN KEY (`entry_test_id`) REFERENCES `exams` (`id`),
  CONSTRAINT `fk_student_profiles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_profiles`
--

LOCK TABLES `student_profiles` WRITE;
/*!40000 ALTER TABLE `student_profiles` DISABLE KEYS */;
INSERT INTO `student_profiles` VALUES (1,46,1,'HV00046','B6','Đạt 730 TOEIC',NULL,'2026-04-30 09:45:25','2026-04-30 09:45:25'),(2,48,2,'HV00048','B7','IELTS 6.5',NULL,'2026-05-03 09:00:00','2026-05-03 09:00:00');
/*!40000 ALTER TABLE `student_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `submissions`
--

DROP TABLE IF EXISTS `submissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `submissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `assignment_id` bigint unsigned NOT NULL,
  `student_id` bigint unsigned NOT NULL,
  `file_url` varchar(255) DEFAULT NULL,
  `submitted_at` datetime DEFAULT NULL,
  `score` decimal(5,2) DEFAULT NULL,
  `teacher_comment` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_submissions_assignment_student` (`assignment_id`,`student_id`),
  KEY `fk_submissions_student` (`student_id`),
  CONSTRAINT `fk_submissions_assignment` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`),
  CONSTRAINT `fk_submissions_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `submissions`
--

LOCK TABLES `submissions` WRITE;
/*!40000 ALTER TABLE `submissions` DISABLE KEYS */;
INSERT INTO `submissions` VALUES (1,2,46,'/assets/uploads/homeworks/submission-46-2-1777544104-Quy_tr__nh_t___v___n_v___gi___ng_d___y.docx','2026-04-30 17:15:04',1.00,'quá dốt','2026-04-30 09:56:05','2026-04-30 10:15:04');
/*!40000 ALTER TABLE `submissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `teacher_certificates`
--

DROP TABLE IF EXISTS `teacher_certificates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `teacher_certificates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `teacher_id` bigint unsigned NOT NULL,
  `certificate_name` varchar(120) NOT NULL,
  `score` varchar(30) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_teacher_cert_teacher` (`teacher_id`),
  CONSTRAINT `fk_teacher_cert_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `teacher_profiles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teacher_certificates`
--

LOCK TABLES `teacher_certificates` WRITE;
/*!40000 ALTER TABLE `teacher_certificates` DISABLE KEYS */;
/*!40000 ALTER TABLE `teacher_certificates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `teacher_profiles`
--

DROP TABLE IF EXISTS `teacher_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `teacher_profiles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `teacher_code` varchar(30) NOT NULL,
  `degree` varchar(150) DEFAULT NULL,
  `experience_years` int DEFAULT '0',
  `bio` text,
  `intro_video_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  UNIQUE KEY `teacher_code` (`teacher_code`),
  CONSTRAINT `fk_teacher_profiles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teacher_profiles`
--

LOCK TABLES `teacher_profiles` WRITE;
/*!40000 ALTER TABLE `teacher_profiles` DISABLE KEYS */;
INSERT INTO `teacher_profiles` VALUES (1,45,'GV00045','cntt',4,'hehe',NULL,'2026-04-30 09:40:53','2026-04-30 09:40:53');
/*!40000 ALTER TABLE `teacher_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tuition_fees`
--

DROP TABLE IF EXISTS `tuition_fees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tuition_fees` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint unsigned NOT NULL,
  `class_id` bigint unsigned NOT NULL,
  `package_id` bigint unsigned DEFAULT NULL,
  `base_amount` decimal(12,2) NOT NULL,
  `discount_type` varchar(100) DEFAULT NULL,
  `discount_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_amount` decimal(12,2) NOT NULL,
  `amount_paid` decimal(12,2) NOT NULL DEFAULT '0.00',
  `payment_plan` enum('full','monthly') NOT NULL DEFAULT 'full',
  `status` enum('paid','debt') NOT NULL DEFAULT 'debt',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_tuition_student` (`student_id`),
  KEY `fk_tuition_class` (`class_id`),
  KEY `fk_tuition_package` (`package_id`),
  CONSTRAINT `fk_tuition_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`),
  CONSTRAINT `fk_tuition_package` FOREIGN KEY (`package_id`) REFERENCES `promotions` (`id`),
  CONSTRAINT `fk_tuition_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tuition_fees`
--

LOCK TABLES `tuition_fees` WRITE;
/*!40000 ALTER TABLE `tuition_fees` DISABLE KEYS */;
INSERT INTO `tuition_fees` VALUES (1,46,1,NULL,300000.00,NULL,0.00,300000.00,300000.00,'full','paid','2026-04-30 09:45:58','2026-04-30 10:07:41'),(2,48,1,NULL,300000.00,NULL,0.00,300000.00,0.00,'monthly','debt','2026-05-03 09:00:00','2026-05-03 09:00:00');
/*!40000 ALTER TABLE `tuition_fees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(120) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `fk_users_role` (`role_id`),
  CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin@ec.local','$2y$10$jJwUdEUYqjqsK9wK.JhdIexiPSIAny.52/xO3ymM8IfcEXSnU7hMO','System Admin',1,'0900000001','admin@ec.local',NULL,'active','2026-04-13 16:14:22','2026-04-13 16:24:07',NULL),(2,'staff@ec.local','$2y$10$cKc.AuoVhVDgazbhiILEAOVfbtmVyVK506JQ91d7DRWV7nNl2x8AO','Academic Staff',2,'0900000002','staff@ec.local',NULL,'active','2026-04-13 16:14:22','2026-04-14 10:06:49',NULL),(12,'staff.finance@ec.local','$2y$10$5luD5xfAGFeqHwRdPWq1ZezZW43r.qwE2wFcaXCanvh1O0DR8XYum','Le Thi Finance',2,'0900000010','staff.finance@ec.local',NULL,'inactive','2026-04-15 13:30:01','2026-04-30 09:28:56','2026-04-30 09:28:56'),(45,'rua093','$2y$10$vG0gGekavRFBeU3D7rcpSeE20D4VzUcvkBxImjDln4ofYB20Wpi5y','Huỳnh Như Ý',3,'0965951245','playinggamer03@gmail.com',NULL,'active','2026-04-30 09:40:53','2026-04-30 09:40:53',NULL),(46,'thanhtrung','$2y$10$1jdrs57lAdc5.cYZVlgVGukgf.IYyLI1ZGY9xTqUF1DVLySppW6Aa','Nguyễn Thành Trung',4,NULL,'thanhtrung1@gmail.com',NULL,'active','2026-04-30 09:45:25','2026-04-30 09:45:25',NULL),(48,'student48','$2y$10$9Jr7m0C8x8fN0VQ5YgZyQeOqjYQm8n4TQ5l6T4w2HqYhW9dYp1WbQK','Trần Minh Khoa',4,'0900000048','student48@example.com',NULL,'active','2026-05-03 09:00:00','2026-05-03 09:00:00',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-01 14:38:45
