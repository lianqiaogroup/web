
ALTER TABLE `material` COMMENT='素材资源';

DROP TABLE IF EXISTS `resource_tags`;
CREATE TABLE IF NOT EXISTS `resource_tags` (
`tag_id`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '标签ID' ,
`tag_name`  varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '标签名称' ,
`oa_uid`  int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '添加人的用户ID' ,
`create_at`  datetime NULL DEFAULT NULL COMMENT '创建时间' ,
`update_at`  datetime NULL DEFAULT NULL COMMENT '修改时间' ,
PRIMARY KEY (`tag_id`),
INDEX `tag_name` (`tag_name`) USING BTREE
) COMMENT='素材可选的标签';


DROP TABLE IF EXISTS `resource_tags_assoc`;
CREATE TABLE IF NOT EXISTS `resource_tags_assoc` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tag_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '标签ID',
  `resource_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '资源文件ID',
  PRIMARY KEY (`id`),
  KEY `tag_id` (`tag_id`),
  KEY `resource_id` (`resource_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='素材与标签的关联';


DROP TABLE IF EXISTS `resource_type`;
CREATE TABLE IF NOT EXISTS `resource_type` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type_name` varchar(50) NOT NULL DEFAULT '' COMMENT '资源类型名称',
  `is_del` tinyint(1) unsigned DEFAULT '0' COMMENT '删除: {1是,0否}',
  PRIMARY KEY (`id`),
  UNIQUE KEY `type_name` (`type_name`) USING BTREE,
  KEY `id_del` (`is_del`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='素材可选的类型';

INSERT INTO `resource_type` VALUES ('1', '促销图', '0');
INSERT INTO `resource_type` VALUES ('2', '搭配图', '0');
INSERT INTO `resource_type` VALUES ('3', '工艺图', '0');
INSERT INTO `resource_type` VALUES ('4', '官方价格图', '0');
INSERT INTO `resource_type` VALUES ('5', '门店图', '0');
INSERT INTO `resource_type` VALUES ('6', '质保图', '0');

ALTER TABLE `material`
ADD COLUMN `product_category_id`  int(10) UNSIGNED NULL DEFAULT 0 COMMENT '产品分类ID' AFTER `downtime`,
ADD COLUMN `resource_type_id`  int(10) UNSIGNED NULL DEFAULT 0 COMMENT '资源类型ID' AFTER `product_category_id`,
ADD INDEX `product_category_id` (`product_category_id`) USING BTREE ,
ADD INDEX `resource_type_id` (`resource_type_id`) USING BTREE ;

update material set product_category_id = mtype, resource_type_id = mtag;