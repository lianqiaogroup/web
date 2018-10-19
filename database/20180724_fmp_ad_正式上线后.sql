
/*刷部门ID*/
update fmp_ad inner join oa_users on oa_users.uid = fmp_ad.oa_uid
set fmp_ad.oa_dept_id = oa_users.id_department
where fmp_ad.oa_dept_id=0;
