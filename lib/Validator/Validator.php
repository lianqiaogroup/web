<?php

namespace lib\Validator;

/**
 * 验证数据
 * example：
 * $arr=array('a'=>'12345699','b'=>'8885558');
 * $ruleStr='a|必须是数字==|int=类型限制||length(6:6)=长度限制{and}b|标题|int=必须是数字||length(6:6)=长度6bbb';//
 * $validator = new \lib\Validator\Validator();
 * $dd=$validator->doValidate($arr,$ruleStr,false);
 * var_dump($dd);
 * var_dump($validator->getMsg());
 *
 * 目前支持以下验证类型 注意验证的提示信息中不能含有空格、换行和竖线和等号空格用{SP}代替，换行用{LF}代替，竖线用{VE}
 * string 字符,
 * time 时间日期,
 * alnum 字母加数字,
 * alpha 字母,
 * alnumu 字母数字下划线,
 * digits 数字,
 * graph 可显示的字符,
 * lower 小写,
 * print 可否打印,
 * punct 标点,
 * whitespace 空格或制表符,
 * upper 大写,
 * int 整型,
 * float 浮点型,
 * ipv4 ip地址,
 * binary 二进制数,
 * domain 域名,
 * email Email
 * 
 * 要验证的数组的key | 中文名称 | 验证类型 | 是否必须 | 其他(equal length size format)
 * title|标题|string=格式不正确|require=必须要填写|length(22:32)=长度不正确
 * age|年龄|int|require|size(1:200) equal(addtime)
 * addtime|时间|time|require|time_format(Y-m-d)
 * ipaddr|IP地址|ipv4|require|
 * email|email地址|email|require|
 * password|密码|string|require|equal(title)
 * a|测试a|float|require|length(0:199.99)
 * b|测试b|binary|require|
 * c|测试c|binary||
 *
 */
class Validator {

    public $_locale = null;
    public $break = false; //true:只要有一个验证不通过就停止验证  false:验证所有的要验证的数据，再返回 默认：false
    public $pass = true;
    public $errmsg = []; //错误提示
    public $arr = []; //要验证的数据

    public function __construct() {
        
    }

    /**
     * 检验
     * @param array $data 检验的数据
     * @param array|string $ruleData 检验规则
     * @param boolean $break true:只要有一个验证不通过就停止验证  false:验证所有的要验证的数据，再返回  默认：true
     * @return boolean
     */
    public function doValidate($data, $ruleData = [], $break = true) {
        $this->_locale = null;
        $this->pass = true;
        $this->break = $break; //true:只要有一个验证不通过就停止验证  false:验证所有的要验证的数据，再返回  默认：true
        $this->errmsg = []; //错误提示
        $this->arr = $data;

        if (empty($ruleData)) {
            return $this->pass;
        }

        $ruleData = is_array($ruleData) ? $ruleData : array($ruleData);

        foreach ($ruleData as $rule) {
            if (!$this->complierRule($rule) && $this->break == true) {
                return false;
            }
        }

        return $this->pass;
    }

    /**
     * 解析每条规则并验证
     */
    public function complierRule($rule) {

        $this->pass = true;
        $rule = trim($rule);

        if ($rule == '') {
            return true;
        }

        //规则数据
        $ruleargs = I('data.', '', 'trim', explode('|', $rule)); //$rule：title|标题|string=格式不正确|require=必须要填写|length(22:32)=长度不正确
        $args = array(
            'field' => $ruleargs[0],
            'cname' => $ruleargs[1],
            'type' => $ruleargs[2],
            'require' => $ruleargs[3],
            'expression' => $ruleargs[4]
        );
        unset($ruleargs);

        if (!$this->validateRequire($args)) { //如果 不需要 检验后续的验证条件，就直接返回检验结果
            return $this->pass;
        }

        if (!empty($args['type'])) {//如果需要验证数据类型，就验证数据类型
            $tpos = strpos($args['type'], '=');
            if ($tpos) {
                $_funcName = substr($args['type'], 0, $tpos);
                $error_title = substr($args['type'], $tpos + 1, strlen($args['type']));
            } else {
                $_funcName = $args['type'];
                $error_title = null;
            }

            $funcName = 'validate' . ucwords($_funcName);
            if (method_exists($this, $funcName)) {
                $isPass = $this->{$funcName}($this->arr[$args['field']], $args, $error_title); //验证数据类型
            } else {
                $funcName = $_funcName;
                if (!function_exists($funcName)) {
                    return true;
                }

                $isPass = call_user_func($funcName, $this->arr[$args['field']]);

                if (!$isPass) {
                    if ($tpos) {
                        $this->setMsg($args['field'], null, $error_title);
                    } else {
                        $this->setMsg($args['field'], $args['cname'], '');
                    }
                }
            }

            if (!$isPass) {
                $this->pass = false;
                return $this->pass;
            }
        }

        //验证表达式
        $this->doExpressions($args);

        return $this->pass;
    }

