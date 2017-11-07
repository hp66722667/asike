<?php
namespace app\admin\model;

use think\Model;
class Comment extends Model
{
    //与问题表关联
    public function question()
    {
        return $this->belongsTo('Question', 'quesid');
    }

    //与回答表关联
    public function answer()
    {
        return $this->belongsTo('Answer', 'answerid');
    }

    //查询评论表
    public function selectComment($field='')
    {
        $re = $this->where("commentcontent", "like", "%$field%")
            ->paginate(3); //分页查询
        if($re){
            return $re;
        }else{
            return false;
        }
    }
}