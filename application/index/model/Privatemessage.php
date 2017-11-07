<?php
/**
 * Created by PhpStorm.
 * User: 凝思杨
 * Date: 2017/10/19
 * Time: 21:28
 */

namespace app\index\model;


use think\Model;

class Privatemessage extends Model
{
	public function getPrivMessageByUid($uid)
	{
		/*$subQuery = $this->distinct(true)->field('senduserid')->where('takeuserid',$uid)->order('create_time desc')->select(false);
		dump($subQuery);*/
		/*$re = $this->table('('.$subQuery.' ) a')->field('count(*) as count,privmesscontent,senduserid,takeuserid,islook,create_time')->group('senduserid')->select();*/
		/*$re = $this->table('('.$subQuery.' ) a')->field('senduserid,count(*) as count,privmesscontent,senduserid,takeuserid,islook,create_time')->distinct(true)->select();
		dump($re);*/
		$re = $this->where(['takeuserid'=>$uid])->order('create_time desc')->paginate(5);
		// if($re){
			return $re;
	// 	}else{
	// 		return false;
	// 	}
	 }
	public function getPrivMessageBySid($senduserid,$uid)
	{
		$re = $this->where("(takeuserid=$uid and senduserid=$senduserid) or (takeuserid=$senduserid and senduserid=$uid)")->order('create_time desc')->paginate(5);
		if($re){
			return $re;
		}else{
			return false;
		}
	}
	public function addMessage($takeuserid,$senduserid,$privmesscontent)
	{
		$this->takeuserid = $takeuserid;
		$this->senduserid = $senduserid;
		$this->privmesscontent = $privmesscontent;
		if($this->save()){
			return true;
		}else{
			return false;
		}
	}
	public function lookedMessage($takeuserid,$senduserid)
	{
		$this->where("takeuserid=$takeuserid and senduserid=$senduserid")->update(['islook'=>1]);
	}
	public function getPrivMessCount($uid)
	{
		return $this->field('count(*) as count ')->where("takeuserid=$uid and islook=0")->find();
	}
}