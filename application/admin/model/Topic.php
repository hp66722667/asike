<?php
namespace app\admin\model;


use think\Model;

class Topic extends Model
{

    public function selectTopic($fiel='')
    {
        return $this->where("topicname", "like", "%$fiel%")->order('topicid','desc')->paginate(10);
    }

    public function topicclass()
    {
        return $this->belongsTo('Topicclass','topicclass_id');
    }

    public function isInTopic($topicname)
    {
        return $this->getByTopicname($topicname);
    }

    //添加话题
    public function insTopic($data)
    {
        $this->data($data);
        $re = $this->save();
        if ($re){
            return $re;
        }else{
            return false;
        }
    }
}