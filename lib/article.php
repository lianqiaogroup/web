<?php

namespace lib;

class article
{

    public $table = 'article';

    public function __construct($register, $serverName)
    {
        $this->register = $register;
        $this->domain   = $serverName;
        $this->view     = new \lib\view('public/home', app_path);
    }

    public function getArticle($id)
    {
        $map = ['AND' => ['is_del' => 0, 'article_id' => $id,'domain[~]'=>"%".$_SERVER['HTTP_HOST']]];
        $ret = $this->register->get('db')->get('article', "*", $map);
        if (!$ret) {
            $this->register->get('view')->show_404();
            exit;
        }
        return $ret;
    }

}
