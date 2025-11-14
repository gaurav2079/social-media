-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 14, 2025 at 12:16 PM
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
-- Database: `tweetphp`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','admin','moderator') DEFAULT 'admin',
  `permissions` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `email`, `password`, `role`, `permissions`, `is_active`, `last_login`, `created_at`) VALUES
(1, 'admin', 'admin@kabi.com', '$2y$10$IJnR/UgGPFjUFjKjz2K6ceWCAi7jUhIiy.UvWIFc4TY0NxZjzDrwm', 'super_admin', 'all', 1, '2025-11-11 18:15:31', '2025-10-29 06:59:01');

-- --------------------------------------------------------

--
-- Table structure for table `admin_logs`
--

CREATE TABLE `admin_logs` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `admin_logs`
--

INSERT INTO `admin_logs` (`id`, `admin_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-29 06:59:08'),
(2, 1, 'login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-29 07:45:18'),
(3, 1, 'login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-30 07:06:06'),
(4, 1, 'login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-30 07:57:38'),
(5, 1, 'login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-10-30 19:33:20'),
(6, 1, 'login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-10-31 07:01:03'),
(7, 1, 'login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-10-31 07:05:07'),
(8, 1, 'login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-10-31 12:53:29'),
(9, 1, 'login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-10-31 13:11:14'),
(10, 1, 'login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 18:15:32');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `comment` longtext NOT NULL,
  `user_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_unicode_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `comment`, `user_id`, `post_id`, `time`) VALUES
(44, 'This is GREAT', 25, 574, '2021-05-01 02:21:10'),
(47, 'hi', 61, 654, '2025-10-26 06:08:52'),
(50, 'good', 61, 741, '2025-10-31 03:07:46'),
(53, 'jj', 61, 741, '2025-10-31 03:21:09'),
(56, 'v', 61, 745, '2025-11-01 14:22:40');

-- --------------------------------------------------------

--
-- Table structure for table `follow`
--

