<?php

require_once 'ini.php';

// 列表get  保存post  删除get  编辑查看get
//类型判断
$actGET = isset($_GET['act']) ? $_GET['act'] : null;
$actPOST = isset($_POST['act']) ? $_POST['act'] : null;

//初始化注册类
$category = new \admin\helper\category($register);

if ($actGET && $actGET == 'preview') {
    //根据分离ID 查看当前分类的所有产品
    $category_id = isset($_GET['category_id'])? trim($_GET['category_id']):null;
    $domain      = isset($_GET['domain']) ? trim($_GET['domain']): null;

    if (empty($category_id) || empty($domain)) {
        echo json_encode(['ret'=>0, 'msg'=>'分类ID为空']);
        exit;
    }

    //根据分类ID 查询当前下的产品数据
    $ret = $category->getProductListsWithCategoryID($category_id, $domain);
    echo json_encode(['ret'=>1, 'msg'=>'查询成功', 'data'=>$ret]);
} else if ($actGET && $actGET == 'edit') {
    //编辑获取产品信息
    $cid = $_GET['cid'];
    $data = [];
    if ($cid) {
        $data = $category->getOneCategory($cid);
        if (!$data) {
            echo json_encode(['ret' => 0, 'msg' => '不存在该分类', 'data' => []]);
            exit;
        }
        echo json_encode(['ret' => 1, 'msg' => '成功', 'data' => $data]);
    } else {
        echo json_encode(['ret' => 0, 'msg' => '不存在该分类', 'data' => []]);
    }
} else if ($actPOST && $actPOST == 'save') {
    //编辑更新产品
    $dataPost = $_POST;

    $cid        = $dataPost['cid'];
    $domain     = $dataPost['domain'];
    if(substr($domain, 0,4) != 'www.'){
        $domain = 'www.'.$domain;
    }
    $parent_id  = isset($dataPost['parent_id']) ? $dataPost['parent_id'] : 0;
    $title      = trim($dataPost['title']);
    $title_zh   = trim($dataPost['title_zh']);
    $sort       = isset($dataPost['sort']) ? trim($dataPost['sort']) : 1;
    $is_del     = isset($dataPost['is_del']) ? $dataPost['is_del'] : 0;
    $aid        = $_SESSION['admin']['uid'];
    $add_time   = date('Y-m-d H:i:s', time());

    if (empty($title)) {
        echo json_encode(['ret' => 0, 'msg' => '标题不能为空', 'date' => []]);
        exit;
    }

    //封装保存数据
    $data['domain'] = $domain;
    $data['title'] = $title;
    $data['title_zh'] = $title_zh;
    $data['sort'] = $sort;
    $data['is_del'] = $is_del;
    $data['parent_id'] = $parent_id;
    $data['add_time'] = $add_time;

    //判断是否设置了主页
    $site = new \admin\helper\site($register);
    $filter['domain'] = $domain;
    $siteRes = $site->getSearchSite($filter);
    if (empty($siteRes)) {
        echo json_encode(['ret' => 0, 'msg' => '未设置主页 请先设置主页后再添加产品', 'date' => []]);
        exit;
    }

    //判断保存或者更新是否成功
    $ret = $category->saveCategory($cid, $data);
    echo json_encode($ret);
} else if ($actGET && $actGET == 'delete') {
    //get删除产品
    $cid = $_GET['cid'];
    $data['is_del'] = $_GET['is_del'];
    $ret = $category->deleteCategory($cid, $data);
    echo json_encode($ret);
} else if ($actGET && $actGET == 'ajaxCategory') {
    $domain = isset($_GET['domain']) ? trim($_GET['domain']) : null;
    if (empty($domain)) {
        echo json_encode(['ret' => 0, 'msg' => '请求域名为空', 'data' => []]);
    } else {
        $ret = $category->getCategory($domain);
        echo json_encode(['ret' => 1, 'msg' => '成功', 'data' => $ret]);
    }
} else {
    //查询产品列表
    $domain = isset($_GET['domain']) ? trim($_GET['domain']) : null;
    $data = [];
    if (!empty($domain)) {
         // 检查域名权限
        $company = new \admin\helper\company($register);
        $id_departments = $company->get_id_departments();
        $site = new \admin\helper\site($register);
        $domainPrivileges = $site->isDoaminPrivate($domain,$id_departments);
        if(!$domainPrivileges['code']){
            echo json_encode([]);
            exit;
        }
        $data = $category->getAllCategory($domain, $_GET['p']);
    }
    echo json_encode(['ret' => 1, 'msg' => '成功', 'data' => $data]);
}