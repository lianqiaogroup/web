<?php

require_once 'ini.php';

if ($_GET['act'] && $_GET['act'] == 'edit') {
    $product = new \admin\helper\article($register);
    $uid = $_GET['article_id'];
    $data = $product->getOneArticle($uid);
    $error = [];
    if ($data['is_del']) {
        array_push($error, ['title' => 'FAIL：此文章已删除，禁止修改。']);
        $data['readonly'] = 1;
    }
    $data['error'] = $error;
    $data['admin'] = $_SESSION['admin'];
    $data['content'] = \admin\helper\qiniu::get_content_path($data['content']);

    if (isset($_GET['json']) && $_GET['json'] == 1) {
        echo json_encode($data);
    } else {
        $register->get('view')->display('article.twig', $data);
    }
}

elseif ($_POST['act'] && $_POST['act'] == 'save') {
    $data = I('post.');

    $article_id = $data['article_id'];
    unset($data['act']);
    if (!$article_id) {
        $data['add_time'] = date("Y-m-d H:i:s");
    }

    if(!trim($data['title'])){
        echo json_encode(['ret'=>0,'msg'=>'标题不能为空']);
       exit;
    }
    if(!trim($data['content'])){
        echo json_encode(['ret'=>0,'msg'=>'内容不能为空']);
        exit;
    }
    $data['content'] = \admin\helper\qiniu::ContentDomain($_POST['content']);

    $data['aid'] = $_SESSION['admin']['uid'];
    $product = new \admin\helper\article($register);
    $ret = $product->saveArticle($data);
    echo json_encode($ret);
}

elseif ($_POST['act'] && $_POST['act'] == 'del') {
    $uid = $_POST['article_id'];
    $data['is_del'] = $_POST['is_del'];
    $product = new \admin\helper\article($register);
    $ret = $product->deleteArticle($uid, $data);

    echo json_encode($ret);
}

elseif ($_GET['act'] && $_GET['act'] == 'delete') {
    $uid = $_GET['article_id'];
    $data['is_del'] = $_GET['is_del'];
    $product = new \admin\helper\article($register);
    $ret = $product->deleteArticle($uid, $data);
    echo json_encode($ret);
}

elseif ($_POST['act'] && $_POST['act'] == "sort") {

    $article_id = I('post.article_id');
    $sort = I("post.sort");
    $product = new \admin\helper\article($register);
    $ret = $product->sort($article_id, $sort);

    echo json_encode($ret);
}

elseif ($_GET['act'] && $_GET['act'] == "check") {

    $ret = ['code' => 0];
    $domain = I('get.domain');
    $product = new \admin\helper\site($register);
    $data = $product->getAllSite();
    if (in_array($domain, $data)) {
        $ret = ['code' => 1];
    }
    echo json_encode($ret);
}

else {
    $domain = isset($_GET['domain']) ? trim($_GET['domain']) : null;
    $data = $_SESSION;;
    if (!empty($domain)) {
        $product = new \admin\helper\article($register);
        $data = $product->getAllArticle($domain);
    }
    $data['domain'] = $domain;
    if (isset($_GET['json']) && $_GET['json'] == '1') {
        echo json_encode($data);
    } else {
        $register->get('view')->display('article_list.twig', $data);
    }
}