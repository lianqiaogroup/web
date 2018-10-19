<?php

namespace admin\helper;

/**
 * 订单查询通用类
 * Class order
 * @package admin\helper
 */
class order extends common {

    /**
     * 获取订单列表
     * @param array $filter 查询条件
     * @return array 订单列表
     */
    public function orderList($filter = '') {
        $_where = [];
        if ($filter) {
            $_where = $filter;
        }

        //if not admin
        if (!$_SESSION['admin']['is_admin']) {//如果不是管理员，就获取当前优化师的订单数据
            $_where['aid'] = $_SESSION['admin']['uid'];
        }

        $where = ['ORDER' => ['order_id' => "DESC"]];
        if ($_where) {
            $where['AND'] = $_where;
        }

        $data = $this->db->pageSelect('order', '*', $where, 20); //获取列表分页数据
        if ($data['goodsList']) {
            $order_id = array_column($data['goodsList'], 'order_id');
            $product_id = array_unique(array_column($data['goodsList'], 'product_id'));
            $orderGoods = $this->getOrderGoods($order_id); //获取订单商品数据
            $product = $this->getProduct($product_id); //获取订单产品数据
            foreach ($data['goodsList'] as $key => $value) {
                $value['payment_amount'] = money_int($value['payment_amount'], 'float');
                $value['currency_code'] = $product[$value['product_id']]['currency_code'];
                $value['thumb'] = isset($product[$value['product_id']]) ? \admin\helper\qiniu::getResourceUrl($product[$value['product_id']]['thumb'], 1) : '';
                $value['orderGoods'] = $orderGoods[$value['order_id']];
                $value['http_referer'] = '';
                $post_ert_data = json_decode($value['post_erp_data'], true);
                if (!empty($post_ert_data)) {
                    $value['http_referer'] = $post_ert_data['http_referer'];
                }
                $data['goodsList'][$key] = $value;
            }
            //$data['goodsList'] = $ret;
        }

        return $data;
    }

    /**
     * 获取订单产品数据
     * @param int|array $product_id 产品id
     * @return array 订单产品数据 以产品id 作为 key 的数组
     */
    public function getProduct($product_id) {
        $map['product_id'] = $product_id;
        $data = $this->db->select("product", ['currency_code', 'thumb', 'product_id'], $map);
        $data = array_column($data, NULL, 'product_id');
        return $data;
    }

    /**
     * 获取订单商品数据
     * @param int|array $order_id
     * @return 订单商品数据 以订单id 作为 key 的数组
     */
    public function getOrderGoods($order_id) {

        $info = [];

        $map['order_id'] = $order_id;
        $data = $this->db->select("order_goods", '*', $map);

        if (empty($data)) {
            return $info;
        }

        $order_goods_id = array_column($data, 'order_goods_id');
        $goods_attr = $this->getOrderGoodsAttr($order_goods_id); //获取订单商品属性数据
        foreach ($data as $goods) {
            $order_id = $goods['order_id'];
            $goods['price'] = money_int($goods['price'], 'float');
            $promotion_price = $this->getPromotionPrice($order_id, $goods['product_id']); //获取产品套餐促销价格
            $goods['promotion_price'] = $promotion_price ? $promotion_price : $goods['price'];
            $goods['total'] = money_int($goods['total'], 'float');
            $goods['attr'] = $goods_attr[$goods['order_goods_id']];

            $info[$order_id][] = $goods;
        }
        return $info;
    }

    /**
     * 获取产品套餐促销价格
     * @param int $order_id
     * @param int $product_id
     * @return int 产品套餐促销价格
     */
    function getPromotionPrice($order_id, $product_id) {

        $sql = "SELECT g.promotion_price FROM `order` o LEFT JOIN `combo_goods` g on o.combo_id = g.combo_id  WHERE o.order_id=$order_id AND o.combo_id > 0 AND g.product_id=$product_id limit 1";
        $query = $this->db->query($sql);

        if (empty($query)) {
            return 0;
        }

        $promotion_price = $query->fetchAll(\PDO::FETCH_ASSOC);

        return empty($promotion_price) ? 0 : $promotion_price[0]['promotion_price'];
    }

