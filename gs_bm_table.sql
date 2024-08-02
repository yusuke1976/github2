-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- ホスト: 127.0.0.1
-- 生成日時: 2024-07-24 22:33:32
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
-- テーブルの構造 `gs_bm_table`
--

CREATE TABLE `gs_bm_table` (
  `username` varchar(64) NOT NULL,
  `id` int(12) NOT NULL,
  `worry` text NOT NULL,
  `book` varchar(64) NOT NULL,
  `url` text NOT NULL,
  `coment` text NOT NULL,
  `date` datetime NOT NULL,
  `helpful_count` int(11) DEFAULT 0,
  `voted_users` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのデータのダンプ `gs_bm_table`
--

INSERT INTO `gs_bm_table` (`username`, `id`, `worry`, `book`, `url`, `coment`, `date`, `helpful_count`, `voted_users`) VALUES
('gs_kadai', 8, '中学英語レベルから、英語の勉強をしたい', '中学英語で読めるはじめての英語ミステリーノベル', 'https://www.nhk-book.co.jp/digicon/goods-000062133752023.html', 'あらすじも掲載されているため、辞書がなくても、中学英語でスラスラと推理小説が読めます！', '2024-06-23 21:36:03', 4, 'gs_kadai'),
('gs_kadai', 9, 'もう少しで50代です。充実した50代にするにはどうすればいいですか。', '50代にしておきたい17のこと', 'https://www.kinokuniya.co.jp/f/dsg-08-9987646727', '50代を充実させるにはどうすればいいか、ヒントとなる視点が、簡潔に書かれています！', '2024-06-23 21:39:14', 1, 'gs_kadai'),
('test2', 10, '今の会社を辞めたいです。しかし、周囲から反対されていて、会社を辞めるべきか悩んでいます。', 'バカとつき合うな', 'https://www.tokuma.jp/book/b494337.html', '人は、自分の経験したことをベースにアドバイスやコメントをしてきます。あなたのやりたいことを経験していない人（転職・起業経験のない人）のアドバイスや反対コメントは無視しましょう！', '2024-06-23 21:45:20', 0, ''),
('test3', 11, '自分の判断に自信がありません', 'ゲッターズ飯田の五星三心占い', 'https://7net.omni7.jp/search/?keyword=gettersiida2025', '思い切って占いの本はいかがですか。新しい視点が手に入りますよ！', '2024-06-23 21:48:47', 0, ''),
('test3', 12, '環境破壊が心配です。しかし、具体的に何をすればいいかわかりません。', 'グリーンブック', 'https://www.e-hon.ne.jp/bec/SA/Detail?refShinCode=0100000000000032072483&Action_id=121&Sza_id=F3', '本気で環境保護を「実践」しています！', '2024-06-23 21:51:48', 0, ''),
('test2', 13, '海外旅行は高いので、国内で、安くて、日常を忘れられるようなところへ行きたい。', '地球の歩き方　東京の島々', 'https://hon.gakken.jp/book/2080215500', '関東にお住まいなら、伊豆七島はいかがですか。身近な「海外」ですよ！', '2024-06-23 22:07:06', 0, NULL),
('test3', 14, '海外初心者です。近場でおすすめはありますか。', '台湾　ランキング＆得テクニック', 'https://hon.gakken.jp/book/2080222500', '最初の海外渡航先として、台湾はいかがでしょうか。台湾のお得技と、観光案内がセットになった本です！', '2024-06-24 01:27:48', 0, ''),
('test2', 15, '英語の勉強がつまらない', '最新　日米口語辞典', 'https://www.asahipress.com/bookdetail_lang/9784255012148/', '読む辞典です。サービス残業や、ない袖は振れぬなどが載っています！', '2024-06-24 01:58:05', 0, ''),
('gs_kadai', 27, '英語の勉強はしているのに、話し言葉がわからない', '日本人が苦手な語彙・表現がわかる「ニュース英語」の読み方', 'https://book-tech.com/books/a2c5ef44-130c-4629-9bad-6965a4faf89b', 'ニュース英語を使って、口語表現を学べます！', '2024-07-07 17:02:04', 0, NULL),
('gs_kadai', 34, '楽してお金持ちになりたいです', 'ほったらかしで年間2000万円入ってくる超★高配当株投資入門', 'http://books.google.co.jp/books?id=y2qp0AEACAAJ&dq=isbn:9784478119945&hl=&source=gbs_api', '個別の銘柄にも触れていて、とても参考になります！', '2024-07-15 23:17:10', 0, ''),
('test600', 35, '英語を話せるようになりたい', '英会話なるほどフレーズ100　ネイティブなら子どものときに身につける　誰もここまで教えてくれなかった使える裏技', 'https://books.rakuten.co.jp/rb/1143395/?scid=af_pc_etc&sc2id=af_117_0_10002118', 'ネイティブの子どもが使っている、定番のフレーズを学べます！！', '2024-07-18 20:33:11', 0, ''),
('test500', 36, '英語がペラペラとしゃべれるようになりたい！', 'リアルな英語の9割は海外ドラマで学べる！', 'https://books.rakuten.co.jp/rb/15185664/?scid=af_pc_etc&sc2id=af_117_0_10002118', '「フレンズ」「24」など計15本の海外ドラマで、実際に使われているセリフ、頻繁に出てくるフレーズなどを紹介しています。難しい教科書では学べない、生きた英会話を学べるのが魅力です。', '2024-07-18 20:36:03', 0, NULL),
('test600', 37, '英語が話せればもっと給料が高くなるのに…！', 'どんどん話すための瞬間英作文トレーニング　反射的に言える　（CD　book）', 'https://books.rakuten.co.jp/rb/4164790/?scid=af_pc_etc&sc2id=af_117_0_10002118', '中学校で習うレベルの英語を使って、スピーディーに英語を組み立てるトレーニングをする教材です。', '2024-07-18 20:47:02', 0, NULL),
('test600', 39, '海外に移住して、海外で仕事ができるようなレベルまで、英語を上達させたい！', '感動スピーチで英語「速」音読', 'https://www.cosmopier.com/shoseki/4864541817/', '英語を日本語に訳さずに、英語のまま理解するには、速音読が一番効果的でした。スピーチの内容もすばらしくて、とてもよい本です。', '2024-07-24 12:20:32', 0, NULL);

--
-- ダンプしたテーブルのインデックス
--

--
-- テーブルのインデックス `gs_bm_table`
--
ALTER TABLE `gs_bm_table`
  ADD PRIMARY KEY (`id`);

--
-- ダンプしたテーブルの AUTO_INCREMENT
--

--
-- テーブルの AUTO_INCREMENT `gs_bm_table`
--
ALTER TABLE `gs_bm_table`
  MODIFY `id` int(12) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