CREATE TABLE `follow` (
  `id` int(11) NOT NULL,
  `follower_id` int(11) NOT NULL,
  `following_id` int(11) NOT NULL,
  `time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_unicode_ci;

--
-- Dumping data for table `follow`
--

INSERT INTO `follow` (`id`, `follower_id`, `following_id`, `time`) VALUES
(15, 40, 2, '2021-04-19 18:30:06'),
(16, 33, 2, '2021-04-19 18:30:56'),
(41, 37, 2, '2021-04-20 20:19:49'),
(43, 5, 2, '2021-04-20 20:20:32'),
(44, 27, 2, '2021-04-20 20:21:18'),
(90, 41, 2, '2021-04-25 18:20:22'),
(94, 25, 27, '2021-04-27 07:07:27'),
(98, 42, 2, '2021-04-29 06:30:41'),
(99, 43, 2, '2021-04-29 06:32:50'),
(100, 44, 2, '2021-04-29 18:17:25'),
(101, 2, 25, '2021-04-30 02:16:24'),
(102, 25, 2, '2021-04-30 22:56:21'),
(121, 55, 2, '2021-05-12 16:18:45'),
(126, 56, 2, '2021-05-12 16:35:31'),
(129, 58, 2, '2021-05-13 14:52:58'),
(139, 40, 55, '2022-01-12 16:50:18'),
(141, 40, 58, '2022-01-12 16:50:23'),
(142, 40, 25, '2022-01-12 16:51:12'),
(144, 2, 41, '2022-01-12 17:03:11'),
(145, 2, 44, '2022-01-12 17:03:20'),
(147, 2, 58, '2022-01-12 17:03:32'),
(159, 58, 25, '2022-01-12 17:49:11'),
(161, 33, 25, '2022-01-12 17:55:36'),
(163, 2, 55, '2022-01-12 18:01:46'),
(164, 42, 55, '2022-01-12 18:02:22'),
(172, 5, 40, '2022-01-13 09:05:05'),
(173, 25, 40, '2022-01-13 09:05:46'),
(174, 27, 40, '2022-01-13 09:06:00'),
(175, 33, 40, '2022-01-13 09:06:32'),
(177, 37, 40, '2022-01-13 09:07:10'),
(178, 42, 40, '2022-01-13 09:08:27'),
(179, 43, 40, '2022-01-13 09:08:38'),
(189, 61, 2, '2025-10-25 18:37:19');

-- --------------------------------------------------------

--
-- Table structure for table `likes`
--

CREATE TABLE `likes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_unicode_ci;

--
-- Dumping data for table `likes`
--

INSERT INTO `likes` (`id`, `user_id`, `post_id`) VALUES
(192, 2, 362),
(209, 25, 573),
(211, 2, 573),
(214, 2, 574),
(227, 2, 711),
(245, 25, 654),
(246, 25, 574),
(247, 25, 362),
(248, 58, 654),
(249, 58, 574),
(250, 58, 573),
(252, 58, 727),
(253, 58, 711),
(258, 33, 654),
(259, 33, 573),
(260, 33, 362),
(261, 33, 727),
(262, 33, 711),
(265, 37, 727),
(266, 40, 727),
(267, 40, 728),
(268, 40, 573),
(269, 40, 362),
(270, 41, 727),
(271, 41, 728),
(272, 42, 727),
(273, 42, 728),
(274, 42, 573),
(275, 43, 573),
(276, 43, 654),
(277, 43, 727),
(279, 2, 729),
(280, 40, 729),
(281, 42, 729),
(291, 40, 654),
(292, 5, 730),
(293, 5, 362),
(294, 5, 574),
(296, 25, 730),
(297, 27, 730),
(298, 33, 730),
(300, 37, 730),
(301, 42, 730),
(302, 43, 730);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `notify_for` int(11) NOT NULL,
  `notify_from` int(11) NOT NULL,
  `target` int(11) NOT NULL,
  `type` enum('follow','like','retweet','qoute','comment','reply','mention') NOT NULL,
  `reason` text DEFAULT NULL,
  `time` datetime NOT NULL,
  `count` int(11) NOT NULL,
  `status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `notify_for`, `notify_from`, `target`, `type`, `reason`, `time`, `count`, `status`) VALUES
(30, 2, 25, 635, 'like', NULL, '2021-04-29 05:50:12', 1, 0),
(32, 2, 42, 0, 'follow', NULL, '2021-04-29 06:30:41', 1, 0),
(34, 2, 25, 711, 'qoute', NULL, '2021-04-29 18:29:24', 1, 0),
(35, 25, 2, 712, 'qoute', NULL, '2021-04-29 18:29:55', 1, 0),
(36, 2, 25, 712, 'like', NULL, '2021-04-29 18:31:11', 1, 0),
(37, 2, 25, 712, 'retweet', NULL, '2021-04-29 18:31:19', 1, 0),
(38, 25, 2, 0, 'follow', NULL, '2021-04-30 02:16:24', 1, 0),
(39, 2, 25, 0, 'follow', NULL, '2021-04-30 22:56:20', 1, 0),
(53, 2, 25, 574, 'comment', NULL, '2021-05-01 02:21:10', 1, 0),
(54, 25, 2, 574, 'reply', NULL, '2021-05-01 02:21:51', 1, 0),
(55, 2, 42, 725, 'mention', NULL, '2021-05-01 02:25:37', 1, 0),
(58, 25, 2, 711, 'like', NULL, '2021-05-01 04:32:36', 1, 0),
(68, 2, 55, 0, 'follow', NULL, '2021-05-12 16:18:46', 1, 0),
(73, 2, 56, 0, 'follow', NULL, '2021-05-12 16:35:31', 1, 0),
(76, 2, 58, 0, 'follow', NULL, '2021-05-13 14:52:58', 1, 0),
(87, 2, 58, 712, 'like', NULL, '2022-01-11 17:55:50', 0, 0),
(90, 55, 40, 0, 'follow', NULL, '2022-01-12 16:50:18', 0, 0),
(92, 58, 40, 0, 'follow', NULL, '2022-01-12 16:50:23', 0, 0),
(93, 2, 40, 712, 'like', NULL, '2022-01-12 16:50:34', 0, 0),
(94, 25, 40, 0, 'follow', NULL, '2022-01-12 16:51:12', 1, 0),
(96, 41, 2, 0, 'follow', NULL, '2022-01-12 17:03:11', 1, 0),
(97, 44, 2, 0, 'follow', NULL, '2022-01-12 17:03:20', 0, 0),
(99, 58, 2, 0, 'follow', NULL, '2022-01-12 17:03:31', 0, 0),
(122, 2, 25, 654, 'like', NULL, '2022-01-12 17:46:43', 0, 0),
(123, 2, 25, 574, 'like', NULL, '2022-01-12 17:46:46', 0, 0),
(124, 2, 25, 362, 'like', NULL, '2022-01-12 17:46:49', 0, 0),
(125, 2, 58, 654, 'like', NULL, '2022-01-12 17:48:57', 0, 0),
(126, 2, 58, 574, 'like', NULL, '2022-01-12 17:48:58', 0, 0),
(127, 2, 58, 573, 'like', NULL, '2022-01-12 17:49:00', 0, 0),
(129, 25, 58, 0, 'follow', NULL, '2022-01-12 17:49:11', 0, 0),
(130, 25, 58, 727, 'like', NULL, '2022-01-12 17:49:13', 0, 0),
(131, 25, 58, 711, 'like', NULL, '2022-01-12 17:49:14', 0, 0),
(137, 2, 33, 654, 'like', NULL, '2022-01-12 17:55:30', 0, 0),
(138, 2, 33, 573, 'like', NULL, '2022-01-12 17:55:32', 0, 0),
(139, 2, 33, 362, 'like', NULL, '2022-01-12 17:55:33', 0, 0),
(140, 25, 33, 0, 'follow', NULL, '2022-01-12 17:55:36', 0, 0),
(141, 25, 33, 727, 'like', NULL, '2022-01-12 17:55:39', 0, 0),
(142, 25, 33, 711, 'like', NULL, '2022-01-12 17:55:41', 0, 0),
(145, 25, 37, 727, 'like', NULL, '2022-01-12 17:56:18', 0, 0),
(146, 25, 40, 727, 'like', NULL, '2022-01-12 17:56:31', 0, 0),
(147, 58, 40, 728, 'like', NULL, '2022-01-12 17:56:33', 0, 0),
(148, 2, 40, 573, 'like', NULL, '2022-01-12 17:56:42', 0, 0),
(149, 2, 40, 362, 'like', NULL, '2022-01-12 17:56:44', 0, 0),
(150, 25, 41, 727, 'like', NULL, '2022-01-12 17:57:00', 0, 0),
(151, 58, 41, 728, 'like', NULL, '2022-01-12 17:57:04', 0, 0),
(152, 25, 42, 727, 'like', NULL, '2022-01-12 17:57:16', 0, 0),
(153, 58, 42, 728, 'like', NULL, '2022-01-12 17:57:21', 0, 0),
(154, 2, 42, 573, 'like', NULL, '2022-01-12 17:57:28', 0, 0),
(155, 2, 43, 573, 'like', NULL, '2022-01-12 17:57:40', 0, 0),
(156, 2, 43, 654, 'like', NULL, '2022-01-12 17:57:42', 0, 0),
(157, 25, 43, 727, 'like', NULL, '2022-01-12 17:57:52', 0, 0),
(160, 55, 2, 0, 'follow', NULL, '2022-01-12 18:01:45', 0, 0),
(161, 55, 2, 729, 'like', NULL, '2022-01-12 18:01:47', 0, 0),
(162, 55, 40, 729, 'like', NULL, '2022-01-12 18:02:09', 0, 0),
(163, 55, 42, 0, 'follow', NULL, '2022-01-12 18:02:22', 0, 0),
(164, 55, 42, 729, 'like', NULL, '2022-01-12 18:02:24', 0, 0),
(181, 2, 40, 654, 'like', NULL, '2022-01-13 09:04:39', 0, 0),
(182, 40, 5, 0, 'follow', NULL, '2022-01-13 09:05:05', 0, 0),
(183, 40, 5, 730, 'like', NULL, '2022-01-13 09:05:07', 0, 0),
(184, 2, 5, 362, 'like', NULL, '2022-01-13 09:05:16', 0, 0),
(185, 2, 5, 574, 'like', NULL, '2022-01-13 09:05:20', 0, 0),
(187, 40, 25, 0, 'follow', NULL, '2022-01-13 09:05:46', 0, 0),
(188, 40, 25, 730, 'like', NULL, '2022-01-13 09:05:48', 0, 0),
(189, 40, 27, 0, 'follow', NULL, '2022-01-13 09:06:00', 0, 0),
(190, 40, 27, 730, 'like', NULL, '2022-01-13 09:06:02', 0, 0),
(191, 40, 33, 0, 'follow', NULL, '2022-01-13 09:06:32', 0, 0),
(192, 40, 33, 730, 'like', NULL, '2022-01-13 09:06:33', 0, 0),
(195, 40, 37, 0, 'follow', NULL, '2022-01-13 09:07:10', 0, 0),
(196, 40, 37, 730, 'like', NULL, '2022-01-13 09:07:12', 0, 0),
(197, 40, 42, 0, 'follow', NULL, '2022-01-13 09:08:27', 0, 0),
(198, 40, 42, 730, 'like', NULL, '2022-01-13 09:08:28', 0, 0),
(199, 40, 43, 0, 'follow', NULL, '2022-01-13 09:08:38', 0, 0),
(200, 40, 43, 730, 'like', NULL, '2022-01-13 09:08:39', 0, 0),
(230, 2, 61, 0, 'follow', NULL, '2025-10-25 18:37:19', 0, 0),
(234, 2, 61, 654, 'comment', NULL, '2025-10-26 06:08:52', 0, 0),
(235, 25, 61, 574, 'reply', NULL, '2025-10-26 06:11:17', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `post_on` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_unicode_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `user_id`, `post_on`) VALUES
(362, 2, '2021-07-08 08:31:07'),
(573, 2, '2021-08-25 03:03:39'),
(574, 2, '2021-08-26 03:04:53'),
(654, 2, '2021-10-11 02:19:45'),
(711, 25, '2021-10-12 18:29:24'),
(727, 25, '2022-01-12 17:48:33'),
(728, 58, '2022-01-12 17:51:22'),
(729, 55, '2021-09-29 18:00:04'),
(730, 40, '2022-01-13 09:04:13'),
(741, 61, '2025-10-27 17:35:34'),
(745, 61, '2025-11-11 19:42:17'),
(750, 61, '2025-11-06 15:37:54'),
(751, 61, '2025-11-06 11:41:55');

-- --------------------------------------------------------

--
-- Table structure for table `replies`
--

CREATE TABLE `replies` (
  `id` int(11) NOT NULL,
  `comment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reply` longtext NOT NULL,
  `time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_unicode_ci;

--
-- Dumping data for table `replies`
--

INSERT INTO `replies` (`id`, `comment_id`, `user_id`, `reply`, `time`) VALUES
(11, 44, 2, 'Oh yeah, it is!', '2021-05-01 02:21:51'),
(12, 44, 61, 'ye', '2025-10-26 06:11:17');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tweet_id` int(11) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','reviewed','resolved','dismissed') DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `resolved_by` int(11) DEFAULT NULL,
  `resolved_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`id`, `user_id`, `tweet_id`, `reason`, `description`, `status`, `admin_notes`, `resolved_by`, `resolved_at`, `created_at`) VALUES
