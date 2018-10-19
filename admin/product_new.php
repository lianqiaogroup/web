<?php

require_once 'ini.php';
use admin\helper\qiniu ;
use lib\log;

if (isset($_GET['act']) && $_GET['act'] == 'edit') {
    //修改
    //$product       = new \admin\helper\product($register);
    $product       = \lib\register::createInstance('\admin\helper\product');
    $product_id    = isset($_GET['product_id']) ? $_GET['product_id'] : '';
    $data = [];
    $support =0;
    $isSuppertBluePay = false;//是否支持blueplay
    $zone_filter = [];
    if ($product_id) {
        $data          = $product->getOneProduct($product_id);
        if(!$data['ret'])
        {
            $error['content'] = $data['msg'];
            $error['url']     = "index.php#products";
            $register->get('view')->display('error.twig', $error);
            exit;
        }
        $data['content']  = qiniu::get_content_path( $data['content']);

        $gle_data      = $product->getProductGoogleExt($product_id);
        $data['gle']   = $gle_data;

        //查找属性
        $attr         = $product->getProduct_group_attr($product_id);
        foreach ($attr as $k =>$v){
            $attr[$k]['thumb'] = qiniu::get_image_path($v['thumb']);
        }
        //$data['attr'] = $attr;
        $data['attrddn'] = $attr;

        $data['logo'] = qiniu::get_image_path($data['logo']);
        $data['thumb'] = qiniu::get_image_path($data['thumb']);

        //图集
        $photos         = $db->select('product_thumb', '*', ["ORDER" => ['add_time' => "DESC"], 'product_id' => $product_id]);
        foreach ($photos as $k=>$v){
            $photos[$k]['thumb'] = qiniu::get_image_path($v['thumb']);
        }
        $data['photos'] = $photos;

        // 视频
        $videoModel = new \admin\helper\productvideo($register);
        $data['video'] = $videoModel->getProductVideo($product_id);
        $data['video']['video_url'] = qiniu::get_video_path($data['video']['video_url']);
        if(isset($data['video']) && isset($data['video']['cover_url']))$data['video']['cover_url'] = qiniu::get_image_path($data['video']['cover_url']);

        //套餐
        $combo         = new \admin\helper\combo($register);
        $combo         = $combo->findCombo($product_id);
        if(!empty($combo)){
            foreach ($combo as $k=>$v){
                $combo[$k]['thumb'] = qiniu::get_image_path($v['thumb']);
            }
        }
        $data['combo'] = $combo;
        $data['combo_num'] = count($combo);
        
        // 获取产品投放地区
        $zone_filter = ['AND' => ['id_zone'=> [$data['id_zone']] ]];
        // # zone email 优先级高于 domain email
        // $data['zone_email']  = $D->getZoneEmail($id_zone);

        //判断是否支持钱海支付
        $pay = new \admin\helper\payment($register);
        $support = $pay->supportOcean($data['domain']);
        $isSuppertBluePay = $pay->supportOcean($data['domain'],'bluePay');
    } else {
        $data['ad_member_id'] = -1;
    }


    $data['admin'] = $_SESSION['admin'];

    $c = new \admin\helper\company($register);
    $ad_member_list = $c->getAdUser($_SESSION['admin']['uid']);

    //判断如果人员已转移但是这个产品属于他则把他加进去
    if(isset($data['ad_member']) && !in_array($data['ad_member'],array_column($ad_member_list,'name'))){
        $userModel = new \admin\helper\oa_users($register);
        $userInfo = $userModel->getOneUser('',$data['ad_member']);
        $userData['ad_member_id'] = $userInfo['uid'];
        $userData['name'] = $userInfo['name_cn'];
        $userData['id_department'] = $userInfo['id_department'];
        $userData['department'] = $userInfo['department'];
        $ad_member_list[] =$userData;
    }

    $data['ad_member_list'] = $ad_member_list;

    $D                 = new \admin\helper\country($register);
    $id_zone           = $D->getAllZone($zone_filter);

    $data['id_zones']  = $id_zone;
    $_SESSION['token'] = $data['token']     = md5(uniqid(rand(), true));
    $region            = $db->select('region', '*', ['parent_id' => 0]);
    $data['region']    = $region;
    $data['supportOcean'] = $support;
    $data['isSuppertBluePay'] = $isSuppertBluePay;

    //获取产品分类目录
    $category = new \admin\helper\category($register);

    //查询改产品的分类
    $module = $category->getTreeCat((isset($data['product_id'])?$data['product_id']:''),(isset($data['domain'])?$data['domain']:''));
    $data['module'] = $module;
    $data['category_title'] = isset($ret_category) ? $ret_category['title'] : '';
    
    $data['imgCdnTypeData'] = [1 => '七牛', 2 => 'AWS'];

    $data['mds_ddn'] = array(

        '259o'=>'购买_女士饰品·直',
        '259p'=>'购买_男士饰品·直',
        '259q'=>'购买_女士包·直',
        '259r'=>'购买_男士包·直',
        '259s'=>'购买_男士表·直',
        '259t'=>'购买_女士表·直',
        '259u'=>'购买_女士眼镜·直',
        '259v'=>'购买_女装·直',
        '259w'=>'购买_男装·直',
        '259x'=>'购买_男鞋·直',
        '259y'=>'购买_女鞋·直',
        '259z'=>'购买_女性减肥·直',

    );

    $md_ddn = '';
    $md_ddn2 = $product->getProductMd($product_id);
    if(!empty($md_ddn2))$md_ddn=$md_ddn2['md'];
    $data['md_ddn'] = $md_ddn;

    $cloak_page_ddn = '';
    $cloak_page_ddn2 = $product->getProductCloak($product_id);
    if(!empty($cloak_page_ddn2))$cloak_page_ddn=$cloak_page_ddn2['safepage'];
    $data['cloak_page_ddn'] = $cloak_page_ddn;

    // print_r($data);exit;
    $register->get('view')->display('/product/product_new.twig', $data);
} else if (isset($_GET['act']) && $_GET['act'] == 'save') {

    $product_id = isset($_POST['product_id']) ? $_POST['product_id'] : 0;
    $is_edit = empty($product_id) ? false : true;

    $users = $_POST['users']; //优化师id
    //新接口数据不兼容 处理ad_member
    $ad_member_id = $users;
    if ($ad_member_id == 1 || empty($ad_member_id)) {
        ajaxReturn(['ret' => 0, 'msg' => '请选择优化师或优化师不能为系统']);
    }

    $validatorData = \admin\Validator\ProductValidator::handle($_POST, $product_id, $ad_member_id, 1);
    $data = $validatorData['usersData'];
    $_POST = $validatorData;

    $error = [];
    $product = \lib\register::createInstance('\admin\helper\product');

    // 潜规则: 按地区是否强制开启短信(开放地区：台湾、泰国、柬埔寨、巴基斯坦、菲律宾、印尼)
    if ($_SESSION['admin']['company_id'] == 1 && $_POST['zoneData']['is_force_open_sms'] == 'enable') {
        $_POST['is_open_sms'] = 1;
    } else {
        if (!empty($_POST['is_open_sms'])) {
            $sms = new \admin\helper\sms($register);
            $ret = $sms->checkIspByZone($_POST['id_zone']);
            if (!$ret['ret']) {
                $_POST['is_open_sms'] = 0;
            }
        }
    }

    // 缩略图
    if (!empty($_POST['thumbsUrl'])) {
        $save['thumb'] = qiniu::changImgDomain($_POST['thumbsUrl']);
    }

    // logo
    if (!empty($_POST['logoUrl'])) {
        $save['logo'] = qiniu::changImgDomain($_POST['logoUrl']);
    }

    $sensitive = $_POST['waybill_title'] . '|' . $_POST['sales_title'] . '|';
    $save['available_zone_ids'] = empty($_POST['available_zone_ids']) ? '' : $_POST['available_zone_ids']; //可投放地区
    //combo验证套餐数据
    if (isset($_POST['combo']) && $_POST['combo']) {
        foreach ($_POST['combo'] as $key => $value) {

            if (empty($value['num'])) {
                continue;
            }

            foreach ($value['num'] as $k => $item) {
                if (empty($value['erp_id'][$k])) {
                    echo json_encode(['ret' => 0, 'msg' => '套餐产品erpid不能为空']);
                    exit;
                }
            }
        }
    }

    $seo_loginid = $data['username'];
    $ad_member = $data['name_cn'];
    $save['lang'] = $_POST['lang'];
    if (empty($product_id)) {//如果是新增，就保存
        $save['new_erp'] = 1; //jade 为兼容老数据，仅仅对于新增的产品 标识 new_erp
    }

    //jimmy：保证数据库是带www的  域名处理
    if (substr($_POST['domain'], 0, 4) == 'www.') {
        $save['domain'] = $_POST['domain'];
    } else {
        $save['domain'] = 'www.' . $_POST['domain'];
    }

    $save['identity_tag'] = trim($_POST['identity_tag']); //二级目录
    $save['erp_number'] = trim($_POST['erp_number']); //erp_id
    //获取地区查询对应货币以及lang包
    $id_zone = $_POST['id_zone'];
    $save['id_zone'] = $id_zone;
    $zone = $_POST['zoneData'];

    //根据 $zone 获取货币数据
    $C = \lib\register::createInstance('\admin\helper\currency');
    $code = empty($zone['currency']) ? 'RMB' : $zone['currency'];
    $currency = $C->getOne($zone['currency_id'], $code);
    $save['currency'] = $currency['currency_code'];
    $save['currency_prefix'] = 0;
    $save['currency_code'] = $currency['symbol_right'];
    if ($currency['symbol_left']) {
        $save['currency_prefix'] = 1;
        $save['currency_code'] = $currency['symbol_left'];
    }
    //获取地区 end
    //沿用 老erp的部门id
    $save['id_department'] = $_POST['id_department'];
    $save['ad_member_id'] = $ad_member_id;
    $save['ad_member'] = $ad_member; //jimmy fix save ad_member
    $save['tags'] = isset($_POST['tags']) ? trim($_POST['tags']) : '';
    $save['theme'] = $_POST['theme'];
    $save['content'] = qiniu::ContentDomain($_POST['content']); //将内容资源的绝对地址 还原为 可替换的相对地址
    $save['photo_txt'] = $_POST['photo_txt'];

    $save['price'] = money_int($_POST['price']);
    if (!$_POST['market_price']) {
        if (empty($_POST['discount']) || ($_POST['discount'] <= 0 ) || ($_POST['discount'] > 10 )) {
            $_POST['discount'] = 10;
        }
        $market_price = round($_POST['price'] / ($_POST['discount'] / 10));
        $save['market_price'] = money_int($market_price);
    } else {
        $save['market_price'] = money_int($_POST['market_price']);
    }
    $save['discount'] = money_int($_POST['discount']);
    $save['title'] = $_POST['title'];
    if (empty($_POST['sales_title'])) {
        echo json_encode(['ret' => 0, 'msg' => "外文名不能为空"]);
        exit;
    }
    $save['waybill_title'] = $_POST['waybill_title']; //面单名称
    $save['sales_title'] = $_POST['sales_title']; //
    $save['payment_online'] = isset($_POST['payment_online'])?$_POST['payment_online']:'';
    $save['payment_underline'] = I('post.payment_underline', 0);

    $save['payment_paypal'] = isset($_POST['payment_paypal']) ? $_POST['payment_paypal'] : '';
    $save['payment_asiabill'] = I('post.payment_asiabill', 0);
    $save['payment_ocean'] = I('post.payment_ocean', 0);
    $save['payment_blue'] = I('post.payment_blue', 0);
    if (!$save['payment_underline'] && !$save['payment_ocean'] && !$save['payment_blue']) {
        echo json_encode(['ret' => 0, 'msg' => '请选择支付方式']);
        exit;
    }

    $save['la'] = $_POST['la'];
    $fb_px = trim($_POST['fb_px']);
    $preg = '/^[0-9|,|，]{1,}$/';
    if (!preg_match($preg, $fb_px)) {
        echo json_encode(['ret' => 0, 'msg' => "fb像素格式错误"]);
        exit;
    }
    $fb_px = str_replace('，', ',', $fb_px);
    $fb_px = array_filter(explode(',', $fb_px));
    if (!$fb_px) {
        echo json_encode(['ret' => 0, 'msg' => "fb像素格式错误:不能全用逗号"]);
        exit;
    }

    $service_email = isset($_POST['service_email']) ? $_POST['service_email'] : '';

    //有统一邮箱则用统一邮箱
//    $zone_email = $db->get('zone',['email','lark_email','dodo_email'],['id_zone'=>$id_zone]);
//    switch ($_SESSION['admin']['company_id']){
//        case 1:
//            if(!empty($zone_email['email'])) $service_email =  $zone_email['email'];
//            break;
//        case 8:
//            if(!empty($zone_email['dodo_email'])) $service_email = $zone_email['dodo_email'];
//            break;
//        case 9:
//            if(!empty($zone_email['lark_email'])) $service_email = $zone_email['lark_email'];
//            break;
//    }
    $fb_px = implode(',', $fb_px);
    $save['fb_px'] = $fb_px;
    $save['sales'] = $_POST['sales'] ?: 1000;
    $save['stock'] = $_POST['stock'] ?: 100;
    $save['service_contact_id'] = $_POST['service_contact_id'];
    $save['service_email'] = $service_email;
    $save['google_js'] = isset($_POST['google_js'])?$_POST['google_js']:'';
    $save['google_analytics_js'] = isset($_POST['google_analytics_js'])?$_POST['google_analytics_js']:'';
    $save['yahoo_js'] = isset($_POST['yahoo_js'])?$_POST['yahoo_js']:'';
    $save['yahoo_js_trigger'] = isset($_POST['yahoo_js_trigger'])?$_POST['yahoo_js_trigger']:'';
    $save['tips'] = $_POST['tips'];

    ## 仅对company为1的 特殊处理 
    if ($_SESSION['admin']['company_id'] == 1) {
        $zone_email = $db->get('zone', 'email', ['id_zone' => $save['id_zone']]);
        $save['service_email'] = empty($zone_email) ? ($save['service_email']) : $zone_email;
    }

    if (!$_POST['seo_title'] || !$_POST['seo_description']) {
        $save['seo_title'] = $save['seo_description'] = $_POST['title'];
    } else {
        $save['seo_title'] = $_POST['seo_title'];
        $save['seo_description'] = $_POST['seo_description'];
    }

    if (!$product_id) {//如果是编辑，就更新以下信息
        $save['company_id'] = $_SESSION['admin']['company_id'] ?: 1;
        $save['add_time'] = date("Y-m-d H:i:s", time());
        $save['oa_id_department'] = $data['id_department'];
    }
    $save['last_utime'] = date("Y-m-d H:i:s", time());
    $save['is_open_sms'] = (int) $_POST['is_open_sms'];

    if (isset($_POST['img_cdn_type'])) {//如果要更新 图片cdn类型 1:国外图片地址img 2:aws 默认:1
        $save['img_cdn_type'] = (int) $_POST['img_cdn_type'];
    }

    //保存产品
    $db->pdo->beginTransaction();

    $ret = $product->saveProduct($product_id, $save);
    $product_id = empty($product_id) ? $ret : $product_id;

    if (!$product_id) {
        echo json_encode(['ret' => 0, 'msg' => '保存产品失败']);
        exit;
    }

    //保存产品分类
    $cat_product_id = empty($product_id) ? $ret : $product_id;
    if ($cat_product_id) {
        $category_id = $_POST['product_category'];
        $category = new \admin\helper\category($register);
        $ret_cat = $category->saveProductCategory(['category_id' => $category_id, 'product_id' => $cat_product_id]);
    }

    //保存产品视频
    $videoProductID = empty($product_id) ? $ret : $product_id;
    if ($videoProductID) {
        $videoData['video_url'] = !empty($_POST['videoUrl']) ? qiniu::changImgDomain($_POST['videoUrl']) : '';
        $videoData['cover_url'] = !empty($_POST['coverUrl']) ? qiniu::changImgDomain($_POST['coverUrl']) : '';
        $videoData['add_time'] = date('Y-m-d H:i:s', time());
        $videoData = array_filter($videoData);
        if (isset($videoData['video_url']) || isset($videoData['cover_url'])) {
            $videoData['product_id'] = $videoProductID;
            $videoModel = new \admin\helper\productvideo($register);
            $ret_cat = $videoModel->saveProductVideo($videoData);
        }
    }

    //新增属性提前判断 并处理
    $insertAttrData = [];
    if (isset($_POST['attr_group_id'])&&$_POST['attr_group_id']) {
        foreach ($_POST['attr_group_id'] as $key => $t) {
            $insertAttrData[$key]['name'] = $_POST['name'][$key];
            $insertAttrData[$key]['attr_group_id'] = $t;
            $insertAttrData[$key]['thumb'] = qiniu::changImgDomain($_POST['attr_thumb'][$key]);
            $insertAttrData[$key]['attr_group_title'] = trim($_POST['attr_group_title'][$key]);
            $insertAttrData[$key]['number'] = trim($_POST['attr_erp_number'][$key]);

            if (empty($insertAttrData[$key]['name']) || empty($insertAttrData[$key]['attr_group_title'])) {
                $db->pdo->rollBack();
                echo json_encode(['ret' => 0, 'msg' => "属性或者属性组不能为空"]);
                exit;
            } else {
                if (!in_array($save['lang'], ['TW', 'CN', 'JP'])) {
                    if (isCn($insertAttrData[$key]['name']) || isCn($insertAttrData[$key]['attr_group_title'])) {
                        $db->pdo->rollBack();
                        echo json_encode(['ret' => 0, 'msg' => "属性组或属性值外文名含有中文，修改后提交"]);
                        exit;
                    }
                }
            }
        }
    }
    //unset($_POST['attr_group_id']);
    //编辑属性提前判断 并处理
    $saveAttrData = [];
    if (isset($_POST['product_attr_id'])&&$_POST['product_attr_id']) {
        foreach ($_POST['product_attr_id'] as $k => $v) {
            $upAttr = [];
            $upAttr['name'] = trim($_POST['up_name'][$k]);
            $upAttr['attr_group_id'] = $_POST['up_attr_group_id'][$k];
            $upAttr['attr_group_title'] = trim($_POST['up_attr_group_title'][$k]);
            $upAttr['number'] = $_POST['up_number'][$k];
            if ($_POST['attr_thumb_url'][$k]) {
                $upAttr['thumb'] = qiniu::changImgDomain($_POST['attr_thumb_url'][$k]);
            }
            $saveAttrData[] = [$v, $upAttr, $product_id];
        }
    }
    //unset($_POST['product_attr_id']);

    $insertCombos = []; //新增套餐 包含产品数据
    $updateCombos = []; //更新套餐
    $updateComboGoods = []; //更新套餐产品
    $addComboGoods = []; //新增 已有套餐产品
    //套餐提前判断 并处理
    if (isset($_POST['combo'])&&$_POST['combo']) {
        foreach ($_POST['combo'] as $key => $value) {
            $combo = [];
            $combo_id = empty($value['combo_id']) ? 0 : $value['combo_id'];
            $combo['title'] = $value['name'];
            $combo['is_single_combo'] = isset($value['is_single_combo'])?$value['is_single_combo']:'';
            if (!$combo['title']) {
                echo json_encode(['ret' => 0, 'msg' => "套餐名称不能为空"]);
                exit;
            }
            $sensitive .= $combo['title'] . '|';

            if (isCn($combo['title']) && !in_array($save['lang'], ['TW', 'CN', 'JP'])) {
                $db->pdo->rollBack();
                echo json_encode(['ret' => 0, 'msg' => "套餐名称含有中文，修改后提交"]);
                exit;
            }
            $combo['price'] = money_int($value['price']);
            if (!$combo['price']) {
                $db->pdo->rollBack();
                echo json_encode(['ret' => 0, 'msg' => "套餐价格不能为0或空"]);
                exit;
            }
            if (isset($value['is_lock_total'])) {
                $combo['is_lock_total'] = 1; //默认值是0
            }
            if ($value['thumb']) {
                $combo['thumb'] = qiniu::changImgDomain($value['thumb']);
            }
            $comboGoods = []; //套餐产品集合 初始数组
            foreach ($value['num'] as $k => $item) {

                $combo_goods_id = empty($value['combo_goods_id'][$k]) ? 0 : $value['combo_goods_id'][$k];
                if (!$value['erp_id'][$k]) {
                    $db->pdo->rollBack();
                    ajaxReturn(['ret' => 0, 'msg' => '套餐产品erpid不能为空']);
                }
                
                $comboGoods[$k] = [];
                $comboGoods[$k]['num'] = $item;
                $comboGoods[$k]['erp_id'] = intval(str_replace(' ', '', $value['erp_id'][$k]));
                $comboGoods[$k]['product_id'] = $value['product_id'][$k];
                $comboGoods[$k]['title'] = $value['title'][$k];
                $comboGoods[$k]['promotion_price'] = money_int($value['promotion_price'][$k]);
                $comboGoods[$k]['sale_title'] = empty($value['sale_title'][$k]) ? $value['title'][$k] : $value['sale_title'][$k];
                if (!$comboGoods[$k]['title'] || !$comboGoods[$k]['sale_title']) {
                    $db->pdo->rollBack();
                    echo json_encode(['ret' => 0, 'msg' => "套餐产品名称不能为空"]);
                    exit;
                }
                if (isCn($comboGoods[$k]['sale_title']) && !in_array($save['lang'], ['TW', 'CN', 'JP'])) {
                    $db->pdo->rollBack();
                    echo json_encode(['ret' => 0, 'msg' => "套餐外文名含有中文，修改后提交"]);
                    exit;
                }

                $sensitive .= $comboGoods[$k]['sale_title'] . "|";
                
                // 判断套餐中的商品是否已删除
                $comboGoodsStatus = $db->get('product', ['product_id', 'ad_member_id', 'erp_number', 'is_del'], ['product_id' => $comboGoods[$k]['product_id']]);
                if ($comboGoodsStatus['is_del'] != 0) {
                    $db->pdo->rollBack();
                    ajaxReturn(['ret' => 0, 'msg' => "套餐中包含已删除商品(产品ID:" . $comboGoods[$k]['product_id'] . "),请优化后再保存"]);
                }

                //套餐产品的 可选属性 检出
                // $comboGoods[$k]['attr_id_desc'] = $value['attr_id_desc'][$k];
                $productAttrWhere = ['product_id' => $comboGoods[$k]['product_id'], 'is_del' => 0];
                if ($value['attr_id_desc'][$k]) {
                    $comboGoods[$k]['attr_id_desc'] = $product->attrCheckout($comboGoods[$k]['product_id'], $value['attr_id_desc'][$k], $comboGoodsStatus);
                    if ($comboGoods[$k]['attr_id_desc'] === false) {
                        $combo['is_del'] = 1; //套餐产品存在某属性组没有可用属性值，保存时套餐自动失效
                    }

                    $comboGoodsAttr = json_decode($comboGoods[$k]['attr_id_desc'], true);
                    $productAttrData = [];
                    foreach ($comboGoodsAttr as $goodsAttr) {
                        $productAttrData = array_merge($productAttrData, $goodsAttr);
                    }

                    if ($productAttrData) {
                        $productAttrData = array_unique($productAttrData);
                        $productAttrWhere['product_attr_id'] = $productAttrData;
                    }
                } else {
                    $comboGoods[$k]['attr_id_desc'] = '';
                }

                $comboValidatorData['productAttr'] = \lib\register::getInstance('db')->select('product_attr', ['number', 'attr_group_id'], $productAttrWhere);

                // 获取套餐中的商品的可投放区域 9:套餐内的产品也要做校验,属性数是否与erp是一致。若是站点新增时，也做一样的校验
                $users = new \admin\helper\oa_users($register);
                $loginid = $users->getUsernameByUid(trim($comboGoodsStatus['ad_member_id'])); //使用优化师的loginid
                $comboValidatorData['id_zone'] = $save['id_zone'];
                $tip['productAttr'] = [
                    'attr_group_id' => '套餐产品 每个属性组至少保留一个属性',
                    'number' => '套餐产品 属性值id与ERP属性值id 不一致',
                ];
                $erpProductInfo = \admin\Validator\ErpProductValidator::handle($_SESSION['admin']['company_id'], $comboGoodsStatus['erp_number'], $loginid, $comboValidatorData, $tip, 1);
                if ($erpProductInfo) {
                    /*if (!in_array($save['id_zone'], $erpProductInfo['productAvailableZoneIds'])) {
                        $db->pdo->rollBack();
                        ajaxReturn(['ret' => 0, 'msg' => "套餐中包含商品(产品ID:" . $comboGoods[$k]['product_id'] . "), 不可投放在" . $zone['title'] . "地区,请修改后,再保存"]);
                    }*/
                }

                if ($combo_id) {
                    $comboGoods[$k]['combo_id'] = $combo_id;
                    if ($combo_goods_id) {
                        $updateComboGoods[] = [$combo_goods_id, $comboGoods[$k]]; //已有套餐的更新产品
                        if($comboGoodsStatus['erp_number'] != $value['erp_id'][$k]){
                            $db->pdo->rollBack();
                            ajaxReturn(['ret' => 0, 'msg' => '套餐产品erpid不能修改']);
                        }
                    } else {
                        $addComboGoods[] = $comboGoods[$k]; //已有套餐的新增产品
                    }
                }
            }
            if ($combo_id) {
                $updateCombos[] = [$combo_id, $combo];
            } else {
                $combo['goods'] = $comboGoods; //新增套餐的新增产品
                $insertCombos[] = $combo;
            }
        }
    }

    $objSensitive = \lib\register::createInstance('\admin\helper\api\sensitive');
    $results = $objSensitive->getSensitive(['sensitive' => $sensitive]);
    if (!empty($results)) {
        $db->pdo->rollBack();
        echo json_encode(['ret' => 0, 'msg' => '外文名称或面单称或者套餐内含有敏感词:' . $results]);
        exit;
    }

    //判断处理完毕：开始sql入库
    try {
        //新增产品功能 保存插入产品属性ID 用户保存产品的原图
        $productAttributeIDS = [];
        //新增属性

        if ($insertAttrData) {
            $insertAttrs = [];
            foreach ($insertAttrData as $insertAttr) {
                $insertAttr['product_id'] = $product_id;
                $insertAttrs[] = $insertAttr;
            }
            $ret = $product->addForSingleProductAttr($insertAttrs);
            $productAttributeIDS = $ret;
        }
        //更新属性
        if ($saveAttrData) {
            foreach ($saveAttrData as $v) {
                $product->saveProductAttr($v[0], $v[1], $v[2]);
            }
        }
        //用户保存插入套餐数据库的套餐ID 用户保存产品的原图
        $productComboIDS = [];
        //新增套餐
        if ($insertCombos) {
            krsort($insertCombos); //页面已经修改，上面的是最新的，最后新增
            foreach ($insertCombos as $insertCombo) {
                $_comboGoods = $insertCombo['goods'];
                krsort($_comboGoods); //新加的套餐产品在上面，id取较大
                $insertComboGoods = [];
                unset($insertCombo['goods']);
                $insertCombo['product_id'] = $product_id;
                $combo_id = $db->insert('combo', $insertCombo);
                $productComboIDS[] = $combo_id;
                foreach ($_comboGoods as $insertGoods) {
                    $insertGoods['combo_id'] = $combo_id;
                    $insertComboGoods[] = $insertGoods;
                }
                $r = $db->insert('combo_goods', $insertComboGoods);
                if ($r) {
                    $sql = $db->last();
                    //仅成功的日志进入mysql日志
                    $log = [];
                    $msg = '产品关联套餐新增套餐内产品';
                    $log['act_table'] = 'combo';
                    $log['act_sql'] = $sql;
                    $log['act_desc'] = $msg;
                    $log['act_time'] = time();
                    $log['act_type'] = 'insert_combo_goods';
                    $log['product_id'] = $product_id;
                    $log['act_loginid'] = $_SESSION['admin']['login_name'];
                    $product->saveProductLog($log); // $db->insert("product_act_logs", $log);
                }
            }
        }

        //新增已有套餐产品
        if ($addComboGoods && count($addComboGoods) > 0) {
            $r = $db->insert('combo_goods', $addComboGoods);
            if ($r) {
                $sql = $db->last();
                //仅成功的日志进入mysql日志
                $log = [];
                $msg = '新增产品关联套餐以及套餐内产品';
                $log['act_table'] = 'combo';
                $log['act_sql'] = $sql;
                $log['act_desc'] = $msg;
                $log['act_time'] = time();
                $log['act_type'] = 'insert_combo_goods';
                $log['product_id'] = $product_id;
                $log['act_loginid'] = $_SESSION['admin']['login_name'];
                $product->saveProductLog($log); // $db->insert("product_act_logs", $log);
            }
        }

        $data_name = ['erp_id' => 'erpID', 'product_id' => '产品ID', 'combo_id' => '套餐ID', 'attr_id_desc' => '属性', 'title' => '套餐名称', 'thumb' => '套餐图片', 'is_single_combo' => '限购', 'price' => '价格', 'promotion_price' => '促销单价', 'num' => '数量', 'sale_title' => '产品外文名称'];
        //更新套餐
        if ($updateCombos) {
            $num = count($updateCombos);
            foreach ($updateCombos as $k => $v) {
                $result = $db->get('combo', '*', ['combo_id' => $v[0]]);
                $result['price'] = money_int($result['price'], 2);
                $r = $db->update('combo', $v[1], ['combo_id' => $v[0]]);
                $v[1]['price'] = money_int($v[1]['price'], 2);

                //关联套餐
                $n = $num - $k;
                $msg = '更新关联套餐' . ($n);
                if ($r) {
                    $sql = $db->last();
                    //仅成功的日志进入mysql日志
                    $log = [];
                    foreach ($v[1] as $key => $value) {
                        if ($result[$key] != $value)
                            $msg .= "字段 {$data_name[$key]} 旧值为{$result[$key]}， 更新为 {$value}  <br>";
                    }
                    $log['act_table'] = 'combo';
                    $log['act_sql'] = $sql;
                    $log['act_desc'] = $msg;
                    $log['act_time'] = time();
                    $log['act_type'] = 'insert_combo_goods';
                    $log['product_id'] = $product_id;
                    $log['act_loginid'] = $_SESSION['admin']['login_name'];
                    $product->saveProductLog($log); // $db->insert("product_act_logs", $log);
                }

                //更新套餐产品
                foreach ($updateComboGoods as $kes => $vd) {
                    if ($v[0] == $vd[1]['combo_id']) {
                        $result = $db->get('combo_goods', '*', ['combo_goods_id' => $vd[0]]);
                        $result['promotion_price'] = money_int($result['promotion_price'], 2);
                        $r = $db->update('combo_goods', $vd[1], ['combo_goods_id' => $vd[0]]);
                        $vd[1]['promotion_price'] = money_int($vd[1]['promotion_price'], 2);

                        $msg = '更新套餐' . $n . '内产品';
                        if ($r) {
                            $sql = $db->last();
                            //仅成功的日志进入mysql日志
                            $log = [];
                            foreach ($vd[1] as $ks => $vas) {
                                if ($result[$ks] != $vas && !empty($data_name[$ks]))
                                    $msg .= "字段 {$data_name[$ks]} 旧值为{$result[$ks]}， 更新为 {$vas}  <br>";
                            }
                            $log['act_table'] = 'combo';
                            $log['act_sql'] = $sql;
                            $log['act_desc'] = $msg;
                            $log['act_time'] = time();
                            $log['act_type'] = 'insert_combo_goods';
                            $log['product_id'] = $product_id;
                            $log['act_loginid'] = $_SESSION['admin']['login_name'];
                            $product->saveProductLog($log); // $db->insert("product_act_logs", $log);
                        }
                    }
                }
            }
        }

        //用户保存产品图集上传成功的ID 用户保存产品的原图
        $productPhotoIDS = [];
        //判断是否上传图集
        if (isset($_POST['photos'])&&count($_POST['photos'])) {
            $photos = [];
            foreach ($_POST['photos'] as $key => $value) {
                $photos[$key]['thumb'] = qiniu::changImgDomain($value);
                $photos[$key]['add_time'] = date('Y-m-d H:i:s', time());
                $photos[$key]['product_id'] = $product_id;
            }

            //插入图集
            if (count($photos) > 0) {
                foreach ($photos as $key => $value) {
                    $ret_photo_id = $db->insert('product_thumb', $value);
                    $productPhotoIDS[] = $ret_photo_id;
                }
            }
        }

        //保存产品扩展google信息(chenhk)
        $gle_data['product_id'] = empty($product_id) ? $ret : $product_id;
        if ($gle_data['product_id']) {
            $gle_data['google_analytics_id'] = isset($_POST['google_analytics_id'])?trim($_POST['google_analytics_id']):'';      //google跟踪id
            $gle_data['google_conversion_id'] = isset($_POST['google_conversion_id'])?trim($_POST['google_conversion_id']):'';     //google转化id
            $gle_data['google_conversion_label'] = isset($_POST['google_conversion_label'])?trim($_POST['google_conversion_label']):'';  //google转化标签
            $gle_data['google_marketing_js'] = isset($_POST['google_marketing_js'])?trim($_POST['google_marketing_js']):'';      //google再营销js
            $gle_data['yahoo_id'] = isset($_POST['yahoo_id'])?trim($_POST['yahoo_id']):''; //雅虎ID
            $gle_data['create_date'] = time(); //时间
            $product->saveProductGoogleExt($product_id, $gle_data);
        }

        //保存产品的原图到数据库logo(1) thumbs(2) photos(3) attr(4) content(5)
        //使用产品ID和是否编辑字段进行编辑
        $addTime = date('Y-m-d H:i:s', time());
        if ($product_id && !$is_edit) {
            $oglData = [];
            //logo
            if (isset($_POST['originalLogoUrl']) && !empty(trim($_POST['originalLogoUrl']))) {
                $oglData[] = ['product_id' => $product_id, 'type' => 1, 'thumb' => $_POST['originalLogoUrl'], 'add_time' => $addTime];
            }
            //thumbs
            if (isset($_POST['originalThumbsUrl']) && !empty(trim($_POST['originalThumbsUrl']))) {
                $oglData[] = ['product_id' => $product_id, 'type' => 2, 'thumb' => $_POST['originalThumbsUrl'], 'add_time' => $addTime];
            }
            //photos
            if (isset($_POST['original_photos'])) {
                foreach ($productPhotoIDS as $key => $value) {
                    if ($value && !empty($_POST['original_photos'][$key])) {
                        $thumb = $_POST['original_photos'][$key];
                        $oglData[] = ['product_id' => $product_id, 'type' => 3, 'fg_id' => $value, 'thumb' => $thumb, 'add_time' => $addTime];
                    }
                }
            }
            //attr
            foreach ($productAttributeIDS as $key => $attribute_id) {
                $thumb = $_POST['original_attr_thumb'][$key];
                if (trim($thumb)) {
                    $oglData[] = ['product_id' => $product_id, 'type' => 4, 'fg_id' => $attribute_id, 'thumb' => $thumb, 'add_time' => $addTime];
                }
            }
            //combo
            foreach ($productComboIDS as $key => $combo_id) {
                $thumb = $_POST['originalCombo'][$key + 1]['thumb'];
                if (trim($thumb)) {
                    $oglData[] = ['product_id' => $product_id, 'type' => 5, 'fg_id' => $combo_id, 'thumb' => $thumb, 'add_time' => $addTime];
                }
            }

            //从ue编辑器里取出所有的img标签
            $imgElement = $save['content'];
            if (!empty($imgElement)) {
                preg_match_all('/<img src=\"([\s\S]*?)\/>/', $imgElement, $allImg); //取出所有img
                if ($allImg) {
                    foreach ($allImg[0] as $contentImg) {
                        preg_match_all('/<img src=\"([\s\S]*?)\"/', $contentImg, $matThumbs); //取出所有七牛图片
                        preg_match_all('/original_src=\"([\s\S]*?)\"/', $contentImg, $matOgiThumbs); //取出所有原图

                        $matOgiThumb = isset($matOgiThumbs[1][0])?$matOgiThumbs[1][0]:'';
                        $matThumb = $matThumbs[1][0];
                        if ($matThumb) {
                            if (!$matOgiThumb) {
                                $matOgiThumb = $matThumb;
                            }
                            $thumb = $matThumb . ',' . $matOgiThumb;
                            $oglData[] = ['product_id' => $product_id, 'type' => 6, 'thumb' => $thumb, 'add_time' => $addTime];
                        }
                    }
                }
            }

            // cover(视频封面)
            if (isset($_POST['originalCoverUrl']) && !empty(trim($_POST['originalCoverUrl']))) {
                $oglData[] = ['product_id' => $product_id, 'type' => 7, 'thumb' => $_POST['originalCoverUrl'], 'add_time' => $addTime];
            }

            if ($oglData) {
                $product->addProductOriginalImage($product_id, $oglData);
            }
        } else {
            // logo
            if (isset($_POST['originalLogoUrl']) && !empty(trim($_POST['originalLogoUrl']))) {
                $upData = ['thumb' => $_POST['originalLogoUrl'], 'add_time' => $addTime];
                $condition = ['product_id' => $product_id, 'type' => 1];
                $product->updateOrCreateProductOriginalImage($upData, $condition);
            }
            //thumbs
            if (isset($_POST['originalThumbsUrl']) && !empty(trim($_POST['originalThumbsUrl']))) {
                $upData = ['thumb' => $_POST['originalThumbsUrl'], 'add_time' => $addTime];
                $condition = ['product_id' => $product_id, 'type' => 2];
                $product->updateOrCreateProductOriginalImage($upData, $condition);
            }
            //cover(视频封面)
            if (isset($_POST['originalCoverUrl']) && !empty(trim($_POST['originalCoverUrl']))) {
                $upData = ['thumb' => $_POST['originalCoverUrl'], 'add_time' => $addTime];
                $condition = ['product_id' => $product_id, 'type' => 7];
                $product->updateOrCreateProductOriginalImage($upData, $condition);
            }
            //photos
            if (!empty($productPhotoIDS)) {
                $photoData = [];
                foreach ($productPhotoIDS as $key => $value) {
                    if ($value && !empty($_POST['original_photos'][$key])) {
                        $thumb = $_POST['original_photos'][$key];
                        $photoData[] = ['product_id' => $product_id, 'type' => 3, 'fg_id' => $value, 'thumb' => $thumb, 'add_time' => $addTime];
                    }
                }
                $product->saveProductOriginalImage($photoData);
            }
            //attr
            $product_attr_ids = $_POST['product_attr_id'];
            $product_attr_original_thumbs = $_POST['original_attr_thumb'];
            if (isset($_POST['original_attr_thumb'])) {
                foreach ($product_attr_ids as $key => $id) {
                    $thumb = $product_attr_original_thumbs[$key];
                    if ($thumb) {
                        $upData = ['fg_id' => $id, 'thumb' => $thumb, 'add_time' => $addTime];
                        $condition = ['product_id' => $product_id, 'type' => 4, 'fg_id' => $id];
                        $product->updateOrCreateProductOriginalImage($upData, $condition);
                    }
                }
            }

            //combo
            $combo_data = [];
            // combo新增套餐原图保存
            foreach ($productComboIDS as $key => $combo_id) {
                $thumb = $_POST['originalCombo'][$key + 1]['thumb'];
                if (trim($thumb)) {
                    $combo_data[] = ['product_id' => $product_id, 'type' => 5, 'fg_id' => $combo_id, 'thumb' => $thumb, 'add_time' => $addTime];
                }
            }
            if ($combo_data) {
                $ret = $db->insert('product_original_thumb', $combo_data);
            }
            // combo更新套餐原图
            $product_combos = isset($_POST['combo'])?$_POST['combo']:array();
            if (isset($_POST['originalCombo'])) {
                $product_combo_original_thumbs = $_POST['originalCombo'];
                foreach ($product_combos as $id => $item) {
                    $thumb = $product_combo_original_thumbs[$id]['thumb'];
                    if ($thumb && $id != 1) {
                        $upData = ['fg_id' => $id, 'thumb' => $thumb, 'add_time' => $addTime];
                        $condition = ['product_id' => $product_id, 'type' => 5, 'fg_id' => $id];
                        $product->updateOrCreateProductOriginalImage($upData, $condition);
                    }
                }
            }

            //content
            $imgElement = $save['content'];
            //先删除全部
            $db->delete('product_original_thumb', ['product_id' => $product_id, 'type' => 6]);
            $contentData = [];
            if (!empty($imgElement)) {
                preg_match_all('/<img src=\"([\s\S]*?)\/>/', $imgElement, $allImg); //取出所有img
                if ($allImg) {
                    foreach ($allImg[0] as $contentImg) {
                        preg_match_all('/<img src=\"([\s\S]*?)\"/', $contentImg, $matThumbs); //取出所有骑牛图片
                        preg_match_all('/original_src=\"([\s\S]*?)\"/', $contentImg, $matOgiThumbs); //取出所有原图

                        $matOgiThumb = isset($matOgiThumbs[1][0])?$matOgiThumbs[1][0]:'';
                        $matThumb = $matThumbs[1][0];
                        if ($matThumb) {
                            if (!$matOgiThumb) {
                                $matOgiThumb = $matThumb;
                            }
                            $thumb = $matThumb . ',' . $matOgiThumb;
                            $contentData[] = ['product_id' => $product_id, 'type' => 6, 'thumb' => $thumb, 'add_time' => $addTime];
                        }
                    }
                }
            }
            if ($contentData) {
                $product->saveProductOriginalImage($contentData);
            }
        }

        $new_erp = $db->get('product', 'new_erp', ['product_id' => $product_id]);
        $tags = isset($_POST['identity_tag']) ? trim($_POST['identity_tag']) : '';

        $res = $product->sendDomainToErp(trim($_POST['erp_number']), trim($_POST['domain']), $tags, $_POST['id_zone'], $product_id, $seo_loginid);
        if ($res['ret']) {
            $db->pdo->commit();
            $url = '/product_new.php?act=edit&product_id=' . $product_id;

            //保存埋点
            if(isset($_POST['md_ddn']))
            {

                $product->saveProductMd($product_id, array('product_id'=>$product_id, 'md'=>$_POST['md_ddn']));
            }
            //保存cloak
            if(isset($_POST['cloak_page_ddn']))
            {

                $product->saveProductCloak($product_id, array('product_id'=>$product_id, 'domain'=>trim($_POST['domain']), 'identity_tag'=>trim($_POST['identity_tag']), 'safepage'=>trim($_POST['cloak_page_ddn'])));
            }

            echo json_encode(['ret' => 1, 'msg' => '保存成功', 'url' => $url]);
//            if(!empty($_POST['identity_tag'])){
//                $key_product = 'PRO_'.$_POST['domain']."_".str_replace('/','',$_POST['identity_tag']);
//                $cache->del($key_product);
//            }
        } else {
            $db->pdo->rollBack();
            echo json_encode(['ret' => 0, 'msg' => $res['desc']]);
        }
    } catch (\Exception $e) {
        $db->pdo->rollBack();
        echo json_encode(['ret' => 0, 'msg' => "保存产品处理出现错误:" . $e->getMessage()]);
        exit;
    }
} elseif(isset($_GET['act']) && $_GET['act'] == 'googleSave'){
    $data = $_POST;
    $product_id = $data['product_id'];
    $map=['product_id'=>$product_id];
    unset($data['product_id'],$data['google_analytics_id']);
    $db->update('product',$data,$map);

    //保存产品扩展google表(ChenHongKai - 20171017)
    $product    = new \admin\helper\product($register);
    $gle_data['product_id'] = empty($product_id) ? 0:$product_id;
    if ($gle_data['product_id']) {
        $gle_data['google_analytics_id']        = trim($_POST['google_analytics_id']);      //google跟踪id
        $gle_data['google_conversion_id']       = trim($_POST['google_conversion_id']);     //google转化id
        $gle_data['google_conversion_label']    = trim($_POST['google_conversion_label']);  //google转化标签
        $gle_data['google_marketing_js']        = trim($_POST['google_marketing_js']);      //google再营销js
        $gle_data['create_date']                = time();                                   //时间
        $product->saveProductGoogleExt($product_id, $gle_data);
    }

    $url = '/product.php?act=edit&product_id='.$product_id;
    echo json_encode(['ret'=>1,'msg'=>'保存成功','url'=>$url]);
}
elseif (isset($_GET['act']) && $_GET['act'] == 'add') {
    $data['admin'] = $_SESSION['admin'];
    $register->get('view')->display('/product/product.twig', $data);
}
elseif (isset($_POST['act']) && $_POST['act'] == 'delete') {

    $product_attr_id = $_POST['product_attr_id'];
    $product         = new \admin\helper\product($register);
    $ret             = $product->deleteAttr($product_attr_id);
    echo json_encode($ret);
}
// 产品删除
elseif (isset($_POST['act']) && $_POST['act'] == 'del') {
    $product_id     = $_POST['product_id'];
    $product        = new \admin\helper\product($register);
    $data['is_del'] = $_POST['is_del'];

    $ret = $product->delProduct($product_id, $data);

    echo json_encode($ret);
}
elseif (isset($_POST['act']) && $_POST['act'] == 'departmentChange') {
    $domain        = $_POST['domain'];
    $id_department = $_POST['id_department'];
    $product       = new \admin\helper\product($register);
    $ret           = $product->departmentChange($domain, $id_department);
    echo json_encode($ret);
}
elseif (isset($_POST['act']) && $_POST['act'] == 'productDepartChange') {
    $productIDs    = isset($_POST['products'])?$_POST['products']:null;
    $id_department = isset($_POST['id_department'])?$_POST['id_department']:null;
    $product       = new \admin\helper\product($register);
    $ret           = $product->updateProductDepartment($productIDs, $id_department);
    echo json_encode($ret);
}
elseif (isset($_POST['act']) && $_POST['act'] == 'deleteCombo') {
    $combo_id = $_POST['combo_id'];
    $product  = new \admin\helper\combo($register);
    $ret      = $product->delCombo($combo_id);
    echo json_encode($ret);
}
//删除套餐产品
elseif (isset($_POST['act']) && $_POST['act'] == 'deleteComboGoods') {
    $id = $_POST['combo_goods_id'];
    $product  = new \admin\helper\combo($register);
    $ret      = $product->delComboGoods($id);
    echo json_encode($ret);
}
//mike：导出产品id和域名
elseif (isset($_GET['act']) && $_GET['act'] == 'downloadid') {
    $data = $db->select('product', '*', ["ORDER" => ['product_id' => "DESC"], 'AND' => ['is_del' => 0]]);
    Header("Content-type:   application/octet-stream ");
    Header("Accept-Ranges:   bytes ");
    Header("Content-type:application/vnd.ms-excel ");
    Header("Content-Disposition:attachment;filename=产品ID和域名.xls ");

    echo "<table  border='1'>";
    //var_dump($data);die;
    //domain,identity_tag,title,erp_number,fb_px,
    echo "<tr align='center'>";
    echo " <td width='200'>产品ID</td>";
    echo " <td width='200'>建站域名</td>";
    echo "</tr>";
    foreach ($data as $k => $v) {
        echo "<tr align='center'>";
        echo "<td width='200'>" . $v['product_id'] . "</td>";
        echo "<td width='200'>http://" . $v['domain'] . "/" . $v['identity_tag'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    
    exit;
}
elseif (isset($_POST['act']) && $_POST['act'] == 'check') {
    $domain = $_POST['domain'];
    //获取域名信息
    $product = new \admin\helper\product($register);
    $ret = $product->getErpDomainInfo($domain);

    if (!$ret['status']) {
        echo json_encode(['ret' => 0, 'msg' => "没找到该域名信息，请确认。"]);
        exit;
    }

    //获取产品分类目录
    $category = new \admin\helper\category($register);
    //查询改产品的分类
    $modules = $category->getCategoryParent($domain);
    if($modules)
    {
        foreach ($modules as $r) {
            $r['spa']  = '&emsp;&emsp;';
            $array[]   = $r;
        }
        $str = "<option value='\$id'>\$spacer \$title \$spa 中文名:\$title_zh</option>";
        $T   = new \lib\tree();
        $T->init($array);
        $module = $T->getTree(0, $str);
    }

    echo json_encode(['ret' => 1, 'data' => $ret['data'], 'module' => $module]);
}
elseif (isset($_POST['act']) && $_POST['act'] == 'getErpProduct') {
    /*$product_id = $_POST['product_id'];
    $erp_number = $_POST['number'];
    $product    = new \admin\helper\product($register);
    ##jade add
    // $company = new \admin\helper\company($register);  ##后台配置之后才能用
    // $objname = $company->getErpProductObjname();
    // $objname = '';
    $objname = 'admin\helper\api\erpproduct';


    $users    = new \admin\helper\oa_users($register);
    // $loginid = $_SESSION['admin']['login_name'];
    $loginid = $users->getUsernameByNameCn(trim($_POST['ad_member']));//使用优化师的loginid
    $params = ['company_id'=>$_SESSION['admin']['company_id'],'loginid'=>$loginid,'id'=>$erp_number];
    $obj = new $objname();
    $ret = $obj->getProduct($params);
    // print_r($ret);exit;
    if(is_array($ret['product']['productZoneNames']) && (count($ret['product']['productZoneNames']) >0) ) {
        $ret['product']['productZoneList'] = $product->getZoneList($ret['product']['productZoneNames']);
        $ret['product']['productAvailableZoneIds'] = implode(array_column($ret['product']['productZoneList'], 'id_zone'), ',');
        unset($ret['product']['productZoneNames']);
    }


    if (!$ret['status'] || !$ret['product']) {
        $msg = empty($ret['message'])?'':$ret['message'];
        echo json_encode(['ret' => 0, 'msg' => "没找到该产品信息:".$msg]);
        exit;
    }

    $ddnp = var_export($ret['product'], true);
    var_dump($ddnp);*/
    $ret = erpInfo();

    echo json_encode(['ret' => 1, 'data' => $ret]);
}
elseif (isset($_POST['act']) && $_POST['act'] == 'deletePhotos') {
    $thumb_id = $_POST['thumb_id'];

    $map = ['thumb_id' => $thumb_id];
    $productId = $db->get('product_thumb', 'product_id', $map);
    $ret = $db->delete('product_thumb', $map);
    if ($ret) {
        $condition = ['product_id' => $productId, 'type' => 3, 'fg_id' => $thumb_id];
        $db->delete('product_original_thumb', $condition);
        echo json_encode(['ret' => 1, 'msg' => "OK"]);
        exit;
    }

    echo json_encode(['ret' => 0, 'error' => "删除失败。"]);
}
elseif(isset($_POST['act']) && $_POST['act'] == 'delAttrThumb'){
    $id = $_POST['product_attr_id'];

    $result = [];
    if ($id) {
        $db->update('product_attr', ['thumb' => ''], ['product_attr_id' => $id]);

        //删除属性原图
        $productId = $db->get('product_attr', 'product_id', ['product_attr_id' => $id]);
        $condition = ['product_id'=>$productId, 'type'=> 4, 'fg_id'=>$id];
        $db->delete('product_original_thumb', $condition);

        $result = ['ret' => 1, 'error' => "删除成功"];
    } else {
        $result = ['ret' => 1, 'error' => "属性ID为空"];
    }

    echo json_encode($result);
}

// 保存产品前,检查优化师和套餐中的产品优化师是否是同一个人
// 如果不是,判断是否同一个产品ID。如果产品ID相同可以保存
elseif (isset($_GET['act']) && $_GET['act'] == 'checkProductUserID') {

    if (!empty($_POST['combo'])) {
        //产品优化师
        $users_id  = $_POST['users'];
        $productID = isset($_POST['product_id']) ? $_POST['product_id'] : 0;

        $users = new \admin\helper\oa_users($register);
        $u_filter['uid'] = $users_id;
        $u_filter['id_department'] = $_SESSION['admin']['id_department'];
        $usersInfo = $users->getUserNameWithID($u_filter);
        if (empty($usersInfo)) {
            echo json_encode(['ret'=>0]);
            exit;
        }
        //优化师名称
        $user_name = $usersInfo['name_cn'];

        //套餐产品标题、id
        $combo_titles  = [];
        $product_ids   = [];
        //套餐产品确认标记 mark 判断套装中的所有产品和当前产品是否一致 1:一致 0:不一致
        $mark = 1;

        foreach ($_POST['combo'] as $item) {
            foreach ($item['product_id'] as $product_id) {
                if (!in_array($product_id, $product_ids)) {
                    $combo_titles[] = $item['name'];
                    $product_ids[] = $product_id;
                }
                //只要存在产品ID不一致的打标记
                if ($mark != 0 && $product_id != $productID && $productID != 0) {
                    $mark = 0;
                }
            }
        }

        //编辑过程套餐产品ID和当前产品ID一致返回
        if ($mark == 1) {
            echo json_encode(['ret'=>0]);
            exit();
        }

        //查询套餐中的产品优化师
        $filter['product_id'] = $product_ids;
        $product = new \admin\helper\product($register);
        $data = $product->getProductInfo($filter);

        //获取优化师数组
        $dataName  = array_column($data, 'ad_member', 'product_id');
        $dataTitle = array_column($data, 'title', 'product_id');

        //封装数组
        $combo_product = [];
        foreach ($product_ids as $key => $product_id) {
            //如果产品ID不相同,再判断产品的优化师是否一致
            if ($dataName[$product_id] != $user_name) {
                $temp = array(
                    'title'         => $combo_titles[$key],
                    'product_title' => $dataTitle[$product_id],
                    'name_cn'       => $dataName[$product_id]
                );

                $combo_product[] = $temp;
            }
        }

        //判断是否存在不同的优化师产品
        if (empty($combo_product)) {
            echo json_encode(['ret'=>0]);
        } else {
            echo json_encode(['ret'=>1, 'data'=>$combo_product]);
        }
    } else {
        echo json_encode(['ret'=>0]);
    }
}
elseif (isset($_GET['act']) && $_GET['act'] == 'publicProduct') {
    if(isset($_GET['type']) && isset($_GET['keyword'])){
        $value = trim($_GET['keyword']);
        if ($_GET['type'] == 'title' && !empty($value)) {
            $filter['product.title[~]'] = ['like', '%' . $value . '%'];
            $filterWeb['title[~]'] = ['like', '%' . $value . '%'];
        }
        if ($_GET['type'] == 'erpid' && !empty($value)) {
            $filter['product.erp_number'] = $value;
            $filterWeb['erp_number'] = $value;
        }
        if ($_GET['type'] == 'domain' && !empty($value)) {
            $filter['product.domain'] = $value;
            $filterWeb['domain'] = $value;
        }
    }
    if(isset($_GET['id_zone'])){
        $filter['product.id_zone'] = $_GET['id_zone'];
        $filterWeb['id_zone'] = $_GET['id_zone'];
    }
    if (isset($_GET['userid'])) {
        $filter['product.userid'] = $_GET['userid'];
        $filterWeb['userid'] = $_GET['userid'];
    }

    $product           = new \admin\helper\product($register);
    $filter['product.is_del']  = 0;
    $filterWeb['is_del']  = 0;

    $data              = $product->getAllProduct($filter, 10);
    $filter['product.keyword'] = isset($value) ? $value : '';
    $filter['product.type'] = isset($_GET['type']) ? $_GET['type'] : 'title';
    $filterWeb['keyword'] = isset($value) ? $value : '';
    $filterWeb['type'] = isset($_GET['type']) ? $_GET['type'] : 'title';
    $data['filter']    = $filterWeb;

    if($filter['product.id_zone'])
    {
        $data['id_zone'] =  $filter['product.id_zone'];
    }

    if ($filter['product.userid']) {
        $data['userid'] =  $filter['product.userid']; 
    }


    if (isset($_GET['self']) && $_GET['self'] == 1) {
        $register->get('view')->display('/product/public_product_self.twig', $data);
    }
    else {
        $register->get('view')->display('/product/public_product.twig', $data);
    }
}

elseif (isset($_GET['act']) && $_GET['act'] == 'showDiffProductUserName')
{
    $data = json_decode($_GET['diffData'], true);
    $register->get('view')->display('/product/product_combo_user_diff.twig', $data);
}

elseif (isset($_GET['act']) && $_GET['act'] == 'publicProduct2') {
    if ($_POST) {
        $value = trim($_POST['keyword']);
        if ($_POST['type'] == 'title') {
            $filter['product.title[~]'] = ['like', '%' . $value . '%'];
        }
        else {
            $filter['product.erp_number'] = $value;
        }
    }
    $product           = new \admin\helper\product($register);
    $filter['product.is_del']  = 0;
    $data              = $product->getAllProduct($filter, 10);
    $filter['product.keyword'] = $value;
    $data['filter']    = $filter;
    echo json_encode($data);
    // if (isset($_GET['self']) && $_GET['self'] == 1) {
    //     $register->get('view')->display('/product/public_product_self.twig', $data);
    // }
    // else {
    //     $register->get('view')->display('/product/public_product.twig', $data);
    // }
}
else if (isset($_POST['act']) && $_POST['act'] == 'seo') { //fixme:需要删除的

    //获取域名判断是否为空￥
    $domain = $_POST['domain'];
    if (!$domain) {
        echo json_encode(array('ret'=>0, 'msg'=>'请求域名为空'));
        exit;
    }

    //erp信息
   // $company = new \admin\helper\company($register);
   // $companyInfo  = $company->getInfo();
   // $uri = $companyInfo['domain_erp'];
    // $uri         = 'http://erp.stosz.com:9090/Domain/Api/get_all?name=' . $_POST['domain'];
    // if(environment=='office')
    // {
    //     $uri        = 'http://192.168.109.252:8081/Domain/Api/get_all?name=' . $_POST['domain'];
    // }
    // $info        = file_get_contents($uri);
    // $info        = json_decode($info, true);
    ##jade add
    // $company = new \admin\helper\company($register);  ##后台配置之后才能用
    // $objname = $company->getErpSeoObjname();
    /*
    $objname = '';
    if(!$objname) {
        $uri         = 'http://erp.stosz.com:9090/Domain/Api/get_all?name=' . $_POST['domain'];
        if(environment=='office')
        {
            $uri        = 'http://192.168.109.252:8081/Domain/Api/get_all?name=' . $_POST['domain'];
        }
        $info        = file_get_contents($uri);
        $info        = json_decode($info, true);
    }else{
        $params = ['name'=>$_POST['domain']];
        $obj = new $objname();
        $info = $obj->getSeo($params);
    }
    $obj  = '';
    $users = $info['data']['users'];
    if ($users) {
        echo json_encode(array('ret'=>1, 'msg'=>'查询成功', 'data'=>$info['data']['users']));
    } else {*/
        echo json_encode(array('ret'=>0, 'msg'=>'查询优化师加载失败'));
    //}
}
elseif(isset($_POST['act']) && $_POST['act'] == 'copy')
{
    //复制一条产品
    $product = new \admin\helper\product($register);

    $data['id']             = $_POST['product_id'];
    $data['id_zone']        = $_POST['id_zone'];
    $data['identity_tag']   = $_POST['identity_tag'];
    $data['domain']         = $_POST['domain'];
    $data['lang']           = $_POST['lang'];
    $data['theme']          = $_POST['theme'];
    $data['zone']           = $_POST['zone'];

    $preg = '/^[a-zA-Z0-9]{1,}$/';
    if(empty($data['domain'])) {
        echo json_encode(['ret'=>0,'msg'=>"域名不能为空"]);
    } elseif (empty($data['identity_tag'])) {
        echo json_encode(['ret'=>0,'msg'=>"二级域名不能为空"]);
    } elseif (empty($data['id_zone'])) {
        echo json_encode(['ret'=>0,'msg'=>"地区不能为空"]);
    } elseif (!preg_match($preg,$data['identity_tag'])) {
        echo json_encode(['ret'=>0,'msg'=>"二级目录只能是字母或者数字或字母数字的组合"]);
    } elseif (empty($data['id'])) {
        echo json_encode(['ret'=>0,'msg'=>"产品ID不能为空"]);
    } elseif (empty($data['lang'])) {
        echo json_encode(['ret'=>0,'msg'=>"产品语言不能为空"]);
    } elseif (empty($data['theme'])) {
        echo json_encode(['ret'=>0,'msg'=>"产品模板不能为空"]);
    } elseif (empty($data['zone'])) {
        echo json_encode(['ret'=>0,'msg'=>"产品地区(英文)不能为空"]);
    }  else {
        // 检测模板匹配
        $res = $product->getThemesByIdzonAndLanguage($data['zone'], $data['lang'], $_SESSION['admin']['id_department']);
        if ($res) {
            $needle = false;
            foreach ($res as $key => $value) {
                if ( $data['theme'] == $value['theme']) {
                    $needle = true; 
                    break;
                }
            }
            if (!$needle) {
                echo json_encode(['ret'=>0,'msg'=>"输入的模板和根据输入的地区和语言查到的模板不匹配, 请检查参数"]);exit;
            }
        }else{
            echo json_encode(['ret'=>0,'msg'=>"根据输入的地区和语言查到的模板为空, 请检查参数"]);exit;
        }

        // 开始复制 
        unset($data['zone']);
        $ret = $product->productCopyWithOriginImage($data, $register);
        unset($ret['product']);
        echo json_encode($ret);
    }
}
elseif(isset($_POST['act']) && $_POST['act'] == 'getThemesByIdzonAndLanguage'){
    $zone = isset($_POST['zone']) ? $_POST['zone'] : 0;
    $lang = isset($_POST['lang']) ? $_POST['lang'] : 0;
    if (empty($zone) || empty($lang)) {
        echo json_encode(['res'=>'0','msg'=>'参数缺失']);exit;
    }
    $product = new \admin\helper\product($register);
    // var_dump($_SESSION);exit;
    $ret = $product->getThemesByIdzonAndLanguage($zone, $lang, $_SESSION['admin']['id_department']);

    if ($ret == false) {
        echo json_encode(['ret'=>0, 'msg'=>'error params']);exit;
    } else {
        echo json_encode(['ret'=>1, 'msg'=>'success', 'list'=>$ret], true);
    }
}
elseif(isset($_POST['act']) && $_POST['act'] == 'reloadProduct'){

    $user = new \admin\helper\user($register);
    $ret = $user->updateUserProduct();
    echo json_encode($ret);

}
elseif(isset($_POST['act']) && $_POST['act'] == 'supportOcean'){
    $domain = $_POST['domain'];
    $pay = new \admin\helper\payment($register);
    $support = $pay->supportOcean($domain);
    echo json_encode(['ret'=>$support]);
}
elseif(isset($_GET['act']) && $_GET['act'] == 'getSeoDomain'){
    $c = new \admin\helper\product($register);
    $r = [];
    if((empty($_GET['loginid']) && empty($_GET['ad_member'])) || empty($_GET['id_department'])){
        
    }else{
        if($_GET['loginid']){
            $loginid = $_GET['loginid'];
        }else{
            $loginid = $db->get('oa_users','username',['name_cn'=>$_GET['ad_member']]);    
        }
        if($loginid){
            //jimmmy fix 如果领导则多个部门id就不使用get的id_department
            $company = new \admin\helper\company($register);
            $id_departmentArray = $company->get_id_departments();
            $id_department = implode(',',$id_departmentArray);

            $params = [];
            $params['loginid'] = $loginid;
            $params['id_department'] = $id_department;

            //$r = $c->getSeoDomain($params);
            $r = ddnDomain();

            if(empty($r) || empty($r['ret'])){
                $r = [];
            }else{
                unset($r['ret']);

                if (isset($_GET['key']) && $_GET['key'] == 'site') {
                    //查询已经用到的域名
                    $siteModel = new \admin\helper\site($register);
                    $condition['oa_id_department'] = $id_departmentArray;
                    $sites = $siteModel->getAllSites($condition);
                    //用于保存没有使用到的数据
                    $temp_array = array();
                    if (!empty($sites['goodsList'])) {
                        $sitesArray = array_column($sites['goodsList'], 'domain');
                        //遍历域名数组 查找哪些域名被使用过
                        foreach ($r as $key => $value) {
                            $temp_domain = 'www.'.$value['domain'];
                            if (!in_array(strtolower($temp_domain), $sitesArray)) {
                                $temp_array[] = $value;
                            }
                        }
                        //重新赋给数组新的值
                        $r = $temp_array;
                    }
                }
            }
        }
    }
    
    //获取产品分类目录
    $category = new \admin\helper\category($register);
    //查询改产品的分类
    $modules = $category->getCategoryParent((isset($domain)?$domain:''));
    if($modules)
    {
        foreach ($modules as $r) {
            $r['spa']  = '&emsp;&emsp;';
            $array[]   = $r;
        }
        $str = "<option value='\$id'>\$spacer \$title \$spa 中文名:\$title_zh</option>";
        $T   = new \lib\tree();
        $T->init($array);
        $module = $T->getTree(0, $str);
    }
    echo json_encode(['ret'=>$r,'module'=>(isset($module)?$module:'')]);
}
elseif(isset($_GET['act']) && $_GET['act'] == 'checkdomain'){
    $c = new \admin\helper\product($register);
    $r = $c->checkDomain($_GET['domain']);
    //获取产品分类目录
    $category = new \admin\helper\category($register);
    //查询改产品的分类
    $modules = $category->getCategoryParent($domain);
    if($modules)
    {
        foreach ($modules as $r) {
            // $r['spa']  = '&emsp;&emsp;';
            $array[]   = $r;
        }
        $str = "<option value='\$id'>\$spacer \$title \$spa 中文名:\$title_zh</option>";
        $T   = new \lib\tree();
        $T->init($array);
        $module = $T->getTree(0, $str);
    }
    //echo json_encode(['ret'=>$r,'module'=>$module]);
    ajaxReturn(['ret'=>$r,'module'=>$module]);
}
elseif(isset($_GET['act']) && $_GET['act'] == 'getAdUser'){
    $c = new \admin\helper\company($register);
    // var_dump($_SESSION['admin']['uid']);exit();
    $ret = $c->getAdUser($_SESSION['admin']['uid']);
    echo json_encode(['ret'=>$ret]);
}
elseif(isset($_GET['act']) && $_GET['act'] == 'getAUser'){
    $c = new \admin\helper\company($register);
    $ret = $c->getAUser($_SESSION['admin']['uid']);
    echo json_encode(['ret'=>$ret]);
}
elseif(isset($_GET['act']) && $_GET['act'] == 'getAllZone'){
    $c                 = new \admin\helper\country($register);
    $ret  = $c->getAllZone();
    echo json_encode(['ret'=>$ret]);
}
elseif(isset($_GET['act']) && $_GET['act'] == 'getDomainIdDepartment'){
    $p                 = new \admin\helper\product($register);
    $ret  = $p->getDomainIdDepartment($domain);
    echo json_encode(['ret'=>$ret]);
}
elseif(isset($_GET['act']) && $_GET['act'] == 'saveBILink'){
   
    if(empty($_REQUEST['product_id']) || empty($_REQUEST['ad_new_channel']) || empty($_REQUEST['ad_channel'])  || empty($_REQUEST['ad_media'])  ||  empty($_REQUEST['ad_id_department']) || empty($_REQUEST['ad_member']) ){
        //|| empty($_REQUEST['ad_loginname'])
        echo json_encode(['res'=>'fail','msg'=>'参数缺失']);exit();
    }

    // 判断优化师是否离职
    $p = new \admin\helper\product($register);
    $productInfos = $p->getProductByProductID($_REQUEST['product_id']);

    $users    = new \admin\helper\oa_users($register);
    $userInfo = $users->getUsernameByNameCn(trim($productInfos['ad_member']), true);//使用优化师的loginid
    if (empty($userInfo['password'])) {
        echo json_encode(['res'=>'fail','msg'=>'站点所属优化师已经离职，请变改优化师！']);exit();
    }

    // 判断优化师是否已经转部门
    if ($userInfo['id_department'] != $productInfos['oa_id_department']) {
        echo json_encode(['res'=>'fail','msg'=>'站点所属优化师不在该部门，请变更优化师！']);exit();
    }

    $adBiLink = $_REQUEST['ad_bilink'];
    $adBilinkArray = parse_url($adBiLink);
    // 指示器
    $needle = false;
    if (!empty($adBilinkArray['query'])) {
        parse_str($adBilinkArray['query'], $output);
        if (isset($output['utm_campaign'])) {
            $param = ['utm_campaign' => $output['utm_campaign']];
            $adBilinkArray['query'] = http_build_query($param);
            $newAdBiLink = http_build_url($adBilinkArray);
            $_REQUEST['ad_bilink'] = $newAdBiLink;
            $needle = true;
         }
    }
    if ($needle) {
        $res  = $p->saveBILink($_REQUEST);
        echo json_encode($res);exit();
    } else {
        echo json_encode(['res'=>'fail','msg'=>'url中的params为空']);exit();
    }
}
elseif(isset($_GET['act']) && $_GET['act'] == 'getBILink'){
    $data = [];
    // if(empty($_REQUEST['product_id']) ){
    //     echo json_encode(['res'=>'fail','msg'=>'参数缺失']);exit();
    // }
    // $data['product_id'] = $_REQUEST['product_id'];
    if(!empty($_REQUEST['product_id'])){
        $data['product_id'] = $_REQUEST['product_id'];
    }
    $p                 = new \admin\helper\product($register);
    if(!empty($_REQUEST['ad_channel'])){
        $data['ad_channel[~]'] = $_REQUEST['ad_channel'];
    }
    if(!empty($_REQUEST['ad_new_channel'])){
        $data['ad_new_channel[~]'] = $_REQUEST['ad_new_channel'];
    }
//    if(!empty($_REQUEST['ad_group'])){
//        $data['ad_group[~]'] = $_REQUEST['ad_group'];
//    }
    if(!empty($_REQUEST['ad_series'])){
        $data['ad_series[~]'] = $_REQUEST['ad_series'];
    }
    if(!empty($_REQUEST['ad_name'])){
        $data['ad_name[~]'] = $_REQUEST['ad_name'];
    }
    if(!empty($_REQUEST['id'])){
        $data['id'] = $_REQUEST['id'];
    }
    if(!empty($_REQUEST['ad_id_department'])){
        $data['ad_id_department'] = $_REQUEST['ad_id_department'];
    }
    if(!empty($_REQUEST['ad_loginname'])){
        $data['ad_loginname'] = $_REQUEST['ad_loginname'];
    }
    if(!empty($_REQUEST['loginid'])){
        $data['loginid'] = $_REQUEST['loginid'];
    }
    $_GET['p'] = empty($_GET['p'])?1:(int)$_GET['p'];
    $_GET['p'] = ($_GET['p']>0)?$_GET['p']:1;
    $res  = $p->getBILink($data);
    echo json_encode($res);exit();
}
elseif(isset($_GET['act']) && $_GET['act'] == 'getProductExtData'){
    if(empty($_REQUEST['product_id']) ){
        echo json_encode(['res'=>'fail','msg'=>'参数缺失']);exit();
    }
    $p                 = new \admin\helper\product($register);
    $res  = $p->getProductExtData($_REQUEST);
    echo json_encode($res);exit();
}
elseif(isset($_GET['act']) && $_GET['act'] == 'getAllDepartment'){
    if(!$_SESSION['admin']['is_admin'] && !$_SESSION['admin']['is_root'] ){
        echo json_encode(['ret'=>0,'msg'=>'权限不足']);exit();
    }
    $p                 = new \admin\helper\product($register);
    $res  = $p->getAllDepartment();
    echo json_encode(['ret'=>1,'departments'=>$res]);exit();
}
elseif(isset($_GET['act']) && $_GET['act'] == 'checkoutToNewDepartment'){
    if(!$_SESSION['admin']['is_admin'] && !$_SESSION['admin']['is_root'] ){
        echo json_encode(['ret'=>0,'msg'=>'权限不足']);exit();
    }
    if(empty($_REQUEST['list']) || empty($_REQUEST['oa_id_department']) ){
        echo json_encode(['ret'=>0,'msg'=>'参数缺失']);exit();
    }
    $p                 = new \admin\helper\product($register);
    $res  = $p->checkoutToNewDepartment($_REQUEST);
    echo json_encode($res);exit();
}
elseif(isset($_GET['act']) && $_GET['act'] == 'changeNewDepartment'){
    if(!$_SESSION['admin']['is_admin'] && !$_SESSION['admin']['is_root'] ){
        echo json_encode(['ret'=>0,'msg'=>'权限不足']);exit();
    }
    // if(empty($_REQUEST['domain']) || empty($_REQUEST['oa_id_department']) || empty($_REQUEST['uid']) ){
    //     echo json_encode(['ret'=>0,'msg'=>'参数缺失']);exit();
    // }
    if(empty($_REQUEST['list']) || empty($_REQUEST['oa_id_department']) ){
        echo json_encode(['ret'=>0,'msg'=>'参数缺失']);exit();
    }
    $p                 = new \admin\helper\product($register);
    $res  = $p->changeNewDepartment($_REQUEST);
    echo json_encode($res);exit();
}
elseif (isset($_GET['act']) && $_GET['act'] == 'publicProductAttr') {
    if(empty($_GET['product_id'])){
        exit('');
    }
    $data = [];
    $product           = new \admin\helper\product($register);
    $attr_id_desc = empty($_GET['attr_id_desc'])?'':$_GET['attr_id_desc'];
    $data['product_attr'] = $product->getComboProductAttr($_GET['product_id'],$attr_id_desc);
    $register->get('view')->display('/product/public_product_attr.twig', $data);
}
elseif (isset($_GET['act']) && $_GET['act'] == 'getRelComboByAttr') {
    if(empty($_REQUEST['product_attr_id']) || empty($_REQUEST['product_id']) ){
        exit('');
    }
    $data = [];
    $product           = new \admin\helper\product($register);
    $data['combo'] = $product->getRelComboByAttr($_REQUEST['product_id'],$_REQUEST['product_attr_id']);
    if( isset($_GET['json']) && $_GET['json']=='1' ){
        echo json_encode($data);exit();
    }else{
        $register->get('view')->display('/product/public_combo.twig', $data);
    }
}
elseif (isset($_GET['act']) && $_GET['act'] == 'getRelComboByPid') {
    if(empty($_GET['product_id'])){
        exit('');
    }
    $data = [];
    $product           = new \admin\helper\product($register);
    $data['combo'] = $product->getRelComboByPid($_REQUEST['product_id']);
    if( isset($_GET['json']) && $_GET['json']=='1' ){
        echo json_encode($data);exit();
    }else{
        $register->get('view')->display('/product/public_combo.twig', $data);
    }
}elseif(isset($_POST['act']) && $_POST['act'] == 'getTreeCat'){
    $domain = I('post.domain');

    $cat = new \admin\helper\category($register);
    $ret = $cat->getTreeCat(0,$domain);

    echo $ret;
}
else {
    $filter = [];
    $info   = [];
    if (!empty($_GET['title'])) {
        $filter['product.title[~]'] = ["%" . $_GET['title'] . '%'];
        $info['title']      = $_GET['title'];
    }
    if (!empty($_GET['domain'])) {
        $domain  = $_GET['domain'];
        if(strpos($domain,'http')!==false)
        {
            $domain = trim(str_replace('http://',"",$domain));
        }
        if(strpos($domain,'/')!==false)
        {
            list($mapDomain,$tag) = explode('/',$domain);
            $filter['product.identity_tag'] = trim($tag);
        }
        else{
            $mapDomain = $domain;
        }
        $filter['product.domain[~]'] = ["%" . $mapDomain . '%'];
        $info['domain']      = $domain;
    }
    if (!empty($_GET['theme'])) {
        $filter['product.theme[~]'] = ["%" . $_GET['theme'] . '%'];
        $info['theme']      = $_GET['theme'];
    }
    if (!empty($_GET['erp_id']) ) {
        $filter['product.erp_number'] = $_GET['erp_id'];
        $info['erp_id']       = $_GET['erp_id'];
    }


    if (!empty($_GET['product_id'])){
        $filter['product.product_id'] = $_GET['product_id'];
        $info['product_id']       = $_GET['product_id'];

        // 加上离职转部门判断
        // 判断优化师是否离职
        $p = new \admin\helper\product($register);
        $productInfos = $p->getProductByProductID($_GET['product_id']);

        $users    = new \admin\helper\oa_users($register);
        $userInfo = $users->getUsernameByNameCn(trim($productInfos['ad_member']), true);//使用优化师的loginid
        if (empty($userInfo['password'])) {
            echo json_encode(['res'=>'fail','msg'=>'站点所属优化师已经离职，请变改优化师！']);exit();
        }

        // 判断优化师是否已经转部门
        if ($userInfo['id_department'] != $productInfos['oa_id_department']) {
            echo json_encode(['res'=>'fail','msg'=>'站点所属优化师不在该部门，请变更优化师！']);exit();
        }


    }

    if (!empty($_GET['is_del'])) {
        if ($_GET['is_del'] != -1) {
            if($_GET['is_del'] == 1){
                $filter['product.is_del'] = [$_GET['is_del'],10];
            }else{
                $filter['product.is_del'] = $_GET['is_del'];
            }
        }
        $info['is_del'] = $_GET['is_del'];
    }
    else {
        $info['is_del']   = $filter['product.is_del'] = 0;
    }

    if(!empty($_GET['aid']) ) $filter['product.aid'] = trim($_GET['aid']);//建站人员
    if(!empty($_GET['ad_member_id'])) $filter['product.ad_member_id'] = trim($_GET['ad_member_id']);//优化师
    if(!empty($_GET['id_zone'])) $filter['product.id_zone'] = trim($_GET['id_zone']);//投放地区

    $product        = new \admin\helper\product($register);
    $data           = $product->getAllProduct($filter);
    $data['admin']  = $_SESSION['admin'];
    $data['filter'] = $info;
    
    if( isset($_GET['json']) && $_GET['json']=='1' ){
        $data['res'] = 'succ';
        echo json_encode($data);
    }else{
        $register->get('view')->display('/product/product_list.twig', $data);
    }

}


function http_build_url($url_arr){
    $new_url = $url_arr['scheme'] . "://".$url_arr['host'];
    if(!empty($url_arr['port']))
        $new_url = $new_url.":".$url_arr['port'];
    $new_url = $new_url . $url_arr['path'];
    if(!empty($url_arr['query']))
        $new_url = $new_url . "?" . $url_arr['query'];
    if(!empty($url_arr['fragment']))
        $new_url = $new_url . "#" . $url_arr['fragment'];
    return $new_url;
}


function ddnDomain()
{

    $domains = array (
  0 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'avoid5.com',
    'dead_time' => '2019-07-29',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '周龙',
    'loginid' => 'zhoulong',
  ),
  1 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'avoid4.com',
    'dead_time' => '2019-07-29',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '周龙',
    'loginid' => 'zhoulong',
  ),
  2 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'avoid3.com',
    'dead_time' => '2019-07-29',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '周龙',
    'loginid' => 'zhoulong',
  ),
  3 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'avoid2.com',
    'dead_time' => '2019-07-29',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '周龙',
    'loginid' => 'zhoulong',
  ),
  4 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'avoid1.com',
    'dead_time' => '2019-07-29',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '周龙',
    'loginid' => 'zhoulong',
  ),
  5 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'avoid0.com',
    'dead_time' => '2019-07-29',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '陈伟',
    'loginid' => 'chenwei',
  ),
  6 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'agree3.com',
    'dead_time' => '2019-07-29',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '高占学',
    'loginid' => 'gaozhanxue',
  ),
  7 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'agree0.com',
    'dead_time' => '2019-07-29',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '高占学',
    'loginid' => 'gaozhanxue',
  ),
  8 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'fedqw.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '何承龙',
    'loginid' => 'hechenglong',
  ),
  9 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'jbxcu.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  10 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'eaxha.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '陈伟',
    'loginid' => 'chenwei',
  ),
  11 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'gudlq.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  12 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'kjnjs.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  13 => 
  array (
    'admin_group_id' => 181,
    'domain' => '﻿showdi.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  14 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'musicze.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  15 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'musicyi.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  16 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'musiccu.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  17 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'musicbu.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  18 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'musicji.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  19 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'musicce.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  20 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'showao.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  21 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'showze.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  22 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'showri.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  23 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'usalu.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  24 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'usaxu.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  25 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'newsyu.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  26 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'newsqu.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  27 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'moneyxi.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  28 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'moneyri.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  29 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'moneyqi.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  30 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'moneyna.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  31 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'travelpu.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  32 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'moneyyu.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  33 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'moneyqu.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  34 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'moneynv.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  35 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'moneymu.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  36 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'moneyju.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  37 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'moneyhu.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  38 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'moneyfo.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  39 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'moneycu.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  40 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'moneyze.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  41 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'moneyde.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  42 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'moneyce.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  43 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'qglms.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  44 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'zuyhj.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  45 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'illrg.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  46 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'fpinr.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  47 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'zplie.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  48 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'kyiib.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  49 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'sriyt.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  50 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'fnpwa.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  51 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'hitqm.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  52 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'ugkze.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  53 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'desfk.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  54 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'kixsv.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  55 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'bxkgf.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  56 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'zveqe.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  57 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'srkki.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  58 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'cupgp.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  59 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'amsuo.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  60 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'hqyga.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  61 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'opjzc.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  62 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'bkqjf.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  63 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'agree4.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  64 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'agree5.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  65 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'agree6.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  66 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'agree7.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  67 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'agree9.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  68 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'affect4.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  69 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'affect8.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  70 => 
  array (
    'admin_group_id' => 181,
    'domain' => 'affect9.com',
    'dead_time' => '2019-04-10',
    'erp_department_id' => '742',
    'olderp_department_id' => '1202',
    'user_name' => 'amazon',
    'admin' => '杨家政',
    'loginid' => 'yangjiazheng',
  ),
  'ret' => 1,
);
    return $domains;
}

