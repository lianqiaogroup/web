<?php
require_once 'ini.php';

if($_GET['act'] && $_GET['act']=='edit')
{
    $model = new \admin\helper\payment($register);
    if($_GET['id'])
    {
        $id = $_GET['id'];
        $data = $model->getOnePay($id);
        $data['info'] = unserialize($data);
    }
    $data['pay_type'] = payment_config();
    echo json_encode($data);

}

elseif ($_GET['act'] && $_GET['act'] =='save')
{
     $data = $_POST;
     $model = new \admin\helper\payment($register);
     $config = payment_config();
     $ret  = $model->save($data,$config);

    echo json_encode($ret);
}

elseif($_GET['act'] && $_GET['act'] =='getPay')
{
    $code = $_GET['code'];
    $pay = payment_config();
    $data = $pay[$code];

    foreach ($data['field'] as $key =>$value)
    {
        $values['name'] = $key;
        $values['title'] = $value;

        $ret[] = $values;
    }
    echo json_encode($ret);
}
else{
  $model = new \admin\helper\payment($register);
  $data = $model->getPayment();
  echo json_encode($data);
}

function payment_config()
{
    $list = [
      'ocean'=>  ['code'=>'ocean','title'=>'钱海支付','field'=>['account'=>'商户号','terminal'=>'终端号','secureCode'=>'秘钥','publicKey'=>'公钥']],
       'asiaBill'=> ['code'=>'asiaBill','title'=>'asiaBill','field'=>['account'=>'商户号','gateWayNo'=>'网关号','key'=>'加密key']]
    ];
    return $list;

}