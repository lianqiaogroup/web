<?php
namespace lib;
/**
 * 通用的树型类，可以生成任何树型结构
$tree=new Tree();
$tree->icon = ['&nbsp;&nbsp;&nbsp;│ ','&nbsp;&nbsp;&nbsp;├─ ','&nbsp;&nbsp;&nbsp;└─ '];
$tree->nbsp = '&nbsp;&nbsp;&nbsp;';
//$result是数据库或缓存数据
foreach ($result as $r) {
$r['str_manage'] = '';
if ($r['parent_id'] != $parent_id) continue;
$r['parent_id'] = 0;
$r['str_manage'] .= '<a href="?m=admin&c=category&a=init&parent_id=' . $r['cat_id'].'">' . L('manage_sub_category') . '</a> | ';
$r['str_manage'] .= '<a href="?m=admin&c=category&a=add&parent_id=' . $r['cat_id'] . '">' . L('add_sub_category') . '</a> | ';

$r['str_manage'] .= '<a href="?m=admin&c=category&a=edit&catid=' . $r['cat_id'] . '">' . L('edit') . '</a>';
$r['type_name'] = $types[$r['type']];
$categorys[$r['cat_id']] = $r;
}
$str = "<tr>
<td align='center'><input name='list_orders[\$id]' type='text' size='3' value='\$list_order' class='input-text-c'></td>
<td align='center'>\$id</td>
<td >\$spacer\$cat_name</td>
<td>\$type_name</td>
<td>\$modelname</td>
<td align='center' >\$str_manage</td>
</tr>";
$tree->init($categorys);
$categorys = $tree->getTree(0, $str);
 *
 */
class tree {
    /**
     * 生成树型结构所需要的2维数组
     * @var array
     */
    public $arr =[];

    /**
     * 生成树型结构所需修饰符号，可以换成图片
     * @var array
     */
    public $icon = ['│','├','└'];
    /**空格
     * @var string
     */
    public $nbsp = "&nbsp;";

    /**
     * @access private
     */
    public $ret = '';
    /**主键字段ID名称
     * @var string
     */
    public $idName='id';
    /**父栏目字段名称
     * @var string
     */
    public $parentIDName='parent_id';

    /**初始化字段
     * @param string $idName 字段ID名称
     * @param string $parentIDName 父栏目ID字段名称
     */
    public function __construct($idName='id',$parentIDName='parent_id'){
        $this->parentIDName=$parentIDName;
        $this->idName=$idName;
    }
    /**
     * 构造函数，初始化类
     * @param array $arr 2维数组，例如：
     * array(
     *      1 => array('id'=>'1','parent_id'=>0,'name'=>'一级栏目一'),
     *      2 => array('id'=>'2','parent_id'=>0,'name'=>'一级栏目二'),
     *      3 => array('id'=>'3','parent_id'=>1,'name'=>'二级栏目一'),
     *      4 => array('id'=>'4','parent_id'=>1,'name'=>'二级栏目二'),
     *      5 => array('id'=>'5','parent_id'=>2,'name'=>'二级栏目三'),
     *      6 => array('id'=>'6','parent_id'=>3,'name'=>'三级栏目一'),
     *      7 => array('id'=>'7','parent_id'=>3,'name'=>'三级栏目二')
     *      )
     * @return array
     */
    public function init(array $arr=[]){
        $this->arr = $arr;
        $this->ret = '';
        return is_array($arr);
    }

    /**
     * 得到父级数组
     * @param int $id
     * @return array
     */
    public function getParent($id){
        if(!isset($this->arr[$id])) return false;
        $pid = $this->arr[$id][$this->parentIDName];
        $pid = $this->arr[$pid][$this->parentIDName];
        $newArr = [];
        if(is_array($this->arr)){
            foreach($this->arr as $k => $a){
                if($a[$this->parentIDName] == $pid) $newArr[$k] = $a;
            }
        }
        return $newArr;
    }

    /**
     * 得到子级数组
     * @param int
     * @return array
     */
    public function getChild($id){
        $newArr = [];
        if(is_array($this->arr)){
            foreach($this->arr as $key => $a){
                if($a[$this->parentIDName] == $id) $newArr[$key] = $a;
            }
        }
        return $newArr ?:false;
    }

