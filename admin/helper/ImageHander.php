<?php
/**
 * 图片水印操作类 (v2.0).
 * User: lixianfei@stosz.com
 * Version : 2.0
 * Date: 2017/09/20
 * Time: 13:46
 *
 * -------------------------------------------------------------------------
 * log-2017/9/20
 * (1) 新增文字水印新方法 makeTextMarkByBackground(): 在文字水印基础上增加了背景颜色
 * (2) 重写了 createimageMark()
 * (3) 构造函数参数新增了字体背景透明度
 * --------------------------------------------------------------------------
 */

namespace admin\helper;

class ImageHander {

    protected $font = "./ttf/msyh.ttf";         //字体

    protected $imagesource = null;          //图片资源
    protected $imagesize = 0;               //图片大小
    protected $imagewidth = 0;              //宽度
    protected $imageheight = 0;             //高度
    protected $imageinfo = null;            //图片信息
    protected $imagemime;                   //图片MIME

    protected $mark_type = 'text';          //水印类型 text:文本 image:图片
    protected $mark_alpha = 70;             //水印透明度
    protected $mark_point = 0.25;           //水印基数点
    protected $mark_words = "";             //水印信息
    protected $mark_offset = 0;            //水印偏移量
    protected $mark_fontsize = 18;          //字体大小
    protected $mark_angle = 30;             //偏移角度
    protected $mark_bg_alpha = 50;          //水印背景透明度 (v2.0新增)

    public $savepath = "./";                //图片保存路径
    public $savename = "";                  //保存名称

    protected $imagetarget = null;          //目标图片对象 (v2.0新增)

    public function __construct($savepath,$mark = "",$type = "text",$alpha = 30,$angle = 45,$point = 0.25,$fontsize = 19,$bg_alpha = 75)
    {
        $this->mark_alpha = $alpha;
        $this->mark_point = $point;
        $this->mark_type = $type;
        $this->mark_words = $mark;
        $this->mark_fontsize = $fontsize;
        $this->savepath = $savepath;
        $this->mark_angle = $angle;
        $this->mark_bg_alpha = $bg_alpha;
        $this->savename = Date("YmdHis",time()).rand(10000,99999);
        $this->font = __DIR__ . '/../ttf/msyh.ttf';
    }

    public function createImageMark($image){
        $this->imageinfo = getimagesize($image);
        if(!$this->imageinfo){
            return "图片格式不正确，请提交正确的图片！";
        }
        $this->imagewidth = $this->imageinfo[0];  //图片宽度
        $this->imageheight = $this->imageinfo[1]; //图片高度
        $this->imagemime = $this->imageinfo["mime"];

        switch ($this->imagemime){
            case "image/jpeg":
            case "image/jpg":
                $this->imagesource = @imagecreatefromjpeg($image);
                break;
            case "image/gif":
                $this->imagesource = @imagecreatefromgif($image);
                break;
            case "image/png":
                $this->imagesource = @imagecreatefrompng($image);
                break;
        }

        //创建目标图片
        $this->imagetarget = imagecreatetruecolor($this->imagewidth,$this->imageheight);
        #设置保持目标图片的透明度
        imagesavealpha($this->imagetarget,true);
        #将原始图片按比例复制到新图上
        imagecopyresampled($this->imagetarget,$this->imagesource,0,0,0,0,$this->imagewidth,$this->imageheight,$this->imagewidth,$this->imageheight);

        if($this->mark_type == "text"){         //文本水印
            //$this->mark_words = implode(" ",[$this->mark_words,$this->mark_words,$this->mark_words]);
            return $this->makeTextMarkByBackground();
            //return $this->makeTextMark();
        }elseif($this->mark_type == 'oldtext'){     //旧水印
            //$this->mark_words = implode(" ",[$this->mark_words,$this->mark_words,$this->mark_words]);
            return $this->makeTextMark();
        }elseif($this->mark_type == "image"){   //图片水印
            return $this->makeImageMark();
        }

    }

