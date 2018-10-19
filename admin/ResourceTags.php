<?php
/**
 * Created by PhpStorm.
 * User: zdb
 * Date: 2018/7/13
 * Time: 9:52
 */

require_once __DIR__ . '/ini.php';

$actionName = isset($_GET['act']) ? trim($_GET['act']) : (isset($_POST['act']) ? trim($_POST['act']) : '');
if(empty($actionName)){
    ResourceTags::notFound();
}else{
//    if( !isset($_SESSION, $_SESSION['admin'], $_SESSION['admin']['uid']) || 0 == intval($_SESSION['admin']['uid'])
//        || 0 == intval($_SESSION['admin']['id_department'])
//    ){
//        \headers_sent() OR header('Content-type: application/json; charset=utf-8');
//        exit(\json_encode(['code'=>400, 'msg'=>'用户ID为0 或 部门ID为0, 请重新登陆']));
//    }

    echo ResourceTags::$actionName();
    exit;
}


class ResourceTags
{
    public static function __callStatic($name, $arguments)
    {
        \headers_sent() OR header('Content-type: application/json; charset=utf-8');
        return \json_encode(['code'=>400, 'msg'=>'method not exists']);

        /*header("HTTP/1.1 404 Not Found");
        header("Status: 404 Not Found");
        header('Refresh: 10; url=/');
        echo '<h1>404 Not Found</h1>';
        exit;*/
    }

    public static function create()
    {
        /**
         * @var \Medoo\Medoo $db
         */
        $db = \lib\register::getInstance('db');
        \headers_sent() OR header('Content-type: application/json; charset=utf-8');

        if(\strtoupper($_SERVER['REQUEST_METHOD'] != 'POST')){
            return \json_encode([
                'code' => 400,
                'msg'  => 'request method not allowed'
            ]);
        }

        //标签名称
        $tagName = (isset($_POST['tag_name']) && is_string($_POST['tag_name'])) ? \trim((string) $_POST['tag_name']) : '';
        if(\mb_strlen( $tagName ) == 0){
            return \json_encode([
                'code' => 400,
                'msg'  => '请填写标签名称'
            ]);
        }
        if(\mb_strlen( $tagName ) > 254){
            return \json_encode([
                'code' => 400,
                'msg'  => '标签名称不得大于254个字符'
            ]);
        }

        //查询是否有重复的标签名称
        $tagInfo = $db->get('resource_tags', '*', ['tag_name' => $tagName]);
        if($tagInfo){
            return \json_encode([
                'code' => 400,
                'msg'  => '已存在重复的标签',
                'tagInfo' => $tagInfo
            ]);
        }

        //插入数据库
        $tagId = $db->insert('resource_tags', [
            'oa_uid'     => $_SESSION['admin']['uid'],
            '#create_at' => 'NOW()',
            'tag_name'   => $tagName,//标签名称
        ]);
        if($tagId){
            $tagInfo = $db->get('resource_tags', '*', ['tag_id' => $tagId]);
            return \json_encode([
                'code' => 200,
                'msg'  => '成功添加标签: '. $tagName,
                'tagInfo' => $tagInfo
            ]);
        }else{
            $errorInfo = $db->error();
            return \json_encode([
                'code' => 400,
                'msg'  => '添加失败: '.$errorInfo[2]
            ]);
        }
    }


    public static function query()
    {
        /**
         * @var \Medoo\Medoo $db
         */
        $db = \lib\register::getInstance('db');
        \headers_sent() OR header('Content-type: application/json; charset=utf-8');

        if(\strtoupper($_SERVER['REQUEST_METHOD'] != 'GET')){
            return \json_encode([
                'code' => 400,
                'msg'  => 'request method not allowed'
            ]);
        }

        //分页参数
        $limit  = isset($_GET['limit']) ? \min(1e5, \max(10, (int) $_GET['limit'])) : 20;
        $offset = isset($_GET['offset']) ? \max(0, (int) $_GET['offset']) : 0;
        if(isset($_GET['p'])){
            $page = \max(1, (int) $_GET['p']);
            $offset = $limit * ($page - 1);
        }else{
            $page = \ceil($offset / $limit);
        }
        $where = [
            'AND' => []
        ];

        //用户ID
        $searchUid = (isset($_POST['search_uid']) && is_string($_POST['search_uid'])) ? \trim((string) $_POST['search_uid']) : '';
        if(\mb_strlen( $searchUid ) > 0){
            $where['AND']['oa_uid'] = $searchUid;
        }

        //标签ID
        $tagId = (isset($_POST['tag_id']) && is_string($_POST['tag_id'])) ? \trim((string) $_POST['tag_id']) : '';
        if(  ((int)$tagId) > 0 ){
            $where['AND']['tag_id'] = $tagId;
        }

        //标签名称
        $tagName = (isset($_POST['tag_name']) && is_string($_POST['tag_name'])) ? \trim((string) $_POST['tag_name']) : '';
        if(\mb_strlen( $tagName ) > 0){
            $where['AND']['tag_name[~]'] = $tagName;
        }

        if(empty($where['AND'])){
            unset($where['AND']);
        }

        //联表
        $join = ["[>]oa_users" => ['resource_tags.oa_uid' => 'uid']];
        //字段
        $fields = [
            'resource_tags.tag_id','resource_tags.tag_name','resource_tags.create_at','resource_tags.update_at',
            'resource_tags.oa_uid',
            'oa_users.name_cn', 'oa_users.id_department', 'oa_users.department', 'oa_users.company_id'
        ];
        //总条数
        $count = $db->count('resource_tags', $join, 'resource_tags.tag_id', $where);
        $where['ORDER'] = ['resource_tags.tag_id' => 'DESC'];
        $where['LIMIT'] = [$offset, $limit];
        //数据列表
        $outputAdList = $db->select('resource_tags', $join, $fields, $where);
        $errrorInfo = $db->error();
        if($errrorInfo[0] !== '00000'){

            return \json_encode([
                'code' => 400,
                'msg'  => '无法获取标签列表: '.$errrorInfo[2],
                'count' => $count,
                'limit' => $limit,
                'offset'=> $offset,
                'page'  => $page,
                'pageCount'  => \ceil($count / $limit),
                'filter' => $where['AND'] ?: [],
                'tagsList' => []
            ]);
        }

        return \json_encode([
            'code' => 200,
            'msg'  => '成功获取标签列表',
            'count' => $count,
            'limit' => $limit,
            'offset'=> $offset,
            'page'  => $page,
            'pageCount'  => \ceil($count / $limit),
            'filter' => $where['AND'] ?: [],
            'tagsList' => $outputAdList
        ]);
    }


