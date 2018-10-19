<?php

namespace admin\helper;

use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use Qiniu\Processing\PersistentFop;

class qiniu
{

    public static $uploadUrl = 'http://up-z2.qiniu.com';
    public static $accessKey = '0znPKSrr3SZ4EGBme8kNqecuw_r6ClHPfY-lmnsk';
    public static $secretKey = 'SoOrKXr8X2kuBTuJ8TkfUyGrbCrEIYkOP4Euq0LY';
    public static $bucket    = 'bucket-cn'; //大陆的bucket
    public static $ext       = "-shop"; // 图片样式
    public static $imgUrlCn  = IMGURLCN; //国内
    public static $videoUrl =  VIDEOURL;//vedio加速域名
    public static $imgUrl    = IMGURL; //国外

    public static function getToken($bucket = "")
    {
        if (empty($bucket)) {
            $bucket = self::$bucket;
        }
        $auth = new Auth(self::$accessKey, self::$secretKey);
        return $auth->uploadToken($bucket);
    }

    public static function getAuth()
    {
        $auth = new Auth(self::$accessKey, self::$secretKey);

        return $auth;
    }

    public static function getKey($type, $ext)
    {
        if (empty($type)) {
            $type = "um";
        }
        if ($ext) {
            return $type . '/' . date('Y') . date('m') . date("d") . '/' . time() . rand(100, 999) . '.jpg';
        }
        else {
            return $type . '/' . date('Y') . date('m') . date("d") . '/' . time() . rand(100, 999) . '.mp4';
        }
    }
    
    /**
     * 获取资源地址
     * @param string $resourceUrl 资源地址
     * @param int $resourceType  资源类型 1:图片 2:视频 默认:1
     * @return string
     */
    public static function getResourceUrl($resourceUrl, $resourceType = 1) {

        if (empty($resourceUrl)) {
            return '';
        }
        $urlInfo = parse_url($resourceUrl);
        if (isset($urlInfo['scheme'])) {
            return $resourceUrl;
        }

        if($resourceUrl[0] !== '/'){
            $resourceUrl = '/'. $resourceUrl;
        }

        //获取资源cdn数据
        switch ($resourceType) {
            case 2://2:视频
                $cdnUrl = VIDEOURL;
                break;
            case 1://1:图片
            default:
                $cdnUrl = IMGURLCN;
                break;
        }

        return $cdnUrl . $resourceUrl;
    }

    /**
     * 获取图片url
     * @param string $imageUrl 图片地址
     * @return string  图片url
     */
    public static function get_image_path($imageUrl) {
        return self::getResourceUrl($imageUrl, 1);
    }

    /**
     * 获取视频地址
     * @param string $videoUrl 视频地址
     * @return string 视频地址
     */
    public static function get_video_path($videoUrl) {
        return self::getResourceUrl($videoUrl, 2);
    }

    /**
     * 获取资源地址为绝对地址的内容
     * @param string $content  内容
     * @return string $content 资源地址为绝对地址的内容
     */
    public static function get_content_path($content) {

        $replaceData = [
            '$videoUrl/' => self::getResourceUrl('/', 2), //视频
            '$imageUrl/' => self::getResourceUrl('/', 1), //图片
        ];
        return strtr($content, $replaceData);
    }

    /**
     * 将资源地址还原为相对地址
     * @param string $imgUrl 资源地址
     * @return string $imgUrl 资源地址相对地址
     */
    public static function changImgDomain($imgUrl) {
        $imgUrl = str_replace('class="lazyload"', "", $imgUrl);
        return preg_replace_callback(
                [
            "/" . str_replace('/', '\/', IMGURL) . "/x",
            "/" . str_replace('/', '\/', IMGURLCN) . "/x",
            "/" . str_replace('/', '\/', VIDEOURL) . "/x",
                ], function($ms) {
            return "";
        }, $imgUrl
        );
    }

    /**
     * 将内容资源的绝对地址 还原为 可替换的相对地址
     * @param string $url 内容
     * @return string $url 还原后的内容
     */
    public static function ContentDomain($url) {

        $replaceData = [
            'class="lazyload"' => '',
            VIDEOURL => '$videoUrl',
            IMGURLCN => '$imageUrl',
            IMGURL => '$imageUrl',
        ];
        return strtr($url, $replaceData);
    }

