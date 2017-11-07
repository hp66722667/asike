<?php
namespace app\admin\model;

use think\Model;
class Permission extends Model
{
    public function perInfo($fid)
    {
        $perInfo = $this->select();
        $re = Tree::toList($perInfo, $fid);
        if($re){
            return $re;
        }else{
            return false;
        }
    }

    //查询权限表
    public function selectPerm()
    {
        $re = $this->select();
        if ($re){
            return $re;
        }else{
            return false;
        }
    }

    //向权限表添加数据
    public function insertPrem($data)
    {
        $per = $this->data($data);
        $re = $per->save();
        if ($re){
            return $re;
        }else{
            return false;
        }
    }

    //更新权限表
    public function upPermission($permid, $data)
    {
        $per = $this->get($permid);
        $per->data($data);
        $re = $per->save();
        if ($re == 0){
            return -1;
        }else{
            return $re;
        }
    }

}