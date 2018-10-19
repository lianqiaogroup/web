<?php

namespace admin\Controllers;

class OrderController extends Controller {

    public function __construct() {

        parent::__construct();

        //无管理权限
        if (!$_SESSION['admin']['is_root']) {
            exit('无此权限');
        }
    }

    /**
     * 获取订单列表
     */
    public function listAction() {

        $order = \lib\register::createInstance('\admin\helper\order');

        $filter = [];

        if ($_GET['order_no']) {//订单编号
            $filter['OR'] = ['order_no' => trim($_GET['order_no']), 'erp_no' => trim($_GET['order_no'])];
        }

        if ($_GET['order_status']) {//订单状态
            $filter['order_status'] = $_GET['order_status'];
        }
        if ($_GET['erp_status']) {//erp订单编号
            $filter['erp_status'] = $_GET['erp_status'];
        }

        if ($_GET['product_id']) {//产品id
            $filter['product_id'] = intval($_GET['product_id']);
        }

        if ($_GET['title']) {//产品标题
            $filter['title'] = trim($_GET['title']);
        }

        if ($_GET['name']) {//收件人
            $filter['name'] = trim($_GET['name']);
        }

        if ($_GET['email']) {//收件email
            $filter['email'] = trim($_GET['email']);
        }

        if ($_GET['mobile']) {//收件手机号
            $filter['mobile'] = trim($_GET['mobile']);
        }

        if (isset($_GET['start_time']) && !empty($_GET['start_time'])) {//开始时间
            $filter['add_time[>]'] = trim($_GET['start_time']);
        }

        if (isset($_GET['end_time']) && !empty($_GET['end_time'])) {//结束时间
            $filter['add_time[<]'] = trim($_GET['end_time']);
        }

        if ($_GET['domain']) {//域名
            $filter['post_erp_data[~]'] = '%"web_url":"' . getHost($_GET['domain']) . '"%';
        }

        $data = $order->orderList($filter);
        $data['admin'] = $_SESSION['admin'];
        $data['filter'] = $filter;

        if (isset($_GET['json']) && $_GET['json'] == '1') {
            $this->ajaxResponse($data);
        }

        $this->httpResponse('order_list.twig', $data);
    }

    /**
     * 展示给定用户的配置文件
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function infoAction() {

        $order = \lib\register::createInstance('\admin\helper\order');
        $order_id = $_POST['order_id'];

        //获取订单数据
        $orders = $order->getOrder($order_id);
        $orders['data']['post_erp_data']['web_url'] = $orders['data']['post_erp_data']['website'];

        if (empty($orders['ret'])) {
            $this->ajaxResponse($orders);
        }

        //获取订单商品
        $order_goods = $order->getOrderGoods($order_id)[$order_id];
        $orders['goods'] = $order_goods; // 商品信息
        //订单收件人信息
        $order_erp_info = $order->getOrderErpInfoList([$orders['data']['erp_no']]);
        $order_erp_info = I('data.' . $orders['data']['erp_no'], [], '', $order_erp_info); // 订单信息
        $orders['erp_status'] = $order_erp_info; // 订单信息

        $this->ajaxResponse($orders);
    }

}