    public static function upload($type)
    {
        $info     = ['state' => 'FAIL'];
        $file     = $_FILES['upfile'];
        // 要上传文件的本地路径
        $filePath = $file['tmp_name'];
        $ext      = self::$ext;

        $filetype = $file['type'];
        
        $isVideo =0;
        $url = self::$imgUrlCn;
        if(strstr($filetype, 'video')){
            $url = self::$videoUrl;
            $isVideo =1;
        }
        
        if (!strstr($filetype, 'image')) {
            $ext = '';
        }
        else {
            if($file['size'] > 5 *1024*1024){
                $info['msg'] = '上传图片超过5M';
                return $info;
            }
            $filetype = 'application/octet-stream';
        }
        // 上传到七牛后保存的文件名
        $key       = self::getKey($type, $ext);
        $token     = self::getToken();
        // 初始化 UploadManager 对象并进行文件的上传。
        $uploadMgr = new UploadManager();
        // 调用 UploadManager 的 putFile 方法进行文件的上传。
        list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath, null, $filetype);
        if($err !=null)
        {
            return $info;
        }
        $filename = $ret['key'];
        $i = '';
        if(substr($filename,0,1) != '/') $i = '/';

        //如果是视频要转码
        if($isVideo){
            //判断是否需转编码
            $uri = self::$videoUrl.$i.$filename.'?avinfo';
            $isChange = self::isVideoChange($uri);

            if($isChange)
            {
                $ret = self::videoCodeChange($filename);

                if(!$ret['ret']){
                    return $info;
                }
                 $filename = $ret['newName'];
            }
        }
        $info     = array(
            "state"    => 'SUCCESS',
            "url"      => $url .$i. $filename . $ext,
            "title"    => $key,
            "original" => $file['name'],
            "type"     => 'jpg',
            "size"     => $file['size']
        );
        return $info;
    }

    /**
     * @param $url 文件路径
     * @return array
     */
    public static function videoCodeChange($url){
        $auth = self::getAuth();
        //转码是使用的队列名称。 https://portal.qiniu.com/mps/pipeline
        $pipeline = 'zhuanma';
        $force = 1;
        //转码完成后通知到你的业务服务器。
        $notifyUrl = '';

        $bucket = self::$bucket;
        $pfop = new PersistentFop($auth, $bucket,$pipeline,$notifyUrl,$force);

        //另存新文件
        $url_array = explode('/',$url);
        $lastExt  = end($url_array);
        list($oldName,$ext) = explode('.',$lastExt);
        $newName = date("YmdHis").rand(0,100).'.'.$ext;
        $name = str_replace($lastExt,$newName,$url);
         //要进行转码的转码操作。 http://developer.qiniu.com/docs/v6/api/reference/fop/av/avthumb.html
        $fops = "avthumb/mp4/vcodec/libx264|saveas/". \Qiniu\base64_urlSafeEncode($bucket .":".$name);
        list($id, $err) = $pfop->execute( $url, $fops);
        if ($err != null) {
            return ['ret'=>0,'msg'=>$err];
        }
        //删除原有文件
        $ret = self::deleteFiles($url);
        if(!$ret['ret']){
            //return ['ret'=>0,'msg'=>$err];
        }
        return ['ret'=>1,'newName'=>$name];

    }

    public  static  function isVideoChange($url){

        $ret = file_get_contents($url);

        $info = json_decode($ret,true);

        $code = $info['streams'][0]['codec_name'];

        if($code =='h264'){
            return false;
        }

        return true;

    }

    public static  function uploadImgTxt($type,$file,$is_del=true){
        $info     = ['state' => 'FAIL'];
        // 要上传文件的本地路径
        $ext      = self::$ext;
        $url = self::$imgUrlCn;
        $filetype = 'application/octet-stream';
        // 上传到七牛后保存的文件名
        $key       = self::getKey($type, $ext);
        $token     = self::getToken();
        // 初始化 UploadManager 对象并进行文件的上传。
        $uploadMgr = new UploadManager();
        // 调用 UploadManager 的 putFile 方法进行文件的上传。
        list($ret, $err) = $uploadMgr->putFile($token, $key, $file, null, $filetype);
        if ($err == null) {
            $filename = $ret['key'];
            $i = '';
            if(substr($filename,0,1) != '/') $i = '/';

            $info     = array(
                "state"    => 'SUCCESS',
                "url"      => $url .$i. $filename . $ext,
                "title"    => $key,
                //"original" => $file['name'],
                "type"     => 'jpg',
                //"size"     => $file['size']
            );
        }
        if($is_del)
        {
            //删除已加水印的图片
            unlink($file);
        }

        return $info;
    }

    /**
     * @param $url
     * @return array
     * 删除空间文件
     */
    public static function deleteFiles($url)
    {
        $auth = self::getAuth();
        $bucketManager = new \Qiniu\Storage\BucketManager($auth);
        $err = $bucketManager->delete(self::$bucket, $url);

        if($err)
        {
            return ['ret'=>0,'msg'=>$err];

        }
        return ['ret'=>1];
    }
}
