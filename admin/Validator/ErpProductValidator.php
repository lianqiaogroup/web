<?php

namespace admin\Validator;

class ErpProductValidator {

    /**
     * 验证单品站在产品 与 ERP 产品数据  是否一致
     * @param int $company_id 公司id
     * @param int $erp_number ERP产品id
     * @param string $loginid   优化师账号
     * @param array $data    要验证的源数据
     * @param array $tip     要校验的数据 包含提示和要校验的key
     * @param int $operationType 验证类型 1：产品添加/编辑  2：复制产品 默认：1
     * @return boolean true:执行完成
     */
    public static function handle($company_id, $erp_number, $loginid, $data, $tip, $operationType = 1) {

        return true;

        if (empty($data)) {
            return true;
        }

        if (empty($tip)) {
            return true;
        }

        //获取ERP产品数据
        $params = ['company_id' => $company_id, 'id' => $erp_number, 'loginid' => $loginid];
        $erpProduct = \lib\register::createInstance('\admin\helper\api\erpproduct');
        $ret = $erpProduct->getProduct($params); //获取 ERP 产品数据
        if (empty($ret)) {//如果获取失败，就提示用户
            ajaxReturn(['ret' => 0, 'msg' => '没找到ERP产品信息']);
        }

        /*if (!($ret['status'] && $ret['product'])) {
            $msg = empty($ret['message']) ? '' : $ret['message'];
            ajaxReturn(['ret' => 0, 'msg' => "没找到ERP产品信息:" . $msg]);
        }*/

        if (is_array($ret['product']['productZoneNames']) && (count($ret['product']['productZoneNames']) > 0)) {
            $product = \lib\register::createInstance('\admin\helper\product');
            $ret['product']['productAvailableZoneIds'] = $product->getZoneIdList($ret['product']['productZoneNames']);
        }
        $erpProductInfo = $ret['product'];

        //4:验证产品是否可以在 $data['id_zone'] 对应的地区投放
        if (isset($tip['id_zone']) && !in_array($data['id_zone'], $erpProductInfo['productAvailableZoneIds'])) {//如果当前产品不可以投放到 $data['id_zone'] 对应的地区，就提示用户
            ajaxReturn(['ret' => 0, 'msg' => $tip['id_zone'], 'code' => 9]);
        }

        //6:做校验是否为原来的ERP产品名称 若是站点新增时，校验是否为erp的产品名称
        if (isset($tip['title']) && trim($data['title']) != trim($erpProductInfo['title'])) {//如果当前产品标题 与 ERP产品标题不一致，就提示用户
            ajaxReturn(['ret' => 0, 'msg' => $tip['title'], 'code' => 10]);
        }

        //8:后台做校验，属性数是否与erp是一致。若是站点新增时，也做一样的校验。
        $erpProductAttrGroupIdData = []; //ERP产品属性组id
        $erpProductAttIdData = []; //ERP产品属性值id
        if (isset($erpProductInfo['product_attr']) && is_array($erpProductInfo['product_attr'])) {
            foreach ($erpProductInfo['product_attr'] as $erpProductAttrData) {
                $erpProductAttrGroupId = $erpProductAttrData['id']; //ERP产品属性组id
                $erpProductAttrGroupIdData[] = $erpProductAttrGroupId;
                $erpProductAttIdData[$erpProductAttrGroupId] = array_column($erpProductAttrData['attributeValueList'], 'id'); //ERP产品属性值id
            }
        }

        if ($operationType == 1 && $erpProductAttrGroupIdData) {//如果是 产品添加/编辑 并且 ERP产品有属性，就验证单品站的产品属性组与ERP的产品属性组 是否完全一致
            if (!isset($data['productAttr']) || empty($data['productAttr'])) {//如果单品站的产品没有属性，就提示用户
                ajaxReturn(['ret' => 0, 'msg' => $tip['productAttr']['attr_group_id'], 'code' => 11]);
            }

            $erpProductAttrGroupIds = array_column($data['productAttr'], 'attr_group_id');
            if (empty($erpProductAttrGroupIds)) {//如果单品站的产品没有属性，就提示用户
                ajaxReturn(['ret' => 0, 'msg' => $tip['productAttr']['attr_group_id'], 'code' => 12]);
            }

            $erpProductAttrGroupIdData = array_filter(array_unique($erpProductAttrGroupIdData));
            $erpProductAttrGroupIds = array_filter(array_unique($erpProductAttrGroupIds));

            sort($erpProductAttrGroupIdData);
            sort($erpProductAttrGroupIds);

            if ($erpProductAttrGroupIdData != $erpProductAttrGroupIds) {//如果单品站的产品属性组和ERP的产品属性组 不一致，就提示用户
                ajaxReturn(['ret' => 0, 'msg' => $tip['productAttr']['attr_group_id'], 'code' => 13]);
            }
        }

        if (isset($tip['productAttr']) && !empty($data['productAttr'])) {

            if (!isset($tip['productAttr']['attr_group_id']) && !isset($tip['productAttr']['number'])) {
                return $erpProductInfo;
            }

            foreach ($data['productAttr'] as $item) {

                $erpProductAttrGroupId = $item['attr_group_id']; //产品属性组id

                if (isset($tip['productAttr']['attr_group_id'])) {
                    if (!in_array($erpProductAttrGroupId, $erpProductAttrGroupIdData)) {//如果 属性id 与 ERP属性id 不一致，就提示用户
                        ajaxReturn(['ret' => 0, 'msg' => $tip['productAttr']['attr_group_id'], 'code' => 14]);
                    }
                }

                if (isset($tip['productAttr']['number'])) {
                    $erpProductAttId = $item['number']; //产品属性值id
                    if (!in_array($erpProductAttId, $erpProductAttIdData[$erpProductAttrGroupId])) {
                        ajaxReturn(['ret' => 0, 'msg' => $tip['productAttr']['number'], 'code' => 15]);
                    }
                }
            }
        }

        return $erpProductInfo;
    }

}
