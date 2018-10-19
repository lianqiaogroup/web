<?php
/**
 * 软文站管理界面
 */
require_once 'ini.php';
$stsiteModel = new \admin\helper\stsiteModel($register); 

/**
 * 新增不用返回任何数据
 */
if($_GET['act'] && $_GET['act']=='addStsite') {
    echo json_encode([]);exit; 
}
/**
 * 新增保存数据
 */
elseif($_GET['act'] && $_GET['act']=='postAddStsite') 
{   
    // 获取参数
    $request = $_POST;
    $request['_FILES'] = $_FILES;
    // $request['site_content'] = "121jsaffjkfjsjfjkx厢工 百里西餐厅在工工";
    // $request['comment_link'] = "http://www.baidu.com/xxx";
    // 检查信息是否完整 
    $b = checkoutParam($stsiteModel,$request, 'insert');
    
    if ($b['status']) {
        // 保存基本表信息
        $stsiteID = insertStsiteBasic($stsiteModel, $request);
        
        if ($stsiteID) {
            //保存评论
            $b['status'] = insertStsiteComment($stsiteModel, $request, $stsiteID);
            if (!$b['status'] ) {
                $b['info'] = '保存评论失败';
            }
        } else {
            $b['status'] = false;
            $b['info'] = '保存基本表信息失败';

        }
    }
    
    $ret = $b['status'] ? '200' : '500';
    echo json_encode(['ret' => $ret, 'info'=>$b['info']]);exit;

    
}
elseif($_GET['act'] && $_GET['act']=='getEditStsite')
{
    $stsiteID = I('get.stsite_id');
    if ($stsiteID) {
        $data = $stsiteModel->getStsiteInfo($stsiteID);
        $data['site_content'] = \admin\helper\qiniu::get_content_path( $data['site_content']);
        $data['author_thumb'] = \admin\helper\qiniu::get_image_path($data['author_thumb']);
        $data['site_master_graph'] = \admin\helper\qiniu::get_image_path($data['site_master_graph']);
        $data['product_img'] = \admin\helper\qiniu::get_image_path($data['product_img']);
        $data['site_bottom_img'] = \admin\helper\qiniu::get_image_path($data['site_bottom_img']);
    }
    $data['admin']   = $_SESSION['admin'];
    echo json_encode($data);exit;
}
/**
 * 编辑保存数据
 */
elseif($_GET['act'] && $_GET['act']=='postEditStsite')
{
    // 获取参数
    $request = $_POST;
    $request['_FILES'] = $_FILES; 
    // 检查信息是否完整 
    $except = ['author_thumb', 'site_master_graph', 'product_img', 'site_bottom_img'];
    $b = checkoutParam($stsiteModel,$request, 'update', $except); 
    if (!isset($request['id'])) {
        $b['status'] =false;
        $b['info'] = 'request中没有站点ID';
    }

    if ($b['status']) {
        // 保存基本表信息
        updateStsiteBasic($stsiteModel, $request);
        updateStsiteComment($stsiteModel, $request, $request['id']);
        
    }
    $status = $b['status'] ? 200 : 500;
    echo json_encode(['ret' => $status, 'info' => $b['info']]);exit;
}
/**
 * 显未页面
 */
 else {
    $filter        = [];
    // 域名
    if (!empty($_GET['domain'])) {
        $domain  = $_GET['domain'];
        if(strpos($domain,'http')!==false)
        {
            $domain = trim(str_replace('http://',"",$domain));
        }
        $blog = null;
        if(strpos($domain,'/')!==false)
        {
            list($mapDomain,$blog,$tag) = explode('/',$domain);
            if (!empty(trim($tag))) {
                $filter['identify_tag'] = trim($tag);
            }
        }
        else{
            $mapDomain = $domain;
        }
        $filter['domain[~]'] = ["%" . $mapDomain . '%'];
    }
    // 是否已经删除
    // if (!empty($_GET['is_del'])) {
    //     if ($_GET['is_del'] != -1) {
    //         if($_GET['is_del'] == 1){
    //             $filter['is_del'] = [$_GET['is_del'],10];
    //         }else{
    //             $filter['is_del'] = $_GET['is_del'];
    //         }
    //     }
    //     $info['is_del'] = $_GET['is_del'];
    // }
    // else {
    //     $info['is_del']   = $filter['is_del'] = 0;
    // }
    if ($blog == null || $blog == 'blog' ) {
        $data          = $stsiteModel->index($filter);
        $data['status'] = 'success';
    }else {
        $data['status'] = 'failed';
    }
    $data['admin'] = $_SESSION['admin'];
    echo json_encode($data);exit;
}


