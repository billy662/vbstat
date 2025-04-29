-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主機： 127.0.0.1
-- 產生時間： 2025-04-28 19:21:13
-- 伺服器版本： 10.4.32-MariaDB
-- PHP 版本： 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 資料庫： `vbstat`
--

-- --------------------------------------------------------

--
-- 資料表結構 `accounts`
--

CREATE TABLE `accounts` (
  `acid` int(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `pw` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `accounts`
--

INSERT INTO `accounts` (`acid`, `username`, `pw`) VALUES
(1, 'Easy', NULL),
(2, 'DF', NULL);

-- --------------------------------------------------------

--
-- 資料表結構 `action`
--

CREATE TABLE `action` (
  `aid` int(255) NOT NULL,
  `aname` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `score` float NOT NULL,
  `sorting` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `action`
--

INSERT INTO `action` (`aid`, `aname`, `category`, `score`, `sorting`) VALUES
(1, '殺波', '進攻', 1, NULL),
(2, 'Tip波', '進攻', 1, NULL),
(3, '二段', '進攻', 1, NULL),
(5, '進攻<br>失分', '進攻', -1, 8),
(6, '破壞性進攻', '進攻', 0, 16),
(7, '攔網+', '攔網', 1, NULL),
(8, '多人<br/>攔網', '攔網', 0.5, NULL),
(9, '攔網-', '攔網', -1, 7),
(10, '有效<br>攔網', '攔網', 0, 11),
(11, '多人攔網', '攔網', 0, 13),
(12, '一傳<br/>過網', '一傳', 1, NULL),
(13, '接發<br>到位', '一傳', 0, 9),
(14, '接發<br>唔到位', '一傳', 0, 10),
(15, '接發<br>失分', '一傳', -1, 6),
(16, '守殺<br>失分', '一傳', -1, 1),
(17, 'Cover到位', '一傳', 0, 7),
(18, 'Cover唔到位', '一傳', 0, 8),
(19, 'Cover失分', '一傳', -1, 4),
(20, '無跟cover', '一傳', -1, 5),
(21, 'Ace', '發球', 1, NULL),
(22, '發球', '發球', 0, 99),
(23, '發球<br>失分', '發球', -1, 10),
(25, 'Set<br>到位', 'Setting', 0, 14),
(26, 'Set<br>唔到位', 'Setting', 0, 15),
(27, 'Set波', 'Setting', -1, 9),
(28, '其他<br>失誤', '其他失誤', -1, 100),
(29, '對方<br>失誤', '對方失誤', 1, NULL),
(30, '發球破壞一傳', '發球', 0, 100),
(31, 'Free波<br/>到位', '一傳', 0, 5),
(32, 'Free波<br/>唔到位', '一傳', 0, 6),
(33, '無效<br>攔網', '攔網', 0, 12),
(34, '無效<br>進攻', '進攻', 0, 17),
(35, '守殺<br/>到位', '一傳', 0, 1),
(36, '守殺<br/>唔到位', '一傳', 0, 2),
(37, '守tip<br/>到位', '一傳', 0, 3),
(38, '守tip<br/>唔到位', '一傳', 0, 4),
(39, '守tip<br>失分', '一傳', -1, 2),
(40, 'Free波<br>失分', '一傳', -1, 3);

-- --------------------------------------------------------

--
-- 資料表結構 `matches`
--

CREATE TABLE `matches` (
  `mid` int(255) NOT NULL,
  `acid` int(255) NOT NULL,
  `date` date NOT NULL,
  `type` varchar(255) NOT NULL,
  `tid` int(255) NOT NULL,
  `tgrade` varchar(255) NOT NULL,
  `trate` int(255) NOT NULL,
  `youtube` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `matches`
--

INSERT INTO `matches` (`mid`, `acid`, `date`, `type`, `tid`, `tgrade`, `trate`, `youtube`) VALUES
(57, 1, '2025-02-12', 'Friendly', 1, '甲一', 9, 'http://missav.ws'),
(58, 1, '2025-02-26', '港運', 15, '甲二', 6, NULL),
(62, 1, '2025-02-10', '港運', 15, '乙組', 10, 'https://www.youtube.com/playlist?list=PLijmxDWKKo1d7pm3DSq01PSbche9k_X3s'),
(66, 2, '2025-02-12', 'Friendly', 1, '甲一', 1, NULL),
(74, 1, '2025-03-11', '聯賽', 19, '其他', 2, 'http://missav.ws');

-- --------------------------------------------------------

--
-- 資料表結構 `player`
--

CREATE TABLE `player` (
  `pid` int(255) NOT NULL,
  `pname` varchar(255) NOT NULL,
  `acid` int(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `player`
--

INSERT INTO `player` (`pid`, `pname`, `acid`) VALUES
(0, '對方<br>球員', NULL),
(1, 'cy', 1),
(2, 'Tygan', 1),
(10, 'Pak', 1),
(11, 'Nick', 1),
(12, 'Ocean', 1),
(13, '聰', 1),
(14, '卡', 1),
(15, '峻', 1),
(16, 'PK', 1),
(17, '樹', 1),
(18, '羊', 1),
(19, 'Hei', 1),
(23, 'fff', 2);

-- --------------------------------------------------------

--
-- 資料表結構 `result`
--

CREATE TABLE `result` (
  `resid` int(255) NOT NULL,
  `sid` int(255) NOT NULL,
  `pid` int(255) NOT NULL,
  `rid` int(255) NOT NULL,
  `aid` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `result`
--

INSERT INTO `result` (`resid`, `sid`, `pid`, `rid`, `aid`) VALUES
(1, 41, 1, 1, 37),
(2, 41, 10, 2, 38),
(3, 41, 10, 2, 2),
(4, 41, 14, 2, 7),
(5, 41, 13, 2, 2),
(6, 41, 2, 2, 39),
(7, 41, 16, 1, 15),
(8, 41, 15, 1, 39),
(9, 41, 12, 6, 1),
(10, 41, 0, 7, 29),
(11, 41, 11, 5, 5),
(12, 41, 16, 1, 12),
(13, 41, 10, 2, 7),
(14, 41, 14, 2, 5),
(15, 41, 11, 5, 26),
(16, 41, 12, 4, 11),
(17, 41, 13, 2, 20),
(18, 41, 2, 2, 5),
(19, 41, 10, 2, 27),
(20, 41, 12, 4, 16),
(21, 41, 12, 4, 39),
(22, 41, 12, 4, 40),
(23, 41, 12, 4, 19),
(24, 41, 12, 4, 20),
(25, 41, 12, 4, 15),
(26, 41, 14, 2, 9),
(27, 41, 11, 5, 5),
(28, 41, 13, 2, 27),
(29, 41, 15, 1, 23),
(30, 41, 17, 4, 28),
(31, 41, 10, 2, 1),
(32, 41, 15, 1, 2),
(33, 41, 14, 2, 3),
(34, 41, 1, 1, 7),
(35, 41, 13, 2, 8),
(36, 41, 14, 2, 12),
(37, 41, 15, 1, 21),
(38, 41, 14, 2, 21),
(39, 41, 0, 7, 29),
(40, 41, 10, 2, 8),
(41, 41, 1, 1, 35),
(42, 41, 10, 2, 36),
(43, 41, 11, 5, 37),
(44, 41, 14, 2, 38),
(45, 41, 15, 1, 31),
(46, 41, 11, 5, 32),
(47, 41, 12, 4, 17),
(48, 41, 2, 2, 18),
(49, 41, 17, 4, 13),
(50, 41, 14, 2, 14),
(51, 41, 10, 2, 10),
(52, 41, 17, 4, 33),
(53, 41, 14, 2, 11),
(54, 41, 11, 5, 25),
(55, 41, 2, 2, 26),
(56, 41, 1, 1, 6),
(57, 41, 10, 2, 34),
(58, 41, 13, 2, 22),
(59, 41, 12, 4, 30),
(60, 41, 1, 1, 22),
(61, 41, 14, 2, 33),
(62, 41, 11, 5, 38),
(63, 41, 12, 4, 25),
(64, 41, 2, 2, 6),
(65, 41, 14, 2, 11),
(66, 41, 12, 4, 11),
(67, 41, 16, 1, 31),
(68, 41, 12, 4, 26),
(69, 41, 14, 2, 2),
(70, 41, 1, 1, 13),
(71, 41, 17, 4, 25),
(72, 41, 2, 2, 2),
(73, 41, 10, 2, 30),
(74, 41, 11, 5, 32),
(75, 41, 12, 4, 26),
(77, 41, 1, 1, 5),
(78, 41, 18, 5, 13),
(79, 41, 12, 4, 26),
(80, 41, 1, 1, 2),
(81, 41, 2, 2, 22),
(82, 41, 12, 4, 38),
(83, 41, 11, 5, 25),
(84, 41, 1, 1, 1),
(85, 52, 1, 1, 16),
(86, 52, 0, 7, 29),
(87, 42, 11, 5, 2);

-- --------------------------------------------------------

--
-- 資料表結構 `role`
--

CREATE TABLE `role` (
  `rid` int(255) NOT NULL,
  `rName` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `role`
--

INSERT INTO `role` (`rid`, `rName`) VALUES
(1, '鎚'),
(2, 'Middle'),
(3, '後二'),
(4, 'Setter'),
(5, 'Libero'),
(6, '後排'),
(7, '對方<br>球員');

-- --------------------------------------------------------

--
-- 資料表結構 `role_action`
--

CREATE TABLE `role_action` (
  `raid` int(255) NOT NULL,
  `rid` int(255) NOT NULL,
  `aid` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `role_action`
--

INSERT INTO `role_action` (`raid`, `rid`, `aid`) VALUES
(139, 1, 1),
(140, 1, 2),
(141, 1, 3),
(143, 1, 5),
(144, 1, 6),
(145, 1, 7),
(146, 1, 8),
(147, 1, 9),
(148, 1, 10),
(149, 1, 11),
(150, 1, 12),
(151, 1, 13),
(152, 1, 14),
(153, 1, 15),
(154, 1, 16),
(155, 1, 17),
(156, 1, 18),
(157, 1, 19),
(158, 1, 20),
(159, 1, 21),
(160, 1, 22),
(161, 1, 23),
(163, 1, 25),
(164, 1, 26),
(165, 1, 27),
(166, 1, 28),
(167, 2, 1),
(168, 2, 2),
(169, 2, 3),
(171, 2, 5),
(172, 2, 6),
(173, 2, 7),
(174, 2, 8),
(175, 2, 9),
(176, 2, 10),
(177, 2, 11),
(178, 2, 12),
(179, 2, 13),
(180, 2, 14),
(181, 2, 15),
(182, 2, 16),
(183, 2, 17),
(184, 2, 18),
(185, 2, 19),
(186, 2, 20),
(187, 2, 21),
(188, 2, 22),
(189, 2, 23),
(191, 2, 25),
(192, 2, 26),
(193, 2, 27),
(194, 2, 28),
(195, 3, 1),
(196, 3, 2),
(197, 3, 3),
(199, 3, 5),
(200, 3, 6),
(201, 3, 7),
(202, 3, 8),
(203, 3, 9),
(204, 3, 10),
(205, 3, 11),
(206, 3, 12),
(207, 3, 13),
(208, 3, 14),
(209, 3, 15),
(210, 3, 16),
(211, 3, 17),
(212, 3, 18),
(213, 3, 19),
(214, 3, 20),
(215, 3, 21),
(216, 3, 22),
(217, 3, 23),
(219, 3, 25),
(220, 3, 26),
(221, 3, 27),
(222, 3, 28),
(223, 4, 1),
(224, 4, 2),
(225, 4, 3),
(227, 4, 5),
(228, 4, 6),
(229, 4, 7),
(230, 4, 8),
(231, 4, 9),
(232, 4, 10),
(233, 4, 11),
(234, 4, 12),
(235, 4, 13),
(236, 4, 14),
(237, 4, 15),
(238, 4, 16),
(239, 4, 17),
(240, 4, 18),
(241, 4, 19),
(242, 4, 20),
(243, 4, 21),
(244, 4, 22),
(245, 4, 23),
(247, 4, 25),
(248, 4, 26),
(249, 4, 27),
(250, 4, 28),
(251, 5, 2),
(252, 5, 3),
(254, 5, 5),
(256, 5, 12),
(257, 5, 13),
(258, 5, 14),
(259, 5, 15),
(260, 5, 16),
(261, 5, 17),
(262, 5, 18),
(263, 5, 19),
(264, 5, 20),
(265, 5, 25),
(266, 5, 26),
(267, 5, 27),
(268, 5, 28),
(269, 6, 1),
(270, 6, 2),
(271, 6, 3),
(273, 6, 5),
(274, 6, 6),
(275, 7, 29),
(276, 2, 30),
(277, 4, 30),
(278, 3, 30),
(279, 1, 30),
(280, 1, 31),
(281, 1, 32),
(282, 2, 31),
(283, 2, 32),
(284, 3, 31),
(285, 3, 32),
(286, 4, 31),
(287, 4, 32),
(288, 5, 31),
(289, 5, 32),
(290, 2, 33),
(291, 2, 34),
(292, 4, 33),
(293, 4, 34),
(294, 3, 33),
(295, 3, 34),
(296, 1, 33),
(297, 1, 34),
(298, 6, 34),
(299, 1, 35),
(300, 1, 36),
(301, 1, 37),
(302, 1, 38),
(303, 2, 35),
(304, 2, 36),
(305, 2, 37),
(306, 2, 38),
(307, 3, 35),
(308, 3, 36),
(309, 3, 37),
(310, 3, 38),
(311, 4, 35),
(312, 4, 36),
(313, 4, 37),
(314, 4, 38),
(315, 5, 35),
(316, 5, 36),
(317, 5, 37),
(318, 5, 38),
(319, 1, 39),
(320, 1, 40),
(321, 2, 39),
(322, 2, 40),
(323, 3, 39),
(324, 3, 40),
(325, 4, 39),
(326, 4, 40),
(327, 5, 39),
(328, 5, 40);

-- --------------------------------------------------------

--
-- 資料表結構 `scoreboard`
--

CREATE TABLE `scoreboard` (
  `sbid` int(255) NOT NULL,
  `resid` int(255) NOT NULL,
  `scored` float(4,1) NOT NULL,
  `lost` float(4,1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `scoreboard`
--

INSERT INTO `scoreboard` (`sbid`, `resid`, `scored`, `lost`) VALUES
(1, 1, 0.0, 0.0),
(2, 2, 0.0, 0.0),
(3, 3, 1.0, 0.0),
(4, 4, 2.0, 0.0),
(5, 5, 3.0, 0.0),
(6, 6, 3.0, 1.0),
(7, 7, 3.0, 2.0),
(8, 8, 3.0, 3.0),
(9, 9, 4.0, 3.0),
(10, 10, 5.0, 3.0),
(11, 11, 5.0, 4.0),
(12, 12, 6.0, 4.0),
(13, 13, 7.0, 4.0),
(14, 14, 7.0, 5.0),
(15, 15, 7.0, 5.0),
(16, 16, 7.0, 5.0),
(17, 17, 7.0, 6.0),
(18, 18, 7.0, 7.0),
(19, 19, 7.0, 8.0),
(20, 20, 7.0, 9.0),
(21, 21, 7.0, 10.0),
(22, 22, 7.0, 11.0),
(23, 23, 7.0, 12.0),
(24, 24, 7.0, 13.0),
(25, 25, 7.0, 14.0),
(26, 26, 7.0, 15.0),
(27, 27, 7.0, 16.0),
(28, 28, 7.0, 17.0),
(29, 29, 7.0, 18.0),
(30, 30, 7.0, 19.0),
(31, 31, 8.0, 19.0),
(32, 32, 9.0, 19.0),
(33, 33, 10.0, 19.0),
(34, 34, 11.0, 19.0),
(35, 35, 11.5, 19.0),
(36, 36, 12.5, 19.0),
(37, 37, 13.5, 19.0),
(38, 38, 14.5, 19.0),
(39, 39, 15.5, 19.0),
(40, 40, 16.0, 19.0),
(41, 41, 16.0, 19.0),
(42, 42, 16.0, 19.0),
(43, 43, 16.0, 19.0),
(44, 44, 16.0, 19.0),
(45, 45, 16.0, 19.0),
(46, 46, 16.0, 19.0),
(47, 47, 16.0, 19.0),
(48, 48, 16.0, 19.0),
(49, 49, 16.0, 19.0),
(50, 50, 16.0, 19.0),
(51, 51, 16.0, 19.0),
(52, 52, 16.0, 19.0),
(53, 53, 16.0, 19.0),
(54, 54, 16.0, 19.0),
(55, 55, 16.0, 19.0),
(56, 56, 16.0, 19.0),
(57, 57, 16.0, 19.0),
(58, 58, 16.0, 19.0),
(59, 59, 16.0, 19.0),
(60, 60, 16.0, 19.0),
(61, 61, 16.0, 19.0),
(62, 62, 16.0, 19.0),
(63, 63, 16.0, 19.0),
(64, 64, 16.0, 19.0),
(65, 65, 16.0, 19.0),
(66, 66, 16.0, 19.0),
(67, 67, 16.0, 19.0),
(68, 68, 16.0, 19.0),
(69, 69, 17.0, 19.0),
(70, 70, 17.0, 19.0),
(71, 71, 17.0, 19.0),
(72, 72, 18.0, 19.0),
(73, 73, 18.0, 19.0),
(74, 74, 18.0, 19.0),
(75, 75, 18.0, 19.0),
(77, 77, 18.0, 20.0),
(78, 78, 18.0, 20.0),
(79, 79, 18.0, 20.0),
(80, 80, 19.0, 20.0),
(81, 81, 19.0, 20.0),
(82, 82, 19.0, 20.0),
(83, 83, 19.0, 20.0),
(84, 84, 20.0, 20.0),
(85, 85, 0.0, 1.0),
(86, 86, 1.0, 1.0),
(87, 87, 1.0, 0.0);

-- --------------------------------------------------------

--
-- 資料表結構 `sets`
--

CREATE TABLE `sets` (
  `sid` int(255) NOT NULL,
  `mid` int(255) NOT NULL,
  `setNo` int(255) NOT NULL,
  `points` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `sets`
--

INSERT INTO `sets` (`sid`, `mid`, `setNo`, `points`) VALUES
(41, 57, 1, 25),
(42, 58, 1, 25),
(43, 62, 1, 25),
(45, 62, 2, 15),
(48, 66, 1, 25),
(50, 74, 1, 15),
(52, 57, 2, 15);

-- --------------------------------------------------------

--
-- 資料表結構 `team`
--

CREATE TABLE `team` (
  `tid` int(255) NOT NULL,
  `tname` varchar(255) NOT NULL,
  `acid` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `team`
--

INSERT INTO `team` (`tid`, `tname`, `acid`) VALUES
(1, 'DF', 1),
(15, '港隊', 1),
(17, 'Tygan testing team', 1),
(19, 'diu', 1),
(24, 'JJ', 1),
(26, 'dllmcfh', 2);

--
-- 已傾印資料表的索引
--

--
-- 資料表索引 `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`acid`);

--
-- 資料表索引 `action`
--
ALTER TABLE `action`
  ADD PRIMARY KEY (`aid`);

--
-- 資料表索引 `matches`
--
ALTER TABLE `matches`
  ADD PRIMARY KEY (`mid`),
  ADD KEY `fk_team_tid` (`tid`),
  ADD KEY `fk_accounts_acid` (`acid`);

--
-- 資料表索引 `player`
--
ALTER TABLE `player`
  ADD PRIMARY KEY (`pid`),
  ADD KEY `fk_accounts_acid1` (`acid`);

--
-- 資料表索引 `result`
--
ALTER TABLE `result`
  ADD PRIMARY KEY (`resid`),
  ADD KEY `fk_set_sid1` (`sid`),
  ADD KEY `fk_player_pid1` (`pid`),
  ADD KEY `fk_role_rid1` (`rid`),
  ADD KEY `fk_action_aid1` (`aid`);

--
-- 資料表索引 `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`rid`);

--
-- 資料表索引 `role_action`
--
ALTER TABLE `role_action`
  ADD PRIMARY KEY (`raid`),
  ADD KEY `fk_role_rid` (`rid`),
  ADD KEY `fk_action_aid` (`aid`);

--
-- 資料表索引 `scoreboard`
--
ALTER TABLE `scoreboard`
  ADD PRIMARY KEY (`sbid`),
  ADD KEY `fk_result_resid` (`resid`);

--
-- 資料表索引 `sets`
--
ALTER TABLE `sets`
  ADD PRIMARY KEY (`sid`),
  ADD KEY `fk_match_mid` (`mid`);

--
-- 資料表索引 `team`
--
ALTER TABLE `team`
  ADD PRIMARY KEY (`tid`);

--
-- 在傾印的資料表使用自動遞增(AUTO_INCREMENT)
--

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `accounts`
--
ALTER TABLE `accounts`
  MODIFY `acid` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `action`
--
ALTER TABLE `action`
  MODIFY `aid` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `matches`
--
ALTER TABLE `matches`
  MODIFY `mid` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `player`
--
ALTER TABLE `player`
  MODIFY `pid` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `result`
--
ALTER TABLE `result`
  MODIFY `resid` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `role`
--
ALTER TABLE `role`
  MODIFY `rid` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `role_action`
--
ALTER TABLE `role_action`
  MODIFY `raid` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=329;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `scoreboard`
--
ALTER TABLE `scoreboard`
  MODIFY `sbid` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `sets`
--
ALTER TABLE `sets`
  MODIFY `sid` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `team`
--
ALTER TABLE `team`
  MODIFY `tid` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- 已傾印資料表的限制式
--

--
-- 資料表的限制式 `matches`
--
ALTER TABLE `matches`
  ADD CONSTRAINT `fk_accounts_acid` FOREIGN KEY (`acid`) REFERENCES `accounts` (`acid`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_team_tid` FOREIGN KEY (`tid`) REFERENCES `team` (`tid`);

--
-- 資料表的限制式 `player`
--
ALTER TABLE `player`
  ADD CONSTRAINT `fk_accounts_acid1` FOREIGN KEY (`acid`) REFERENCES `accounts` (`acid`);

--
-- 資料表的限制式 `result`
--
ALTER TABLE `result`
  ADD CONSTRAINT `fk_action_aid1` FOREIGN KEY (`aid`) REFERENCES `action` (`aid`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_player_pid1` FOREIGN KEY (`pid`) REFERENCES `player` (`pid`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_role_rid1` FOREIGN KEY (`rid`) REFERENCES `role` (`rid`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_set_sid1` FOREIGN KEY (`sid`) REFERENCES `sets` (`sid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- 資料表的限制式 `role_action`
--
ALTER TABLE `role_action`
  ADD CONSTRAINT `fk_action_aid` FOREIGN KEY (`aid`) REFERENCES `action` (`aid`),
  ADD CONSTRAINT `fk_role_rid` FOREIGN KEY (`rid`) REFERENCES `role` (`rid`);

--
-- 資料表的限制式 `scoreboard`
--
ALTER TABLE `scoreboard`
  ADD CONSTRAINT `fk_result_resid` FOREIGN KEY (`resid`) REFERENCES `result` (`resid`);

--
-- 資料表的限制式 `sets`
--
ALTER TABLE `sets`
  ADD CONSTRAINT `fk_match_mid` FOREIGN KEY (`mid`) REFERENCES `matches` (`mid`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
