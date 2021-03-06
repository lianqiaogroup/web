<?php

namespace admin\Validator;

class ProductValidator {

    /**
     * 验证产品数据
     * @param array $data 要验证的源数据
     * @param int $product_id 产品id
     * @param int $lastUid    优化师id
     * @param int $operationType 验证类型 1：产品添加/编辑  2：复制产品
     * @return array 验证后的数据
     */
    public static function handle($data, $product_id, $lastUid = 0, $operationType = 1) {

        $data['usersData'] = []; //优化师数据
        $data['product_department_id'] = 0; //产品优化师部门id
        $data['user_department_id'] = 0; //优化师部门id
        $oldUid = 0; //产品优化师id

        $validator = \lib\register::createInstance('\lib\Validator\Validator'); //验证工具类
        if ($product_id) {
            $productData = \lib\register::getInstance('db')->get('product', ['oa_id_department', 'ad_member_id', 'domain', 'identity_tag', 'erp_number', 'id_zone', 'photo_txt'], ['product_id' => $product_id]); //获取产品部门数据
            if ($productData) {
                $oldUid = $productData['ad_member_id'];
                $lastUid = $lastUid ? $lastUid : $oldUid;
                $data['product_department_id'] = $productData['oa_id_department'];
                $data['erp_number'] = isset($data['erp_number']) ? $data['erp_number'] : $productData['erp_number']; //ERP产品id

                if ($operationType == 1) {//如果是编辑产品，ERP域名/二级目录/ERP产品id/地区  不可以修改
                    $data['old_domain'] = $productData['domain']; //ERP域名
                    $data['old_identity_tag'] = $productData['identity_tag']; //二级目录
                    $data['old_erp_number'] = $productData['erp_number']; //ERP产品id
                    $data['old_id_zone'] = $productData['id_zone']; //地区
                    $data['old_photo_txt'] = $productData['photo_txt']; //图片文字水印
                    $ruleData = [
                        'domain||||equal(old_domain)=ERP域名有误，请检查后再提交',
                        'identity_tag||||equal(old_identity_tag)=二级目录有误，请检查后再提交',
                        'erp_number||||equal(old_erp_number)=ERP产品id有误，请检查后再提交',
                        'id_zone||||equal(old_id_zone)=地区有误，请检查后再提交',
                        'photo_txt||||equal(old_photo_txt)=图片水印文字有误，请检查后再提交', //7:后台做校验，图片水印文字，是否为之前的信息。
                    ];
                    $isPass = $validator->doValidate($data, $ruleData, true);
                    if (!$isPass) {
                        ajaxReturn(['ret' => 0, 'msg' => $validator->getMsg()]);
                    }
                }
            }
        }

        if ($lastUid) {
            $usersData = \lib\register::getInstance('db')->get('oa_users', ['username', 'name_cn', 'id_department'], ['uid' => $lastUid, 'username[!]' => '', 'password[!]' => '', 'id_department[!]' => 0]); //检查过滤正常的优化师
            if ($usersData) {
                $data['usersData'] = $usersData;
                $data['user_department_id'] = $usersData['id_department'];
            }
        }


        //验证优化师
        $ruleData = [
            'usersData|||require=站点所属优化师已经离职，请变更优化师！|',
        ];
        $isPass = $validator->doValidate($data, $ruleData, true);
        if (!$isPass) {
            ajaxReturn(['ret' => 0, 'msg' => $validator->getMsg(), 'code' => 1]);
        }

        //验证部门是否变更
        if ($product_id && $oldUid == $lastUid) {

            $ruleData = [
                'product_department_id||||equal(user_department_id)=站点所属优化师不在该部门，请变更优化师！',
            ];
            $isPass = $validator->doValidate($data, $ruleData, true);
            if (!$isPass) {
                ajaxReturn(['ret' => 0, 'msg' => $validator->getMsg(), 'code' => 2]);
            }
        }

        //验证ERP产品id
        if ($operationType == 1 && empty($product_id)) {//如果是新增，就验证ERP产品id
            if (strpos(strval($data['erp_number']), '0') === 0 || !preg_match("/^\d*$/", $data['erp_number'])) {
                ajaxReturn(['ret' => 0, 'msg' => 'erp_id只能为纯数字以及第一位数字不能为0', 'code' => 3]);
            }
        }

        $loginid = $data['usersData']['username'];
        $domain = $data['domain'];

        $ruleData = [
            'id_zone||int=地区有误，请检查后再提交|require=地区有误，请检查后再提交|', //验证地区
            'lang||noempty=语言有误，请检查后再提交|require=语言有误，请检查后再提交|', //验证语言
            'theme||noempty=主题有误，请检查后再提交|require=主题有误，请检查后再提交|', //验证模板
        ];
        switch ($operationType) {
            case 1://产品 新增/编辑
                if (isCn($data['sales_title']) && !in_array($data['lang'], ['TW', 'CN', 'JP'])) {
                    ajaxReturn(['ret' => 0, 'msg' => "外文名含有中文，修改后提交", 'code' => 5]);
                }
                $ruleData[] = 'title||noempty=ERP产品名称有误，请检查后再提交|require=ERP产品名称有误，请检查后再提交|'; //验证产品名称
                $ruleData[] = 'waybill_title||noempty=面单名称有误，请检查后再提交|require=面单名称有误，请检查后再提交|'; //验证面单名称
                $ruleData[] = 'erp_number||int=erp_id只能为纯数字以及第一位数字不能为0|require=erp_id只能为纯数字以及第一位数字不能为0|'; //验证erp_id
                $data['new_photo_txt'] = $domain . '/' . $data['identity_tag'];
                $ruleData[] = 'photo_txt||||equal(new_photo_txt)=图片水印文字有误，请检查后再提交'; //7:后台做校验，图片水印文字，是否为之前的信息。
                break;

            case 2://产品 复制
                break;

            default:
                break;
        }
        $isPass = $validator->doValidate($data, $ruleData, true);
        if (!$isPass) {
            ajaxReturn(['ret' => 0, 'msg' => $validator->getMsg(), 'code' => 6]);
        }

        $product = \lib\register::createInstance('\admin\helper\product');
        if (!($operationType == 1 && $product_id)) {//如果是添加 或者  复制，就要验证域名的有效性

            /*             * **************验证域名 start ******************** */
            $ruleData = [
                'domain||domain=ERP域名有误，请检查后再提交|require=ERP域名有误，请检查后再提交|',
            ];
            $isPass = $validator->doValidate($data, $ruleData, true);
            if (!$isPass) {
                ajaxReturn(['ret' => 0, 'msg' => $validator->getMsg(), 'code' => 7]);
            }

            //jimmmy fix 如果领导则多个部门id就不使用get的id_department
            $company = \lib\register::createInstance('\admin\helper\company');
            $id_departmentArray = $company->get_id_departments();
            $id_department = implode(',', $id_departmentArray);

            $params = ['loginid' => $loginid, 'id_department' => $id_department];
            $damainData = \lib\register::createInstance('\admin\helper\api\domain')->getSeoDomain($params);
            $damainData = array_column($damainData, 'dead_time', 'domain');
            foreach ($damainData as $key => $deadTime) {
                if (substr($key, 0, 4) != 'www.') {
                    $damainData['www.' . $key] = $deadTime;
                }
            }

            /*if (!array_key_exists($domain, $damainData)) {
                ajaxReturn(['ret' => 0, 'msg' => '您部门不能使用该域名：' . $domain]);
            }*/

            if (isset($damainData[$domain]) && $damainData[$domain] < date('Y-m-d')) {
                ajaxReturn(['ret' => 0, 'msg' => '域名：' . $domain] . ' 到期时间：' . $damainData[$domain]);
            }
            /*             * **************验证域名 end ******************** */

            /*             * ********验证二级目录 start **************** */
            if (trim($data['identity_tag']) == '0') {
                ajaxReturn(['ret' => 0, 'msg' => '二级目录不能为0']);
            }

            $ruleData = [
                'identity_tag||alnum=二级目录只能是字母或者数字或字母数字的组合|require=二级目录只能是字母或者数字或字母数字的组合|',
            ];
            $isPass = $validator->doValidate($data, $ruleData, true);
            if (!$isPass) {
                ajaxReturn(['ret' => 0, 'msg' => $validator->getMsg(), 'code' => 8]);
            }

            $ret = $product->domainCheck($data['domain'], $data['identity_tag'], $product_id);
            if (!$ret['ret']) {
                ajaxReturn(['ret' => 0, 'msg' => $ret['msg'], 'code' => 8]);
            }
            /*             * ********验证二级目录 end  **************** */
        }

        /*         * *************验证地区和短信开关 start ****************** */
        if ($operationType == 1) {//产品 新增/编辑
            $country = \lib\register::createInstance('\admin\helper\country');
            $zoneData = $country->getOne($data['id_zone']);
            if (empty($zoneData)) {
                ajaxReturn(['ret' => 0, 'msg' => '地区id不存在']);
            }
            $data['zoneData'] = $zoneData;
            $data['zone'] = $zoneData['code'];

            //验证短信开关  规则：该地区是否为强制开通短信验证码的地区，如果是的话，短信验证码开通状态只能是。
            if ($zoneData['is_force_open_sms'] == 'enable' && $data['is_open_sms'] != 1 && $_SESSION['admin']['company_id']==1) {//如果 $data['id_zone'] 对应的地区强制开启短信，但是对应的产品选择不开启短信，就提示用户
                ajaxReturn(['ret' => 0, 'msg' => '该国家为强制开通短信验证码的地区，请选择 "是" !']);
            }
        }
        /*         * *************验证地区和短信开关 end  ****************** */

        /*         * *************验证模板有效性 start ****************** */
        $res = $product->getThemesByIdzonAndLanguage($data['zone'], $data['lang'], $_SESSION['admin']['id_department']);
        if (empty($res)) {
            ajaxReturn(['ret' => 0, 'msg' => '根据输入的地区和语言查到的模板为空, 请更换模版']);
        }

        $themeData = array_column($res, 'theme');
        if (!in_array($data['theme'], $themeData)) {
            ajaxReturn(['ret' => 0, 'msg' => '输入的模板和根据输入的地区和语言查到的模板不匹配, 请更换模板']);
        }
        /*         * *************验证模板有效性 end ****************** */

        /*         * *************验证单品站在产品 与 ERP 产品数据  是否一致 ****************** */
        $tip = ['id_zone' => "产品不可投放在您选择的地区,请检查后再提交"];
        if ($operationType == 1) {//产品 新增/编辑
            $tip['title'] = 'ERP产品名称有误，请检查后再提交';

            $tip['productAttr'] = [
                'attr_group_id' => '每个属性组至少保留一个属性',
                'number' => 'ERP属性值id有误，请检查后再提交',
            ];

            //8:后台做校验，属性数是否与erp是一致。若是站点新增时，也做一样的校验。
            $data = self::getProductAttr($data, 'attr_group_id', 'attr_group_title', 'attr_erp_number', 'name');
            $data = self::getProductAttr($data, 'up_attr_group_id', 'up_attr_group_title', 'up_number', 'up_name');
        }

        //验证单品站在产品 与 ERP 产品数据  是否一致
        \admin\Validator\ErpProductValidator::handle($_SESSION['admin']['company_id'], $data['erp_number'], $loginid, $data, $tip, $operationType);

        return $data;
    }

