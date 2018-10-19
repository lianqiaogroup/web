<?php

require_once 'ini.php';
use admin\helper\qiniu ;


//当前域名
$site = new \admin\helper\site($register);

/**
 * 首页保存
 */
if (isset($_POST['act']) && $_POST['act'] == 'save') {

    $domain = null;
    if (isset($_POST['domain'])) {
        $domain = $_POST['domain'];
        // if(substr($domain, 0,4) != 'www.'){
        //     $domain = 'www'.$domain;
        // }
    }

    //判断是否有可编译权限
  //  $allDomain = $site->getAllDomain();
    $product = new \admin\helper\product($register);
    $ret = $product->checkDomain($domain);
    if (!$ret) {
        $return['code'] = 0;
        $return['msg'] = '获取域名信息失败，稍后重试';
        echo json_encode($return);
        exit;
    }
    $company = new \admin\helper\company($register);
    $id_departments = $company->get_id_departments();
    if(($_SESSION['admin']['is_root']!=1) && ($_SESSION['admin']['is_admin']!=1) && (!in_array($ret['erp_department_id'], $id_departments)) ){
        $return['code'] = 0;
        $return['msg'] = '没有权限';
        echo json_encode($return);
        exit;
    }

    $data = $_POST;
    unset($data['update']);
    unset($data['act']);
    $data['logo'] = \trim(\is_string(isset($data['logo']) ? $data['logo'] : '') ? $data['logo'] : '');
    if($data['logo'] === 'null'){ //前端js 传递了字符串null过来 bug fix:http://jira.stosz.com/browse/DPZ-720
        $data['logo'] = '';
    }
    if($data['logo']) {
        $data['logo'] = qiniu::changImgDomain($data['logo']);
    }

    $data['domain'] = trim($data['domain']);
    $data['oa_id_department'] = $ret['erp_department_id'];
    $data['ad_member_id'] = $_SESSION['admin']['uid'];
    $data['ad_member'] = $_SESSION['admin']['username'];

    if($_POST['update']){

        $filter['domain'] = $domain;
        unset($data['ad_member_id']);
        unset($data['ad_member']);
        $site->updateSite($filter, $data);
    }else{
       
        $site->saveSite($data);
    }
    $data['code'] = 1;
    echo json_encode($data);
}


/**
 *  删除主页
 */
else if (isset($_GET['act']) && $_GET['act'] == 'delete') {
    $domain = $_GET['domain'];
    $data['is_del'] = $_GET['is_del'];
    $ret = $site->deleteSite($domain, $data);
    if(!empty($cache)){
        $r = $cache->hDel('KEY_IS_INDEX',$domain);//jade add 删除 哈希表某记录   
    }
    echo json_encode($ret);
}


/**
 * 首页编辑
 */
else if (isset($_GET['act']) && $_GET['act'] == 'edit') {
    $data = $site->getSiteInfo($_GET['domain']);
    $data['logo'] = qiniu::get_image_path($data['logo']);
    if( isset($_GET['json']) && $_GET['json']==1 ){
        echo json_encode($data);
    }else{
        $register->get('view')->display('/site.twig', $data);
    }
}


/**
 * 所有的模板代号
 */
else if (isset($_GET['act']) && $_GET['act'] == 'theme_code_list') {
    $themeCodeList = $site->getThemeCodeList();
    echo json_encode(['list' => $themeCodeList]);
}


/**
 *  根据域名搜索
 */
else if(isset($_GET['act']) && $_GET['act'] == 'search') {

    $filter = [];
    $id_departments = [];
    $domain = empty($_GET['domain'])?'':$_GET['domain'];

    if(isset($_REQUEST['is_del'])){
        $filter['is_del'] = empty($_REQUEST['is_del'])?0:1;
    }
    //模板代号
    if (isset($_GET['theme_code'])) {
        $filter['theme[~]'] = trim($_GET['theme_code']);
    }
    //语言
    if (isset($_GET['lang'])) {
        $filter['lang[~]'] = trim($_GET['lang']);
    }

    if(!empty($_REQUEST['domain'])){
        $filter['domain[~]'] = $_REQUEST['domain'];
    }

    $data = [];
    $data['ret'] = 0;//0表示 查询不到数据
    $data['msg'] = '查询不到数据';//0表示 查询不到数据

    if (empty($domain)) {
        $ret = $site->getSitesByPrivilege($filter);
        if ($ret) {
            $data['ret'] = 1;
            $data['msg'] = '';
        }
        $data['domainList'] = $ret;
    }else{
        // 检查域名权限
        $company = new \admin\helper\company($register);
        $id_departments = $company->get_id_departments();
        $domainPrivileges = $site->isDoaminPrivate($domain,$id_departments);
        
        if(!$domainPrivileges['code']){
            echo json_encode(['ret'=>0,'domainList'=>[],'msg'=>'没有权限']);
            exit();
        } 

        // 列表
        if (isset($_GET['key']) && $_GET['key'] == 'list') {
            $ret = $site->getSitesByPrivilege($filter);
            if ($ret) {
                $data['ret'] = 1;
                $data['msg'] = '';
            }
            $data['domainList'] = $ret; 
        } else {
            $ret = $site->getSingleSite($filter);
            if($ret){
                $data['ret'] = 1;
                $data['msg'] = '';
            }
            $data['domainList'][] = $ret;
        }
    }

    //构建菜单
    $data['filter'] = $filter;
    echo json_encode($data);exit();
}


/**
 * 默认主页设置列表
 */
else {

    //查询当前可编辑域名
    $data =  $_SESSION;
    if(($_SESSION['admin']['is_admin'] == 1 ) || ($_SESSION['admin']['is_root'] == 1 ) ){
        $data['domainList'] = $site->getAllSites();
    }
    $register->get('view')->display('/site_list.twig', $data);
}