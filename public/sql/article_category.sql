/*
 Navicat Premium Data Transfer

 Source Server         : 106.54.187.34
 Source Server Type    : MySQL
 Source Server Version : 50728
 Source Host           : 106.54.187.34:3306
 Source Schema         : ol_system

 Target Server Type    : MySQL
 Target Server Version : 50728
 File Encoding         : 65001

 Date: 10/12/2019 23:33:57
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for article_category
-- ----------------------------
DROP TABLE IF EXISTS `article_category`;
CREATE TABLE `article_category`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 247 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '文章分类表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of article_category
-- ----------------------------
INSERT INTO `article_category` VALUES (239, '后端');
INSERT INTO `article_category` VALUES (240, '前端');
INSERT INTO `article_category` VALUES (241, 'Android');
INSERT INTO `article_category` VALUES (242, '代码人生');
INSERT INTO `article_category` VALUES (243, '阅读');
INSERT INTO `article_category` VALUES (244, '人工智能');
INSERT INTO `article_category` VALUES (245, 'iOS');
INSERT INTO `article_category` VALUES (246, '开发工具');

SET FOREIGN_KEY_CHECKS = 1;
