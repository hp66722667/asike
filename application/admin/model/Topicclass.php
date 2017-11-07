<?php
namespace app\admin\model;

use think\Model;

class Topicclass extends Model
{
    public function topic()
    {
        return $this->hasMany('Topic','topicclass_id');
    }

    public function selectTopicClass()
    {
        $re = $this->select();
        if ($re){
            return $re;
        }else{
            return false;
        }
    }

    //添加话题类别
    public function addTopicClass($data)
    {
        $this->data($data);
        $re = $this->save();
        if ($re){
            return $re;
        }else{
            return false;
        }
    }

    //查询类别名称是否已经存在
    public function isInTopicclass($topicclassname)
    {
        $re = $this->getByTopicclassname($topicclassname);
        if ($re){
            return $re;
        }else{
            return false;
        }
    }
}