/**
 * [checkoutParam 检查信息是否完整]
 * @param  [type] $request [description]
 * @return [type]          [description]
 */
function checkoutParam($stsiteModel, $request, $type='update', $except = [])
{
    // 指示器
    $needle = 0;

    $keyArray = [
        "id",
        "site_name",
        "domain",
        "identify_tag",
        "language",
        "site_title",
        "site_subtitle",
        "site_author",
        "author_thumb",
        "product_name",
        "site_master_graph",
        "product_img",
        "site_bottom_img",
        "site_bottom_txt",
        "site_content",
        "comment_count",
        "theme",
        "is_buy_show",
        "fb_status",
        'comment_key_word',
        'comment_link'
    ];

    $info = ''; // 调试使用
    foreach ($keyArray as $k => $v) {
        if ($v == 'id' && $type == "insert") {
            continue;
        }
        if ($v == "fb_status" || $v == 'is_buy_show') {
            if (isset($request[$v])) {
                continue;
            } else {
                $needle +=1;
            }
        }
        
        if (!isset($request[$v])) {
            $needle +=1;
            $info .= 'request中没有' . $v . '\r\n';
        }
        if (isset($request[$v]) && empty($request[$v]) && !in_array($v, $except)) {
            $needle +=1;
            $info .= 'request中' . $v . '是空的\r\n';
        } 
        if ($v == "identify_tag") {
            if (preg_match('/^[0-9a-z]*$/i',  $request[$v])){
                // 域名+二级目录 是否有重复
                $site = $stsiteModel->getStsiteByDomainAndTag($request['domain'], $request['identify_tag']);
                if (count($site) && $type == 'update') {
                    if (count($site) >= 2) {
                        $needle +=1;
                        $info .= '更新域名+二级目录有重复'; 
                    }
                    if (count($site) == 1 &&  $request['id'] != $site[0]['id']) {
                        $needle +=1;
                        $info .= '更新域名+二级目录有重复'; 
                    }
                }
                
                if ($type == 'insert' && count($site) != 0) {
                    $needle +=1;
                    $info .= 'request中域名+二级目录有重复';
                }else{
                    continue; 
                }
            }else {
                $needle +=1;
                $info .= 'request中' . $v . '只能是字母数字';
            }
        }
    }
    // var_dump($info);exit;
    // unset($info);

    return empty($needle) ? ['status' => true, 'info' => '参数检查正常']: ['status' => false,'info'=>$info] ;
}


/**
 * [insertStsiteBasic 保存软文站基本表信息]
 * @param  [type] $request [description]
 * @return [type]          [description]
 */
function insertStsiteBasic($stsiteModel, $request)
{
    $admin = $_SESSION['admin'];
    // 获取参数
    $tmp = [
        "site_name" => $request['site_name'],
        "domain" => $request['domain'], 
        "identify_tag" => $request['identify_tag'], 
        "language" => $request['language'],
        "site_title" => $request['site_title'], 
        "site_subtitle" => $request['site_subtitle'], 
        "site_author" => $request['site_author'],
        "author_thumb" => \admin\helper\qiniu::changImgDomain($request['author_thumb']),
        "product_name" => $request['product_name'], 
        "site_master_graph" => \admin\helper\qiniu::changImgDomain($request['site_master_graph']),
        "product_img" => \admin\helper\qiniu::changImgDomain($request['product_img']),
        "site_bottom_img" => \admin\helper\qiniu::changImgDomain($request['site_bottom_img']),
        "site_bottom_txt" => $request['site_bottom_txt'], 
        "site_content" => \admin\helper\qiniu::ContentDomain($request['site_content']),
        "theme" => $request['theme'], 
        "is_buy_show" => $request['is_buy_show'],
        "comment_count" => $request['comment_count'],
        "fb_status" => $request['fb_status'],
        "comment_key_word" => $request['comment_key_word'],
        "comment_link" => $request['comment_link'],
        "uid" => $admin['uid'],
        "oa_id_department" => $admin['id_department'],
        "company_id" => $admin['company_id'],
        "add_time" => date('Y-m-d H:i:s')
    ];

    return $stsiteModel->insertStsiteBasic($tmp);
    
}


/**
 * [updateStsiteBasic description]
 * @param  [type] $request [description]
 * @return [type]          [description]
 */
