<?php
// +----------------------------------------------------------------------
// | ChenHK [ 模板管理控制器 ]
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------
// | Team:   Cuckoo
// +----------------------------------------------------------------------
// | Date:   2018/1/9 09:49
// +----------------------------------------------------------------------


require_once 'ini.php';


//GET 请求标示
$_act_get  = empty($_GET['act']) ? null : strtoupper(trim($_GET['act']));
$_act_post = empty($_POST['act']) ? null : strtoupper(trim($_POST['act']));


//更新
if (strcmp($_act_get, 'EDIT') == 0)
{
    $id = $_GET['id'];
    if ($id) {
        $data = $db->get('theme', "*", ['tid' => $id]);
    }

    $D = new \admin\helper\country($register);
    $id_zone = $D->getAllZone();
    $data['id_zones'] = $id_zone;
    echo json_encode($data);
}


//保存
elseif (strcmp($_act_get, 'SAVE') == 0) {

    unset($_POST['country'], $_POST['language']);

    $model = new  \admin\helper\theme($register);

    $ret = $model->save($_POST);
    echo json_encode($ret);
}


//删除
elseif (strcmp($_act_get, 'DEL') == 0) {

    $model = new  \admin\helper\theme($register);
    $data['tid'] = $_GET['tid'];
    $data['is_del'] = $_GET['is_del'];
    $ret = $model->del($data);
    echo json_encode($ret);
}


//加载
elseif (strcmp($_act_post, 'LOAD') == 0) {
    $file = file_get_contents(app_path . 'upload/theme.txt');
    $list = json_decode($file, true);
    //获取语言

    $json = file_get_contents('./template/config/theme_language');
    $decode = json_decode($json, true);
    $langFlip = array_flip($decode);
    foreach ($list['areamodel'] as $key => $val) {

        $row[$key]['theme'] = $val['theme'];
        $row[$key]['title'] = $val['title'];
        $row[$key]['author'] = $val['author'];
        $row[$key]['referto_links'] = $val['referto_links'];
        $row[$key]['style'] = $val['style'];
        $row[$key]['desc'] = $val['scene'];
        $row[$key]['belong_id_department'] = $val['belong'] ?: 0;
        //获取地区3字码
        $zone = $db->select("zone", ['code'], ['title' => $val['regions']]);

        $row[$key]['zone'] = implode(',', array_column($zone, 'code'));
        $lang = "";
        foreach ($val['lang'] as $item) {
            $lang .= $langFlip[$item] . ',';
        }

        $row[$key]['lang'] = trim($lang, ',');
    }
}


//默认列表
else {

    $filter =[];

    //地区查询
    if($_GET['region_code']){
        $zone = $_GET['region_code'];
        $filter['zone[~]'] = '%'.$zone.'%';
    }

    //模板代号查询
    if(isset($_GET['theme_code']) && $_GET['theme_code']){
        $theme = $_GET['theme_code'];
        $filter['theme[~]'] = '%'.$theme.'%';
    }

    //语种查询
    if (isset($_GET['lang']) && $_GET['lang']) {
        $lang = $_GET['lang'];
        $filter['lang[~]'] = '%'. $lang .'%';
    }

    // 上下架查询
    if( isset($_GET['is_del']) ){
        $filter['is_del'] = $_GET['is_del'];
    }

    $model = new  \admin\helper\theme($register);
    $list = $model->getThemeList($filter);

    echo json_encode($list);
}