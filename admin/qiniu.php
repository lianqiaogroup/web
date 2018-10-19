<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/21
 * Time: 9:28
 */
require_once '../vendor/autoload.php';

// 引入鉴权类
use Qiniu\Auth;
// 引入上传类
use Qiniu\Storage\UploadManager;
// 需要填写你的 Access Key 和 Secret Key
$accessKey = '0znPKSrr3SZ4EGBme8kNqecuw_r6ClHPfY-lmnsk';
$secretKey = 'SoOrKXr8X2kuBTuJ8TkfUyGrbCrEIYkOP4Euq0LY';
$bucket    = 'bucket-cn';
// 构建鉴权对象
$auth      = new Auth($accessKey, $secretKey);
// 生成上传 Token
$token     = $auth->uploadToken($bucket);
?>
<input class ="thumbs" type = "file" name = "file" multiple>
<script src="http://cdn.bgnht.com/javascript/jquery.js"></script>
<script>
    $(function () {
        $(document).on('change', '.thumbs', function () {
            var obj = $(this);
            var file = this.files[0];
            var fd = new FormData();
            fd.append("token", '<?php echo $token ;?>');
            fd.append("file", file);
            $.ajax({
                url: "http://up-na0.qiniu.com",
                type: "POST",
                processData: false,
                contentType: false,
                data: fd,
                dataType: "json",
                success: function (d) {

                }
            });
        });
    })
</script>