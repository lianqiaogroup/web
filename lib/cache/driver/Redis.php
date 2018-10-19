<?php

namespace lib\cache\driver;

use lib\cache\Driver;

class Redis extends Driver {
    
    protected $handler = null;
    
    private $_options = [
                "host"      => "127.0.0.1",
                "port"      => 6379,
                "password"  => "",
                "select"    => 0,
                "timeout"   => 0,
                "expire"    => 0,
                "prefix"    => "",
                "persistence" => false    //是否长连接
            ];

    public function __construct($options = []) {

        if(!extension_loaded("redis")){
            throw new  Exception("not supports : redis");
        }
        if(!empty($options)){
            $this->_options = array_merge($this->_options,$options);
        }
        
        $func = $this->_options['persistence'] ? "pconnect":"connect";
        $this->handler = new \Redis();
        $this->handler->$func($this->_options['host'],$this->_options['port'],$this->_options["timeout"]);
        if(!empty($this->_options['password'])){
            $this->handler->auth($this->_options['password']);
        }        
        if(0 != $this->_options["select"]){ 
            $this->handler->select($this->_options['select']);
        }
    }


    public function set($key,$value,$expire = 0){
        if(empty($expire)){
            return $this->handler->set($key,$value);
        }else {
            return $this->setEx($key,$expire,$value);
        }
    }
    
    public function get($key){
        return $this->handler->get($key);
    }
    
    /**
     * 设置带过期时间的键值
     * @param type $key
     * @param type $expire
     * @param type $value
     * @return type
     */
    public function setEx($key,$expire,$value){
        return $this->handler->setex($key,$expire,$value);
    }
    
    /**
     * 指定设置$key的新值，并返回当前 $key 的旧值
     * @param type $key
     * @param type $value
     * @return type
     */
    public function getSet($key,$value){
        return $this->handler->getSet($key,$value);
    }


    /**
     * 获取键值中的子字符串
     * @param string $key  
     * @param int $start  开始索引
     * @param int $end    结束索引
     * @return string
     */
    public function getRange($key,$start,$end){
        return $this->handler->getRange($key,$start,$end);
    }
    
    /**
     * 判断键值是否存在
     * @param type $key
     */
    public function has($key) {
        return $this->handler->exists($key) ? true : false;
    }
    
    /**
     * 删除指定key的值
     * @param type $key
     * @return type
     */
    public function del($key) {
        return $this->handler->delete($key);
    }
    
    /**
     * 对值进行递增操作
     * @param type $key
     * @param type $range  幅度，默认1
     */
    public function incr($key,$range = 1){
        return $this->handler->incr($key,$range);
    }
    
    /**
     * 对键进行递减操作
     * @param type $key
     * @param type $range 幅度，默认为1
     */
    public function decr($key,$range = 1){
        return $this->handler->decr($key,$range);
    }
    
    /**
     * 从列表的头部插入键值
     * @param type $key
     * @param type $value
     * @return type
     */
    public function lPush($key,$value){
        return $this->handler->lPush($key,$value);
    }
    
    /**
     * 从列表的尾部插入一个元素
     * @param type $key
     * @param type $value
     * @return type
     */
    public function rPush($key,$value){
        return $this->handler->rPush($key,$value);
    }
    
    /**
     * 从列表的头部或尾部弹出元素
     * @param type $key
     * @return type
     */
    public function lPop($key){
        return $this->handler->lPop($key);
    }
    
    /**
     * 返回当前列表的长度
     * @param type $key
     * @return type
     */
    public function lSize($key){
        return $this->handler->lSize($key);
    }
    
    /**
     * 在列表中指定的索引位置插入元素
     * @param type $key
     * @param type $value
     * @param type $index
     * @return type
     */
    public function lSet($key,$value,$index = 0){
        return $this->handler->lSet($key,$index,$value);
    }
    
    /**
     * 获取列表中指定索引位置的元素
     * @param type $key
     * @param type $index
     */
    public function lGet($key,$index = 0){
        return $this->handler->lGet($key,$index);
    }
    
