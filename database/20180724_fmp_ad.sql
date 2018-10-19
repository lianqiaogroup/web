delete from fmp_ad where oa_uid=0;

ALTER TABLE `fmp_ad`
MODIFY COLUMN `oa_uid`  int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'OA用户ID' AFTER `id`,
ADD COLUMN `oa_dept_id`  int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'OA部门ID' AFTER `oa_uid`;

ALTER TABLE `fmp_ad`
ADD COLUMN `account_id` varchar(32) COLLATE utf8_bin DEFAULT '' COMMENT '企业账号ID' AFTER `ad_describe`,
ADD COLUMN `page_id` varchar(32) COLLATE utf8_bin DEFAULT '' COMMENT '粉丝页ID(如果不填写广告和创意的内容)' AFTER `account_id`,
ADD COLUMN `post_id` varchar(32) COLLATE utf8_bin DEFAULT '' COMMENT '创意ID(如果不填写广告和创意的内容)' AFTER `page_id`;

/*刷数据之前,预览*/
/*select
fmp_ad.id,
fmp_ad.oa_uid,
fmp_ad.oa_dept_id,
oa_users.uid,
oa_users.id_department
from fmp_ad inner join oa_users on oa_users.uid = fmp_ad.oa_uid where fmp_ad.oa_dept_id!=0;*/

/*历史数据使用该企业账号*/
update fmp_ad set account_id = '2028306480774711' where account_id='';

/*刷部门ID*/
update fmp_ad inner join oa_users on oa_users.uid = fmp_ad.oa_uid
set fmp_ad.oa_dept_id = oa_users.id_department
where fmp_ad.oa_dept_id=0;
