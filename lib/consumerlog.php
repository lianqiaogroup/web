<?php

namespace lib;
class consumerlog
{
     public $path ;  

     public function write($subPath='',$str)
     {
         $this->path= app_path.'/log/';
         $fimeName =  date('Y-m-d').'.txt';
         $output =  '['.date('Y-m-d H:i:s').']'. json_encode($_SERVER)."\t\n";
         $output .=  $str;
         $dir = $this->path.$subPath.'/';
         if(!is_dir($dir))
         {
             $this->addDir($dir) ;
         }
         file_put_contents($dir.'/'.$fimeName,$output,FILE_APPEND | LOCK_EX);
     }

     public function addDir($dir)
     {
            mkdir($dir,0770,true);
     }

}