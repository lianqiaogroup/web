<?php 
require_once 'ini.php';

$_act_get = isset($_GET['act']) ? strtolower(trim($_GET['act'])) : NULL;


/**
 *  下载产品原图
 */
if (strcasecmp($_act_get, 'downloadProductOriginImgs') == 0) {

    $productID    = isset($_GET['product_id']) ? $_GET['product_id'] : 0;    
    if (empty($productID)) {
        die("传递参数有误");
    }

    $where = ['product_id' => $productID];
    $result = $register->get('db')->select('product_original_thumb', ['id', 
                                                            'product_id', 
                                                            'type',
                                                            'thumb',
                                                            'add_time'], $where);
    //获取列表 
    $datalist = [];
    if ($result) {
        $logger = new \lib\log();
        $logInfoFile = $logger->writeTmpLog("-" . $productID. "-" .time(), $result);
        array_push($datalist, $logInfoFile);
        foreach ($result as $k1 => $v1) {
            $tmp = explode(",", $v1['thumb']);
            if ($tmp) {
                foreach ($tmp as $k2 => $v2) {
                    array_push($datalist, [$v1['type'] ,app_path . $v2]);
                }
            }
        }
    }
    // 创建图片下载文件夹
    $zipDir = app_path . "/log/productImgZip/";
    if (!file_exists($zipDir)) {
        @mkdir($zipDir,0777,true);
    }
    
    $filename = $zipDir . $productID . "图片信息".time().".zip"; //最终生成的文件名（含路径）   
    if(!file_exists($filename)){   
        $zip = new ZipArchive();//使用本类，linux需开启zlib，windows需取消php_zip.dll前的注释   
        if ($zip->open($filename, ZIPARCHIVE::CREATE)!==TRUE) {   
            exit('无法打开文件，或者文件创建失败');
        }   
        $prefix = [1=>"logo", 2=>"thumb", 3=>"photo", 4=>"attribute", 5=>"combo", 6=>"content", 7=>"cover"];
        // print_r($datalist);exit;
        foreach( $datalist as $val){ 
            if (is_string($val)) {
                if(file_exists($val)){   
                    $zip->addFile($val, basename($val));
                }   
            }
            if (is_array($val) && count($val) == 2) {
                if(file_exists($val[1])){   
                    $zip->addFile($val[1], $prefix[$val[0]] . "-" . basename($val[1]));
                }   
            }
        }   
        $zip->close();//关闭   
    }

    // 删除日志
    if (file_exists($logInfoFile)) {
        @unlink($logInfoFile);
    }

   
    if(!file_exists($filename)){   
        exit("无法找到文件"); //即使创建，仍有可能失败。。。。   
    }
    header("Cache-Control: public"); 
    header("Content-Description: File Transfer"); 
    header('Content-disposition: attachment; filename='.basename($filename)); //文件名   
    header("Content-Type: application/zip"); //zip格式的   
    header("Content-Transfer-Encoding: binary"); //告诉浏览器，这是二进制文件    
    header('Content-Length: '. filesize($filename)); //告诉浏览器，文件大小   
    @readfile($filename);
} 

/**
 * 批量导出产品信息
 */
elseif (strcasecmp($_act_get, 'exportProducts') == 0) {
    //检查请求参数
    $filter = checkoutParam($_act_get); 

    // 查询产品信息列表
    $data = getProductInfoList($register, $filter, $db);

    // 检查产品的信息
    $info = checkProductInfo(app_path,$data, $filter);
 
    echo json_encode(['status'=>200, 'info' => $info]);
} 

// 确认导出到excel
elseif (strcasecmp($_act_get, 'exportconfirm') == 0) 
{
    //检查请求参数
    $filter = checkoutParam($_act_get);

    // 查询产品信息列表
    $productLists = getProductInfoList($register, $filter, $db);
    
    // 创建图片下载文件夹
    $unixtime = time();
    $zipDir = app_path . "log/productTmpZip/" . date('Ymd') . '/' . $unixtime . '/';
    $yesday = app_path . "log/productTmpZip/" . date('Ymd', strtotime("-1 day")) . '/';
    
    if (file_exists($yesday)) {
        @rmdir($yesday);
        unset($yesday);
    }
    if (!file_exists($zipDir)) {
        @mkdir($zipDir,0777,true);
    }

    // 导出excel文件 
    $file = saveExcel($productLists, $zipDir);
    if ($file) {
        findProductOriginImg($productLists, app_path, $zipDir);
    }
    // 压缩
    $phpZip = new \admin\helper\PhpZip($register);
    $outPath = app_path ."/log/productTmpZip/" . date('Ymd') . '/';
    // $phpZip->zip($zipDir, $outPath . $unixtime . '.zip');
    $phpZip->zipAndDownload($zipDir);
}

