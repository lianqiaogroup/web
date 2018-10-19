<?php
// +----------------------------------------------------------------------
// |[ 产品扩展类操作控制器 - 除了产品增删改查操作 - 其余后续增加在这里 ]
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------
// | Team:   Cuckoo
// +----------------------------------------------------------------------
// | Date:   2018/2/2 10:53
// +----------------------------------------------------------------------

require_once 'ini.php';

//GET 请求标示
$_act_get  = empty($_GET['act'])  ? null : strtolower(trim($_GET['act']));
$_act_post = empty($_POST['act']) ? null : strtolower(trim($_POST['act']));

/**
 * ---------------------------------------------------------------------------------------------------------------------
 *                                                  导入导出功能
 *                                                产品域名导出、
 * ---------------------------------------------------------------------------------------------------------------------
 */

//产品域名导出
if (!strcasecmp($_act_get, 'exportdomain'))
{
    //构建查询条件
    if (!empty($_GET['domain'])) {
        $domain  = $_GET['domain'];
        if(strpos($domain,'http')!==false)
        {
            $domain = trim(str_replace('http://',"",$domain));
        }
        if(strpos($domain,'/')!==false)
        {
            list($mapDomain,$tag) = explode('/',$domain);
            $filter['identity_tag'] = trim($tag);
        }
        else{
            $mapDomain = $domain;
        }
        $filter['domain[~]'] = [$mapDomain . '%'];
    }

    !empty($_GET['title'])        && ($filter['title[~]']=['%'.$_GET['title'].'%']);
    !empty($_GET['product_id'])   && ($filter['product_id']=$_GET['product_id']);
    !empty($_GET['theme'])        && ($filter['theme[~]']=['%'.$_GET['theme'].'%']);
    !empty($_GET['erp_id'])       && ($filter['erp_number']=$_GET['erp_id']);
    !empty($_GET['aid'])          && ($filter['aid']=$_GET['aid']);
    !empty($_GET['id_zone'])      && ($filter['id_zone']=$_GET['id_zone']);

    $is_del = empty($_GET['is_del']) ? 0 : 1;
    if ($is_del == 0) {
        $filter['is_del'] = 0;
    } else {
        $filter['is_del'] = [1, 10];
    }

    include_once 'helper/common.php';
    $comMod = new \admin\helper\common($register);
    $uid    = $comMod->getUids();
    !empty($uid['company_id']) && ($filter['company_id']=$uid['company_id']);

    if ($uid['uid']) {
        $filter['ad_member'] = $uid['ad_member'];
        $filter['oa_id_department'] = $uid['id_department'];
    }
    if ($uid['is_leader']) unset($filter['ad_member']);
    if (!empty($_GET['ad_member_id'])) {
        $ad_member = $db->get('oa_users', ['name_cn(ad_member)'], ['uid' => $_GET['ad_member_id']]);
        unset($_GET['ad_member_id']);
        $filter['ad_member'] = $ad_member['ad_member'];
    }
    $filter['ORDER']  = ['product_id' => "DESC"];

    //查询产品信息
    $field = ['title','domain','identity_tag','erp_number','fb_px','ad_member_id','price','theme'];
    $data = $db->select('product', $field , $filter);

    $objPHPExcel = new \PHPExcel();
    $objPHPExcel->getProperties()
        ->setCreator("产品域名")
        ->setLastModifiedBy("产品域名")
        ->setTitle("产品域名")
        ->setSubject("产品域名")
        ->setDescription("产品域名")
        ->setKeywords("excel")
        ->setCategory("result file");

    $filename = '产品域名.xlsx';
    //设置单元格
    $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', '序号')
                ->setCellValue('B1', '产品名称')
                ->setCellValue('C1', 'erp域名')
                ->setCellValue('D1', '建站域名')
                ->setCellValue('E1', '二级目录')
                ->setCellValue('F1', 'ERP产品id')
                ->setCellValue('G1', 'FB通用像素id')
                ->setCellValue('H1', '广告手id')
                ->setCellValue('I1', '价格')
                ->setCellValue('J1', '前台显示模板');

    //居中
    $objPHPExcel->getActiveSheet()
        ->getStyle('A1:J1')
        ->applyFromArray(
            array('font' => array ('bold' => true),
                'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)));

    $objPHPExcel->getActiveSheet()->getStyle('F:I')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

    //填充数据
    $number = 0;
    foreach ($data as $key => $v) {
        $number = $key + 1;
        $count = $key + 2;
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A'.$count, $number)
                    ->setCellValue('B'.$count, $v['title'])
                    ->setCellValue('C'.$count, $v['domain'])
                    ->setCellValue('D'.$count, 'http://'.$v['domain'].'/'.$v['identity_tag'])
                    ->setCellValue('E'.$count, $v['identity_tag'])
                    ->setCellValueExplicit('F'.$count, $v['erp_number'], PHPExcel_Cell_DataType::TYPE_STRING2)
                    ->setCellValueExplicit('G'.$count, $v['fb_px'], PHPExcel_Cell_DataType::TYPE_STRING2)
                    ->setCellValueExplicit('H'.$count, $v['ad_member_id'], PHPExcel_Cell_DataType::TYPE_STRING2)
                    ->setCellValueExplicit('i'.$count, ($v['price']/100), PHPExcel_Cell_DataType::TYPE_STRING2)
                    ->setCellValue('J'.$count, $v['theme']);

    }

    //设置单元格自动宽度
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(6);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(19);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(40);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);

    //有数据才进行居中处理
    if ($number) {
        //内容居中
        $objPHPExcel->getActiveSheet()
            ->getStyle('A2:A'.($number+1))
            ->applyFromArray(array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)));
        $objPHPExcel->getActiveSheet()
            ->getStyle('B2:J'.($number+1))
            ->applyFromArray(array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT)));
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