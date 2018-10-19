<?php

namespace admin\Controllers;

class Controller {

    public function __construct() {
        
    }

    /**
     * Dispatch a job to its appropriate handler.
     *
     * @param  mixed  $job
     * @return mixed
     */
    public function dispatch($method = '', $parameters = []) {
        $method = $method ? $method : I('get.act', 'list') . 'Action';
        return $this->callAction($method, $parameters);
    }

    /**
     * Execute an action on the controller.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return Response
     */
    public function callAction($method, $parameters) {
        return call_user_func_array([$this, $method], $parameters);
    }

    /**
     * Handle calls to missing methods on the controller.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     *
     * @throws \BadMethodCallException
     */
    public function __call($method, $parameters) {
        return "Method [{$method}] does not exist on [" . get_class($this) . '].';
    }

    public function ajaxResponse($array = []) {
        //header("Accept-Ranges", "bytes");
        //header("Pragma", "no-cache");
        //header("Content-type:application/json");
        //header("charset:utf-8");
        ob_end_clean();
        ob_start();
        echo json_encode($array); //,JSON_UNESCAPED_UNICODE
        ob_end_flush();
        exit(0);
    }

    public function httpResponse($view, $data = []) {
        \lib\register::getInstance('view')->display($view, $data);
        exit(0);
    }

}
