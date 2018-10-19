<?php

namespace admin\helper;


class ResourceHelper {

    # 文件格式转化
    const MIME_TYPE = [
        'image/gif' => ['GIF'],
        'image/png' => ['PNG'],
        'image/jpeg'=> ['JPG', 'JPEG'],
        'image/bmp' => ['BMP'],
        'video/mp4' => ['MP4'],
        'text/xml'  => ['XML'],
        'image/vnd.adobe.photoshop'   => ['PSD'],//让运维配置一下
        'image/x-vnd.adobe.photoshop' => ['PSD'],
        'image/x-photoshop'           => ['PSD'],
        'image/x-psd'                 => ['PSD'],
        'image/photoshop'             => ['PSD'],
        'application/octet-stream'    => ['PSD'] //浏览器无法识别PSD的mime type,阿里云OSS可能不会主动检测mime type,七牛会
    ];

    public static function getMimeType($suffix) {
        if(empty($suffix)){
            return 'application/octet-stream';
        }
        $suffix = \strtoupper($suffix);
        foreach(self::MIME_TYPE as $type => $suffixList) {
            if( \in_array($suffix, $suffixList) ) {
                return $type;
            }
        }
        return 'application/octet-stream';
    }

    public static function getFormat($mimeType){
        if(empty($mimeType)){
            return '';
        }
        $mimeType = strtolower($mimeType);
        foreach(self::MIME_TYPE as $type => $suffixList) {
            if( $mimeType === $type ) {
                return $suffixList[0];
            }
        }
        return 'PSD';
    }

    public static function formatSize($size = 0){
        $size = intval($size);

        if($size >= 1073741824) {
            $size = round($size / 1073741824 * 100) / 100 . 'GB';
        } elseif($size >= 1048576) {
            $size = round($size / 1048576 * 100) / 100 . 'MB';
        } elseif($size >= 1024) {
            $size = round($size / 1024 * 100) / 100 . 'KB';
        } else {
            $size = $size . '字节';
        }
        return $size;
    }
}