    /**
     * 验证必填字段
     * @param array $args 规则数据
     * @return boolean false:不需要检验后续的验证条件;  true:需要检验后续的验证条件
     */
    public function validateRequire($args) {

        if (!isset($this->arr[$args['field']])) {

            if (strncasecmp($args['require'], 'require', 7) != 0) {
                $this->pass = true;
                return false;
            }

            $this->pass = false;

            $mpos = strpos($args['require'], '=');
            $name = $mpos ? null : $args['cname'];
            $text = $mpos ? substr($args['require'], $mpos + 1, strlen($args['require'])) : '不能为空!';
            $this->setMsg($args['field'], $name, $text);

            return false;
        }

        if (strncasecmp($args['require'], 'require', 7) == 0 && empty($this->arr[$args['field']])) {

            $this->pass = false;

            $mpos = strpos($args['require'], '=');
            $name = $mpos ? null : $args['cname'];
            $text = $mpos ? substr($args['require'], $mpos + 1, strlen($args['require'])) : '不能为空!';
            $this->setMsg($args['field'], $name, $text);

            return false;
        }

        if (!empty($this->arr[$args['field']])) {
            return true;
        }

        return false;
    }

    /**
     * 验证表达式
     * @param array $args 规则数据
     * @return boolean 执行成功
     */
    public function doExpressions($args) {

        if (!preg_match_all('/([\s|\S]*)\(([\s|\S]*)\)=?([\s|\S]*)/', $args['expression'], $t)) {//如果不需要验证其他规则，就直接返回
            return true;
        }

        foreach ($t[0] as $k => $v) {

            if ($t[1][$k] == 'time_format') {
                continue;
            }

            $tt = array(
                'expression' => trim($t[1][$k]),
                'setting' => trim($t[2][$k]),
                'title' => trim($t[3][$k]),
            );

            $equal_func = "erun" . ucwords($tt['expression']);
            if (method_exists($this, $equal_func)) {
                $this->{$equal_func}($args, $tt);
                continue;
            }

            $equal_func = "_erun_" . $t[1][$k];
            if (!function_exists($equal_func)) {
                continue;
            }

            list($return, $msg) = call_user_func($equal_func, $this->arr[$args['field']], $args, $tt);
            if ($return) {
                continue;
            }

            $this->pass = false;
            $name = !empty($tt['title']) ? null : $args['cname'];
            $text = !empty($tt['title']) ? $tt['title'] : $msg;
            $this->setMsg($args['field'], $name, $text);
        }

        return true;
    }

    /**
     * 表达式equal判断与某个字段是否相等
     * @param array $args
     * @param array $t  提示数据
     * @return $this
     */
    public function erunEqual($args, $t) {

        if ($this->arr[$args['field']] == $this->arr[$t['setting']]) {
            $this->pass = true;
            return $this;
        }

        $this->pass = false;
        $name = !empty($t['title']) ? null : $args['cname'];
        $text = !empty($t['title']) ? $t['title'] : '不一致!';
        $this->setMsg($args['field'], $name, $text);

        return $this;
    }

    /**
     * 表达式length，判断字符长度
     * @param array $args
     * @param array $t  提示数据
     * @return $this
     */
    public function erunLength($args, $t) {

        if (strpos($t['setting'], ':') === false) {
            return $this;
        }

        $length_rule = explode(':', $t['setting']);
        $length = strlen($this->arr[$args['field']]);

        if ($length_rule[0] == '' && $length > $length_rule[1]) {
            $title = sprintf("大于 %s 字符的限制!", $length_rule[1]);
        }
        if ($length_rule[1] == '' && $length < $length_rule[0]) {
            $title = sprintf("不得小于 %s 字符!", $length_rule[0]);
        }
        if ($length_rule[0] != '' && $length_rule[1] != '' && ($length < $length_rule[0] || $length > $length_rule[1])) {
            $title = sprintf("必须介于 %s 和 %s 字符之间!", $length_rule[0], $length_rule[1]);
        }

        if (!isset($title)) {
            return $this;
        }

        $this->pass = false;
        $name = !empty($t['title']) ? null : $args['cname'];
        $text = !empty($t['title']) ? $t['title'] : $title;
        $this->setMsg($args['field'], $name, $text);

        return $this;
    }

