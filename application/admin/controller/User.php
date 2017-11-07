<?php
namespace app\admin\controller;

use app\admin\model\User as UserModel;
use phpDocumentor\Reflection\Types\Resource;
use think\Request;

class User extends Auth
{
    protected $is_login = ['*'];
    protected $user;

    public function _initialize()
    {
        parent::_initialize();
        $this->user = new UserModel();
    }

    //查询用户信息
    public function userList(Request $request)
    {
        $nameStr = $request->param('str');
        $userInfo = $this->user->selectUser($nameStr);
        foreach ($userInfo as $key => $value) {
            $userid = $value->userid;
            $userInfo[$key]['userphoto'] = $this->userPhoto($userid);
        }
        $page = $userInfo->render();

        $this->assign([
            'userInfo' => $userInfo,
            'page' => $page
        ]);
        return $this->fetch();
    }

    //一对一查询用头像
    public function userPhoto($userid)
    {
        $arr = [];
        $userInfo = $this->user->get($userid);
        $userphoto = $userInfo->userinfo->photo;
        $arr['photo'] = $userphoto;
        return $arr;
    }

    //批量删除用户
    public function deleteUser(Request $request)
    {
        $userid = $request->param();
        $uidStr = join(',', $userid['userid']);
        $result = $this->user->softDelUsers($uidStr);
        if ($result) {
            $this->success('删除成功', 'userList');
        } else {
            $this->error('删除失败', 'userList');
        }
    }
    //单删
    public function deleteOne(Request $request)
    {
        $userid = $request->param('userid');
        $result = $this->user->softDelUsers($userid);
        if ($result){
            $info = ['code'=>1, 'info'=>'删除成功！'];
            echo json_encode($info);
        }else{
            $info = ['code'=>0, 'info'=>'删除失败！'];
            echo json_encode($info);
        }
    }

    //锁定用户
    public function lockUser(Request $request)
    {
        $userid = $request->param('userid');
        $forbidden = 1;
        $result = $this->user->userLock($userid, $forbidden);
        if ($result){
            $info = ['code'=>1, 'info'=>'锁定成功！'];
            echo json_encode($info);
        }else{
            $info = ['code'=>0, 'info'=>'锁定失败！'];
            echo json_encode($info);
        }
    }

    //解除锁定
    public function unlockUser(Request $request)
    {
        $userid = $request->param('userid');
        $forbidden = 0;
        $result = $this->user->userLock($userid, $forbidden);
        if ($result){
            $info = ['code'=>1, 'info'=>'解锁成功！'];
            echo json_encode($info);
        }else{
            $info = ['code'=>0, 'info'=>'解锁失败！'];
            echo json_encode($info);
        }
    }
}