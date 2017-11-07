<?php
namespace app\index\model;

use think\Model;
class Answer extends Model
{
	public function addAnswer($answer)
	{
		$this->userid = $answer['userid'];
		$this->quesid = $answer['quesid'];
		$this->anonymity = $answer['anonymity'];
		$this->answercontent = $answer['content'];
		$this->userid = $answer['userid'];

		if($this->save()){
			return 1;
		}else{
			return 0;
		}
	}
	public function changeAgreeNum($answerid,$isagree)
	{
		$answer = $this->get($answerid);
		$agreenum = $answer->agreenum;
		if($agreenum != 0){
			if($isagree == 1){
				$answer->agreenum = $agreenum + 1;
			}else{
				$answer->agreenum = $agreenum - 1;
			}			
		}else{
			if($isagree == 1){
				$answer->agreenum = $agreenum + 1;
			}else{
				$answer->agreenum = 0;
			}	
		}
		if($answer->save()){
			return $answer->agreenum;
		}else{
			return false;
		}
		
	}
	
	public function getAnswByUid($num,$uid)
    {
    	$page = ($num-1)*4;
    	$re = $this->where(['userid'=>$uid])->limit($page,4)->select();
    	return $re;  	
    }
	public function user()
	{
		return $this->belongsTo('User','userid');
	}
	public function question()
	{
		return $this->belongsTo('Question','quesid');
	}
	public function getNumByUid($userid)
    {
        $re = $this->field('count(answerid) as count')->where(['userid'=>$userid])->find();
        return $re['count'];
    }
    public function getAnswerNumByQid($quesid)
    {
    	$re = $this->field('count(answerid) as count')->where(['quesid'=>$quesid])->find();
        return $re['count'];
    }
}