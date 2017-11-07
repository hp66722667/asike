<?php
namespace app\admin\controller;

use app\admin\model\Role;
use app\admin\model\Admin as AdminModel;
use app\admin\model\Permission;
use think\Request;
use think\Session;
use think\Validate;

class Admin extends Auth
{
    protected $is_login = ['*'];
    protected $role;
    protected $admin;
    protected $permission;
    public function _initialize()
    {
        parent::_initialize();
        $this->role = new Role();
        $this->admin = new AdminModel();
        $this->permission = new Permission();
    }

    public function index()
    {
        return $this->fetch('admin/index');
    }

    //添加角色
    public function addRole(Request $request)
    {
        $roleName = trim($request->param('rolename'));
        $result = $this->role->addRolename($roleName);
        if ($result){
            $this->redirect('roleList');
        }else{
            $this->error('添加失败！','roleList');
        }

    }

    //查询角色名是否已经存在
    public function isInRolelist(Request $request)
    {
        $rolename = $request->param('roname');
        $result = $this->role->isInList($rolename);
        if ($result){
            $info = ['code'=>1, 'info'=>'该角色名已经存在'];
            echo json_encode($info);
        }else{
            $info = ['code'=>0, 'info'=>'该角色可以添加'];
            echo json_encode($info);
        }
    }

    //管理员列表
    public function adminList(Request $request)
    {
        $adStr = $request->param('str');
        $adminInfo = $this->admin->selectAdmin($adStr);

        foreach ($adminInfo as $key => $value){
            $adminid = $value->adminid;
            $adminInfo[$key]['rolename'] = $this->adminRole($adminid);
        }
        $roles = $this->role->selectRole();
        $page = $adminInfo->render();
        $this->assign([
            'adminInfo' => $adminInfo,
            'page'      => $page,
            'roles'     => $roles
        ]);
        return $this->fetch();
    }

    //多对多查询管理员对应的角色
    public function adminRole($adminid)
    {
        $arr = [];
        $adinfo = $this->admin->get($adminid);
        foreach ($adinfo->role as $key => $value){
            $arr[$key] = $value->rolename;
        }
        $roleStr = join(',', $arr);
        return $roleStr;
    }

    //更改角色
    public function upRole(Request $request)
    {
        $adminid = $request->param('adminid');
        $roleid = $request->param('uprolename');
        //单独更新中间表
        $adminInfo = $this->admin->get($adminid);
        $adminInfo->role()->detach(); //删除中间表数据
        $result = $adminInfo->role()->attach($roleid);//给中间表增加一条新的数据
        if ($result){
            $this->redirect('adminList');
        }else{
            $this->error('角色更改失败','adminList');
        }
    }

    //添加管理员页面
    public function addAdmin()
    {
        $roles = $this->role->selectRole();
        $this->assign([
           'roles' => $roles
        ]);
        return $this->fetch();
    }

    //添加管理员数据表
    public function insertAdmin(Request $request)
    {
        $adname = trim($request->param('adname'));
        $ademail = trim($request->param('ademail'));
        $roleid = $request->param('adrole'); //获取角色id
        $data = [
            'adname' => $adname,
            'ademail' => $ademail
        ];
        $newAdminid = $this->admin->insertAdminInfo($data);
        if ($newAdminid){
            $adminInfo = $this->admin->get($newAdminid);
            $result = $adminInfo->role()->save($roleid); //关联新增，此处仅新增中间表数据
            if ($result){
                $this->redirect('adminList');
            }
        }else{
            $this->error('添加失败','addAdmin');
        }
    }

    //邮箱验证
    public function verEmail(Request $request)
    {
        $validate = new Validate([
            'email|邮箱'=>'email'
        ]);
        $data = [
            'email' => $request->param('ademail'),
        ];
        if (!$validate->check($data)) {
            $errorInfo = $validate->getError();
            $info = ['code' => 0, 'info' => $errorInfo];
            echo json_encode($info);
        }else{
            $info = ['code' => 1, 'info' => '验证成功'];
            echo json_encode($info);
        }
    }

    //判断用户名在数据表中是否存在
    public function isInList(Request $request)
    {
        $adname = trim($request->param('adname'));
        $result = $this->admin->isInAdmin($adname);
        if ($result){
            $info = ['code' => 1, 'info' => '该管理员已经存在！'];
            echo json_encode($info);
        }else{
            $info = ['code' => 0, 'info' => '用户名可以添加！'];
            echo json_encode($info);
        }
    }

    //角色列表
    public function roleList(Request $request)
    {
        $roleStr = $request->param('str');
        $roles = $this->role->selectRole($roleStr);
        $roleNum = count($roles);
        foreach ($roles as $key => $value){
            $roleid = $value->roleid;
            $roles[$key]['permission'] = $this->rolePermission($roleid);
        }
        $page = $roles->render();
        $this->assign([
            'roles' => $roles,
            'roleNum' => $roleNum,
            'page' => $page
        ]);
        return $this->fetch();
    }

