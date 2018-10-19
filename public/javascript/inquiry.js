require(['jquery'],function($, Swiper){
	$(document).ready(function(){
		var orderid = Cjs.url.getParamByName('order');
		if(orderid){
			$('#loading').show();
			getOrderGoods(orderid);
		}
		$('#go').click(function(){
			var mob = $.trim($('input[name="mob"]').val());
			$('.nextpage').attr("mob",mob);
			if(mob != ""){
				viewpage(mob,1);
				$('.nextpage').attr("mob",mob);
			}
		});
		var pnum = 1;
		$('.nextpage').click(function(){
			var mobile = $(this).attr('mob');
			var page = parseInt($(this).attr('allp'));
			pnum++;
			if(pnum > page){
				alert($(this).attr('error'));
			}else{
				viewpage(mobile,pnum);
			}
		})
		function viewpage(mob,p){
			//var datas = alldata;
			var time = $('#inquiry_list').attr('data_time');
			var num = $('#inquiry_list').attr('data_num');
			var viewT = $('#inquiry_list').attr('data_sea');
			var data_null = $('#inquiry_list').attr('data_null');
			$.ajax({
				url:"/order_quality.php?mobile="+mob+"&p="+p,
				type:'get',
				dataType:"json",
				async:true,
				beforeSend:function(){
					$('#loading').show();
				},
				success:function(data){
					if(data.code == 200){
						$('#loading').hide();
						var datas = data.data;
						var html = [];
						if(datas.goodsList != ""){
							for(var i = 0 ;i < datas.goodsList.length ; i++){
								html.push('<div class="item">');
								html.push('<div class="flexbox">');
								html.push('<div class="time flex">');
								html.push('<span>'+ time +'</span>');
								html.push('<div class="deviate">'+ datas.goodsList[i].add_time +'</div>');
								html.push('</div>');
								html.push('<div class="number flex">');
								html.push('<span>'+ num +'</span>');
								html.push('<div class="deviate">'+ datas.goodsList[i].erp_no +'</div>');
								html.push('</div>');
								html.push('</div>');
								html.push('<button rel="'+ datas.goodsList[i].order_id +'" class="order_details">'+ viewT +'</button>');
								html.push('</div>');
							}
							$('#inquiry_list').html(html.join(''));
							if(datas.total_p != 1){
								$('.nextpage').show();
								$('.nextpage').attr("allp",datas.total_p);
							}
						}else{
							$('#inquiry_list').html('<div class="data_null">'+data_null+'</div>');
							$('.nextpage').hide();
						}
						
					}else{
						$('#loading').hide();
						alert(data.msg);
					}
				}
			})





		}
		$('#inquiry_list').off().on('click','.order_details',function(){
			var id = $(this).attr('rel');
			window.location.href = '/order_quality.php?order=' + id;
		})
        $('#showComboProduct_triggle2').click(function(event) {
        	var close = $(this).attr('rel');
        	var show  = $(this).attr('rel_s');
            /* Act on the event */
            if($(this).hasClass('action')){
            	$(this).removeClass('action');
            	$('#showComboProduct_content').slideDown();
            	$('#lang').html(close);
            }else{
            	$(this).addClass('action');
            	$('#showComboProduct_content').slideUp();
            	$('#lang').html(show);
            }
        });
        function getOrderGoods(id){
        	$.ajax({
				url:"/order_quality.php?order_id="+id,
				type:'get',
				dataType:"json",
				async:true,
				beforeSend:function(){
					$('#loading').show();
				},
				success:function(data){
					if(data.code == 200){
						$('#loading').hide();
						var datas = data.data.goodsList[0];
						var html = [],str = [];
						$('#add_time').html(datas.add_time);
						$('#erp_no').html(datas.erp_no);
						$('#address').html(datas.address);
						$('#pay_type').html(datas.pay_type);
						$('#payment_amount,.total').html(datas.currency_code+datas.payment_amount);
						$('#name').html(datas.name+"&nbsp;&nbsp;&nbsp;"+datas.mobile);
						//if(datas.count == 0){
							//for(var i = 0; i < datas.orderGoods.length;i++){
								html.push('<div class="row-2 flexbox">');
				            	html.push('<div class="goodImg">');
				            	if(datas.combo_id == 0){
				            		for(var i = 0; i < datas.orderGoods.length;i++){
						            	for(var a = 0 ;a < datas.orderGoods[i].attr.length;a++){
						            		if(datas.orderGoods[i].attr[a] != null){
								            	if(datas.orderGoods[i].attr[a].thumb){
								            		var thumbimg = datas.orderGoods[i].attr[a].thumb;
								            	}
							            	}else{
							            		var thumbimg = datas.thumb;
							            	}
						                }
						                html.push('<img src="'+ thumbimg +'" width="100%" height="100%">');
					            	}
				            	}else{
					            	html.push('<img src="'+ datas.thumb +'" width="100%" height="100%">');
					            }
				            	html.push('</div>');
				            	html.push('<div class="goodInfo flex">');
				            	html.push('<div class="goodName">'+ datas.title +'</div>');
				            	html.push('<div class="goodName ti">');
				                if(datas.combo_id == 0){
				                	//html.push('<div class="goodName ti">');
					                for(var b = 0 ;b < datas.orderGoods[0].attr.length;b++){
					                	if(datas.orderGoods[0].attr[b] != null){
					                	html.push('<span class="attr">' + datas.orderGoods[0].attr[b].name +'&nbsp;&nbsp;</span>');
					                	}
					                }
					                //html.push('</div>');
				            	}else{
				            		html.push('<span>'+datas.combo_title+'</span>');
				            	}
				            	html.push('<div class="goodStatus f-fr">');;
			                    html.push('<span>X</span><span>'+ datas.num +'</span>');
				            	html.push('</div>');
				                html.push('</div>');
				            	html.push('</div>');
				        		html.push('</div>');
							for(var i = 0; i < datas.orderGoods.length;i++){
								str.push('<div class="row-2 flexbox">');
			                	str.push('<div class="goodImg">');
			                	for(var a = 0 ;a < datas.orderGoods[i].attr.length;a++){
			                		if(datas.orderGoods[i].attr[a] != null){
						            	if(datas.orderGoods[i].attr[a].thumb){
						            		var imgsrc = datas.orderGoods[i].attr[a].thumb;
						            	}
						            }else{
						            	var imgsrc = datas.thumb;
						            }
				                }
				                str.push('<img src="'+ imgsrc +'">');
			                	str.push('</div>');
			                	str.push('<div class="goodInfo flex u3">');
			                    str.push('<div class="goodName">');
			                    str.push('<span>'+ datas.orderGoods[i].title +'</span>')
			                    
				            	str.push('<div class="goodStatus">');
				            	for(var b = 0 ;b < datas.orderGoods[i].attr.length;b++){
				            		if(datas.orderGoods[i].attr[b] != null){
				                	str.push('<span class="attr">' + datas.orderGoods[i].attr[b].name +'&nbsp;&nbsp;</span>');
				                }
				            	}
				            	str.push('</div>');

			                    str.push('</div>');
			                	str.push('</div>');
			            		str.push('</div>');
		            		}
		            		if(datas.combo_id != 0){
		            			$('#showComboProduct_content').html(str.join(''));
		            		}else{
		            			$('#showComboProduct_triggle2').hide();
		            		}
						//}
		        		$('#goods').append(html.join(''));
		        		$('#details').show();
		        		$('.content').hide();
					}else{
						alert(data.msg);
						$('#loading').hide();
					}
				}
			})
        }
	})
})
