-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- ホスト: 127.0.0.1
-- 生成日時: 2024-07-24 22:34:19
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
-- テーブルの構造 `gs_worry_comments`
--

CREATE TABLE `gs_worry_comments` (
  `id` int(12) NOT NULL,
  `worry_id` int(12) NOT NULL,
  `username` varchar(64) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのデータのダンプ `gs_worry_comments`
--

INSERT INTO `gs_worry_comments` (`id`, `worry_id`, `username`, `comment`, `created_at`) VALUES
(1, 2, 'gs_kadai', 'aaaa', '2024-07-21 03:45:18'),
(2, 2, 'gs_kadai', 'aaaa', '2024-07-21 03:45:30'),
(3, 2, 'gs_kadai', 'あいうえお', '2024-07-21 03:46:50'),
(4, 2, 'gs_kadai', 'できるか', '2024-07-21 03:56:22'),
(5, 2, 'gs_kadai', 'できないか', '2024-07-21 03:56:31'),
(6, 2, 'gs_kadai', 'どうなのよ', '2024-07-21 04:04:02'),
(8, 2, 'test3', 'test3', '2024-07-21 04:26:27'),
(9, 3, 'gs_kadai', 'どのようなジャンルが好きですか？', '2024-07-21 04:52:14'),
(10, 3, 'test3', '冒険ものが好きです', '2024-07-21 04:53:10'),
(11, 3, 'gs_kadai', '失礼かもれないですが、英語のレベルはどの程度でしょうか？', '2024-07-21 04:57:10'),
(12, 3, 'test3', '英検2級レベルです', '2024-07-21 04:57:49');

--
-- ダンプしたテーブルのインデックス
--

--
-- テーブルのインデックス `gs_worry_comments`
--
ALTER TABLE `gs_worry_comments`
  ADD PRIMARY KEY (`id`);

--
-- ダンプしたテーブルの AUTO_INCREMENT
--

--
-- テーブルの AUTO_INCREMENT `gs_worry_comments`
--
ALTER TABLE `gs_worry_comments`
  MODIFY `id` int(12) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