    public static function update()
    {
        /**
         * @var \Medoo\Medoo $db
         */
        $db = \lib\register::getInstance('db');
        \headers_sent() OR header('Content-type: application/json; charset=utf-8');

        if(\strtoupper($_SERVER['REQUEST_METHOD'] != 'POST')){
            return \json_encode([
                'code' => 400,
                'msg'  => 'request method not allowed'
            ]);
        }

        //标签ID
        $tagId = (isset($_POST['tag_id']) && is_string($_POST['tag_id'])) ? \trim((string) $_POST['tag_id']) : '';
        if( \mb_strlen( $tagId ) == 0 || ((int)$tagId) < 0 ){

            return \json_encode([
                'code' => 400,
                'msg'  => '标签ID不能为空'
            ]);
        }
        $tagInfo = $db->get('resource_tags', '*', ['tag_id' => $tagId]);
        if( ! $tagInfo ) {
            return \json_encode([
                'code' => 400,
                'msg'  => '无法从数据库中找到该标签记录 #'.$tagId
            ]);
        }

        //标签名称
        $tagName = (isset($_POST['tag_name']) && is_string($_POST['tag_name'])) ? \trim((string) $_POST['tag_name']) : '';
        if(\mb_strlen( $tagName ) == 0){
            return \json_encode([
                'code' => 400,
                'msg'  => '请填写标签名称'
            ]);
        }
        if(\mb_strlen( $tagName ) > 254){
            return \json_encode([
                'code' => 400,
                'msg'  => '标签名称不得大于254个字符'
            ]);
        }

        //查询是否有重复的标签名称
        if( $tagName != $tagInfo['tag_name'] ){
            $exists = $db->get('resource_tags', 'tag_id', ['tag_name' => $tagName]);
            if($exists){
                return \json_encode([
                    'code' => 400,
                    'msg'  => '已存在重复的标签',
                    'tagInfo' => $tagInfo
                ]);
            }

            //修改数据库
            $success = $db->update('resource_tags', [
                '#update_at' => 'NOW()',
                'tag_name'   => $tagName,//标签名称
            ], ['tag_id' => $tagInfo['tag_id'] ]);
            if($success) {
                return \json_encode([
                    'code' => 200,
                    'msg' => '成功修改标签名称'
                ]);
            }else {
                $errorInfo = $db->error();
                return \json_encode([
                    'code' => 400,
                    'msg' => '编辑失败: '.$errorInfo[2],
                ]);
            }
        }else{
            return \json_encode([
                'code' => 200,
                'msg' => '保存成功'
            ]);
        }
    }


    public static function delete()
    {
        /**
         * @var \Medoo\Medoo $db
         */
        $db = \lib\register::getInstance('db');
        \headers_sent() OR header('Content-type: application/json; charset=utf-8');

        if(\strtoupper($_SERVER['REQUEST_METHOD'] != 'POST')){
            return \json_encode([
                'code' => 400,
                'msg'  => 'request method not allowed'
            ]);
        }

        //标签
        $tagId = (isset($_POST['tag_id']) && is_string($_POST['tag_id'])) ? \trim((string) $_POST['tag_id']) : '';
        if( \mb_strlen( $tagId ) == 0 || ((int)$tagId) < 0 ){

            return \json_encode([
                'code' => 400,
                'msg'  => '标签ID不能为空'
            ]);
        }
        $tagInfo = $db->get('resource_tags', '*', ['tag_id' => $tagId]);
        if( ! $tagInfo ) {
            return \json_encode([
                'code' => 400,
                'msg'  => '无法从数据库中找到该标签记录 #'.$tagId
            ]);
        }

        //删除数据
        $success = $db->delete('resource_tags', ['tag_id' => $tagInfo['tag_id'] ] );
        if($success) {
            $db->delete('resource_tags_assoc', ['tag_id' => $tagInfo['tag_id'] ] );//比较耗费时间
            return \json_encode([
                'code' => 200,
                'msg' => '成功删除标签'
            ]);
        }else {
            $errorInfo = $db->error();
            return \json_encode([
                'code' => 400,
                'msg' => '删除标签失败: '.$errorInfo[2],
            ]);
        }
    }
}