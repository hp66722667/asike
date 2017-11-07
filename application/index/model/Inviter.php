<?php
/**
 * Created by PhpStorm.
 * User: 凝思杨
 * Date: 2017/10/19
 * Time: 21:24
 */

namespace app\index\model;


use think\Model;

class Inviter extends Model
{
	public function addInviter($userid,$inviterid,$quesid)
	{
		$this->userid = $userid;
		$this->inviterid = $inviterid;
		$this->quesid = $quesid;
		$this->save();

	}
	public function notlook($uid)
	{
		$re = $this->where("islook=0 and inviterid=$uid")->select();
		if($re){
			return $re;
		}else{
			return false;
		}
	}
}