<?php
// +----------------------------------------------------------------------
// | ChenHK [ 新增统计报表模块控制器 ]
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------
// | Team:   Cuckoo
// +----------------------------------------------------------------------
// | Date:   2018/1/26 16:34
// +----------------------------------------------------------------------

require_once 'ini.php';
use admin\helper;

//GET 请求标示
$_act_get = empty($_GET['act']) ? null : strtolower(trim($_GET['act']));


//查询统计模板
if (!strcasecmp($_act_get, 'query'))
{
     //检查请求参数
    $filter = checkoutParam($_act_get);

    //查询爆款列表
    $data = getHotReportList($register, $filter);

    //构建查询到的所有爆款的产品id
    $productIDS = "";
    foreach ($data as $item) {
        if ($item['product_id'] != 0) {
            $productIDS = $productIDS.$item['product_id'].',';
        }
    }
    $productIDS = substr($productIDS, 0, strlen($productIDS)-1);

    //请求参数格式化
    $filter = checkoutParam($_act_get);
    $erp_number = implode(',', $filter);

    echo json_encode(['status'=>1, 'data'=>$data, 'ids'=>$productIDS, 'erp_number'=>$erp_number]);
}


//将下架改为待下架
elseif (!strcasecmp($_act_get, 'modify'))
{
    //不存在产品id条件
    if (!isset($_POST['product_id'])) {
        echo json_encode(['status'=>0, 'msg'=>'不存在请求参数']);
        exit;
    }

    //实例化对象
    $hotReportHelper = new helper\hotReport($register);

    $productID = trim($_POST['product_id']);
    //查询判断该产品的域名和二级目录是否已经出现两次
    $isBool = $hotReportHelper->checkProductIdentityTay($productID);
    if ($isBool == 0) {
        echo json_encode(['status'=>0, 'msg'=>'与在线的站点有重复']);
        exit;
    }

    //封装条件
    $filter['product_id'] = $productID;
    $filter['is_del'] = 1;
    $isBool = $hotReportHelper->updateDelHot($filter);

    if ($isBool) {
        echo json_encode(['status'=>$isBool, 'msg'=>'修改待下架成功']);
    } else {
        echo json_encode(['status'=>$isBool, 'msg'=>'修改待下架失败']);
    }
}


//导出到excel
elseif (!strcasecmp($_act_get, 'exportexcel'))
{
     //检查请求参数
    $filter = checkoutParam($_act_get);

    //查询爆款列表
    if ($filter) {
        $data = getHotReportList($register, $filter);
    } else {
        $data = [];
    }
    
    //导出时间
    $time = date('y-m-d H:i:s', time());

    //导出Excel
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->getProperties()->setCreator("统计报表")
        ->setLastModifiedBy("统计报表")
        ->setTitle("统计报表")
        ->setSubject("统计报表")
        ->setDescription("统计报表")
        ->setKeywords("excel")
        ->setCategory("result file");


    $filename = '统计报表.xlsx';

    //设置单元格
    $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A1', '序号')
        ->setCellValue('B1', 'erp_id')
        ->setCellValue('C1', '站点链接')
        ->setCellValue('D1', '订单总数')
        ->setCellValue('E1', '站点状态')
        ->setCellValue('F1', '查询时间');

    //标题居中
    $objPHPExcel->getActiveSheet()
        ->getStyle('A1:F1')
        ->applyFromArray(
            array(
                'font' => array ('bold' => true),
                'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
            )
        );

    $objPHPExcel->getActiveSheet()
        ->getStyle('B')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

    //填充数据
    $number = 0;
    foreach ($data as $key => $v) {
        $number = $key + 1;

        switch ($v['is_del']) {
            case 1: $del_str='下架';break;
            case 0: $del_str='上架';break;
            case 10: $del_str='待下架';break;
            default: $del_str = '不存在该站点';break;
        }

        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A'.($key + 2), $number)
            ->setCellValueExplicit('B'.($key + 2), $v['erp_number'], PHPExcel_Cell_DataType::TYPE_STRING2)
            ->setCellValue('C'.($key + 2), $v['url'])
            ->setCellValue('D'.($key + 2), $v['cnt'])
            ->setCellValue('E'.($key + 2), $del_str)
            ->setCellValue('F'.($key + 2), $time);
    }

    //设置单元格自动宽度
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(40);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);

    //有数据才进行居中处理
    if ($number) {
        //内容居中
        $objPHPExcel->getActiveSheet()
            ->getStyle('A2:B'.($number+1))
            ->applyFromArray(array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)));
        $objPHPExcel->getActiveSheet()
            ->getStyle('D2:F'.($number+1))
            ->applyFromArray(array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)));
    }

    $objPHPExcel->setActiveSheetIndex(0);
    //清楚缓存
    ob_end_clean();

    //构建表头 输出文件信息
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename='.$filename);
    header('Cache-Control: max-age=0');
    $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save("php://output");

    exit;
}






/**
 * ---------------------------------------------------------------------------------------------------------------------
 *                                                   公用类方法
 * ---------------------------------------------------------------------------------------------------------------------
 */


/**
 * 检查请求参数
 * @return array
 */
function checkoutParam($act = "exportexcel")
{
    //不存在查询条件
    if (!isset($_POST['list']) || empty($_POST['list'])) {
        if ($act == "exportexcel") {
            return false;
        } else {
            echo json_encode(['status'=>0, 'msg'=>'请求参数为空']);
            exit;
        }
    }

    //判断请求参数是否合法
    $filter = array_filter(explode(',', $_POST['list']));
    $is_integer = 1;
    foreach ($filter as $val) {
        if (intval($val) < 0) {
            $is_integer = 0;
            break;
        }
    }
    if (!$is_integer) {
        if ($act == "exportexcel") {
            return false;
        } else {
            echo json_encode(['status'=>0, 'msg'=>'请求参数不合法,有字符串']);
            exit;
        }
    }

    //去重
    $filter = array_unique($filter);

    return $filter;
}


/**
 * 查询爆款产品列表
 * @param $register
 * @return array|null
 */
function getHotReportList($register, $filter)
{
    $hotReportHelper = new helper\hotReport($register);
    $data = $hotReportHelper->selectedHotList($filter);
    
    //构建不存在爆款的数据
    $hasErpNumbers = array();
    if (!empty($data)) {
        $hasErpNumbers = array_column($data, 'erp_number');
    }
    foreach ($filter as $val) {
        if (!in_array($val, $hasErpNumbers)) {
            $data[] = array(
                'product_id' => 0,
                'url'        => '',
                'title'      => '',
                'erp_number' => $val,
                'is_del'     => -1,
                'cnt'        => 0
            );
        }
    }

    return $data;
}
