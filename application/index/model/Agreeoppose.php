<?php
/**
 赞同反对回答表
 */
namespace app\index\model;


use think\Model;

class Agreeoppose extends Model
{
	public function check($agreerid,$answerid)
	{
		$re = $this->where(['agreerid'=>$agreerid,'answerid'=>$answerid])->find();
		if($re == null){
			return 0;
		}else{
			return $re['bid'];
		}
	}
	public function add($userid,$agreerid,$answerid,$quesid,$isagree)
	{
		//$this->userid = $userid;
		$this->data([
			'userid'=>$userid,
			'agreerid'=>$agreerid,
			'answerid'=>$answerid,
			'quesid'=>$quesid,
			'isagree'=>$isagree,
			]);
		if($this->save()){
			return 1;
		}else{
			return 0;
		}
	}
	public function updateagg($re,$isagree)
	{
		//$this->userid = $userid;
		$agg = $this->get($re);
		$agg->data([
			'isagree'=>$isagree,
			]);
		if($agg->save()){
			return 1;
		}else{
			return 0;
		}
	}
	public function isagree($answerid,$uid)
	{
		$re = $this->where(['agreerid'=>$uid,'answerid'=>$answerid])->find();
		if($re == null){
			return 0;
		}else{
			return $re['isagree'];
		}
	}
	public function notLook($uid)
	{
		$re = $this->where("userid=$uid and islook=0 and isagree=1")->select();
		if($re){
			return $re;
		}else{
			return false;
		}
	}
}