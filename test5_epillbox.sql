-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Dec 13, 2024 at 02:18 AM
-- Server version: 8.0.29
-- PHP Version: 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `test1_epillbox`
--

-- --------------------------------------------------------

--
-- Table structure for table `content`
--

DROP TABLE IF EXISTS `content`;
CREATE TABLE IF NOT EXISTS `content` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` text,
  `body` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `content`
--

INSERT INTO `content` (`id`, `title`, `body`) VALUES
(1, 'Test', 'asgdhgsfdrhfshfhfxgfs'),
(2, 'Test', 'asgdhgsfdrhfshfhfxgfs'),
(3, 'Test', 'asgdhgsfdrhfshfhfxgfs');

-- --------------------------------------------------------

--
-- Table structure for table `doctors`
--

DROP TABLE IF EXISTS `doctors`;
CREATE TABLE IF NOT EXISTS `doctors` (
  `user_id` int NOT NULL,
  `username` varchar(25) NOT NULL,
  `specialization` text NOT NULL,
  `experience` text NOT NULL,
  `hospital` text NOT NULL,
  `hospital_address` text NOT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `doctors`
--

INSERT INTO `doctors` (`user_id`, `username`, `specialization`, `experience`, `hospital`, `hospital_address`) VALUES
(3, 'doctor', 'dfafgasf', 'afasfaf', 'affafaf', 'afasff');

-- --------------------------------------------------------

--
-- Table structure for table `medication_logs`
--

DROP TABLE IF EXISTS `medication_logs`;
CREATE TABLE IF NOT EXISTS `medication_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `prescription_id` int NOT NULL,
  `taken_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `prescription_id` (`prescription_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medication_reminders`
--

DROP TABLE IF EXISTS `medication_reminders`;
CREATE TABLE IF NOT EXISTS `medication_reminders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `prescription_id` int NOT NULL,
  `reminder_time` time NOT NULL,
  `frequency` varchar(255) NOT NULL,
  `alert_type` enum('browser','email') NOT NULL,
  `personalized_message` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('active','snoozed','dismissed') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `prescription_id` (`prescription_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
CREATE TABLE IF NOT EXISTS `messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sender_id` int NOT NULL,
  `recipient_id` int NOT NULL,
  `subject` text NOT NULL,
  `body` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `sender_id` (`sender_id`),
  KEY `recipient_id` (`recipient_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `type` enum('refill_reminder','missed_dose','message') NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

DROP TABLE IF EXISTS `patients`;
CREATE TABLE IF NOT EXISTS `patients` (
  `user_id` int NOT NULL,
  `username` varchar(25) NOT NULL,
  `conditions` text NOT NULL,
  `medications` text NOT NULL,
  `emergency_contact_1` varchar(20) DEFAULT NULL,
  `emergency_contact_2` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`user_id`, `username`, `conditions`, `medications`, `emergency_contact_1`, `emergency_contact_2`) VALUES
(2, 'patient', 'Fever', 'sdgdsghsdg', NULL, NULL),
(5, 'sripal', '', '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `pharmacies`
--

DROP TABLE IF EXISTS `pharmacies`;
CREATE TABLE IF NOT EXISTS `pharmacies` (
  `pharmacy_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `contact_information` varchar(255) DEFAULT NULL,
  `opening_hours` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`pharmacy_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pharmacies`
--

INSERT INTO `pharmacies` (`pharmacy_id`, `name`, `address`, `latitude`, `longitude`, `contact_information`, `opening_hours`) VALUES
(1, 'Makandura ', 'sdgdg', 7.32123740, 79.97562890, 'dgdgdgdg', 'sgdgg');

-- --------------------------------------------------------

--
-- Table structure for table `pharmacists`
--

DROP TABLE IF EXISTS `pharmacists`;
CREATE TABLE IF NOT EXISTS `pharmacists` (
  `user_id` int NOT NULL,
  `username` varchar(25) NOT NULL,
  `pharmacy_id` int DEFAULT NULL,
  `pharmacy_name` text NOT NULL,
  `license_number` text NOT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  KEY `FK_pharmacists_pharmacy_id` (`pharmacy_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pharmacists`
--

INSERT INTO `pharmacists` (`user_id`, `username`, `pharmacy_id`, `pharmacy_name`, `license_number`) VALUES
(4, 'pharmacist', 1, 'Makandura pharmacy', 'dgdgdg');

-- --------------------------------------------------------

--
-- Table structure for table `prescriptions`
--

DROP TABLE IF EXISTS `prescriptions`;
CREATE TABLE IF NOT EXISTS `prescriptions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `medication_name` varchar(255) NOT NULL,
  `dosage` varchar(255) NOT NULL,
  `frequency` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `special_instructions` text,
  `doctor_id` int DEFAULT NULL,
  `pharmacy_id` int NOT NULL,
  `refill_status` enum('new','refill_requested','refilled','discontinued') NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `request_status` enum('pending','approved','rejected','no') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'no',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `FK_prescriptions_doctor_id` (`doctor_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `prescriptions`
--

INSERT INTO `prescriptions` (`id`, `user_id`, `medication_name`, `dosage`, `frequency`, `start_date`, `end_date`, `special_instructions`, `doctor_id`, `pharmacy_id`, `refill_status`, `created_at`, `updated_at`, `request_status`) VALUES
(1, 2, 'penadol', '2', '5', '2024-12-12', '2024-12-19', '', 3, 1, 'refilled', '2024-12-12 02:29:55', '2024-12-12 11:32:19', 'approved'),
(2, 2, 'disprin', '5', '6', '2024-12-06', '2024-12-14', '', 3, 1, 'new', '2024-12-12 09:06:50', '2024-12-12 09:33:48', 'no'),
(3, 2, 'sdgdhs', '5', '5', '2024-12-03', '2024-12-05', '', 3, 1, 'new', '2024-12-12 09:07:30', '2024-12-12 09:33:56', 'no'),
(7, 2, 'shdsh', '5', '5', '2024-12-09', '2024-12-20', '', 3, 1, 'new', '2024-12-12 09:40:01', '2024-12-12 09:40:01', 'no'),
(8, 2, 'hjhgfds', '5', '6', '2024-12-09', '2024-12-24', '', 3, 1, 'new', '2024-12-12 10:06:35', '2024-12-12 10:06:35', 'no');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(191) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `last_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `dob` date NOT NULL,
  `contact` varchar(20) NOT NULL,
  `role` enum('patient','doctor','pharmacist','admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `first_name`, `last_name`, `email`, `dob`, `contact`, `role`, `created_at`, `updated_at`, `reset_token`, `reset_token_expires_at`) VALUES
(1, 'dileepa', '$2y$10$YXOtcDF//MeaYM1q68o62upmjMlkNtH3btcsPy8IQ8zAluQi13Bmy', NULL, NULL, NULL, '1990-02-28', '0766322288', 'admin', '2024-12-12 06:42:45', '2024-12-12 06:43:03', NULL, NULL),
(2, 'patient', '$2y$10$.Xid85LXUYesDUofWWFto.9U95vRN5y1GJkiQ515Gb.k07olGIFf2', NULL, NULL, NULL, '2024-12-04', '64637387', 'patient', '2024-12-12 07:14:31', '2024-12-12 07:14:31', NULL, NULL),
(3, 'doctor', '$2y$10$Vj0ejle72QpoGDrcsZRztOAECpGq9mXTVojvCnT3N.XzbabMC8NBC', NULL, NULL, NULL, '2024-12-03', '356777', 'doctor', '2024-12-12 07:18:54', '2024-12-12 07:18:54', NULL, NULL),
(4, 'pharmacist', '$2y$10$gnBOXHe8scu.xXINj7Zs/upVtDzujHX55UBZG0752M6Yw0EtkqxaS', NULL, NULL, NULL, '2024-12-01', '77544343', 'pharmacist', '2024-12-12 07:19:21', '2024-12-12 07:19:21', NULL, NULL),
(5, 'sripal', '$2y$10$fGPgObVFOT8FvtHe..uP6ebZEppd.jENdGD6M0M3PgeiRMJw6XMP.', '', '', '', '2024-12-03', '35646464', 'patient', '2024-12-13 00:55:25', '2024-12-13 00:55:25', NULL, NULL);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `doctors`
--
ALTER TABLE `doctors`
  ADD CONSTRAINT `FK_doctors_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `FK_doctors_username` FOREIGN KEY (`username`) REFERENCES `users` (`username`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `FK_notifications_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `patients`
--
ALTER TABLE `patients`
  ADD CONSTRAINT `FK_patients_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `FK_patients_username` FOREIGN KEY (`username`) REFERENCES `users` (`username`);

--
-- Constraints for table `pharmacists`
--
ALTER TABLE `pharmacists`
  ADD CONSTRAINT `FK_pharmacists_pharmacy_id` FOREIGN KEY (`pharmacy_id`) REFERENCES `pharmacies` (`pharmacy_id`),
  ADD CONSTRAINT `FK_pharmacists_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `FK_pharmacists_username` FOREIGN KEY (`username`) REFERENCES `users` (`username`);

--
-- Constraints for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD CONSTRAINT `FK_prescriptions_doctor_id` FOREIGN KEY (`doctor_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `FK_prescriptions_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