// 导入产品压缩包
elseif (strcasecmp($_act_get, 'importPack') == 0) 
{
    // 上传文件
    $uploadCompressDir = app_path . "/upload/compackages/";
    $uploadInfo = uploadProductPack($uploadCompressDir);
    
    if (strcasecmp($uploadInfo['status'], 'success') == 0) {
        $zipFile = $uploadInfo['file'];
    } else {
        echo json_encode(['code'=>0, 'data' => [], 'info'=>$uploadInfo['message']], JSON_UNESCAPED_UNICODE);
        exit();
    }
    unset($uploadInfo);
    $zipFilePathInfo = pathinfo($zipFile);
    
    // 解压文件
    $extractFloder = $uploadCompressDir . $zipFilePathInfo['filename'] . time() . '/' ;    
    unzipProductPack($uploadCompressDir, $extractFloder, $zipFile);

    // 读取xlxs
    // $extractFloder = "/upload/compackages/15233334911523333921/";
    $filePathDir = $extractFloder;
    $filePath = $filePathDir . "products_info.xlsx";

    if (!file_exists($filePath)) {
        echo json_encode(['code'=>0, 'data' => [], 'info'=>'xlsx文件不存在！'], JSON_UNESCAPED_UNICODE);
        exit();
    }

    // 默认用excel2007读取excel，若格式不对，则用之前的版本进行读取    
    $reader = new PHPExcel_Reader_Excel2007();
    if(!$reader->canRead($filePath)){
        $reader = new PHPExcel_Reader_Excel5();
        if(!$reader->canRead($filePath)){
            echo json_encode(['code'=>0, 'data' => [], 'info'=>'no Excel'], JSON_UNESCAPED_UNICODE);
            exit();
        }
    }
    $PHPExcel = $reader->load($filePath); // 载入excel文件
    $sheet = $PHPExcel->getSheet(0); // 读取第一個工作表
    $highestRow = $sheet->getHighestRow(); // 取得总行数
    $highestColumm = $sheet->getHighestColumn(); // 取得总列数

    
    $product    = new \admin\helper\product($register);
    $departmentList = $product->getDepartmentList(); 
    // var_dump($departmentList);exit;

    /** 循环读取每个单元格的数据 */
    $output = [];
    for ($row = 1; $row <= $highestRow; $row++){//行数是以第1行开始
        if ($row == 1) {
            continue;
        }
        $singleProductData = $sheet->getCell('B'.$row)->getValue();
        $parseData = json_decode($singleProductData, true);
        
        $outputTmp['product_id'] = $parseData['ex_basic']['product_id'];
        $outputTmp['weburl'] = $parseData['ex_basic']['domain'] .'/'. $parseData['ex_basic']['identity_tag']; 
        $outputTmp['erp_number'] = $parseData['ex_basic']['erp_number'];

        // 检查原始图数据量
        $info = existsOriginImg($parseData['ex_originImg'], app_path);
        if ($info) {
            $outputTmp['status'] = 'success';
            $outputTmp['info'] = 'ok';
        } else {
            $outputTmp['status'] = 'success';
            $outputTmp['info'] = '一张原始图片都没有发现';
        }

        array_push($output, $outputTmp);
        unset($outputTmp);
    }
    if ($output) {
        // 记录解压目录
        $_SESSION['admin']['tmp_imp_pro_dir'] = $filePathDir;
        echo json_encode(['code' => 1, 'data' => $output, 'info' => 'success', 'departmentList'=>$departmentList], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['code' => 0, 'data' => $output, 'info' => 'failed, empty produtct list!']);
    }

}