function updateStsiteBasic($stsiteModel, $request )
{
    $res = false; // 指示是否成功

    if (isset($request['id']) && !empty($request['id'])) {
        $tmpData = [];
        $keyArray = [
            "site_name" ,
            "domain" ,
            "identify_tag" ,
            "language" ,
            "site_title" ,
            "site_subtitle" ,
            "site_author",
            "author_thumb" ,
            "product_name",
            "site_master_graph",
            "product_img" ,
            "site_bottom_img" ,
            "site_bottom_txt" ,
            "site_content" ,
            "theme",
            "is_buy_show",
            "comment_count",
            "fb_status",
            "comment_key_word",
            "comment_link"
        ];

        foreach ($keyArray as $k => $v) {
            if ($v == 'fb_status' && isset($request[$v])) {
                $tmpData[$v] = $request[$v];
                continue;
            }
            if ($v == 'is_buy_show' && isset($request[$v])) {
                $tmpData[$v] = $request[$v];
                continue;
            }
            if (isset($request[$v]) && !empty($request[$v]) ) {
                $tmpData[$v] = $request[$v];
            } 
        }
        $tmpData['author_thumb'] = \admin\helper\qiniu::changImgDomain($tmpData['author_thumb']);
        $tmpData['site_master_graph'] = \admin\helper\qiniu::changImgDomain($tmpData['site_master_graph']);
        $tmpData['product_img'] = \admin\helper\qiniu::changImgDomain($tmpData['product_img']);
        $tmpData['site_bottom_img'] = \admin\helper\qiniu::changImgDomain($tmpData['site_bottom_img']);
        $tmpData['site_content'] = \admin\helper\qiniu::ContentDomain($tmpData['site_content']);

        // 更新数据库
        $where = ['id' => $request['id']];
        $res = $stsiteModel->updateStsiteBasic($tmpData,$where); 
    } 

    return $res;
   

   
}


/**
 * [insertStsiteComment 保存软文站评论]
 * @param  [type]  $request  [description]
 * @param  integer $stsiteID [description]
 * @return [type]            [description]
 */
function insertStsiteComment($stsiteModel, $request, $stsiteID=0)
{
    $tmpRequest = $request['_FILES'];
    $tmpRequest['language'] = $request['language'];

    $res = false;
    $comments = readAndCombineComments($tmpRequest, $stsiteID);
    if (count($comments)) {
        // 多条评论插入到数据库中
        $res = $stsiteModel->insertComment($comments);
    } else {
        $res = true;
    }
    return $res;
}

/**
 * [readAndCombineComments 读取和获取excel评论并组装]
 * @param  [type] $request  [description]
 * @param  [type] $stsiteID [description]
 * @return [type]           [description]
 */
function readAndCombineComments($request, $stsiteID)
{
    // 获取excel文件
    $excelFile = $request['file']['tmp_name'];

    if (empty($excelFile)) {
        return [];
    }

    // 读取excel文件
    $reader = new PHPExcel_Reader_Excel2007();
    if(!$reader->canRead($excelFile)){
        $reader = new PHPExcel_Reader_Excel5();
        if(!$reader->canRead($excelFile)){
            // echo json_encode(['code'=>0, 'data' => [], 'info'=>'no Excel'], JSON_UNESCAPED_UNICODE);
            return false;
        }
    }
    $PHPExcel = $reader->load($excelFile); // 载入excel文件
    $sheet = $PHPExcel->getSheet(0); // 读取第一個工作表
    $highestRow = $sheet->getHighestRow(); // 取得总行数
    $highestColumm = $sheet->getHighestColumn(); // 取得总列数

    /** 循环读取每个单元格的数据 */
    $commentArray = [];
    $language = $request['language'];
    $tmpNameKeys = [];
        
    for ($row = 1; $row <= $highestRow; $row++){//行数是以第1行开始
        if (in_array($row, [1, 2])) {
            continue;
        }

        // 读取excel表中的数据 
        $commentContent = $sheet->getCell('A'.$row)->getValue();
        $commentSex = trim($sheet->getCell('B'.$row)->getValue());
        $commentPraise = $sheet->getCell('C'.$row)->getValue();

        // 只要有一个是空的， 直接放弃该行
        if (empty($commentContent) || empty($commentSex) || empty($commentPraise) || !is_numeric($commentPraise) || $commentPraise < 0) {
            continue;
        }

        // 随机用户名
        if (in_array($commentSex, ['男','女'])) {
            
            if ($commentSex == '男') {
                $comSex = 1;
            }
            if ($commentSex == '女') {
                $comSex = 0;
            }
        } else {
            continue;
        }
        $nameArray = getRandomUserNameByLanguage($tmpNameKeys,$language, $comSex);
        array_push($tmpNameKeys, $nameArray['key']);

        // 组装一条评论
        $tmpComment = [];
        $tmpComment['st_site_id'] = $stsiteID;
        $tmpComment['comment_name'] = $nameArray['name'];
        $tmpComment['comment_content'] = $commentContent;
        $tmpComment['comment_sex'] = (string)$comSex;
        $tmpComment['comment_praise'] = empty($commentPraise)?0:$commentPraise;
        $tmpComment['comment_time'] = date('Y-m-d H:i:s');
      
        unset($commentContent);
        unset($commentSex);
        unset($commentPraise);
        unset($nameArray);
        // 推送到多条评论的数组中
        array_push($commentArray, $tmpComment);
    }
    return $commentArray;
}

