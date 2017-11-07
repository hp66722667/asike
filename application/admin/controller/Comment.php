<?php
namespace app\admin\controller;

use app\admin\model\Comment as CommentModel;
use app\admin\model\User;
use think\Request;

class Comment extends Auth
{
    protected $is_login = ['*'];
    protected $comment;
    protected $user;
    public function _initialize()
    {
        parent::_initialize();
        $this->comment = new CommentModel();
        $this->user = new User();
    }

    //评论列表
    public function commentList(Request $request)
    {
        $commStr = $request->param('str');
        $commInfo = $this->comment->selectComment($commStr);
        foreach ($commInfo as $key => $value){
            $commid = $value->commentid;
            $comTuserid = $value->takeuserid; //被评论人id
            $comSuserid = $value->senduserid; //评论人id
            $commInfo[$key]['comToQuAnUser'] = $this->comQueAnsUser($commid, $comTuserid, $comSuserid);
        }
        $page = $commInfo->render();
        $this->assign([
            'commInfo' => $commInfo,
            'page'     => $page
        ]);
        return $this->fetch();
    }

    //一对多相对查询
    public function comQueAnsUser($commid, $comTuserid, $comSuserid)
    {
        $arr = [];
        $comminfo = $this->comment->get($commid);
        $question = $comminfo->question->quesname;
        $answer   = $comminfo->answer->answercontent;
        $arr['commtoques'] = $question;
        $arr['commtoansw'] = $answer;
        if ($comTuserid == 0){
            $arr['comtname'] = '';
        }else{
            $comtuser = $this->user->get($comTuserid);
            $arr['comtname'] = $comtuser->nickname;
        }
        $comsuser = $this->user->get($comSuserid);
        $arr['comsname'] = $comsuser->nickname;
        return $arr;
    }
}