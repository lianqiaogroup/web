/*后台对接FMP项目组的Facebook广告接口*/
CREATE TABLE IF NOT EXISTS `fmp_ad` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `oa_uid` int(10) unsigned DEFAULT '0' COMMENT 'OA用户ID',
  `create_at` datetime NOT NULL DEFAULT '2018-01-01 00:00:01' COMMENT '创建日期',
  `campaign_name` varchar(255) COLLATE utf8_bin DEFAULT '' COMMENT '广告系列-名称',
  `adset_name` varchar(255) COLLATE utf8_bin DEFAULT '' COMMENT '广告分组-名称',
  `customer_price` decimal(14,2) unsigned DEFAULT '0.00' COMMENT '预算-客单价',
  `targeting_age_min` tinyint(2) unsigned DEFAULT '13' COMMENT '受众-年龄-最小',
  `targeting_age_max` tinyint(2) unsigned DEFAULT '65' COMMENT '受众-年龄-最大',
  `targeting_genders` tinyint(3) unsigned DEFAULT '0' COMMENT '受众-性别{0不限,1男,2女}',
  `targeting_interests` text COLLATE utf8_bin COMMENT '受众-兴趣ID(逗号分隔)',
  `creative_name` varchar(255) CHARACTER SET utf8 DEFAULT '' COMMENT '创意-名称',
  `creative_subtitle` text COLLATE utf8_bin COMMENT '创意-副标题',
  `creative_link_url` varchar(255) COLLATE utf8_bin DEFAULT '' COMMENT '创意-链接',
  `file` varchar(255) COLLATE utf8_bin DEFAULT '' COMMENT '创意-图片或视频相对地址',
  `ad_name` varchar(255) COLLATE utf8_bin DEFAULT '' COMMENT '广告-名称',
  `ad_describe` text COLLATE utf8_bin COMMENT '广告-描述',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='FMP广告';