-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- ホスト: 127.0.0.1
-- 生成日時: 2024-07-24 22:34:09
-- サーバのバージョン： 10.4.32-MariaDB
-- PHP のバージョン: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- データベース: `gs_db5`
--

-- --------------------------------------------------------

--
-- テーブルの構造 `gs_worry`
--

CREATE TABLE `gs_worry` (
  `username` varchar(64) NOT NULL,
  `id` int(12) NOT NULL,
  `worry` text NOT NULL,
  `date` datetime NOT NULL,
  `proposal_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのデータのダンプ `gs_worry`
--

INSERT INTO `gs_worry` (`username`, `id`, `worry`, `date`, `proposal_count`) VALUES
('test3', 2, 'test3', '2024-07-20 23:48:26', 0),
('test3', 3, '洋書を読みたいが、何を読んだらいいのかわからない', '2024-07-21 13:49:22', 0),
('', 4, '', '2024-07-23 08:37:55', 0),
('', 5, '', '2024-07-23 08:38:39', 1);

--
-- ダンプしたテーブルのインデックス
--

--
-- テーブルのインデックス `gs_worry`
--
ALTER TABLE `gs_worry`
  ADD PRIMARY KEY (`id`);

--
-- ダンプしたテーブルの AUTO_INCREMENT
--

--
-- テーブルの AUTO_INCREMENT `gs_worry`
--
ALTER TABLE `gs_worry`
  MODIFY `id` int(12) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
