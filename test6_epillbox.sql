-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Dec 13, 2024 at 12:02 PM
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
(3, 'doctor', '', '', '', ''),
(6, 'sunimal', '', '', '', ''),
(7, 'amali', '', '', '', ''),
(8, 'ranjith', '', '', '', ''),
(9, 'nadeesha', '', '', '', ''),
(10, 'mahesh', '', '', '', ''),
(11, 'shyamali', '', '', '', ''),
(12, 'prabath', '', '', '', ''),
(13, 'dulanjali', '', '', '', ''),
(14, 'kasun', '', '', '', ''),
(15, 'nishantha', '', '', '', ''),
(16, 'champa', '', '', '', ''),
(17, 'janaka', '', '', '', ''),
(18, 'sujitha', '', '', '', ''),
(19, 'damith', '', '', '', ''),
(20, 'suresh', '', '', '', ''),
(21, 'indika', '', '', '', ''),
(22, 'lasantha', '', '', '', ''),
(23, 'kumara', '', '', '', ''),
(24, 'priyanthi', '', '', '', ''),
(25, 'tharuka', '', '', '', '');

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
(2, 'patient', '', '', '', ''),
(5, 'sripal', '', '', '', ''),
(26, 'pasan', '', '', '', ''),
(27, 'nimalka', '', '', '', ''),
(28, 'saman', '', '', '', ''),
(29, 'kamani', '', '', '', ''),
(30, 'sunil', '', '', '', ''),
(31, 'gayani', '', '', '', ''),
(32, 'chandima', '', '', '', ''),
(33, 'nayana', '', '', '', ''),
(34, 'ruwan', '', '', '', ''),
(35, 'amara', '', '', '', ''),
(36, 'sanjeewa', '', '', '', ''),
(37, 'dilhani', '', '', '', ''),
(38, 'nuwan', '', '', '', ''),
(39, 'anushka', '', '', '', ''),
(40, 'malith', '', '', '', ''),
(41, 'ishara', '', '', '', ''),
(42, 'manori', '', '', '', ''),
(43, 'dinesh', '', '', '', ''),
(44, 'priyanka', '', '', '', ''),
(45, 'thilina', '', '', '', '');

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
(4, 'pharmacist', NULL, '', ''),
(55, 'amaraa', NULL, '', ''),
(56, 'kasunA', NULL, '', ''),
(57, 'nimalkaa', NULL, '', '');

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
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `first_name`, `last_name`, `email`, `dob`, `contact`, `role`, `created_at`, `updated_at`, `reset_token`, `reset_token_expires_at`) VALUES
(1, 'dileepa', '$2y$10$YXOtcDF//MeaYM1q68o62upmjMlkNtH3btcsPy8IQ8zAluQi13Bmy', NULL, NULL, NULL, '1990-02-28', '0766322288', 'admin', '2024-12-11 21:42:45', '2024-12-11 21:43:03', NULL, NULL),
(2, 'patient', '$2y$10$.Xid85LXUYesDUofWWFto.9U95vRN5y1GJkiQ515Gb.k07olGIFf2', NULL, NULL, NULL, '2024-12-04', '64637387', 'patient', '2024-12-11 22:14:31', '2024-12-11 22:14:31', NULL, NULL),
(3, 'doctor', '$2y$10$Vj0ejle72QpoGDrcsZRztOAECpGq9mXTVojvCnT3N.XzbabMC8NBC', NULL, NULL, NULL, '2024-12-03', '356777', 'doctor', '2024-12-11 22:18:54', '2024-12-11 22:18:54', NULL, NULL),
(4, 'pharmacist', '$2y$10$gnBOXHe8scu.xXINj7Zs/upVtDzujHX55UBZG0752M6Yw0EtkqxaS', NULL, NULL, NULL, '2024-12-01', '77544343', 'pharmacist', '2024-12-11 22:19:21', '2024-12-11 22:19:21', NULL, NULL),
(5, 'sripal', '$2y$10$fGPgObVFOT8FvtHe..uP6ebZEppd.jENdGD6M0M3PgeiRMJw6XMP.', '', '', '', '2024-12-03', '35646464', 'patient', '2024-12-12 15:55:25', '2024-12-12 15:55:25', NULL, NULL),
(6, 'sunimal', '$2y$10$fGPgObVFOT8FvtHe..uP6ebZEppd.jENdGD6M0M3PgeiRMJw6XMP.', 'Sunimal', 'Perera', 'sunimal@example.com', '1980-05-10', '0771234567', 'doctor', '2024-12-12 17:26:01', '2024-12-12 17:26:01', NULL, NULL),
(7, 'amali', '$2y$10$fGPgObVFOT8FvtHe..uP6ebZEppd.jENdGD6M0M3PgeiRMJw6XMP.', 'Amali', 'Silva', 'amali@example.com', '1985-09-15', '0779876543', 'doctor', '2024-12-12 17:26:01', '2024-12-12 17:26:01', NULL, NULL),
(8, 'ranjith', '$2y$10$fGPgObVFOT8FvtHe..uP6ebZEppd.jENdGD6M0M3PgeiRMJw6XMP.', 'Ranjith', 'Fernando', 'ranjith@example.com', '1978-02-28', '0765432109', 'doctor', '2024-12-12 17:26:01', '2024-12-12 17:26:01', NULL, NULL),
(9, 'nadeesha', '$2y$10$fGPgObVFOT8FvtHe..uP6ebZEppd.jENdGD6M0M3PgeiRMJw6XMP.', 'Nadeesha', 'Kumari', 'nadeesha@example.com', '1982-07-20', '0714329876', 'doctor', '2024-12-12 17:26:01', '2024-12-12 17:26:01', NULL, NULL),
(10, 'mahesh', '$2y$10$fGPgObVFOT8FvtHe..uP6ebZEppd.jENdGD6M0M3PgeiRMJw6XMP.', 'Mahesh', 'Jayasinghe', 'mahesh@example.com', '1979-12-03', '0709875432', 'doctor', '2024-12-12 17:26:01', '2024-12-12 17:26:01', NULL, NULL),
(11, 'shyamali', '$2y$10$fGPgObVFOT8FvtHe..uP6ebZEppd.jENdGD6M0M3PgeiRMJw6XMP.', 'Shyamali', 'Perera', 'shyamali@example.com', '1984-04-11', '0776541230', 'doctor', '2024-12-12 17:26:01', '2024-12-12 17:26:01', NULL, NULL),
(12, 'prabath', '$2y$10$fGPgObVFOT8FvtHe..uP6ebZEppd.jENdGD6M0M3PgeiRMJw6XMP.', 'Prabath', 'Silva', 'prabath@example.com', '1981-09-28', '0761238765', 'doctor', '2024-12-12 17:26:01', '2024-12-12 17:26:01', NULL, NULL),
(13, 'dulanjali', '$2y$10$fGPgObVFOT8FvtHe..uP6ebZEppd.jENdGD6M0M3PgeiRMJw6XMP.', 'Dulanjali', 'Fernando', 'dulanjali@example.com', '1983-01-15', '0717653210', 'doctor', '2024-12-12 17:26:01', '2024-12-12 17:26:01', NULL, NULL),
(14, 'kasun', '$2y$10$fGPgObVFOT8FvtHe..uP6ebZEppd.jENdGD6M0M3PgeiRMJw6XMP.', 'Kasun', 'Gunawardena', 'kasun@example.com', '1977-06-08', '0703219876', 'doctor', '2024-12-12 17:26:01', '2024-12-12 17:26:01', NULL, NULL),
(15, 'nishantha', '$2y$10$fGPgObVFOT8FvtHe..uP6ebZEppd.jENdGD6M0M3PgeiRMJw6XMP.', 'Nishantha', 'Karunaratne', 'nishantha@example.com', '1980-11-22', '0718764321', 'doctor', '2024-12-12 17:26:01', '2024-12-12 17:26:01', NULL, NULL),
(16, 'champa', '$2y$10$fGPgObVFOT8FvtHe..uP6ebZEppd.jENdGD6M0M3PgeiRMJw6XMP.', 'Champa', 'Kumari', 'champa@example.com', '1982-03-05', '0724328765', 'doctor', '2024-12-12 17:26:01', '2024-12-12 17:26:01', NULL, NULL),
(17, 'janaka', '$2y$10$fGPgObVFOT8FvtHe..uP6ebZEppd.jENdGD6M0M3PgeiRMJw6XMP.', 'Janaka', 'Jayasinghe', 'janaka@example.com', '1979-08-18', '0708763210', 'doctor', '2024-12-12 17:26:01', '2024-12-12 17:26:01', NULL, NULL),
(18, 'sujitha', '$2y$10$fGPgObVFOT8FvtHe..uP6ebZEppd.jENdGD6M0M3PgeiRMJw6XMP.', 'Sujitha', 'Perera', 'sujitha@example.com', '1984-01-01', '0775439876', 'doctor', '2024-12-12 17:26:01', '2024-12-12 17:26:01', NULL, NULL),
(19, 'damith', '$2y$10$fGPgObVFOT8FvtHe..uP6ebZEppd.jENdGD6M0M3PgeiRMJw6XMP.', 'Damith', 'Silva', 'damith@example.com', '1981-06-14', '0769874321', 'doctor', '2024-12-12 17:26:01', '2024-12-12 17:26:01', NULL, NULL),
(20, 'suresh', '$2y$10$fGPgObVFOT8FvtHe..uP6ebZEppd.jENdGD6M0M3PgeiRMJw6XMP.', 'Suresh', 'Fernando', 'suresh@example.com', '1983-10-29', '0713218765', 'doctor', '2024-12-12 17:26:01', '2024-12-12 17:26:01', NULL, NULL),
(21, 'indika', '$2y$10$fGPgObVFOT8FvtHe..uP6ebZEppd.jENdGD6M0M3PgeiRMJw6XMP.', 'Indika', 'Gunawardena', 'indika@example.com', '1977-03-22', '0707652109', 'doctor', '2024-12-12 17:26:01', '2024-12-12 17:26:01', NULL, NULL),
(22, 'lasantha', '$2y$10$fGPgObVFOT8FvtHe..uP6ebZEppd.jENdGD6M0M3PgeiRMJw6XMP.', 'Lasantha', 'Karunaratne', 'lasantha@example.com', '1980-08-05', '0712309876', 'doctor', '2024-12-12 17:26:01', '2024-12-12 17:26:01', NULL, NULL),
(23, 'kumara', '$2y$10$fGPgObVFOT8FvtHe..uP6ebZEppd.jENdGD6M0M3PgeiRMJw6XMP.', 'Kumara', 'Kumari', 'kumara@example.com', '1982-01-18', '0728764321', 'doctor', '2024-12-12 17:26:01', '2024-12-12 17:26:01', NULL, NULL),
(24, 'priyanthi', '$2y$10$fGPgObVFOT8FvtHe..uP6ebZEppd.jENdGD6M0M3PgeiRMJw6XMP.', 'Priyanthi', 'Jayasinghe', 'priyanthi@example.com', '1979-05-01', '0704328765', 'doctor', '2024-12-12 17:26:01', '2024-12-12 17:26:01', NULL, NULL),
(25, 'tharuka', '$2y$10$fGPgObVFOT8FvtHe..uP6ebZEppd.jENdGD6M0M3PgeiRMJw6XMP.', 'Tharuka', 'Perera', 'tharuka@example.com', '1984-09-14', '0779873210', 'doctor', '2024-12-12 17:26:01', '2024-12-12 17:26:01', NULL, NULL),
(26, 'pasan', '$2y$10$fGPgObVFOT8FvtHe..uP6ebZEppd.jENdGD6M0M3PgeiRMJw6XMP.', 'Pasan', 'Jayawardena', 'pasan@example.com', '1990-11-20', '0718765432', 'patient', '2024-12-12 17:26:01', '2024-12-12 17:26:01', NULL, NULL),
(27, 'nimalka', '$2y$10$fGPgObVFOT8FvtHe..uP6ebZEppd.jENdGD6M0M3PgeiRMJw6XMP.', 'Nimalka', 'Karunaratne', 'nimalka@example.com', '1995-06-05', '0701239876', 'patient', '2024-12-12 17:26:01', '2024-12-12 17:26:01', NULL, NULL),
(28, 'saman', '$2y$10$fGPgObVFOT8FvtHe..uP6ebZEppd.jENdGD6M0M3PgeiRMJw6XMP.', 'Saman', 'Kumara', 'saman@example.com', '1988-03-12', '0729871234', 'patient', '2024-12-12 17:26:01', '2024-12-12 17:26:01', NULL, NULL),
(29, 'kamani', '$2y$10$fGPgObVFOT8FvtHe..uP6ebZEppd.jENdGD6M0M3PgeiRMJw6XMP.', 'Kamani', 'Perera', 'kamani@example.com', '1992-09-25', '0714327650', 'patient', '2024-12-12 17:26:01', '2024-12-12 17:26:01', NULL, NULL),
(30, 'sunil', '$2y$10$fGPgObVFOT8FvtHe..uP6ebZEppd.jENdGD6M0M3PgeiRMJw6XMP.', 'Sunil', 'Silva', 'sunil@example.com', '1985-04-08', '0708762109', 'patient', '2024-12-12 17:26:01', '2024-12-12 17:26:01', NULL, NULL),
(31, 'gayani', '$2y$10$fGPgObVFOT8FvtHe..uP6ebZEppd.jENdGD6M0M3PgeiRMJw6XMP.', 'Gayani', 'Fernando', 'gayani@example.com', '1991-12-17', '0773216540', 'patient', '2024-12-12 17:26:01', '2024-12-12 17:26:01', NULL, NULL),
(32, 'chandima', '$2y$10$fGPgObVFOT8FvtHe..uP6ebZEppd.jENdGD6M0M3PgeiRMJw6XMP.', 'Chandima', 'Gunawardena', 'chandima@example.com', '1989-07-01', '0717650987', 'patient', '2024-12-12 17:26:01', '2024-12-12 17:26:01', NULL, NULL),
(33, 'nayana', '$2y$10$fGPgObVFOT8FvtHe..uP6ebZEppd.jENdGD6M0M3PgeiRMJw6XMP.', 'Nayana', 'Karunaratne', 'nayana@example.com', '1993-02-14', '0702108765', 'patient', '2024-12-12 17:26:01', '2024-12-12 17:26:01', NULL, NULL),
(34, 'ruwan', '$2y$10$fGPgObVFOT8FvtHe..uP6ebZEppd.jENdGD6M0M3PgeiRMJw6XMP.', 'Ruwan', 'Kumara', 'ruwan@example.com', '1987-08-29', '0726549871', 'patient', '2024-12-12 17:26:01', '2024-12-12 17:26:01', NULL, NULL),
(35, 'amara', '$2y$10$fGPgObVFOT8FvtHe..uP6ebZEppd.jENdGD6M0M3PgeiRMJw6XMP.', 'Amara', 'Perera', 'amara@example.com', '1991-05-12', '0719874320', 'patient', '2024-12-12 17:26:01', '2024-12-12 17:26:01', NULL, NULL),
(36, 'sanjeewa', '$2y$10$fGPgObVFOT8FvtHe..uP6ebZEppd.jENdGD6M0M3PgeiRMJw6XMP.', 'Sanjeewa', 'Silva', 'sanjeewa@example.com', '1984-11-27', '0703217654', 'patient', '2024-12-12 17:26:01', '2024-12-12 17:26:01', NULL, NULL),
(37, 'dilhani', '$2y$10$fGPgObVFOT8FvtHe..uP6ebZEppd.jENdGD6M0M3PgeiRMJw6XMP.', 'Dilhani', 'Fernando', 'dilhani@example.com', '1990-08-05', '0777651239', 'patient', '2024-12-12 17:26:01', '2024-12-12 17:26:01', NULL, NULL),
(38, 'nuwan', '$2y$10$fGPgObVFOT8FvtHe..uP6ebZEppd.jENdGD6M0M3PgeiRMJw6XMP.', 'Nuwan', 'Gunawardena', 'nuwan@example.com', '1983-03-19', '0711238764', 'patient', '2024-12-12 17:26:01', '2024-12-12 17:26:01', NULL, NULL),
(39, 'anushka', '$2y$10$fGPgObVFOT8FvtHe..uP6ebZEppd.jENdGD6M0M3PgeiRMJw6XMP.', 'Anushka', 'Karunaratne', 'anushka@example.com', '1988-10-31', '0725436789', 'patient', '2024-12-12 17:26:01', '2024-12-12 17:26:01', NULL, NULL),
(40, 'malith', '$2y$10$fGPgObVFOT8FvtHe..uP6ebZEppd.jENdGD6M0M3PgeiRMJw6XMP.', 'Malith', 'Kumara', 'malith@example.com', '1992-06-12', '0718763214', 'patient', '2024-12-12 17:26:01', '2024-12-12 17:26:01', NULL, NULL),
(41, 'ishara', '$2y$10$fGPgObVFOT8FvtHe..uP6ebZEppd.jENdGD6M0M3PgeiRMJw6XMP.', 'Ishara', 'Perera', 'ishara@example.com', '1986-01-26', '0709871235', 'patient', '2024-12-12 17:26:01', '2024-12-12 17:26:01', NULL, NULL),
(42, 'manori', '$2y$10$fGPgObVFOT8FvtHe..uP6ebZEppd.jENdGD6M0M3PgeiRMJw6XMP.', 'Manori', 'Silva', 'manori@example.com', '1989-09-08', '0774328760', 'patient', '2024-12-12 17:26:01', '2024-12-12 17:26:01', NULL, NULL),
(43, 'dinesh', '$2y$10$fGPgObVFOT8FvtHe..uP6ebZEppd.jENdGD6M0M3PgeiRMJw6XMP.', 'Dinesh', 'Fernando', 'dinesh@example.com', '1982-04-21', '0768762103', 'patient', '2024-12-12 17:26:01', '2024-12-12 17:26:01', NULL, NULL),
(44, 'priyanka', '$2y$10$fGPgObVFOT8FvtHe..uP6ebZEppd.jENdGD6M0M3PgeiRMJw6XMP.', 'Priyanka', 'Gunawardena', 'priyanka@example.com', '1987-12-04', '0712307658', 'patient', '2024-12-12 17:26:01', '2024-12-12 17:26:01', NULL, NULL),
(45, 'thilina', '$2y$10$fGPgObVFOT8FvtHe..uP6ebZEppd.jENdGD6M0M3PgeiRMJw6XMP.', 'Thilina', 'Karunaratne', 'thilina@example.com', '1991-07-17', '0727650982', 'patient', '2024-12-12 17:26:01', '2024-12-12 17:26:01', NULL, NULL),
(55, 'amaraa', '$2y$10$fGPgObVFOT8FvtHe..uP6ebZEppd.jENdGD6M0M3PgeiRMJw6XMP.', 'Amara', 'Perera', 'amara.p@example.com', '1985-07-15', '0771234568', 'pharmacist', '2024-12-12 17:32:33', '2024-12-12 17:32:33', NULL, NULL),
(56, 'kasunA', '$2y$10$fGPgObVFOT8FvtHe..uP6ebZEppd.jENdGD6M0M3PgeiRMJw6XMP.', 'Kasun', 'Silva', 'kasun.s@example.com', '1990-03-22', '0719876544', 'pharmacist', '2024-12-12 17:32:33', '2024-12-12 17:32:33', NULL, NULL),
(57, 'nimalkaa', '$2y$10$fGPgObVFOT8FvtHe..uP6ebZEppd.jENdGD6M0M3PgeiRMJw6XMP.', 'Nimalka', 'Fernando', 'nimalka.f@example.com', '1982-11-08', '0705432110', 'pharmacist', '2024-12-12 17:32:33', '2024-12-12 17:32:33', NULL, NULL);

--
-- Triggers `users`
--
DROP TRIGGER IF EXISTS `after_users_insert`;
DELIMITER $$
CREATE TRIGGER `after_users_insert` AFTER INSERT ON `users` FOR EACH ROW BEGIN
    IF NEW.role = 'doctor' THEN
        INSERT INTO doctors (user_id, username, specialization, experience, hospital, hospital_address) 
        VALUES (NEW.id, NEW.username, '', '', '', ''); -- Set default values for other columns
    ELSEIF NEW.role = 'pharmacist' THEN
        INSERT INTO pharmacists (user_id, username, pharmacy_id, pharmacy_name, license_number) 
        VALUES (NEW.id, NEW.username, NULL, '', ''); -- Set default values for other columns
    ELSEIF NEW.role = 'patient' THEN
        INSERT INTO patients (user_id, username, conditions, medications, emergency_contact_1, emergency_contact_2) 
        VALUES (NEW.id, NEW.username, '', '', '', ''); -- Set default values for other columns
    END IF;
END
$$
DELIMITER ;

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
