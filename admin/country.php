<?php
require_once 'ini.php';

if ($_GET['act'] && $_GET['act']=='edit')
{
    $id = I("get.id");

    $D = new \admin\helper\country($register);
    //获取获取国家
    $country = $D->getAllCountry();
    //获取所有货币
    $currency = $D->getAllCurrency();
    if($id)
    {
        $data =  $D->getOne($id);
    }

    // 判断该地区是否已经开通短信功能(判断方式：是不是有该地区绑定到ISP服务商)
    $sms    = new \admin\helper\sms($register);
    $ret = $sms->checkIspByZone($id);
    if(!$ret['ret']){ // 短信有没有绑定服务商
        $data['is_sms_bind_isp'] = 0;
    } else {
        $data['is_sms_bind_isp'] = 1;
    }

    $data['id_zone'] = $id;
    $data['country'] = $country;
    $data['currency'] = $currency;
    if( isset($_GET['json']) && $_GET['json']=='1'){
        echo json_encode($data);
        exit();
    }
    // $register->get('view')->display('country/edit.twig',$data);
}
//增加下级区域
elseif($_GET['act'] && $_GET['act']=='add_son')
{
    $id = I('id');
    $D = new \admin\helper\country($register);
    //全部区域
    $zone =  $D->getAllZone();
    $data['zone'] = $zone;
    $data['zone_json'] = json_encode($zone);
    if( isset($_GET['json']) && $_GET['json']=='1'){
        echo json_encode($data);
        exit();
    }
    $register->get('view')->display('country/add_area.twig',$data);
}

//保存
elseif($_GET['act'] && $_GET['act']=='add_son_save')
{
     $id_zone = I("post.id_zone");
     $lang = I('post.title');
     $code = I('post.code');
     $error= [];
     $D = new \admin\helper\country($register);
     $zone =  $D->getAllZone();
     $zone = array_column($zone,NULL,'id_zone');
     foreach ($id_zone as $key=> $id)
     {
         if($id=="")
         {
             $error['ret'] =0;
             $error['msg'] = "区域不能为空";
             break;
         }
         else{
             if($id > 0)
             {
                 $old_id = $id;
                 $row[$key]['parent_id'] = $id;
                 $row[$key]['id_country'] = $zone[$id]['id_country'];
             }
             else{
                 $row[$key]['parent_id'] = $old_id;
                 $row[$key]['id_country'] = $zone[$old_id]['id_country'];
             }
         }
         if(!$lang[$key])
         {
             $error['ret'] =0;
             $error['msg'] = "名称不能为空";
             break;
         }
         
         $row[$key]['lang'] =  $lang[$key] ;
         $row[$key]['title'] =  $lang[$key] ;
         $row[$key]['code'] = $code[$key];
     
     }

     if(count($error)>0)
     {
         echo json_encode($error) ;
         exit;
     }
     $ret = $register->get('db')->insert('zone',$row);
     if(!$ret)
     {
         echo json_encode(['ret'=>0,'msg'=>'插入数据库失败'.$register->get('db')->last()]) ;
         exit;
     }
     echo json_encode(['ret'=>1,'msg'=>'OK']) ;
}

elseif($_GET['act'] && $_GET['act']=='save')
{
    // 判断该地区是否已经开通短信功能(判断方式：是不是有该地区绑定到ISP服务商)
    $sms    = new \admin\helper\sms($register);
    $id = isset($_POST['id']) ? $_POST['id'] : 0;
    $result = $sms->checkIspByZone($id);
    $isForceOpenSMS = isset($_POST['is_force_open_sms']) && ($_POST['is_force_open_sms'] == 'enable'); // 强制短信为真
    if (! $result['ret'] && $isForceOpenSMS) {
        echo json_encode([
                           'ret'       => 0,
                           'msg'       => '该地区没有绑定到ISP服务商，不支持短信, 请联系管理员进行配置)',
                           'errorList' => []

                       ]);
        exit;
    } 

    // 如果强制短信为真，刷新之前的产品短信
    $db->pdo->beginTransaction();
    // 更新之前该地区产品开启短信状态
    if($isForceOpenSMS){
        $product = new \admin\helper\product($register); 
        $product->updateProductForceOpenSmsByZone($id);
    }
    
    // 更新地区
    $currency = new \admin\helper\country($register);
    $ret = $currency->update($_POST,$register);

    $db->pdo->commit();
    echo  json_encode($ret);
}
elseif ($_GET['act'] && $_GET['act']=='delete'){

    $id = I('id');
    $currency = new \admin\helper\country($register);
    $ret = $currency->delete($id);
    echo json_encode($ret);
}
elseif ($_GET['act'] && $_GET['act']=='getZone'){

    $currency = new \admin\helper\country($register);
    $ret = $currency->getAllZone();
    echo json_encode($ret);
}
elseif ($_GET['act'] && $_GET['act']=='clist'){
    $currency = new \admin\helper\country($register);
    $data = $currency->getLists();
    $data['admin'] =  $_SESSION['admin'];
    if( isset($_GET['json']) && $_GET['json']=='1'){
        echo json_encode($data);
        exit();
    }
    $register->get('view')->display('country/clist.twig',$data);
}
elseif ($_GET['act'] && $_GET['act']=='cedit'){
    $id = I('id');
    $data = [];
    if($id){
        $D = new \admin\helper\country($register);
        $data['c'] = $D->gCountry($id);
    }
    $register->get('view')->display('country/cedit.twig',$data);
}
elseif ($_GET['act'] && $_GET['act']=='country_save'){
    $id = I('id');
    $data = [];
    // $data['id_country'] = $_POST['id_country'];
    // $data['ncode'] = $_POST['ncode'];
    $data['id_country'] = I('post.id_country');
    $data['ncode'] = I('post.ncode');
    $D = new \admin\helper\country($register);
    $res = $D->saveCountry($data);
    echo json_encode($res);exit();
}
elseif($_GET['act'] && $_GET['act']=='syncZone')
{
    $D = new \admin\helper\country($register);
    $res = $D->syncZone();
    echo json_encode($res);exit();
    #echo json_encode($res);exit();
}
elseif($_GET['act'] && $_GET['act']=='getZoneEmail')
{
    $filter = [];
    if(!empty($_GET['id_zone'])){
        $filter['id_zone'] = $_GET['id_zone'];
    }
    $D = new \admin\helper\country($register);
    $res = $D->getZoneEmail($filter);
    echo json_encode(['email'=>$res]);exit();
}
elseif($_GET['json'] && $_GET['json']=='1'){
    $currency = new \admin\helper\country($register);
    $data = $currency->lists();
    echo json_encode($data);
    exit();
}
else{
    $currency = new \admin\helper\country($register);
    $data = $currency->lists();
    $data['admin'] =  $_SESSION['admin'];
    $register->get('view')->display('country/index.twig',$data);
}