    /**
     * 获取订单商品属性数据
     * @param int|array $order_goods_id 订单产品id
     * @return array 订单产品属性数据 以订单商品id order_goods.order_goods_id
     */
    public function getOrderGoodsAttr($order_goods_id) {

        $ret = [];
        $map['order_goods_id'] = $order_goods_id;
        $data = $this->db->select("order_goods_attr", '*', $map);

        if (empty($data)) {
            return $ret;
        }

        $product_attr_id = array_column($data, 'product_attr_id');
        $product_attr = $this->getProductAttr($product_attr_id); //产品属性数据 以产品属性id 作为 key 的数组
        foreach ($data as $attr) {
            $ret[$attr['order_goods_id']][] = $product_attr[$attr['product_attr_id']];
        }

        return $ret;
    }

    /**
     * 获取产品属性数据
     * @param int|array $product_attr_id 产品属性id
     * @return array 产品属性数据 以产品属性id 作为 key 的数组
     */
    public function getProductAttr($product_attr_id) {

        $map['product_attr_id'] = $product_attr_id;
        $data = $this->db->select("product_attr", '*', $map);

        $_data = [];
        if (empty($data)) {
            unset($data);
            return $_data;
        }

        foreach ($data as $row) {
            $row['thumb'] = \admin\helper\qiniu::getResourceUrl($row['thumb'], 1);
            $_data[$row['product_attr_id']] = $row;
        }
        unset($data);

        return $_data;
    }

    /**
     * 获取订单详情数据
     * @param int|array $order_id 订单id
     * @return array
     */
    public function getOrder($order_id) {

        $map['order_id'] = $order_id;
        $order = $this->db->get("order", '*', $map);
        if (!$order) {
            return ['ret' => 0, 'msg' => '没找该订单'];
        }

        $post_data = json_decode($order['post_erp_data'], true);
        $post_data['web_info'] = unserialize($post_data['web_info']);
        $user = $this->db->get('oa_users', ['name_cn', 'department'], ['username' => $post_data['username']]);
        $order['user'] = $user;
        $order['post_erp_data'] = $post_data;

        return ['ret' => 1, 'data' => $order];
    }

    /**
     * 通过erp 获取订单收件人信息
     * @param type $oid_list
     * @return type
     */
    function getOrderErpInfoList($oid_list) {
        if (!is_array($oid_list) || count($oid_list) < 1) {
            return [];
        }
        $t = time();
        $url = \lib\register::getInstance('config')->get('apiUrl.oldErp');
        $url .= '/order/api/get_order_status';

        $params = ['time' => $t, 'key' => md5($t . 'stosz'), 'orderdata' => json_encode($oid_list)];
        $log = new \lib\log();
        $log->write('getOrderStatus', "发送data" . print_r($params, true));
        $res = $this->sendPost($url, $params);
        $log->write('getOrderStatus', "接收data" . print_r($res, true));

        if (empty($res['status'])) {
            return [];
        }

        $res = json_decode($res['message'], 1);
        if (empty($res) || !is_array($res['data']) || empty($res['data'][0])) {
            return [];
        }

        $res = array_column($res['data'], null, 'id_increment');

        //访问量大的时候 再启用队列入库
        $sql = "SELECT o.order_id,o.erp_no,oe.order_id as _order_id,oe.erp_code,oe.order_code FROM `order` o LEFT JOIN order_expand oe  on oe.order_id=o.order_id  WHERE o.erp_no IN (" . implode($oid_list, ',') . ") ";
        $list = $this->register->get('db')->query($sql)->fetchAll();
        foreach ($list as $key => $value) {
            $erp_no = $value['erp_no'];
            $erp_code = !empty($res[$erp_no]['id_order_status']) ? $res[$erp_no]['id_order_status'] : '1';

            $erp_status = $this->getOrderCode($erp_code);
            $res[$erp_no]['msg_erp_status'] = $erp_status['msg'];

            if (empty($value['_order_id'])) {
                $this->register->get('db')->insert("order_expand", ['order_id' => $value['order_id'], 'erp_code' => $erp_code, 'order_code' => $erp_status['code']]); //状态入库--新增
            } else {
                if ($value['erp_code'] != $erp_code) {
                    $this->register->get('db')->update("order_expand", ['erp_code' => $erp_code, 'order_code' => $erp_status['code']], ['order_id' => $value['order_id']]); //状态入库--更新
                }
            }
        }

        return $res;
    }

