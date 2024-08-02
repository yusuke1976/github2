-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- ホスト: 127.0.0.1
-- 生成日時: 2024-07-24 22:34:00
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
-- テーブルの構造 `gs_user_table5`
--

CREATE TABLE `gs_user_table5` (
  `id` int(12) NOT NULL,
  `username` varchar(64) NOT NULL,
  `email` varchar(128) NOT NULL,
  `password` varchar(64) NOT NULL,
  `concern` varchar(64) DEFAULT NULL,
  `genre` varchar(64) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `life_flg` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのデータのダンプ `gs_user_table5`
--

INSERT INTO `gs_user_table5` (`id`, `username`, `email`, `password`, `concern`, `genre`, `profile_image`, `life_flg`) VALUES
(7, 'gs_kadai', 'test@example.jp', '$2y$10$I7mwydyxBdLDUPa50bw95ukttYBWa8zmGV9InAwDKRrZMjyL/ZHfe', 'work', 'selfhelp', '66988e34af6c1_about_03.jpg', 0),
(11, 'test2', 'aaaa@aaaa.jp', '$2y$10$iHA2ezAYZyWo8Ptg1oF5Y.cldSh8JtCuEHXy5e9YFOYjMyt7nwXQu', 'work', 'selfhelp', '669a7d63a2f36_cheese3.jpg', 0),
(13, 'test3', 'test3@example.jp', '$2y$10$w6rF1hQBEA4S/wH3z/Pc2ulkt/6N4yPDRRYn.bz2yKukcEWh28IHq', 'work', 'selfhelp', '669bd3675a08e_course_02.jpg', 0),
(14, 'admin', 'admin@example.jp', '$2y$10$KrFpwEvCyP/wNLN9FxJRiOzXxrNS8qwUspxdnXGla12/uuxK.REAa', 'work', 'selfhelp', '6690d58f5c88c_about_03.jpg', 0),
(15, 'test100', 'test01@example.jp', '$2y$10$VuH2hq9CTlzQYdeqTHZDRuwL7A.7CjhqXqakUuY4syM6Pxvx6fQ16', 'work', 'psychology', NULL, 0),
(16, 'test500', 'test500@example.jp', '$2y$10$NxB39zPGRJ5UwNhas.IfeeDp0wEwPe9fUjBu0BxW/fqOntAUglV.q', 'relationship', 'psychology', NULL, 0),
(17, 'test600', 'test600@example.jp', '$2y$10$vPQ4z.NrDpWHvc6X8wac2u8oUDAyfPTcQc2fE5ejtYcNptMLbNmV2', 'health', 'philosophy', NULL, 0),
(18, 'test700', 'test700@example.jp', '$2y$10$sDvqC3gXOuUEAJoVsFI9pOm6euZNlC31x/qmqdbwgNB9sE3mMxizW', 'future', 'fiction', NULL, 0);

--
-- ダンプしたテーブルのインデックス
--

--
-- テーブルのインデックス `gs_user_table5`
--
ALTER TABLE `gs_user_table5`
  ADD PRIMARY KEY (`id`);

--
-- ダンプしたテーブルの AUTO_INCREMENT
--

--
-- テーブルの AUTO_INCREMENT `gs_user_table5`
--
ALTER TABLE `gs_user_table5`
  MODIFY `id` int(12) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
