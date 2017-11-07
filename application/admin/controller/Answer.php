<?php
namespace app\admin\controller;

use think\Request;
use app\admin\model\Answer as AnswerModel;
use app\admin\model\User;
class Answer extends Auth
{
    protected $is_login = ['*'];
    protected $answer;
    protected $user;
    public function _initialize()
    {
        parent::_initialize();
        $this->answer = new AnswerModel();
        $this->user = new User();
    }

    //回答列表
    public function answerList(Request $request)
    {
        $answerStr = $request->param('str');
        $answerInfo = $this->answer->selectAnswer($answerStr);
        foreach ($answerInfo as $key => $value){
            $anewerid = $value->answerid;

            $answerInfo[$key]['ansqueuser'] = $this->ansQuesUser($anewerid);

        }
        $page = $answerInfo->render();
        $this->assign([
            'answerInfo' => $answerInfo,
            'page' => $page
        ]);
        return $this->fetch();
    }

    //一对多分别相对查询回答的问题与回答者
    public function ansQuesUser($anewerid)
    {
        $arr = [];
        $ansinfo = $this->answer->get($anewerid);
        $userName = $ansinfo->user->nickname;
        $quesname = $ansinfo->question->quesname;
        $arr['nickname'] = $userName;
        $arr['quesname'] = $quesname;
        return $arr;
    }





}