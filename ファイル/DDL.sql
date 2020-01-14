-- phpMyAdmin SQL Dump
-- version 4.9.1
-- https://www.phpmyadmin.net/
--
-- ホスト: 35.247.17.100
-- 生成日時: 2020 年 1 月 08 日 11:28
-- サーバのバージョン： 5.7.14-google-log
-- PHP のバージョン: 7.3.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- データベース: `2019ChiruSapo`
--

-- --------------------------------------------------------

--
-- テーブルの構造 `account_child`
--

CREATE TABLE `account_child` (
  `id` int(10) NOT NULL COMMENT 'ID',
  `group_id` int(10) NOT NULL COMMENT 'グループID',
  `user_id` varchar(30) NOT NULL COMMENT 'ユーザーID',
  `user_name` varchar(30) NOT NULL COMMENT 'ユーザー名',
  `birthday` date NOT NULL COMMENT '誕生日',
  `age` int(3) NOT NULL COMMENT '年齢',
  `gender` int(1) NOT NULL COMMENT '性別',
  `blood_type` int(1) NOT NULL COMMENT '血液型',
  `icon` varchar(50) NOT NULL COMMENT 'ユーザーアイコン',
  `delete_flg` tinyint(1) NOT NULL DEFAULT '0' COMMENT '削除フラグ'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='子どもアカウント';

-- --------------------------------------------------------

--
-- テーブルの構造 `account_user`
--

CREATE TABLE `account_user` (
  `id` int(10) NOT NULL COMMENT 'ID',
  `user_id` varchar(30) NOT NULL COMMENT 'ユーザーID',
  `user_name` varchar(30) NOT NULL COMMENT 'ユーザー名',
  `email` varchar(200) NOT NULL COMMENT 'メールアドレス',
  `password` varchar(100) DEFAULT NULL COMMENT 'パスワード',
  `gender` int(1) DEFAULT '0' COMMENT '性別',
  `birthday` date DEFAULT NULL COMMENT '誕生日',
  `introduction` text COMMENT '自己紹介',
  `icon_file_name` varchar(50) DEFAULT NULL COMMENT 'アイコンファイル名',
  `line_id` varchar(20) DEFAULT NULL COMMENT 'LINE ID',
  `line_token` varchar(100) DEFAULT NULL COMMENT 'LINEログイントークン',
  `resign_flg` tinyint(1) DEFAULT '0' COMMENT '退会フラグ'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='親アカウント';

-- --------------------------------------------------------

--
-- テーブルの構造 `account_user_token`
--

CREATE TABLE `account_user_token` (
  `id` int(10) NOT NULL COMMENT 'ID',
  `user_id` int(10) NOT NULL COMMENT 'ユーザーID',
  `token` varchar(100) NOT NULL COMMENT 'ログイントークン',
  `expiration_date` datetime NOT NULL COMMENT '有効期限'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='ログイントークン';

-- --------------------------------------------------------

--
-- テーブルの構造 `album_data`
--

CREATE TABLE `album_data` (
  `id` int(10) NOT NULL COMMENT 'ID',
  `group_id` int(10) NOT NULL COMMENT 'グループID',
  `file_name` varchar(100) NOT NULL COMMENT 'ファイル名',
  `upload_time` datetime NOT NULL COMMENT 'アップロード時間'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='アルバムデータ';

-- --------------------------------------------------------

--
-- テーブルの構造 `calendar_data`
--

CREATE TABLE `calendar_data` (
  `id` int(10) NOT NULL COMMENT 'ID',
  `group_id` int(10) NOT NULL COMMENT 'グループID',
  `user_id` int(10) NOT NULL COMMENT 'ユーザーID',
  `title` varchar(50) NOT NULL COMMENT 'タイトル',
  `content` text NOT NULL COMMENT '内容',
  `date` date NOT NULL COMMENT '日付',
  `year` int(4) NOT NULL COMMENT '年',
  `month` int(2) NOT NULL COMMENT '月',
  `day` int(2) NOT NULL COMMENT '日',
  `remind_flg` tinyint(1) DEFAULT '0' COMMENT 'リマインドフラグ'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='カレンダーデータ';

-- --------------------------------------------------------

--
-- テーブルの構造 `child_allergy`
--

CREATE TABLE `child_allergy` (
  `id` int(10) NOT NULL COMMENT 'ID',
  `child_id` int(10) DEFAULT NULL COMMENT '子どもID',
  `allergy_name` varchar(100) DEFAULT NULL COMMENT 'アレルギー名',
  `add_date` date DEFAULT NULL COMMENT '追加日'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='アレルギー情報';

-- --------------------------------------------------------

--
-- テーブルの構造 `child_face`
--

CREATE TABLE `child_face` (
  `id` int(10) NOT NULL COMMENT 'ID',
  `child_id` int(10) NOT NULL COMMENT '子どもID',
  `file_name` varchar(50) NOT NULL COMMENT 'ファイル名'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='子ども顔情報';

-- --------------------------------------------------------

--
-- テーブルの構造 `child_friend`
--

CREATE TABLE `child_friend` (
  `id` int(10) NOT NULL COMMENT 'ID',
  `child_id` int(10) DEFAULT NULL COMMENT '子どもID',
  `user_name` varchar(30) NOT NULL COMMENT 'ユーザー名',
  `birthday` date DEFAULT NULL COMMENT '誕生日',
  `gender` int(1) DEFAULT NULL COMMENT '性別',
  `memo` text COMMENT 'メモ',
  `icon` varchar(50) DEFAULT NULL COMMENT 'ユーザーアイコン'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='友だち情報';

-- --------------------------------------------------------

--
-- テーブルの構造 `child_friend_face`
--

CREATE TABLE `child_friend_face` (
  `id` int(10) NOT NULL COMMENT 'ID',
  `friend_id` int(10) NOT NULL COMMENT 'フレンドID',
  `file_name` varchar(50) NOT NULL COMMENT 'ファイル名'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='子ども友だち顔情報';

-- --------------------------------------------------------

--
-- テーブルの構造 `child_friend_parents`
--

CREATE TABLE `child_friend_parents` (
  `id` int(10) NOT NULL COMMENT 'ID',
  `friend_id` int(10) DEFAULT NULL COMMENT '友だちID',
  `user_name` varchar(30) NOT NULL COMMENT 'ユーザー名',
  `gender` int(1) DEFAULT '0' COMMENT '性別',
  `introduction` text COMMENT '自己紹介',
  `line_id` varchar(20) DEFAULT NULL COMMENT 'LINE ID'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='友だち親情報';

-- --------------------------------------------------------

--
-- テーブルの構造 `child_growth_diary`
--

CREATE TABLE `child_growth_diary` (
  `id` int(10) NOT NULL COMMENT 'ID',
  `child_id` int(10) DEFAULT NULL COMMENT '子どもID',
  `user_id` int(10) NOT NULL COMMENT 'ユーザーID',
  `content_type` varchar(10) NOT NULL COMMENT 'コンテンツタイプ',
  `text_content` varchar(250) NOT NULL COMMENT 'コメント',
  `date` datetime NOT NULL COMMENT '日付',
  `delete_flg` tinyint(1) DEFAULT '0' COMMENT '削除フラグ'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='子ども成長日記';

-- --------------------------------------------------------

--
-- テーブルの構造 `child_growth_diary_comment`
--

CREATE TABLE `child_growth_diary_comment` (
  `id` int(10) NOT NULL COMMENT 'ID',
  `diary_id` int(10) NOT NULL COMMENT '日記ID',
  `user_id` int(10) NOT NULL COMMENT 'ユーザーID',
  `comment` varchar(250) NOT NULL COMMENT 'コメント',
  `post_time` datetime NOT NULL COMMENT '投稿時間'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='子ども成長日記コメント';

-- --------------------------------------------------------

--
-- テーブルの構造 `child_growth_diary_image`
--

CREATE TABLE `child_growth_diary_image` (
  `diary_id` int(10) NOT NULL COMMENT '日記ID',
  `order_id` int(1) NOT NULL COMMENT '並び順',
  `file_name` varchar(100) NOT NULL COMMENT 'ファイル名'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='子ども成長日記画像';

-- --------------------------------------------------------

--
-- テーブルの構造 `child_growth_diary_movie`
--

CREATE TABLE `child_growth_diary_movie` (
  `diary_id` int(10) NOT NULL COMMENT '日記ID',
  `file_thumbnail` varchar(100) NOT NULL COMMENT 'サムネイル',
  `file_name` varchar(100) NOT NULL COMMENT 'ファイル名'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='子ども成長日記動画';

-- --------------------------------------------------------

--
-- テーブルの構造 `child_growth_history`
--

CREATE TABLE `child_growth_history` (
  `id` int(10) NOT NULL COMMENT '記録ID',
  `child_id` int(10) DEFAULT NULL COMMENT '子どもID',
  `body_height` double DEFAULT NULL COMMENT '身長',
  `body_weight` double DEFAULT NULL COMMENT '体重',
  `clothes_size` int(3) DEFAULT NULL COMMENT '服サイズ',
  `shoes_size` double DEFAULT NULL COMMENT '靴サイズ',
  `add_date` date NOT NULL COMMENT '記録日'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='子ども成長記録';

-- --------------------------------------------------------

--
-- テーブルの構造 `child_vaccination`
--

CREATE TABLE `child_vaccination` (
  `id` int(10) NOT NULL COMMENT 'ID',
  `child_id` int(10) DEFAULT NULL COMMENT '子どもID',
  `vaccine_name` varchar(100) DEFAULT NULL COMMENT 'ワクチン名',
  `visit_date` date DEFAULT NULL COMMENT '受診日',
  `add_date` date DEFAULT NULL COMMENT '追加日'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='予防接種情報';

-- --------------------------------------------------------

--
-- テーブルの構造 `group_master`
--

CREATE TABLE `group_master` (
  `id` int(10) NOT NULL COMMENT 'ID',
  `group_id` varchar(30) NOT NULL COMMENT 'グループID',
  `group_name` varchar(30) NOT NULL COMMENT 'グループ名',
  `pin_code` varchar(4) NOT NULL DEFAULT '0000' COMMENT 'PINコード',
  `delete_flg` tinyint(1) DEFAULT '0' COMMENT '削除フラグ'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='グループ情報';

-- --------------------------------------------------------

--
-- テーブルの構造 `group_timeline`
--

CREATE TABLE `group_timeline` (
  `id` int(10) NOT NULL COMMENT 'ID',
  `group_id` int(10) NOT NULL COMMENT 'グループID',
  `user_id` int(10) NOT NULL COMMENT 'ユーザーID',
  `content_type` varchar(10) NOT NULL COMMENT '投稿タイプ',
  `post_time` datetime NOT NULL COMMENT '投稿時間',
  `delete_flg` tinyint(1) DEFAULT '0' COMMENT '削除フラグ'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='タイムライン投稿';

-- --------------------------------------------------------

--
-- テーブルの構造 `group_timeline_comment`
--

CREATE TABLE `group_timeline_comment` (
  `id` int(10) NOT NULL COMMENT 'ID',
  `timeline_id` int(10) NOT NULL COMMENT 'タイムラインID',
  `user_id` int(10) NOT NULL COMMENT 'ユーザーID',
  `comment` varchar(250) NOT NULL COMMENT 'コメント',
  `post_time` datetime NOT NULL COMMENT '投稿時間'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='タイムライン投稿コメント';

-- --------------------------------------------------------

--
-- テーブルの構造 `group_timeline_image`
--

CREATE TABLE `group_timeline_image` (
  `timeline_id` int(10) NOT NULL COMMENT 'タイムラインID',
  `order_id` int(1) NOT NULL COMMENT '並び順',
  `file_name` varchar(100) NOT NULL COMMENT 'ファイル名'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='タイムライン投稿画像';

-- --------------------------------------------------------

--
-- テーブルの構造 `group_timeline_movie`
--

CREATE TABLE `group_timeline_movie` (
  `timeline_id` int(10) NOT NULL COMMENT 'タイムラインID',
  `file_thumbnail` varchar(100) NOT NULL COMMENT 'サムネイル',
  `file_name` varchar(100) NOT NULL COMMENT 'ファイル名'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='タイムライン投稿動画';

-- --------------------------------------------------------

--
-- テーブルの構造 `group_timeline_text`
--

CREATE TABLE `group_timeline_text` (
  `timeline_id` int(10) NOT NULL COMMENT 'タイムラインID',
  `content` varchar(250) NOT NULL COMMENT '投稿内容'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='タイムライン投稿文章';

-- --------------------------------------------------------

--
-- テーブルの構造 `group_user`
--

CREATE TABLE `group_user` (
  `group_id` int(10) DEFAULT NULL COMMENT 'グループID',
  `user_id` int(10) DEFAULT NULL COMMENT '親ユーザーID'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='グループ参加親ユーザー';

-- --------------------------------------------------------

--
-- テーブルの構造 `master_allergy`
--

CREATE TABLE `master_allergy` (
  `id` int(10) NOT NULL COMMENT 'ID',
  `allergy_name` varchar(100) NOT NULL COMMENT 'アレルギー名'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='アレルギー名マスター';

-- --------------------------------------------------------

--
-- テーブルの構造 `master_vaccination`
--

CREATE TABLE `master_vaccination` (
  `id` int(10) NOT NULL COMMENT 'ID',
  `vaccination_name` varchar(100) NOT NULL COMMENT '予防接種名'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='予防接種名マスター';

-- --------------------------------------------------------

--
-- テーブルの構造 `model_child`
--

CREATE TABLE `model_child` (
  `id` int(10) NOT NULL COMMENT 'ID',
  `group_id` int(10) DEFAULT NULL COMMENT 'グループID',
  `file_name` varchar(100) NOT NULL COMMENT 'ファイル名'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='子どもモデル';

-- --------------------------------------------------------

--
-- テーブルの構造 `model_clothes`
--

CREATE TABLE `model_clothes` (
  `id` int(10) NOT NULL COMMENT 'ID',
  `group_id` int(10) DEFAULT NULL COMMENT 'グループID',
  `file_name` varchar(100) NOT NULL COMMENT 'ファイル名'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='服モデル';

-- --------------------------------------------------------

--
-- ビュー用の代替構造 `view_child_growth_diary`
-- (実際のビューを参照するには下にあります)
--
CREATE TABLE `view_child_growth_diary` (
`id` int(10)
,`child_id` int(10)
,`inner_user_id` int(10)
,`user_id` varchar(30)
,`user_name` varchar(30)
,`icon_file_name` varchar(50)
,`content_type` varchar(10)
,`content` varchar(250)
,`image01` varchar(100)
,`image02` varchar(100)
,`image03` varchar(100)
,`image04` varchar(100)
,`movie01_thumbnail` varchar(100)
,`movie01_content` varchar(100)
,`post_time` datetime
);

-- --------------------------------------------------------

--
-- ビュー用の代替構造 `view_group_timeline`
-- (実際のビューを参照するには下にあります)
--
CREATE TABLE `view_group_timeline` (
`id` int(10)
,`group_id` int(10)
,`inner_user_id` int(10)
,`user_id` varchar(30)
,`user_name` varchar(30)
,`icon_file_name` varchar(50)
,`content_type` varchar(10)
,`content` varchar(250)
,`image01` varchar(100)
,`image02` varchar(100)
,`image03` varchar(100)
,`image04` varchar(100)
,`movie01_thumbnail` varchar(100)
,`movie01_content` varchar(100)
,`post_time` datetime
);

-- --------------------------------------------------------

--
-- ビュー用の構造 `view_child_growth_diary`
--
DROP TABLE IF EXISTS `view_child_growth_diary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`%` SQL SECURITY DEFINER VIEW `view_child_growth_diary`  AS  select `cgd`.`id` AS `id`,`cgd`.`child_id` AS `child_id`,`au`.`id` AS `inner_user_id`,`au`.`user_id` AS `user_id`,`au`.`user_name` AS `user_name`,`au`.`icon_file_name` AS `icon_file_name`,`cgd`.`content_type` AS `content_type`,`cgd`.`text_content` AS `content`,(select `child_growth_diary_image`.`file_name` from `child_growth_diary_image` where ((`child_growth_diary_image`.`diary_id` = `cgd`.`id`) and (`child_growth_diary_image`.`order_id` = 1))) AS `image01`,(select `child_growth_diary_image`.`file_name` from `child_growth_diary_image` where ((`child_growth_diary_image`.`diary_id` = `cgd`.`id`) and (`child_growth_diary_image`.`order_id` = 2))) AS `image02`,(select `child_growth_diary_image`.`file_name` from `child_growth_diary_image` where ((`child_growth_diary_image`.`diary_id` = `cgd`.`id`) and (`child_growth_diary_image`.`order_id` = 3))) AS `image03`,(select `child_growth_diary_image`.`file_name` from `child_growth_diary_image` where ((`child_growth_diary_image`.`diary_id` = `cgd`.`id`) and (`child_growth_diary_image`.`order_id` = 4))) AS `image04`,(select `child_growth_diary_movie`.`file_name` from `child_growth_diary_movie` where (`child_growth_diary_movie`.`diary_id` = `cgd`.`id`)) AS `movie01_thumbnail`,(select `child_growth_diary_movie`.`file_thumbnail` from `child_growth_diary_movie` where (`child_growth_diary_movie`.`diary_id` = `cgd`.`id`)) AS `movie01_content`,`cgd`.`date` AS `post_time` from (`child_growth_diary` `cgd` left join `account_user` `au` on((`cgd`.`user_id` = `au`.`id`))) where (`cgd`.`delete_flg` = 0) ;

-- --------------------------------------------------------

--
-- ビュー用の構造 `view_group_timeline`
--
DROP TABLE IF EXISTS `view_group_timeline`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`%` SQL SECURITY DEFINER VIEW `view_group_timeline`  AS  select `gt`.`id` AS `id`,`gt`.`group_id` AS `group_id`,`au`.`id` AS `inner_user_id`,`au`.`user_id` AS `user_id`,`au`.`user_name` AS `user_name`,`au`.`icon_file_name` AS `icon_file_name`,`gt`.`content_type` AS `content_type`,(select `group_timeline_text`.`content` from `group_timeline_text` where (`group_timeline_text`.`timeline_id` = `gt`.`id`)) AS `content`,(select `group_timeline_image`.`file_name` from `group_timeline_image` where ((`group_timeline_image`.`timeline_id` = `gt`.`id`) and (`group_timeline_image`.`order_id` = 1))) AS `image01`,(select `group_timeline_image`.`file_name` from `group_timeline_image` where ((`group_timeline_image`.`timeline_id` = `gt`.`id`) and (`group_timeline_image`.`order_id` = 2))) AS `image02`,(select `group_timeline_image`.`file_name` from `group_timeline_image` where ((`group_timeline_image`.`timeline_id` = `gt`.`id`) and (`group_timeline_image`.`order_id` = 3))) AS `image03`,(select `group_timeline_image`.`file_name` from `group_timeline_image` where ((`group_timeline_image`.`timeline_id` = `gt`.`id`) and (`group_timeline_image`.`order_id` = 4))) AS `image04`,(select `group_timeline_movie`.`file_thumbnail` from `group_timeline_movie` where (`group_timeline_movie`.`timeline_id` = `gt`.`id`)) AS `movie01_thumbnail`,(select `group_timeline_movie`.`file_name` from `group_timeline_movie` where (`group_timeline_movie`.`timeline_id` = `gt`.`id`)) AS `movie01_content`,`gt`.`post_time` AS `post_time` from (`group_timeline` `gt` left join `account_user` `au` on((`au`.`id` = `gt`.`user_id`))) where (`gt`.`delete_flg` = 0) ;

--
-- ダンプしたテーブルのインデックス
--

--
-- テーブルのインデックス `account_child`
--
ALTER TABLE `account_child`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `account_child_child_id_uindex` (`user_id`),
  ADD KEY `account_child_group_master_id_fk` (`group_id`);

--
-- テーブルのインデックス `account_user`
--
ALTER TABLE `account_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_email_uindex` (`email`),
  ADD UNIQUE KEY `user_user_id_uindex` (`user_id`);

--
-- テーブルのインデックス `account_user_token`
--
ALTER TABLE `account_user_token`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `account_user_token_token_uindex` (`token`),
  ADD KEY `account_user_token_account_user_id_fk` (`user_id`);

--
-- テーブルのインデックス `album_data`
--
ALTER TABLE `album_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `album_data_group_master_id_fk` (`group_id`);

--
-- テーブルのインデックス `calendar_data`
--
ALTER TABLE `calendar_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `calendar_data_group_master_id_fk` (`group_id`),
  ADD KEY `calendar_data_account_user_id_fk` (`user_id`);

--
-- テーブルのインデックス `child_allergy`
--
ALTER TABLE `child_allergy`
  ADD PRIMARY KEY (`id`),
  ADD KEY `child_allergy_account_child_id_fk` (`child_id`);

--
-- テーブルのインデックス `child_face`
--
ALTER TABLE `child_face`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `child_face_file_name_uindex` (`file_name`),
  ADD KEY `child_face_account_child_id_fk` (`child_id`);

--
-- テーブルのインデックス `child_friend`
--
ALTER TABLE `child_friend`
  ADD PRIMARY KEY (`id`),
  ADD KEY `child_friend_account_child_id_fk` (`child_id`);

--
-- テーブルのインデックス `child_friend_face`
--
ALTER TABLE `child_friend_face`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `child_friend_face_file_name_uindex` (`file_name`),
  ADD KEY `child_face_account_child_id_fk` (`friend_id`);

--
-- テーブルのインデックス `child_friend_parents`
--
ALTER TABLE `child_friend_parents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `child_friend_parents_child_friend_id_fk` (`friend_id`);

--
-- テーブルのインデックス `child_growth_diary`
--
ALTER TABLE `child_growth_diary`
  ADD PRIMARY KEY (`id`),
  ADD KEY `child_growth_diary_account_child_id_fk` (`child_id`),
  ADD KEY `child_growth_diary_account_user_id_fk` (`user_id`);

--
-- テーブルのインデックス `child_growth_diary_comment`
--
ALTER TABLE `child_growth_diary_comment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `child_growth_diary_comment_account_user_id_fk` (`user_id`),
  ADD KEY `child_growth_diary_comment_child_growth_diary_id_fk` (`diary_id`);

--
-- テーブルのインデックス `child_growth_diary_image`
--
ALTER TABLE `child_growth_diary_image`
  ADD PRIMARY KEY (`diary_id`,`order_id`);

--
-- テーブルのインデックス `child_growth_diary_movie`
--
ALTER TABLE `child_growth_diary_movie`
  ADD PRIMARY KEY (`diary_id`);

--
-- テーブルのインデックス `child_growth_history`
--
ALTER TABLE `child_growth_history`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `child_growth_history_child_id_uindex` (`child_id`,`add_date`);

--
-- テーブルのインデックス `child_vaccination`
--
ALTER TABLE `child_vaccination`
  ADD PRIMARY KEY (`id`),
  ADD KEY `child_vaccination_account_child_id_fk` (`child_id`);

--
-- テーブルのインデックス `group_master`
--
ALTER TABLE `group_master`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `group_master_grop_id_uindex` (`group_id`);

--
-- テーブルのインデックス `group_timeline`
--
ALTER TABLE `group_timeline`
  ADD PRIMARY KEY (`id`),
  ADD KEY `group_timeline_account_user_id_fk` (`user_id`),
  ADD KEY `group_timeline_group_master_id_fk` (`group_id`);

--
-- テーブルのインデックス `group_timeline_comment`
--
ALTER TABLE `group_timeline_comment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `group_timeline_comment_account_user_id_fk` (`user_id`),
  ADD KEY `group_timeline_comment_group_timeline_data_id_fk` (`timeline_id`);

--
-- テーブルのインデックス `group_timeline_image`
--
ALTER TABLE `group_timeline_image`
  ADD PRIMARY KEY (`timeline_id`,`order_id`);

--
-- テーブルのインデックス `group_timeline_movie`
--
ALTER TABLE `group_timeline_movie`
  ADD PRIMARY KEY (`timeline_id`);

--
-- テーブルのインデックス `group_timeline_text`
--
ALTER TABLE `group_timeline_text`
  ADD PRIMARY KEY (`timeline_id`);

--
-- テーブルのインデックス `group_user`
--
ALTER TABLE `group_user`
  ADD KEY `group_user_account_user_id_fk` (`user_id`),
  ADD KEY `group_user_group_master_id_fk` (`group_id`);

--
-- テーブルのインデックス `master_allergy`
--
ALTER TABLE `master_allergy`
  ADD PRIMARY KEY (`id`);

--
-- テーブルのインデックス `master_vaccination`
--
ALTER TABLE `master_vaccination`
  ADD PRIMARY KEY (`id`);

--
-- テーブルのインデックス `model_child`
--
ALTER TABLE `model_child`
  ADD PRIMARY KEY (`id`),
  ADD KEY `model_child_group_master_id_fk` (`group_id`);

--
-- テーブルのインデックス `model_clothes`
--
ALTER TABLE `model_clothes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `model_clothes_group_master_id_fk` (`group_id`);

--
-- ダンプしたテーブルのAUTO_INCREMENT
--

--
-- テーブルのAUTO_INCREMENT `account_child`
--
ALTER TABLE `account_child`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID';

--
-- テーブルのAUTO_INCREMENT `account_user`
--
ALTER TABLE `account_user`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID';

--
-- テーブルのAUTO_INCREMENT `account_user_token`
--
ALTER TABLE `account_user_token`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID';

--
-- テーブルのAUTO_INCREMENT `album_data`
--
ALTER TABLE `album_data`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID';

--
-- テーブルのAUTO_INCREMENT `calendar_data`
--
ALTER TABLE `calendar_data`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID';

--
-- テーブルのAUTO_INCREMENT `child_allergy`
--
ALTER TABLE `child_allergy`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID';

--
-- テーブルのAUTO_INCREMENT `child_face`
--
ALTER TABLE `child_face`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID';

--
-- テーブルのAUTO_INCREMENT `child_friend`
--
ALTER TABLE `child_friend`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID';

--
-- テーブルのAUTO_INCREMENT `child_friend_face`
--
ALTER TABLE `child_friend_face`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID';

--
-- テーブルのAUTO_INCREMENT `child_friend_parents`
--
ALTER TABLE `child_friend_parents`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID';

--
-- テーブルのAUTO_INCREMENT `child_growth_diary`
--
ALTER TABLE `child_growth_diary`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID';

--
-- テーブルのAUTO_INCREMENT `child_growth_diary_comment`
--
ALTER TABLE `child_growth_diary_comment`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID';

--
-- テーブルのAUTO_INCREMENT `child_growth_history`
--
ALTER TABLE `child_growth_history`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '記録ID';

--
-- テーブルのAUTO_INCREMENT `child_vaccination`
--
ALTER TABLE `child_vaccination`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID';

--
-- テーブルのAUTO_INCREMENT `group_master`
--
ALTER TABLE `group_master`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID';

--
-- テーブルのAUTO_INCREMENT `group_timeline`
--
ALTER TABLE `group_timeline`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID';

--
-- テーブルのAUTO_INCREMENT `group_timeline_comment`
--
ALTER TABLE `group_timeline_comment`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID';

--
-- テーブルのAUTO_INCREMENT `master_allergy`
--
ALTER TABLE `master_allergy`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID';

--
-- テーブルのAUTO_INCREMENT `master_vaccination`
--
ALTER TABLE `master_vaccination`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID';

--
-- テーブルのAUTO_INCREMENT `model_child`
--
ALTER TABLE `model_child`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID';

--
-- テーブルのAUTO_INCREMENT `model_clothes`
--
ALTER TABLE `model_clothes`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID';

--
-- ダンプしたテーブルの制約
--

--
-- テーブルの制約 `account_child`
--
ALTER TABLE `account_child`
  ADD CONSTRAINT `account_child_group_master_id_fk` FOREIGN KEY (`group_id`) REFERENCES `group_master` (`id`);

--
-- テーブルの制約 `account_user_token`
--
ALTER TABLE `account_user_token`
  ADD CONSTRAINT `account_user_token_account_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `account_user` (`id`);

--
-- テーブルの制約 `album_data`
--
ALTER TABLE `album_data`
  ADD CONSTRAINT `album_data_group_master_id_fk` FOREIGN KEY (`group_id`) REFERENCES `group_master` (`id`);

--
-- テーブルの制約 `calendar_data`
--
ALTER TABLE `calendar_data`
  ADD CONSTRAINT `calendar_data_account_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `account_user` (`id`),
  ADD CONSTRAINT `calendar_data_group_master_id_fk` FOREIGN KEY (`group_id`) REFERENCES `group_master` (`id`);

--
-- テーブルの制約 `child_allergy`
--
ALTER TABLE `child_allergy`
  ADD CONSTRAINT `child_allergy_account_child_id_fk` FOREIGN KEY (`child_id`) REFERENCES `account_child` (`id`);

--
-- テーブルの制約 `child_face`
--
ALTER TABLE `child_face`
  ADD CONSTRAINT `child_face_account_child_id_fk` FOREIGN KEY (`child_id`) REFERENCES `account_child` (`id`);

--
-- テーブルの制約 `child_friend`
--
ALTER TABLE `child_friend`
  ADD CONSTRAINT `child_friend_account_child_id_fk` FOREIGN KEY (`child_id`) REFERENCES `account_child` (`id`);

--
-- テーブルの制約 `child_friend_face`
--
ALTER TABLE `child_friend_face`
  ADD CONSTRAINT `child_friend_face_child_friend_id_fk` FOREIGN KEY (`friend_id`) REFERENCES `child_friend` (`id`);

--
-- テーブルの制約 `child_friend_parents`
--
ALTER TABLE `child_friend_parents`
  ADD CONSTRAINT `child_friend_parents_child_friend_id_fk` FOREIGN KEY (`friend_id`) REFERENCES `child_friend` (`id`);

--
-- テーブルの制約 `child_growth_diary`
--
ALTER TABLE `child_growth_diary`
  ADD CONSTRAINT `child_growth_diary_account_child_id_fk` FOREIGN KEY (`child_id`) REFERENCES `account_child` (`id`),
  ADD CONSTRAINT `child_growth_diary_account_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `account_user` (`id`);

--
-- テーブルの制約 `child_growth_diary_comment`
--
ALTER TABLE `child_growth_diary_comment`
  ADD CONSTRAINT `child_growth_diary_comment_account_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `account_user` (`id`),
  ADD CONSTRAINT `child_growth_diary_comment_child_growth_diary_id_fk` FOREIGN KEY (`diary_id`) REFERENCES `child_growth_diary` (`id`);

--
-- テーブルの制約 `child_growth_diary_image`
--
ALTER TABLE `child_growth_diary_image`
  ADD CONSTRAINT `child_growth_diary_image_child_growth_diary_id_fk` FOREIGN KEY (`diary_id`) REFERENCES `child_growth_diary` (`id`);

--
-- テーブルの制約 `child_growth_diary_movie`
--
ALTER TABLE `child_growth_diary_movie`
  ADD CONSTRAINT `child_growth_diary_movie_child_growth_diary_id_fk` FOREIGN KEY (`diary_id`) REFERENCES `child_growth_diary` (`id`);

--
-- テーブルの制約 `child_vaccination`
--
ALTER TABLE `child_vaccination`
  ADD CONSTRAINT `child_vaccination_account_child_id_fk` FOREIGN KEY (`child_id`) REFERENCES `account_child` (`id`);

--
-- テーブルの制約 `group_timeline`
--
ALTER TABLE `group_timeline`
  ADD CONSTRAINT `group_timeline_account_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `account_user` (`id`),
  ADD CONSTRAINT `group_timeline_group_master_id_fk` FOREIGN KEY (`group_id`) REFERENCES `group_master` (`id`);

--
-- テーブルの制約 `group_timeline_comment`
--
ALTER TABLE `group_timeline_comment`
  ADD CONSTRAINT `group_timeline_comment_account_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `account_user` (`id`),
  ADD CONSTRAINT `group_timeline_comment_group_timeline_data_id_fk` FOREIGN KEY (`timeline_id`) REFERENCES `group_timeline` (`id`);

--
-- テーブルの制約 `group_timeline_image`
--
ALTER TABLE `group_timeline_image`
  ADD CONSTRAINT `group_timeline_image_group_timeline_id_fk` FOREIGN KEY (`timeline_id`) REFERENCES `group_timeline` (`id`);

--
-- テーブルの制約 `group_timeline_movie`
--
ALTER TABLE `group_timeline_movie`
  ADD CONSTRAINT `group_timeline_movie_group_timeline_data_id_fk` FOREIGN KEY (`timeline_id`) REFERENCES `group_timeline` (`id`);

--
-- テーブルの制約 `group_timeline_text`
--
ALTER TABLE `group_timeline_text`
  ADD CONSTRAINT `group_timeline_text_group_timeline_data_id_fk` FOREIGN KEY (`timeline_id`) REFERENCES `group_timeline` (`id`);

--
-- テーブルの制約 `group_user`
--
ALTER TABLE `group_user`
  ADD CONSTRAINT `group_user_account_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `account_user` (`id`),
  ADD CONSTRAINT `group_user_group_master_id_fk` FOREIGN KEY (`group_id`) REFERENCES `group_master` (`id`);

--
-- テーブルの制約 `model_child`
--
ALTER TABLE `model_child`
  ADD CONSTRAINT `model_child_group_master_id_fk` FOREIGN KEY (`group_id`) REFERENCES `group_master` (`id`);

--
-- テーブルの制約 `model_clothes`
--
ALTER TABLE `model_clothes`
  ADD CONSTRAINT `model_clothes_group_master_id_fk` FOREIGN KEY (`group_id`) REFERENCES `group_master` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