    /**得到当前位置数组
     * @param $id
     * @param $newArr
     *
     * @return array|bool
     * author Fox
     */
    public function getPos($id,&$newArr){
        $a = [];
        if(!isset($this->arr[$id])) return false;
        $newArr[] = $this->arr[$id];
        $pid = $this->arr[$id][$this->parentIDName];
        if(isset($this->arr[$pid])){
            $this->getPos($pid,$newArr);
        }
        if(is_array($newArr)){
            krsort($newArr);
            foreach($newArr as $v){
                $a[$v[$this->idName]] = $v;
            }
        }
        return $a;
    }

    /**得到树型结构
     * @param int $id 表示获得这个ID下的所有子级
     * @param string $_str 生成树型结构的基本代码，例如："<option value=\$id \$selected>\$spacer\$name</option>"
     * @param int $sid 被选中的ID，比如在做树型下拉框的时候需要用到
     * @param string $adds
     * @param string $str_group
     * @return string
     * author Fox
     */
    public function getTree($id, $_str, $sid = 0, $adds = '', $str_group = ''){
        $number=1;
        $child = $this->getChild($id);
        if(is_array($child)){
            $parent_id=0;
            $_nstr_tmp_123d3='';
            $total = count($child);
            foreach($child as $id=>$value){
                $j=$k='';
                if($number==$total){
                    $j .= $this->icon[2];
                }else{
                    $j .= $this->icon[1];
                    $k = $adds ? $this->icon[0] : '';
                }
                $spacer = $adds ? $adds.$j : '';
                $selected = $id==$sid ? 'selected' : '';
                @extract($value);

                $parent_id == 0 && $str_group ? eval("\$_nstr_tmp_123d3 = \"$str_group\";") : eval("\$_nstr_tmp_123d3 = \"$_str\";");
                /*if($parent_id == 0 && $str_group) {
                    $_nstr_tmp_123d3 = eval_format($str_group,get_defined_vars());
                }else {
                    $_nstr_tmp_123d3 = eval_format($_str,get_defined_vars());
                }*/
                $this->ret .= $_nstr_tmp_123d3;
                $nbsp = $this->nbsp;
                $this->getTree($id, $_str, $sid, $adds.$k.$nbsp,$str_group);
                $number++;
            }
        }
        return $this->ret;
    }
    /**得到树型结构
     * @param int $id 表示获得这个ID下的所有子级
     * @param string $_str 生成树型结构的基本代码，例如："<option value=\$id \$selected>\$spacer\$name</option>"
     * @param int $sid 被选中的ID，比如在做树型下拉框的时候需要用到
     * @param string $adds
     * @return string
     * author Fox
     */
    public function getTreeMulti($id, $_str, $sid = 0, $adds = ''){
        $number=1;
        $child = $this->getChild($id);
        if(is_array($child)){
            $total = count($child);
            foreach($child as $id=>$a){
                $j=$k='';
                if($number==$total){
                    $j .= $this->icon[2];
                }else{
                    $j .= $this->icon[1];
                    $k = $adds ? $this->icon[0] : '';
                }
                $spacer = $adds ? $adds.$j : '';
                $selected = $this->have($sid,$id) ? 'selected' : '';
                @extract($a);
                eval("\$_nstr_tmp_123d3 = \"$_str\";");
                $this->ret .= $_nstr_tmp_123d3;
                //$this->ret .= eval_format($_str,get_defined_vars());
                $this->getTreeMulti($id, $_str, $sid, $adds.$k.'&nbsp;');
                $number++;
            }
        }
        return $this->ret;
    }
    /**
     * @param int $id 要查询的ID
     * @param string $_str   第一种HTML代码方式
     * @param string $str2  第二种HTML代码方式
     * @param int $sid  默认选中
     * @param string $adds 前缀
     * @return string
     */
    public function getTreeCategory($id, $_str, $str2, $sid = 0, $adds = ''){
        $number=1;
        $child = $this->getChild($id);
        if(is_array($child)){
            $total = count($child);
            foreach($child as $id=>$a){
                $j=$k='';
                if($number==$total){
                    $j .= $this->icon[2];
                }else{
                    $j .= $this->icon[1];
                    $k = $adds ? $this->icon[0] : '';
                }
                $spacer = $adds ? $adds.$j : '';

                $selected = $this->have($sid,$id) ? 'selected' : '';
                @extract($a);
                if (empty($html_disabled)) {
                    eval("\$_nstr_tmp_123d3 = \"$_str\";");
                    //$this->ret .= eval_format($_str,get_defined_vars());
                } else {
                    eval("\$_nstr_tmp_123d3 = \"$str2\";");
                    $this->ret .= eval_format($str2,get_defined_vars());
                }
                $this->ret .= $_nstr_tmp_123d3;
                $this->getTreeCategory($id, $_str, $str2, $sid, $adds.$k.'&nbsp;');
                $number++;
            }
        }
        return $this->ret;
    }

