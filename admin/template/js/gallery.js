jQuery(function(){
    // var height = window.screen.height;
    // var width  = window.screen.width; 
	var domstr = '<section id="zoom-layer" ondblclick="return false;"> <div class="zoom-boxer"><div class="zoom-bd"> <span id="upper" class="page">上一个</span>  <img src="public/image/videoPlay.jpg" id="zoom-img" class="zoom-img"> <span id="next" class="page">下一个</span></div> </div> </section>';
	var css = '<style type="text/css"> #zoom-layer {display: none; position: fixed; left: 0px; top: 0px; bottom: 0px; right: 0px; background-color: rgba(0,0,0,0.7); z-index: 99999; max-width: 100%; height: 100%; margin: 0 auto; } #zoom-layer .zoom-boxer {display: table; width: 100%; max-width: 100%; height: 100%; } #zoom-layer .zoom-boxer .zoom-bd {width: 100%; overflow: hidden; display: table-cell; text-align: center; vertical-align: middle;z-index:90;} #zoom-layer .zoom-boxer .zoom-bd img {max-width: 95%;} .page{color:#fff;font-size:16px;cursor:pointer;z-index:999;}.layer{position:absolute;width:100%;height:100%;z-index:90;}</style>';

	$("body").append(css).append(domstr);
	var zoom = $('#zoom-layer');
	$(function(){
		var attr = [],index = 0;
		function indexOf(arr, str){
		    if(arr && arr.indexOf){
		        return arr.indexOf(str);
		    }
		    var len = arr.length;
		    for(var i = 0; i < len; i++){
		        if(arr[i] == str){
		            return i;
		        }
		    }
		    return -1;
		}
		$(document).on('click', '[data-zoom-img]', function(event) {
			/* Act on the event */
			var len = $('.img-responsive').length;
			for(var i = 0;i < len;i++){
				var img = $('.img-responsive').eq(i).attr('src');
				attr.push(img);
			}
			var src = $(this).attr('src');
			index = indexOf(attr, src);
			if( src!="" ){
				zoom.find('.zoom-img').attr('src', src);
				zoom.fadeIn();
			}

		});
		$('#upper').click(function(e){
			index--;
			if(index < 0){
				index = attr.length - 1;
			}
			var src = attr[index];
			zoom.find('.zoom-img').attr('src', src);
		});
		$('#next').click(function(e){
			index++;
			if(index == attr.length){
				index = 0;
			}
			var src = attr[index];
			zoom.find('.zoom-img').attr('src', src);
		})

		$(zoom).click(function(event) {
			var target = $(event.target);
			if(target.closest("#upper").length == 0 && target.closest("#next").length == 0 && target.closest("#zoom-img").length == 0){
				zoom.fadeOut();
			}
		});
	});
});
