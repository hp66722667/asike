<?php
namespace app\admin\model;

use think\Model;
use traits\model\SoftDelete;
class User extends Model
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    public function selectUser($fiel='')
    {
        $re = $this->where("nickname","like", "%$fiel%")->paginate(5);
        if ($re){
            return $re;
        }else{
            return false;
        }
    }

    public function userinfo()
    {
        return $this->hasOne('Userinfo','user_id');
    }

    //软删除
    public function softDelUsers($userid)
    {
        $re = $this->destroy($userid);
        if ($re){
            return $re;
        }else{
            return false;
        }
    }

    //锁定用户
    public function userLock($uid, $forbidden)
    {
        $userInfo = $this->get($uid);
        $userInfo->forbidden = $forbidden;
        $re = $userInfo->save();
        if ($re){
            return $re;
        }else{
            return false;
        }
    }
}