    /**
     * 同上一类方法，jquery treeview 风格，可伸缩样式（需要treeview插件支持）
     * @param $id 表示获得这个ID下的所有子级
     * @param string $effected_id 需要生成treeview目录数的id
     * @param string $_str 末级样式
     * @param string $str2 目录级别样式
     * @param int $showLevel 直接显示层级数，其余为异步显示，0为全部限制
     * @param string $style 目录样式 默认 filetree 可增加其他样式如'filetree treeview-famfamfam'
     * @param int $currentLevel 计算当前层级，递归使用 适用改函数时不需要用该参数
     * @param bool $recursion 递归使用 外部调用时为FALSE
     * @return string
     */
    function getTreeView($id,$effected_id='example',$_str="<span class='file'>\$name</span>", $str2="<span class='folder'>\$name</span>" ,$showLevel = 0 ,$style='filetree ' , $currentLevel = 1,$recursion=FALSE) {
        $child = $this->getChild($id);
        if(!defined('EFFECTED_INIT')){
            $effected = ' id="'.$effected_id.'"';
            define('EFFECTED_INIT', 1);
        } else {
            $effected = '';
        }
        $placeholder = 	'<ul><li><span class="placeholder"></span></li></ul>';
        if(!$recursion) $this->str .='<ul'.$effected.'  class="'.$style.'">';
        foreach($child as $id=>$a) {

            @extract($a);
            if($showLevel > 0 && $showLevel == $currentLevel && $this->getChild($id)) $folder = 'hasChildren'; //如设置显示层级模式@2011.07.01
            $floder_status = isset($folder) ? ' class="'.$folder.'"' : '';
            $this->str .= $recursion ? '<ul><li'.$floder_status.' id=\''.$id.'\'>' : '<li'.$floder_status.' id=\''.$id.'\'>';
            $recursion = FALSE;
            if($this->getChild($id)){
                //eval("\$_nstr_tmp_123d3 = \"$str2\";");
                //$this->str .= $_nstr_tmp_123d3;
                $this->str .= eval_format($str2,get_defined_vars());
                if($showLevel == 0 || ($showLevel > 0 && $showLevel > $currentLevel)) {
                    $this->getTreeView($id, $effected_id, $_str, $str2, $showLevel, $style, $currentLevel+1, TRUE);
                } elseif($showLevel > 0 && $showLevel == $currentLevel) {
                    $this->str .= $placeholder;
                }
            } else {
                //eval("\$_nstr_tmp_123d3 = \"$_str\";");
                //$this->str .= $_nstr_tmp_123d3;
                $this->str .= eval_format($_str,get_defined_vars());
            }
            $this->str .=$recursion ? '</li></ul>': '</li>';
        }
        if(!$recursion)  $this->str .='</ul>';
        return $this->strstr;
    }

    /**获取子栏目json
     * @param        $id
     * @param string $_str
     *
     * @return string
     * author Fox
     */
    public function creatSubJson($id, $_str='') {
        $sub_cats = $this->getChild($id);
        $n = 0;
        $data=[];
        if(is_array($sub_cats)) foreach($sub_cats as $c) {
            $data[$n]['id'] = $c[$this->idName];
            if($this->getChild($c[$this->idName])) {
                $data[$n]['li_class'] = 'hasChildren';
                $data[$n]['children'] = array(array('text'=>'&nbsp;','classes'=>'placeholder'));
                $data[$n]['classes'] = 'folder';
                $data[$n]['text'] = CHARSET=='utf-8'?$c['cat_name']: iconv(CHARSET,'utf-8',$c['cat_name']);
            } else {
                if($_str) {
                    @extract(array_iconv($c,CHARSET,'utf-8'));
                    //eval("\$data[$n]['text'] = \"$_str\";");
                    $data[$n]['text'] = eval_format($_str,get_defined_vars());
                } else {
                    $data[$n]['text'] = CHARSET=='utf-8'?$c['cat_name']: iconv(CHARSET,'utf-8',$c['cat_name']);
                }
            }
            $n++;
        }
        return json_encode($data);
    }
    private function have($list,$item){
        return(strpos(',,'.$list.',',','.$item.','));
    }
}