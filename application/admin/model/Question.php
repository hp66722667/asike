<?php
namespace app\admin\model;

use think\Model;
use traits\model\SoftDelete;
class Question extends Model
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $updateTime = false;
    public function queList($fiel='')
    {
        $re = $this->where("quesname", "like", "%$fiel%")->paginate(10);
        if ($re){
            return $re;
        }else{
            return false;
        }
    }

    //批量删除
    public function softDelQues($arrqids)
    {
        $re = $this->destroy($arrqids);
        return $re;
    }

    public function topic()
    {
        return $this->belongsTo('Topic','topcid');
    }

    public function user()
    {
        return $this->hasOne('User','userid');
    }
}