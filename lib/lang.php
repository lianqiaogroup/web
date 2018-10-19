<?php
namespace lib;

class lang{

    public $theme;
    public $lang ;
    public function __construct($lang,$theme)
    {
              $this->theme =$theme;
              $this->lang = $lang;
    }

    public function getLang($lang ='', $theme ='')
    {

        $_LANG =[];
        if($lang)
        {
            $this->lang = $lang;
        }
        if($theme)
        {
            $this->theme = $theme;
        }
        //引入通用的语言包
        if(file_exists(app_path.'/lang/'.$this->lang.'.php') )
        {
            require app_path.'/lang/'.$this->lang.'.php';
        }

        if(file_exists(app_path.'/theme/'.$this->theme.'/lang/'.$this->lang.'.php') )
        {
            require  app_path.'/theme/'.$this->theme.'/lang/'.$this->lang.'.php';
        }
        if($this->theme=='home'&&file_exists(app_path.'/public/'.$this->theme.'/lang/'.$this->lang.'.php') )
        { 
            require  app_path.'/public/'.$this->theme.'/lang/'.$this->lang.'.php';
        }

        return $_LANG;
    }
}