<?php
namespace app\index\model;

use think\Model;
class Attentionquestion extends Model
{
	public function checkattention($uid,$quesid)
    {
        $re = $this->where("userid=$uid and quesid=$quesid")->find();
        if($re){
            return $re['attentionid'];
        }else{
            return false;
        }
    }
    public function addattention($uid,$quesid)
    {
    	$this->userid = $uid;
    	$this->quesid = $quesid;
    	$this->save();
    }
    public function getQuesByUid($num,$userid)
    {
        $page = ($num-1)*4;
        $re = $this->where(['userid'=>$userid])->limit($page,4)->select();
        return $re;  
    }
    public function getAttentionNumByQid($quesid)
    {
        $re = $this->field('count(attentionid) as count')->where(['quesid'=>$quesid])->find();
        return $re['count'];
    }
    public function getAttentionNumByUid($userid)
    {
        $re = $this->field('count(attentionid) as count')->where(['userid'=>$userid])->find();
        return $re['count'];
    }
}