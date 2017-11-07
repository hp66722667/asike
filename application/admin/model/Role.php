<?php
namespace app\admin\model;

use think\Model;
class Role extends Model
{
    public function addRolename($roleName)
    {
        $this->rolename = $roleName;
        $re = $this->save();
        $inId = $this->getLastInsID();
        if($re)
        {
            return $inId;
        }else{
            return false;
        }

    }

    public function selectRole($field='')
    {
        $re = $this->where("rolename", "like", "%$field%")->paginate(10);
        if ($re) {
            return $re;
        }else{
            return false;
        }
    }

    //多对多关联权限表
    public function permission()
    {
        return $this->belongsToMany('Permission','role_permission',
            'permission_id','role_id');
    }

    //更新角色表
    public function updateRole($roleid, $data)
    {
        $roleInfo = $this->get($roleid);
        $roleInfo->data($data);
        $re = $roleInfo->save();
        if ($re == 0){
            return -1;
        }else{
            return $re;
        }
    }

    //角色表中是否存在该角色名
    public function isInList($rolename)
    {
        $re = $this->getByRolename($rolename);
        if ($re){
            return $re;
        }else{
            return false;
        }
    }
}