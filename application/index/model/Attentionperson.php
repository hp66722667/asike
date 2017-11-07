<?php
/**
    关注话题表
 */

namespace app\index\model;


use think\Model;

class Attentionperson extends Model
{
	public function checkattention($attentionuserid,$userid)
	{
		$re = $this->where("userid=$userid and attentionuserid=$attentionuserid")->find();
        if($re){
            return $re['attentionid'];
        }else{
            return false;
        }
	}
	public function addattention($attentionuserid,$userid)
    {
    	$this->attentionuserid = $attentionuserid;
    	$this->userid = $userid;
    	$this->save();
    }
    public function getAttentionPersonByUid($num,$userid)
    {
        $page = ($num-1)*4;
        $re = $this->where(['userid'=>$userid])->limit($page,4)->select();
        return $re; 
    }
    public function getAttentionFansByUid($num,$attentionuserid)
    {
        $page = ($num-1)*4;
        $re = $this->where(['attentionuserid'=>$attentionuserid])->limit($page,4)->select();
        return $re; 
    }
    public function getNumByUid($attentionuserid)
    {
        $re = $this->field('count(attentionid) as count')->where(['attentionuserid'=>$attentionuserid])->find();
        return $re['count'];
    }
    public function getUserNumByUid($userid)
    {
        $re = $this->field('count(attentionid) as count')->where(['userid'=>$userid])->find();
        return $re['count'];
    }
}