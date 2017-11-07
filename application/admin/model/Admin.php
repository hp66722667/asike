<?php
namespace app\admin\model;

use think\Model;
class Admin extends Model
{
    public function adlogin($adinfo)
    {
        $re = $this->get([
            'adname' => $adinfo['adname'],
            'adpassword' => md5($adinfo['password'])
        ]);
        if ($re){
            session('adminid', $re->adminid);
            session('adname', $re->adname);
            return true;
        }else{
            return false;
        }
    }

    //查询管理员信息
    public function selectAdmin($fiel = '')
    {
        $re = $this->where("adname", "like", "%$fiel%")->paginate(10);
        if ($re){
            return $re;
        }else{
            return false;
        }
    }

    //查询数据表中是否已经存在数据
    public function isInAdmin($adname)
    {
        $re = $this->getByAdname($adname);
        if ($re){
            return $re;
        }else{
            return false;
        }
    }

    //添加管理员
    public function insertAdminInfo($data)
    {
        $newAdmin = $this->data($data);
        $re = $newAdmin->save();
        if ($re){
            return $this->getLastInsID();
        }else {
            return false;
        }

    }

    //多对多关联角色
    public function role()
    {
        return $this->belongsToMany('Role', 'admin_role',
            'role_id','admin_id');
    }

    //更新管理员信息
    public function upAdmin($adminid, $data)
    {
        $adminInfo = $this->get($adminid);
        $adminInfo->data($data);
        $re = $adminInfo->save();
        if ($re == 0){
            return -1;
        }else{
            return $re;
        }
    }

    //查询输入的原密码是否正确
    public function isRightPassword($adminid, $oldpwd)
    {
        $re = $this->where("adminid", $adminid)
            ->where("adpassword", md5($oldpwd))->select();
        if ($re){
            return $re;
        }else{
            return false;
        }
    }

    //更改密码
    public function upPassword($adminid, $newpwd)
    {
        $adminInfo = $this->get($adminid);
        $adminInfo->adpassword = md5($newpwd);
        $re = $adminInfo->save();
        if ($re == 0){
            return -1;
        }else{
            return $re;
        }
    }
}