    //表达式size，验证数字型的大小,支持digits,int和float
    public function erunSize($args, $t) {

        if (!(strpos($t['setting'], ':') !== false && preg_match('/^(digits|int|float)/i', $args['type']))) {
            return true;
        }

        $size_rule = explode(':', $t['setting']);

        $num = $this->arr[$args['field']];

        if ($size_rule[0] == '' && $num > $size_rule[1]) {
            $title = sprintf("不得大于 %s!", $size_rule[1]);
        }
        if ($size_rule[1] == '' && $num < $size_rule[0]) {
            $title = sprintf("不得小于 %s!", $size_rule[0]);
        }
        if ($size_rule[0] != '' && $size_rule[1] != '' && ($num < $size_rule[0] || $num > $size_rule[1])) {
            $title = sprintf("必须在 %s 和 %s 之间!", $size_rule[0], $size_rule[1]);
        }

        if (!isset($title)) {
            return $this;
        }

        $this->pass = false;
        $name = !empty($t['title']) ? null : $args['cname'];
        $text = !empty($t['title']) ? $t['title'] : $title;
        $this->setMsg($args['field'], $name, $text);

        return $this;
    }

    /**
     * 获取错误提示信息
     * @return type
     */
    public function getMsg() {

        if (!$this->break) {//如果是 验证所有的要验证的数据，就返回所有错误信息
            return $this->errmsg;
        }

        return end($this->errmsg);
    }

    /**
     * 验证是否必须是字符
     * @param mix $value 要验证的数据
     * @param array $args 规则数据
     * @param string|null $typemsg 提示
     * @return boolean 是否验证通过 true：通过  false：不通过
     */
    public function validateString($value, $args, $typemsg = null) {
        if (is_string($value)) {
            return true;
        }

        $name = isset($typemsg) ? null : $args['cname'];
        $text = isset($typemsg) ? $typemsg : '必须是字符!';
        $this->setMsg($args['field'], $name, $text);

        return false;
    }

    /**
     * 验证是否只能是数字和字母
     * @param mix $value 要验证的数据
     * @param array $args 规则数据
     * @param string|null $typemsg 提示
     * @return boolean 是否验证通过 true：通过  false：不通过
     */
    public function validateTime($value, $args, $typemsg = null) {
        $test = @strtotime($value);

        if ($test === false || $test === - 1) {
            if (isset($typemsg)) {
                $this->setMsg($args['field'], null, $typemsg);
            } else {
                $this->setMsg($args['field'], $args['cname'], '不是正确的时间/日期格式!');
            }
            return false;
        }

        if (preg_match('/time_format\(([\s|\S]*)\)=?([\s|\S]*)/', $args['expression'], $t)) {
            if (date($t[1], $test) == $value) {
                return true;
            }

            if (!empty($t[2])) {
                $this->setMsg($args['field'], null, $t[2]);
            } else {
                $this->setMsg($args['field'], $args['cname'], '不是正确的时间/日期格式!');
            }
            return false;
        }

        return true;
    }

    /**
     * 验证是否只能是数字和字母
     * @param mix $value 要验证的数据
     * @param array $args 规则数据
     * @param string|null $typemsg 提示
     * @return boolean 是否验证通过 true：通过  false：不通过
     */
    public function validateAlnum($value, $args, $typemsg = null) {
        if (ctype_alnum($value)) {
            return true;
        }
        if (isset($typemsg)) {
            $this->setMsg($args['field'], null, $typemsg);
        } else {
            $this->setMsg($args['field'], $args['cname'], '只能是数字和字母!');
        }
        return false;
    }

    /**
     * 验证是否纯英文字
     * @param mix $value 要验证的数据
     * @param array $args 规则数据
     * @param string|null $typemsg 提示
     * @return boolean 是否验证通过 true：通过  false：不通过
     */
    public function validateAlpha($value, $args, $typemsg = null) {
        if (ctype_alpha($value)) {
            return true;
        }
        if (isset($typemsg)) {
            $this->setMsg($args['field'], null, $typemsg);
        } else {
            $this->setMsg($args['field'], $args['cname'], '必须是纯英文字!');
        }
        return false;
    }

    /**
     * 验证是否 只能是字母数字和下划线
     * @param mix $value 要验证的数据
     * @param array $args 规则数据
     * @param string|null $typemsg 提示
     * @return boolean 是否验证通过 true：通过  false：不通过
     */
    public function validateAlnumu($value, $args, $typemsg = null) {
        if (preg_match('/[^a-zA-Z0-9_]/', $value) == 0) {
            return true;
        }

        if (isset($typemsg)) {
            $this->setMsg($args['field'], null, $typemsg);
        } else {
            $this->setMsg($args['field'], $args['cname'], '只能是字母数字和下划线!');
        }
        return false;
    }

