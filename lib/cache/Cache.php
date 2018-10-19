<?php

namespace lib\cache;

use lib\cache\driver\Redis as RedisHandler;

class Cache {
    protected static $config = [
                    "driver"    => "redis",         //缓存引擎
                    "host"      => "127.0.0.1",     //主机地址
                    "port"      => 6379,            //端口号
                    "password"  => "",              //认证密码
                    "select"    => 1,               //数据库
                    "timeout"   => 0,               //有效期
                    "prefix"    => "",              //前缀
                    "persistence" => false          //是否持久化长连接
    ];
    
    public static function createInstance($config = []){        
        if(!empty(self::$config)){
            self::$config = array_merge(self::$config,$config);
        }
        $driver = array_shift(self::$config);
        if($driver == "redis"){ 
            return new RedisHandler(self::$config);
        }
    }
}