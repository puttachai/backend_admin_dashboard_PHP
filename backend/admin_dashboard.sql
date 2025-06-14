-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Jun 14, 2025 at 08:07 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `admin_dashboard`
--

-- --------------------------------------------------------

--
-- Table structure for table `employee`
--

CREATE TABLE `employee` (
  `id` int(11) NOT NULL COMMENT 'id',
  `emp_ids` int(6) NOT NULL COMMENT 'รหัสพนักงาน',
  `image_path` varchar(255) DEFAULT NULL COMMENT 'ที่อยู่รูปภาพ',
  `full_name` varchar(255) NOT NULL COMMENT 'ชื่อเต็ม',
  `email` varchar(255) NOT NULL COMMENT 'อีเมล',
  `password` varchar(255) NOT NULL COMMENT 'รหัสผ่าน',
  `telephone` varchar(10) NOT NULL COMMENT 'โทรศัพท์',
  `address` varchar(255) NOT NULL COMMENT 'ที่อยู่',
  `department` varchar(128) NOT NULL COMMENT 'แผนกงาน',
  `salary` double(16,2) NOT NULL COMMENT 'เงินเดือน',
  `status` enum('Normal','Active','Inactive','Abnormal') NOT NULL COMMENT 'สถานะ',
  `start_work` datetime DEFAULT NULL ON UPDATE current_timestamp() COMMENT 'วันเริ่มงาน',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee`
--

INSERT INTO `employee` (`id`, `emp_ids`, `image_path`, `full_name`, `email`, `password`, `telephone`, `address`, `department`, `salary`, `status`, `start_work`, `created_at`) VALUES
(2, 857375, 'http://localhost/api_admin_dashboard/img/profile/684bcc63d0645_64016203.png', 'puttachai bodkhuntod', 'puttachai.2546@gmail.com', '3c9909afec25354d551dae21590bb26e38d53f2173b8d3dc3eee4c047e7ab1c1eb8b85103e3be7ba613b31bb5c9c36214dc9f14a42fd7a2fdb84856bca5c44c2', '0952517869', '107 rbac Facility of Engineering', 'Software Engineer', 26000.00, 'Active', '2025-06-16 00:00:00', '2025-06-13 17:56:53'),
(4, 503220, 'http://localhost/api_admin_dashboard/img/profile/684bd42b0a21c_mena4.png', 'mena zaza', 'menazaza@gmail.com', '3c9909afec25354d551dae21590bb26e38d53f2173b8d3dc3eee4c047e7ab1c1eb8b85103e3be7ba613b31bb5c9c36214dc9f14a42fd7a2fdb84856bca5c44c2', '0952517869', 'Srimahasarakham Nursing College', 'Nursing', 34000.00, 'Active', '2025-06-16 00:00:00', '2025-06-13 17:56:53'),
(6, 751278, 'http://localhost/api_admin_dashboard/img/profile/684bdfbe69b17_81d20a9b-0a88-47d2-841b-788244728efb.jpeg', 'Thanakit Bangprasoet', 'Thanakit@gmail.com', '3c9909afec25354d551dae21590bb26e38d53f2173b8d3dc3eee4c047e7ab1c1eb8b85103e3be7ba613b31bb5c9c36214dc9f14a42fd7a2fdb84856bca5c44c2', '0851456369', '107 rbac Facility of Engineering', 'Engineering', 26000.00, 'Active', '2025-06-16 00:00:00', '2025-06-13 17:56:53'),
(11, 996716, NULL, 'Testter Test', 'testters@gmail.com', '3c9909afec25354d551dae21590bb26e38d53f2173b8d3dc3eee4c047e7ab1c1eb8b85103e3be7ba613b31bb5c9c36214dc9f14a42fd7a2fdb84856bca5c44c2', '', '', 'Tester', 21000.00, 'Active', '2025-06-17 00:00:00', '2025-06-14 11:34:53');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`id`,`telephone`),
  ADD UNIQUE KEY `emp_ids_Relationship` (`emp_ids`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `employee`
--
ALTER TABLE `employee`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id', AUTO_INCREMENT=12;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