function erpInfo()
{

    return array (
  //'id_department' => 742,
  //'old_id_department' => '1202',
  //'id_users' => 2996,
  //'title' => 'MC-LHC-KS旅行包',
  //'foreign_title' => 'MC-LHC-KS旅行包',
  //'id_category' => 1780,
  //'category' => '旅行袋',
  //'id_classify' => 'S',
  //'inner_name' => 'MC-LHC-KS旅行包',
  //'model' => 'S0180441',
  //'thumbs' => 'pixiu-inside.stosz.com/productNew/jpg/2018/8/29/c26a587062ec4c14.jpg',
  //'purchase_price' => NULL,
  //'quantity' => 0,
  //'special_from_date' => 0,
  //'special_to_date' => 0,
  //'length' => NULL,
  //'width' => NULL,
  //'height' => NULL,
  //'weight' => NULL,
  //'is_attach' => 0,
  'status' => 'onsale',
  'desc' => '',
  //'created_at' => '2018-08-29 14:49:17',
  //'updated_at' => '2018-08-29 15:24:22',
  'id' => 999999,
  'price' => NULL,
  'type' => 'simple',
  'bundle' => '',
  'product_attr' => 
  array (
    /*0 => 
    array (
      'creatorId' => NULL,
      'id' => 196001,
      'title' => '款式',
      'createAt' => '2017-10-27 15:17:54',
      'updateAt' => '2017-10-27 15:17:54',
      'version' => 1,
      'attributeValueList' => 
      array (
        0 => 
        array (
          'creatorId' => 2996,
          'id' => 704661,
          'title' => '大红花',
          'createAt' => '2018-08-29 14:49:34',
          'updateAt' => '2018-08-29 14:49:34',
          'attributeId' => 196001,
          'version' => 1,
          'relId' => NULL,
          'bindIs' => NULL,
          'attributeTitle' => NULL,
          'attributeValueLangs' => NULL,
          'productId' => 180441,
          'productAttributeValueRel' => NULL,
          'table' => 'attribute_value',
        ),
        1 => 
        array (
          'creatorId' => 2996,
          'id' => 704662,
          'title' => '斗牛犬',
          'createAt' => '2018-08-29 14:49:38',
          'updateAt' => '2018-08-29 14:49:38',
          'attributeId' => 196001,
          'version' => 1,
          'relId' => NULL,
          'bindIs' => NULL,
          'attributeTitle' => NULL,
          'attributeValueLangs' => NULL,
          'productId' => 180441,
          'productAttributeValueRel' => NULL,
          'table' => 'attribute_value',
        ),
        2 => 
        array (
          'creatorId' => 2996,
          'id' => 704663,
          'title' => '红包',
          'createAt' => '2018-08-29 14:49:41',
          'updateAt' => '2018-08-29 14:49:41',
          'attributeId' => 196001,
          'version' => 1,
          'relId' => NULL,
          'bindIs' => NULL,
          'attributeTitle' => NULL,
          'attributeValueLangs' => NULL,
          'productId' => 180441,
          'productAttributeValueRel' => NULL,
          'table' => 'attribute_value',
        ),
        3 => 
        array (
          'creatorId' => 2996,
          'id' => 704664,
          'title' => '黑包',
          'createAt' => '2018-08-29 14:49:43',
          'updateAt' => '2018-08-29 14:49:43',
          'attributeId' => 196001,
          'version' => 1,
          'relId' => NULL,
          'bindIs' => NULL,
          'attributeTitle' => NULL,
          'attributeValueLangs' => NULL,
          'productId' => 180441,
          'productAttributeValueRel' => NULL,
          'table' => 'attribute_value',
        ),
      ),
      'attributeLangs' => 
      array (
        0 => 
        array (
          'creatorId' => NULL,
          'id' => 10,
          'createAt' => '2017-11-22 11:15:29',
          'updateAt' => '2017-11-22 11:15:29',
          'name' => '貔貅吊坠',
          'attributeId' => 90452,
          'langCode' => 'TH',
          'langName' => '泰语',
          'table' => 'attribute_lang',
        ),
      ),
      'bindIs' => true,
      'categoryId' => NULL,
      'productId' => NULL,
      'bindingNumber' => 0,
      'relId' => NULL,
      'table' => 'attribute',
    ),*/
  ),
  'productZoneState' => 
  array (
    '澳门' => 'archiving',
    '柬埔寨' => 'archiving',
    '泰国' => 'onsale',
    '越南' => 'archiving',
    '新加坡' => 'onsale',
    '菲律宾' => 'archiving',
    '香港' => 'onsale',
    '台湾' => 'onsale',
    '马来西亚' => 'onsale',
    '阿拉伯联合酋长国' => 'archiving',
  ),
  'productZoneList' => 
  array (
    0 => 
    array (
      'id_zone' => '2',
      'title' => '台湾',
      'code' => 'TW',
    ),
    1 => 
    array (
      'id_zone' => '3',
      'title' => '香港',
      'code' => 'HK',
    ),
    2 => 
    array (
      'id_zone' => '7',
      'title' => '新加坡',
      'code' => 'SG',
    ),
    3 => 
    array (
      'id_zone' => '9',
      'title' => '越南',
      'code' => 'VNM',
    ),
    4 => 
    array (
      'id_zone' => '11',
      'title' => '泰国',
      'code' => 'THA',
    ),
    5 => 
    array (
      'id_zone' => '15',
      'title' => '澳门',
      'code' => 'MOP',
    ),
    6 => 
    array (
      'id_zone' => '17',
      'title' => '马来西亚',
      'code' => 'MYS',
    ),
    7 => 
    array (
      'id_zone' => '37',
      'title' => '柬埔寨',
      'code' => 'KHM',
    ),
    8 => 
    array (
      'id_zone' => '45',
      'title' => '菲律宾',
      'code' => 'PHL',
    ),
    9 => 
    array (
      'id_zone' => '55',
      'title' => '阿拉伯联合酋长国',
      'code' => 'ARE',
    ),
  ),
  'productAvailableZoneIds' => '2,3,7,9,11,15,17,37,45,55',
);
}