<?php
namespace app\admin\controller;

use app\admin\model\Admin;
use think\Controller;
use think\Request;
use think\Session;
use think\Validate;
use think\Db;

class Auth extends Controller
{
    protected $admin;
    protected $is_login = [''];
    public function _initialize()
    {
        $this->admin = new Admin();
        if (!$this->checklogin() && in_array('*', $this->is_login)){
            $this->error('您还没有登陆，请登录', url('admin/Auth/login'));
        }

        //不需要判断的其他的方法节点
        $ig_url=[
            '/admin/Admin/index',
            '/admin/Auth/login',
            '/admin/Auth/logout',
            '/admin/Auth/checklogin',
            '/admin/Auth/adinfo',
            '/admin/Auth/checkinfo',
        ];
        //获得用户所拥有的权限
        $privarr_urls=$this->getRole();
        // dump($privarr_urls);

        //取得当前节点进行拼接
        $request = Request::instance();//获取当前域名
        $module = $request->module();//获取当前模块
        $controller = $request->controller();//获取当前控制器
        $action = $request->action();//获取当前方法
        //当前节点
        $node = '/'.$module.'/'.$controller.'/'.$action;

        //不用判断的页面
        if(in_array($node,$ig_url)){
            return true;
        }
        //超级管理员
        if(Session::get('adminid') == 1 || Session::get('adminid') == 2){
            return true;
        } else {
//            dump($node);
//            dump($privarr_urls);die;
            if(!in_array($node, $privarr_urls)){
                $this->error('你没有权限操作!',"admin/Admin/index");
            }
        }
    }

    /*
	*获取用户所有的权限
	*取出指定用户所有角色
	*在通过角色 取出 所属 权限关系
	*在权限表中取出所有权限
	*/

    //取出用户的所有权限
    public function getRole()
    {
        $adminid=Session::get('adminid');
        // dump($tu_id);
        $privarr_urls=[];

        //取出用户所述的角色 是一个数组
        $role_idss=Db::name('admin_role')->where('admin_id',$adminid)->select();
        //dump($role_idss);
        //die;
        if($role_idss){
            foreach ($role_idss as $keys => $values) {
                $role_ids[]=$values['role_id'];//角色ID
            }
//            dump($role_ids);
            //$role_ids = $role_idss['role_id'];
            // dump($role_ids);die;
            if($role_ids)
            {
                //通过角色取出所述的权限id,返回值是一个数组
                foreach($role_ids as $v)
                {
                    //取出权限ID
                    $access_idss[]=Db::name('role_permission')->
                        distinct(true)->where('role_id',$v)->
                        field('role_id,permission_id')->select();
                }
                //dump($access_idss);
                //die;
                foreach ($access_idss as $ke => $value) {
                    foreach ($value as  $val) {
                        $access_ids[]=$val['permission_id'];
                    }
                }
                //dump($access_ids);
                //die;
                if(!empty($access_ids)){
                    //在权限表中取出对应的权限  urls 是个数组
                    foreach($access_ids as $key=>$value)
                    {
                        $urllist[]=Db::name('permission')->where('permissionid',$value)->select();
                    }
                    //dump($urllist);die;
                    if($urllist)
                    {
                        foreach ($urllist as $key => $value) {
                            foreach($value as $val){
                                $tmp_urls = $val['contromethod'];
                                $privarr_urls[]=$tmp_urls; //将当前权限对应的url压入数组中
                            }
                        }
                    }
                }else{
                    return [];
                }
            }
        }
        //dump($privarr_urls);die;
        return $privarr_urls;
    }

    public function checklogin()
    {
        return session('?adminid');
    }

    public function login()
    {

        return $this->fetch('login/login');
    }

    public function logout()
    {
        session(null);
        $this->redirect('Auth/login');
    }

    public function adinfo()
    {
        $validate = new Validate([
            'captcha|验证码'=>'require|captcha'
        ]);
        $data = [
            'captcha' => $this->request->param('cap'),

        ];
        if (!$validate->check($data)) {
            $errorInfo = $validate->getError();
            $info = ['code' => 0, 'info' => $errorInfo];
            echo json_encode($info);
        }else{
            $info = ['code' => 1, 'info' => '验证码输入成功'];
            echo json_encode($info);
        }
    }

    //验证登录信息
    public function checkinfo(Request $request)
    {
        $result = $this->admin->adlogin($request->param());
        if($result){
            $this->redirect('Admin/index');
        }else{
            $this->error('登录失败！', 'login');
        }
    }
}