    public static function getProductAttr($data, $attrGroupIdName, $attrGroupTitleName, $numberName, $attrName) {

        if (!isset($data[$attrGroupIdName]) || empty($data[$attrGroupIdName])) {
            return $data;
        }

        //8:后台做校验，属性数是否与erp是一致。若是站点新增时，也做一样的校验。
        foreach ($data[$attrGroupIdName] as $key => $t) {
            $name = trim($data[$attrName][$key]); //属性值名称
            $attrGroupTitle = trim($data[$attrGroupTitleName][$key]); //属性组标题
            if (empty($name) || empty($attrGroupTitle)) {
                ajaxReturn(['ret' => 0, 'msg' => '属性名称 或者 属性值名称 不能为空']);
            }

            if (!in_array($data['lang'], ['TW', 'CN', 'JP'])) {//如果使用 'TW', 'CN', 'JP'，就判断 属性或属性组值外文名是否含有中文
                if (isCn($name) || isCn($attrGroupTitle)) {//如果 属性或属性组值外文名含有中文，就提示用户修改后提交
                    ajaxReturn(['ret' => 0, 'msg' => '属性名称 或 属性值名称 外文名含有中文，修改后提交']);
                }
            }

            $data['productAttr'][] = [
                'attr_group_id' => $data[$attrGroupIdName][$key], //产品属性组id
                'number' => $data[$numberName][$key], //产品属性值id
            ];
        }

        return $data;
    }

}