(1, 61, 574, 'harassment', '', 'pending', NULL, NULL, NULL, '2025-10-29 07:24:07'),
(2, 62, 744, 'hate_speech', '', 'pending', NULL, NULL, NULL, '2025-10-31 07:04:31'),
(3, 61, 362, 'copyright', '', 'pending', NULL, NULL, NULL, '2025-11-11 19:42:55');

-- --------------------------------------------------------

--
-- Table structure for table `retweets`
--

CREATE TABLE `retweets` (
  `post_id` int(11) NOT NULL,
  `retweet_msg` longtext DEFAULT NULL,
  `tweet_id` int(11) DEFAULT NULL,
  `retweet_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_unicode_ci;

--
-- Dumping data for table `retweets`
--

INSERT INTO `retweets` (`post_id`, `retweet_msg`, `tweet_id`, `retweet_id`) VALUES
(711, '10/10', 654, NULL),
(751, NULL, 750, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `trends`
--

CREATE TABLE `trends` (
  `id` int(11) NOT NULL,
  `hashtag` varchar(140) NOT NULL,
  `created_on` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_unicode_ci;

--
-- Dumping data for table `trends`
--

INSERT INTO `trends` (`id`, `hashtag`, `created_on`) VALUES
(1, 'php', '2021-01-06 05:57:43'),
(4, 'hi', '2021-01-25 21:42:35'),
(5, 'alex', '2021-01-25 21:42:36'),
(6, '7oda', '2021-03-20 23:40:12'),
(9, 'js', '2021-04-02 03:24:28'),
(12, 'bro', '2021-04-02 03:31:38'),
(13, 'mysql', '2022-01-13 16:10:54'),
(14, 'explore', '2022-01-13 16:10:54'),
(15, 'fun', '2022-01-13 16:10:54');

-- --------------------------------------------------------

--
-- Table structure for table `tweets`
--

CREATE TABLE `tweets` (
  `post_id` int(11) NOT NULL,
  `status` longtext DEFAULT NULL,
  `img` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_unicode_ci;

--
-- Dumping data for table `tweets`
--

INSERT INTO `tweets` (`post_id`, `status`, `img`) VALUES
(362, '@testacc This tweet is for mentioning other users!', NULL),
(573, 'Sample Post with Image', 'tweet-60666d6b426a1.jpg'),
(574, '#php #mysql #jquery for Hashtags..', NULL),
(654, 'You can be everything. You can be the infinite amount of things that people are.', NULL),
(727, 'Unfortunately I feel like I have to master every skill I begin to enjoy!!!', NULL),
(728, 'You canâ€™t climb uphill by thinking downhill thoughts', NULL),
(729, 'I just wanna be nice, but some people are so ANNOYING.', NULL),
(730, 'The need for forgiveness is an illusion. There is nothing to forgive.', NULL),
(741, 'Here’s a **beautiful poem about Nature** 🌿✨\r\n\r\n---\r\n\r\n### **Whispers of Nature**\r\n\r\nThe morning sun begins to rise,\r\nPainting gold across the skies,\r\nThe gentle breeze through forests deep,\r\nAwakes the world from dreamful sleep.\r\n\r\nThe rivers hum their endless song,\r\nWhile mountains stand so proud and strong,\r\nThe flowers dance with colors bright,\r\nAnd stars await to crown the night.\r\n\r\nEach leaf, each drop, each grain of sand,\r\nIs crafted by a tender hand,\r\nOh Nature—pure, serene, and true,\r\nThe earth feels alive because of you.\r\n\r\nSo let us guard each tree and stream,\r\nProtect the land, the light, the dream,\r\nFor when we care, the world will show,\r\nA greener heart, a brighter glow. 🌏💚\r\n\r\n---\r\n\r\nWould you like me to make it **shorter (4 lines)** or **longer (for school recitation)**?', NULL),
(745, 'i will love you', NULL),
(750, 'knknkn..bkvvjhv jm', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(40) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(32) NOT NULL,
  `name` varchar(40) NOT NULL,
  `img` varchar(255) NOT NULL DEFAULT 'default.jpg',
  `imgCover` varchar(255) NOT NULL DEFAULT 'cover.png',
  `bio` varchar(140) NOT NULL DEFAULT '',
  `location` varchar(255) NOT NULL DEFAULT '',
  `website` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `name`, `img`, `imgCover`, `bio`, `location`, `website`) VALUES
(2, 'codeastro', 'astro@mail.com', 'e10adc3949ba59abbe56e057f20f883e', 'Astro', 'user-61def28fce0d7.jpg', 'cover.png', 'Well, nothing...', 'South Dakota', 'https://codeastro.com/'),
(5, 'matholiver', 'oliver@mail.com', 'e10adc3949ba59abbe56e057f20f883e', 'Matthew Oliver', 'user-61dee95ac1f87.jpg', 'cover.png', 'I make money in my sleep', '', ''),
(25, 'itselisagrnt', 'elisag@mail.com', 'e10adc3949ba59abbe56e057f20f883e', 'Elisa Grant', 'user-61deea3b7c7b3.jpg', 'cover.png', 'Midnight snacker', '', ''),
(27, 'viodaw', 'dawson@mail.com', 'e10adc3949ba59abbe56e057f20f883e', 'Viola Dawson', 'default.jpg', 'cover.png', 'Recovering cake addict', '', ''),
(33, 'floreshe', 'sherflore@mail.com', 'e10adc3949ba59abbe56e057f20f883e', 'Sherman Flores', 'default.jpg', 'cover.png', 'Smart. Strong. Silly. ', '', ''),
(37, 'edwhitney', 'whed@mail.com', 'e10adc3949ba59abbe56e057f20f883e', 'Whitney Edwards', 'default.jpg', 'cover.png', 'thank you, come again.', '', ''),
(40, 'wilburpotter', 'potterw@mail.com', 'e10adc3949ba59abbe56e057f20f883e', 'Wilbur Potter', 'user-61deeaa4a6178.jpg', 'cover.png', 'Having the time of my life', '', ''),
(41, 'miwalters', 'micwalters@mail.com', 'e10adc3949ba59abbe56e057f20f883e', 'Michelle Walters', 'user-61dee97f31d06.png', 'cover.png', 'Status Update: Currently hungry', '', ''),
(42, 'osborne', 'aosborne@mail.com', 'e10adc3949ba59abbe56e057f20f883e', 'Andy Osborne', 'default.jpg', 'user-609be2968c0b9.png', 'Don’t kale my vibe', '', ''),
(43, 'tiffiny', 'irvint@mail.com', 'e10adc3949ba59abbe56e057f20f883e', 'Tiffiny Irvin', 'default.jpg', 'cover.png', 'Seas the day', '', ''),
(44, 'mrbarton', 'compton@mail.com', 'e10adc3949ba59abbe56e057f20f883e', 'Barton Compton', 'user-61dee9ff9a415.jpg', 'cover.png', 'Doing better', '', ''),
(55, 'lynscott', 'scottt@mail.com', 'e10adc3949ba59abbe56e057f20f883e', 'Lynn Scott', 'user-61dee92566ea7.jpg', 'cover.png', 'Goal: bigger smile', '', ''),
(56, 'gregg58', 'gregcarr@mail.com', 'e10adc3949ba59abbe56e057f20f883e', 'Greg Carr', 'user-61dee9a0f415d.png', 'cover.png', 'I apologize for anything I post while hungry', '', ''),
(58, 'ralph', 'ralph5@mail.com', 'e10adc3949ba59abbe56e057f20f883e', 'Ralph Garza', 'user-61dda8817afe8.png', 'cover.png', 'In a world of worriers, be the warrior', '', ''),
(61, 'gaurav', 'gk@gmail.com', '209f66e701e4a817d8693c8edc8a5da6', 'Gaurav', 'default.jpg', 'cover.png', '', '', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `follow`
--
ALTER TABLE `follow`
  ADD PRIMARY KEY (`id`),
  ADD KEY `follower_id` (`follower_id`),
  ADD KEY `following_id` (`following_id`);

--
-- Indexes for table `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `likes_ibfk_2` (`post_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_ibfk_1` (`notify_for`),
  ADD KEY `notifications_ibfk_2` (`notify_from`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `replies`
--
ALTER TABLE `replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `comment_id` (`comment_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `tweet_id` (`tweet_id`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `retweets`
--
ALTER TABLE `retweets`
  ADD PRIMARY KEY (`post_id`) USING BTREE,
  ADD KEY `retweet_id` (`retweet_id`),
  ADD KEY `retweets_ibfk_2` (`tweet_id`);

--
-- Indexes for table `trends`
--
ALTER TABLE `trends`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `hashtag` (`hashtag`);

--
-- Indexes for table `tweets`
--
ALTER TABLE `tweets`
  ADD PRIMARY KEY (`post_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admin_logs`
--
ALTER TABLE `admin_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `follow`
--
ALTER TABLE `follow`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=197;

--
-- AUTO_INCREMENT for table `likes`
--
ALTER TABLE `likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=337;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=258;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=752;

--
-- AUTO_INCREMENT for table `replies`
--
ALTER TABLE `replies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `trends`
--
ALTER TABLE `trends`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `follow`
--
ALTER TABLE `follow`
  ADD CONSTRAINT `follow_ibfk_1` FOREIGN KEY (`follower_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `follow_ibfk_2` FOREIGN KEY (`following_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `likes`
--
ALTER TABLE `likes`
  ADD CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `likes_ibfk_2` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`notify_for`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`notify_from`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `replies`
--
ALTER TABLE `replies`
  ADD CONSTRAINT `replies_ibfk_1` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `replies_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `retweets`
--
ALTER TABLE `retweets`
  ADD CONSTRAINT `retweets_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `retweets_ibfk_2` FOREIGN KEY (`tweet_id`) REFERENCES `tweets` (`post_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `retweets_ibfk_3` FOREIGN KEY (`retweet_id`) REFERENCES `retweets` (`post_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tweets`
--
ALTER TABLE `tweets`
  ADD CONSTRAINT `tweets_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