    /**
     * 验证是否必须是数字
     * @param mix $value 要验证的数据
     * @param array $args 规则数据
     * @param string|null $typemsg 提示
     * @return boolean 是否验证通过 true：通过  false：不通过
     */
    public function validateDigits($value, $args, $typemsg = null) {
        if (is_numeric($value)) {
            return true;
        }

        if (isset($typemsg)) {
            $this->setMsg($args['field'], null, $typemsg);
        } else {
            $this->setMsg($args['field'], $args['cname'], '必须是数字!');
        }
        return false;
    }

    /**
     * 验证是否可见字符
     * @param mix $value 要验证的数据
     * @param array $args 规则数据
     * @param string|null $typemsg 提示
     * @return boolean 是否验证通过 true：通过  false：不通过
     */
    public function validateGraph($value, $args, $typemsg = null) {
        if (ctype_graph($value)) {
            return true;
        }

        if (isset($typemsg)) {
            $this->setMsg($args['field'], null, $typemsg);
        } else {
            $this->setMsg($args['field'], $args['cname'], '必须是可见字符!');
        }
        return false;
    }

    /**
     * 验证是否小写
     * @param mix $value 要验证的数据
     * @param array $args 规则数据
     * @param string|null $typemsg 提示
     * @return boolean 是否验证通过 true：通过  false：不通过
     */
    public function validateLower($value, $args, $typemsg = null) {
        if (ctype_lower($value)) {
            return true;
        }

        if (isset($typemsg)) {
            $this->setMsg($args['field'], null, $typemsg);
        } else {
            $this->setMsg($args['field'], $args['cname'], '必须全部小写!');
        }
        return false;
    }

    /**
     * 验证是否可以打印
     * @param mix $value 要验证的数据
     * @param array $args 规则数据
     * @param string|null $typemsg 提示
     * @return boolean 是否验证通过 true：通过  false：不通过
     */
    public function validatePrint($value, $args, $typemsg = null) {
        if (ctype_print($value)) {
            return true;
        }

        if (isset($typemsg)) {
            $this->setMsg($args['field'], null, $typemsg);
        } else {
            $this->setMsg($args['field'], $args['cname'], '必须是可打印字符!');
        }
        return false;
    }

    /**
     * 验证标点符号
     * @param mix $value 要验证的数据
     * @param array $args 规则数据
     * @param string|null $typemsg 提示
     * @return boolean 是否验证通过 true：通过  false：不通过
     */
    public function validatePunct($value, $args, $typemsg = null) {
        if (ctype_punct($value)) {
            return true;
        }
        if (isset($typemsg)) {
            $this->setMsg($args['field'], null, $typemsg);
        } else {
            $this->setMsg($args['field'], $args['cname'], '必须是标点符号!');
        }
        return false;
    }

    /**
     * 验证空格或制表符
     * @param mix $value 要验证的数据
     * @param array $args 规则数据
     * @param string|null $typemsg 提示
     * @return boolean 是否验证通过 true：通过  false：不通过
     */
    public function validateWhitespace($value, $args, $typemsg = null) {
        if (ctype_space($value)) {
            return true;
        }

        if (isset($typemsg)) {
            $this->setMsg($args['field'], null, $typemsg);
        } else {
            $this->setMsg($args['field'], $args['cname'], '必须为空格或制表符!');
        }
        return false;
    }

    /**
     * 验证大写
     * @param mix $value 要验证的数据
     * @param array $args 规则数据
     * @param string|null $typemsg 提示
     * @return boolean 是否验证通过 true：通过  false：不通过
     */
    public function validateUpper($value, $args, $typemsg = null) {
        if (ctype_upper($value)) {
            return true;
        }

        if (isset($typemsg)) {
            $this->setMsg($args['field'], null, $typemsg);
        } else {
            $this->setMsg($args['field'], $args['cname'], '必须全部大写!');
        }
        return false;
    }

