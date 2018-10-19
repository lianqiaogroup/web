<?php

namespace admin\helper;

use lib\register;

class Material extends common {

    const TableName = 'material';

    private $host ;

    private static function gmt_iso8601($time){
        $dtStr = date("c", $time);
        $mydatetime = new \DateTime($dtStr);
        $expiration = $mydatetime->format(\DateTime::ISO8601);
        $pos = strpos($expiration, '+');
        $expiration = substr($expiration, 0, $pos);
        return $expiration."Z";
    }

    public static function getConfig(){
       $config = register::getInstance('config')->get('Aliyun');
       if($config && array_key_exists('OSS',$config)){
           $oss = $config['OSS'];

           $now = time();
           $end = $now + $oss['Expire'];
           $expiration = self::gmt_iso8601($end);

           list($min,$max) = $oss['SizeRange'];
           $conditions[] = ['content-length-range',$min,$max];

           $conditions[] = ['starts-with','$key',$oss['Dir']];

           $policy = json_encode(['expiration'=>$expiration,'conditions'=>$conditions]);
           $base64_policy = base64_encode($policy);
           $string_to_sign = $base64_policy;
           $signature = base64_encode(hash_hmac('sha1',$string_to_sign,$oss['AccessKeySecret'],true));

           return [
               'accessid'=>$oss['AccessKeyID'],
               'host' => $oss['EndPoint'],
               'policy' => $base64_policy,
               'signature' => $signature,
               'bucket' => $oss['Bucket'],
               'expire' => $end,
               'dir' => $oss['Dir']
           ];
       }
    }

    #获取列表
    public function getlist($filter,$pageno = 1,$pageSize = 20){

        $config = register::getInstance('config')->get('Aliyun');

        $total = $this->total($filter);

        $filter['ORDER'] = ['id'=>'DESC'];
        $filter['LIMIT'] = [$pageSize*($pageno-1),$pageSize];
        $data = $this->db->select('material',['id','mtype','mtag','thumb','format','size','ad_member','add_time'],$filter);
        $data = array_map(function($rs) use ($config) {
           $rs['size'] = $this->formatSize($rs['size']);
           $rs['format'] = $this->getformat($rs['format']);
           $rs['thumb'] = $config['OSS']['EndPoint'].'/'. $rs['thumb'] ;
           return $rs;
        },$data);
        return [
            'page' => $pageno,
            'pagesize' => $pageSize,
            'pagetatal' => $total,
            'lists' => $data
        ];
    }

    #获取素材总数
    public function total($filter = []){
        if(count($filter) > 0){
            return $this->db->count('material',$filter);
        }else{
            return $this->db->count('material');
        }
    }

    #获取标签
    public function fetchTag(){
        return $this->db->select('material_tag',['id','tagname']);
    }

    #保存数据
    public function insert($mtype,$mtag,$datas){

        if(is_array($datas) && count($datas) > 0){
            $res = [];
            foreach ($datas as $rs){
                $rs['mtype'] = $mtype;
                $rs['mtag'] = $mtag;
                //$rs['thumb'] = $rs['thumb'];
                //$rs['format'] = $rs['format'];
                //$rs['size'] = $rs['size'];
                $rs['ad_member'] =$_SESSION['admin']['username'];
                $rs['add_time'] = date('Y-m-d H:i:s',time());

                unset($rs['uid']);
                $res[] = $rs;
            }
            $result = $this->db->insert('material',$res);
            if($result){
                return true;
            }else{
                return false;
            }
        }
        return false;
    }

    # 文件格式转化
    public function convertFileType($str) {
        switch (strtoupper($str)) {
            case 'GIF':
                return 'image/gif';
                break;
            case 'MP4':
                return 'video/mp4';
                break;
            case 'PNG':
                return 'image/png';
                break;
            case 'JPG';
                return 'image/jpeg';
                break;
            case 'BMP':
                return 'image/bmp';
                break;
            default:
                return 'application/octet-stream';
        }
    }

    private function formatSize($size = 0){
        $size = intval($size);

        $size = floatval($size) / 1024;
        if(($size - 1024) > 0 ){
            $size = $size / 1024 ;
            return round($size,2).'MB';
        }else{
            return round($size,2).'KB';
        }
    }

    private function getformat($format){
        switch ($format){
            case 'image/png':
                return 'PNG';
                break;
            case 'image/jpeg';
                return 'JPG';
                break;
            case 'image/gif':
                return 'GIF';
                break;
            case 'text/xml':
                return 'XML';
                break;
            case 'image/bmp':
                return 'BMP';
                break;
            case 'video/mp4':
                return 'MP4';
                break;
            default:
                return 'FILE';
                break;
        }
    }

}