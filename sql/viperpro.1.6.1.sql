/*
 Navicat Premium Dump SQL

 Source Server         : Localhost Laragon
 Source Server Type    : MySQL
 Source Server Version : 80030 (8.0.30)
 Source Host           : localhost:3306
 Source Schema         : viperprogithub

 Target Server Type    : MySQL
 Target Server Version : 80030 (8.0.30)
 File Encoding         : 65001

 Date: 15/01/2025 15:46:05
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for affiliate_histories
-- ----------------------------
DROP TABLE IF EXISTS `affiliate_histories`;
CREATE TABLE `affiliate_histories`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED NOT NULL,
  `inviter` int UNSIGNED NOT NULL,
  `commission` decimal(20, 2) NOT NULL DEFAULT 0.00,
  `commission_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `deposited` tinyint NULL DEFAULT 0,
  `deposited_amount` decimal(10, 2) NULL DEFAULT 0.00,
  `losses` bigint NULL DEFAULT 0,
  `losses_amount` decimal(10, 2) NULL DEFAULT 0.00,
  `commission_paid` decimal(10, 2) NULL DEFAULT 0.00,
  `status` tinyint NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `receita` decimal(10, 2) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `affiliate_histories_user_id_index`(`user_id`) USING BTREE,
  INDEX `affiliate_histories_inviter_index`(`inviter`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of affiliate_histories
-- ----------------------------

-- ----------------------------
-- Table structure for affiliate_withdraws
-- ----------------------------
DROP TABLE IF EXISTS `affiliate_withdraws`;
CREATE TABLE `affiliate_withdraws`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `payment_id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `amount` decimal(20, 2) NOT NULL DEFAULT 0.00,
  `proof` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT 0,
  `pix_key` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `pix_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `bank_info` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `currency` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `symbol` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `affiliate_withdraws_user_id_foreign`(`user_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of affiliate_withdraws
-- ----------------------------

-- ----------------------------
-- Table structure for banners
-- ----------------------------
DROP TABLE IF EXISTS `banners`;
CREATE TABLE `banners`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `link` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `image` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'home',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 15 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of banners
-- ----------------------------
INSERT INTO `banners` VALUES (12, NULL, '01HN3AKDHVEN6TQ36QF8B0RD5G.png', 'home', '...', '2024-01-26 13:45:43', '2024-01-26 13:45:43');
INSERT INTO `banners` VALUES (13, NULL, '01HN3AM094CHA78JMNA1WFJQ48.png', 'home', '....', '2024-01-26 13:46:03', '2024-01-26 13:46:03');
INSERT INTO `banners` VALUES (11, '#', '01J56F0P92C3304N767PCX16F3.png', 'carousel', '....', '2024-01-13 18:43:10', '2024-08-13 15:11:45');
INSERT INTO `banners` VALUES (10, '#', '01J56F0K86G2HXN898SSMT3J60.webp', 'carousel', '...', '2024-01-13 18:41:51', '2024-08-13 15:11:42');
INSERT INTO `banners` VALUES (9, '#', '01J56F0HBS6X3BJ3GWAMWJR7RJ.png', 'carousel', '...', '2024-01-13 18:41:29', '2024-08-13 15:11:40');
INSERT INTO `banners` VALUES (8, '#', '01J56EZK8CT3S95HAP6Z8D5649.png', 'carousel', '...', '2024-01-13 18:41:09', '2024-08-13 15:11:09');
INSERT INTO `banners` VALUES (14, NULL, '01HN3ANKG0HVN2Z6XKN93Z4ZH9.png', 'home', '...', '2024-01-26 13:46:55', '2024-01-26 13:46:55');

-- ----------------------------
-- Table structure for categories
-- ----------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `slug` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `casino_categories_slug_unique`(`slug`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of categories
-- ----------------------------

-- ----------------------------
-- Table structure for category_game
-- ----------------------------
DROP TABLE IF EXISTS `category_game`;
CREATE TABLE `category_game`  (
  `category_id` bigint UNSIGNED NOT NULL,
  `game_id` bigint UNSIGNED NOT NULL,
  INDEX `category_games_category_id_foreign`(`category_id`) USING BTREE,
  INDEX `category_games_game_id_foreign`(`game_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = FIXED;

-- ----------------------------
-- Records of category_game
-- ----------------------------

-- ----------------------------
-- Table structure for currencies
-- ----------------------------
DROP TABLE IF EXISTS `currencies`;
CREATE TABLE `currencies`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `symbol` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 114 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of currencies
-- ----------------------------
INSERT INTO `currencies` VALUES (1, 'Leke', 'ALL', 'Lek', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (2, 'Dollars', 'USD', '$', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (3, 'Afghanis', 'AFN', '؋', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (4, 'Pesos', 'ARS', '$', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (5, 'Guilders', 'AWG', 'ƒ', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (6, 'Dollars', 'AUD', '$', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (7, 'New Manats', 'AZN', 'ман', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (8, 'Dollars', 'BSD', '$', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (9, 'Dollars', 'BBD', '$', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (10, 'Rubles', 'BYR', 'p.', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (11, 'Euro', 'EUR', '€', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (12, 'Dollars', 'BZD', 'BZ$', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (13, 'Dollars', 'BMD', '$', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (14, 'Bolivianos', 'BOB', '$b', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (15, 'Convertible Marka', 'BAM', 'KM', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (16, 'Pula', 'BWP', 'P', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (17, 'Leva', 'BGN', 'лв', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (18, 'Reais', 'BRL', 'R$', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (19, 'Pounds', 'GBP', '£', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (20, 'Dollars', 'BND', '$', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (21, 'Riels', 'KHR', '៛', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (22, 'Dollars', 'CAD', '$', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (23, 'Dollars', 'KYD', '$', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (24, 'Pesos', 'CLP', '$', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (25, 'Yuan Renminbi', 'CNY', '¥', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (26, 'Pesos', 'COP', '$', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (27, 'Colón', 'CRC', '₡', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (28, 'Kuna', 'HRK', 'kn', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (29, 'Pesos', 'CUP', '₱', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (30, 'Koruny', 'CZK', 'Kč', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (31, 'Kroner', 'DKK', 'kr', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (32, 'Pesos', 'DOP', 'RD$', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (33, 'Dollars', 'XCD', '$', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (34, 'Pounds', 'EGP', '£', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (35, 'Colones', 'SVC', '$', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (36, 'Pounds', 'FKP', '£', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (37, 'Dollars', 'FJD', '$', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (38, 'Cedis', 'GHC', '¢', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (39, 'Pounds', 'GIP', '£', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (40, 'Quetzales', 'GTQ', 'Q', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (41, 'Pounds', 'GGP', '£', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (42, 'Dollars', 'GYD', '$', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (43, 'Lempiras', 'HNL', 'L', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (44, 'Dollars', 'HKD', '$', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (45, 'Forint', 'HUF', 'Ft', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (46, 'Kronur', 'ISK', 'kr', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (47, 'Rupees', 'INR', 'Rp', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (48, 'Rupiahs', 'IDR', 'Rp', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (49, 'Rials', 'IRR', '﷼', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (50, 'Pounds', 'IMP', '£', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (51, 'New Shekels', 'ILS', '₪', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (52, 'Dollars', 'JMD', 'J$', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (53, 'Yen', 'JPY', '¥', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (54, 'Pounds', 'JEP', '£', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (55, 'Tenge', 'KZT', 'лв', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (56, 'Won', 'KPW', '₩', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (57, 'Won', 'KRW', '₩', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (58, 'Soms', 'KGS', 'лв', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (59, 'Kips', 'LAK', '₭', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (60, 'Lati', 'LVL', 'Ls', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (61, 'Pounds', 'LBP', '£', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (62, 'Dollars', 'LRD', '$', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (63, 'Switzerland Francs', 'CHF', 'CHF', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (64, 'Litai', 'LTL', 'Lt', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (65, 'Denars', 'MKD', 'ден', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (66, 'Ringgits', 'MYR', 'RM', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (67, 'Rupees', 'MUR', '₨', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (68, 'Pesos', 'MXN', '$', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (69, 'Tugriks', 'MNT', '₮', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (70, 'Meticais', 'MZN', 'MT', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (71, 'Dollars', 'NAD', '$', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (72, 'Rupees', 'NPR', '₨', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (73, 'Guilders', 'ANG', 'ƒ', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (74, 'Dollars', 'NZD', '$', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (75, 'Cordobas', 'NIO', 'C$', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (76, 'Nairas', 'NGN', '₦', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (77, 'Krone', 'NOK', 'kr', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (78, 'Rials', 'OMR', '﷼', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (79, 'Rupees', 'PKR', '₨', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (80, 'Balboa', 'PAB', 'B/.', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (81, 'Guarani', 'PYG', 'Gs', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (82, 'Nuevos Soles', 'PEN', 'S/.', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (83, 'Pesos', 'PHP', 'Php', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (84, 'Zlotych', 'PLN', 'zł', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (85, 'Rials', 'QAR', '﷼', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (86, 'New Lei', 'RON', 'lei', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (87, 'Rubles', 'RUB', 'руб', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (88, 'Pounds', 'SHP', '£', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (89, 'Riyals', 'SAR', '﷼', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (90, 'Dinars', 'RSD', 'Дин.', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (91, 'Rupees', 'SCR', '₨', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (92, 'Dollars', 'SGD', '$', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (93, 'Dollars', 'SBD', '$', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (94, 'Shillings', 'SOS', 'S', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (95, 'Rand', 'ZAR', 'R', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (96, 'Rupees', 'LKR', '₨', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (97, 'Kronor', 'SEK', 'kr', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (98, 'Dollars', 'SRD', '$', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (99, 'Pounds', 'SYP', '£', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (100, 'New Dollars', 'TWD', 'NT$', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (101, 'Baht', 'THB', '฿', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (102, 'Dollars', 'TTD', 'TT$', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (103, 'Lira', 'TRY', '₺', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (104, 'Liras', 'TRL', '£', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (105, 'Dollars', 'TVD', '$', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (106, 'Hryvnia', 'UAH', '₴', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (107, 'Pesos', 'UYU', '$U', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (108, 'Sums', 'UZS', 'лв', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (109, 'Bolivares Fuertes', 'VEF', 'Bs', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (110, 'Dong', 'VND', '₫', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (111, 'Rials', 'YER', '﷼', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (112, 'Zimbabwe Dollars', 'ZWD', 'Z$', '2023-11-07 18:01:38', NULL);
INSERT INTO `currencies` VALUES (113, 'Rupees', 'INR', '₹', '2023-11-07 18:01:38', NULL);

-- ----------------------------
-- Table structure for currency_alloweds
-- ----------------------------
DROP TABLE IF EXISTS `currency_alloweds`;
CREATE TABLE `currency_alloweds`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `currency_id` bigint UNSIGNED NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `currency_alloweds_currency_id_foreign`(`currency_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = FIXED;

-- ----------------------------
-- Records of currency_alloweds
-- ----------------------------

-- ----------------------------
-- Table structure for custom_layouts
-- ----------------------------
DROP TABLE IF EXISTS `custom_layouts`;
CREATE TABLE `custom_layouts`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `font_family_default` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `primary_color` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#0073D2',
  `primary_opacity_color` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `secundary_color` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#084375',
  `gray_dark_color` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#3b3b3b',
  `gray_light_color` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#c9c9c9',
  `gray_medium_color` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#676767',
  `gray_over_color` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#1A1C20',
  `title_color` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#ffffff',
  `text_color` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#98A7B5',
  `sub_text_color` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#656E78',
  `placeholder_color` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#4D565E',
  `background_color` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#24262B',
  `background_base` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '#ECEFF1',
  `background_base_dark` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '#24262B',
  `carousel_banners` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '#1E2024',
  `carousel_banners_dark` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '#1E2024',
  `sidebar_color` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `sidebar_color_dark` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `navtop_color` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `navtop_color_dark` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `side_menu` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `side_menu_dark` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `input_primary` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `input_primary_dark` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `footer_color` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `footer_color_dark` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `card_color` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `card_color_dark` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `border_radius` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '.25rem',
  `custom_css` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `custom_js` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `custom_header` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `custom_body` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `instagram` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `facebook` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `telegram` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `twitter` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `whastapp` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `youtube` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of custom_layouts
-- ----------------------------
INSERT INTO `custom_layouts` VALUES (1, '\'Roboto Condensed\', sans-serif', '#1da639', '#03ad4022', '#0db849', '#3b3b3b', '#c9c9c9', '#676767', '#191A1E', '#ffffff', '#98A7B5', '#656E78', '#4D565E', '#24262B', '#e8e8e8', '#24262B', '#bdbdbd', '#1E2024', '#ffffff', '#191A1E', '#d8d8de', '#24262B', '#828282', '#24262B', '#dedede', '#1E2024', '#919191', '#1E2024', '#ababab', '#1E2024', '.25rem', NULL, NULL, NULL, NULL, '2024-01-01 14:36:03', '2024-03-30 22:29:45', 'https://www.instagram.com/victormsalatiel', 'https://www.facebook.com/groups/870751784800439', 'https://t.me/vipercassinos', 'https://twitter.com/Victormsalatiel', 'https://api.whatsapp.com/send?phone=5531986904249', 'https://www.youtube.com/c/VictorSalatiel');

-- ----------------------------
-- Table structure for debug
-- ----------------------------
DROP TABLE IF EXISTS `debug`;
CREATE TABLE `debug`  (
  `text` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of debug
-- ----------------------------
INSERT INTO `debug` VALUES ('\"PlaceBet: 914806915\"');
INSERT INTO `debug` VALUES ('\"PlaceBet: 914806915\"');
INSERT INTO `debug` VALUES ('\"AwardWinnings: 914806915\"');
INSERT INTO `debug` VALUES ('\"PlaceBet: 914806916\"');
INSERT INTO `debug` VALUES ('\"AwardWinnings: 914806916\"');
INSERT INTO `debug` VALUES ('\"PlaceBet: 914806916\"');
INSERT INTO `debug` VALUES ('\"PlaceBet: 914806917\"');
INSERT INTO `debug` VALUES ('\"PlaceBet: 914806918\"');
INSERT INTO `debug` VALUES ('\"AwardWinnings: 914806918\"');
INSERT INTO `debug` VALUES ('\"PlaceBet: 914806918\"');
INSERT INTO `debug` VALUES ('\"PlaceBet: 914806919\"');
INSERT INTO `debug` VALUES ('\"AwardWinnings: 914806919\"');
INSERT INTO `debug` VALUES ('\"AwardWinnings: 914806919\"');
INSERT INTO `debug` VALUES ('\"PlaceBet: 914806920\"');
INSERT INTO `debug` VALUES ('\"PlaceBet: 914806921\"');
INSERT INTO `debug` VALUES ('\"AwardWinnings: 914806921\"');
INSERT INTO `debug` VALUES ('\"PlaceBet: 535367\"');
INSERT INTO `debug` VALUES ('\"PlaceBet: 154646\"');
INSERT INTO `debug` VALUES ('\"PlaceBet: 914806922\"');
INSERT INTO `debug` VALUES ('\"PlaceBet: 914806923\"');
INSERT INTO `debug` VALUES ('\"PlaceBet: 914806924\"');
INSERT INTO `debug` VALUES ('\"AwardWinnings: 914806924\"');

-- ----------------------------
-- Table structure for deposits
-- ----------------------------
DROP TABLE IF EXISTS `deposits`;
CREATE TABLE `deposits`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `payment_id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `amount` decimal(20, 2) NOT NULL DEFAULT 0.00,
  `type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `proof` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT 0,
  `currency` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `symbol` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `deposits_user_id_foreign`(`user_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 8 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of deposits
-- ----------------------------
INSERT INTO `deposits` VALUES (1, '26565654654', 1, 109.00, 'deposit', NULL, 1, 'BRL', 'R$', '2024-08-02 13:25:47', NULL);
INSERT INTO `deposits` VALUES (2, '56654656546', 1, 100.00, 'deposit', NULL, 0, 'BRL ', 'R$', '2024-08-02 13:26:08', NULL);
INSERT INTO `deposits` VALUES (3, '55665465654', 1, 37.00, 'deposit', NULL, 1, 'BRL', 'R$', NULL, NULL);

-- ----------------------------
-- Table structure for digito_pay_payments
-- ----------------------------
DROP TABLE IF EXISTS `digito_pay_payments`;
CREATE TABLE `digito_pay_payments`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `payment_id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `withdrawal_id` bigint UNSIGNED NOT NULL,
  `pix_key` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `pix_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(10, 2) NOT NULL DEFAULT 0.00,
  `observation` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `status` tinyint NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `digito_pay_payments_user_id_foreign`(`user_id`) USING BTREE,
  INDEX `digito_pay_payments_withdrawal_id_foreign`(`withdrawal_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of digito_pay_payments
-- ----------------------------

-- ----------------------------
-- Table structure for failed_jobs
-- ----------------------------
DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `failed_jobs_uuid_unique`(`uuid`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of failed_jobs
-- ----------------------------

-- ----------------------------
-- Table structure for game_favorites
-- ----------------------------
DROP TABLE IF EXISTS `game_favorites`;
CREATE TABLE `game_favorites`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `game_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `game_favorites_user_id_game_id_unique`(`user_id`, `game_id`) USING BTREE,
  INDEX `game_favorites_game_id_foreign`(`game_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = FIXED;

-- ----------------------------
-- Records of game_favorites
-- ----------------------------

-- ----------------------------
-- Table structure for game_likes
-- ----------------------------
DROP TABLE IF EXISTS `game_likes`;
CREATE TABLE `game_likes`  (
  `user_id` bigint UNSIGNED NOT NULL,
  `game_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  UNIQUE INDEX `game_likes_user_id_game_id_unique`(`user_id`, `game_id`) USING BTREE,
  INDEX `game_likes_game_id_foreign`(`game_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = FIXED;

-- ----------------------------
-- Records of game_likes
-- ----------------------------

-- ----------------------------
-- Table structure for game_reviews
-- ----------------------------
DROP TABLE IF EXISTS `game_reviews`;
CREATE TABLE `game_reviews`  (
  `user_id` bigint UNSIGNED NOT NULL,
  `game_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `description` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `rating` int NOT NULL DEFAULT 0,
  UNIQUE INDEX `game_reviews_user_id_game_id_unique`(`user_id`, `game_id`) USING BTREE,
  INDEX `game_reviews_game_id_foreign`(`game_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of game_reviews
-- ----------------------------

-- ----------------------------
-- Table structure for games
-- ----------------------------
DROP TABLE IF EXISTS `games`;
CREATE TABLE `games`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `provider_id` int UNSIGNED NOT NULL,
  `game_server_url` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `game_id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `game_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `game_code` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `game_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `description` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `cover` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `status` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `technology` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `has_lobby` tinyint NOT NULL DEFAULT 0,
  `is_mobile` tinyint NOT NULL DEFAULT 0,
  `has_freespins` tinyint NOT NULL DEFAULT 0,
  `has_tables` tinyint NOT NULL DEFAULT 0,
  `only_demo` tinyint NULL DEFAULT 0,
  `rtp` bigint NOT NULL COMMENT 'Controle de RTP em porcentagem',
  `distribution` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'O nome do provedor',
  `views` bigint NOT NULL DEFAULT 0,
  `is_featured` tinyint(1) NULL DEFAULT 0,
  `show_home` tinyint(1) NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `games_provider_id_index`(`provider_id`) USING BTREE,
  INDEX `games_game_code_index`(`game_code`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 26 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of games
-- ----------------------------
INSERT INTO `games` VALUES (1, 1, NULL, 'pg-1', 'Honey Trap of Diao Chan', 'diaochan', NULL, NULL, 'venixcg/diaochan.webp', '1', 'html5', 0, 0, 0, 0, 0, 90, 'venixcg', 0, 0, 1, '2025-01-15 13:43:36', '2025-01-15 13:43:37');
INSERT INTO `games` VALUES (2, 1, NULL, 'pg-3', 'Fortune Gods', 'fortune-gods', NULL, NULL, 'venixcg/fortune-gods.webp', '1', 'html5', 0, 0, 0, 0, 0, 90, 'venixcg', 0, 0, 1, '2025-01-15 13:43:36', '2025-01-15 13:43:37');
INSERT INTO `games` VALUES (3, 1, NULL, 'pg-7', 'Medusa 1: The Curse of Athena', 'medusa', NULL, NULL, 'venixcg/medusa.webp', '1', 'html5', 0, 0, 0, 0, 0, 90, 'venixcg', 0, 0, 1, '2025-01-15 13:43:36', '2025-01-15 13:43:37');
INSERT INTO `games` VALUES (4, 1, NULL, 'pg-20', 'Reel Love', 'reel-love', NULL, NULL, 'venixcg/reel-love.webp', '1', 'html5', 0, 0, 0, 0, 0, 90, 'venixcg', 0, 0, 1, '2025-01-15 13:43:36', '2025-01-15 13:43:38');
INSERT INTO `games` VALUES (5, 1, NULL, 'pg-24', 'Win Win Won', 'win-win-won', NULL, NULL, 'venixcg/win-win-won.jpg', '1', 'html5', 0, 0, 0, 0, 0, 90, 'venixcg', 0, 0, 1, '2025-01-15 13:43:36', '2025-01-15 13:43:38');
INSERT INTO `games` VALUES (6, 1, NULL, 'pg-25', 'Plushie Frenzy', 'plushie-frenzy', NULL, NULL, 'venixcg/plushie-frenzy.webp', '1', 'html5', 0, 0, 0, 0, 0, 90, 'venixcg', 0, 0, 1, '2025-01-15 13:43:36', '2025-01-15 13:43:39');
INSERT INTO `games` VALUES (7, 1, NULL, 'pg-35', 'Mr. Hallow-Win', 'mr-hallow-win', NULL, NULL, 'venixcg/mr-hallow-win.webp', '1', 'html5', 0, 0, 0, 0, 0, 90, 'venixcg', 0, 0, 1, '2025-01-15 13:43:36', '2025-01-15 13:43:39');
INSERT INTO `games` VALUES (8, 1, NULL, 'pg-36', 'Prosperity Lion', 'prosperity-lion', NULL, NULL, 'venixcg/prosperity-lion.webp', '1', 'html5', 0, 0, 0, 0, 0, 90, 'venixcg', 0, 0, 1, '2025-01-15 13:43:36', '2025-01-15 13:43:39');
INSERT INTO `games` VALUES (9, 1, NULL, 'pg-37', 'Santa\'s Gift Rush', 'santas-gift-rush', NULL, NULL, 'venixcg/santas-gift-rush.webp', '1', 'html5', 0, 0, 0, 0, 0, 90, 'venixcg', 0, 0, 1, '2025-01-15 13:43:36', '2025-01-15 13:43:40');
INSERT INTO `games` VALUES (10, 1, NULL, 'pg-39', 'Piggy Gold', 'piggy-gold', NULL, NULL, 'venixcg/piggy-gold.jpg', '1', 'html5', 0, 0, 0, 0, 0, 90, 'venixcg', 0, 0, 1, '2025-01-15 13:43:36', '2025-01-15 13:43:40');
INSERT INTO `games` VALUES (11, 1, NULL, 'pg-40', 'Jungle Delight', 'jungle-delight', NULL, NULL, 'venixcg/jungle-delight.webp', '1', 'html5', 0, 0, 0, 0, 0, 90, 'venixcg', 0, 0, 1, '2025-01-15 13:43:36', '2025-01-15 13:49:37');
INSERT INTO `games` VALUES (12, 1, NULL, 'pg-44', 'Emperor\'s Favour', 'emperors-favour', NULL, NULL, 'venixcg/emperors-favour.jpg', '1', 'html5', 0, 0, 0, 0, 0, 90, 'venixcg', 0, 0, 1, '2025-01-15 13:43:36', '2025-01-15 13:43:41');
INSERT INTO `games` VALUES (13, 1, NULL, 'pg-50', 'Journey to the Wealth', 'journey-to-the-wealth', NULL, NULL, 'venixcg/journey-to-the-wealth.jpg', '1', 'html5', 0, 0, 0, 0, 0, 90, 'venixcg', 0, 0, 1, '2025-01-15 13:43:36', '2025-01-15 13:43:41');
INSERT INTO `games` VALUES (14, 1, NULL, 'pg-68', 'Fortune Mouse', 'fortune-mouse', NULL, NULL, 'venixcg/fortune-mouse.webp', '1', 'html5', 0, 0, 0, 0, 0, 90, 'venixcg', 0, 0, 1, '2025-01-15 13:43:36', '2025-01-15 13:43:42');
INSERT INTO `games` VALUES (15, 1, NULL, 'pg-98', 'Fortune Ox', 'fortune-ox', NULL, NULL, 'venixcg/fortune-ox.webp', '1', 'html5', 0, 0, 0, 0, 0, 90, 'venixcg', 0, 0, 1, '2025-01-15 13:43:36', '2025-01-15 13:43:42');
INSERT INTO `games` VALUES (16, 1, NULL, 'pg-126', 'Fortune Tiger', 'fortune-tiger', NULL, NULL, 'venixcg/fortune-tiger.webp', '1', 'html5', 0, 0, 0, 0, 0, 90, 'venixcg', 8, 1, 1, '2025-01-15 13:43:36', '2025-01-15 14:43:08');
INSERT INTO `games` VALUES (17, 1, NULL, 'pg-1543462', 'Fortune Rabbit', 'fortune-rabbit', NULL, NULL, 'venixcg/fortune-rabbit.webp', '1', 'html5', 0, 0, 0, 0, 0, 90, 'venixcg', 0, 0, 1, '2025-01-15 13:43:36', '2025-01-15 13:43:43');
INSERT INTO `games` VALUES (18, 1, NULL, 'pg-1601012', 'Lucky Clover Lady', 'lucky-clover', NULL, NULL, 'venixcg/lucky-clover.jpg', '1', 'html5', 0, 0, 0, 0, 0, 90, 'venixcg', 0, 0, 1, '2025-01-15 13:43:36', '2025-01-15 13:43:43');
INSERT INTO `games` VALUES (19, 1, NULL, 'pg-1695365', 'Fortune Dragon', 'fortune-dragon', NULL, NULL, 'venixcg/fortune-dragon.webp', '1', 'html5', 0, 0, 0, 0, 0, 90, 'venixcg', 0, 1, 1, '2025-01-15 13:43:36', '2025-01-15 13:46:45');
INSERT INTO `games` VALUES (20, 2, NULL, 'darwin-luvabet', 'Luva Melhor do Mundo', 'luvabet', NULL, NULL, 'venixcg/luvabet.webp', '1', 'html5', 0, 0, 0, 0, 0, 90, 'venixcg', 0, 0, 1, '2025-01-15 13:43:36', '2025-01-15 13:43:44');
INSERT INTO `games` VALUES (21, 2, NULL, 'darwin-luvamines', 'Luva Mines', 'luva-mines', NULL, NULL, 'venixcg/luva-mines.webp', '1', 'html5', 0, 0, 0, 0, 0, 90, 'venixcg', 0, 0, 1, '2025-01-15 13:43:36', '2025-01-15 13:43:44');
INSERT INTO `games` VALUES (22, 1, NULL, 'pg-1682240', 'Cash Mania', 'cash-mania', NULL, NULL, 'venixcg/cash-mania.webp', '1', 'html5', 0, 0, 0, 0, 0, 90, 'venixcg', 0, 1, 1, '2025-01-15 13:43:36', '2025-01-15 13:47:28');
INSERT INTO `games` VALUES (23, 3, NULL, 'nt-narcos', 'Narcos', 'nt-narcos', NULL, NULL, 'venixcg/nt-narcos.jpg', '1', 'html5', 0, 0, 0, 0, 0, 90, 'venixcg', 0, 1, 1, '2025-01-15 13:43:36', '2025-01-15 13:47:24');
INSERT INTO `games` VALUES (24, 1, NULL, 'pg-1738001', 'Chicky Run', 'chicky-run', NULL, NULL, 'venixcg/chicky-run.jpg', '1', 'html5', 0, 0, 0, 0, 0, 90, 'venixcg', 0, 1, 1, '2025-01-15 13:43:36', '2025-01-15 13:47:19');
INSERT INTO `games` VALUES (25, 1, NULL, 'pg-1747549', 'Wings of Iguazu', 'wings-iguazu', NULL, NULL, 'venixcg/wings-iguazu.webp', '1', 'html5', 0, 0, 0, 0, 0, 90, 'venixcg', 0, 1, 1, '2025-01-15 13:43:36', '2025-01-15 13:47:15');

-- ----------------------------
-- Table structure for games_keys
-- ----------------------------
DROP TABLE IF EXISTS `games_keys`;
CREATE TABLE `games_keys`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `merchant_url` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `merchant_id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `merchant_key` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `agent_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `agent_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `agent_secret_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `api_endpoint` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `salsa_base_uri` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `salsa_pn` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `salsa_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `vibra_site_id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `vibra_game_mode` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `worldslot_agent_code` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `worldslot_agent_token` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `worldslot_agent_secret_key` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `worldslot_api_endpoint` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'https://api.worldslotgame.com/api/v2/',
  `games2_agent_code` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `games2_agent_token` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `games2_agent_secret_key` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `games2_api_endpoint` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'api.games2api.xyz',
  `evergame_agent_code` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `evergame_agent_token` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `evergame_api_endpoint` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `venix_agent_code` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `venix_agent_token` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `venix_agent_secret` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `play_gaming_hall` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `play_gaming_key` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `play_gaming_login` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `pig_agent_code` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `pig_agent_token` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `pig_agent_secret` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `venixcg_api_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `venixcg_agent_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `venixcg_agent_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `venixcg_agent_secret` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of games_keys
-- ----------------------------
INSERT INTO `games_keys` VALUES (1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-15 13:17:22', '2025-01-15 13:26:13', NULL, NULL, NULL, NULL, NULL, 'https://api.worldslotgame.com/api/v2/', NULL, NULL, NULL, 'api.games2api.xyz', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://venix.games/api/v1/', NULL, NULL, NULL);

-- ----------------------------
-- Table structure for gateways
-- ----------------------------
DROP TABLE IF EXISTS `gateways`;
CREATE TABLE `gateways`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `suitpay_uri` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `suitpay_cliente_id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `suitpay_cliente_secret` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `stripe_production` tinyint NULL DEFAULT 0,
  `stripe_public_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `stripe_secret_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `stripe_webhook_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `bspay_uri` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `bspay_cliente_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `bspay_cliente_secret` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `public_key` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `private_key` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `mp_client_id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `mp_client_secret` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `mp_public_key` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `mp_access_token` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `digitopay_uri` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `digitopay_cliente_id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `digitopay_cliente_secret` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `shark_public_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `shark_private_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of gateways
-- ----------------------------
INSERT INTO `gateways` VALUES (1, 'https://ws.suitpay.app/api/v1/', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2023-11-30 21:05:51', '2025-01-15 14:57:50', NULL, NULL, NULL, NULL, NULL, NULL, 'https://api.digitopayoficial.com.br/', NULL, NULL, NULL, NULL);

-- ----------------------------
-- Table structure for ggds_spin_config
-- ----------------------------
DROP TABLE IF EXISTS `ggds_spin_config`;
CREATE TABLE `ggds_spin_config`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `prizes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of ggds_spin_config
-- ----------------------------
INSERT INTO `ggds_spin_config` VALUES (1, '[{\"currency\":\"BRL\",\"value\":5},{\"currency\":\"BRL\",\"value\":10},{\"currency\":\"BRL\",\"value\":25},{\"currency\":\"BRL\",\"value\":30},{\"currency\":\"BRL\",\"value\":40},{\"currency\":\"BRL\",\"value\":50},{\"currency\":\"BRL\",\"value\":90},{\"currency\":\"BRL\",\"value\":100},{\"currency\":\"BRL\",\"value\":140},{\"currency\":\"BRL\",\"value\":15},{\"currency\":\"BRL\",\"value\":30},{\"currency\":\"BRL\",\"value\":500},{\"currency\":\"BRL\",\"value\":1000},{\"currency\":\"BRL\",\"value\":1500},{\"currency\":\"BRL\",\"value\":2000},{\"currency\":\"BRL\",\"value\":2500}]', '2024-01-11 20:52:59', '2024-01-14 17:07:12');

-- ----------------------------
-- Table structure for ggds_spin_runs
-- ----------------------------
DROP TABLE IF EXISTS `ggds_spin_runs`;
CREATE TABLE `ggds_spin_runs`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nonce` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `possibilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `prize` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 14 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of ggds_spin_runs
-- ----------------------------
INSERT INTO `ggds_spin_runs` VALUES (1, 'bccec5b62ab37e996470f8bb36ff83ea127a73f17f31cba275453f5ed7b24c34', '592f4304-4837-4a81-8f27-6c3ac412c7dc', '[{\"currency\":\"brl\",\"value\":5.02},{\"currency\":\"brl\",\"value\":9.79},{\"currency\":\"brl\",\"value\":25},{\"currency\":\"brl\",\"value\":30},{\"currency\":\"brl\",\"value\":40},{\"currency\":\"brl\",\"value\":50},{\"currency\":\"brl\",\"value\":90},{\"currency\":\"brl\",\"value\":105},{\"currency\":\"brl\",\"value\":140},{\"currency\":\"brl\",\"value\":15},{\"currency\":\"brl\",\"value\":30},{\"currency\":\"brl\",\"value\":500},{\"currency\":\"brl\",\"value\":980},{\"currency\":\"brl\",\"value\":1500},{\"currency\":\"brl\",\"value\":2000},{\"currency\":\"brl\",\"value\":2500}]', '{\"currency\":\"brl\",\"value\":2000}', '2024-01-12 15:49:28', '2024-01-12 15:49:28');
INSERT INTO `ggds_spin_runs` VALUES (2, '93f0c422ecb15337d358cca690529c9db7d9b7a52acc0727092b938ade4e1c9b', 'eff5d8c3-475b-4994-a49d-778cf06aebf4', '[{\"currency\":\"brl\",\"value\":5.02},{\"currency\":\"brl\",\"value\":9.79},{\"currency\":\"brl\",\"value\":25},{\"currency\":\"brl\",\"value\":30},{\"currency\":\"brl\",\"value\":40},{\"currency\":\"brl\",\"value\":50},{\"currency\":\"brl\",\"value\":90},{\"currency\":\"brl\",\"value\":105},{\"currency\":\"brl\",\"value\":140},{\"currency\":\"brl\",\"value\":15},{\"currency\":\"brl\",\"value\":30},{\"currency\":\"brl\",\"value\":500},{\"currency\":\"brl\",\"value\":980},{\"currency\":\"brl\",\"value\":1500},{\"currency\":\"brl\",\"value\":2000},{\"currency\":\"brl\",\"value\":2500}]', '{\"currency\":\"brl\",\"value\":25}', '2024-01-12 15:51:01', '2024-01-12 15:51:01');
INSERT INTO `ggds_spin_runs` VALUES (3, 'c7f0b2cc871954aa3a03f680969f029034d111e5550ee0769389fefe3fd6f9fd', '203098ef-3347-4938-813e-91be82891cbc', '[{\"currency\":\"brl\",\"value\":5.02},{\"currency\":\"brl\",\"value\":9.79},{\"currency\":\"brl\",\"value\":25},{\"currency\":\"brl\",\"value\":30},{\"currency\":\"brl\",\"value\":40},{\"currency\":\"brl\",\"value\":50},{\"currency\":\"brl\",\"value\":90},{\"currency\":\"brl\",\"value\":105},{\"currency\":\"brl\",\"value\":140},{\"currency\":\"brl\",\"value\":15},{\"currency\":\"brl\",\"value\":30},{\"currency\":\"brl\",\"value\":500},{\"currency\":\"brl\",\"value\":980},{\"currency\":\"brl\",\"value\":1500},{\"currency\":\"brl\",\"value\":2000},{\"currency\":\"brl\",\"value\":2500}]', '{\"currency\":\"brl\",\"value\":105}', '2024-01-12 15:51:34', '2024-01-12 15:51:34');
INSERT INTO `ggds_spin_runs` VALUES (4, '5b4059c88237be6a78ff1fb35e427fd1e4e1a30184ced8d3e4ed9044a0939663', '65cf85de-3dee-48f4-a1d3-d26a8f89ba16', '[{\"currency\":\"brl\",\"value\":5.02},{\"currency\":\"brl\",\"value\":9.79},{\"currency\":\"brl\",\"value\":25},{\"currency\":\"brl\",\"value\":30},{\"currency\":\"brl\",\"value\":40},{\"currency\":\"brl\",\"value\":50},{\"currency\":\"brl\",\"value\":90},{\"currency\":\"brl\",\"value\":105},{\"currency\":\"brl\",\"value\":140},{\"currency\":\"brl\",\"value\":15},{\"currency\":\"brl\",\"value\":30},{\"currency\":\"brl\",\"value\":500},{\"currency\":\"brl\",\"value\":980},{\"currency\":\"brl\",\"value\":1500},{\"currency\":\"brl\",\"value\":2000},{\"currency\":\"brl\",\"value\":2500}]', '{\"currency\":\"brl\",\"value\":2000}', '2024-01-12 16:56:56', '2024-01-12 16:56:56');
INSERT INTO `ggds_spin_runs` VALUES (5, '0901814bf6598c5c7e1072abcc8fa16290dff49b3c2aa44a9346cee34444f98f', '79519214-2125-4ed5-a672-45973c8af5eb', '[{\"currency\":\"brl\",\"value\":5.02},{\"currency\":\"brl\",\"value\":9.79},{\"currency\":\"brl\",\"value\":25},{\"currency\":\"brl\",\"value\":30},{\"currency\":\"brl\",\"value\":40},{\"currency\":\"brl\",\"value\":50},{\"currency\":\"brl\",\"value\":90},{\"currency\":\"brl\",\"value\":105},{\"currency\":\"brl\",\"value\":140},{\"currency\":\"brl\",\"value\":15},{\"currency\":\"brl\",\"value\":30},{\"currency\":\"brl\",\"value\":500},{\"currency\":\"brl\",\"value\":980},{\"currency\":\"brl\",\"value\":1500},{\"currency\":\"brl\",\"value\":2000},{\"currency\":\"brl\",\"value\":2500}]', '{\"currency\":\"brl\",\"value\":2500}', '2024-01-12 16:57:47', '2024-01-12 16:57:47');
INSERT INTO `ggds_spin_runs` VALUES (6, '7b5642d63b3081907a3b7489794ba74f3052fe490095e7354d98570f6c4e5a6c', 'e5b728db-07e3-4531-910b-7c85f1555973', '[{\"currency\":\"brl\",\"value\":5.02},{\"currency\":\"brl\",\"value\":9.79},{\"currency\":\"brl\",\"value\":25},{\"currency\":\"brl\",\"value\":30},{\"currency\":\"brl\",\"value\":40},{\"currency\":\"brl\",\"value\":50},{\"currency\":\"brl\",\"value\":90},{\"currency\":\"brl\",\"value\":105},{\"currency\":\"brl\",\"value\":140},{\"currency\":\"brl\",\"value\":15},{\"currency\":\"brl\",\"value\":30},{\"currency\":\"brl\",\"value\":500},{\"currency\":\"brl\",\"value\":980},{\"currency\":\"brl\",\"value\":1500},{\"currency\":\"brl\",\"value\":2000},{\"currency\":\"brl\",\"value\":2500}]', '{\"currency\":\"brl\",\"value\":9.79}', '2024-01-12 18:51:25', '2024-01-12 18:51:25');
INSERT INTO `ggds_spin_runs` VALUES (7, '001f8f60f30e431a9f28d39a4db43e0b3e03d0379816fe04ff5007aca5d0a14e', '4ab2acba-57f3-46f4-9dc6-c32e993fb995', '[{\"currency\":\"brl\",\"value\":5.02},{\"currency\":\"brl\",\"value\":9.79},{\"currency\":\"brl\",\"value\":25},{\"currency\":\"brl\",\"value\":30},{\"currency\":\"brl\",\"value\":40},{\"currency\":\"brl\",\"value\":50},{\"currency\":\"brl\",\"value\":90},{\"currency\":\"brl\",\"value\":105},{\"currency\":\"brl\",\"value\":140},{\"currency\":\"brl\",\"value\":15},{\"currency\":\"brl\",\"value\":30},{\"currency\":\"brl\",\"value\":500},{\"currency\":\"brl\",\"value\":980},{\"currency\":\"brl\",\"value\":1500},{\"currency\":\"brl\",\"value\":2000},{\"currency\":\"brl\",\"value\":2500}]', '{\"currency\":\"brl\",\"value\":1500}', '2024-01-12 19:13:25', '2024-01-12 19:13:25');
INSERT INTO `ggds_spin_runs` VALUES (8, '8a801a13e786df312b4d3a2a023b5472cfca776b2b9524af3c12fac4e00bb935', 'e050a5ad-ab8a-4922-95cc-6fae7f62be99', '[{\"currency\":\"brl\",\"value\":5.02},{\"currency\":\"brl\",\"value\":9.79},{\"currency\":\"brl\",\"value\":25},{\"currency\":\"brl\",\"value\":30},{\"currency\":\"brl\",\"value\":40},{\"currency\":\"brl\",\"value\":50},{\"currency\":\"brl\",\"value\":90},{\"currency\":\"brl\",\"value\":105},{\"currency\":\"brl\",\"value\":140},{\"currency\":\"brl\",\"value\":15},{\"currency\":\"brl\",\"value\":30},{\"currency\":\"brl\",\"value\":500},{\"currency\":\"brl\",\"value\":980},{\"currency\":\"brl\",\"value\":1500},{\"currency\":\"brl\",\"value\":2000},{\"currency\":\"brl\",\"value\":2500}]', '{\"currency\":\"brl\",\"value\":2500}', '2024-01-12 19:40:12', '2024-01-12 19:40:12');
INSERT INTO `ggds_spin_runs` VALUES (9, '3432d11c1efd935afcf84fba6c3466c3c98aee06c63163368a18b7d07749ea66', 'de330a3f-b3d0-41ef-94ae-2cee7686f341', '[{\"currency\":\"brl\",\"value\":5.02},{\"currency\":\"brl\",\"value\":9.79},{\"currency\":\"brl\",\"value\":25},{\"currency\":\"brl\",\"value\":30},{\"currency\":\"brl\",\"value\":40},{\"currency\":\"brl\",\"value\":50},{\"currency\":\"brl\",\"value\":90},{\"currency\":\"brl\",\"value\":105},{\"currency\":\"brl\",\"value\":140},{\"currency\":\"brl\",\"value\":15},{\"currency\":\"brl\",\"value\":30},{\"currency\":\"brl\",\"value\":500},{\"currency\":\"brl\",\"value\":980},{\"currency\":\"brl\",\"value\":1500},{\"currency\":\"brl\",\"value\":2000},{\"currency\":\"brl\",\"value\":2500}]', '{\"currency\":\"brl\",\"value\":15}', '2024-01-12 20:01:33', '2024-01-12 20:01:33');
INSERT INTO `ggds_spin_runs` VALUES (10, 'cf741c182225f003f16fb815517a3d94d9cde7e16d767766864c7536ae51e101', 'ec3a409c-159f-4723-848c-7a45fe838c65', '[{\"currency\":\"brl\",\"value\":5.02},{\"currency\":\"brl\",\"value\":9.79},{\"currency\":\"brl\",\"value\":25},{\"currency\":\"brl\",\"value\":30},{\"currency\":\"brl\",\"value\":40},{\"currency\":\"brl\",\"value\":50},{\"currency\":\"brl\",\"value\":90},{\"currency\":\"brl\",\"value\":105},{\"currency\":\"brl\",\"value\":140},{\"currency\":\"brl\",\"value\":15},{\"currency\":\"brl\",\"value\":30},{\"currency\":\"brl\",\"value\":500},{\"currency\":\"brl\",\"value\":980},{\"currency\":\"brl\",\"value\":1500},{\"currency\":\"brl\",\"value\":2000},{\"currency\":\"brl\",\"value\":2500}]', '{\"currency\":\"brl\",\"value\":105}', '2024-01-12 20:26:04', '2024-01-12 20:26:04');
INSERT INTO `ggds_spin_runs` VALUES (11, 'f7a528bd425441c09093c146b4456acc0391b2aaf6af26ed7282dd8b153411df', 'b9e39a17-328e-4730-979f-bd37714552f1', '[{\"currency\":\"brl\",\"value\":5.02},{\"currency\":\"brl\",\"value\":9.79},{\"currency\":\"brl\",\"value\":25},{\"currency\":\"brl\",\"value\":30},{\"currency\":\"brl\",\"value\":40},{\"currency\":\"brl\",\"value\":50},{\"currency\":\"brl\",\"value\":90},{\"currency\":\"brl\",\"value\":105},{\"currency\":\"brl\",\"value\":140},{\"currency\":\"brl\",\"value\":15},{\"currency\":\"brl\",\"value\":30},{\"currency\":\"brl\",\"value\":500},{\"currency\":\"brl\",\"value\":980},{\"currency\":\"brl\",\"value\":1500},{\"currency\":\"brl\",\"value\":2000},{\"currency\":\"brl\",\"value\":2500}]', '{\"currency\":\"brl\",\"value\":5.02}', '2024-01-13 01:02:21', '2024-01-13 01:02:21');
INSERT INTO `ggds_spin_runs` VALUES (12, '7ec6e7dea1750162832a02bbcb986e3a77e4ea3ece5110b0c8c746e9ab586d27', '876d2835-b5c5-4c2f-997d-007fa566d356', '[{\"currency\":\"brl\",\"value\":5.02},{\"currency\":\"brl\",\"value\":9.79},{\"currency\":\"brl\",\"value\":25},{\"currency\":\"brl\",\"value\":30},{\"currency\":\"brl\",\"value\":40},{\"currency\":\"brl\",\"value\":50},{\"currency\":\"brl\",\"value\":90},{\"currency\":\"brl\",\"value\":105},{\"currency\":\"brl\",\"value\":140},{\"currency\":\"brl\",\"value\":15},{\"currency\":\"brl\",\"value\":30},{\"currency\":\"brl\",\"value\":500},{\"currency\":\"brl\",\"value\":980},{\"currency\":\"brl\",\"value\":1500},{\"currency\":\"brl\",\"value\":2000},{\"currency\":\"brl\",\"value\":2500}]', '{\"currency\":\"brl\",\"value\":980}', '2024-01-13 13:40:26', '2024-01-13 13:40:26');
INSERT INTO `ggds_spin_runs` VALUES (13, '7980558afa73f95c09d898c3f40a488de06097822831729b21659e6e330d6f92', '19962ee0-52f1-45dd-b858-4003deb2eb43', '[{\"currency\":\"BRL\",\"value\":5},{\"currency\":\"BRL\",\"value\":10},{\"currency\":\"BRL\",\"value\":25},{\"currency\":\"BRL\",\"value\":30},{\"currency\":\"BRL\",\"value\":40},{\"currency\":\"BRL\",\"value\":50},{\"currency\":\"BRL\",\"value\":90},{\"currency\":\"BRL\",\"value\":100},{\"currency\":\"BRL\",\"value\":140},{\"currency\":\"BRL\",\"value\":15},{\"currency\":\"BRL\",\"value\":30},{\"currency\":\"BRL\",\"value\":500},{\"currency\":\"BRL\",\"value\":1000},{\"currency\":\"BRL\",\"value\":1500},{\"currency\":\"BRL\",\"value\":2000},{\"currency\":\"BRL\",\"value\":2500}]', '{\"currency\":\"BRL\",\"value\":30}', '2024-01-15 19:28:00', '2024-01-15 19:28:00');

-- ----------------------------
-- Table structure for ggr_games
-- ----------------------------
DROP TABLE IF EXISTS `ggr_games`;
CREATE TABLE `ggr_games`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED NOT NULL,
  `provider` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `game` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `balance_bet` decimal(20, 2) NOT NULL DEFAULT 0.00,
  `balance_win` decimal(20, 2) NOT NULL DEFAULT 0.00,
  `currency` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `aggregator` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `ggr_games_fivers_user_id_index`(`user_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of ggr_games
-- ----------------------------

-- ----------------------------
-- Table structure for ggr_games_world_slots
-- ----------------------------
DROP TABLE IF EXISTS `ggr_games_world_slots`;
CREATE TABLE `ggr_games_world_slots`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED NOT NULL,
  `provider` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `game` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `balance_bet` decimal(20, 2) NOT NULL DEFAULT 0.00,
  `balance_win` decimal(20, 2) NOT NULL DEFAULT 0.00,
  `currency` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'BRL',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `ggr_games_world_slots_user_id_index`(`user_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of ggr_games_world_slots
-- ----------------------------

-- ----------------------------
-- Table structure for jobs
-- ----------------------------
DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED NULL DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `jobs_queue_index`(`queue` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 26 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of jobs
-- ----------------------------

-- ----------------------------
-- Table structure for likes
-- ----------------------------
DROP TABLE IF EXISTS `likes`;
CREATE TABLE `likes`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `liked_user_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `likes_user_id_foreign`(`user_id`) USING BTREE,
  INDEX `likes_liked_user_id_foreign`(`liked_user_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = FIXED;

-- ----------------------------
-- Records of likes
-- ----------------------------

-- ----------------------------
-- Table structure for migrations
-- ----------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 100 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of migrations
-- ----------------------------
INSERT INTO `migrations` VALUES (1, '0000_00_00_000000_create_websockets_statistics_entries_table', 1);
INSERT INTO `migrations` VALUES (2, '2014_10_12_000000_create_users_table', 1);
INSERT INTO `migrations` VALUES (3, '2014_10_12_100000_create_password_reset_tokens_table', 1);
INSERT INTO `migrations` VALUES (4, '2019_08_19_000000_create_failed_jobs_table', 1);
INSERT INTO `migrations` VALUES (5, '2019_12_14_000001_create_personal_access_tokens_table', 1);
INSERT INTO `migrations` VALUES (15, '2023_10_30_034921_create_settings_table', 6);
INSERT INTO `migrations` VALUES (7, '2023_11_03_205251_create_sport_categories_table', 1);
INSERT INTO `migrations` VALUES (8, '2023_11_03_205252_create_sports_table', 1);
INSERT INTO `migrations` VALUES (10, '2023_11_04_001342_create_sport_events_table', 2);
INSERT INTO `migrations` VALUES (11, '2023_11_04_213841_add_fields_to_users_table', 3);
INSERT INTO `migrations` VALUES (12, '2023_11_07_202919_create_currencies_table', 4);
INSERT INTO `migrations` VALUES (13, '2023_11_07_210310_create_wallets_table', 5);
INSERT INTO `migrations` VALUES (16, '2023_11_07_214236_create_withdrawals_table', 7);
INSERT INTO `migrations` VALUES (17, '2023_11_07_214240_create_deposits_table', 7);
INSERT INTO `migrations` VALUES (18, '2023_11_07_214244_create_orders_table', 7);
INSERT INTO `migrations` VALUES (19, '2023_11_07_214708_create_suit_pay_payments_table', 7);
INSERT INTO `migrations` VALUES (20, '2023_11_07_215204_create_notifications_table', 8);
INSERT INTO `migrations` VALUES (21, '2023_11_07_202919_create_currency_alloweds_table', 9);
INSERT INTO `migrations` VALUES (22, '2023_11_11_205824_create_casino_categories_table', 9);
INSERT INTO `migrations` VALUES (23, '2023_11_11_205834_create_casino_providers_table', 9);
INSERT INTO `migrations` VALUES (24, '2023_11_11_205938_create_casino_games_slotgrators_table', 9);
INSERT INTO `migrations` VALUES (25, '2023_11_11_210018_create_casino_games_kscinuses_table', 9);
INSERT INTO `migrations` VALUES (26, '2023_11_12_225424_create_transactions_table', 10);
INSERT INTO `migrations` VALUES (27, '2023_11_12_225431_create_affiliate_histories_table', 10);
INSERT INTO `migrations` VALUES (28, '2023_11_12_234643_add_field_to_wallet_table', 11);
INSERT INTO `migrations` VALUES (29, '2023_11_14_203632_create_likes_table', 12);
INSERT INTO `migrations` VALUES (30, '2023_09_27_214903_create_wallet_changes_table', 13);
INSERT INTO `migrations` VALUES (31, '2023_11_16_155140_create_permission_tables', 14);
INSERT INTO `migrations` VALUES (32, '2023_11_17_012533_add_language_to_users_table', 15);
INSERT INTO `migrations` VALUES (33, '2023_11_22_171616_create_football_leagues_table', 16);
INSERT INTO `migrations` VALUES (34, '2023_11_22_175530_create_football_venues_table', 17);
INSERT INTO `migrations` VALUES (35, '2023_11_22_175547_create_football_teams_table', 17);
INSERT INTO `migrations` VALUES (36, '2023_11_23_143637_create_football_events_table', 18);
INSERT INTO `migrations` VALUES (38, '2023_11_29_134520_create_sport_bets_table', 19);
INSERT INTO `migrations` VALUES (39, '2023_11_29_135451_create_sport_bets_odds_table', 19);
INSERT INTO `migrations` VALUES (40, '2023_11_30_195548_create_gateways_table', 20);
INSERT INTO `migrations` VALUES (41, '2023_11_30_195557_create_games_keys_table', 20);
INSERT INTO `migrations` VALUES (42, '2023_11_30_195609_create_setting_mails_table', 20);
INSERT INTO `migrations` VALUES (43, '2023_10_08_111755_add_fields_to_game_exclusives_table', 20);
INSERT INTO `migrations` VALUES (44, '2023_10_07_183921_create_game_exclusives_table', 21);
INSERT INTO `migrations` VALUES (45, '2023_10_11_144956_create_system_wallets_table', 22);
INSERT INTO `migrations` VALUES (46, '2023_12_18_172721_create_banners_table', 23);
INSERT INTO `migrations` VALUES (47, '2023_12_20_135908_create_casino_games_salsas_table', 24);
INSERT INTO `migrations` VALUES (48, '2023_12_23_224032_create_fivers_providers_table', 25);
INSERT INTO `migrations` VALUES (49, '2023_12_23_224105_create_fivers_games_table', 25);
INSERT INTO `migrations` VALUES (50, '2023_12_31_121453_create_custom_layouts_table', 26);
INSERT INTO `migrations` VALUES (51, '2024_01_01_193712_create_g_g_r_games_fivers_table', 27);
INSERT INTO `migrations` VALUES (52, '2024_01_14_155144_create_missions_table', 28);
INSERT INTO `migrations` VALUES (53, '2024_01_14_155150_create_mission_users_table', 28);
INSERT INTO `migrations` VALUES (54, '2024_01_19_120728_create_ka_gamings_table', 29);
INSERT INTO `migrations` VALUES (55, '2024_01_19_170650_create_categories_table', 30);
INSERT INTO `migrations` VALUES (56, '2024_01_19_170657_create_providers_table', 30);
INSERT INTO `migrations` VALUES (57, '2024_01_19_170658_create_games_table', 31);
INSERT INTO `migrations` VALUES (58, '2023_10_07_183920_create_categories_table', 32);
INSERT INTO `migrations` VALUES (59, '2023_10_07_183921_create_providers_table', 33);
INSERT INTO `migrations` VALUES (60, '2023_10_07_183922_create_games_table', 34);
INSERT INTO `migrations` VALUES (61, '2024_01_20_144529_create_category_games_table', 35);
INSERT INTO `migrations` VALUES (62, '2024_01_20_182155_add_vibra_to_games_keys_table', 36);
INSERT INTO `migrations` VALUES (63, '2024_01_21_173742_create_game_favorites_table', 37);
INSERT INTO `migrations` VALUES (64, '2024_01_21_173752_create_game_likes_table', 37);
INSERT INTO `migrations` VALUES (65, '2024_01_21_173803_create_game_reviews_table', 37);
INSERT INTO `migrations` VALUES (66, '2024_01_11_231932_create_vibra_casino_games_table', 38);
INSERT INTO `migrations` VALUES (69, '2024_01_28_194456_add_vip_to_wallet_table', 40);
INSERT INTO `migrations` VALUES (68, '2024_01_28_194645_create_vips_table', 39);
INSERT INTO `migrations` VALUES (70, '2024_01_28_231843_create_vip_users_table', 41);
INSERT INTO `migrations` VALUES (71, '2024_01_29_102939_add_paid_to_limits_table', 41);
INSERT INTO `migrations` VALUES (72, '2024_01_10_001705_create_sub_affiliates_table', 42);
INSERT INTO `migrations` VALUES (73, '2024_01_30_120547_create_affiliate_withdraws_table', 43);
INSERT INTO `migrations` VALUES (74, '2024_02_09_233936_add_worldslot_to_games_keys_table', 44);
INSERT INTO `migrations` VALUES (75, '2024_02_10_191215_add_disable_spin_to_settings_table', 45);
INSERT INTO `migrations` VALUES (76, '2024_02_17_210822_add_games2_to_games_keys_table', 46);
INSERT INTO `migrations` VALUES (78, '2024_02_20_004853_add_sub_to_settings_table', 47);
INSERT INTO `migrations` VALUES (79, '2024_02_24_121146_add_header_to_custom_layouts_table', 48);
INSERT INTO `migrations` VALUES (80, '2024_02_26_144235_create_ggr_games_world_slots_table', 49);
INSERT INTO `migrations` VALUES (81, '2024_03_01_172613_add_evergame_to_games_keys_table', 50);
INSERT INTO `migrations` VALUES (82, '2024_03_03_201700_add_venixkey_to_games_keys_table', 51);
INSERT INTO `migrations` VALUES (83, '2024_03_08_201557_add_play_gaming_to_games_keys_table', 52);
INSERT INTO `migrations` VALUES (84, '2024_03_21_154342_add_headerbody_to_custom_layouts_table', 53);
INSERT INTO `migrations` VALUES (85, '2024_03_21_154342_add_headerbody_to_custom_layouts_table', 54);
INSERT INTO `migrations` VALUES (86, '2024_03_21_223739_add_sharkpay_to_gateways_table', 55);
INSERT INTO `migrations` VALUES (87, '2024_03_21_230017_add_reference_to_transactions_table', 56);
INSERT INTO `migrations` VALUES (88, '2024_03_24_125025_add_rollver_protection_to_settings_table', 57);
INSERT INTO `migrations` VALUES (89, '2024_03_24_134101_add_accept_bonus_to_transactions_table', 58);
INSERT INTO `migrations` VALUES (90, '2024_03_24_172243_add_receita_to_affiliate_histories_table', 59);
INSERT INTO `migrations` VALUES (91, '2024_03_26_161932_add_baseline_column_to_settings_table', 60);
INSERT INTO `migrations` VALUES (92, '2024_03_26_234226_add_playigaming_column_to_games_key_table', 61);
INSERT INTO `migrations` VALUES (93, '2024_03_25_231103_add_mercado_pago_column_to_gateways_table', 62);
INSERT INTO `migrations` VALUES (94, '2024_03_30_215051_add_social_to_custom_layouts_table', 63);
INSERT INTO `migrations` VALUES (95, '2024_03_30_225900_create_digito_pay_payments_table', 64);
INSERT INTO `migrations` VALUES (96, '2024_03_30_225929_add_digitopay_to_gateways_table', 64);
INSERT INTO `migrations` VALUES (97, '2024_03_31_124211_add_digitopay_to_settings_table', 65);
INSERT INTO `migrations` VALUES (98, '2024_08_05_170433_create_reports_table', 66);
INSERT INTO `migrations` VALUES (99, '2025_01_15_134307_create_jobs_table', 67);

-- ----------------------------
-- Table structure for mission_users
-- ----------------------------
DROP TABLE IF EXISTS `mission_users`;
CREATE TABLE `mission_users`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED NOT NULL,
  `mission_id` int UNSIGNED NOT NULL,
  `rounds` bigint NULL DEFAULT 0,
  `rewards` decimal(10, 2) NULL DEFAULT 0.00,
  `status` tinyint NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `mission_users_user_id_index`(`user_id`) USING BTREE,
  INDEX `mission_users_mission_id_index`(`mission_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = FIXED;

-- ----------------------------
-- Records of mission_users
-- ----------------------------

-- ----------------------------
-- Table structure for missions
-- ----------------------------
DROP TABLE IF EXISTS `missions`;
CREATE TABLE `missions`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `challenge_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `challenge_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `challenge_rules` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `challenge_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'game',
  `challenge_link` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `challenge_start_date` datetime NOT NULL,
  `challenge_end_date` datetime NOT NULL,
  `challenge_bonus` decimal(20, 2) NOT NULL DEFAULT 0.00,
  `challenge_total` bigint NOT NULL DEFAULT 1,
  `challenge_currency` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `challenge_provider` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `challenge_gameid` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of missions
-- ----------------------------

-- ----------------------------
-- Table structure for model_has_permissions
-- ----------------------------
DROP TABLE IF EXISTS `model_has_permissions`;
CREATE TABLE `model_has_permissions`  (
  `permission_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL,
  PRIMARY KEY (`permission_id`, `model_id`, `model_type`) USING BTREE,
  INDEX `model_has_permissions_model_id_model_type_index`(`model_id` ASC, `model_type` ASC) USING BTREE,
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of model_has_permissions
-- ----------------------------

-- ----------------------------
-- Table structure for model_has_roles
-- ----------------------------
DROP TABLE IF EXISTS `model_has_roles`;
CREATE TABLE `model_has_roles`  (
  `role_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL,
  PRIMARY KEY (`role_id`, `model_id`, `model_type`) USING BTREE,
  INDEX `model_has_roles_model_id_model_type_index`(`model_id` ASC, `model_type` ASC) USING BTREE,
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of model_has_roles
-- ----------------------------
INSERT INTO `model_has_roles` VALUES (1, 'App\\Models\\User', 1);

-- ----------------------------
-- Table structure for notifications
-- ----------------------------
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications`  (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint UNSIGNED NOT NULL,
  `data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `notifications_notifiable_type_notifiable_id_index`(`notifiable_type`, `notifiable_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of notifications
-- ----------------------------

-- ----------------------------
-- Table structure for orders
-- ----------------------------
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `session_id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `transaction_id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `game` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `game_uuid` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_money` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(20, 2) NOT NULL DEFAULT 0.00,
  `providers` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `refunded` tinyint NOT NULL DEFAULT 0,
  `status` tinyint NOT NULL DEFAULT 0,
  `round_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `orders_user_id_foreign`(`user_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of orders
-- ----------------------------

-- ----------------------------
-- Table structure for password_reset_tokens
-- ----------------------------
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens`  (
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of password_reset_tokens
-- ----------------------------

-- ----------------------------
-- Table structure for permissions
-- ----------------------------
DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `permissions_name_guard_name_unique`(`name` ASC, `guard_name` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 41 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of permissions
-- ----------------------------
INSERT INTO `permissions` VALUES (1, 'games-exclusive-edit', 'web', '2023-10-12 16:23:45', '2023-10-12 18:12:28');
INSERT INTO `permissions` VALUES (2, 'games-exclusive-view', 'web', '2023-10-12 16:23:56', '2023-10-12 18:11:25');
INSERT INTO `permissions` VALUES (3, 'games-exclusive-create', 'web', '2023-10-12 16:25:06', '2023-10-12 18:11:10');
INSERT INTO `permissions` VALUES (4, 'admin-view', 'web', '2023-10-12 17:56:35', '2023-10-12 17:56:35');
INSERT INTO `permissions` VALUES (5, 'admin-create', 'web', '2023-10-12 18:56:02', '2023-10-12 18:56:02');
INSERT INTO `permissions` VALUES (6, 'admin-edit', 'web', '2023-10-12 18:56:27', '2023-10-12 18:56:27');
INSERT INTO `permissions` VALUES (7, 'admin-delete', 'web', '2023-10-12 18:56:55', '2023-10-12 18:56:55');
INSERT INTO `permissions` VALUES (8, 'category-view', 'web', '2023-10-12 19:01:31', '2023-10-12 19:01:31');
INSERT INTO `permissions` VALUES (9, 'category-create', 'web', '2023-10-12 19:01:46', '2023-10-12 19:01:46');
INSERT INTO `permissions` VALUES (10, 'category-edit', 'web', '2023-10-12 19:01:59', '2023-10-12 19:01:59');
INSERT INTO `permissions` VALUES (11, 'category-delete', 'web', '2023-10-12 19:02:09', '2023-10-12 19:02:09');
INSERT INTO `permissions` VALUES (12, 'game-view', 'web', '2023-10-12 19:02:27', '2023-10-12 19:02:27');
INSERT INTO `permissions` VALUES (13, 'game-create', 'web', '2023-10-12 19:02:36', '2023-10-12 19:02:36');
INSERT INTO `permissions` VALUES (14, 'game-edit', 'web', '2023-10-12 19:02:44', '2023-10-12 19:02:44');
INSERT INTO `permissions` VALUES (15, 'game-delete', 'web', '2023-10-12 19:02:54', '2023-10-12 19:02:54');
INSERT INTO `permissions` VALUES (16, 'wallet-view', 'web', '2023-10-12 19:05:49', '2023-10-12 19:05:49');
INSERT INTO `permissions` VALUES (17, 'wallet-create', 'web', '2023-10-12 19:06:01', '2023-10-12 19:06:01');
INSERT INTO `permissions` VALUES (18, 'wallet-edit', 'web', '2023-10-12 19:06:11', '2023-10-12 19:06:11');
INSERT INTO `permissions` VALUES (19, 'wallet-delete', 'web', '2023-10-12 19:06:18', '2023-10-12 19:06:18');
INSERT INTO `permissions` VALUES (20, 'deposit-view', 'web', '2023-10-12 19:06:44', '2023-10-12 19:06:44');
INSERT INTO `permissions` VALUES (21, 'deposit-create', 'web', '2023-10-12 19:06:56', '2023-10-12 19:06:56');
INSERT INTO `permissions` VALUES (22, 'deposit-edit', 'web', '2023-10-12 19:07:05', '2023-10-12 19:07:05');
INSERT INTO `permissions` VALUES (23, 'deposit-update', 'web', '2023-10-12 19:08:00', '2023-10-12 19:08:00');
INSERT INTO `permissions` VALUES (24, 'deposit-delete', 'web', '2023-10-12 19:08:11', '2023-10-12 19:08:11');
INSERT INTO `permissions` VALUES (25, 'withdrawal-view', 'web', '2023-10-12 19:09:31', '2023-10-12 19:09:31');
INSERT INTO `permissions` VALUES (26, 'withdrawal-create', 'web', '2023-10-12 19:09:40', '2023-10-12 19:09:40');
INSERT INTO `permissions` VALUES (27, 'withdrawal-edit', 'web', '2023-10-12 19:09:51', '2023-10-12 19:09:51');
INSERT INTO `permissions` VALUES (28, 'withdrawal-update', 'web', '2023-10-12 19:10:00', '2023-10-12 19:10:00');
INSERT INTO `permissions` VALUES (29, 'withdrawal-delete', 'web', '2023-10-12 19:10:09', '2023-10-12 19:10:09');
INSERT INTO `permissions` VALUES (30, 'order-view', 'web', '2023-10-12 19:12:36', '2023-10-12 19:12:36');
INSERT INTO `permissions` VALUES (31, 'order-create', 'web', '2023-10-12 19:12:47', '2023-10-12 19:12:47');
INSERT INTO `permissions` VALUES (32, 'order-edit', 'web', '2023-10-12 19:12:56', '2023-10-12 19:12:56');
INSERT INTO `permissions` VALUES (33, 'order-update', 'web', '2023-10-12 19:13:06', '2023-10-12 19:13:06');
INSERT INTO `permissions` VALUES (34, 'order-delete', 'web', '2023-10-12 19:13:19', '2023-10-12 19:13:19');
INSERT INTO `permissions` VALUES (35, 'admin-menu-view', 'web', '2023-10-12 20:26:06', '2023-10-12 20:26:06');
INSERT INTO `permissions` VALUES (36, 'setting-view', 'web', '2023-10-12 21:25:46', '2023-10-12 21:25:46');
INSERT INTO `permissions` VALUES (37, 'setting-create', 'web', '2023-10-12 21:25:57', '2023-10-12 21:25:57');
INSERT INTO `permissions` VALUES (38, 'setting-edit', 'web', '2023-10-12 21:26:06', '2023-10-12 21:26:06');
INSERT INTO `permissions` VALUES (39, 'setting-update', 'web', '2023-10-12 21:26:19', '2023-10-12 21:26:19');
INSERT INTO `permissions` VALUES (40, 'setting-delete', 'web', '2023-10-12 21:26:33', '2023-10-12 21:26:33');

-- ----------------------------
-- Table structure for personal_access_tokens
-- ----------------------------
DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE `personal_access_tokens`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `personal_access_tokens_token_unique`(`token`) USING BTREE,
  INDEX `personal_access_tokens_tokenable_type_tokenable_id_index`(`tokenable_type`, `tokenable_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of personal_access_tokens
-- ----------------------------

-- ----------------------------
-- Table structure for providers
-- ----------------------------
DROP TABLE IF EXISTS `providers`;
CREATE TABLE `providers`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `cover` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT 1,
  `rtp` bigint NULL DEFAULT 90,
  `views` bigint NULL DEFAULT 1,
  `distribution` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of providers
-- ----------------------------
INSERT INTO `providers` VALUES (1, NULL, 'PGSOFT', 'PGSOFT', 1, 80, 1, 'venixcg', '2025-01-15 13:26:58', '2025-01-15 13:26:58');
INSERT INTO `providers` VALUES (2, NULL, 'DARWIN', 'Darwin', 1, 80, 1, 'venixcg', '2025-01-15 13:26:58', '2025-01-15 13:26:58');
INSERT INTO `providers` VALUES (3, NULL, 'NETENT', 'Netent', 1, 80, 1, 'venixcg', '2025-01-15 13:26:58', '2025-01-15 13:26:58');
INSERT INTO `providers` VALUES (4, NULL, 'PRAGMATIC1', 'Pracmatic One', 1, 80, 1, 'venixcg', '2025-01-15 13:26:58', '2025-01-15 13:26:58');

-- ----------------------------
-- Table structure for reports
-- ----------------------------
DROP TABLE IF EXISTS `reports`;
CREATE TABLE `reports`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED NOT NULL,
  `description` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `page_url` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `page_action` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 24 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of reports
-- ----------------------------
INSERT INTO `reports` VALUES (1, 1, 'O Admin Admin atualizou o saldo do usuário do Id: 41 no valor de R$ 100.00', 'http://viperprogithub.test/livewire/update', 'Atualizou Saldo', '2024-08-05 17:29:57', '2024-08-05 17:29:57');
INSERT INTO `reports` VALUES (2, 1, 'O Admin Admin atualizou um usuário', 'http://viperprogithub.test/admin/logout', 'Atualizou', '2024-08-05 23:14:31', '2024-08-05 23:14:31');
INSERT INTO `reports` VALUES (3, 52, 'Novo usuário cadastrado do id: 52', 'http://viperprogithub.test/api/auth/register', 'Cadastrado', '2024-08-05 23:43:20', '2024-08-05 23:43:20');
INSERT INTO `reports` VALUES (4, 53, 'Novo usuário cadastrado do id: 53', 'http://viperprogithub.test/api/auth/register', 'Cadastrado', '2024-08-05 23:54:40', '2024-08-05 23:54:40');
INSERT INTO `reports` VALUES (5, 54, 'Novo usuário cadastrado do id: 50', 'http://viperprogithub.test/api/auth/register', 'Cadastrado', '2024-08-06 00:06:14', '2024-08-06 00:06:14');
INSERT INTO `reports` VALUES (6, 1, 'Novo usuário cadastrado do id: 1', 'http://viperprogithub.test/api/auth/login', 'Cadastrado', '2024-08-13 16:02:21', '2024-08-13 16:02:21');
INSERT INTO `reports` VALUES (7, 1, 'Novo usuário cadastrado do id: 1', 'http://viperprogithub.test/api/auth/login', 'Cadastrado', '2025-01-15 13:55:08', '2025-01-15 13:55:08');
INSERT INTO `reports` VALUES (8, 1, 'O Admin Admin apagou um usuário', 'http://viperprogithub.test/livewire/update', 'Apagou', '2025-01-15 13:55:24', '2025-01-15 13:55:24');
INSERT INTO `reports` VALUES (9, 1, 'O Admin Admin apagou um usuário', 'http://viperprogithub.test/livewire/update', 'Apagou', '2025-01-15 13:56:10', '2025-01-15 13:56:10');
INSERT INTO `reports` VALUES (10, 1, 'O Admin Admin apagou um usuário', 'http://viperprogithub.test/livewire/update', 'Apagou', '2025-01-15 13:56:11', '2025-01-15 13:56:11');
INSERT INTO `reports` VALUES (11, 1, 'O Admin Admin apagou um usuário', 'http://viperprogithub.test/livewire/update', 'Apagou', '2025-01-15 13:56:11', '2025-01-15 13:56:11');
INSERT INTO `reports` VALUES (12, 1, 'O Admin Admin apagou um usuário', 'http://viperprogithub.test/livewire/update', 'Apagou', '2025-01-15 13:56:11', '2025-01-15 13:56:11');
INSERT INTO `reports` VALUES (13, 1, 'O Admin Admin apagou um usuário', 'http://viperprogithub.test/livewire/update', 'Apagou', '2025-01-15 13:56:11', '2025-01-15 13:56:11');
INSERT INTO `reports` VALUES (14, 1, 'O Admin Admin apagou um usuário', 'http://viperprogithub.test/livewire/update', 'Apagou', '2025-01-15 13:56:11', '2025-01-15 13:56:11');
INSERT INTO `reports` VALUES (15, 1, 'O Admin Admin apagou um usuário', 'http://viperprogithub.test/livewire/update', 'Apagou', '2025-01-15 13:56:11', '2025-01-15 13:56:11');
INSERT INTO `reports` VALUES (16, 1, 'O Admin Admin apagou um usuário', 'http://viperprogithub.test/livewire/update', 'Apagou', '2025-01-15 13:56:11', '2025-01-15 13:56:11');
INSERT INTO `reports` VALUES (17, 1, 'O Admin Admin apagou um usuário', 'http://viperprogithub.test/livewire/update', 'Apagou', '2025-01-15 13:56:11', '2025-01-15 13:56:11');
INSERT INTO `reports` VALUES (18, 1, 'O Admin Admin apagou um usuário', 'http://viperprogithub.test/livewire/update', 'Apagou', '2025-01-15 13:57:16', '2025-01-15 13:57:16');
INSERT INTO `reports` VALUES (19, 1, 'O Admin Admin apagou um usuário', 'http://viperprogithub.test/livewire/update', 'Apagou', '2025-01-15 13:57:16', '2025-01-15 13:57:16');
INSERT INTO `reports` VALUES (20, 1, 'O Admin Admin apagou um usuário', 'http://viperprogithub.test/livewire/update', 'Apagou', '2025-01-15 13:57:16', '2025-01-15 13:57:16');
INSERT INTO `reports` VALUES (21, 1, 'O Admin Admin apagou um usuário', 'http://viperprogithub.test/livewire/update', 'Apagou', '2025-01-15 13:57:16', '2025-01-15 13:57:16');
INSERT INTO `reports` VALUES (22, 1, 'O Admin Admin apagou um usuário', 'http://viperprogithub.test/livewire/update', 'Apagou', '2025-01-15 13:57:16', '2025-01-15 13:57:16');
INSERT INTO `reports` VALUES (23, 1, 'Novo usuário cadastrado do id: 1', 'http://viperprogithub.test/api/auth/login', 'Cadastrado', '2025-01-15 15:10:01', '2025-01-15 15:10:01');

-- ----------------------------
-- Table structure for role_has_permissions
-- ----------------------------
DROP TABLE IF EXISTS `role_has_permissions`;
CREATE TABLE `role_has_permissions`  (
  `permission_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL,
  PRIMARY KEY (`permission_id`, `role_id`) USING BTREE,
  INDEX `role_has_permissions_role_id_foreign`(`role_id` ASC) USING BTREE,
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of role_has_permissions
-- ----------------------------

-- ----------------------------
-- Table structure for roles
-- ----------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `roles_name_guard_name_unique`(`name` ASC, `guard_name` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of roles
-- ----------------------------
INSERT INTO `roles` VALUES (1, 'admin', 'web', '2023-10-12 16:20:41', '2023-10-12 16:20:41');
INSERT INTO `roles` VALUES (2, 'afiliado', 'web', '2023-10-12 16:21:08', '2023-10-12 16:21:08');

-- ----------------------------
-- Table structure for setting_mails
-- ----------------------------
DROP TABLE IF EXISTS `setting_mails`;
CREATE TABLE `setting_mails`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `software_smtp_type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `software_smtp_mail_host` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `software_smtp_mail_port` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `software_smtp_mail_username` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `software_smtp_mail_password` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `software_smtp_mail_encryption` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `software_smtp_mail_from_address` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `software_smtp_mail_from_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of setting_mails
-- ----------------------------

-- ----------------------------
-- Table structure for settings
-- ----------------------------
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `software_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `software_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `software_favicon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `software_logo_white` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `software_logo_black` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `software_background` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `currency_code` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'BRL',
  `decimal_format` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'dot',
  `currency_position` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'left',
  `revshare_percentage` bigint NULL DEFAULT 20,
  `ngr_percent` bigint NULL DEFAULT 20,
  `soccer_percentage` bigint NULL DEFAULT 30,
  `prefix` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'R$',
  `storage` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'local',
  `initial_bonus` bigint NULL DEFAULT 0,
  `min_deposit` decimal(10, 2) NULL DEFAULT 20.00,
  `max_deposit` decimal(10, 2) NULL DEFAULT 0.00,
  `min_withdrawal` decimal(10, 2) NULL DEFAULT 20.00,
  `max_withdrawal` decimal(10, 2) NULL DEFAULT 0.00,
  `rollover` bigint NULL DEFAULT 10,
  `rollover_deposit` bigint NULL DEFAULT 1,
  `suitpay_is_enable` tinyint NULL DEFAULT 1,
  `stripe_is_enable` tinyint NULL DEFAULT 1,
  `bspay_is_enable` tinyint NULL DEFAULT 0,
  `sharkpay_is_enable` tinyint NULL DEFAULT 1,
  `turn_on_football` tinyint NULL DEFAULT 1,
  `revshare_reverse` tinyint(1) NULL DEFAULT 1,
  `bonus_vip` bigint NULL DEFAULT 100,
  `activate_vip_bonus` tinyint(1) NULL DEFAULT 1,
  `updated_at` timestamp NULL DEFAULT NULL,
  `maintenance_mode` tinyint NULL DEFAULT 0,
  `withdrawal_limit` bigint NULL DEFAULT NULL,
  `withdrawal_period` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `disable_spin` tinyint(1) NOT NULL DEFAULT 0,
  `perc_sub_lv1` bigint NOT NULL DEFAULT 4,
  `perc_sub_lv2` bigint NOT NULL DEFAULT 2,
  `perc_sub_lv3` bigint NOT NULL DEFAULT 3,
  `disable_rollover` tinyint NULL DEFAULT 0,
  `rollover_protection` bigint NOT NULL DEFAULT 1,
  `cpa_baseline` decimal(10, 2) NULL DEFAULT NULL,
  `cpa_value` decimal(10, 2) NULL DEFAULT NULL,
  `mercadopago_is_enable` tinyint NULL DEFAULT 0,
  `digitopay_is_enable` tinyint NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of settings
-- ----------------------------
INSERT INTO `settings` VALUES (1, 'Viper Pro ', 'Maior plataforma de Cassino Online Viper Pro', 'uploads/4W8fqH20qwVbO2sMOBwQFZ57TXWzjdpKFOLKWNev.png', 'uploads/dwNgrgaXOXbsnbqsB94geNl0d0bem1QowGDUzsIT.png', 'uploads/K25OGQSFZefZHWJd6zybXsEAPe1YOoQ3TkEw1LAK.png', '[]', 'BRL', 'dot', 'left', 20, 0, 30, 'R$', 'local', 100, 20.00, 50000.00, 20.00, 50000.00, 10, 2, 1, 1, 1, 1, 0, 1, 100, 1, '2025-01-15 15:06:25', 0, 500, 'weekly', 1, 4, 2, 3, 0, 5, 50.00, 50.00, 0, 0);

-- ----------------------------
-- Table structure for sub_affiliates
-- ----------------------------
DROP TABLE IF EXISTS `sub_affiliates`;
CREATE TABLE `sub_affiliates`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `affiliate_id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `status` tinyint(1) NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `sub_affiliates_affiliate_id_index`(`affiliate_id`) USING BTREE,
  INDEX `sub_affiliates_user_id_index`(`user_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = FIXED;

-- ----------------------------
-- Records of sub_affiliates
-- ----------------------------

-- ----------------------------
-- Table structure for suit_pay_payments
-- ----------------------------
DROP TABLE IF EXISTS `suit_pay_payments`;
CREATE TABLE `suit_pay_payments`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `payment_id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `withdrawal_id` bigint UNSIGNED NOT NULL,
  `pix_key` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `pix_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `amount` decimal(10, 2) NOT NULL DEFAULT 0.00,
  `observation` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `status` tinyint NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `suit_pay_payments_user_id_foreign`(`user_id`) USING BTREE,
  INDEX `suit_pay_payments_withdrawal_id_foreign`(`withdrawal_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of suit_pay_payments
-- ----------------------------

-- ----------------------------
-- Table structure for system_wallets
-- ----------------------------
DROP TABLE IF EXISTS `system_wallets`;
CREATE TABLE `system_wallets`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `label` char(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `balance` decimal(27, 12) NOT NULL DEFAULT 0.000000000000,
  `balance_min` decimal(27, 12) NOT NULL DEFAULT 10000.100000000000,
  `pay_upto_percentage` decimal(4, 2) NOT NULL DEFAULT 45.00,
  `mode` enum('balance_min','percentage') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'percentage',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = FIXED;

-- ----------------------------
-- Records of system_wallets
-- ----------------------------
INSERT INTO `system_wallets` VALUES (1, 'system', 261.800000000000, 10000.100000000000, 45.00, 'percentage', '2023-10-11 16:11:15', '2023-10-16 18:42:00');

-- ----------------------------
-- Table structure for transactions
-- ----------------------------
DROP TABLE IF EXISTS `transactions`;
CREATE TABLE `transactions`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `payment_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `payment_method` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `price` decimal(20, 2) NOT NULL DEFAULT 0.00,
  `currency` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'usd',
  `status` tinyint NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `reference` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `accept_bonus` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `transactions_user_id_index`(`user_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of transactions
-- ----------------------------

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `oauth_id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `oauth_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `avatar` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `last_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `cpf` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `phone` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `logged_in` tinyint NOT NULL DEFAULT 0,
  `banned` tinyint NOT NULL DEFAULT 0,
  `inviter` int NULL DEFAULT NULL,
  `inviter_code` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `affiliate_revenue_share` bigint NOT NULL DEFAULT 2,
  `affiliate_revenue_share_fake` bigint NULL DEFAULT NULL,
  `affiliate_cpa` decimal(20, 2) NOT NULL DEFAULT 10.00,
  `affiliate_baseline` decimal(20, 2) NOT NULL DEFAULT 40.00,
  `is_demo_agent` tinyint NOT NULL DEFAULT 0,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `language` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pt_BR',
  `role_id` int NULL DEFAULT 3,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `users_email_unique`(`email`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 55 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO `users` VALUES (1, 'Admin', 'admin@demo.com', NULL, '$2y$10$sq4gZjSHoI4Jk4yEthM5Cua/yVoFac6nTpRsTy7v9ayXAvKLHHrsq', 'YQbRDY7ZqR60Iajs50pv5o6mNNLjyUgqOgIyEWuusltzvrYaGNlkE5nkdzn3', '2023-11-07 22:15:13', '2024-03-31 15:51:21', NULL, NULL, 'uploads/8lx3OeL0c6GX18GMIhbgf2Kj4JVew0NRmnTUYYSb.png', NULL, NULL, '(31) 98690-4249', 0, 0, NULL, 'IL9O93HOCY', 20, NULL, 10.00, 40.00, 0, 'active', 'pt_BR', 1);

-- ----------------------------
-- Table structure for vip_users
-- ----------------------------
DROP TABLE IF EXISTS `vip_users`;
CREATE TABLE `vip_users`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED NOT NULL,
  `vip_id` int UNSIGNED NOT NULL,
  `level` bigint NOT NULL,
  `points` bigint NOT NULL,
  `status` tinyint NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `vip_users_user_id_index`(`user_id`) USING BTREE,
  INDEX `vip_users_vip_id_index`(`vip_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = FIXED;

-- ----------------------------
-- Records of vip_users
-- ----------------------------

-- ----------------------------
-- Table structure for vips
-- ----------------------------
DROP TABLE IF EXISTS `vips`;
CREATE TABLE `vips`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `bet_symbol` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `bet_level` bigint NOT NULL DEFAULT 1,
  `bet_required` bigint NULL DEFAULT NULL,
  `bet_period` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `bet_bonus` bigint NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of vips
-- ----------------------------
INSERT INTO `vips` VALUES (1, '01J59Z3548TT4QJR4MYZMFPB06.png', 1, 500, NULL, 10, '2024-08-14 23:50:26', '2024-08-14 23:50:26');
INSERT INTO `vips` VALUES (2, '01J59Z3XPBP9SSF80MG59RS8HA.png', 2, 1000, NULL, 30, '2024-08-14 23:50:51', '2024-08-14 23:50:51');

-- ----------------------------
-- Table structure for wallet_changes
-- ----------------------------
DROP TABLE IF EXISTS `wallet_changes`;
CREATE TABLE `wallet_changes`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `reason` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `change` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `value_bonus` decimal(20, 2) NOT NULL DEFAULT 0.00,
  `value_total` decimal(20, 2) NOT NULL DEFAULT 0.00,
  `value_roi` decimal(20, 2) NOT NULL DEFAULT 0.00,
  `value_entry` decimal(20, 2) NOT NULL DEFAULT 0.00,
  `refer_rewards` decimal(20, 2) NOT NULL DEFAULT 0.00,
  `game` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `wallet_changes_user_id_foreign`(`user_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of wallet_changes
-- ----------------------------

-- ----------------------------
-- Table structure for wallets
-- ----------------------------
DROP TABLE IF EXISTS `wallets`;
CREATE TABLE `wallets`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED NOT NULL,
  `currency` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `symbol` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `balance` decimal(20, 2) NOT NULL DEFAULT 0.00,
  `balance_bonus_rollover` decimal(10, 2) NULL DEFAULT 0.00,
  `balance_deposit_rollover` decimal(10, 2) NULL DEFAULT 0.00,
  `balance_withdrawal` decimal(10, 2) NULL DEFAULT 0.00,
  `balance_bonus` decimal(20, 2) NOT NULL DEFAULT 0.00,
  `balance_cryptocurrency` decimal(20, 8) NOT NULL DEFAULT 0.00000000,
  `balance_demo` decimal(20, 8) NULL DEFAULT 1000.00000000,
  `refer_rewards` decimal(20, 2) NOT NULL DEFAULT 0.00,
  `hide_balance` tinyint(1) NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `total_bet` decimal(20, 2) NOT NULL DEFAULT 0.00,
  `total_won` bigint NOT NULL DEFAULT 0,
  `total_lose` bigint NOT NULL DEFAULT 0,
  `last_won` bigint NOT NULL DEFAULT 0,
  `last_lose` bigint NOT NULL DEFAULT 0,
  `vip_level` bigint NULL DEFAULT 0,
  `vip_points` bigint NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `wallets_user_id_index`(`user_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 51 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of wallets
-- ----------------------------
INSERT INTO `wallets` VALUES (1, 1, 'BRL', 'R$', 1000.00, 0.00, 0.00, 1000.00, 0.00, 0.00000000, 0.00000000, 0.00, 0, 1, '2023-11-07 22:15:13', '2025-01-15 13:54:49', 0.00, 0, 0, 0, 0, 0, 0);

-- ----------------------------
-- Table structure for websockets_statistics_entries
-- ----------------------------
DROP TABLE IF EXISTS `websockets_statistics_entries`;
CREATE TABLE `websockets_statistics_entries`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `app_id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `peak_connection_count` int NOT NULL,
  `websocket_message_count` int NOT NULL,
  `api_message_count` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of websockets_statistics_entries
-- ----------------------------

-- ----------------------------
-- Table structure for withdrawals
-- ----------------------------
DROP TABLE IF EXISTS `withdrawals`;
CREATE TABLE `withdrawals`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `payment_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `amount` decimal(20, 2) NOT NULL DEFAULT 0.00,
  `type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `proof` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT 0,
  `pix_key` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `pix_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `bank_info` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `currency` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `symbol` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `withdrawals_user_id_foreign`(`user_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of withdrawals
-- ----------------------------

SET FOREIGN_KEY_CHECKS = 1;
