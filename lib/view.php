<?php
namespace lib;

class view{
    public $path;
    public $root_path;
    public $view;
    public $loader;

    public function __construct($path,$root_path)
    {
              $this->path = $path;
              $this->root_path = $root_path;
              $loader = new \Twig_Loader_Filesystem([$this->path,$this->root_path]);
              $this->loader =  $loader;
              $this->view =  new \Twig_Environment($loader);
    }

    public function display($template,$arg=[]){

        $this->view->display($template,$arg);
    }
    public function render($template,$arg=[])
    {
       return $this->view->render($template,$arg);
    }
    public function showSuccessTemplates($theme,$data)
    {
        $this->loader->prependPath('theme/'.$theme);
        $this->view->display('success.twig',$data);
    }
    public function showErrorTemplates($data)
    {
        $this->view->display('error_pay.twig',$data);
    }
    public function show_404()
    {
        $this->loader->prependPath('public/theme');
        $this->view->display('error_template.twig');
    }


}