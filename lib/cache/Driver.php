<?php

namespace lib\cache;

abstract class Driver {
    
    /**
     * 设置缓存
     */
    abstract public function set($key,$value,$expire = null);
    
    /**
     * 获取缓存数据
     */
    abstract public function get($key);
    
    /**
     * 判断某个键值是否存在
     */
    abstract public function has($key);
    
    /**
     * 删除某个键值
     */
    abstract public function del($key);
}