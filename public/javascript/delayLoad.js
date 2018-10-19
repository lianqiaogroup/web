define(['jquery'], function($){
	var load = function(url){
		var script = '<script language="javascript" src="'+url+'"></script>';
		$("body").append($(script));
	}
	return {
		load: load
	}
});