    /**
     * 返回列表指定长度的元素
     * @param type $key
     * @param type $start 开始索引
     * @param type $end 结束索引，默认-1为全部
     */
    public function lGetRange($key,$start = 0,$end = -1){
        return $this->handler->lGetRange($key,$start,$end);
    }
    
    /**
     * 对指定长度的列表元素进行截取
     * @param type $key
     * @param type $start
     * @param type $end
     */
    public function lTrim($key,$start = 0 ,$end = -1){
        return $this->handler->ltrim($key,$start,$end);
    }
    
    /**
     * 向集合中插入元素，如果插入的键存在，则返回FALSE；
     * @param type $key
     * @param type $value
     */
    public function sAdd($key,$value){
        return $this->handler->sAdd($key,$value);
    }
    
    public function sRemove($key,$value){
        return $this->handler->sRemove($key,$value);
    }
    
    /**
     * 将集合元素进行移动
     * @param type $oldset 待移出的集合
     * @param type $newset 待移进的集合
     * @param type $value 待移动的元素
     */
    public function sMove($oldset,$newset,$value){
        return $this->handler->sMove($oldset,$newset,$value);
    }
    
    /**
     * 在指定的元素中查找指定的元素是否存在，存在返回True,失败返回False
     * @param type $key
     * @param type $value
     */
    public function sIsMember($key,$value){
        return $this->handler->sismember($key,$value);
    }
    
    /**
     * 返回指定集合中元素的个数
     * @param type $key
     */
    public function sSize($key){
        return $this->handler->sSize($key);
    }
    
    /**
     * 从集合中移出并返回一个随机的元素
     * @param type $key
     */
    public function sPop($key){
        return $this->handler->sPop($key);
    }
    
    /**
     * 返回两个集合的差集，不存在的集合KEY为NULL
     * @param type $key1
     * @param type $key2
     * @return type
     */
    public function sDiff($key1,$key2){
        return $this->handler->sDiff($key1,$key2);
    }
    
    /**
     * 返回指定N个几个的差集，并将返回的差集保存在指定的$store集合中
     * @param type $store 指定保存差集的集合
     * @param type $key 待比较的集合 (key1,key2,...$keyN) N>=2
     * @return type
     */
    public function sDiffStore($store,...$key){
        return $this->handler->sDiffStore($store, implode(",", $key));
    }
    
    /**
     * 返回指定集合中的所有元素
     * @param type $key
     */
    public function sMembers($key){
        return $this->handler->sMembers($key);
    }
    
    /**
     * 返回指定N个集合的并集
     * @param type $key
     * @return type
     */
    public function sUnion(...$key){
        return $this->handler->sUnion(implode(",", $key));
    }
    
    /**
     * 返回指定N个集合的并集，并存储到指定的集合中
     * @param type $store 指定存储的集合
     * @param type $key 带比较的集合 [key1,key2,...$keyN] N>=2
     * @return type
     */
    public function sUnionStore($store,...$key){
        return $this->handler->sUnionStore($store, implode(",", $key));
    }
    
    public function hSet($key,$field,$value){
        return $this->handler->hSet($key,$field,$value);
    }
    
    public function hGet($key,$field){
        return $this->handler->hGet($key,$field);
    }
    
    public function hDel($key,$field){
        return $this->handler->hDel($key,$field);
    }
    
    public function hLen($key){
        return $this->handler->hLen($key);
    }
 
    public function hVals($key){
        return $this->handler->hVals($key);
    } 
    
    public function hGetAll($key){
        return $this->handler->hGetAll($key);
    }
    
    public function hExists($key,$hashkey){
        return $this->handler->hExists($key,$hashkey);
    }
    
    public function hIncrBy($key,$field,$range = 1){
        return $this->handler->hIncrBy($key,$field,$range);
    }
    
    public function hMset($key,$fields = array()){
        return $this->handler->hMset($key,$fields);
    }
    
    public function hMGet($key,$fields = array()){
        return $this->handler->hMget($key,$fields);
    }
    
    public function Move($key,$database){
        return $this->handler->move($key,$database);
    }
    
    public function Close(){
        return true;
    }
    
}
