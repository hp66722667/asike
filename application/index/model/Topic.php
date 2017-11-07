<?php
/**
 * Created by PhpStorm.
 * User: 凝思杨
 * Date: 2017/10/19
 * Time: 21:30
 */

namespace app\index\model;


use think\Model;

class Topic extends Model
{
	public function question()
	{
		return $this->belongsTo('Question','quesid');
	}
	public function getToLiByStr($str){
        return $this->where("topicname","like","%$str%")->select();
    }
    public function addTopic($topicname,$topicclass_id,$topicdescribe='暂无该子话题描述')
    {
        $this->topicclass_id = $topicclass_id;
        $this->topicname = $topicname;
        $this->topicdescribe = $topicdescribe;
        if($this->save()){
            return $this->topicid;
        }else{
            return false;
        }
    }

}