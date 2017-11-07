<?php
/**
 * Created by PhpStorm.
 * User: 凝思杨
 * Date: 2017/10/19
 * Time: 21:43
 */

namespace app\index\model;


use think\Model;

class User extends Model
{
	public function setPasswordAttr($value)
	{
		return md5($value);
	}
	public function userinfo()
	{
		return $this->hasOne('Userinfo');
	}
	public function answers()
	{
		return $this->hasMany('Userinfo','userid');
	}
	public function getNameLiByStr($str){
        return $this->where("nickname","like","%$str%")->select();
    }

	public function add($user)
	{
		$this->nickname = $user['nickname'];
		$this->phone = $user['phone'];
		$this->password = $user['password'];
		
		if($this->save()){
			return $this->userid;
		}else{
			return 0;
		}
	}
	public function login($user){
		//dump($user);
		$re = $this->get([
			'phone'=>$user['account'],
			'password'=>md5($user['password'])
			]);
		if(!$re){
			$re = $this->get([
			'email'=>$user['account'],
			'password'=>md5($user['password'])
			]);
		}
		if($re){
			session('userid',$re->userid);
			return 1;
		}else{
			return 0;
		}
	}
	public function checkname($nickname)
	{
		$re = $this->get(['nickname'=>$nickname]);
		if($re){
			$info = ['code'=>0,'info'=>'用户名已存在'];
			echo json_encode($info);
		}else{
			$info = ['code'=>1,'info'=>'用户名可用'];
			echo json_encode($info);
		}
	}
	public function checkPhone($phone)
	{
		$re = $this->get(['phone'=>$phone]);
		if($re){
			$info = ['code'=>0,'info'=>'号码已存在'];
			echo json_encode($info);
		}else{
			$info = ['code'=>1,'info'=>'号码可用'];
			echo json_encode($info);
		}
	}
	public function checkYzm($yzm)
	{
		if($yzm == session('yzm')){
			$info = ['code'=>1,'info'=>'填写正确'];
			echo json_encode($info);
		}else{
			$info = ['code'=>0,'info'=>'填写错误'];
			echo json_encode($info);
		}
	}
	
	
	public function sendYzm($phone)
	{
		$phone = $phone;
		define('BASE_URL', 'https://api.ucpaas.com/');
		define('SOFT_VERSION','2014-06-30');

		$accountSid = 'b8104067bc0e21f00a0fe02952ddb369';
		$authorToken = 'c694143bdfc86d40e3e3f245115380e3';
		//设置默认时区
		date_default_timezone_set('PRC');
		$timestamp = date('YmdHis');

		$sigParameter = strtoupper(md5($accountSid.$authorToken.$timestamp));

		$url = BASE_URL . SOFT_VERSION . '/Accounts/' . $accountSid . '/Messages/templateSMS?sig=' . $sigParameter;

		$authorization = base64_encode($accountSid.':'.$timestamp);
		//拼接header
		$header = [
					'Accept:application/json',
					'Content-Type:application/json;charset=utf-8',
					'Authorization:'.$authorization
				];

		$nums = '1234567890';
		$yzm = substr(str_shuffle($nums),0,4);
		$data['templateSMS'] = [
								'appId'=>'43533da1fd8e4dcf9802fd6f1a2e70e0',
								'templateId'=>'153836',
								'to'=>$phone,
								'param'=>$yzm
							];

		$body = json_encode($data);

		//发送请求
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch,CURLOPT_POST,1);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$body);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		$result = curl_exec($ch);
		curl_close($ch);
		//var_dump($result);
		$result = json_decode($result, true);
		$respCode = $result['resp']['respCode'];

		if ($respCode == '000000') {
			$send = '发送成功';
			//将验证码保存在session中
			session('yzm',$yzm);
		} else {
			$send = '发送失败 ';
		}
		echo json_encode(['send' => $send]);
	}
}