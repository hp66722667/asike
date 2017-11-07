<?php
namespace app\index\controller;


use think\Controller;
use think\Request;

class Auth extends Controller
{
    protected $is_login = [''];
    public function _initialize()
    {
        if (!$this->checklogin() && in_array('*', $this->is_login)){
            $this->error('您还没有登陆，请登录', url('Index/login'));
        }
	}
    
	public function checklogin()
    {
        return session('?userid');
    }
}