/**
 * [updateStsiteComment description]
 * @param  [type] $stsiteModel [description]
 * @param  [type] $_REQUEST    [description]
 * @param  [type] $stsiteID    [description]
 * @return [type]              [description]
 */
function updateStsiteComment($stsiteModel, $request, $stsiteID=0)
{
    $tmpRequest = $request['_FILES'];
    $tmpRequest['language'] = $request['language'];

    $res = false;
    $comments = readAndCombineComments($tmpRequest, $stsiteID);
    if (count($comments)) {
        // 多条评论插入到数据库中
        return $stsiteModel->updateComment($comments, $stsiteID);
    } else {
        $comments = $stsiteModel->getAllCommentsBySiteID($stsiteID);
        if (!empty($comments)) {
            $nameUsed = [];
            foreach ($comments as $key => $value) {
                $randName = getRandomUserNameByLanguage($nameUsed,$tmpRequest['language'], $value['comment_sex']);
                array_push($nameUsed, $randName['key']);
                $stsiteModel->updateSingleComment($randName['name'], $value['id']);
            }
        }
        $res = true;
    }
    return $res;
}

/**
 * [getRandomUserNameBySexAndLanguage 根据语言和性别随机用户名]
 * @param  string  $language [语言]
 * @param  integer $sex      [性别]
 * @return [type]            [description]
 */
