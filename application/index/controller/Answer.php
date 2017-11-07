<?php
namespace app\index\controller;

use \think\Controller;
use \think\Request;
use app\index\model\Agreeoppose;
use app\index\model\Comment;
use app\index\model\Question;
use app\index\model\User;

use app\index\model\Answer as AnswerModel;

class Answer extends Auth
{
	protected $is_login = ['*'];
	protected $answer;
	protected $comment;
	protected $user;
	protected $question;
	protected $agreeoppose;

	public function _initialize()
	{
		parent::_initialize();
		$this->answer = new AnswerModel;
		$this->comment = new Comment;
		$this->user = new User;
		$this->agreeoppose = new Agreeoppose;
		$this->question = new Question;
	}
	public function quesDetail()
	{
		$uid = session('userid');
		$this->assign('userid',$uid);
		return $this->fetch();
	}
	public function addAnswer(Request $request)
	{
		$quesid = $request->param('quesid');
		$re = $this->answer->addAnswer($request->param());
		if($re){
			$question = $this->question->get($quesid);
        	$question->setInc('browsenum','-1');
			$this->redirect("Question/quesDetailByQid",array('quesid'=>$quesid));
		}else{
			$this->error("发表失败");
		}
	}
	public function addreport(Request $request)
	{
		$reason = $request->param('reason');
		$answerid = $request->param('answerid');
		$answer = $this->answer->get($answerid);
		$answer->isreport = 1;
		$answer->reason = $answer['reason'].' '.trim($reason);
		$answer->setInc('reportnum');
		if($answer->save()){
			echo json_encode(['code'=>1]);
		}else{
			echo json_encode(['code'=>0]);
		}
	}
	public function updateAgree(Request $request)
	{
		$quesid = $request->param('quesid');
		$userid = $request->param('userid');
		$answerid = $request->param('answerid');
		$isagree = ($request->param('isagree')+1)%2;

		$agreerid = session('userid');
		$re = $this->agreeoppose->check($agreerid,$answerid);
		if($re == 0){
			$res = $this->agreeoppose->add($userid,$agreerid,$answerid,$quesid,$isagree);
			if($res){
				$result = $this->answer->changeAgreeNum($answerid,$isagree);
				echo json_encode(['code'=>1,'aggrenum'=>$result,'isagree'=>$isagree]);//aggrenum:赞的总数;isagree:点完后的是否点赞,0没点|1点了
			}else{
				echo json_encode(['code'=>0]);
			}
		}else{
			$res = $this->agreeoppose->updateagg($re,$isagree);
			if($res){
				$result = $this->answer->changeAgreeNum($answerid,$isagree);
				echo json_encode(['code'=>1,'aggrenum'=>$result,'isagree'=>$isagree]);
			}else{
				echo json_encode(['code'=>0]);
			}

		}

	}
	public function addComment(Request $request)
	{
		$uid = session('userid');
		$senduserid = $uid;
		$quesid = $request->param('quesid');
		$answerid = $request->param('answerid');
		$commentcontent = $request->param('commentcontent');
		$takeuserid = $request->param('takeuserid');
		if($takeuserid == null){
			$takeuserid = 0;
		}
		$re = $this->comment->addCommentByUid($quesid,$answerid,$senduserid,$takeuserid,$commentcontent);
		if($re){
			$answer = $this->answer->get($answerid);
			$answer->setInc('commentnum');
			$answer->save();
			$this->redirect("Answer/getComment",array('quesid'=>$quesid,'answerid'=>$answerid));
		}else{
			echo json_encode(['code'=>0]);
		}
	}
	public function getComment(Request $request)
	{
		$uid = session('userid');
		$quesid = $request->param('quesid');
		$answerid = $request->param('answerid');
		$page = $request->param('page');
		if($page == null){
			$page = 1;
		}

		$count = $this->comment->getCount($answerid);
		$pagenum = ceil($count/2);
		$prevpage = ($page-1>0)?($page-1):1;
		$nextpage = ($page+1<$pagenum)?($page+1):$pagenum;

		$comments = $this->comment->getCommentByAid($answerid,$page);
		if($comments){
			foreach($comments as $key=>$comment)
			{
				$takeuserid = $comment['takeuserid'];
				$comment['takeuserid'] = $takeuserid;
				if($takeuserid != 0){
					$takeuser = $this->user->get($comment['takeuserid']);
					$comments[$key]['takeuser'] = $takeuser;
					$comments[$key]['takeuserinfo'] = $takeuser->userinfo;
				}
				
				$senduser = $this->user->get($comment['senduserid']);				
				$comments[$key]['senduser'] = $senduser;				
				$comments[$key]['senduserinfo'] = $senduser->userinfo;
			}
			$this->assign([
				'comments'=>$comments,
				'count'=>$count,
				'pagenum'=>$pagenum,
				'prevpage'=>$prevpage,
				'nextpage'=>$nextpage,
				'page'=>$page,
				'answerid'=>$answerid,
				'quesid'=>$quesid,
				]);
			echo json_encode(['code'=>1,'commentinfo'=>$this->fetch('answer/getcomment')]);
		}else{
			// echo json_encode(['code'=>0]);
			$this->assign([
				'comments'=>$comments,
				'count'=>0,
				'pagenum'=>1,
				'prevpage'=>1,
				'nextpage'=>1,
				'page'=>1,
				'answerid'=>$answerid,
				'quesid'=>$quesid,
				]);
			echo json_encode(['code'=>1,'commentinfo'=>$this->fetch('answer/getcomment')]);
		}

	}
}