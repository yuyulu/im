/*
Navicat MySQL Data Transfer

Source Server         : 黄磊的连接
Source Server Version : 50730
Source Host           : 127.0.0.1:3306
Source Database       : im

Target Server Type    : MYSQL
Target Server Version : 50730
File Encoding         : 65001

Date: 2020-06-04 13:27:22
*/

SET FOREIGN_KEY_CHECKS=0;

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
-- Records of chat_message
-- ----------------------------

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO `users` VALUES ('1', '', '123456', '3c7423d34253c2af542b000dae36e138', '0', '13193835001', 'tong1', 'tong1', '', '0', '123456', '0', '0', '0');
INSERT INTO `users` VALUES ('2', '', '123456', 'f75a72e8bfb559a473ee33be6064d47d', '0', '13193835002', 'tong2', 'tong2', '', '0', '123456', '0', '0', '0');
