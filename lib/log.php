<?php

namespace lib;
class log
{
     public $path ;  

     public function write($subPath='',$str)
     {
         $this->path= app_path.'/log/';
         $fimeName =  date('Y-m-d').'.txt';
         $output =  '['.date('Y-m-d H:i:s').']'. $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."\t\n";
         //var_dump($str);
         $output .=  (is_string($str)?$str:base64_encode(serialize($str)));
         $dir = $this->path.$subPath.'/';
         if(!is_dir($dir))
         {
             $this->addDir($dir) ;
         }
         file_put_contents($dir.'/'.$fimeName,$output,FILE_APPEND | LOCK_EX);
     }

     public function addDir($dir)
     {
            mkdir($dir,0777,true);
     }

    public function writeTmpLog($identify, $content)
    {
        if (is_array($content)) {
            $str = var_export($content, true);
        } else {
            $str = (string)$content;
        }

        $this->path= app_path.'/log/';
        $fimeName =  date('Y-m-d'). $identify .'img.txt';
        $logFile = $this->path.'/'.$fimeName;
        file_put_contents($this->path.'/'.$fimeName, $str, FILE_APPEND | LOCK_EX);
        return $logFile;
    }

}