// 开始导入产品
elseif ( strcasecmp($_act_get, 'importproduct') == 0 ) 
{
    // $filePathDir = app_path . "/upload/compackages/15233334911523333921/";
    // $filePath = $filePathDir . "products_info.xlsx";
    // $_SESSION['admin']['tmp_imp_pro_dir'] = $filePathDir;

    // 接收参数
    $productLists = []; // 产品信息
    $productIds = []; // 原产品ID
    if (isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
        $param = json_decode($GLOBALS['HTTP_RAW_POST_DATA'], true);
        foreach ($param as $vp) {
            $productLists[$vp['product_id']] = $vp;
            array_push($productIds, $vp['product_id']);
        }
    } else {
        die('参数为空，无效操作');
    }
    // var_dump($productIds);
    // var_dump($productLists);exit;
    
    // 判断是否记忆解压文件位置所在目录
    if (!isset($_SESSION['admin']['tmp_imp_pro_dir']) || empty($_SESSION['admin']['tmp_imp_pro_dir'])) {
        die('操作异常, 程序自动退出!');
    } else {
        $tmpExtractDir = $_SESSION['admin']['tmp_imp_pro_dir'];
    }

    $filePath =  $tmpExtractDir . 'products_info.xlsx';

    $reader = new PHPExcel_Reader_Excel2007();
    if(!$reader->canRead($filePath)){
        $reader = new PHPExcel_Reader_Excel5();
        if(!$reader->canRead($filePath)){
            echo 'no Excel';
            return ;
        }
    }
    $PHPExcel = $reader->load($filePath); // 载入excel文件
    $sheet = $PHPExcel->getSheet(0); // 读取第一個工作表
    $highestRow = $sheet->getHighestRow(); // 取得总行数
    $highestColumm = $sheet->getHighestColumn(); // 取得总列数
    
    /** 循环读取每个单元格的数据 */
    $output = [];
    $errNeedle = 0;
    for ($row = 1; $row <= $highestRow; $row++){//行数是以第1行开始
        if ($row == 1) {
            continue;
        }

        // 读取excel表中的数据 
        $exBasic = $sheet->getCell('B'.$row)->getValue();
        $exAttr = $sheet->getCell('C'.$row)->getValue();
        $exVideo = $sheet->getCell('D'.$row)->getValue();
        $exBilink = $sheet->getCell('E'.$row)->getValue();
        $exPhotos = $sheet->getCell('F'.$row)->getValue();
        $exOriginImg = $sheet->getCell('G'.$row)->getValue();
        $exComment = $sheet->getCell('H'.$row)->getValue();

        // 组装excel中取出的数据 
        $parseData = [];
        $parseData['ex_basic'] = empty(json_decode($exBasic, true))?[]:json_decode($exBasic, true);
        $parseData['ex_attr'] = empty(json_decode($exAttr, true))?[]:json_decode($exAttr, true);
        $parseData['ex_video'] = empty(json_decode($exVideo, true))?[]:json_decode($exVideo, true);
        $parseData['ex_bilink'] = empty(json_decode($exBilink, true))?[]:json_decode($exBilink, true);
        $parseData['ex_photos'] = empty(json_decode($exPhotos, true))?[]:json_decode($exPhotos, true);
        $parseData['ex_originImg'] = empty(json_decode($exOriginImg, true))?[]:json_decode($exOriginImg, true);
        $parseData['ex_comment'] = empty(json_decode($exComment, true))?[]:json_decode($exComment, true);

        // 取出文件中的content内容
        $content = @file_get_contents( $tmpExtractDir . $parseData['ex_basic']['product_id'] . '.txt');
        $parseData['ex_basic']['content'] = !empty($content) ? $content : '';

        // 销毁一些数据
        unset($exBasic);
        unset($exAttr);
        unset($exVideo);
        unset($exBilink);
        unset($exPhotos);
        unset($exOriginImg);
        unset($exComment);
        unset($content);

        $originProductID = $parseData['ex_basic']['product_id'];
        
        
        if (in_array($originProductID, $productIds)) {

            // 核查信息（检查域名+二级目录. ）
            
            $targetProductInfo = $productLists[$originProductID];
            $b = checkDetailInformation($register, $targetProductInfo);
            unset($targetProductInfo);

            if (!$b['identity']) {
                $errNeedle += 1;
            } 
            $output[$row]['product_id'] = $originProductID;
            $output[$row]['status'] = $b['status'];
            $output[$row]['info'] = $b['info'];
        }
    }

    if ($errNeedle) {
        echo json_encode(['ret' => 202, 'list'=>array_values($output)]);
        exit();
    } else {
        unset($output);
        $output = [];
        for ($row = 1; $row <= $highestRow; $row++){//行数是以第1行开始
            if ($row == 1) {
                continue;
            }
              // 读取excel表中的数据 
            $exBasic = $sheet->getCell('B'.$row)->getValue();
            $exAttr = $sheet->getCell('C'.$row)->getValue();
            $exVideo = $sheet->getCell('D'.$row)->getValue();
            $exBilink = $sheet->getCell('E'.$row)->getValue();
            $exPhotos = $sheet->getCell('F'.$row)->getValue();
            $exOriginImg = $sheet->getCell('G'.$row)->getValue();
            $exComment = $sheet->getCell('H'.$row)->getValue();

            // 组装excel中取出的数据 
            $parseData = [];
            $parseData['ex_basic'] = empty(json_decode($exBasic, true))?[]:json_decode($exBasic, true);
            $parseData['ex_attr'] = empty(json_decode($exAttr, true))?[]:json_decode($exAttr, true);
            $parseData['ex_video'] = empty(json_decode($exVideo, true))?[]:json_decode($exVideo, true);
            $parseData['ex_bilink'] = empty(json_decode($exBilink, true))?[]:json_decode($exBilink, true);
            $parseData['ex_photos'] = empty(json_decode($exPhotos, true))?[]:json_decode($exPhotos, true);
            $parseData['ex_originImg'] = empty(json_decode($exOriginImg, true))?[]:json_decode($exOriginImg, true);
            $parseData['ex_comment'] = empty(json_decode($exComment, true))?[]:json_decode($exComment, true);

            // 取出文件中的content内容
            $content = @file_get_contents( $tmpExtractDir . $parseData['ex_basic']['product_id'] . '.txt');
            $parseData['ex_basic']['content'] = !empty($content) ? $content : '';

            // 销毁一些数据
            unset($exBasic);
            unset($exAttr);
            unset($exVideo);
            unset($exBilink);
            unset($exPhotos);
            unset($exOriginImg);
            unset($exComment);
            unset($content);

            $originProductID = $parseData['ex_basic']['product_id'];
            
            if (in_array($originProductID, $productIds)) {

                // 组装数据
                $parseData['ex_basic']['domain'] = $productLists[$originProductID]['domain'];
                $parseData['ex_basic']['identity_tag'] = $productLists[$originProductID]['identity_tag'];
                $parseData['ex_basic']['ad_member_id'] = $productLists[$originProductID]['ad_member_id'];
                $parseData['ex_basic']['ad_member'] = $productLists[$originProductID]['ad_member'];
                $parseData['ex_basic']['ad_member_pinyin'] = '';
                $parseData['ex_basic']['id_zone'] = $productLists[$originProductID]['id_zone'];
                $parseData['ex_basic']['photo_txt'] = $productLists[$originProductID]['domain'] 
                                                      . '/' 
                                                      . $productLists[$originProductID]['identity_tag'];
                $parseData['ex_basic']['oa_id_department'] = $productLists[$originProductID]['oa_id_department'];
                $parseData['ex_basic']['is_del'] = 0;
                $parseData['ex_basic']['id_department'] = 0; // 老erp ID 
                $parseData['ex_basic']['available_zone_ids'] = '';

                // 批量上传图片
                $picInfos = uploadProductImages($parseData, $tmpExtractDir, app_path);

                unset($parseData['ex_basic']['product_id']);
                if ($picInfos) {
                    // 更新parseData信息
                    $parseData = updateParseDataImgInfo($picInfos, $parseData, $originProductID);
                    // 导入产品表信息
                    $r = importProduct($register,$parseData);

                    if ($r) {
                        // 填充成功信息
                        $output[$row]['product_id'] = $originProductID;
                        $output[$row]['status'] = 'success';
                        $output[$row]['info'] = 'ok';
                    } else {
                        // 填充失败信息
                        $output[$row]['product_id'] = $originProductID;
                        $output[$row]['status'] = 'failed';
                        $output[$row]['info'] = '写数据库失败';
                    }
                }
            }
        }
        echo json_encode(['ret' => 200, 'list'=>array_values($output)]);
        exit();
    }


    

    
        
    


    

    
}
// 获取部门成员
elseif ( strcasecmp($_act_get, 'getMembersByDpID') == 0 )
{
    $departmentID = isset($_GET['department_id']) ? $_GET['department_id'] : '';
    if (empty($departmentID)) {
        die('参数为空，无效操作');
    }

    $product = new \admin\helper\product($register);
    $dptMembers = $product->getMembersByDpID($departmentID);
    $tmp = [];
    if (!empty($dptMembers)) {
        foreach ($dptMembers as $key => $value) {
            if (!empty($value['username'])) {
                array_push($tmp, $value);
            }
        }
    }
    echo json_encode(['ret'=>$tmp]);
}

