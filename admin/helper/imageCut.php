<?php

namespace admin\helper;

/**
 * 上传类
 */
class imageCut extends common
{
    /*
     * 裁剪图片
     * @params path 图片路径
     * @params image 图片名称
     */

    static function cut($path, $image)
    {
        $imgstream = file_get_contents($path . $image);
        $im        = imagecreatefromstring($imgstream);
        $x         = imagesx($im); //获取图片的宽
        $y         = imagesy($im); //获取图片的高
        // 缩略后的大小
        $xx        = 80;
        $yy        = 80;

        if ($x > $y) {
            //图片宽大于高
            $sx     = abs(($y - $x) / 2);
            $sy     = 0;
            $thumbw = $y;
            $thumbh = $y;
        }
        else {
            //图片高大于等于宽
            $sy     = abs(($x - $y) / 2.5);
            $sx     = 0;
            $thumbw = $x;
            $thumbh = $x;
        }
        if (function_exists("imagecreatetruecolor")) {
            $dim = imagecreatetruecolor($yy, $xx); // 创建目标图gd2
        }
        else {
            $dim = imagecreate($yy, $xx); // 创建目标图gd1
        }
        imageCopyreSampled($dim, $im, 0, 0, $sx, $sy, $yy, $xx, $thumbw, $thumbh);
        $newName = self::getCutName($image);
        $newPath = $path .'/80/' ;
        if (!is_dir($newPath)) {
            @mkdir($newPath,0777,true);
        }
        imagejpeg($dim, $newPath . $newName);
        imagedestroy($dim);
    }

    static function getCutName($image)
    {
        $image = explode('.', $image);
        return $image[0] . '_80_80.' . $image[1];
    }

}
