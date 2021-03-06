<?php

require_once 'ini.php';

$comment = new \admin\helper\comment($register);

if (isset($_GET['act']) && $_GET['act'] == 'edit') {
    $comment_id = I('get.comment_id');
    if ($comment_id) {
        $data = $comment->getComment($comment_id);
        foreach ($data['thumbs'] as $k=>$v){
            $data['thumbs'][$k]['thumb'] = \admin\helper\qiniu::get_image_path($v['thumb']);
        }
        $product         = new \admin\helper\product($register);
        $productData = $product->getOneProduct($data['product_id']);
        $data['title'] = $productData['title'];
    }
    $data['admin']   = $_SESSION['admin'];
    echo json_encode($data);
}


elseif (isset($_POST['act']) && $_POST['act'] == 'save') {

    $photos = I('post.photos');
    $data   = I('post.');

    unset($data['act']);
    unset($data['title']);
    unset($data['upfile']);
    unset($data['photos']);
    $comment_id = $data['comment_id'];
    // var_dump($data);
    // die();
    if ($comment_id) {
        //编辑
        $map = ['comment_id' => $comment_id];
        $db->update('product_comment', $data, $map);
    }
    else {
        unset($data['comment_id']);
        //新增
        $data['aid'] = $_SESSION['admin']['uid'];
        $data['company_id'] = $_SESSION['admin']['company_id'];
        $comment_id  = $db->insert('product_comment', $data);
    }
    if ($photos) {
        if (count($photos) > 0) {
            $d = [] ;
            foreach ($photos as $key => $value) {
                if ($value) {
                    $d[$key]['thumb']      = \admin\helper\qiniu::changImgDomain($value);
                    $d[$key]['comment_id'] = $comment_id;
                }
            }
            $db->insert('product_comment_thumb', $d);
        }
    }
    $ret['code'] = '1';
    echo json_encode($ret);
}


elseif (isset($_GET['act']) && $_GET['act'] == 'del') {

    $uid            = $_GET['comment_id'];
    $data['is_del'] = $_GET['is_del'];
    $product        = new \admin\helper\comment($register);
    $ret            = $product->deleteUser($uid, $data);
    $ret['code'] = '1';
    echo json_encode($ret);
}

// excel模板导出功能
elseif (isset($_GET['act']) && $_GET['act'] == 'downfile') {
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->getProperties()->setCreator("excel导出模板")
            ->setLastModifiedBy("excel导出模板")
            ->setTitle("excel导出模板")
            ->setSubject("excel导出模板")
            ->setDescription("excel导出模板")
            ->setKeywords("excel")
            ->setCategory("result file");


    $filename = 'excel导入模板.xlsx';

    $objPHPExcel->getActiveSheet()->setTitle('excel导出模板');

    //设置单元格
    $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '产品ID 不能为空')
            ->setCellValue('B1', '是否匿名（数字：1或0）不能为空')
            ->setCellValue('C1', '用户名 匿名可为空')
            ->setCellValue('D1', '星级（数字：1~5） 不能为空')
            ->setCellValue('E1', '评论内容 可为空')
            ->setCellValue('F1', '评论时间(格式:Y-m-d H:i:s 如:2017-06-06 06:06:06) 不能为空');
    $objPHPExcel->getActiveSheet()->getStyle('F1')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

    $objPHPExcel->setActiveSheetIndex(0);
    ob_end_clean();
    // header('Content-Type: application/vnd.ms-excel');
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename='.$filename);
    header('Cache-Control: max-age=0');
    $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save("php://output");
    exit;
}