// 获取员工的可用域名
elseif ( strcasecmp($_act_get, 'getDomainAndAreaByLoginID') == 0 )
{
    $c = new \admin\helper\product($register);
    $r = [];
    if(empty($_GET['loginid']) || empty($_GET['id_department']) || empty($_GET['erp_number'])){
        
    }else{
        if($_GET['loginid']){
            $loginid = $_GET['loginid'];
        }
        if($loginid){
            // 根据loginID查域名
            $id_department = $_GET['id_department'];
            $params = [];
            $params['loginid'] = $loginid;
            $params['id_department'] = $id_department;

            $r = $c->getSeoDomain($params);
            if(empty($r) || empty($r['ret'])){
                $r = [];
            }else{
                unset($r['ret']);
                if (isset($_GET['key']) && $_GET['key'] == 'site') {
                    //查询已经用到的域名
                    $siteModel = new \admin\helper\site($register);
                    $condition['oa_id_department'] = $_GET['id_department'];
                    $sites = $siteModel->getAllSites($condition);
                    //用于保存没有使用到的数据
                    $temp_array = array();
                    if (!empty($sites['goodsList'])) {
                        $sitesArray = array_column($sites['goodsList'], 'domain');
                        //遍历域名数组 查找哪些域名被使用过
                        foreach ($r as $key => $value) {
                            $temp_domain = 'www.'.$value['domain'];
                            if (!in_array(strtolower($temp_domain), $sitesArray)) {
                                $temp_array[] = $value;
                            }
                        }
                        //重新赋给数组新的值
                        $r = $temp_array;
                    }
                }
            }

            // 根据商品的loginID获取商品可投放地区
            $productZoneList = []; // 可投放地区列表
            $companyID = $db->select('oa_users', 'company_id', ['id_department' => $id_department, 'username'=>$loginid]);
            
            if ($companyID) {
                $paramsTmp = ['company_id'=>$companyID,'loginid'=>$loginid,'id'=>$_GET['erp_number']];

                $objname = 'admin\helper\api\erpproduct';
                $obj = new $objname();
                $retResult = $obj->getProduct($paramsTmp);
                if(is_array($retResult['product']['productZoneNames']) && (count($retResult['product']['productZoneNames']) >0) ) {
                     $product    = new \admin\helper\product($register);
                    $productZoneList = $product->getZoneList($retResult['product']['productZoneNames']);
                    unset($retResult);
                }
            }
        }
    }
    echo json_encode(['domainList'=>$r, 'productZoneList'=>$productZoneList]);
}

/**
 *  保留
 */
else {

}


/**
 * [uploadProductPack 上传文件]
 * @return [type] [description]
 */
function uploadProductPack($uploadCompressDir){
    // 创建压缩包上传文件夹  
    if (!file_exists($uploadCompressDir)) {
        @mkdir($uploadCompressDir,0777,true);
    }

    $fileUpload = new \admin\helper\FileUpload();
    //设置属性(上传的位置， 大小， 类型， 名是是否要随机生成)
    $fileUpload->set("path", $uploadCompressDir);
    $fileUpload->set("allowtype", array("zip"));
    $fileUpload->set("israndname", false);

    //使用对象中的upload方法， 就可以上传文件， 方法需要传一个上传表单的名子 pic, 如果成功返回true, 失败返回false
    $res = [];
    if ($fileUpload->upload("zipFile")) {
        $zipFile = $fileUpload->getFileName();
        $res = ['status'=>'success', 'file'=>$zipFile, 'message'=>''];
    } else {
        $message = $fileUpload->getErrorMsg();
        $res = ['status'=>'falied', 'file'=>'', 'message'=>$message];

    }
    return $res;
}


/**
 * [unzipProductPack 解压缩产品]
 * @author leonchou130
 * @param  [type] $extractFloder [解压缩产品包]
 * @param  [type] $zipFile       [description]
 * @return [type]                [description]
 */
function unzipProductPack($uploadCompressDir, $extractFloder, $zipFile)
{
    // 准备解压文件夹
    if (!file_exists($extractFloder)) {
        @mkdir($extractFloder,0777,true);
    }

    // 解压
    $phpZip = new \admin\helper\PhpZip($register);
    $unzipfile   = $uploadCompressDir . $zipFile;
    $savepath  = $extractFloder;
    $array     = $phpZip->getZipInnerFilesInfo($unzipfile);
    $filecount = 0;
    $dircount  = 0;
    $failfiles = array();
    set_time_limit(90);  // 修改为不限制超时时间(默认为30秒)
    // for($i=0; $i<count($array); $i++) {
    //     if($array[$i]['folder'] == 0){
    //         if($phpZip->unZip($unzipfile, $savepath, $i) > 0){
    //             $filecount++;
    //         }else{
    //             $failfiles[] = $array[$i][filename];
    //         }
    //     }else{
    //         $dircount++;
    //     }
    // }
    $phpZip->unZipTmp($unzipfile, $savepath);
    set_time_limit(30);
    // printf("文件夹:%d&nbsp;&nbsp;&nbsp;&nbsp;解压文件:%d&nbsp;&nbsp;&nbsp;&nbsp;失败:%d<br>\r\n", $dircount, $filecount, count($failfiles)); 
    // if(count($failfiles) > 0){
    //    foreach($failfiles as $file){
    //        printf("&middot;%s<br>\r\n", $file);
    //    }
    // } 
    return ['total'=>count($array)];
}

/**
 * [checkDetailInformation 检查产品的详细信息]
 * @param  array  $productInfo [description]
 * @return [type]              [description]
 */
function checkDetailInformation($register, $productInfo=[])
{
    if ($productInfo) {
        $info = '';
        $needle = 0;
        
        //检查参数值是否有空
        if (empty($productInfo['product_id'])) {
            $info .= 'product_id是空的';
            $needle += 1;
        }
        if (empty($productInfo['domain'])) {
            $info .= 'domain是空的';
            $needle += 1;
        }
        if (empty($productInfo['identity_tag'])) {
            $info .= 'identity_tag是空的';
            $needle += 1;
        }
        if (empty($productInfo['ad_member_id'])) {
            $info .= 'ad_member_id是空的';
            $needle += 1;
        }
        if (empty($productInfo['ad_member'])) {
            $info .= 'ad_member是空的';
            $needle += 1;
        }
        if (empty($productInfo['id_zone'])) {
            $info .= 'id_zone是空的';
            $needle += 1;
        }
        if (empty($productInfo['id_zone_name'])) {
            $info .= 'id_zone_name是空的';
            $needle += 1;
        }
        if (empty($productInfo['oa_id_department'])) {
            $info .= 'oa_id_department是空的';
            $needle += 1;
        }

        // 检查域名+二级目录是否有重复
        if (!empty($productInfo['domain'] && !empty($productInfo['identity_tag']))) {
            $pro = new \admin\helper\product($register);

            $res = $pro->domainCheck($productInfo['domain'], $productInfo['identity_tag'], 0);
            unset($pro);
            if ($res['ret'] == 0) {
                $info .= '域名和二级目录有重复';
                $needle += 1;
            }
        }
    } else {
         $res = ['identity' => false, 'status' => 'failed', 'info' => '没有上传参数'];
    }

    if ($needle > 0) {
        $res = ['identity' => false, 'status' => 'failed', 'info' => ''];
        $res['info'] = $info;
    } else {
        $res = ['identity' => true, 'status' => 'success', 'info' => 'ok'];
    }

    return $res;
}

