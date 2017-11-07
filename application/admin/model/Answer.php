<?php
namespace app\admin\model;

use think\Model;
class Answer extends Model
{
    public function question()
    {
        return $this->belongsTo('Question', 'quesid');
    }

    public function user()
    {
        return $this->belongsTo('User','userid');
    }

    //查询回答
    public function selectAnswer($field='')
    {
        $re = $this->where("answercontent", "like", "%$field%")->order("answerid","desc")
            ->paginate(10);
        if ($re){
            return $re;
        }else{
            return false;
        }
    }

    //查询被举报的回答
    public function selectRepoetAnswer($field='')
    {
        $re = $this->where("answercontent", "like", "%$field%")
            ->where("isreport",1)
            ->where("ishidden",0)
            ->order("answerid","desc")
            ->paginate(10);
        if ($re){
            return $re;
        }else{
            return false;
        }
    }

    //屏蔽处理
    public function hideAnswer($answerid, $ishidden)
    {
        $answerInfo = $this->get($answerid);
        $answerInfo->ishidden = $ishidden;
        $re = $answerInfo->save();
        if ($re){
            return $re;
        }else{
            return false;
        }
    }

    //解除屏蔽
    public function showAnswer($answerid, $ishidden)
    {
        $answerInfo = $this->get($answerid);
        $answerInfo->ishidden = $ishidden;
        $re = $answerInfo->save();
        if ($re){
            return $re;
        }else{
            return false;
        }
    }

    //查看已经处理的举报
    public function selectDisposed($field='')
    {
        $re = $this->where("answercontent", "like", "%$field%")
            ->where("isreport",1)
            ->where("ishidden",1)
            ->order("answerid","desc")
            ->paginate(10);
        if ($re){
            return $re;
        }else{
            return false;
        }
    }
}