    /**
     * 验证整数
     * @param mix $value 要验证的数据
     * @param array $args 规则数据
     * @param string|null $typemsg 提示
     * @return boolean 是否验证通过 true：通过  false：不通过
     */
    public function validateInt($value, $args, $typemsg = null) {
        if (is_null($this->_locale)) {
            $this->_locale = localeconv();
        }

        $value = str_replace($this->_locale['decimal_point'], '.', $value);
        $value = str_replace($this->_locale['thousands_sep'], '', $value);

        if (strval(intval($value)) == $value) {
            return true;
        }

        if (isset($typemsg)) {
            $this->setMsg($args['field'], null, $typemsg);
        } else {
            $this->setMsg($args['field'], $args['cname'], '必须为整数!');
        }
        return false;
    }

    /**
     * 验证浮点数
     * @param mix $value 要验证的数据
     * @param array $args 规则数据
     * @param string|null $typemsg 提示
     * @return boolean 是否验证通过 true：通过  false：不通过
     */
    public function validateFloat($value, $args, $typemsg = null) {
        if (is_null($this->_locale)) {
            $this->_locale = localeconv();
        }

        $value = str_replace($this->_locale['decimal_point'], '.', $value);
        $value = str_replace($this->_locale['thousands_sep'], '', $value);

        if (strval(floatval($value)) == $value) {
            return true;
        }

        if (isset($typemsg)) {
            $this->setMsg($args['field'], null, $typemsg);
        } else {
            $this->setMsg($args['field'], $args['cname'], '必须为浮点数!');
        }
        return false;
    }

    /**
     * 是否是 IPv4 地址（格式为 a.b.c.h）
     * @param mix $value 要验证的数据
     * @param array $args 规则数据
     * @param string|null $typemsg 提示
     * @return boolean 是否验证通过 true：通过  false：不通过
     */
    public function validateIpv4($value, $args, $typemsg = null) {
        $test = @ip2long($value);
        if ($test !== - 1 && $test !== false) {
            return true;
        }

        if (isset($typemsg)) {
            $this->setMsg($args['field'], null, $typemsg);
        } else {
            $this->setMsg($args['field'], $args['cname'], '不是有效的IP地址!');
        }

        return false;
    }

    /**
     * 是否是二进制数值
     * @param mix $value 要验证的数据
     * @param array $args 规则数据
     * @param string|null $typemsg 提示
     * @return boolean 是否验证通过 true：通过  false：不通过
     */
    public function validateBinary($value, $args, $typemsg = null) {
        if (preg_match('/[01]+/', $value)) {
            return true;
        }

        if (isset($typemsg)) {
            $this->setMsg($args['field'], null, $typemsg);
        } else {
            $this->setMsg($args['field'], $args['cname'], '必须是二进制数字!');
        }
        return false;
    }

    /**
     * 是否是 Internet 域名
     * @param mix $value 要验证的数据
     * @param array $args 规则数据
     * @param string|null $typemsg 提示
     * @return boolean 是否验证通过 true：通过  false：不通过
     */
    public function validateDomain($value, $args, $typemsg = null) {
        if (preg_match('/[a-zA-Z0-9.]+/i', $value)) {
            return true;
        }

        if (isset($typemsg)) {
            $this->setMsg($args['field'], null, $typemsg);
        } else {
            $this->setMsg($args['field'], $args['cname'], '不是有效的域名!');
        }

        return false;
    }

    /**
     * 验证邮箱
     * @param mix $value 要验证的数据
     * @param array $args 规则数据
     * @param string|null $typemsg 提示
     * @return boolean 是否验证通过 true：通过  false：不通过
     */
    public function validateEmail($value, $args, $typemsg = null) {
        if (preg_match('/^[a-zA-Z0-9_-]+@[a-zA-Z0-9_-]+.[a-zA-Z]{2,4}$/i', $value)) {
            return true;
        }
        if (isset($typemsg)) {
            $this->setMsg($args['field'], null, $typemsg);
        } else {
            $this->setMsg($args['field'], $args['cname'], '不是有效的email!');
        }
        return false;
    }

    public function validateNoempty($value, $args, $typemsg = null) {

        if (!empty($value)) {//如果不为空
            return true;
        }

        if (isset($typemsg)) {
            $this->setMsg($args['field'], null, $typemsg);
        } else {
            $this->setMsg($args['field'], $args['cname'], '不是有效的email!');
        }
        return false;
    }

    /**
     * 设置提示
     * @param string $key  提示字段
     * @param string $name
     * @param string $text 提示信息
     * @return $this
     */
    public function setMsg($key, $name = null, $text = '') {
        if (isset($this->errmsg[$key]) && $this->errmsg[$key]) {
            return $this;
        }

        $this->errmsg[$key] = !empty($name) ? $name : str_replace(array('{SP}', '{LF}', '{VE}'), array(' ', "n", '|'), $text);

        return $this;
    }

}