/**
 * [uploadProductImages 复制后批量上传产品图片]
 * @param  [type] $parseData  [解析的数据]
 * @param  [type] $picPathDir [图片路径]
 * @return [type]             [description]
 */
function uploadProductImages($parseData, $picPathDir, $app_path)
{
    // 创建原始图片文件夹
    $rootPath = $app_path."upload/";
    $path = 'origin/'. date('y-m-d',time()).'/';
    if (!file_exists($rootPath . $path)) {
        @mkdir($rootPath . $path,0777,true);
    }

    // 上传图片
    $basic = $parseData['ex_basic'];
    $originImgs = $parseData['ex_originImg'];
    $waterText = $basic['photo_txt']; // 水印文字
    unset($parseData); // 数据大，及时释放

    $tmpProductPicPath = $picPathDir . $basic['product_id'] . '/'; // 目标产品图片临时目录
    $uploadImgInfo = [];
    if (!empty($originImgs) && file_exists($tmpProductPicPath)) {
        foreach ($originImgs as $k => $v) {
            if ($v['thumb']) {
                // 属于图片的类型 
                $type = $v['type'];
                $fgID = $v['fg_id'];

                $upInfo = '';
                $tmp = explode(',', $v['thumb']);

                if (count($tmp) == 1 ) {
                    $picName = pathinfo($tmp[0]);
                    if (file_exists($tmpProductPicPath . $picName['basename'])) {
                        // 图片复制到原路径下
                        $newOriginImgFile = $rootPath . $path . $picName['basename'];
                        @copy($tmpProductPicPath . $picName['basename'], $newOriginImgFile); 
                        // 上传图片到七牛
                        $upInfo = uploadImgToQiniu($newOriginImgFile, $type, '', $fgID, $waterText);
                    }
                } 

                if (count($tmp) == 2 ) {
                    $picName = pathinfo($tmp[1]);
                    if (file_exists($tmpProductPicPath . $picName['basename'])) {
                         // 图片复制到原路径下
                        $newOriginImgFile = $rootPath . $path . $picName['basename'];
                        @copy($tmpProductPicPath . $picName['basename'], $newOriginImgFile); 
                        // 上传图片到七牛
                        $upInfo = uploadImgToQiniu($newOriginImgFile, $type, $tmp[0], $fgID, $waterText);
                    }
                }    

                // 填充
                if (!empty($upInfo)) {
                    array_push($uploadImgInfo, $upInfo);
                }
            }
        }
    } 
    return $uploadImgInfo;
}

/**
 * [uploadImgToQiniu 上传图片到七牛]
 * @param  [type] $imgFile [description]
 * @return [type]          [description]
 */
function  uploadImgToQiniu($imgFile,  $type, $originQiniuUrl = '', $fgID = '', $waterText='')
{

    $result = '';
    if ($type != 5) { // combo 忽略
        $uploadOB = new \admin\helper\upload();
        // 设置上传文件
        $bn = $uploadOB->setUploadFile($imgFile);

        if (file_exists($imgFile) && $bn) {

            //加水印
            $files = $uploadOB->imgTxt($imgFile, $waterText);

            // 上传加了水印的图片
            $tmp = [0 => 'null', 1 => 'logo', 2 => 'thumb', 3=>'photo', 4=>'attribute', 5=>'combo', 6=>'content', 7=>'cover'];
            $typeName = $tmp[$type];
            $result = \admin\helper\qiniu::uploadImgTxt($typeName,$files);

            // 更改url域名
            $result['url'] = \admin\helper\qiniu::changImgDomain($result['url']);

            $result['o_type'] = $type;
            $result['o_fg_id'] = $fgID;
            $result['origin_qiniu_url'] = $originQiniuUrl;

            $imgInfo = pathinfo($imgFile);
            $path = '/upload/origin/'. date('y-m-d',time()).'/';
            $result['original_name'] = $path . $imgInfo['basename'];
        }
    }

    return $result;
}

/**
 * [updateParseDataImgInfo 根据返回的信息，更新parseData的数据信息]
 * @param  [type] $picInfos [description]
 * @return [type]           [description]
 */
function updateParseDataImgInfo($picInfos, $parseData, $originProductID)
{
    // 保存新的原图信息
    $exNewOriginImg = [];

    if ($picInfos && $parseData) {
        foreach ($picInfos as $k => $v) {
            // 业务中的类型 
            // type => '1:logo 2:thumb 3:photo 4:attribute 5:combo 6:content 7:cover';
            switch ($v['o_type']) {
                case 1: // logo
                    $parseData['ex_basic']['logo'] = $v['url'];
                    $ogi_thumb = $v['original_name'];
                    break;

                case 2: // thumb 缩略图 
                    $parseData['ex_basic']['thumb'] = $v['url'];
                    $ogi_thumb = $v['original_name'];
                    break;

                case 3: // 图集
                    if (empty($parseData['ex_photos'])) {
                        $appendThumb = [];
                        $appendThumb['thumb_id'] = 0;
                        $appendThumb['product_id'] = $originProductID;
                        $appendThumb['thumb'] = $v['url'];
                        $appendThumb['add_time'] = date('Y-m-d H:i:s');
                        array_push($parseData['ex_photos'], $appendThumb);
                        unset($appendThumb);
                    } else {
                        $needle = true;
                        if ($v['o_fg_id'] != 0) {
                            foreach ($parseData['ex_photos'] as $kp => $vp) {
                                if ($v['o_fg_id'] == $vp['thumb_id']) {
                                    $parseData['ex_photos'][$kp]['thumb'] = $v['url'];
                                    $needle = false;
                                    break;
                                }
                            }
                        }

                        if ($needle) {
                            $appendThumb = [];
                            $appendThumb['thumb_id'] = 0;
                            $appendThumb['product_id'] = $originProductID;
                            $appendThumb['thumb'] = $v['url'];
                            $appendThumb['add_time'] = date('Y-m-d H:i:s');
                            array_push($parseData['ex_photos'], $appendThumb);
                            unset($appendThumb);
                        }
                    }
                    $ogi_thumb = $v['original_name'];
                    break;

                case 4: // 属性
                    if (!empty($parseData['ex_attr'])) {
                        if ($v['o_fg_id'] != 0) {
                            foreach ($parseData['ex_attr'] as $kp => $vp) {
                                if ($v['o_fg_id'] == $vp['product_attr_id']) {
                                    $parseData['ex_attr'][$kp]['thumb'] = $v['url'];
                                    break;
                                }
                            }
                        }
                    }
                    $ogi_thumb = $v['url'] . ',' . $v['original_name'];
                    break;

                case 5: // combo 忽略
                    $ogi_thumb = $v['original_name'];
                    break;

                case 6:
                    $content = $parseData['ex_basic']['content'];
                    if (strpos($content, $v['origin_qiniu_url'])) {
                        $content = str_replace($v['origin_qiniu_url'], $v['url'], $content);
                        $parseData['ex_basic']['content'] = $content;
                    }
                    $ogi_thumb = $v['url'] . ',' . $v['original_name'];
                    break;

                case 7: // 视频封面
                    $parseData['ex_video']['cover_url'] = $v['url'];
                    $ogi_thumb = $v['original_name'];
                    break;

                default:
                    // 不处理
                    break;
            }

            if ($v['o_type'] != 5) {
                //重新封装图片数据 保存到产品原图表
                $temp_data['product_id'] = $originProductID;
                $temp_data['type'] = $v['o_type'];
                $temp_data['fg_id'] = $v['o_fg_id'];
                $temp_data['thumb'] = $ogi_thumb;
                $temp_data['add_time'] = time();
                array_push($exNewOriginImg, $temp_data);
                unset($temp_data);
            }
        }
    }

    $parseData['ex_originImg'] = $exNewOriginImg;
    return $parseData;
}

