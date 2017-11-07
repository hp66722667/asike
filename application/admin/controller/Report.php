<?php
namespace app\admin\controller;

use app\admin\model\Answer;
use app\admin\model\User;
use app\admin\model\Mgc;
use think\Request;

class Report extends Auth
{
    protected $is_login = ['*'];
    protected $answer;
    protected $user;
    protected $mgc;
    public function _initialize()
    {
        parent::_initialize();
        $this->answer = new Answer();
        $this->user = new User();
        $this->mgc = new Mgc();
    }
    //被举报的回答
    public function report(Request $request)
    {
        $answerStr = $request->param('str');
        $answerInfo = $this->answer->selectRepoetAnswer($answerStr);
        foreach ($answerInfo as $key => $value){
            $anewerid = $value->answerid;
            $reporid = $value->reporter;
            $answerInfo[$key]['ansqueuser'] = $this->ansQuesUser($anewerid, $reporid);
        }
        $page = $answerInfo->render();
        $this->assign([
            'answerInfo' => $answerInfo,
            'page' => $page
        ]);
        return $this->fetch();
    }

    //屏蔽回答
    public function hiddenAnswer(Request $request)
    {
        $answerid = $request->param('answid');
        $ishidden = 1;
        $result = $this->answer->hideAnswer($answerid, $ishidden);
        if ($result){
            $info = ['code'=>1, 'info'=>'屏蔽成功！'];
            echo json_encode($info);
        }else{
            $info = ['code'=>0, 'info'=>'屏蔽失败！'];
            echo json_encode($info);
        }
    }

    //解除屏蔽
    public function showAnswer(Request $request)
    {
        $answerid = $request->param('answid');
        $ishidden = 0;
        $result = $this->answer->showAnswer($answerid, $ishidden);
        if ($result){
            $info = ['code'=>1, 'info'=>'解除屏蔽成功！'];
            echo json_encode($info);
        }else{
            $info = ['code'=>0, 'info'=>'解除屏蔽失败！'];
            echo json_encode($info);
        }
    }

    //已经处理的举报
    public function disposed(Request $request)
    {
        $answerStr = $request->param('str');
        $answerInfo = $this->answer->selectDisposed($answerStr);
        foreach ($answerInfo as $key => $value){
            $anewerid = $value->answerid;
            $reporid = $value->reporter;
            $answerInfo[$key]['ansqueuser'] = $this->ansQuesUser($anewerid, $reporid);
        }
        $page = $answerInfo->render();
        $this->assign([
            'answerInfo' => $answerInfo,
            'page' => $page
        ]);
        return $this->fetch();
    }

    //一对多分别相对查询回答的问题与回答者
    public function ansQuesUser($anewerid, $reporid)
    {
        $arr = [];
        $ansinfo = $this->answer->get($anewerid);
        $userinfo = $this->user->get($reporid);
        $userName = $userinfo->nickname;
        $reporterName = $ansinfo->user->nickname;
        $quesname = $ansinfo->question->quesname;
        $arr['username'] = $userName; //被举报人
        $arr['reportername'] = $reporterName; //举报人
        $arr['quesname'] = $quesname;
        return $arr;
    }

    //敏感词汇
    public function mgc()
    {
        $result = $this->mgc->selectMgc();
        $this->assign([
            'mgctext' => $result
        ]);
        return $this->fetch();
    }

    //更新敏感词
    public function upMgc(Request $request)
    {
        $mgctext = trim($request->param('mgc'));
        $result = $this->mgc->upMgctext($mgctext);
        if ($result){
            $this->redirect('mgc');
        }
    }
}