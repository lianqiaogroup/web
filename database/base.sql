# ************************************************************
# Sequel Pro SQL dump
# Version 4541
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: 192.168.105.252 (MySQL 5.5.52-MariaDB)
# Database: stoshop
# Generation Time: 2017-12-07 13:46:40 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table admin_logs
# ------------------------------------------------------------

DROP TABLE IF EXISTS `admin_logs`;

CREATE TABLE `admin_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `loginid` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `name_cn` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `act_sql` text CHARACTER SET utf8,
  `act_type` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `act_table` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `act_loginid` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `act_desc` text CHARACTER SET utf8,
  `act_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `product_id` (`loginid`),
  KEY `logid` (`act_loginid`),
  KEY `time` (`act_time`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table article
# ------------------------------------------------------------

DROP TABLE IF EXISTS `article`;

CREATE TABLE `article` (
  `article_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `domain` char(50) NOT NULL,
  `title` varchar(50) NOT NULL DEFAULT '',
  `content` text NOT NULL,
  `add_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `sort` tinyint(2) NOT NULL DEFAULT '0',
  `aid` int(8) NOT NULL DEFAULT '0',
  `theme` char(20) DEFAULT '',
  `is_del` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`article_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table category
# ------------------------------------------------------------

DROP TABLE IF EXISTS `category`;

CREATE TABLE `category` (
  `id` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `domain` varchar(60) NOT NULL,
  `parent_id` int(8) DEFAULT '0',
  `title` varchar(60) NOT NULL DEFAULT '',
  `title_zh` varchar(60) NOT NULL DEFAULT '',
  `sort` tinyint(1) NOT NULL DEFAULT '0' COMMENT '排序',
  `is_del` tinyint(4) NOT NULL DEFAULT '0',
  `add_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table combo
# ------------------------------------------------------------

DROP TABLE IF EXISTS `combo`;

CREATE TABLE `combo` (
  `combo_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `price` int(10) NOT NULL DEFAULT '0',
  `product_id` int(8) NOT NULL,
  `is_del` tinyint(1) NOT NULL DEFAULT '0',
  `thumb` varchar(255) DEFAULT '' COMMENT '缩略图',
  `is_single_combo` tinyint(1) DEFAULT '0' COMMENT '是否套餐限购',
  `is_lock_total` tinyint(1) DEFAULT '0' COMMENT '是否固定套餐价格',
  PRIMARY KEY (`combo_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table combo_goods
# ------------------------------------------------------------

DROP TABLE IF EXISTS `combo_goods`;

CREATE TABLE `combo_goods` (
  `combo_goods_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `num` int(5) NOT NULL,
  `erp_id` int(8) NOT NULL DEFAULT '0',
  `combo_id` int(8) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `sale_title` varchar(255) NOT NULL COMMENT '外文名称',
  `product_id` int(8) NOT NULL DEFAULT '0',
  `is_del` tinyint(1) DEFAULT '0',
  `promotion_price` int(10) DEFAULT NULL,
  `price` int(10) DEFAULT NULL,
  PRIMARY KEY (`combo_goods_id`),
  KEY `combo_id` (`combo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table company
# ------------------------------------------------------------

DROP TABLE IF EXISTS `company`;

CREATE TABLE `company` (
  `company_id` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '公司名称',
  `domain_erp` varchar(255) NOT NULL DEFAULT '' COMMENT 'erp拉取产品接口',
  `product_erp_api` int(8) DEFAULT NULL,
  `domain_erp_api` varchar(255) DEFAULT NULL,
  `order_erp_api` varchar(8) DEFAULT NULL,
  `seo_erp_api` varchar(255) DEFAULT NULL,
  `add_time` int(11) DEFAULT NULL,
  `last_modify` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table country
# ------------------------------------------------------------

DROP TABLE IF EXISTS `country`;

CREATE TABLE `country` (
  `id_country` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(20) NOT NULL COMMENT '国家名字',
  `iso_code2` varchar(60) NOT NULL COMMENT '2位代码',
  `iso_code3` varchar(60) NOT NULL COMMENT '3位代码',
  `currency_code` varchar(50) DEFAULT NULL COMMENT '货币编码',
  `ncode` varchar(255) DEFAULT NULL COMMENT '区号',
  PRIMARY KEY (`id_country`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='国家表';



# Dump of table currency
# ------------------------------------------------------------

DROP TABLE IF EXISTS `currency`;

CREATE TABLE `currency` (
  `currency_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `currency_title` char(50) NOT NULL DEFAULT '' COMMENT '货币名称',
  `currency_code` char(20) NOT NULL DEFAULT '' COMMENT '货币简称',
  `symbol_left` char(20) DEFAULT '',
  `symbol_right` char(20) DEFAULT '',
  `currency_format` varchar(16) DEFAULT NULL,
  `exchange_rate` float DEFAULT NULL,
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`currency_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table domain_payment
# ------------------------------------------------------------

DROP TABLE IF EXISTS `domain_payment`;

CREATE TABLE `domain_payment` (
  `payment_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `domain` char(20) NOT NULL DEFAULT '',
  `code` char(10) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `aid` int(11) NOT NULL,
  `data` text,
  PRIMARY KEY (`payment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table erp_api
# ------------------------------------------------------------

DROP TABLE IF EXISTS `erp_api`;

CREATE TABLE `erp_api` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `domain` varchar(255) DEFAULT NULL,
  `tag` varchar(255) DEFAULT NULL,
  `classname` varchar(255) DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `add_time` int(11) DEFAULT NULL,
  `last_modify` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table facebook_user
# ------------------------------------------------------------

DROP TABLE IF EXISTS `facebook_user`;

CREATE TABLE `facebook_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `facebook_uid` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'facebook用户id',
  `nickname` varchar(64) NOT NULL DEFAULT '' COMMENT '昵称',
  `email` varchar(255) NOT NULL DEFAULT '' COMMENT '邮箱',
  `created_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_facebook_uid` (`facebook_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='facebook用户信息表';



# Dump of table index_focus
# ------------------------------------------------------------

DROP TABLE IF EXISTS `index_focus`;

CREATE TABLE `index_focus` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `domain` char(50) NOT NULL,
  `thumb` varchar(255) NOT NULL DEFAULT '',
  `desc` char(50) DEFAULT '',
  `product_id` int(8) NOT NULL DEFAULT '0',
  `sort` int(2) NOT NULL DEFAULT '0',
  `is_del` tinyint(1) NOT NULL DEFAULT '0',
  `aid` int(10) NOT NULL,
  `add_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table oa_department
# ------------------------------------------------------------

DROP TABLE IF EXISTS `oa_department`;

CREATE TABLE `oa_department` (
  `id_department` int(11) unsigned NOT NULL COMMENT '部门id',
  `id_parent` int(11) NOT NULL DEFAULT '0' COMMENT '上级部门',
  `old_id_department` int(10) NOT NULL DEFAULT '0' COMMENT '老erpid',
  `department` varchar(255) NOT NULL DEFAULT '' COMMENT '部门',
  `manager_id` int(10) DEFAULT '0' COMMENT '上级id',
  `code` char(20) DEFAULT '' COMMENT '编码',
  `create_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `update_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id_department`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table oa_users
# ------------------------------------------------------------

DROP TABLE IF EXISTS `oa_users`;

CREATE TABLE `oa_users` (
  `uid` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'uid',
  `username` varchar(255) DEFAULT NULL COMMENT '登录账户',
  `password` char(32) DEFAULT '' COMMENT '登录密码',
  `email` varchar(255) DEFAULT '' COMMENT '邮箱',
  `mobile` varchar(255) DEFAULT NULL COMMENT '手机号码',
  `id_department` int(8) NOT NULL COMMENT '部门id',
  `department` varchar(255) NOT NULL DEFAULT '' COMMENT '部门',
  `manager_id` int(8) DEFAULT '0' COMMENT '上级领导id',
  `name_cn` varchar(255) NOT NULL DEFAULT '' COMMENT '中文名',
  `create_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `update_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  `is_admin` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否为系统管理员',
  `is_root` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否为超管',
  `company_id` int(8) NOT NULL DEFAULT '0' COMMENT '公司id',
  `is_base` tinyint(1) DEFAULT '0' COMMENT '是否树形根目录',
  `is_leaf` tinyint(1) DEFAULT '0' COMMENT '是否叶子节点',
  `path` varchar(255) DEFAULT '' COMMENT '完整递归路径',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table order
# ------------------------------------------------------------

DROP TABLE IF EXISTS `order`;

CREATE TABLE `order` (
  `order_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `order_no` char(32) NOT NULL DEFAULT '',
  `erp_no` char(32) DEFAULT NULL,
  `order_status` char(20) NOT NULL DEFAULT 'NOT_PAID',
  `product_id` int(8) NOT NULL DEFAULT '0',
  `num` int(8) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL,
  `name` char(11) NOT NULL DEFAULT '',
  `last_name` char(11) DEFAULT NULL,
  `email` char(20) NOT NULL DEFAULT '',
  `mobile` char(20) NOT NULL DEFAULT '',
  `address` varchar(255) NOT NULL DEFAULT '',
  `order_msg` varchar(255) DEFAULT '',
  `payment_amount` int(10) NOT NULL DEFAULT '0',
  `erp_status` char(20) NOT NULL DEFAULT 'SUCCESS',
  `pay_type` char(50) DEFAULT '',
  `add_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ip` char(20) NOT NULL DEFAULT '0.0.0.0',
  `aid` int(8) NOT NULL DEFAULT '0',
  `post_erp_data` text,
  `post_erp_msg` varchar(255) DEFAULT NULL,
  `combo_id` int(8) NOT NULL DEFAULT '0',
  PRIMARY KEY (`order_id`),
  KEY `order_no` (`order_no`),
  KEY `idx_mobile` (`mobile`),
  KEY `add_time` (`add_time`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table order_expand
# ------------------------------------------------------------

DROP TABLE IF EXISTS `order_expand`;

CREATE TABLE `order_expand` (
  `order_id` int(11) unsigned NOT NULL,
  `memo` varchar(255) DEFAULT NULL COMMENT '订单备注 留言',
  `erp_code` varchar(255) DEFAULT NULL COMMENT 'erp接口返回的  状态码',
  `order_code` varchar(255) DEFAULT NULL COMMENT '前台订单的对应 状态码',
  `date_delivery` datetime DEFAULT NULL COMMENT '发货时间',
  `shipping_name` varchar(255) DEFAULT NULL COMMENT '物流商名称',
  `domain` varchar(255) NOT NULL DEFAULT '' COMMENT '域名',
  `postal` char(20) DEFAULT NULL COMMENT '邮编',
  `track_numer` varchar(255) DEFAULT NULL COMMENT '运单号',
  `facebook_uid` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'facebook用户id',
  `add_time` int(11) DEFAULT NULL,
  `last_modify` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table order_goods
# ------------------------------------------------------------

DROP TABLE IF EXISTS `order_goods`;

CREATE TABLE `order_goods` (
  `order_goods_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `num` int(8) NOT NULL DEFAULT '0',
  `price` int(10) NOT NULL DEFAULT '0',
  `total` int(10) NOT NULL DEFAULT '0',
  `order_id` int(8) NOT NULL DEFAULT '0',
  `erp_id` int(8) DEFAULT NULL,
  `product_id` int(8) NOT NULL DEFAULT '0',
  PRIMARY KEY (`order_goods_id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table order_goods_attr
# ------------------------------------------------------------

DROP TABLE IF EXISTS `order_goods_attr`;

CREATE TABLE `order_goods_attr` (
  `order_goods_attr_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `order_goods_id` int(8) NOT NULL,
  `product_attr_id` int(8) NOT NULL,
  PRIMARY KEY (`order_goods_attr_id`),
  KEY `order_goods_id` (`order_goods_id`,`order_goods_attr_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table order_unpost
# ------------------------------------------------------------

DROP TABLE IF EXISTS `order_unpost`;

CREATE TABLE `order_unpost` (
  `post_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL DEFAULT '0',
  `add_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table payment_notice
# ------------------------------------------------------------

DROP TABLE IF EXISTS `payment_notice`;

CREATE TABLE `payment_notice` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `serial_number` varchar(30) DEFAULT '' COMMENT '序列号',
  `erp_no` varchar(30) DEFAULT '' COMMENT '订单编号',
  `data` text COMMENT '收到的数据',
  `received_url` varchar(200) DEFAULT '' COMMENT '接收的URL',
  `syn_status` smallint(1) DEFAULT '0' COMMENT '同步状态',
  `paid_status` smallint(8) DEFAULT '200' COMMENT '支付状态 200为成功',
  `created_time` datetime DEFAULT NULL COMMENT '生成时间',
  `updated_time` datetime DEFAULT NULL COMMENT '最后一次更新时间',
  `payment_method` varchar(30) DEFAULT '' COMMENT '支付方式',
  `callback_time` int(11) DEFAULT NULL COMMENT 'ERP回调时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='支付通知';



# Dump of table product
# ------------------------------------------------------------

DROP TABLE IF EXISTS `product`;

CREATE TABLE `product` (
  `product_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` char(100) NOT NULL DEFAULT '',
  `seo_title` varchar(255) NOT NULL DEFAULT '',
  `seo_description` varchar(255) NOT NULL DEFAULT '',
  `content` longtext CHARACTER SET utf8mb4,
  `price` int(10) NOT NULL,
  `market_price` int(10) DEFAULT '0',
  `add_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `is_del` tinyint(1) NOT NULL DEFAULT '0',
  `tags` varchar(45) DEFAULT NULL,
  `domain` char(30) DEFAULT NULL,
  `currency` char(20) NOT NULL DEFAULT '',
  `currency_prefix` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 前置 0 后置',
  `currency_code` char(10) NOT NULL,
  `discount` int(8) NOT NULL DEFAULT '0',
  `sales` int(8) NOT NULL DEFAULT '0',
  `stock` int(8) NOT NULL DEFAULT '0',
  `thumb` varchar(255) DEFAULT '',
  `theme` char(50) NOT NULL DEFAULT '',
  `lang` char(20) NOT NULL DEFAULT '',
  `payment_online` tinyint(1) DEFAULT '0' COMMENT '1 线上 0 否',
  `payment_underline` tinyint(1) DEFAULT '0' COMMENT '1线下 0否',
  `payment_paypal` tinyint(1) DEFAULT '0' COMMENT 'paypal',
  `payment_blue` tinyint(1) DEFAULT NULL COMMENT '是否支持bluepay 0.no 1.yes',
  `erp_number` char(50) NOT NULL DEFAULT '',
  `ad_member_id` int(8) NOT NULL DEFAULT '0' COMMENT '广告手id',
  `ad_member` varchar(20) DEFAULT NULL,
  `ad_member_pinyin` varchar(20) DEFAULT NULL,
  `id_zone` int(8) NOT NULL DEFAULT '0',
  `id_department` int(8) NOT NULL DEFAULT '0',
  `la` text,
  `fb_px` char(200) DEFAULT NULL,
  `aid` int(8) NOT NULL DEFAULT '0',
  `logo` varchar(255) DEFAULT '',
  `service_contact_id` int(10) DEFAULT '0',
  `service_email` char(100) DEFAULT NULL,
  `identity_tag` varchar(30) DEFAULT '',
  `google_js` text CHARACTER SET utf8 COLLATE utf8_bin,
  `yahoo_js` text CHARACTER SET utf8 COLLATE utf8_bin,
  `yahoo_js_trigger` text CHARACTER SET utf8 COLLATE utf8_bin,
  `tips` varchar(255) DEFAULT '',
  `payment_asiabill` tinyint(4) DEFAULT '0' COMMENT 'asiabill',
  `sales_title` varchar(255) DEFAULT NULL,
  `google_analytics_js` text,
  `payment_ocean` tinyint(1) DEFAULT '0',
  `photo_txt` char(50) DEFAULT '' COMMENT '图片文字水印',
  `del_time` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `is_open_sms` tinyint(4) DEFAULT '0' COMMENT '是否开启验证码 0-否 1-是',
  `company_id` int(8) NOT NULL DEFAULT '1',
  `available_zone_ids` text,
  `new_erp` int(8) DEFAULT NULL,
  `last_utime` int(11) DEFAULT NULL COMMENT '最后修改时间',
  `oa_id_department` int(8) NOT NULL DEFAULT '0' COMMENT 'oa部门',
  PRIMARY KEY (`product_id`),
  KEY `theme` (`theme`),
  KEY `aid` (`aid`),
  KEY `ad_member` (`ad_member`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table product_act_logs
# ------------------------------------------------------------

DROP TABLE IF EXISTS `product_act_logs`;

CREATE TABLE `product_act_logs` (
  `act_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) DEFAULT NULL,
  `act_loginid` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `act_type` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `act_table` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `act_sql` text CHARACTER SET utf8,
  `act_desc` text CHARACTER SET utf8,
  `act_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`act_id`),
  KEY `product_id` (`product_id`),
  KEY `logid` (`act_loginid`),
  KEY `time` (`act_time`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table product_attr
# ------------------------------------------------------------

DROP TABLE IF EXISTS `product_attr`;

CREATE TABLE `product_attr` (
  `product_attr_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) NOT NULL DEFAULT '0',
  `attr_group_id` int(8) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `price` int(8) DEFAULT '0',
  `thumb` varchar(255) DEFAULT NULL,
  `sort` tinyint(1) NOT NULL DEFAULT '0',
  `price_prefix` tinyint(1) DEFAULT '1',
  `number` char(20) NOT NULL DEFAULT '',
  `attr_group_title` char(50) NOT NULL DEFAULT '',
  `is_del` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`product_attr_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table product_bilink
# ------------------------------------------------------------

DROP TABLE IF EXISTS `product_bilink`;

CREATE TABLE `product_bilink` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `ad_channel` varchar(100) DEFAULT '',
  `ad_media` varchar(100) DEFAULT '',
  `ad_group` varchar(100) DEFAULT '',
  `ad_series` varchar(100) DEFAULT '',
  `ad_name` varchar(100) DEFAULT '',
  `ad_bilink` varchar(255) DEFAULT '',
  `ad_id_department` varchar(10) DEFAULT '',
  `ad_loginid` varchar(50) DEFAULT NULL,
  `ad_loginname` varchar(50) DEFAULT NULL,
  `loginid` varchar(50) DEFAULT NULL,
  `times` int(11) NOT NULL DEFAULT '1',
  `add_time` int(11) DEFAULT '0',
  `last_modify` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table product_category
# ------------------------------------------------------------

DROP TABLE IF EXISTS `product_category`;

CREATE TABLE `product_category` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `product_id` int(10) DEFAULT NULL,
  `category_id` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table product_comment
# ------------------------------------------------------------

DROP TABLE IF EXISTS `product_comment`;

CREATE TABLE `product_comment` (
  `comment_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(20) DEFAULT '',
  `content` varchar(255) DEFAULT NULL,
  `is_anonymous` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否匿名',
  `is_del` int(1) DEFAULT '0',
  `star` tinyint(4) DEFAULT '0',
  `product_id` int(10) NOT NULL DEFAULT '0',
  `aid` int(8) NOT NULL DEFAULT '0',
  `add_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `company_id` int(8) NOT NULL DEFAULT '1' COMMENT '公司id',
  PRIMARY KEY (`comment_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table product_comment_thumb
# ------------------------------------------------------------

DROP TABLE IF EXISTS `product_comment_thumb`;

CREATE TABLE `product_comment_thumb` (
  `commont_thumb_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `thumb` varchar(255) NOT NULL DEFAULT '',
  `comment_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`commont_thumb_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table product_ext
# ------------------------------------------------------------

DROP TABLE IF EXISTS `product_ext`;

CREATE TABLE `product_ext` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL,
  `google_analytics_id` varchar(24) DEFAULT NULL,
  `google_marketing_id` varchar(24) NOT NULL,
  `google_marketing_js` text NOT NULL,
  `google_conversion_id` varchar(24) NOT NULL,
  `google_conversion_label` varchar(32) NOT NULL,
  `google_e_commerce_id` varchar(24) NOT NULL,
  `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table product_original_thumb
# ------------------------------------------------------------

DROP TABLE IF EXISTS `product_original_thumb`;

CREATE TABLE `product_original_thumb` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `product_id` int(11) NOT NULL COMMENT '产品ID',
  `type` smallint(6) NOT NULL COMMENT '1:logo 2:thumb 3:photo 4:attribute 5:combo 6:content',
  `fg_id` int(11) DEFAULT NULL COMMENT '属性套餐时才用到',
  `thumb` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '图片在本地相对地址',
  `add_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '添加的时间戳',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table product_sku
# ------------------------------------------------------------

DROP TABLE IF EXISTS `product_sku`;

CREATE TABLE `product_sku` (
  `sku_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) NOT NULL,
  `spec` longtext,
  `spec_value` longtext,
  `price` int(10) DEFAULT NULL,
  `mktprice` int(10) DEFAULT NULL,
  `stock` int(11) unsigned DEFAULT NULL,
  `img` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `qrcode_image_url` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `add_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_modify` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`sku_id`),
  KEY `idx_product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table product_thumb
# ------------------------------------------------------------

DROP TABLE IF EXISTS `product_thumb`;

CREATE TABLE `product_thumb` (
  `thumb_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(8) DEFAULT '0',
  `thumb` varchar(255) NOT NULL DEFAULT '',
  `add_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`thumb_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table region
# ------------------------------------------------------------

DROP TABLE IF EXISTS `region`;

CREATE TABLE `region` (
  `region_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `region_name` varchar(120) NOT NULL DEFAULT '',
  `region_type` tinyint(1) NOT NULL DEFAULT '2',
  `agency_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `region_code` char(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`region_id`),
  KEY `parent_id` (`parent_id`),
  KEY `region_type` (`region_type`),
  KEY `agency_id` (`agency_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table site
# ------------------------------------------------------------

DROP TABLE IF EXISTS `site`;

CREATE TABLE `site` (
  `domain` varchar(255) NOT NULL COMMENT '域名',
  `title` varchar(100) NOT NULL,
  `mail` varchar(100) DEFAULT '',
  `seo_title` varchar(255) NOT NULL,
  `seo_description` varchar(255) NOT NULL,
  `lang` varchar(50) NOT NULL COMMENT '语言',
  `google_js` text,
  `is_del` tinyint(1) NOT NULL DEFAULT '0',
  `theme` varchar(20) NOT NULL DEFAULT 'style1',
  `logo` varchar(255) DEFAULT '',
  PRIMARY KEY (`domain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table site_products
# ------------------------------------------------------------

DROP TABLE IF EXISTS `site_products`;

CREATE TABLE `site_products` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `domain` char(50) NOT NULL,
  `product_id` int(8) NOT NULL DEFAULT '0',
  `sort` int(2) NOT NULL DEFAULT '0',
  `is_del` tinyint(1) NOT NULL DEFAULT '0',
  `aid` int(10) NOT NULL,
  `thumb` varchar(120) NOT NULL,
  `add_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table site_templs
# ------------------------------------------------------------

DROP TABLE IF EXISTS `site_templs`;

CREATE TABLE `site_templs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `style_name` varchar(255) DEFAULT NULL,
  `lang` varchar(255) DEFAULT NULL,
  `add_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_modify` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;






# Dump of table t_sms_isp
# ------------------------------------------------------------

DROP TABLE IF EXISTS `t_sms_isp`;

CREATE TABLE `t_sms_isp` (
  `id` tinyint(4) NOT NULL AUTO_INCREMENT,
  `ispname` varchar(50) DEFAULT NULL COMMENT '信息服务提供商',
  `status` tinyint(4) DEFAULT '1' COMMENT '状态 1-正常 0-关闭',
  `classname` varchar(30) DEFAULT NULL COMMENT '类名',
  `add_time` int(11) DEFAULT NULL COMMENT '加入时间',
  `last_modify` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;



# Dump of table t_sms_order
# ------------------------------------------------------------

DROP TABLE IF EXISTS `t_sms_order`;

CREATE TABLE `t_sms_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) DEFAULT NULL,
  `sms_code` varchar(30) DEFAULT NULL,
  `in_code` varchar(30) DEFAULT NULL,
  `erp_data` text,
  `times` tinyint(4) DEFAULT '0' COMMENT '验证次数',
  `status` tinyint(4) DEFAULT NULL COMMENT '验证状态 0-失败 1-成功',
  `atime` int(11) DEFAULT NULL,
  `issync` tinyint(4) DEFAULT '0',
  `isp` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `sms_code` (`sms_code`),
  KEY `in_code` (`in_code`),
  KEY `times` (`times`),
  KEY `status` (`status`),
  KEY `issync` (`issync`),
  KEY `isp` (`isp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;






# Dump of table t_sms_record
# ------------------------------------------------------------

DROP TABLE IF EXISTS `t_sms_record`;

CREATE TABLE `t_sms_record` (
  `oid` int(11) NOT NULL AUTO_INCREMENT,
  `mobile` varchar(30) NOT NULL,
  `smscode` varchar(8) DEFAULT NULL,
  `content` varchar(200) NOT NULL,
  `atime` int(11) NOT NULL,
  `isp` tinyint(4) DEFAULT NULL COMMENT '1-paasoo 2-yun 3-yunpian 4-nexmo',
  `status` tinyint(4) DEFAULT '0',
  `errmsg` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`oid`),
  KEY `mobile` (`mobile`),
  KEY `mobile_code` (`mobile`,`smscode`),
  KEY `smscode` (`smscode`),
  KEY `isp` (`isp`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;



# Dump of table theme
# ------------------------------------------------------------

DROP TABLE IF EXISTS `theme`;

CREATE TABLE `theme` (
  `tid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
  `theme` varchar(255) NOT NULL DEFAULT '' COMMENT '前台模板路劲',
  `thumb` varchar(255) DEFAULT NULL,
  `author` varchar(255) NOT NULL DEFAULT '' COMMENT '开发作者',
  `referto_links` varchar(255) DEFAULT '' COMMENT '预览地址',
  `belong_id_department` int(8) NOT NULL DEFAULT '0' COMMENT '部门专属',
  `zone` varchar(255) NOT NULL DEFAULT '' COMMENT '支持地区',
  `lang` varchar(255) NOT NULL DEFAULT '' COMMENT '自持语言',
  `style` char(100) DEFAULT '',
  `desc` varchar(255) DEFAULT '',
  `create_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `is_del` tinyint(1) NOT NULL COMMENT '0',
  PRIMARY KEY (`tid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table theme_diy
# ------------------------------------------------------------

DROP TABLE IF EXISTS `theme_diy`;

CREATE TABLE `theme_diy` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(8) NOT NULL DEFAULT '0',
  `data` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table theme_modules
# ------------------------------------------------------------

DROP TABLE IF EXISTS `theme_modules`;

CREATE TABLE `theme_modules` (
  `mid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(20) NOT NULL,
  `title` char(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`mid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table themebuilder
# ------------------------------------------------------------

DROP TABLE IF EXISTS `themebuilder`;

CREATE TABLE `themebuilder` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `theme_id` int(11) NOT NULL,
  `domain` text NOT NULL,
  `module_id` int(11) NOT NULL,
  `module_type` varchar(11) CHARACTER SET latin1 NOT NULL,
  `module_label` varchar(11) NOT NULL,
  `module_sort` int(11) NOT NULL,
  `module_param` text NOT NULL,
  `module_config` text CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




# Dump of table watermark_style
# ------------------------------------------------------------

DROP TABLE IF EXISTS `watermark_style`;

CREATE TABLE `watermark_style` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `product_id` int(11) NOT NULL COMMENT '产品ID',
  `position` smallint(6) NOT NULL COMMENT '水印位置',
  `transparency` smallint(11) NOT NULL COMMENT '水印透明度',
  `angle` smallint(6) NOT NULL COMMENT '水印角度',
  `add_time` timestamp NULL DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table yn_region
# ------------------------------------------------------------

DROP TABLE IF EXISTS `yn_region`;

CREATE TABLE `yn_region` (
  `id_region` int(11) NOT NULL AUTO_INCREMENT,
  `id_zone` int(11) DEFAULT NULL,
  `id_parent` int(11) DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `tag` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `type` enum('city','district','ward') CHARACTER SET utf8mb4 DEFAULT NULL,
  `post_code` varchar(10) CHARACTER SET utf8mb4 DEFAULT NULL,
  `is_del` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_region`),
  KEY `index_name` (`name`(191)),
  KEY `post_code` (`post_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='地区表';



# Dump of table zone
# ------------------------------------------------------------

DROP TABLE IF EXISTS `zone`;

CREATE TABLE `zone` (
  `id_zone` int(10) NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) DEFAULT '0' COMMENT '多级时，父ID',
  `id_country` int(10) NOT NULL,
  `code` varchar(60) NOT NULL,
  `title` varchar(60) NOT NULL,
  `currency_id` int(8) NOT NULL DEFAULT '0' COMMENT '货币id',
  `lang` char(50) NOT NULL DEFAULT '' COMMENT '当地写法',
  `erp_id_zone` int(10) DEFAULT NULL,
  `erp_country_id` int(11) DEFAULT NULL,
  `currency` varchar(60) DEFAULT NULL,
  `erp_parent_id` int(10) DEFAULT NULL,
  `email` varchar(150) DEFAULT '' COMMENT '统一售后邮箱',
  PRIMARY KEY (`id_zone`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='地区表';




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

V4.7
##增加统一售后邮箱
ALTER TABLE zone ADD COLUMN `email` varchar(150) DEFAULT '' COMMENT '统一售后邮箱' AFTER `erp_parent_id`;
###修改product表字段长度
alter table product modify service_email char(100);
###删除oa_user两位多余数据
delete from oa_users where id_department =0 and name_cn in ('陆媛','汪佳慧');
###原部门 4部 一营 一组  新部门 4部 一营3组
update product set oa_id_department=76 where domain in ('www.sltjy.com','www.vzasw.com','www.sefqm.com')