/**
 * [importProduct 导入产品]
 * @param  array  $productInfo [description]
 * @return [type]              [description]
 */
function importProduct($register, $productInfo = [])
{
    $DB = $register->get('db');
   
    $productBasicInfo = $productInfo['ex_basic'];
    $productAttrInfo = $productInfo['ex_attr'];
    $productVideoInfo = $productInfo['ex_video'];
    $productBilinkInfo = $productInfo['ex_bilink'];
    $productPhotosInfo = $productInfo['ex_photos'];
    $productOriginImgInfo = $productInfo['ex_originImg'];
    $productCommentInfo = $productInfo['ex_comment'];
    unset($productInfo);
    // var_dump($productCommentInfo);exit;
    
    // 开启事务
    $DB->pdo->beginTransaction(); 

    // 1） 产品基本信息(product表)
    $DB->insert("product", $productBasicInfo);
    $sql = $DB->last();
    $newProductID = $DB->id();
    

    // 2) 产品属性(product_attr) 4
    $attrRflecs = insertProductAttrs($DB, $productAttrInfo, $newProductID);
    
     // 3) 产品视频(product_video)
    insertProductVideo($DB, $productVideoInfo, $newProductID);

    // 4) 产品广告(product_blink 已经取消)
    insertProductBilink($DB, $productBilinkInfo, $newProductID);
    
    // 5) 产品图集(product_thumb) 3
    $photoRflecs =insertProductThumb($DB, $productPhotosInfo, $newProductID);
    
    // 6) 产品评论 (product_comment)
    insertProductComments($DB, $productCommentInfo, $newProductID);

    // 更改$productOriginImgInfo产品属性fg_id 
    $productOriginImgInfo = changeProductOriginImgInfo($productOriginImgInfo, $attrRflecs, $photoRflecs);

    // 7) 产品原始图片(product_original_thumb) 原始图片最后处理
    insertProductOriginalThumb($DB, $productOriginImgInfo, $newProductID);

    //通知erp建站 
    $seoLoginID = $DB->get('oa_users', 'username', ['uid' => $productBasicInfo['ad_member_id']]);
    $product = new \admin\helper\product($register);
    $erpResult = $product->sendDomainToErp(trim($productBasicInfo['erp_number']), trim($productBasicInfo['domain']), $productBasicInfo['identity_tag'], $productBasicInfo['id_zone'], $newProductID, $seoLoginID);

    if ($erpResult['ret']) {
        // 提交事务
        $ci = $DB->pdo->commit();
    }else{
        $DB->pdo->rollBack();
        $ci = false;
    }

    return $ci ? ['newProID' => $newProductID] : false;
}

/**
 * [insertProductOriginalThumb 批量插入原始图片]
 * @param  [type] $DB                   [description]
 * @param  [type] $productOriginImgInfo [description]
 * @param  [type] $newProductID         [description]
 * @return [type]                       [description]
 */
function insertProductOriginalThumb($DB, $productOriginImgInfo, $newProductID)
{
    if ($DB && $productOriginImgInfo && $newProductID) {
        foreach ($productOriginImgInfo as $key => $value){
            if (isset($productOriginImgInfo[$key]['id'])) {
                unset($productOriginImgInfo[$key]['id']);
            }
            $productOriginImgInfo[$key]['product_id'] = $newProductID;
        }
        $DB->insert('product_original_thumb', $productOriginImgInfo);
    }
}

/**
 * [changeProductOriginImgInfo 更新productOriginImgInfo]
 * @param  [type] $productOriginImgInfo [原始信息]
 * @param  [type] $attrRflecs           [属性拷入数据库要更新数据]
 * @param  [type] $photoRflecs          [图集要更新]
 * @return [type]                       [description]
 */
function changeProductOriginImgInfo($productOriginImgInfo, $attrRflecs, $photoRflecs)
{
    if (count($productOriginImgInfo)) {
        foreach ($productOriginImgInfo as $k => $v) {
            if ($v['type'] == 4 && !empty($attrRflecs) && in_array($v['fg_id'], array_keys($attrRflecs))) {
                $productOriginImgInfo[$k]['fg_id'] = $attrRflecs[$v['fg_id']];
            }
            if ($v['type'] == 3 && !empty($photoRflecs) && in_array($v['fg_id'], array_keys($photoRflecs))) {
                $productOriginImgInfo[$k]['fg_id'] = $photoRflecs[$v['fg_id']];
            }
        }
    }
    return $productOriginImgInfo;
}

