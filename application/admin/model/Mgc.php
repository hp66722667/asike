<?php
namespace app\admin\model;

use think\Model;

class Mgc extends Model
{
    public function selectMgc()
    {
        $re = $this->get(1);
        if ($re){
            return $re;
        }else{
            return false;
        }
    }

    //更新敏感词数据表
    public function upMgctext($mgctext)
    {
        $mgcInfo = $this->get(1);
        $mgcInfo->mgctext = $mgctext;
        $re = $mgcInfo->save();
        if ($re == 0){
            return -1;
        }else{
            return $re;
        }
    }
}