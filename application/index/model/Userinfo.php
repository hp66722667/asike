<?php
namespace app\index\model;

use think\Model;
class Userinfo extends Model
{
    //上传头像
    public function upPic($imageName ,$uid)
    {
        $userinfo = $this->get($uid);
        $userinfo->photo = $imageName;
        $re = $userinfo->save();
        if ($re){
            return true;
        }else{
            return false;
        }
    }
}