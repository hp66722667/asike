<?php
namespace app\index\controller;

use think\Controller;
use app\index\model\Question as QuestionModel;
use app\index\model\Topic;
use app\index\model\User;
use app\index\model\Mgc;
use app\index\model\Attentionquestion;
use app\index\model\Agreeoppose;
use app\index\model\Userinfo;
use think\Request;

class Question extends Auth
{
    protected $is_login = ['*'];
    protected $mgc;
    protected $question;
    protected $topic;
    protected $user;
    protected $attentionquestion;
    protected $agreeoppose;
    public function _initialize()
    {
        parent::_initialize();
        $this->question = new QuestionModel();
        $this->topic = new Topic();
        $this->user = new User();
        $this->mgc = new Mgc();
        $this->agreeoppose = new Agreeoppose();
        $this->attentionquestion = new Attentionquestion();
    }


    //添加问题
    public function addQuestion(Request $request)
    {
        $uid = session('userid');
        $topicname = trim($request->param('qutopic'));

        $result = $this->topic->getByTopicname("$topicname");

        if($result == null){
            $topicid = $this->topic->addTopic($topicname,11);
            if($topicid == false){
                echo json_encode(['code'=>0,'info'=>'添加话题失败,请重试']);die;
            }
        }else{
            $topicid = $result->topicid; 
        }
        $quesname = trim($request->param('qutitle'));
        if($this->question->getByQuesname($quesname)){
            echo json_encode(['code'=>1,'info'=>'该问题已存在,您可进行搜索']);die;
        }else{
            $dataList = [
                [
                    'topic_id'     => $topicid,
                    'user_id'      => $uid,
                    'quesname'     => $quesname,
                    'quesdescribe' => $request->param('qudes'),
                ]
            ];
            $quesid = $this->question->addQu($dataList);
            if($quesid){
                echo json_encode(['code'=>3,'info'=>'提问成功','quesid'=>$quesid]);
            }else{
                echo json_encode(['code'=>2,'info'=>'提问失败,请重试']);
            }
        }
        
    }
    public function getQuesListByStr(Request $request){
        $str = $request->param('term');
        $re = $this->question->getQuLiByStr($str);
        
        foreach ($re as $key => $value) {
            $result[] = array( 
                'label' => $value['quesname'] 
            ); 
        }
        if(isset($result)){
            echo json_encode($result);
        }
    }
    public function getQuesListByUid()
    {       
        $this->question->getQuesByUid(1);
    }
    public function quesDetailByQid(Request $request)
    {
        $mgc = $this->mgc->field('mgctext')->find();
        $str = $mgc['mgctext'];
        $str = "/$str/";  // 关键字正则字符串  

        $uid = session('userid');
        $quesid = $request->param('quesid');
        $question = $this->question->getQuesByQid($quesid);
        $question->setInc('browsenum');
        $topicid = $question->topic_id;
        $topic = $this->topic->get($topicid);
        $answers = $question->answer()->order('agreenum desc,create_time desc')->paginate(5);
        $page = $answers->render();
        $isatten = 0;
        $atten = $this->attentionquestion->checkattention($uid,$quesid);
        if($atten){
            $isatten = 1;
        }

        foreach($answers as $key=>$answer)//给answers添加回答者的nickname
        {
            $answers[$key]['user'] = $answer->user;
            $answers[$key]['userinfo'] = $answer->user->userinfo;
            $answers[$key]['isagree'] = $this->agreeoppose->isagree($answer['answerid'],$uid);
            $content = $answer['answercontent'];
            
            $answer['answercontent'] = preg_replace($str, "**", $content); 
        }   
        $user = $this->user->get($uid);
        $info = $user->userinfo;
        $this->assign(['question'=>$question,'userid'=>$uid,'user'=>$user,'userinfo'=>$info,'answers'=>$answers,'isatten'=>$isatten,'page'=>$page,'topic'=>$topic]);
        return $this->fetch('answer/quesdetail');
    }
    public function attentionQues(Request $request)
    {
        $uid = session('userid');
        $quesid = $request->param('quesid');
        $re = $this->attentionquestion->checkattention($uid,$quesid);
        if($re){
            $question = $this->question->get($quesid);
            $question->setInc('attentionnum','-1');
            $atten = $this->attentionquestion->get($re);
            $atten->delete();
            echo json_encode(false);
        }else{
            $question = $this->question->get($quesid);
            $question->setInc('attentionnum');

            $this->attentionquestion->addattention($uid,$quesid);
            echo json_encode(true);
        }
        
        
    }

}