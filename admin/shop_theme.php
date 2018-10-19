<?php
// +----------------------------------------------------------------------
// | ChenHK [ 店铺模板管理控制器 ]
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------
// | Team:   Cuckoo
// +----------------------------------------------------------------------
// | Date:   2018/1/2 17:49
// +----------------------------------------------------------------------

require_once 'ini.php';
use admin\helper;

//GET 请求标示
$_act_get = empty($_GET['act']) ? null : strtoupper(trim($_GET['act']));

//保存或更新
if (strcmp($_act_get, 'SAVE') == 0)
{
    $_post = $_POST;

    $data['sid']    = empty($_post['sid']) ? 0 : $_post['sid'];
    $data['title']  = empty($_post['title']) ? '' : $_post['title'];
    $data['theme']  = empty($_post['theme']) ? '' : $_post['theme'];
    $data['zone']   = $_post['zone'];
    $data['lang']   = $_post['lang'];
    $data['desc']   = $_post['desc'];

    $data['belong_id_department']   = $_post['belong_id_department'];

    $model = new  helper\shop_theme($register);
    $ret = $model->insertOrUpdate($data);

    echo json_encode($ret);
}

//编辑
else if (strcmp($_act_get, 'EDIT') == 0)
{
    $sid = empty($_GET['sid']) ? null : trim($_GET['sid']);

    $model = new helper\shop_theme($register);
    $data = [];
    if ($sid) {
        $filter['sid'] = $sid;
        $data = $model->get($filter);
        $country = new helper\country($register);
        $id_zone = $country->getAllZone();

        $data['id_zones'] = $id_zone;
    }

    echo json_encode($data);
}

//删除
else if (strcmp($_act_get, 'DELETE') == 0)
{
    $model = new  helper\shop_theme($register);

    $condition['sid']    = empty($_GET['sid']) ? 0 : trim($_GET['sid']);
    $is_del = isset($_GET['is_del']) ? trim($_GET['is_del']) : -1;
    $is_del = ($is_del != 1) && ($is_del != 0) ? -1 : $is_del;
    
    $condition['is_del'] = $is_del;

    if ($condition['sid'] == 0 || $condition['is_del'] == -1) {
        echo json_encode(['ret' => 1]);
    } else {
        $ret = $model->delete($condition);
        echo json_encode($ret);
    }
}

//主页模板选择风格
else if (strcmp($_act_get, 'SITE') == 0)
{
    $list = \lib\register::createInstance('\admin\helper\shop_theme')->getThemeByQuery();
    ajaxReturn($list);
}

//列表
else
{
    $filter =[];

    //地区查询条件关键字
    if ($_GET['region_code']) {
        $filter['zone[~]'] = '%'.trim($_GET['region_code']).'%';
    }

    //模板代号查询条件关键字
    if ($_GET['theme_code']) {
        $filter['theme[~]'] = '%'.trim($_GET['theme_code']).'%';
    }

    //语种查询
    if ($_GET['lang']) {
        $filter['lang[~]'] = '%'.trim($_GET['lang']).'%';
    }

    $model = new helper\shop_theme($register);
    $list  = $model->getLists($filter);

    echo json_encode($list);
}