    //修改角色信息（包括分配权限）
    public function editRole(Request $request)
    {
        $permInfo = $this->permission->perInfo(0);
        $rolename = $request->param('rname');
        $roleid   = $request->param('rid');
        $perms = $this->rolePermission($roleid);
        $permission = $this->permission->selectPerm();

        $this->assign([
            'rolename'   => $rolename,
            'permInfo'   => $permInfo,
            'perms'      => $perms,
            'roleid'     => $roleid,
            'permission' => $permission
        ]);
        return $this->fetch();
    }

    //更新角色表、角色权限中间表
    public function upRoleInfo(Request $request)
    {
        $roleinfo = $request->param();
        //dump($roleinfo);
        $rolename = trim($request->param('rolename'));
        $roleid = $request->param('roleid');
        $data = ['rolename'=>$rolename];
        $result = $this->role->updateRole($roleid, $data); //更新角色表

        //关联更新中间表
        $role = $this->role->get($roleid);
        //删除中间表对应的数据
        if (empty($roleinfo['permid'])){
            $rolePerm = $role->permission()->detach();
            if($result && $rolePerm){
                $this->redirect('更改成功!','roleList');
            }
        }else{
            $role->permission()->detach();
            $rolePermid = $roleinfo['permid'];
            for ($i = 0; $i < count($rolePermid); $i++){
                $upRolePerm = $role->permission()->attach($rolePermid[$i]);
            }
            if ($result && $upRolePerm){
                $this->redirect('roleList');
            }else{
                $this->error('更改失败','roleList');
            }
        }
    }

    //多对多查询角色的权限
    public function rolePermission($roleid)
    {
        $arr = [];
        $roleinfo = $this->role->get($roleid);
        foreach ($roleinfo->permission as $key => $value){
            $arr[$key] = $value->permissionid;
        }
        //$permissionStr = join(',', $arr);
        return $arr;
    }

    //添加权限
    public function addPermission(Request $request)
    {
        $fpermid = $request->param('perFmname'); //获取父级权限id
        if (empty($fpermid)){
            $fid = 0;
        }else{
            $fid = $fpermid;
        }
        $pername = trim($request->param('permSname'));
        $data = [
            'fid' => $fid,
            'permname' => $pername
        ];
        $result = $this->permission->insertPrem($data);
        if ($result){
            $this->redirect('permList');
        }else{
            $this->error('添加权限失败！', 'permList');
        }
    }

    //权限列表
    public function permList()
    {
        $permInfo = $this->permission->perInfo(0);
        $this->assign([
            'permInfo' => $permInfo,
        ]);
        return $this->fetch();
    }

    //编辑权限
    public function editPerm(Request $request)
    {
        $permid = $request->param('permid');
        $permname = $request->param('permname');
        $permission = $this->permission->selectPerm();
        $this->assign([
            'permname'   => $permname,
            'permission' => $permission,
            'permid'    => $permid
        ]);
        return $this->fetch();
    }

    //更改父级权限
    public function upFperm(Request $request)
    {
        $permid = $request->param('permissionid');
        $permname = trim($request->param('permname'));
        $fpermid = $request->param('perFmname');
        if ($fpermid == ''){
            $data = [
                'permname' => $permname
            ];
        }else{
            $fid = $fpermid;
            $data = [
                'fid' => $fid,
                'permname' => $permname
            ];
        }
        $result = $this->permission->upPermission($permid, $data);
        if ($result){
            $this->redirect('permList');

        }else{
            $this->error('更改失败！','permList');
        }
    }

    //个人信息页面
    public function adminInfo()
    {
        $adminid = Session::get('adminid');
        $adminInfo = $this->admin->get($adminid);
        $adminInfo['rolename'] = $this->adminRole($adminid);
        //dump($adminInfo['rolename']);die;
        $this->assign([
            'adminInfo' => $adminInfo,
            'adminRole' => $adminInfo['rolename']
        ]);
        return $this->fetch();
    }

    //更新个人信息
    public function upAdminInfo(Request $request)
    {
        $adminid = $request->param('adminid');
        $adname = trim($request->param('adname'));
        $ademail = trim($request->param('ademail'));
        $data = [
            'adname' => $adname,
            'ademail'=> $ademail
        ];
        $result = $this->admin->upAdmin($adminid, $data);
        if ($result){
            $this->redirect('adminInfo');
        }
    }

    //修改密码
    public function upPwd()
    {
        return $this->fetch();
    }
    public function upadpwd(Request $request)
    {
        $adminid = $adminid = Session::get('adminid');
        $newPwd = trim($request->param('renewpwd'));
        $result = $this->admin->upPassword($adminid, $newPwd);
        if ($result){
            $this->redirect('adminInfo');
        }
    }

    //对比输入的原密码是否正确
    public function isRightPwd(Request $request)
    {
        $oldpwd = trim($request->param('oldpwd'));
        $adminid = Session::get('adminid');
        $result = $this->admin->isRightPassword($adminid, $oldpwd);
        if ($result){
            $info = ['code'=>1, 'info'=>'原密码正确'];
            echo json_encode($info);
        }else{
            $info = ['code'=>0, 'info'=>'原密码错误'];
            echo json_encode($info);
        }
    }
}