<?php
namespace app\index\model;

use think\Model;

class Question extends Model
{
    public function addQu($dataList)
    {
        $re = $this->saveAll($dataList);
        if ($re){
            return $re[0]['quesid'];
        }else{
            return false;
        }
    }

    public function answer()
    {
        return $this->hasMany('Answer','quesid');
    }
    public function user()
    {
        return $this->belongsTo('User','user_id');
    }
    public function topic()
    {
        return $this->belongsTo('Topic','topic_id');
    }
    public function getQuLiByStr($str){
        return $this->where("quesname","like","%$str%")->select();
    }
    public function getQuesByUid($num,$uid)
    {
    	$page = ($num-1)*4;
    	$re = $this->where(['user_id'=>$uid])->limit($page,4)->select();
    	return $re;  	
    }
    public function getQuLiByTid($topic_id)
    {
        $re = $this->where(['topic_id'=>$topic_id])->select();
        return $re; 
    }
    public function getQuesList($num)
    {
        $page = $num*8;
        $re = $this->order('browsenum desc,answernum desc,attentionnum desc,create_time desc')->limit($page,8)->select();
        return $re;     
    }
    public function getQuesByQid($quesid)
    {
        return $this->get($quesid);
    }

    public function pushQueSec()
    {
        $re = $this->where(['editorpush'=>1])->limit(5)->select();
        return $re;
    }
    public function hotQueSec()
    {
        $re = $this->order('browsenum','desc')->select();
        return $re;
    }
    public function getNumByUid($user_id)
    {
        $re = $this->field('count(quesid) as count')->where(['user_id'=>$user_id])->find();
        return $re['count'];
    }
}