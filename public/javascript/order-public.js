require(['jquery'],function($, Swiper){
	var count = Cjs.url.getParamByName('count');
	var combo_id = Cjs.url.getParamByName('combo_id');
    $('#goods_'+combo_id).show();
	$('input[name="combo_id"]').val(combo_id);
	$('input[name="num"]').val(count);
	$('.number').html(count);
    var str = Cjs.url.getParamByName('proto') || "";
    var strAttr = str.split('|');
    var html = [];

    if(parseInt(combo_id) != 0){
        strAttr.map(function(elem, index) {
            var obj = elem.split('-');
            var goodsid = obj[0]
            var group = obj[1];
            var value = obj[2]; 
            html.push('<input type="hidden" name="attr['+ obj[0]+'-'+ obj[1] +']" value="'+ obj[2] +'">');
            var data = elem;
            $('span[optionskey][data-id="'+ data +'"]').show();
            var imgattr = $('span[optionskey][data-id="'+ data +'"]').attr('attr_img');
            if(imgattr){
                $('span[optionskey][data-id="'+ data +'"]').parents('.flexbox').find('.attrimg').attr('src',imgattr);
                //$('.attrimg').eq(index).attr('src',imgattr);
            }
        });
    }else{
        strAttr.map(function(elem, index) {
            var obj = elem.split('-');
            //var goodsid = obj[0]
            var group = obj[0];
            var value = obj[1];
            html.push('<input type="hidden" name="attr['+obj[0] +']" value="'+ obj[1] +'">');
            $('[optionsKey][data-id="'+ value +'"]').show();

        });
    }
    $('#form').append(html.join(''));
    $('.tab_sele').click(function(){
        $('.tab_sele').removeClass('action');
        $(this).addClass('action');

        var payt = $(this).attr('rel');
        if(payt){
            $('input[name="payment_type"]').val(payt);
            $('.pay-type').hide();
        }else{
            $('.pay-type').show();
            payt = $('#pay-online').val();
            $('input[name="payment_type"]').val(payt);
        }
    });
    $('#pay-online').change(function(){
        payt = $(this).val();
        $('input[name="payment_type"]').val(payt);
    });
    $('input[name="postal_1"],input[name="postal_2"]').change(function(){
        var left = $.trim($('input[name="postal_1"]').val());
        var right = $.trim($('input[name="postal_2"]').val());
        $('input[name="postal"]').val(left+right);
    })
    $('#showComboProduct_triggle2').click(function(event) {
    	var close = $(this).attr('rel');
    	var show  = $(this).attr('rel_s');
        /* Act on the event */
        if($(this).hasClass('action')){
        	$(this).removeClass('action');
        	$('#showComboProduct_'+combo_id).slideDown();
        	$('#lang').html(close);
        }else{
        	$(this).addClass('action');
        	$('#showComboProduct_'+combo_id).slideUp();
        	$('#lang').html(show);
        }
    });
    refresh_price();
    function refresh_price() {
        $.ajax({
            url: '/checkout.php?',
            type: 'post',
            data: $('input[name=combo_id], #act, input[name=\'num\']'),
            dataType: 'json',
            success: function(json) {
               if(json.ret){
                    $("#payment_amount").html(json.total);
                    $('#total').html(json.total);
               }
               else{
                   alert(json.msg)
               }
            },
            error: function(xhr, ajaxOptions, thrownError) {
            }
        });
    }
    setDistrict = function(){
        var cid = $("select[name='city']").find("option:selected").attr('cid');
        $.ajax({
            url: 'region.php?',
            type: 'post',
            data: {'yn_region_id':cid},
            dataType: 'json',
            success: function(ret) {
                if (ret)
                {
                    var option ='<select name="district" style="">';
                    for(var i in ret)
                    {
                        option += '<option name="'+ret[i].name+'">'+ret[i].name+'</option>';
                    }
                    option +='</select>';
                    $(".district").html(option);
                }

            },
            error: function(xhr, ajaxOptions, thrownError) {
            }
        });
    }
    var _region = $("input[name='province']").val();
    postcheck = function(){
        try{
            switch(_region){
                case "台灣":
                    if (/^09/.test(document.form.mob.value) && !/^\d{10}$/.test(document.form.mob.value)) {
                        alert('手機號碼格式不正確，請重新填寫！');
                        document.form.mob.focus();
                        return false;
                    }
                    if (!/^0\d{6,10}/.test(document.form.mob.value)) {
                        alert('手機號碼格式不正確，請重新填寫！');
                        document.form.mob.focus();
                        return false;
                    }
                    break;
                case "香港":
                    break;
            }
        }
        catch(ex){
        }
        try{
            if($('select[name="city"]').val() == ""){
                var errormsg = $('[name="city"]').attr('error');
                alert(errormsg);
                return false;
            }
        }catch(ex){

        }
        return true;
    }
})