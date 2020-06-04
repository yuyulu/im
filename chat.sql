-- ----------------------------
-- Table structure for chat_message
-- ----------------------------
DROP TABLE IF EXISTS `chat_message`;
CREATE TABLE `chat_message` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `from` int(11) NOT NULL,
  `to` int(11) NOT NULL,
  `time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `type` varchar(11) NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `from_to` (`from`,`to`),
  KEY `time` (`time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `avatar` varchar(255) NOT NULL DEFAULT '' COMMENT '头像',
  `password` varchar(128) NOT NULL,
  `signature` varchar(255) NOT NULL DEFAULT '' COMMENT '签名',
  `recommend_uid` int(11) NOT NULL DEFAULT '0' COMMENT '推荐人',
  `phone` varchar(32) NOT NULL,
  `realname` varchar(32) NOT NULL DEFAULT '' COMMENT '真实姓名',
  `nickname` varchar(128) NOT NULL DEFAULT '',
  `id_number` varchar(32) NOT NULL DEFAULT '' COMMENT '身份证',
  `sex` tinyint(1) NOT NULL DEFAULT '0' COMMENT '性别 0女 1男',
  `token` varchar(32) NOT NULL,
  `created_at` int(11) NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否禁用 0为未禁用 1为禁用',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;