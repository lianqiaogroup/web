<?php
/**
 * Created by PhpStorm.
 * User: jade
 * Date: 2017/9/21
 * Time: 11:12
 */
#use GuzzleHttp\Client;


namespace admin\helper\api;

class erpproduct extends erpbase {
    private $domain;//http://112.95.135.114:8090 正式地址

    function __construct($type = ''){
        $this->domain = \lib\register::getInstance('config')->get('apiUrl.erp');
    }

    function getMillisecond(){
        list($s1, $s2) = explode(' ', microtime()); 
        return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }

	function getProduct($params){
		$token = \lib\register::getInstance('config')->get('erpToken');
        $timestamp = $this->getMillisecond();
		$nonce = $this->getRandomStr(8);
		$uri = $this->domain.'/product/pc/product/'.$params['id'].'/seoLoginid/'.$params['loginid'];
        // var_dump($uri);//exit();
		$sign = $this->getSign($token,$timestamp,$nonce);
		$headers = [
			"X-PROJECT-ID:frontend.website.build",
			"Accept:application/json,text/plain,*/*",
			"x-requested-with:XMLHttpRequest",
			"X-AUTH-TIMESTAMP:$timestamp",
			"X-AUTH-NONCE:$nonce",
			"X-AUTH-SIGNATURE:$sign",
		];
		#jade 新erp接口 对比老erp接口的变更规则
		$res = $this->sendGet($uri,$headers);//var_dump($res);exit();
        \lib\register::createInstance('\lib\log')->write('product', $uri . '=>' . print_r($headers, 1) . print_r($res, 1) . 'api_time=>' . ($this->getMillisecond() - $timestamp) . 'ms' . PHP_EOL);
        $res = json_decode($res['message'],1);
        if($res['code'] == 'OK'){
            $r = [];
            $r['status'] = true;
            $r['message'] = '';
            $r['product']['id_department'] = $res['item']['productZoneList'][0]['departmentId'];
            $r['product']['old_id_department'] = $res['item']['productZoneList'][0]['departmentOldId'];
            $r['product']["id_users"] = $res['item']['creatorId'];
            $r['product']["title"] = $res['item']['title'];
            $r['product']["foreign_title"] = $res['item']['title'];//新erp 没有
            $r['product']["id_category"] = $res['item']['categoryId'];
            $r['product']["category"] = $res['item']['categoryName'];
            $r['product']["id_classify"] = $res['item']['classifyEnum'];
            $r['product']["inner_name"] = $res['item']['innerName'];
            $r['product']["model"] = $res['item']['spu'];
            $r['product']["thumbs"] = $res['item']['mainImageUrl'];
            $r['product']["purchase_price"] = $res['item']['purchasePrice'];
            $r['product']["quantity"] = $res['item']['totalStock'];//??
            $r['product']["special_from_date"] = 0;//新erp 没有
            $r['product']["special_to_date"] = 0;//新erp 没有
            $r['product']["length"] = isset($res['item']['length'])?$res['item']['length']:'';
            $r['product']["width"] = $res['item']['width'];
            $r['product']["height"] = $res['item']['height'];
            $r['product']["weight"] = $res['item']['weight'];
            $r['product']["is_attach"] = 0;//新erp 没有
            $r['product']["status"] = $res['item']['state'];
            $r['product']["desc"] = $res['item']['memo'];
            $r['product']["created_at"] = $res['item']['createAt'];
            $r['product']["updated_at"] = $res['item']['updateAt'];
            $r['product']["id"] = $res['item']['id'];
            $r['product']["price"] = $res['item']['purchasePrice'];//新erp 没有price 使用 purchasePrice
            $r['product']["type"] = 'simple';//$res['item']['castomEnum']
            $r['product']["bundle"] = '';//新erp 没有
            $r['product']["product_attr"] = $res['item']['attributeList'];
            $r['product']["productZoneNames"] = array_column($res['item']['productZoneList'], 'zoneName');
            $r['product']["productZoneState"] = array_column($res['item']['productZoneList'],'stateEnum','zoneName');
            return $r;
        }
        $msg = empty($res['desc'])?'拉取产品失败：未知错误':$res['desc'];
        return ['status'=>false,'product'=>[],'message'=>$msg];
	}

	


	
	
	function getSign($token,$timestamp,$nonce){
		return md5($token.$timestamp.$nonce);
	}
}