/**
 * [insertProductThumb 批量插入图集]
 * @param  [type] $DB                [description]
 * @param  [type] $productPhotosInfo [description]
 * @param  [type] $newProductID      [description]
 * @return [type]                    [description]
 */
function  insertProductThumb($DB, $productPhotosInfo, $newProductID)
{
    $rflect = [];
    if ($DB && $productPhotosInfo && $newProductID) {
        foreach ($productPhotosInfo as $key => $value) {
            $originProductThumbId = $productPhotosInfo[$key]['thumb_id'];
            unset($productPhotosInfo[$key]['thumb_id']);
            $productPhotosInfo[$key]['product_id'] = $newProductID;
            $DB->insert("product_thumb", $productPhotosInfo[$key]);
            $thumbId = $DB->id();
            $rflect[$originProductThumbId] = $thumbId; 
        }
    }
    return $rflect;
}

/**
 * [insertProductBilink 批量插入广告(不保留)]
 * @param  [type] $DB                [description]
 * @param  [type] $productBilinkInfo [description]
 * @param  [type] $newProductID      [description]
 * @return [type]                    [description]
 */
function insertProductBilink($DB, $productBilinkInfo, $newProductID)
{
    // if ($DB && $productBilinkInfo && $newProductID) {
    //     foreach ($productBilinkInfo as $key => $value){
    //         unset($productBilinkInfo[$key]['id']);
    //         $productBilinkInfo[$key]['product_id'] = $newProductID;
    //     }
    //     $DB->insert('product_bilink', $productBilinkInfo);
    // }
}

/**
 * [insertProductVideo 插入视频]
 * @param  [type] $DB               [description]
 * @param  [type] $productVideoInfo [description]
 * @param  [type] $newProductID     [description]
 * @return [type]                   [description]
 */
function insertProductVideo($DB, $productVideoInfo, $newProductID)
{
    if ($DB && $productVideoInfo && $newProductID) {
        unset($productVideoInfo['id']);
        $productVideoInfo['product_id'] = $newProductID;
        $DB->insert('product_video', $productVideoInfo);
    }
}

/**
 * [insertProductAttrs 批量插入产品属性]
 * @param  [type] $DB              [description]
 * @param  [type] $productAttrInfo [description]
 * @param  [type] $newProductID    [description]
 * @return [type]                  [description]
 */
function insertProductAttrs($DB, $productAttrInfo, $newProductID)
{
    $rflect = [];
    if ($DB && count($productAttrInfo) &&  $newProductID) {
        foreach ($productAttrInfo as $key => $value) {
            $originProductAttrId = $productAttrInfo[$key]['product_attr_id'];
            unset($productAttrInfo[$key]['product_attr_id']);
            $productAttrInfo[$key]['product_id'] = $newProductID;
            $DB->insert("product_attr", $productAttrInfo[$key]);
            $attrId = $DB->id();
            $rflect[$originProductAttrId] = $attrId; 
        }
    }
    return $rflect;
}

/**
 * [insertProductComments 批量插入产品评论]
 * @param  [type] $DB                 [description]
 * @param  [type] $productCommentInfo [description]
 * @param  [type] $newProductID       [description]
 * @return [type]                     [description]
 */
function insertProductComments($DB, $productCommentInfo, $newProductID)
{
    if ($DB && count($productCommentInfo) && $newProductID) {
        $commentWithNoThumb = [];
        foreach ($productCommentInfo as $kc => $vc) {
            unset($vc['comment_id']);
            if (empty($vc['thumb'])) {
                $vc['product_id'] = $newProductID;
                array_push($commentWithNoThumb, $vc);
            }else{
                
                $commentThumb = $vc['thumb'];
                unset($vc['product_id']);
                $DB->insert("product_comment", $vc);
                $tmpCommentID = $DB->id();
                $DB->insert("product_comment_thumb", ['comment_id' => $tmpCommentID,
                                                      'thumb' => $commentThumb
                                                ]);
                
            }
        }
        $DB->insert("product", $commentWithNoThumb);
    }
}

/**
 * [findProductOriginImg 查找产品的原图]
 * @param  array  $productLists [description]
 * @param  [type] $zipDir       [description]
 * @return [type]               [description]
 */
function findProductOriginImg($productLists=[], $app_path, $zipDir)
{
   if (count($productLists)) {
       foreach ($productLists as $key => $pro) {
            // 创建商品文件夹
            $productDir = $zipDir. $pro['ex_basic']['product_id'] . '/';
            if (!file_exists($productDir)) {
                @mkdir($productDir,0777,true);
            }

            // 复制图片
            if ($pro['ex_originImg']) {
                foreach ($pro['ex_originImg'] as $value) {
                    // $thumb = $app_path . $value['thumb'];
                    $tmp = explode(",", $value['thumb']);
                    if (count($tmp) == 1 && file_exists($app_path . $tmp[0])) {
                        $fileInfo = pathinfo($app_path . $tmp[0]);
                        @copy($app_path . $tmp[0], $productDir . $fileInfo['basename']); 
                        
                    } 
                    if (count($tmp) == 2 && file_exists($app_path . $tmp[1])) {
                        $fileInfo = pathinfo($app_path . $tmp[1]);
                        @copy($app_path . $tmp[1], $productDir . $fileInfo['basename']); 
                    } 
                }
            } 
       }
   }
}

/**
 * [saveExcel 保存excel文件]
 * @param  array  $productLists [产品的相关信息]
 * @return [type]       [返回相关信息]
 */
