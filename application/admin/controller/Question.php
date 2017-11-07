<?php
namespace app\admin\controller;

use think\Request;
use app\admin\model\Question as QuestionModel;
use app\admin\model\User;
use app\admin\model\Topic;
class Question extends Auth
{
    protected $is_login = ['*'];
    protected $question;
    protected $user;
    protected $topic;
    public function _initialize()
    {
        parent::_initialize();
        $this->question  = new QuestionModel();
        $this->user = new User();
        $this->topic = new Topic();
    }

    public function questionList(Request $request)
    {
        $quenameStr = $request->param('str');
        $questInfo = $this->question->queList($quenameStr);
        $page = $questInfo->render();

        foreach ($questInfo as $key => $value){
            $quesid = $value->quesid;
            $userid = $value->user_id;
            $topicid = $value->topic_id;
            $questInfo[$key]['userTopic'] = $this->queUserTopic($userid, $topicid);
//            dump($questInfo[$key]['userTopic']);
            //dump($key);
        }

        $this->assign([
            'questInfo' => $questInfo,
            'page' => $page
        ]);
        return $this->fetch();
    }

    public function queUserTopic($userid, $topicid)
    {
        $arr = [];
        $userInfo = $this->user->get($userid);
        $userName = $userInfo->nickname;
        $arr['nickname'] = $userName;
        //通过topicid查询所属话题
        $topicInfo = $this->topic->get($topicid);
        $topicname = $topicInfo->topicname;
        $arr['topicname'] = $topicname;
        return $arr;

    }

    //批量删除
    public function deleteQuestion(Request $request)
    {
        //多删
        $arrqueid = $request->param();
        if (empty($arrqueid)){
            $this->error('您没有选择要删除问题', 'questionList');
        }
        //将多个id拼接成字符串
        $quiStr = join(',',$arrqueid['quesid']);
        $result = $this->question->softDelQues($quiStr);
        if ($result) {
            $this->success('删除成功', 'questionList');
        } else {
            $this->error('删除失败', 'questionList');
        }
    }

    //单删
    public function deleteOne(Request $request)
    {
        $quesid = $request->param('quesid');
        $result = $this->question->softDelQues($quesid);
        if ($result){
            $info = ['code'=>1, 'info'=>'删除成功！'];
            echo json_encode($info);
        }else{
            $info = ['code'=>0, 'info'=>'删除失败！'];
            echo json_encode($info);
        }
    }
}