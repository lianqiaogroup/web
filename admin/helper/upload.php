<?php

namespace admin\helper;

class upload{

    /**
     * [$uploadFile 上传图片文件]
     * @var null
     */
    private $uploadFile = null;

    /**
     * [setUploadFile 设置附加路径]
     * @param [type] $filePath []
     */
    public function setUploadFile($picFile)
    {
        $this->uploadFile = $picFile;
        return true;
    }

    public function  uploadImg(){
        // 设置上传图片文件
        $uFile = $_FILES['upfile'];

        //上传图片
        $ret['state'] = 'FAIL';
        //判断size
        if(!$this->checkFileSize($uFile)){
            $ret['msg'] = '上传失败，图片大小超过5M';
            return $ret;
        }
        $rootPath = app_path."upload/";
        $path = 'origin/'. date('y-m-d',time()).'/';

        $uploadHandler = new \Sirius\Upload\Handler($rootPath.$path);
        $uploadHandler->setSanitizerCallback(function($name){
            return date('ymdHis') . preg_replace('/[^a-z0-9\.]+/', '-', strtolower($name));
        });
        $result = $uploadHandler->process($uFile);
        $fileName = '/upload/'.$path.$result->name;
        if($fileName)
        {
            $result->confirm();
            $ret['size'] = $result->size;
            $ret['name'] = $result->name;
            $ret['type'] = $result->type;
            $ret['fileName'] = $fileName;
            $ret['original_name'] = $result->original_name;
            $ret['state'] = 'SUCCESS';
        }

        return $ret;
    }


    //加水印
    public function imgTxt($file,$txt){
        $rootPath = app_path."upload/watermark/";
        $path = date('y-m-d',time()).'/';
        $img = new ImageHander($rootPath.$path,$txt);
        if (!empty($this->uploadFile)) {
            $files = $img->createImageMark($file);
        } else {
            $files = $img->createImageMark(app_path.$file);
        }
        return $files;
    }

    public function checkFileSize($file){
        $maxSize = 5 *1024*1024;//b

        if($file['size'] > $maxSize){
            return false;
        }

       return true;
    }
}