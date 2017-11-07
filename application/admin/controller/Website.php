<?php
namespace app\admin\controller;


use think\Request;

class Website extends Auth
{
    protected $is_login = ['*'];
    public function _initialize()
    {
        parent::_initialize();
    }

    public function webSite(Request $request)
    {
        if (!empty($request->param())) {
            $str = file_get_contents('../application/config.php');
            foreach($request->param() as $key=>$val){
                $pattern='/\'__' .$key .'__\'=>\'.*?\'/';
                $replace='\'__' .$key .'__\'=>\''.$val.'\'';
                $str = preg_replace($pattern, $replace, $str);
            }
            $re = file_put_contents('../application/config.php',$str);
            if ($re) {
                $this->redirect('webSite');
            }
        }
        return $this->fetch('admin/website');
    }
}