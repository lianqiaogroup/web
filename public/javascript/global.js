define(['jquery'], function($){

    // 视频缩略图
    if( $(".edui-upload-video").length > 0 ){
        $(".edui-upload-video").attr("poster", "/public/image/videoPlay.jpg");
        $(".edui-upload-video").click(function(){
            $(this).get(0).play();
        });
    }

});