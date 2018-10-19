<?php
/**
 * 图片压缩操作类.
 * User: chenhongkai@stosz.com
 * Version : 1.0
 * Date: 2017/10/24
 * Time: 10:26
 */

namespace admin\helper;

class ImageCompression
{
    /**
     * 压缩图片
     * @param $Image    图片地址
     * @param $Dw       宽度
     * @param $Dh       高度
     * @param int $Type 是否生成缩略图
     * @return bool
     */
    public function geoCompression($Image, $Dw, $Dh, $Type = 1)
    {
        if (!File_Exists($Image)) {
            return False;
        }
        //如果需要生成缩略图,则将原图拷贝一下重新给$Image赋值
        if ($Type != 1) {
            Copy($Image, Str_Replace(".", "_x.", $Image));
            $Image = Str_Replace(".", "_x.", $Image);
        }
        //取得文件的类型,根据不同的类型建立不同的对象
        $ImgInfo = GetImageSize($Image);
        Switch ($ImgInfo[2]) {
            Case 1:
                $Img = @ImageCreateFromGif($Image);
                Break;
            Case 2:
                $Img = @ImageCreateFromJPEG($Image);
                Break;
            Case 3:
                $Img = @ImageCreateFromPNG($Image);
                Break;
        }
        //如果对象没有创建成功,则说明非图片文件
        if (empty($Img)) {
            //如果是生成缩略图的时候出错,则需要删掉已经复制的文件
            if ($Type != 1) {
                Unlink($Image);
            }
            return False;
        }
        //如果是执行调整尺寸操作则
        if ($Type == 1) {
            $w = ImagesX($Img);
            $h = ImagesY($Img);
            $width = $w;
            $height = $h;
            if ($width > $Dw) {
                $Par = $Dw / $width;
                $width = $Dw;
                $height = $height * $Par;
                if ($height > $Dh) {
                    $Par = $Dh / $height;
                    $height = $Dh;
                    $width = $width * $Par;
                }
            } elseif ($height > $Dh) {
                $Par = $Dh / $height;
                $height = $Dh;
                $width = $width * $Par;
                if ($width > $Dw) {
                    $Par = $Dw / $width;
                    $width = $Dw;
                    $height = $height * $Par;
                }
            } else {
                $width = $width;
                $height = $height;
            }
            $nImg = ImageCreateTrueColor($width, $height);   //新建一个真彩色画布
            ImageCopyReSampled($nImg, $Img, 0, 0, 0, 0, $width, $height, $w, $h);//重采样拷贝部分图像并调整大小
            ImageJpeg($nImg, $Image);     //以JPEG格式将图像输出到浏览器或文件
            return true;
        } else {
            $w = ImagesX($Img);
            $h = ImagesY($Img);
            $width = $w;
            $height = $h;
            $nImg = ImageCreateTrueColor($Dw, $Dh);
            if ($h / $w > $Dh / $Dw) { //高比较大
                $width = $Dw;
                $height = $h * $Dw / $w;
                $IntNH = $height - $Dh;
                ImageCopyReSampled($nImg, $Img, 0, -$IntNH / 1.8, 0, 0, $Dw, $height, $w, $h);
            } else {   //宽比较大
                $height = $Dh;
                $width = $w * $Dh / $h;
                $IntNW = $width - $Dw;
                ImageCopyReSampled($nImg, $Img, -$IntNW / 1.8, 0, 0, 0, $width, $Dh, $w, $h);
            }
            ImageJpeg($nImg, $Image);
            return true;
        }
    }
}