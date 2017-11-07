<?php
namespace app\index\controller;

use \think\Controller;
use \think\Request;
use \think\Image;
use think\Validate;
use app\index\model\User as UserModel; 
use app\index\model\Question;
use app\index\model\Answer;
use app\index\model\Userinfo;
use app\index\model\Inviter;
use app\index\model\Attentionquestion;
use app\index\model\Attentionperson;
use think\view\driver\Think;

class User extends Auth
{
	protected $is_login = ['*'];
	protected $user;
	protected $question;
	protected $userinfo;
	protected $answer;
	protected $inviter;
	protected $attentionquestion;
	protected $attentionperson;
	public function _initialize()
	{
		parent::_initialize();
		$this->user = new UserModel();
		$this->question = new Question();
		$this->userinfo = new Userinfo();
		$this->answer = new Answer();
		$this->inviter = new Inviter();
		$this->attentionperson = new Attentionperson();
        $this->attentionquestion = new Attentionquestion();
	}
	public function userCenter(Request $request)
	{
		$uid = session('userid');
		$personid = $request->param('personid');
		if($personid == null){
			$personid = $uid;
		}

		$isatten = 0;
        $atten = $this->attentionperson->checkattention($personid,$uid);
        if($atten){
            $isatten = 1;
        }
        $attentionnum = $this->attentionperson->getUserNumByUid($personid);
        $fansnum = $this->attentionperson->getNumByUid($personid);
        $attentionquesnum = $this->attentionquestion->getAttentionNumByUid($uid);
        $myquesnum = $this->question->getNumByUid($uid);
		$person = $this->user->get($personid);
		$user = $this->user->get($uid);
		//dump($user->userinfo);
		$this->assign([
			'userid'=>$uid,
			'user'=>$user,
			'person'=>$person,
			'userinfo'=>$user->userinfo,
			'personinfo'=>$person->userinfo,
			'personid'=>$personid,
			'gender'=>$person->userinfo->gender,
			'isatten'=>$isatten,
			'attentionnum'=>$attentionnum,
			'fansnum'=>$fansnum,
			'attentionquesnum'=>$attentionquesnum,
			'myquesnum'=>$myquesnum,
			]);
		return $this->fetch();
	}
	public function userInfoEdit()
	{
		$uid = session('userid');
		$user = $this->user->get($uid);
		$this->assign(['userid'=>$uid,'user'=>$user,'userinfo'=>$user->userinfo]);
		return $this->fetch();
	}
	public function editInfo(Request $request)
	{
		$name = $request->param('name');
		$value = $request->param('value');
		$user = $this->user->get(session('userid'));
		$user->userinfo->$name = $value;
		if($user->userinfo->save()){
			echo json_encode(['code'=>1,'info'=>$value,'isfail'=>1]);
		}else{
			echo json_encode(['code'=>0,'info'=>'修改失败','isfail'=>0]);
		}
		
	}
	public function editTwoInfo(Request $request)
	{
		$pname = $request->param('pname');
		$pvalue = $request->param('pvalue');
		$cname = $request->param('cname');
		$cvalue = $request->param('cvalue');
		$user = $this->user->get(session('userid'));
		$user->userinfo->$pname = $pvalue;
		$user->userinfo->$cname = $cvalue;
		if($user->userinfo->save()){
			echo json_encode(['code'=>1,'pinfo'=>$pvalue,'cinfo'=>$cvalue]);
		}else{
			echo json_encode(['code'=>0,'info'=>'修改失败']);
		}
		
	}
	public function attentionPerson(Request $request)
	{
		$userid = session('userid');
		$attentionuserid = $request->param('attentionuserid');
		$re = $this->attentionperson->checkattention($attentionuserid,$userid);
        if($re){
            $atten = $this->attentionperson->get($re);
            $atten->delete();
            echo json_encode(false);
        }else{
            $this->attentionperson->addattention($attentionuserid,$userid);
            echo json_encode(true);
        }

	}
	public function getNameListByStr(Request $request)
	{
		$str = $request->param('term');

        $re = $this->user->getNameLiByStr($str);
        foreach ($re as $key => $value) {
            $result[] = array( 
                'label' => $value['nickname'] 
            ); 
        }
        if(isset($result)){
        	echo json_encode($result);
        }
       
	}
	public function addInviter(Request $request)
	{
		$userid = session('userid');
		$quesid = $request->param('quesid');
		$nickname = $request->param('nickname');
		$user = $this->user->getByNickname($nickname);
		if($user){
			$inviterid = $user['userid'];

			$this->inviter->addInviter($userid,$inviterid,$quesid);
				
				echo json_encode(['code'=>1]);

		}else{
			echo json_encode(['code'=>0]);
		}
	}
	public function getAnswers(Request $request)
	{
		$num = $request->param('num');
		$personid = $request->param('personid');
		$person = $this->user->get($personid);
		$isfirst = $request->param('isfirst');
		$answList = $this->answer->getAnswByUid($num,$personid);
		if($answList == null){
			echo json_encode(0);
		}else{
			foreach($answList as $key=>$answer){//给answList添加回答者的nickname
	            $answList[$key]['user'] = $answer->user;
	            $answList[$key]['userinfo'] = $answer->user->userinfo;
	            $answList[$key]['question'] = $answer->question;
		    }
		    $uid = session('userid');

        	$user = $this->user->get($uid);
        	$info = $user->userinfo;
			$this->assign([
				'answList' => $answList,
				'isfirst'  => $isfirst,
				'sql'      => $this->answer->getLastSql(),
				'num'      => $num,
				'user'     => $user,
				'userinfo'=>$info,
				'person'=>$person,
				'personinfo'=>$person->userinfo,
				]);
			return $this->fetch();
		}
	}
	public function getAsks(Request $request)
	{
		$num = $request->param('num');
		$personid = $request->param('personid');
		$person = $this->user->get($personid);
		$gender = $person->userinfo->gender;
		$isfirst = $request->param('isfirst');
		$quesList = $this->question->getQuesByUid($num,$personid);
		if($quesList == null){
			echo json_encode(0);
		}else{
			foreach($quesList as $key=>$question)
			{
				$quesList[$key]['answernum'] = $this->answer->getAnswerNumByQid($question['quesid']);
				$quesList[$key]['attentionnum'] = $this->attentionquestion->getAttentionNumByQid($question['quesid']);
			}
			$this->assign([
				'quesList'=>$quesList,
				'isfirst'=>$isfirst,
				'sql'=>$this->question->getLastSql(),
				'num'=>$num,
				'personid'=>$personid,
				'gender'=>$gender,
				]);
			return $this->fetch();
		}
		
	}
	public function getActivities(Request $request)
	{
		return $this->fetch();
	}
	public function getPosts(Request $request)
	{
		$num = $request->param('num');
		$personid = $request->param('personid');
		$person = $this->user->get($personid);
		$gender = $person->userinfo->gender;
		$isfirst = $request->param('isfirst');
		$attenQuesList = $this->attentionquestion->getQuesByUid($num,$personid);
		if($attenQuesList == null){
			echo json_encode(0);
		}else{
			foreach($attenQuesList as $key => $attenquestion)
			{	
				$question = $this->question->get($attenquestion['quesid']);
				$attenQuesList[$key]['question'] = $question;
				$attenQuesList[$key]['answernum'] = $this->answer->getAnswerNumByQid($attenquestion['quesid']);
				$attenQuesList[$key]['attentionnum'] = $this->attentionquestion->getAttentionNumByQid($attenquestion['quesid']);
			}

			$this->assign([
				'attenQuesList'=>$attenQuesList,
				'isfirst'=>$isfirst,
				'num'=>$num,
				'personid'=>$personid,
				'gender'=>$gender,
				]);
			return $this->fetch();
		}
	}
	public function getColumns()
	{
		return $this->fetch();
	}
	public function getFans(Request $request)
	{
		$uid = session('userid');
		$num = $request->param('num');
		$personid = $request->param('personid');
		$person = $this->user->get($personid);
		$gender = $person->userinfo->gender;
		$isfirst = $request->param('isfirst');
		$attenPersonList = $this->attentionperson->getAttentionFansByUid($num,$personid);
		if($attenPersonList == null){
			echo json_encode(0);
		}else{
			foreach($attenPersonList as $key => $attenperson)
			{	
				$attentionuser = $this->user->get($attenperson['userid']);
				$attenPersonList[$key]['attentionuser'] = $attentionuser;
				$attenPersonList[$key]['attentionuserinfo'] = $attentionuser->userinfo;
				$attenPersonList[$key]['attentionnum'] = $this->attentionperson->getNumByUid($attenperson['userid']);
				$attenPersonList[$key]['answernum'] = $this->answer->getNumByUid($attenperson['userid']);
				$attenPersonList[$key]['questionnum'] = $this->question->getNumByUid($attenperson['userid']);
				$attenPersonList[$key]['isatten'] = 0;
		        $atten = $this->attentionperson->checkattention($attenperson['userid'],$uid);
		        if($atten){
		            $attenPersonList[$key]['isatten'] = 1;
		        }
			}

			$this->assign([
				'attenPersonList'=>$attenPersonList,
				'isfirst'=>$isfirst,
				'sql'=>$this->question->getLastSql(),
				'num'=>$num,
				'personid'=>$personid,
				'gender'=>$gender,
				'userid'=>$uid,
				'isfans'=>1,
				]);
			return $this->fetch('user/getfollowing');
		}
	}
	public function getCollections()
	{

		return $this->fetch();
	}
	public function getFollowing(Request $request)
	{
		$uid = session('userid');
		$num = $request->param('num');
		$personid = $request->param('personid');
		$person = $this->user->get($personid);
		$gender = $person->userinfo->gender;
		$isfirst = $request->param('isfirst');
		$attenPersonList = $this->attentionperson->getAttentionPersonByUid($num,$personid);
		if($attenPersonList == null){
			echo json_encode(0);
		}else{
			foreach($attenPersonList as $key => $attenperson)
			{	
				$attentionuser = $this->user->get($attenperson['attentionuserid']);
				$attenPersonList[$key]['attentionuser'] = $attentionuser;
				$attenPersonList[$key]['attentionuserinfo'] = $attentionuser->userinfo;
				$attenPersonList[$key]['attentionnum'] = $this->attentionperson->getNumByUid($attenperson['attentionuserid']);
				$attenPersonList[$key]['answernum'] = $this->answer->getNumByUid($attenperson['attentionuserid']);
				$attenPersonList[$key]['questionnum'] = $this->question->getNumByUid($attenperson['attentionuserid']);
				$attenPersonList[$key]['isatten'] = 0;
		        $atten = $this->attentionperson->checkattention($attenperson['attentionuserid'],$uid);
		        if($atten){
		            $attenPersonList[$key]['isatten'] = 1;
		        }
			}

			$this->assign([
				'attenPersonList'=>$attenPersonList,
				'isfirst'=>$isfirst,
				'sql'=>$this->question->getLastSql(),
				'num'=>$num,
				'personid'=>$personid,
				'gender'=>$gender,
				'userid'=>$uid,
				'isfans'=>0,
				]);
			return $this->fetch();
		}
	}

	//修改头像
    public function setPicture()
    {

        $file = request()->file('image');
        $info = $file->validate(['size' => 2000000, 'ext' => 'jpg,png,gif'])
            ->move(ROOT_PATH . 'public' . DS . 'uploads');
        if ($info) {
            //输出文件名
            $imagename = $info->getFilename();
            //缩放
            $image = Image::open(request()->file('image'));
            $image->thumb(300, 300)->save('userpic\\' . $imagename);
            $imagePath = '\\userpic\\' . $imagename;
            //$rePath = "\userpic". $imagename;
            $uid = session('userid');
            $result = $this->userinfo->upPic($imagePath, $uid);
            if ($result){
                return $imagePath;
            }else{
                return '上传失败';
            }
        } else {
            //$errorInfo = ['code'=>0, 'info' => $file->getError()];
            return $file->getError();
        }
    }
}