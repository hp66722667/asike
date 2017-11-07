<?php
namespace app\index\controller;

use think\Controller;
use app\index\model\Topic as TopicModel;
use app\index\model\Topicclass;
use app\index\model\User;
use think\Request;

class Topic extends Auth
{
    protected $is_login = ['*'];
    protected $topic;
    protected $user;
    protected $topicClass;
    public function _initialize()
    {
        parent::_initialize();
        $this->topic = new TopicModel();
        $this->user = new User();
        $this->topicClass = new Topicclass();
    }
    public function getTopicListByStr(Request $request)
    {
        $str = $request->param('term');
        $re = $this->topic->getToLiByStr($str);
        
        foreach ($re as $key => $value) {
            $result[] = array( 
                'label' => $value['topicname'] 
            ); 
        }
        if(isset($result)){
            echo json_encode($result);
        }
    }
    


    /*//添加子话题
    public function add()
    {
        $this->topic->topicclass_id = 4;
        $this->topic->topicname = '音乐节';
        $this->topic->topicdescribe = '音乐节是一种大型的室外音乐演出活动，通常由许多类似曲风或类型的乐团共同演出。';
        dump($this->topic->save());
        dump($this->topic->getLastInsID());
    }*/


    public function topicguang(Request $request)
    {
        $uid = session('userid');
        $user = $this->user->get($uid);
        $userinfo = $user->userinfo;
        $tcid = 1;
        if (!empty($request->param('tcid'))){
            $tcid = $request->param('tcid');
        }
        $topicS = $this->topicClass->get($tcid);
        //一对多查询子话题
        $result = $topicS->topic()->select();
        $topClass = $this->topicClass->tc();
        $this->assign(['topClass'=>$topClass,'result'=>$result,'userid'=>$uid,'user'=>$user,'userinfo'=>$userinfo]);
        return $this->fetch();
    }
}