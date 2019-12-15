/*
 Navicat Premium Data Transfer

 Source Server         : 本地数据库
 Source Server Type    : MySQL
 Source Server Version : 50726
 Source Host           : localhost:3306
 Source Schema         : ol_system

 Target Server Type    : MySQL
 Target Server Version : 50726
 File Encoding         : 65001

 Date: 15/12/2019 17:29:11
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for user
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user`  (
  `name` varchar(25) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '用户名',
  `sex` tinyint(1) NULL DEFAULT NULL COMMENT '性别',
  `desc` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '描述',
  `phone` varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '手机号',
  `open_id` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `wechat_user` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '微信的用户信息',
  PRIMARY KEY (`open_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '用户表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of user
-- ----------------------------
INSERT INTO `user` VALUES ('Jasper', 1, NULL, NULL, '1', NULL);
INSERT INTO `user` VALUES ('PD', 1, NULL, NULL, '2', NULL);
INSERT INTO `user` VALUES ('kxjm', 2, NULL, NULL, '3', NULL);
INSERT INTO `user` VALUES ('ojvnd', 2, NULL, NULL, '4', NULL);
INSERT INTO `user` VALUES ('KD', 1, NULL, NULL, '5', NULL);
INSERT INTO `user` VALUES ('lckmx', 2, NULL, NULL, '6', NULL);
INSERT INTO `user` VALUES ('mzl', 1, NULL, NULL, '7', NULL);
INSERT INTO `user` VALUES ('〔 _ 〕', 1, NULL, NULL, 'o5nEd5EtCpel6kw-bQ9tCRCZOgO4', '\n  {\"nickName\":\"〔 _ 〕\",\"gender\":1,\"language\":\"zh_CN\",\"city\":\"Shantou\",\"province\":\"Guangdong\",\"country\":\"China\",\"avatarUrl\":\"https://wx.qlogo.cn/mmopen/vi_32/3IabKibtv6yATicoxX6lnT2YV7koSgUjOI3pSPkPPoVzyB5TFrguAUTicBxMRyCXNg0j04PEeXibL82ArEuMKIpn9Q/132\"}\n  ');

SET FOREIGN_KEY_CHECKS = 1;
