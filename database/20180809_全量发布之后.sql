/* 后续不再使用 mtype和mtag字段,因为字段的语义 有歧义 */
update material set product_category_id = mtype, resource_type_id = mtag;