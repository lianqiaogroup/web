<?php
namespace lib;


class config{

    private $config;

    public function __construct()
    {
        $file = app_path.'/config/config.php';
        $fileDev = app_path.'/config/dev.php';
        if(file_exists($file)){

            $config = include $file;

        }else{
            if(!file_exists($fileDev)){
                throw  new \exception('配置文件不存在');
            }
            $config = include $fileDev;
        }
        $this->config = $config;


    }
    /**
     * @param $key
     * @return mixed
     * 支持 a.b.c获取单个配置
     */
    public function get($key=""){

        $config = $this->config;
        if(empty($key)){

            return $config;
        }

        if(strpos($key,'.') !==false){
            $keys = explode('.',$key);
            foreach ($keys as $k=>$value){
                $config = $config[$value];
            }
            return $config;
        }else{
            return $config[$key];
        }

    }
}