    /**
     * 创建带背景颜色的文字水印图片 (v2.0)
     */
    private function makeTextMarkByBackground(){
        $offset = 100;
        $font_len = strlen($this->mark_words) * 6;

        if($this->imagewidth <= ($this->imagewidth/2 + $offset)){
            $backwidth = $this->imagewidth/2 + $font_len ;
        }else{
            $backwidth = $this->imagewidth/2 + $offset + $font_len ;
        }
        #创建背景图片
        $backimage = imagecreate($backwidth ,55);
        #设置背景图片颜色以及透明度
        $backimage_color = imagecolorallocatealpha($backimage,80,80,80,10);
        #颜色填充
        imagefilledrectangle($backimage,0,0,imagesx($backimage),imagesy($backimage),$backimage_color);
        #消除图片锯齿
        #imageantialias($backimage,1);
        #旋转图片
        //$backimage = imagerotate($backimage,$this->mark_angle,1);
        #填充颜色，消除经过旋转产生的背景颜色
        //$fill_color = imagecolorallocate($backimage,0,0,0);
        //imagecolortransparent($backimage,$fill_color);
        //imagefill($backimage,0,0,$fill_color);
        #平移间隙差值
        //$d_value = ($this->imagewidth/2) - ($this->imagewidth - imagesx($backimage)) - 10;
        $x_offset = (imagesx($this->imagetarget)-imagesx($backimage))/2 + ($backwidth/2 - strlen($this->mark_words) * 6);
        #图片复制合并
        imagecopymerge($this->imagetarget,$backimage,(imagesx($this->imagetarget)-imagesx($backimage))/2 ,($this->imageheight-imagesy($backimage))/2,0,0,imagesx($backimage) ,imagesy($backimage),15);
        #设置文字颜色及透明度
        $font_color = imagecolorallocatealpha($this->imagetarget,255,255,255,72);
        #填充文字
        imagettftext($this->imagetarget,18,0,$x_offset,($this->imageheight-imagesy($backimage))/2 + 35,$font_color,$this->font,$this->mark_words);

        return $this->output();
    }

    private function makeTextMark(){
        $white = @imagecolorallocatealpha($this->imagetarget,255,255,255,80);
        $im_x = $this->imagewidth * $this->mark_point;
        $im_y = $this->imageheight + $this->mark_offset;
        ImageTTFText($this->imagetarget,$this->mark_fontsize,$this->mark_angle,$im_x,$im_y,$white,$this->font,$this->mark_words);
        return $this->output();
    }

    private function makeImageMark(){
        //2.0版本待开发
        return null;
    }

    protected function output(){
        if($this->imagesource){
            //如果目录不存在，就创建
            if(!is_dir($this->savepath)){
                mkdir($this->savepath,0755, true);
            }

            $outpath = null;

            switch ($this->imagemime){
                case "image/jpeg":
                case "image/jpg":
                    $outpath = $this->savepath.$this->savename.".jpg";
                    imagejpeg($this->imagetarget,$outpath);
                    break;
                case "image/png":
                    $outpath = $this->savepath.$this->savename.".png";
                    imagepng($this->imagetarget,$outpath);
                    break;
                case "image/gif":
                    $outpath = $this->savepath.$this->savename.".gif";
                    imagegif($this->imagetarget,$outpath);
                    break;
            }
            return $outpath;
        }else{
            return false;
        }
    }

    public function setFont($fontpath){
        if($fontpath) {
            $this->font = $fontpath;
        }
    }

    public function setMarkType($type){
        $this->mark_type = $type;
    }

    public function setMarkAlpha($alpha){
        $this->mark_alpha = $alpha;
    }

    public function setMarkPoint($point){
        $this->mark_point = $point;
    }

    public function setMarkWords($words){
        $this->mark_words = $words;
    }

}