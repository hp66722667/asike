<?php
/**
 * Created by PhpStorm.
 * User: 凝思杨
 * Date: 2017/10/19
 * Time: 21:21
 */

namespace app\index\model;


use think\Model;

class Comment extends Model
{
	public function getCommentByAid($answerid,$page)
	{
		$re = $this->where("answerid=$answerid")->order('create_time desc')->limit(($page-1)*2,2)->select();
		if($re){
			return $re;
		}else{
			return false;
		}
	}
	public function getCount($answerid)
	{
		$re = $this->where("answerid=$answerid")->field('count(commentid) as count')->find();
		return $re['count'];
	}
	public function addCommentByUid($quesid,$answerid,$senduserid,$takeuserid,$commentcontent)
	{
		$this->quesid = $quesid;
		$this->answerid = $answerid;
		$this->senduserid = $senduserid;
		$this->takeuserid = $takeuserid;
		$this->commentcontent = $commentcontent;
		if($this->save()){
			return true;
		}else{
			return false;
		}
	}
	public function notlook($uid)
	{
		$re = $this->where("islook=0 and takeuserid=$uid")->select();
		if($re){
			return $re;
		}else{
			return false;
		}
	}

}