    public function getOrderCode($erp_code = '') {
        switch ($erp_code) {
            case '8':
            case '18':
            case '27':
                return ['code' => 2, 'msg' => '已发货'];
                break;
            case '9':
                return ['code' => 3, 'msg' => '已收货'];
                break;
            case '11':
            case '12':
            case '13':
            case '14':
            case '19':
                return ['code' => 11, 'msg' => '已取消'];
                break;
            case '15':
                return ['code' => 21, 'msg' => '待退货'];
                break;
            case '16':
                return ['code' => 22, 'msg' => '退货中'];
                break;
            case '10':
            case '21':
            case '23':
            case '24':
                return ['code' => 23, 'msg' => '已退货'];
                break;
            default:
                return ['code' => 1, 'msg' => '待发货'];
                break;
        }
    }

    public function sendPost($url, $data, $headers = []) {

        $curlOptions = array(
            //CURLOPT_USERAGENT => $user_agent, //用户浏览器信息 $_SERVER['HTTP_USER_AGENT'] User-Agent：格式为 model/rom/app-name/app-version/encrypted-imei，不存在的项以空串代替，分隔符保留。
            CURLOPT_URL => $url, //访问URL
            // CURLOPT_REFERER => $refer, //哪个页面链接过来的 
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true, //获取结果作为字符串返回
            CURLOPT_FOLLOWLOCATION => true, // 使用自动跳转
            CURLOPT_HEADER => false, //获取返回头信息
            //CURLOPT_COOKIEFILE => $cookieFile, //请求时发送的cookie所在的文件
            //CURLOPT_COOKIEJAR => $cookieFile, //获取结果后cookie存储的文件
            CURLOPT_POST => true, //发送时带有POST参数
            CURLOPT_POSTFIELDS => $data, //请求的POST参数字符串
//            CURLOPT_CONNECTTIMEOUT_MS => 1000, // 等待响应的时间,单位毫秒
//            CURLOPT_TIMEOUT_MS => 1000, //设置cURL允许执行的最长秒数  
            //CURLOPT_COOKIE => 'PHPSESSID=123456860918021331410_41067be18b0415d2622e51080ddeac377b98e908_E6_860918021331410_460029194382961_WIFI_8609180213314101381919171315_1000_1_0_JOP40D', //用户 cookie 对应 $_SERVER['HTTP_COOKIE']
            CURLOPT_SSL_VERIFYPEER => 0, // 对认证证书来源的检查
            CURLOPT_SSL_VERIFYHOST => 0, // 从证书中检查SSL加密算法是否存在
        );

        $status = 1;
        try {
            /* 初始化curl模块 */
            $curl = curl_init();

            /* 设置curl选项 */
            curl_setopt_array($curl, $curlOptions);

            $result = curl_exec($curl);
            if (($errno = curl_errno($curl)) != CURLM_OK) {
                $result = curl_error($curl);
                $status = 0;
            }
        } catch (\Exception $e) {
            $status = 0;
            $result = $e->getMessage();
        }

        $curlInfo = curl_getinfo($curl);
        curl_close($curl);

        return ['status' => $status, 'message' => $result, 'curlInfo' => $curlInfo];
    }

}
