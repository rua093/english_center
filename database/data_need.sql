-- MySQL dump 10.13  Distrib 8.0.36, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: english_center_db
-- ------------------------------------------------------
-- Server version	8.0.45

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (4,'Nop bai tap tu portal','student.assignment.submit'),(5,'Cap nhat hoc phi tu portal','student.tuition.update'),(9,'Cham diem bai nop','academic.submissions.grade'),(11,'Xem dashboard quan tri','admin.dashboard.view'),(19,'Xem lop hoc','academic.classes.view'),(20,'Tao lop hoc','academic.classes.create'),(21,'Cap nhat lop hoc','academic.classes.update'),(22,'Xoa lop hoc','academic.classes.delete'),(23,'Xem lich hoc','academic.schedules.view'),(24,'Tao lich hoc','academic.schedules.create'),(25,'Cap nhat lich hoc','academic.schedules.update'),(26,'Xoa lich hoc','academic.schedules.delete'),(27,'Xem bai tap','academic.assignments.view'),(28,'Tao bai tap','academic.assignments.create'),(29,'Cap nhat bai tap','academic.assignments.update'),(30,'Xoa bai tap','academic.assignments.delete'),(31,'Xem bai nop','academic.submissions.view'),(32,'Xem tai lieu','materials.view'),(33,'Tao tai lieu','materials.create'),(34,'Cap nhat tai lieu','materials.update'),(35,'Xoa tai lieu','materials.delete'),(36,'Xem hoc phi','finance.tuition.view'),(38,'Xem danh gia','feedback.view'),(40,'Cap nhat danh gia','feedback.update'),(41,'Xoa danh gia','feedback.delete'),(42,'Xem phe duyet','approval.view'),(43,'Cap nhat phe duyet','approval.update'),(44,'Xem hoat dong','activity.view'),(45,'Tao hoat dong','activity.create'),(46,'Cap nhat hoat dong','activity.update'),(47,'Xoa hoat dong','activity.delete'),(48,'Xem tai khoan ngan hang','bank.view'),(49,'Tao tai khoan ngan hang','bank.create'),(50,'Cap nhat tai khoan ngan hang','bank.update'),(51,'Xoa tai khoan ngan hang','bank.delete'),(58,'Xoa hoc phi','finance.tuition.delete'),(59,'Yêu cầu chỉnh sửa tài chính','finance.adjust.request'),(60,'Tạo yêu cầu phê duyệt','approval.request'),(62,'Xem khoa hoc','academic.courses.view'),(63,'Tao khoa hoc','academic.courses.create'),(64,'Cap nhat khoa hoc','academic.courses.update'),(65,'Xoa khoa hoc','academic.courses.delete'),(66,'Xem roadmap khoa hoc','academic.roadmaps.view'),(67,'Tao roadmap khoa hoc','academic.roadmaps.create'),(68,'Cap nhat roadmap khoa hoc','academic.roadmaps.update'),(69,'Xoa roadmap khoa hoc','academic.roadmaps.delete'),(157,'Cap nhat phan quyen vai tro','admin.role_permission.update'),(158,'Xem phan quyen vai tro','admin.role_permission.view'),(159,'Tao nguoi dung','admin.user.create'),(160,'Xoa nguoi dung','admin.user.delete'),(161,'Cap nhat nguoi dung','admin.user.update'),(162,'Xem nguoi dung','admin.user.view'),(163,'Tao phe duyet','approval.create'),(164,'Xoa phe duyet','approval.delete'),(165,'Tao giao dich thanh toan','finance.payments.create'),(166,'Xoa giao dich thanh toan','finance.payments.delete'),(167,'Cap nhat giao dich thanh toan','finance.payments.update'),(168,'Xem giao dich thanh toan chi tiet','finance.payments.view'),(169,'Tao khuyen mai','finance.promotions.create'),(170,'Xoa khuyen mai','finance.promotions.delete'),(171,'Cap nhat khuyen mai','finance.promotions.update'),(172,'Xem khuyen mai','finance.promotions.view'),(173,'Tao hoc phi','finance.tuition.create'),(174,'Cap nhat hoc phi','finance.tuition.update'),(176,'Xoa ho so ung tuyen giao vien','job_application.delete'),(177,'Cap nhat ho so ung tuyen giao vien','job_application.update'),(178,'Xem ho so ung tuyen giao vien','job_application.view'),(180,'Xoa dau moi hoc vien','student_lead.delete'),(181,'Cap nhat dau moi hoc vien','student_lead.update'),(182,'Xem dau moi hoc vien','student_lead.view'),(183,'Xem phòng học','academic.rooms.view'),(184,'Tạo phòng học','academic.rooms.create'),(185,'Cập nhật phòng học','academic.rooms.update'),(186,'Xóa phòng học','academic.rooms.delete'),(187,'Xem thông báo','notifications.view'),(188,'Tạo thông báo','notifications.create'),(189,'Cập nhật thông báo','notifications.update'),(190,'Xóa thông báo','notifications.delete'),(203,'Xem xuat Excel hoc vien','academic.exports.view');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `role_permissions`
--

LOCK TABLES `role_permissions` WRITE;
/*!40000 ALTER TABLE `role_permissions` DISABLE KEYS */;
INSERT INTO `role_permissions` VALUES (1,4),(4,4),(1,5),(1,9),(3,9),(1,11),(2,11),(3,11),(1,19),(2,19),(1,20),(2,20),(1,21),(2,21),(1,22),(1,23),(2,23),(3,23),(1,24),(2,24),(1,25),(2,25),(1,26),(1,27),(2,27),(3,27),(1,28),(2,28),(3,28),(1,29),(2,29),(3,29),(1,30),(1,31),(2,31),(3,31),(1,32),(2,32),(4,32),(1,33),(2,33),(1,34),(2,34),(1,35),(1,36),(2,36),(1,38),(2,38),(1,40),(2,40),(1,41),(1,42),(2,42),(1,43),(2,43),(1,44),(2,44),(4,44),(1,45),(2,45),(1,46),(2,46),(1,47),(1,48),(1,49),(1,50),(1,51),(1,58),(1,59),(2,59),(1,60),(2,60),(3,60),(1,62),(2,62),(1,63),(2,63),(1,64),(2,64),(1,65),(1,66),(2,66),(1,67),(2,67),(1,68),(2,68),(1,69),(1,157),(1,158),(1,159),(1,160),(1,161),(1,162),(2,162),(1,163),(1,164),(1,165),(2,165),(1,166),(1,167),(2,167),(1,168),(2,168),(1,169),(1,170),(1,171),(1,172),(2,172),(1,173),(2,173),(1,174),(2,174),(1,176),(1,177),(2,177),(1,178),(2,178),(1,180),(1,181),(2,181),(1,182),(2,182),(1,183),(2,183),(1,184),(2,184),(1,185),(2,185),(1,186),(1,187),(2,187),(1,188),(2,188),(1,189),(2,189),(1,190),(1,203),(2,203),(3,203);
/*!40000 ALTER TABLE `role_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'admin','Quan tri he thong','2026-04-29 17:20:48','2026-04-29 17:20:48'),(2,'staff','Giao vu va tu van','2026-04-29 17:20:48','2026-04-29 17:20:48'),(3,'teacher','Giao vien','2026-04-29 17:20:48','2026-04-29 17:20:48'),(4,'student','Hoc vien','2026-04-29 17:20:48','2026-04-29 17:20:48');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-14 23:19:31
