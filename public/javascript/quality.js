// 数量加减通用函数
define(['jquery'], function($){

	var min, add, count, callback;

	var init = function(params){
		min = params.minBtn;
		add = params.addBtn;
		count = params.countIpt;
		callback = params.callback || function(){};
		bindEvents();
	}
	
	function bindEvents(){
		add && add.click(function(event) {
			/* Act on the event */
			 count.val( parseInt(count.val())+1 );
			 callback && callback();
		});
		min && min.click(function(){
		    if( count.val()>1 ){
		        count.val(parseInt( count.val())-1);
		        callback && callback();
		    }
		});
	}
	return {
		init: init
	}
});