function getRandomUserNameByLanguage($nameUsed=[], $language='cn', $sex=0)
{
    if (trim($language) == 'ch') {
        $language = 'cn';
    }
    $userNameSet = [];

    // 简体中文
    $userNameSet['cn']['miss'] = [
            '-我爱崇光。',
            '刘丹娟',
            '伴我多久',
            '刘仡冉',
            '天黑路滑人心杂。',
            '李雨秋',
            '单身姑娘最坚强@',
            '李丝敏',
            '將心比心',
            '欧巴桑',
            '王长洁',
            'Growl',
            '王顺悦',
            '小时代',
            '王婷沅',
            '好难瘦@',
            '韩霜玥',
            '兰陵王',
            '韩婧珩',
            '我迷了鹿',
            '邓重蓉',
            '女汉纸',
            '邓怡壬',
            '伴我',
            '邓默冉',
            '、陪我从学堂到殿堂',
            '何魏琳',
            '萌神@',
            '何媛葸',
            '开着空调盖被子',
            '史莉霄',
            '史韫琼',
            '讨厌验证码的同学',
            '史婵菲',
            '柠檬只是好闻而已 *',
            '常茹渲',
            '苹果',
            '常莹葶',
            '我在等你',
            '常军莹',
            '陪我从校服到婚纱',
            '常贺莉',
            '敢爱敢恨的女汉子',
            '常雪茹',
            '常另婧',
            '喜欢跷二郎腿的女汉子@',
            '孔销艳',
            '爱玩的菇凉很霸气°',
            '孔娅彤',
            '兔儿神',
            '孔薪羽',
            '郑儿 郑儿',
            '孔社霞',
            '低头哭过别忘了抬头',
            '孔加茹',
            '我要瘦！',
            '杨钊文',
            '软肋@',
            '杨妤燕',
            '知返',
            '杨蓉欣',
            '短发的女孩',
            '杨盛妍',
            '假闺蜜',
            '杨钶娜',
            '呆萌小泰迪ミ',
            '宋遵红',
            'ぐ朴灿烈づ',
            '宋妍帆',
            '我是青柠',
            '单身女万岁',
            '宋蓉畅',
            '如初@',
            '宋颦艳',
            '一瓶卸妆水',
            '杜美艳',
            '我是青柠我嗨皮',
            '杜芬茵',
            '那些年丶',
            '杜仟娥',
            '一只兔儿神',
            '杜萍薇',
            '孤独成性',
            '杜黄琳',
            '小梦-°',
            '颜筝芬',
            '小徐至少有小梦-°',
            '颜翎婵',
            '溜溜球',
            'Baby Don\'t cry *',
            '颜茹芥',
            '如初。',
            '颜琳娅',
            '软妹子',
            '颜镰萍',
            '女生最心软',
            'we are one',
            '凉巷',
            '[归人雨巷 @]',
            '孙花玲'

        ];
    $userNameSet['cn']['boy'] = [
            '絵飛ヾ的魚',
            '孙允儿',
            'Οo汧心宀赑оΟ',
            '吃的姑娘出来',
            '孙霏昊',
            '伴我多久@',
            '李雨擎',
            '想减肥又想狂',
            '原来齐远是好人、',
            '李帼峻',
            '周俜夏',
            '我来自1999@',
            '周栢业',
            '我爱你若能倒着说@',
            '_______荒城旧梦',
            '吴展颐',
            '吴佩帆',
            '乖王萌姐',
            '吴茵浩',
            '怪萌熟叔',
            '郑瑁',
            '人长得漂亮',
            '郑颗',
            '活的漂亮',
            '郑秦',
            '看见爱',
            '王哕',
            '百合',
            '王滉',
            'xxxibgdrgn',
            '冯小潮',
            '冯伟姣',
            '㎜° 稀饭金钟仁ゞ',
            '冯苓霄',
            'Emotion',
            '陈富心',
            '雄起',
            '陈广护',
            '鱼爱海的蓝',
            '陈铭嵘',
            '柠檬只是好闻而已 *',
            '卫酝',
            '自己好美',
            '卫伙',
            '萌汉子@',
            '蒋家大',
            'xoxo',
            '蒋希紫',
            '病态美@',
            '款爷@',
            '沈嘎',
            '于朦胧 @',
            '沈烬',
            '沈咙',
            '原谅我优柔寡断心软成患@',
            '谢宗蕾',
            '-＊ 朝思暮想的少年叫EXO',
            '谢峰硕',
            '谢舂利',
            '单身姑娘在哪儿。',
            '雷宏琦',
            '爱我你会火@',
            '雷岱炜',
            '雷自敏',
            '我在楼下喊加油@',
            '宋京典',
            '火星族',
            '宋景立',
            '剪了头发、',
            '宋宛芙',
            '[ 我对自己开了一枪 ]',
            '余筝',
            'minoz',
            '余向',
            '大爱于朦胧 °',
            '屈导',
            '屈枣',
            '谁暖我春秋梦',
            '屈八',
            '屈殷',
            'We Are One°',
            '江甘',
            '睡懒觉的孩子',
            '江倪',
            '江区',
            '蔡鸸',
            '我爱你若能倒着说',
            '蔡侗',
            '蔡礁',
            '请对我好点',
            '邓通',
            '苦瓜不苦、',
            '邓鲢',
            'Heart Attack',
            '邓僮',
            '凉了海静了天',
            '陆迎旭',
            '[海港] [深巷]',
            '陆之唐',
            'Lucky'

        ];

    // 英文
    $userNameSet['en']['miss'] = [
            'Abigail', 'Ada', 'Alexis', 'Angela', 'Annie', 'Ava', 'Avis', 'Babs', 'Bertha', 'Beryl', 'Betty', 'Bid', 'Bonny', 'Brenda',
            'Bridget', 'Brittany', 'Camilla', 'Candice', 'Candida', 'Carla', 'Carmen', 'Celia', 'Cindy', 'Clare', 'Corinna', 'Crystal', 
            'Cynthia', 'Daisy', 'Daphne', 'Dawn', 'Deb', 'Dot', 'Dottie', 'Dulcie', 'Edith', 'Edna', 'Eileen', 'Elaine', 'Eleanor', 
            'Eunice', 'Eva', 'Evadne', 'Evangeline', 'Eve', 'Evelyn', 'Faustina', 'Fay', 'Felicia', 'Felicity', 'Fidelia', 'Fiona',
            'Flavia', 'Frances', 'Frankie', 'Freda', 'Frieda', 'Gene', 'Georgia', 'Georgina', 'Geraldine', 'Hannah', 'Harriet', 'Hazel',
            'Heather', 'Hedda', 'Hedwig', 'Helen', 'Helga', 'Henrietta', 'Hilary', 'Hilda', 'Hortensia', 'Ida', 'Ingrid', 'Irene', 'Iris',
            'Isabel', 'Isabella', 'Ivy', 'Jackie', 'Jacqueline', 'Jessica', 'Joyce', 'Karen', 'Kelly', 'Kim', 'Kimberly', 'Kirsten', 'Kit',
            'Kitty', 'Laura', 'Lauretta', 'Leila', 'Lynn', 'Mabel', 'Madeleine', 'Madge', 'Maggie', 'Maisie', 'Mandy'
        ];
    $userNameSet['en']['boy'] = [
            'Abe', 'Abel', 'Adrian', 'Al', 'Alfred', 'Allen', 'Auberon', 'Augustus', 'Baldwin', 'Barnaby', 'Barry', 'Bernard', 'Brian',
            'Bruce', 'Burt', 'Caesar', 'Carl', 'Carlton', 'Cary', 'Cecil', 'Charlie', 'Chris', 'Clyde', 'Constant', 'Craig', 'Curt',
            'Cuthbert', 'Dale', 'Daniel', 'Danny', 'Dominic', 'Douglas', 'Duane', 'Dudley', 'Duke', 'Duncan', 'Dustin', 'Eamonn', 'Earl',
            'Ed', 'Edmund', 'Edward', 'Eliot', 'Elmer', 'Emlyn', 'Enoch', 'Enos', 'Ernest', 'Errol', 'Ferdinand', 'Floyd', 'Francis',
            'Frankie', 'Frederick', 'Gabriel', 'Gary', 'Gaston', 'Gavin', 'Geoff', 'Graeme', 'Guy', 'Hamilton', 'Hank', 'Harold', 'Henry',
            'Herbert', 'Herman', 'Hiram', 'Homer', 'Howard', 'Howell', 'Hugh', 'Hugo', 'Humphry', 'Ira', 'Irving', 'Isaac', 'Ivan',
            'Jack', 'Jacob', 'James', 'Jamie', 'Jimmy', 'John', 'Jon', 'Jonathan', 'Joshua', 'Julian', 'Julius', 'Karl', 'Keith', 'Ken',
            'Kenny', 'Kent', 'Kev', 'Kit', 'Laban', 'Larry', 'Laurence', 'Lee'
    ];

    // 繁体中文 
    $userNameSet['tw']['miss'] = [
            'kkA',
            '夏夏',
            '盧小小',
            'miss田',
            '小妹愛口紅',
            '童童',
            '小紅薯',
            '長歡',
            '燕燕',
            '檸檬不太酸',
            '金小禾',
            '幸福的豬',
            '哆啦的麻麻',
            '柚子',
            '泡沫',
            '紅玫瑰',
            '吳菲兒',
            '餃子',
            '天寶麻麻',
            '小丸子',
            '林萌',
            '一米陽光',
            '月半',
            '妍妍',
            '小花貓',
            'Simple',
            'Hope',
            'Amber',
            '夢夢',
            '九色鹿',
            '艾米',
            'MSG',
            'Elsawong',
            'vieway',
            '美嘉',
            'Kuma喵',
            'Andy',
            'kison',
            '穎兒',
            '小麵包',
            '小懶蟲',
            '果凍媽',
            '米麗',
            'cherry',
            'Diana',
            '蘋果派',
            'Pace',
            '林小妹',
            '小可愛',
            'joy',
            '一如既往',
            '英子',
            '若曦',
            '娜娜',
            '林婉茹',
            '陳文麗',
            '張小逗',
            '鄧小瑩',
            '旭寶寶LD',
            '圓眼鏡少女的LAnDEn',
            '小紅薯_3309',
            '嘉雯Karmen',
            '黃花花',
            '沒有故事的女同學',
            'Jolie小姐姐',
            '啊哦巴達拉',
            '胡智美8866',
            '我是依依呀',
            '睿智熊的麻麻',
            '鹿綠AOliao',
            '眼睛含著陽光',
            '腕上風情',
            'SHERO',
            '小迷糊',
            '辣兒姐',
            '蘋Princess',
            '清青er',
            '小藍寶兒_',
            '能不取就不取吧',
            '愛吃榴蓮的凍雞翅',
            'hellomolly',
            '花花鹿小公子',
            '你和七里香',
            'Wendy',
            '無問西東',
            '巴黎金字塔',
            '水冰月',
            '王先生的小仙女',
            '小怪獸_58C79555',
            '梧桐阿麥',
            'R18又見小肚兜',
            '小咸菜姐姐',
            '糠糠',
            '大欣欣在這兒呢',
            '柒月的夏目',
            '八個比娃娃',
            '愛睡覺的宅貓',
            '隔壁家的小可愛',
            '歡喜糰子',
            '超人叫寶貝'

        ];
    $userNameSet['tw']['boy'] = [
            '不再回憶不在傷心',
            '孫允兒',
            '心爲你而跳動',
            '孫虛任',
            '坐懷不亂',
            '李健強',
            '優柔寡斷',
            '李梓勤',
            '浅訴肆唸',
            '李幗峻',
            '︿轉身淚成',
            '周陳暄',
            '雲中的國度',
            '周俊淳',
            '齊名╦好難',
            '吳展頤',
            '吳佩帆',
            '吳弈心',
            '转身痐頭沒簬',
            '吳長順',
            '心門已閉',
            '鄭懿',
            '試著忘記壹切',
            '鄭緦',
            '沈淪你的愛',
            '醉後抉定愛上你',
            '王噦',
            '舊情绵綿',
            '王滉',
            '永不永不說再見',
            '馮小潮',
            '擱淺的彩渱',
            '你跟緊點',
            '馮苓霄',
            '心灵擱淺',
            'ω飛天豬',
            '陳仲挺',
            '花敗ツ無奈',
            '疲憊的身軀',
            '疲憊的身軀',
            '陳銘嶸',
            '汐顔如夢',
            '衛醞',
            '請用心去對待',
            '衛夥',
            '輕風中微笑',
            '蔣家大',
            '傷了誰的心',
            '蔣希紫',
            '依舊愛你∈',
            '蔣鈺眚',
            '沈嘎',
            '總是太依賴',
            '沈燼',
            '滲透靈魂的疼ぐ',
            '沈彩',
            '用微笑演繹悲傷',
            '謝亞達',
            '網不了你de咿傾红',
            '謝春利',
            '沒腳本的演出',
            '雷宏琦',
            '一抹紅顔笑',
            '雷岱煒',
            '因爲年少所以輕狂',
            '雷金函',
            '諾言|誰伤了',
            '譕淚づ宝唄',
            '不太乖喜歡搗蛋',
            '宋亞霆',
            '宋宛芙',
            '沉溺萬紫千紅リ',
            '余箏',
            '余噌',
            'yi個人失藝',
            '余位',
            '熏染的堅強',
            '屈棗',
            '微笑説侢見ν',
            '屈八',
            'ε給不起你要的以後',
            '江椒',
            '孤獨陪伴年華',
            '江霾',
            '詠恆de爱',
            '江區',
            '整理零亂的思緒',
            '蔡鹿',
            '空葥絕後',
            '蔡礁',
            '誰有誰更倔强',
            '鄧通',
            '為你傾心西',
            '鄧鰱',
            '少一點絗憶',
            '鄧童',
            '過眼雲烟',
            '陸迎旭',
            '摯愛咱想你',
            '陸之唐',
            '當愛失去了默契'

        ];

    // 泰文 
    $userNameSet['tha']['miss'] = [
            'เฟื่อง', 'มะปราง', 'ราเชล', 'โม', 'ประภาภรณ์', 'บีม', 'ชนนิกานต์', 'ณิชา', 'อรวรรณ', 'กุ๊กกิ๊ก', 'มินตรา', 'ส้ม', 'พิมพ์', 'แพท',
            'นิสรีน', 'ดลญา', 'เบลล์', 'ฝ้าย', 'ธนัสถา', 'นริศรา', 'ฝน', 'พลอยพรรณ', 'ชลธิชา', 'สุพัตรา', 'ฟ้า', 'แบงค์', 'ดลยา', 'ฐิมาพร', 'ผกามาศ',
            'ศิรินันท์', 'ใบตอง', 'บุลภรณ์', 'มิกิ', 'เมย์', 'กัลยรัตน์', 'เอิร์น', 'อินธุอร', 'เบนซ์', 'วิรดา', 'กวาง', 'สุพรรษา ศรีคีรี', 'ณัฐชา', 
            'นัสรีน', 'นุ๊ก', 'คอดีเยาะห์', 'ณัฐธิดา', 'ทราย', 'ธารารัตน์', 'ภัทราวดี', 'ลูกเต๋า เกศินี', 'มิ้น', 'ปาณิศา', 'เฟาซียะ', 'ส้มโอ', 'นาเดียร์',
            'เสกสัน', 'กนกวรรณ', 'กรรณิการ์', 'แก้ว', 'จตุพร', 'ศรัญย์รัชต์', 'พรพิมล', 'หนึ่งฤทัย', 'แทน', 'หมวย', 'สุธาสินี', 'ปาริสา', 'หญิง', 
            'ไอรดา หมายถึง', 'รัตนา', 'จุนิตา', 'พรีม', 'ภาริณี', 'บัว', 'รุ่งทิวา', 'ศรัญญา', 'มะเหมี่ยว', 'จันทร์จุฬา', 'อนิตยา', 'ปนิดา', 'ฟาร่า',
            'สิริพร', 'หมิว', 'สุภาวดี', 'ออม', 'แอน', 'ไอซ์', 'ไข่มุก', 'ฟาเดียนี', 'อาทิตติยา', 'จิราพร', 'เนตรชนก', 'ดาว', 'ทิพย์', 'สุภาพร',
            'อีฟ', 'กัญญาลักษณ์', 'ญาณิศา', 'ฟิล์ม', 'ศศิธร'
        ];
    $userNameSet['tha']['boy'] = [
            'สุทธิศักดิ์', 'กาฬไชยสิทธิ์', 'بنيامين', 'ศุภสิทธิ์', 'สันติ', 'ชานนท์', 'ราเชนทร์', 'ภูวิศ', 'ชญาน์วัต', 'วีรภัทร', 'สุธิพงษ์', 'ฟาอิซ',
            'แข็งแรง', 'เจมส์', 'จิรายุ', 'ฟลุ๊ค', 'ธีรวัฒน์', 'ธีรวัจน์', 'ภราดร', 'อชิตะ', 'บัสรี่', 'โพเช่', 'เเซ๊ก', 'กฤษณะ', 'จิรภัทร', 'อดิศักดิ์',
            'หนึ่ง', 'มาร์ค', 'ภูดิศ', 'อับดุลรอซีด', 'อับบาส', 'อัซมาวีย์', 'นิว', 'เฟรม', 'อาร์ม', 'สุชาติ เทียนเงิน', 'จีราวัฒน์', 'เจ้าคุณ', 'อนุชา',
            'กมลภู', 'ภูมิ', 'ปลื้ม', 'ศราวุฒิ', 'ศุภโชค', 'หยก', 'ณัฐชนน', 'อาม', 'ณัฐปริญญ์', 'อานัส', 'ภูวินท์', 'อิชินะ', 'ปรีชา', 'พุทธ',
            'ปัณฐ์ณภัทร', 'ก้อง', 'กิติศักดิ์', 'กิตติภาส', 'ศราวุธ', 'ไชย์วัฒน์', 'ปราณธนา', 'นัด', 'ภาณุวัฒน์ คำนิกรณ์', 'บอส', 'ศุภเดช',
            'ไททัน', 'ธัชกร', 'นครินทร์', 'สมชาย', 'พันธกานต์', 'อนุพงษ์', 'อนุวัฒน์', 'สิทธิพงศ์', 'วุฒินันท์', 'คมกฤษณ์', 'กฤติกานต์',
            'อาลีฟ', 'พชรพล', 'ทัศนัย', 'สุทธิพงศ์', 'ศิริชัย', 'ณภัทร', 'ตฤณชาติ', 'อาฟันดี', 'พัชราวุธ', 'ธีรพงษ์', 'ประสิทธิชัย', 'กันต์',
            'บอย', 'กฤษณพงศ์', 'สุนิติ', 'บอล', 'เบส', 'พงศกร', 'ธีระศักดิ์', 'ซีโร่', 'ณัฐพล', 'วิทยา', 'คฑาวุฒิ', 'ต้นกล้า', 'เติ้ล'
        ];

    // 主逻辑
    $tmpUserNameSet = $userNameSet[$language];
    unset($userNameSet);
    if ($sex == 2) { 
        $sexArray = ['miss', 'boy'];
        $randKey = mt_rand(0,1);
        $key = $sexArray[$randKey];
        $targetUserNameSet = $tmpUserNameSet[$key];
        
    } else { 
        $key = $sex ? 'boy' : 'miss';
        $targetUserNameSet = $tmpUserNameSet[$key];
    }

    $range = range(0, 99);
    $diff = array_diff($range, $nameUsed);
    $time = date("Y-m-d H:i:s");

    $diff = array_values($diff);

    $rand = mt_rand(0, count($diff)-1);
    $targetKey = $diff[$rand];

    return ['key'=> $targetKey, 'name' => $targetUserNameSet[$targetKey]];


}
