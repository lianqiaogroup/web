<?php

namespace lib;

class register {

    public static $register = [];

    public function set($key, $value) {
        if (!$this->has($key)) {
            self::$register[$key] = $value;
        }
    }

    public function has($key) {
        if (!self::$register) {
            return false;
        }
        return in_array($key, self::$register);
    }

    public function get($key) {
        return self::$register[$key];
    }

    public static function getInstance($key) {
        return self::$register[$key];
    }

    /**
     * 创建一个实例
     * @param string $class 类的完整路径
     * @param mix $args 类构造方法参数
     * @return object
     */
    public static function createInstance($class, ...$args) {
        $alias = md5(json_encode(func_get_args()));
        if (isset(self::$register[$alias]) && self::$register[$alias] instanceof $class) {
            return self::$register[$alias];
        }

        self::$register[$alias] = new $class(...$args);

        return self::$register[$alias];
    }

}
