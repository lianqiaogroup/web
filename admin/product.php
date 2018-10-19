<?php

require_once 'ini.php';
use admin\helper\qiniu ;

if ($_GET['act'] && $_GET['act'] == 'edit') {
    //修改
    $product       = new \admin\helper\product($register);
    $product_id    = $_GET['product_id'];
    $data = [];
    $support =0;
    $isSuppertBluePay = false;//是否支持blueplay
    if ($product_id) {
        $data          = $product->getOneProduct($product_id);
        $gle_data      = $product->getProductGoogleExt($product_id);
        $data['gle']   = $gle_data;
        if(!$data)
        {
            $error['content'] = '你没有权限查看此产品！';
            $error['url']     = "index.php#products";
            $register->get('view')->display('error.twig', $error);
            exit;
        }
        //查找属性
        $attr         = $product->getProduct_attr($product_id);
        $data['attr'] = $attr;

        //扩展表ext-google数据
        $gle          = $db->select('product_ext', '*', ['product_id'=>$product_id]);
        if (!empty($gle)) {
            $data['gle'] = $gle[0];
        }

        //图集
        $photos         = $db->select('product_thumb', '*', ["ORDER" => ['add_time' => "DESC"], 'product_id' => $product_id]);
        $data['photos'] = $photos;

        //套餐
        $combo         = new \admin\helper\combo($register);
        $combo         = $combo->findCombo($product_id);
        $data['combo'] = $combo;
        ##jade
        $sms    = new \admin\helper\sms($register);
        $ret = $sms->checkIspByZone($data['id_zone']);
        if($ret['ret']){
            $data['show_open_sms'] = 1;
        }
        //判断是否支持钱海支付
        $pay = new \admin\helper\payment($register);
        $support = $pay->supportOcean($data['domain']);
        $isSuppertBluePay = $pay->supportOcean($data['domain'],'bluePay');
    } else {
        $data['ad_member_id'] = -1;
    }
    $data['admin'] = $_SESSION['admin'];
    $D                 = new \admin\helper\country($register);
    $id_zone           = $D->getAllZone();
    $data['id_zones']  = $id_zone;
    $_SESSION['token'] = $data['token']     = md5(uniqid(rand(), true));
    $region            = $db->select('region', '*', ['parent_id' => 0]);
    $data['region']    = $region;
    $data['supportOcean'] = $support;
    $data['isSuppertBluePay'] = $isSuppertBluePay;

    //获取产品分类目录
    $category = new \admin\helper\category($register);

    //查询改产品的分类
    $ret_category = $category->getProductCategory($data['product_id']);
    $modules = $category->getCategoryParent($data['domain']);

    if($modules)
    {
        foreach ($modules as $r) {
            $r['selected'] = $r['id'] == $ret_category['id'] ? 'selected' : '';
            $r['spa']      = '&emsp;&emsp;';
            $array[]       = $r;
        }
        $str = "<option value='\$id' \$selected>\$spacer \$title \$spa\$spa 中文名:\$title_zh</option>";
        $T   = new \lib\tree();
        $T->init($array);

        $module = $T->getTree(0, $str);
    }
    $data['module'] = $module;
    $data['category_title'] = $ret_category['title'];
    $register->get('view')->display('/product/product.twig', $data);
}
else if ($_GET['act'] && $_GET['act'] == 'save') {
    $product    = new \admin\helper\product($register);
    $product_id = $_POST['product_id'];
    $is_edit    = empty($product_id) ? false : true;
    $error      = [];

    if (!$product_id) {
        $preg = '/^[a-zA-Z0-9]{1,}$/';
        if ($_POST['identity_tag'] && !preg_match($preg, trim($_POST['identity_tag']))) {
            echo json_encode(['ret' => 0, 'msg' => "二级目录只能是字母或者数字或字母数字的组合"]);
            exit;
        }
    } else if (!$_POST['title']) {
        echo json_encode(['ret' => 0, 'msg' => '产品名称不能空']);
        exit;
    } else if (!$_POST['theme']) {
        echo json_encode(['ret' => 0, 'msg' => '请选择主题']);
        exit;
    } else if (!$_POST['domain']) {
        echo json_encode(['ret' => 0, 'msg' => '域名不能空']);
        exit;
    } else if (isset($_POST['identity_tag']) && !empty($_POST['identity_tag'])) {
        //允许重复域名
        $ret = $product->domainCheck($_POST['domain'], $_POST['identity_tag'], $product_id);
        if (!$ret['ret']) {
            echo json_encode(['ret' => 0, 'msg' => $ret['msg']]);
            exit;
        }
    } else if (trim($_POST['identity_tag']) == '0') {
        echo json_encode(['ret' => 0, 'msg' => "二级目录不能为0"]);
        exit;
    }

    // jade add:  check is_open_sms
    if(!empty($_POST['is_open_sms'])){
        $sms    = new \admin\helper\sms($register);
        $ret = $sms->checkIspByZone($_POST['id_zone']);
        if(!$ret['ret']){
            $_POST['is_open_sms'] = 0;
        }
    }

    //缩略图
    if (!empty($_POST['thumbsUrl'])) {
        $save['thumb'] = qiniu::changImgDomain($_POST['thumbsUrl']);
    }
    //logo
    if (!empty($_POST['logoUrl'])) {
        $save['logo'] =  qiniu::changImgDomain($_POST['logoUrl']);
    }

    //获取地区查询对应货币以及lang包
    $id_zone                 = $_POST['id_zone'];
    $D                       = new \admin\helper\country($register);
    $zone                    = $D->getOne($id_zone);
    $save['id_zone']         = $id_zone;
    $C                       = new \admin\helper\currency($register);
    $currency                = $C->getOne($zone['currency_id']);
    $save['currency']        = $currency['currency_code'];
    $save['currency_prefix'] = 0;
    $save['currency_code']   = $currency['symbol_right'];
    if ($currency['symbol_left']) {
        $save['currency_prefix'] = 1;
        $save['currency_code']   = $currency['symbol_left'];
    }
    //获取地区 end
    $users = $_POST['users'];
    $ad_member_id =0;
    if(strpos($users,'_')!==false)
    {
        list($ad_member_id,$ad_member) = explode('_',$users);
    }
    if ($ad_member_id ==1 || !$ad_member_id) {
        echo json_encode(['ret'=>0,'msg'=>'请选择优化师或优化师不能为系统']);
        exit;
    }

    $save['identity_tag']  = trim($_POST['identity_tag']);
    $save['lang']          = $_POST['lang'];
    $save['domain']        = trim($_POST['domain']);
    $save['id_department'] = $_POST['id_department'];
    $save['ad_member_id']  = $ad_member_id;
    $save['ad_member']     =  $ad_member; //jimmy fix save ad_member
    $save['tags']          = isset($_POST['tags']) ? trim($_POST['tags']):'';
    $save['theme']         = $_POST['theme'];
    $save['content']       = qiniu::changImgDomain($_POST['content']);
    $save['photo_txt']     = $_POST['photo_txt'];

    $save['price'] = money_int($_POST['price']);
    if (!$_POST['market_price']) {
        if(empty($_POST['discount']) || ($_POST['discount'] <= 0 ) || ($_POST['discount'] > 10 ) ){
            $_POST['discount'] = 10;
        }
        $market_price          = round($_POST['price'] / ($_POST['discount'] / 10));
        $save['market_price']  = money_int($market_price);
    }
    else {
        $save['market_price']  = money_int($_POST['market_price']);
    }
    $save['discount']          = money_int($_POST['discount']);
    $save['title']             = $_POST['title'];
    $save['waybill_title']     = $_POST['waybill_title'];//面单名称
    $save['sales_title']       = trim($_POST['sales_title']);
    if(!$save['sales_title']){
        echo json_encode( ['ret'=>0,'msg'=>"外文名不能为空"]);
        exit;
    }
    if(isCn($save['sales_title']) && !in_array($save['lang'],['TW','CN','JP'])){
        echo json_encode( ['ret'=>0,'msg'=>"外文名含有中文，修改后提交"]);
        exit;
    }

    $save['payment_online']    = $_POST['payment_online'];
    $save['payment_underline'] = I('post.payment_underline', 0);

    $save['payment_paypal']    = $_POST['payment_paypal'];
    $save['payment_asiabill']  = I('post.payment_asiabill', 0);
    $save['payment_ocean']     = I('post.payment_ocean', 0);
    $save['payment_blue']      = I('post.payment_blue', 0);
    if(!$save['payment_underline'] && !$save['payment_ocean'] && !$save['payment_blue']){
        echo json_encode(['ret'=>0,'msg'=>'请选择支付方式']);
        exit;
    }
    $save['erp_number']        = trim($_POST['erp_number']);
    $save['la']                = $_POST['la'];
    $fb_px =                    trim($_POST['fb_px']);
    $fb_px = str_replace('，',',',$fb_px);
    $save['fb_px']             = $fb_px;

    $save['sales']               = $_POST['sales'] ?: 1000;
    $save['stock']               = $_POST['stock'] ?: 100;
    $save['service_contact_id']  = $_POST['service_contact_id'];
    $save['service_email']       = $_POST['service_email'];
    $save['google_js']           = $_POST['google_js'];
    $save['google_analytics_js'] = $_POST['google_analytics_js'];
    $save['yahoo_js']            = $_POST['yahoo_js'];
    $save['yahoo_js_trigger']    = $_POST['yahoo_js_trigger'];
    $save['tips']                = $_POST['tips'];
    if (!$_POST['seo_title'] || !$_POST['seo_description']) {
        $save['seo_title']       = $save['seo_description'] = $_POST['title'];
    }
    else {
        $save['seo_title']       = $_POST['seo_title'];
        $save['seo_description'] = $_POST['seo_description'];
    }
    if (!$product_id) {
        $save['company_id'] = $_SESSION['admin']['company_id'] ?:1;
        $save['add_time'] = date("Y-m-d H:i:s", time());
    }
    $save['is_open_sms'] = (int)$_POST['is_open_sms'];

    //jimmy fix 把套餐，属性这些提前判断。
    //是否有插入属性
    if ($_POST['attr_group_id']) {
        foreach ($_POST['attr_group_id'] as $key => $t) {
            $attrArray[$key]['name']             = $_POST['name'][$key];
            $attrArray[$key]['attr_group_id']    = $t;
            $attrArray[$key]['thumb']            = qiniu::changImgDomain($_POST['attr_thumb'][$key]);
            $attrArray[$key]['attr_group_title'] = trim($_POST['attr_group_title'][$key]);
            $attrArray[$key]['number']           = trim($_POST['attr_erp_number'][$key]);
            if(!in_array($save['lang'],['TW','CN','JP'])){
                if(isCn($attrArray[$key]['name']) || isCn($attrArray[$key]['attr_group_title']))
                {
                    echo json_encode( ['ret'=>0,'msg'=>"属性组或属性值外文名含有中文，修改后提交"]);
                    exit;
                }
            }
            if(empty($attrArray[$key]['name'] ) || empty($attrArray[$key]['attr_group_title'])){
                echo json_encode( ['ret'=>0,'msg'=>"属性或者属性组不能为空"]);
                exit;
            }
        }
    }

    //判断是否有套餐
    if ($_POST['combo']) {
        ////插入更新
        foreach ($_POST['combo'] as $key => $value) {
            //判断是更新还是新增
            $combo_id = $value['combo_id'];
            $combo['title']      = trim($value['name']);
            $combo['is_single_combo'] = $value['is_single_combo'];
            if(!$combo['title']){
                echo json_encode( ['ret'=>0,'msg'=>"套餐名称不能为空"]);
                exit;
            }
            if(isCn($combo['title']) && !in_array($save['lang'],['TW','CN','JP'])) {
                echo json_encode( ['ret'=>0,'msg'=>"套餐名称含有中文，修改后提交"]);
                exit;
            }
            $combo['price']      = money_int($value['price']);
            //  $combo['product_id'] = $product_id;
            if($value['thumb'])
            {
                $combo['thumb']      = qiniu::changImgDomain($value['thumb']);
            }
            if($combo_id)
            {
                $db->update('combo', $combo,['combo_id'=>$combo_id]);
            }
            foreach ($value['num'] as $k => $item) {
                $combo_goods_id = $value['combo_goods_id'][$k];
                if (!$value['erp_id'][$k]) {
                    echo json_encode(['ret'=>1,'msg'=>'套餐产品erpid不能为空']);
                    exit;
                }
                if($combo_goods_id){
                    $comboGoodsEdit['num']        = $item;
                    $comboGoodsEdit['erp_id']     = $value['erp_id'][$k];
                    $comboGoodsEdit['product_id'] = $value['product_id'][$k];
                    $comboGoodsEdit['combo_id']   = $combo_id;
                    $comboGoodsEdit['title']      = trim($value['title'][$k]);
                    $comboGoodsEdit['sale_title'] = trim($value['sale_title'][$k]) ;
                    if(!$comboGoodsEdit['title'] || !$comboGoodsEdit['sale_title']){
                        echo json_encode( ['ret'=>0,'msg'=>"套餐产品名称不能为空"]);
                        exit;
                    }
                    if(isCn($comboGoodsEdit['sale_title']) && !in_array($save['lang'],['TW','CN','JP'])){
                        echo json_encode( ['ret'=>0,'msg'=>"套餐外文名含有中文，修改后提交"]);
                        exit;
                    }
                    $comboGoodsEdit['promotion_price'] = money_int($value['promotion_price'][$k]);
                    $db->update('combo_goods', $comboGoodsEdit,['combo_goods_id'=>$combo_goods_id]);
                    continue;
                }
                $comboGoods[$k]['num']        = $item;
                $comboGoods[$k]['erp_id']     = $value['erp_id'][$k];
                $comboGoods[$k]['product_id'] = $value['product_id'][$k];
                $comboGoods[$k]['title']      = $value['title'][$k];
                $comboGoods[$k]['promotion_price']      = money_int($value['promotion_price'][$k]);
                $comboGoods[$k]['sale_title'] = empty($value['sale_title'][$k]) ?  $value['title'][$k] :$value['sale_title'][$k] ;
                if(!$comboGoods[$k]['title'] || !$comboGoods[$k]['sale_title']){
                    echo json_encode( ['ret'=>0,'msg'=>"套餐产品名称不能为空"]);
                    exit;
                }
                if(isCn($comboGoods[$k]['sale_title']) && !in_array($save['lang'],['TW','CN','JP'])){
                    echo json_encode( ['ret'=>0,'msg'=>"套餐外文名含有中文，修改后提交"]);
                    exit;
                }

                if($combo_id)
                {
                    $comboGoods[$k]['combo_id']      = $combo_id;
                    $addComboGoods[] = $comboGoods[$k];
                    unset($comboGoods[$k]);
                }
            }
            if($comboGoods)
            {
                $combo['goods'] = $comboGoods;
                $combos[] =$combo;
            }
        }
    }

    $ret = $product->saveProduct($product_id, $save);
    if($ret === false)
    {
        echo json_encode(['ret'=>0,'msg'=>'保存产品失败']);
        exit;
    }

    //保存产品成功后赋值产品ID
    if (!$product_id) { $product_id = $ret; }

    //新增产品功能 保存插入产品属性ID 用户保存产品的原图
    $productAttributeIDS = [];
    //如果有属性 插入属性
     if($attrArray)
     {
         foreach ($attrArray as $insertAttr)
         {
             $insertAttr['product_id'] = $product_id;
             $insertAttrs[] = $insertAttr;
         }

         $ret = $product->addForSingleProductAttr($insertAttrs);
         $productAttributeIDS = $ret;
     }

    //用户保存插入套餐数据库的套餐ID 用户保存产品的原图
    $productComboIDS = [];
    //如果有套餐 插入套餐
    if($combos)
    {
        foreach ($combos as $insertCombo)
        {
            $comboGoods = $insertCombo['goods'];
            $insertComboGoods  =[];
            unset($insertCombo['goods']);
            $insertCombo['product_id'] = $product_id;
            $combo_id            = $db->insert('combo', $insertCombo);
            $productComboIDS[]   = $combo_id;
            foreach ($comboGoods as  $insertGoods)
            {
                $insertGoods['combo_id'] = $combo_id;
                $insertComboGoods[] =  $insertGoods;
            }
            $db->insert('combo_goods', $insertComboGoods);
        }
    }
    if($addComboGoods && count($addComboGoods) >0){
        $db->insert('combo_goods', $addComboGoods);
    }

    //保存产品分类
    if($_POST['product_category']){
        $category = new \admin\helper\category($register);
        $category_id = $_POST['product_category'];
        $ret_cat = $category->saveProductCategory(['category_id'=>$category_id, 'product_id'=>$product_id]);
    }


    //判断是否有编辑的属性，有就写入
    if($_POST['product_attr_id'])
    {
        foreach ($_POST['product_attr_id'] as $k => $v) {
            $upAttr =[];
            $upAttr['name']             = trim($_POST['up_name'][$k]);
            $upAttr['attr_group_id']    = $_POST['up_attr_group_id'][$k];
            $upAttr['attr_group_title'] = trim($_POST['up_attr_group_title'][$k]);
            $upAttr['number']           = $_POST['up_number'][$k];
            if($_POST['attr_thumb_url'][$k])
            {
                $upAttr['thumb'] = qiniu::changImgDomain($_POST['attr_thumb_url'][$k]);
            }
            if(!in_array($save['lang'],['TW','CN','JP'])){
                if(isCn($upAttr['name']) || isCn($upAttr['attr_group_title']))
                {
                    echo json_encode( ['ret'=>0,'msg'=>"属性组或属性值外文名含有中文，修改后提交"]);
                    exit;
                }
            }
            if(empty($upAttr['name']) || empty($upAttr['attr_group_title'])){
                echo json_encode( ['ret'=>0,'msg'=>"属性或者属性组不能为空"]);
                exit;
            }
            $product->saveProductAttr($v, $upAttr);
        }
    }

    //用户保存产品图集上传成功的ID 用户保存产品的原图
    $productPhotoIDS = [];
    //判断是否上传图集
    if (count($_POST['photos'])) {
        $photos = [];
        foreach ($_POST['photos'] as $key => $value) {
            $photos[$key]['thumb']      =  qiniu::changImgDomain($value);
            $photos[$key]['add_time']   = date('Y-m-d H:i:s', time());
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
    $gle_data['product_id'] = empty($product_id) ? 0:$product_id;
    if ($gle_data['product_id']) {
        $gle_data['google_analytics_id']        = trim($_POST['google_analytics_id']);      //google跟踪id
        $gle_data['google_conversion_id']       = trim($_POST['google_conversion_id']);     //google转化id
        $gle_data['google_conversion_label']    = trim($_POST['google_conversion_label']);  //google转化标签
        $gle_data['google_marketing_js']        = trim($_POST['google_marketing_js']);      //google再营销js
        $gle_data['create_date']                = time();                                   //时间
        $product->saveProductGoogleExt($product_id, $gle_data);
    }

    //保存产品的原图到数据库logo(1) thumbs(2) photos(3) attr(4) content(5)
    //使用产品ID和是否编辑字段进行编辑
    if ($product_id && !$is_edit) {
        $addTime = time();
        $oglData = [];
        //logo
        if (isset($_POST['originalLogoUrl']) && !empty(trim($_POST['originalLogoUrl']))) {
            $oglData[] = ['product_id'=>$product_id, 'type'=>1, 'thumb'=>$_POST['originalLogoUrl'], 'add_time'=>$addTime];
        }
        //thumbs
        if (isset($_POST['originalThumbsUrl']) && !empty(trim($_POST['originalThumbsUrl']))) {
            $oglData[] = ['product_id'=>$product_id, 'type'=>2, 'thumb'=>$_POST['originalThumbsUrl'], 'add_time'=>$addTime];
        }
        //photos
        if (isset($_POST['original_photos'])) {
            foreach ($productPhotoIDS as $key => $value) {
                if ($value && !empty($_POST['original_photos'][$key])) {
                    $thumb = $_POST['original_photos'][$key];
                    $oglData[] = ['product_id'=>$product_id, 'type'=>3,'fg_id'=>$value, 'thumb'=>$thumb, 'add_time'=>$addTime];
                }
            }
        }
        //attr
        foreach ($productAttributeIDS as $key=>$attribute_id) {
            $thumb = $_POST['original_attr_thumb'][$key];
            if (trim($thumb)) {
                $oglData[] = ['product_id'=>$product_id, 'type'=>4, 'fg_id'=>$attribute_id, 'thumb'=>$thumb, 'add_time'=>$addTime];
            }
        }
        //combo
        foreach ($productComboIDS as $key=>$combo_id) {
            $thumb = $_POST['originalCombo'][$key+1]['thumb'];
            if (trim($thumb)) {
                $oglData[] = ['product_id'=>$product_id, 'type'=>5, 'fg_id'=>$combo_id, 'thumb'=>$thumb, 'add_time'=>$addTime];
            }
        }

        //从ue编辑器里取出所有的img标签
        $imgElement = $save['content'];
        if ($imgElement) {
            preg_match_all('/<img src=\"([\s\S]*?)\"/',     $imgElement, $matThumbs);
            preg_match_all('/original_src=\"([\s\S]*?)\"/', $imgElement, $matOgiThumbs);
            $matThumbs    = $matThumbs[1];
            $matOgiThumbs = $matOgiThumbs[1];
            $count_thumbs = count($matThumbs);
            $count_original = count($matOgiThumbs);
            if ($count_thumbs > 0 && $count_original > 0 && ($count_thumbs == $count_original)) {
                foreach ($matThumbs as $key=>$value) {
                    $thumb = $matThumbs[$key].','.$matOgiThumbs[$key];
                    $oglData[] = ['product_id'=>$product_id, 'type'=>6, 'thumb'=>$thumb, 'add_time'=>$addTime];
                }
            }
        }
        $product->addProductOriginalImage($product_id, $oglData);
    } else {
        if (isset($_POST['originalLogoUrl']) && !empty(trim($_POST['originalLogoUrl']))) {
            $upData     = ['product_id'=>$product_id,'type'=>1, 'thumb'=>$_POST['originalLogoUrl'], 'add_time'=>time()];
            $condition  = ['product_id'=>$product_id, 'type'=>1];
            $product->updateProductOriginalImage($upData, $condition);
        }
        //thumbs
        if (isset($_POST['originalThumbsUrl']) && !empty(trim($_POST['originalThumbsUrl']))) {
            $upData     = ['product_id'=>$product_id,'type'=>2, 'thumb'=>$_POST['originalThumbsUrl'], 'add_time'=>time()];
            $condition  = ['product_id'=>$product_id, 'type'=>2];
            $product->updateProductOriginalImage($upData, $condition);
        }
        //photos
        if (!empty($productPhotoIDS)) {
            $photoData = [];
            foreach ($productPhotoIDS as $key => $value) {
                if ($value && !empty($_POST['original_photos'][$key])) {
                    $thumb = $_POST['original_photos'][$key];
                    $photoData[] = ['product_id'=>$product_id, 'type'=>3,'fg_id'=>$value, 'thumb'=>$thumb, 'add_time'=>time()];
                }
            }
            $product->saveProductOriginalImage($photoData);
        }
        //attr
        $product_attr_ids               = $_POST['product_attr_id'];
        $product_attr_original_thumbs   = $_POST['original_attr_thumb'];
        if (isset($_POST['original_attr_thumb'])) {
            foreach ($product_attr_ids as $key => $id) {
                $thumb = $product_attr_original_thumbs[$key];
                if ($thumb) {
                    $upData = ['product_id'=>$product_id,'type'=>4, 'fg_id'=>$id, 'thumb'=>$thumb, 'add_time'=>time()];
                    $condition = ['product_id'=>$product_id, 'type'=>4, 'fg_id'=>$id];
                    $product->updateProductOriginalImage($upData, $condition);
                }
            }
        }

        //combo
        $combo_data = [];
        foreach ($productComboIDS as $key=>$combo_id) {
            $thumb = $_POST['originalCombo'][$key+1]['thumb'];
            if (trim($thumb)) {
                $combo_data[] = ['product_id'=>$product_id, 'type'=>5, 'fg_id'=>$combo_id, 'thumb'=>$thumb, 'add_time'=>time()];
            }
        }
        $ret = $db->insert('product_original_thumb', $combo_data);
        $product_combos = $_POST['combo'];
        if (isset($_POST['originalCombo'])) {
            $product_combo_original_thumbs = $_POST['originalCombo'];
            foreach ($product_combos as $id => $item) {
                $thumb = $product_combo_original_thumbs[$id]['thumb'];
                if ($thumb) {
                    $upData = ['product_id'=>$product_id,'type'=>5, 'fg_id'=>$id, 'thumb'=>$thumb, 'add_time'=>time()];
                    $condition = ['product_id'=>$product_id, 'type'=>5, 'fg_id'=>$id];
                    $product->updateProductOriginalImage($upData, $condition);
                }
            }
        }

        //content
        $imgElement = $save['content'];
        //先删除全部
        $db->delete('product_original_thumb', ['product_id'=>$product_id, 'type'=>6]);
        if (!empty($imgElement)) {
            $contentData= [];
            preg_match_all('/<img src=\"([\s\S]*?)\"/',     $imgElement, $matThumbs);
            preg_match_all('/original_src=\"([\s\S]*?)\"/', $imgElement, $matOgiThumbs);
            $matThumbs    = $matThumbs[1];
            $matOgiThumbs = $matOgiThumbs[1];
            $count_thumbs = count($matThumbs);
            $count_original = count($matOgiThumbs);

            if ($count_thumbs > 0 && $count_original > 0 && ($count_thumbs == $count_original)) {
                foreach ($matThumbs as $key=>$value) {
                    $thumb = $matThumbs[$key].','.$matOgiThumbs[$key];
                    $contentData[] = ['product_id'=>$product_id, 'type'=>6, 'thumb'=>$thumb, 'add_time'=>$addTime];
                }

                $product->saveProductOriginalImage($contentData);
            }
        }
    }

    $url = '/product.php?act=edit&product_id='.$product_id;
    echo json_encode(['ret'=>1,'msg'=>'保存成功','url'=>$url]);
    if(!empty($_POST['identity_tag'])){
        $key_product = 'PRO_'.$_POST['domain']."_".str_replace('/','',$identity_tag);
        $cache->del($key_product);
    }
    // $tags = isset($_POST['identity_tag']) ? trim($_POST['identity_tag']):'';
    // $seo_loginid = $db->get('oa_users','username',['name_cn'=>$ad_member]);
    // $product->sendDomainToErp(trim($_POST['erp_number']),trim($_POST['domain']),$tags,$_POST['id_zone'],$product_id,$seo_loginid);
    //使用队列完成,等待erp上线再上线
}
elseif($_GET['act'] && $_GET['act'] == 'googleSave'){
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
elseif ($_GET['act'] && $_GET['act'] == 'add') {
    $data['admin'] = $_SESSION['admin'];
    $register->get('view')->display('/product/product.twig', $data);
}
elseif ($_POST['act'] && $_POST['act'] == 'delete') {

    $product_attr_id = $_POST['product_attr_id'];
    $product         = new \admin\helper\product($register);
    $ret             = $product->deleteAttr($product_attr_id);
    echo json_encode($ret);
}
elseif ($_POST['act'] && $_POST['act'] == 'del') {
    $product_id     = $_POST['product_id'];
    $product        = new \admin\helper\product($register);
    $data['is_del'] = $_POST['is_del'];

    $ret = $product->delProduct($product_id, $data);
    echo json_encode($ret);
}
elseif ($_POST['act'] && $_POST['act'] == 'departmentChange') {
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
elseif ($_POST['act'] && $_POST['act'] == 'deleteCombo') {
    $combo_id = $_POST['combo_id'];
    $product  = new \admin\helper\combo($register);
    $ret      = $product->delCombo($combo_id);
    echo json_encode($ret);
}
//删除套餐产品
elseif ($_POST['act'] && $_POST['act'] == 'deleteComboGoods') {
    $id = $_POST['combo_goods_id'];
    $product  = new \admin\helper\combo($register);
    $ret      = $product->delComboGoods($id);
    echo json_encode($ret);
}
//mike：导出产品id和域名
elseif ($_GET['act'] && $_GET['act'] == 'downloadid') {
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
elseif ($_POST['act'] && $_POST['act'] == 'check') {
    $domain = $_POST['domain'];
    //获取域名信息
    $product = new \admin\helper\product($register);
    $ret = $product->getErpDomainInfo($domain);
// var_dump($ret);exit();
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
elseif ($_POST['act'] && $_POST['act'] == 'getErpProduct') {
//    $company = new \admin\helper\company($register);
//    $companyInfo  = $company->getInfo();
//    $uri = $companyInfo['domain_erp'];
    //$product_id = $_POST['product_id'];
    //$erp_number = $_POST['number'];
    //$product    = new \admin\helper\product($register);
    ##jade add
    // $company = new \admin\helper\company($register);  ##后台配置之后才能用
    // $objname = $company->getErpProductObjname();
    //$objname = '';
    // $objname = 'admin\helper\api\erpproduct';
    /*
    if(!$objname) {
        $uri        = 'http://erp.stosz.com:9090/Product/Api/get?id=' . $erp_number;
        if(environment=='office')
        {
            $uri        = 'http://192.168.109.252:8081/Product/Api/get?id=' . $erp_number;
        }
        $ret        = file_get_contents($uri);
        $ret        = json_decode($ret, true);
    }else{
        $params = ['company_id'=>$_SESSION['admin']['company_id'],'loginid'=>$_SESSION['admin']['login_name'],'id'=>$erp_number];
        if(environment=='office'){
            $obj = new $objname('dev');
        }else{
            $obj = new $objname();
        }
        $ret = $obj->getProduct($params);
    }

    if (!$ret['status'] || !$ret['product']) {
        echo json_encode(['ret' => 0, 'msg' => "没找到该产品信息，请确认。"]);
        exit;
    }
*/
    echo json_encode(['ret' => 1, 'data' => $ret['product']]);
}
elseif ($_POST['act'] && $_POST['act'] == 'deletePhotos') {
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
elseif($_POST['act'] && $_POST['act'] == 'delAttrThumb'){
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
elseif ($_GET['act'] && $_GET['act'] == 'publicProduct') {
    // if ($_POST) {
    //     if ($_POST['type'] == 'title') {
    //         $filter['title[~]'] = ['like', '%' . $value . '%'];
    //     }
    //     else if ($_POST['type'] == 'erpid'){
    //         $filter['erp_number'] = $value;
    //     }else if ($_POST['type'] == 'domain'){
    //         $filter['domain'] = $value;
    //     }
    // }

    if(isset($_GET['type']) && isset($_GET['keyword'])){
        $value = trim($_GET['keyword']);
        if ($_GET['type'] == 'title') {
            $filter['product.title[~]'] = ['like', '%' . $value . '%'];
            $filterWeb['title[~]'] = ['like', '%' . $value . '%'];
        }
        if ($_GET['type'] == 'erpid') {
            $filter['product.erp_number'] = $value;
            $filterWeb['erp_number'] = $value;

        }
        if ($_GET['type'] == 'domain') {
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
    $filter['product.keyword'] = $value;
    $filterWeb['keyword'] = $value;
    $data['filter']    = $filterWeb;
    if($filter['product.id_zone'])
    {
        $data['id_zone'] =  $filter['product.id_zone'];
    }
    if ($filter['product.user_id']) {
        # code... 
        $data['user_id'] =  $filter['product.userid']; 
    }

    
    if (isset($_GET['self']) && $_GET['self'] == 1) {
        $register->get('view')->display('/product/public_product_self.twig', $data);
    }
    else {
        $register->get('view')->display('/product/public_product.twig', $data);
    }
}
elseif ($_GET['act'] && $_GET['act'] == 'publicProduct2') {
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
else if (isset($_POST['act']) && $_POST['act'] == 'seo') {
    /*
    //获取域名判断是否为空
    $domain = $_POST['domain'];
    if (!$domain) {
        echo json_encode(array('ret'=>0, 'msg'=>'请求域名为空'));
        exit;
    }*/

    //erp信息
//    $company = new \admin\helper\company($register);
//    $companyInfo  = $company->getInfo();
//    $uri = $companyInfo['domain_erp'];
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
        if(environment=='office'){
            $obj = new $objname('dev');
        }else{
            $obj = new $objname();
        }
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
elseif (isset($_POST['act']) && $_POST['act'] == 'copy') {

    $_POST = \admin\Validator\ProductValidator::handle($_POST, $_POST['product_id'], 0, 2);

    //复制一条产品
    $product = new \admin\helper\product($register);

    $data['id'] = $_POST['product_id'];
    $data['id_zone'] = $_POST['id_zone'];
    $data['identity_tag'] = $_POST['identity_tag'];
    $data['domain'] = $_POST['domain'];
    $data['lang'] = $_POST['lang'];
    $data['theme'] = $_POST['theme'];
    $data['zone'] = $_POST['zone']; //地区缩写
    $data['is_comment'] = isset($_POST['is_comment']) ? $_POST['is_comment'] : NULL;

    if (empty($data['id'])) {
        ajaxReturn(['ret' => 0, 'msg' => "产品ID不能为空"]);
    }

    if (empty($data['zone'])) {
        ajaxReturn(['ret' => 0, 'msg' => "产品地区(英文)不能为空"]);
    }
    // 开始复制 
    unset($data['zone']);
    $ret = $product->productCopyWithOriginImage($data, $register);
    unset($ret['product']);
    ajaxReturn($ret);
} elseif(isset($_POST['act']) && $_POST['act'] == 'reloadProduct') {

    $user = new \admin\helper\user($register);
    $ret = $user->updateUserProduct();

    echo json_encode($ret);

} 
elseif(isset($_POST['act']) && $_POST['act'] == 'supportOcean') {

    $domain = $_POST['domain'];
    $pay = new \admin\helper\payment($register);
    $support = $pay->supportOcean($domain);
    echo json_encode(['ret'=>$support]);
} 
elseif(isset($_GET['act']) && $_GET['act'] == 'checkdomain') {
    $c = new \admin\helper\product($register);
    $r = $c->checkDomain($_GET['domain']);
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
    echo json_encode(['ret'=>$r,'module'=>$module]);
}
elseif(isset($_GET['act']) && $_GET['act'] == 'getAdUser'){
    $c = new \admin\helper\company($register);
    // var_dump($_SESSION['admin']['uid']);exit();
    $ret = $c->getAUser($_SESSION['admin']['uid']);
    echo json_encode(['ret'=>$ret]);
}
elseif(isset($_GET['act']) && $_GET['act'] == 'getAUser'){
    $c = new \admin\helper\company($register);
    $ret = $c->getAUser($_SESSION['admin']['uid']);//getAUser获取本部门
    echo json_encode(['ret'=>$ret]);
}
elseif(isset($_GET['act']) && $_GET['act'] == 'getAllZone'){
    $c                 = new \admin\helper\country($register);
    $ret  = $c->getAllZone();
    echo json_encode(['ret'=>$ret]);
}
elseif(isset($_POST['act']) && $_POST['act'] == 'deleteCover'){
    $productID = isset($_POST['product_id'])?$_POST['product_id']:'';
    if (empty($productID)) {
        echo json_encode(['ret'=>'fail','msg'=>'empty product_id']);
    }
    $productVideoModel = new \admin\helper\productvideo($register);
    $b = $productVideoModel->deleteCover($productID);
    if ($b) {
        echo json_encode(['ret'=>'1','msg'=>'ok']);
    } else {
        echo json_encode(['ret'=>'0','msg'=>'fail']);
    }
}
elseif(isset($_POST['act']) && $_POST['act'] == 'deleteVideo'){
    $productID = isset($_POST['product_id'])?$_POST['product_id']:'';
    if (empty($productID)) {
        echo json_encode(['ret'=>'fail','msg'=>'empty product_id']);
    }
    $productVideoModel = new \admin\helper\productvideo($register);
    $b = $productVideoModel->deleteVideo($productID);
    if ($b) {
        echo  json_encode(['ret'=>'1','msg'=>'ok']);
    } else {
        echo json_encode(['ret'=>'0','msg'=>'fail']);
    }
}
else {
    $filter = [];
    $info   = [];
    if ($_GET['title']) {
        $filter['title[~]'] = ["%" . $_GET['title'] . '%'];
        $info['title']      = $_GET['title'];
    }
    if ($_GET['domain']) {
        $domain  = $_GET['domain'];
        if(strpos($domain,'http')!==false)
        {
            $domain = trim(str_replace('http://',"",$domain));
        }
        if(strpos($domain,'/')!==false)
        {
            list($mapDomain,$tag) = explode('/',$domain);
            $filter['identity_tag'] = trim($tag);
        }
        else{
            $mapDomain = $domain;
        }
        $filter['domain[~]'] = ["%" . $mapDomain . '%'];
        $info['domain']      = $domain;
    }
    if ($_GET['theme']) {
        $filter['theme[~]'] = ["%" . $_GET['theme'] . '%'];
        $info['theme']      = $_GET['theme'];
    }
    if ($_GET['erp_id']) {
        $filter['erp_number'] = $_GET['erp_id'];
        $info['erp_id']       = $_GET['erp_id'];
    }

    if ($_GET['product_id']){
        $filter['product_id'] = $_GET['product_id'];
        $info['product_id']       = $_GET['product_id'];
    }

    if (isset($_GET['is_del'])) {
        if ($_GET['is_del'] != -1) {
            $filter['is_del'] = $_GET['is_del'];
        }

        $info['is_del'] = $_GET['is_del'];
    }
    else {
        $info['is_del']   = $filter['is_del'] = 0;
    }

    if(!empty($_GET['aid']) ){
        $filter['aid'] = trim($_GET['aid']);//建站人员
    }
    if(isset($_GET['ad_member_id'])){
        $filter['ad_member_id'] = trim($_GET['ad_member_id']);//优化师
    }
    if(isset($_GET['id_zone'])){
        $filter['id_zone'] = trim($_GET['id_zone']);//投放地区
    }

    $product        = new \admin\helper\product($register);
    $data           = $product->getAllProduct($filter);
    $data['admin']  = $_SESSION['admin'];
    $data['filter'] = $info;
    //user
    $user = $register->get('db')->select('user',['uid','username']);
    $data['user'] = $user;
    if( isset($_GET['json']) && $_GET['json']=='1' ){
        echo json_encode($data);
    }else{
        $register->get('view')->display('/product/product_list.twig', $data);
    }

}
