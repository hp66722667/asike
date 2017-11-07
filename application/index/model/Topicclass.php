<?php
namespace app\index\model;

use think\Model;
class Topicclass extends Model
{
    public function tc()
    {
        return $this->select();

    }
    //一对多查询对应子话题
    public function topic()
    {
        return $this->hasMany('Topic');
    }

}