// excel导入功能
elseif (isset($_POST['act']) && $_POST['act'] == 'excel-import') {
    //判断上传文件是否有效
    $excelFile     = $_FILES['file'];
    $excelFileName = $excelFile['name'];
    $excelFileName = time() . '.' . end(explode('.', $excelFileName));

    $_FILES['file']['name'] = $excelFileName;

    //判断文件是否有效
    if (empty($excelFile)) {
        echo json_encode(['ret' => 400, 'msg' => '上传文件为空']);
        die;
    }
    $endItemFile = end(explode('.', $excelFileName));
    if ($endItemFile !== 'xls' && $endItemFile !== 'xlsx') {
        echo json_encode(['ret' => 400, 'msg' => '需要上传Excel文件']);
        die;
    }
    //上传excel
    $rootPath      = app_path . "upload/";
    $path          = 'stoshop.com/temp/';
    $uploadHandler = new \Sirius\Upload\Handler($rootPath . $path);
    $result        = $uploadHandler->process($_FILES['file']);
    if (!$result->isValid()) {
        $messages = $result->getMessages();
        echo json_encode(['ret' => 400, 'msg' => '上传excel文件失败']);
    }
    else {
        $result->confirm();
        //临时文件目录
        $tmpFielPath = $rootPath . $path . $result->name;

        //使用 PHPExcel_IOFactory 来鉴别文件应该使用哪一个读取类
        $inputFielType = \PHPExcel_IOFactory::identify($tmpFielPath);
        $objReader     = \PHPExcel_IOFactory::createReader($inputFielType);
        //忽略里面各种格式
        $objReader->setIncludeCharts(TRUE);
        $objPHPExcel   = $objReader->load($tmpFielPath);
        //获取工作表
        $sheetNames    = $objPHPExcel->getSheetNames();
        //获取第一个工作表
        $objWorksheet  = $objPHPExcel->getActiveSheet();
        //获取可用的列数和行数
        $highestRow    = $objWorksheet->getHighestRow(); // 取得总行数
        $highestColumn = $objWorksheet->getHighestColumn(); //取得总列数
        if($highestRow > 1000){
            echo json_encode(['ret' => 400, 'msg' => '一次只能导入最多1000条评论']);
            die;
        }

        if ($highestColumn != 'F') {
            echo json_encode(['ret' => 400, 'msg' => '导入Excel数据格式有误']);
            die;
        }

        $filedArr = array('product_id', 'is_anonymous', 'name', 'star', 'content', 'add_time');

        //记录导入成功条数
        $count = 0;
        // 循环excel获取对象
        for ($i = 2; $i <= $highestRow; $i++) {
            $data = array();
            for ($j = 0; $j < 6; $j++) {
                //获取单元格中的数据
                $val                 = $objWorksheet->getCellByColumnAndRow($j, $i)->getCalculatedValue();
                if ($j == 5) {
                    $add_time = strtotime($val);
                     $add_time = date('Y-m-d H:i:s', $add_time);
                    $data[$filedArr[$j]] = $add_time;
                } else {
                    $data[$filedArr[$j]] = trim($val);
                }
            }
            //对数据判断
            if (empty($data['product_id']) || intval($data['product_id']) <= 0) {
                continue;
            };
            if (intval($data['is_anonymous']) < 0 || intval($data['is_anonymous']) > 1) {
                continue;
            };
            if (intval($data['star']) < 0 || intval($data['star']) > 5) {
                continue;
            };

            if (substr($data['add_time'],0, 10) == '1970-01-01' ||
                substr($data['add_time'],0, 10) == '0000-00-00')
            {
                continue;
            }

            $data['aid'] = $_SESSION['admin']['uid'];
            $data['company_id'] = $_SESSION['admin']['company_id'];
            // 保持到数据库
            $rel   = $comment->saveComment($data);
            $count += $rel;
        }

        echo json_encode(['ret' => 200, 'msg' => 'Excel数据导入成功:' . $count . '条']);
        //删除文件
        unlink($tmpFielPath);
    }
}


elseif (isset($_POST['act']) && $_POST['act'] == 'search'){

    $comment       = new \admin\helper\comment($register);
    $filter        = [];
    $value = trim($_POST['keyword']);
    $productId = $_POST['product_id'];

    if($productId)
    {
        $filter['product_comment.product_id'] = $productId;
    }
    else{
        $filter['product.title[~]'] = '%' . $value . '%';
    }
    $filter['product_comment.is_del'] = 0;

    $data          = $comment->search($filter);
    echo json_encode($data);
}


elseif (isset($_GET['act']) && $_GET['act'] == 'delThumb') {

    $id            = $_GET['id'];
    $where['commont_thumb_id']=$id;
    $num=$db->delete('product_comment_thumb', $where);
    $ret['code'] = $num;
    echo json_encode($ret);
}


else {

    $comment       = new \admin\helper\comment($register);
    $filter        = [];
    $data          = $comment->index($filter);
    $data['admin'] = $_SESSION['admin'];
    echo json_encode($data);
}
