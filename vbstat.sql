-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主機： 127.0.0.1
-- 產生時間： 2025-03-11 16:39:15
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
(4, '殺波Out', '進攻', -1, NULL),
(5, '殺波under', '進攻', -1, NULL),
(6, '破壞性進攻', '進攻', 0, 1),
(7, '攔網', '攔網', 1, NULL),
(8, '多人攔網', '攔網', 0.5, NULL),
(9, '攔網', '攔網', -1, NULL),
(10, '有效<br>攔網', '攔網', 0, 5),
(11, '多人攔網', '攔網', 0, 7),
(12, '一傳過網', '一傳', 1, NULL),
(13, '一傳<br>到位', '一傳', 0, 8),
(14, '一傳<br>唔到位', '一傳', 0, 9),
(15, '一傳<br>守失', '一傳', -1, NULL),
(16, '一傳<br>掂唔到', '一傳', -1, NULL),
(17, 'Cover到位', 'Cover', 0, 14),
(18, 'Cover唔到位', 'Cover', 0, 15),
(19, 'Cover失分', 'Cover', -1, NULL),
(20, '無跟cover', 'Cover', -1, NULL),
(21, 'Ace', '發球', 1, NULL),
(22, '發球', '發球', 0, 4),
(23, '發球Out', '發球', -1, NULL),
(24, '發球<br>落網', '發球', -1, NULL),
(25, 'Set<br>到位', 'Setting', 0, 10),
(26, 'Set<br>唔到位', 'Setting', 0, 11),
(27, 'Set波', 'Setting', -1, NULL),
(28, '其他<br>失誤', '其他失誤', -1, NULL),
(29, '對方失誤', '對方失誤', 1, NULL),
(30, '發球破壞一傳', '發球', 0, 3),
(31, 'Free波 到位', 'Free ball', 0, 12),
(32, 'Free波 唔到位', 'Free ball', 0, 13),
(33, '無效<br>攔網', '攔網', 0, 6),
(34, '無效<br>進攻', '進攻', 0, 2);

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
(71, 1, '2025-02-05', 'Friendly', 1, '甲一', 1, '');

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
(0, '對方球員', NULL),
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
(548, 41, 0, 7, 29),
(549, 41, 2, 2, 11),
(550, 41, 10, 3, 4),
(551, 41, 0, 7, 29),
(552, 41, 10, 3, 6),
(553, 41, 12, 4, 13),
(554, 41, 13, 3, 10),
(555, 41, 2, 2, 10),
(556, 41, 1, 1, 3),
(557, 41, 10, 3, 7),
(558, 41, 15, 1, 1),
(559, 41, 14, 2, 16),
(560, 41, 14, 2, 9),
(561, 41, 11, 5, 20),
(563, 42, 2, 2, 22),
(564, 42, 18, 2, 13),
(565, 42, 12, 4, 25),
(566, 42, 1, 1, 6),
(567, 42, 18, 5, 14),
(568, 42, 12, 4, 25),
(569, 42, 1, 1, 1),
(571, 41, 0, 7, 29),
(572, 41, 1, 1, 8),
(573, 41, 1, 1, 8),
(574, 41, 1, 1, 8),
(575, 41, 1, 1, 8),
(576, 41, 1, 1, 8),
(577, 41, 1, 1, 8),
(578, 41, 1, 1, 8),
(579, 41, 1, 1, 1),
(580, 41, 1, 1, 8),
(581, 41, 10, 2, 8),
(582, 43, 1, 1, 8),
(583, 43, 10, 2, 8),
(584, 43, 10, 2, 1),
(585, 43, 1, 1, 11),
(586, 43, 1, 1, 6),
(587, 43, 12, 4, 4),
(588, 43, 2, 2, 2),
(589, 43, 13, 1, 1),
(590, 43, 14, 4, 19),
(591, 43, 18, 5, 12),
(592, 43, 0, 7, 29),
(593, 41, 1, 1, 30),
(594, 43, 1, 1, 8),
(596, 43, 1, 1, 8),
(598, 41, 10, 2, 8),
(599, 41, 12, 4, 8),
(600, 41, 2, 2, 31),
(601, 41, 0, 7, 29);

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
(7, '對方球員');

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
(142, 1, 4),
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
(162, 1, 24),
(163, 1, 25),
(164, 1, 26),
(165, 1, 27),
(166, 1, 28),
(167, 2, 1),
(168, 2, 2),
(169, 2, 3),
(170, 2, 4),
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
(190, 2, 24),
(191, 2, 25),
(192, 2, 26),
(193, 2, 27),
(194, 2, 28),
(195, 3, 1),
(196, 3, 2),
(197, 3, 3),
(198, 3, 4),
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
(218, 3, 24),
(219, 3, 25),
(220, 3, 26),
(221, 3, 27),
(222, 3, 28),
(223, 4, 1),
(224, 4, 2),
(225, 4, 3),
(226, 4, 4),
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
(246, 4, 24),
(247, 4, 25),
(248, 4, 26),
(249, 4, 27),
(250, 4, 28),
(251, 5, 2),
(252, 5, 3),
(253, 5, 4),
(254, 5, 5),
(255, 5, 6),
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
(272, 6, 4),
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
(298, 6, 34);

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
(425, 548, 1.0, 0.0),
(426, 549, 1.0, 0.0),
(427, 550, 1.0, 1.0),
(428, 551, 2.0, 1.0),
(429, 552, 2.0, 1.0),
(430, 553, 2.0, 1.0),
(431, 554, 2.0, 1.0),
(432, 555, 2.0, 1.0),
(433, 556, 3.0, 1.0),
(434, 557, 4.0, 1.0),
(435, 558, 5.0, 1.0),
(436, 559, 5.0, 2.0),
(437, 560, 5.0, 3.0),
(438, 561, 5.0, 4.0),
(440, 563, 0.0, 0.0),
(441, 564, 0.0, 0.0),
(442, 565, 0.0, 0.0),
(443, 566, 0.0, 0.0),
(444, 567, 0.0, 0.0),
(445, 568, 0.0, 0.0),
(446, 569, 1.0, 0.0),
(448, 571, 6.0, 4.0),
(449, 572, 6.0, 4.0),
(450, 573, 6.0, 4.0),
(451, 574, 6.0, 4.0),
(452, 577, 6.5, 4.0),
(453, 578, 7.0, 4.0),
(454, 579, 8.0, 4.0),
(455, 580, 8.5, 4.0),
(456, 581, 9.0, 4.0),
(457, 582, 0.5, 0.0),
(458, 583, 1.0, 0.0),
(459, 584, 2.0, 0.0),
(460, 585, 2.0, 0.0),
(461, 586, 2.0, 0.0),
(462, 587, 2.0, 1.0),
(463, 588, 3.0, 1.0),
(464, 589, 4.0, 1.0),
(465, 590, 4.0, 2.0),
(466, 591, 5.0, 2.0),
(467, 592, 6.0, 2.0),
(468, 593, 9.0, 4.0),
(469, 594, 6.5, 2.0),
(471, 596, 7.0, 2.0),
(473, 598, 9.5, 4.0),
(474, 599, 10.0, 4.0),
(475, 600, 10.0, 4.0),
(476, 601, 11.0, 4.0);

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
(44, 57, 2, 25),
(45, 62, 2, 15),
(48, 66, 1, 25);

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
(19, 'diu', 1);

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
  MODIFY `aid` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `matches`
--
ALTER TABLE `matches`
  MODIFY `mid` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `player`
--
ALTER TABLE `player`
  MODIFY `pid` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `result`
--
ALTER TABLE `result`
  MODIFY `resid` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=602;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `role`
--
ALTER TABLE `role`
  MODIFY `rid` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `role_action`
--
ALTER TABLE `role_action`
  MODIFY `raid` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=299;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `scoreboard`
--
ALTER TABLE `scoreboard`
  MODIFY `sbid` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=477;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `sets`
--
ALTER TABLE `sets`
  MODIFY `sid` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `team`
--
ALTER TABLE `team`
  MODIFY `tid` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

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
