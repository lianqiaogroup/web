<?php

require_once 'ini.php';
if(isset($_POST['act']) && $_POST['act'] == 'currency_format') {
    $currency_id     = $_POST['currency_id'];
    $currency_format = $_POST['currency_format'];
    $exchange_rate   = floatval($_POST['exchange_rate']);
    $exchange_rate   = $exchange_rate <= 0 ? 1: $exchange_rate;
    if (intval($currency_id) > 0) {
        //更新
        $data['currency_format'] = $currency_format;
        $data['exchange_rate']   = $exchange_rate;
        $currency = new \admin\helper\currency($register);
        $ret = $currency->updateCurrency($data, ['currency_id'=>$currency_id]);
        if ($ret) {
            echo json_encode(['ret' => 0, 'msg' => '更新成功']);
        } else {
            echo json_encode(['ret' => 0, 'msg' => '更新失败']);
        }
    }
    exit;
}
elseif($_GET['act'] && $_GET['act']=='syncCurrency')
{
    $currency = new \admin\helper\currency($register);
    $res = $currency->syncCurrency();
    echo json_encode($res);exit();
}
 else {
    $currency = new \admin\helper\currency($register);
    $data = $currency->lists();
    $data['admin'] = $_SESSION['admin'];

    echo json_encode($data);
}