function saveExcel($productLists=[], $zipDir)
{
    //导出时间
    $time = date('y-m-d H:i:s', time());
    //导出Excel
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->getProperties()->setCreator("产品导出信息")
        ->setLastModifiedBy("产品信息")
        ->setTitle("产品信息")
        ->setSubject("产品信息")
        ->setDescription("产品导出信息")
        ->setKeywords("excel")
        ->setCategory("product file");

    $filename = 'products_info.xlsx'; // 未知原因， 压缩的时候少了个e

    // 设置单元格
    $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A1', '序号')
        ->setCellValue('B1', '产品基本信息')
        ->setCellValue('C1', '产口属性信息')
        ->setCellValue('D1', '产品视频信息')
        ->setCellValue('E1', '产品链接信息')
        ->setCellValue('F1', '产品图集信息')
        ->setCellValue('G1', '产品原始图片信息')
        ->setCellValue('H1', '产品评论信息');

    // 填充数据
    foreach ($productLists as $key => $v) {
        $number = $key + 1;
        // 将基本信息content中的内容保存在文本文件中;
        $content = $v['ex_basic']['content'];
        @file_put_contents($zipDir . $v['ex_basic']['product_id'] . '.txt', $content);
        $content = null;
        unset($v['ex_basic']['content']);
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A'.($key + 2), $number)
            ->setCellValue('B'.($key + 2), json_encode($v['ex_basic']))
            ->setCellValue('C'.($key + 2), json_encode($v['ex_attr']))
            ->setCellValue('D'.($key + 2), json_encode($v['ex_video']))
            ->setCellValue('E'.($key + 2), json_encode($v['ex_bilink']))
            ->setCellValue('F'.($key + 2), json_encode($v['ex_photos']))
            ->setCellValue('G'.($key + 2), json_encode($v['ex_originImg']))
            ->setCellValue('H'.($key + 2), json_encode($v['ex_comment']));
    }
   
    // 清楚缓存
    ob_end_clean();

    //构建表头 输出文件信息
    $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save($zipDir . $filename);
    return file_exists($zipDir . $filename) ? $filename : false;
}

/**
 * 检查请求参数
 * @return array
 */
function checkoutParam($act = "exportconfirm")
{
    //不存在查询条件
    if (!isset($_POST['list']) || empty($_POST['list'])) {
        if ($act == "exportconfirm") {
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
        if ($act == "exportconfirm") {
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
 * 查询产品信息列表
 * @param $register
 * @return array|null
 */
function getProductInfoList($register, $filter, $db)
{
    // 1)产品基本信息(product)
    $productData = [];
    $productHelper = new \admin\helper\product($register);
    $basicProductInfos = $productHelper->selectedProductList($filter);
    
    if (count($basicProductInfos)) {
        foreach ($basicProductInfos as $key => $basicProduct) {
            // 1) 产品属性(product_attr)
            $attr = $productHelper->getProduct_group_attr($basicProduct['product_id']);

            // 2) 产品视频(product_video)
            $videoModel = new \admin\helper\productvideo($register);
            $video = $videoModel->getProductVideo($basicProduct['product_id']);
           
            // 3) 产品广告(product_blink)
            // $bilink = $db->select('product_bilink', '*', ["ORDER" => ['add_time' => "DESC"], 'product_id' => $basicProduct['product_id']]);
            $bilink = [];

            // 4) 产品图集(product_thumb)
            $photos = $db->select('product_thumb', '*', ["ORDER" => ['add_time' => "DESC"], 'product_id' => $basicProduct['product_id']]);
            
            // 5) 产品原始图片(product_original_thumb)
            $originImg = $db->select('product_original_thumb', '*', ["ORDER" => ['add_time' => "DESC"], 'product_id' => $basicProduct['product_id']]);

            // 6) 产品评论(product_comment)
            $commentSql = "select c.*, t.thumb from `product_comment` as c left join `product_comment_thumb` as t on c.comment_id = t.comment_id where 1=1 and c.product_id=" . $basicProduct['product_id'];
            $commentData = $db->query($commentSql)->fetchAll(\PDO::FETCH_ASSOC);

            // 保存到数据中
            $productData[$key]['ex_basic'] = $basicProduct;
            $productData[$key]['ex_attr'] = $attr;
            $productData[$key]['ex_video'] = $video;
            $productData[$key]['ex_bilink'] = $bilink;
            $productData[$key]['ex_photos'] = $photos;
            $productData[$key]['ex_originImg'] = $originImg;
            $productData[$key]['ex_comment'] = $commentData;

        }
    }

    return $productData;
}

/**
 * [checkProductInfo 检查产品的信息完整性]
 * @param  Array|array $productInfo [返回产品数据]
 * @return [type]                   []
 */
function checkProductInfo($app_path, $productsInfos = [],$array_filter=[])
{
    $needle = [];
    $reason = '';
    if (!empty($productsInfos)) {
        $tmpPrdoctIDs = [];
        foreach ($productsInfos as $k => $vProduct) {
            $info = "";
            array_push($tmpPrdoctIDs,  $vProduct['ex_basic']['product_id']);
            // 忽略检查文本

            // 检查图片
            $info = existsOriginImg($vProduct['ex_originImg'], $app_path);
            if ($info) {
                array_push($needle, ['product_id'=> $vProduct['ex_basic']['product_id'], 
                                     'weburl' => $vProduct['ex_basic']['domain'] .'/'. $vProduct['ex_basic']['identity_tag'],
                                     'status' => 'success',
                                     'info' => $reason
                                     ]);
            } else {
                array_push($needle, ['product_id'=> $vProduct['ex_basic']['product_id'], 
                                     'weburl' => $vProduct['ex_basic']['domain'] .'/'. $vProduct['ex_basic']['identity_tag'],
                                     'status' => 'failed',
                                     'info' => '该产品找不到一张原图' ]);

            }
        }

        // 查出没有在数据中的产品ID
        $tmp = array_diff($array_filter, $tmpPrdoctIDs);
        foreach ($tmp as $kt => $vt) {
            array_push($needle, ['product_id'=> $vt, 
                                 'weburl' => '',
                                 'status' => 'failed',
                                 'info' => '不存在该ID的产品信息' ]);
        }
    } 
    return $needle;
}


/**
 * [existsOriginImg 检查原始图片的存在性]
 * @return [type] [description]
 */
function existsOriginImg($result, $app_path)
{
    // 检测图片是否存在
    $res = 0;
    if ($result) {
        foreach ($result as $k1 => $v1) {
            $tmp = explode(",", $v1['thumb']);
            
            if (count($tmp) == 1 && file_exists($app_path . $tmp[0])) {
                 $res += 1;
                 break;
            } 
            if (count($tmp) == 2 && file_exists( $app_path . $tmp[1])) {
                $res += 1;
                break; 
            } 
        }

    }

    // 检测原图个数(忽略)

    return $res;
}


?>