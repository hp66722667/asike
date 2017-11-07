<?php
namespace app\index\controller;

use \think\Controller;
use \think\Request;
use app\index\model\User;
use app\index\model\Comment;
use app\index\model\Userinfo;
use app\index\model\Question;
use app\index\model\Privatemessage;
use app\index\model\Agreeoppose;
use app\index\model\Inviter;

class Index extends Controller
{
    private $appid;
    private $token; 
    private $return_uri;
    private $access_token;
    private $url = 'http://open.51094.com/user/auth.html';


	protected $user;
    protected $comment;
	protected $question;
    protected $agreeoppose;
    protected $inviter;
    protected $privatemessage;
	public function _initialize()
	{
        $this->appid = '159f40fa13961e';
        $this->token = 'a4c3399ee8c6f6e6505bbeb9b9027827';

		$this->user = new User();
        $this->comment = new Comment();
		$this->question = new Question();
        $this->agreeoppose = new Agreeoppose();
        $this->inviter = new Inviter();
        $this->privatemessage = new Privatemessage();
	}
    public function _empty($name)
    {
    //把所有城市的操作解析到city方法
    return $this->error("404");
    }

    function me( $code ){
        #$this->getAccessToken();
        $params=array(
                'type'=>'get_user_info',
                'code'=>$code,
                'appid'=>$this->appid,
                'token'=>$this->token
            );
        return $this->http( $params );
    }

    public function http( $postfields='', $method='POST', $headers=array()){
        $ci=curl_init();
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, FALSE); 
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ci, CURLOPT_TIMEOUT, 30);
        if($method=='POST'){
            curl_setopt($ci, CURLOPT_POST, TRUE);
            if($postfields!='')curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
        }
        $headers[]="User-Agent: 51094PHP(open.51094.com)";
        curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ci, CURLOPT_URL, $this->url);
        $response=curl_exec($ci);
        curl_close($ci);
        $json_r=array();
        if(!empty( $response ))$json_r=json_decode($response, true);
        return $json_r;
    }

	public function login()
	{
		return $this->fetch();
	}

	//首页
    public function index(Request $request)
    {

        $code = $request->param('code');
        if($code != null){
            $qq = $this->me($code);
            $uniq = $qq['uniq'];
            if($user = $this->user->getByUniq($uniq)){
                session('userid',$user['userid']);
            }else{               
                $this->user->nickname = 'qq_'.$qq['name'];
                $this->user->uniq = $qq['uniq'];
                if($this->user->save()){
                    $qquserid = $this->user->userid;
                    session('userid',$qquserid);
                    $this->user->userinfo()->save(['user_id'=>$qquserid,'photo'=>$qq['img'],'gender'=>$qq['sex']]);
                }
            }
            
        }

    	$uid      = session('userid');
        if($uid == null){
            $uid = 0;
            $user     = null;
            $userinfo = null;
        }else{
            $user     = $this->user->get($uid);
            $userinfo = $user->userinfo;
        }
    	
    	$this->assign([
            'userid'=>$uid,
    		'user'    =>$user,
    		'userinfo'=>$userinfo,
    		]);
        return $this->fetch();
    }
    public function logout()
    {
        session(null);
        $this->redirect("Index/login");
    }
    public function search(Request $request)
    {
        $uid      = session('userid');
        if($uid == null){
           return $this->error('请先登录','index/login');
        }
        $user     = $this->user->get($uid);
        $userinfo = $user->userinfo;
        $str = $request->param('str');
        //dump($str);
        $quesList = $this->question->getQuLiByStr($str);
        foreach($quesList as $key => $question){
            $hotanswer = $question->answer()->find();
            $quesList[$key]['hotanswer'] = $hotanswer;
            if($hotanswer == null){
                $quesList[$key]['person'] = 0;
                $quesList[$key]['personinfo'] = 0;
            }else{
                $quesList[$key]['person'] = $hotanswer->user;
                $quesList[$key]['personinfo'] = $hotanswer->user->userinfo;
            }
            
        }



        $this->assign([
            'user'    =>$user,
            'userinfo'=>$userinfo,
            'quesList' => $quesList,
            'str' => $str,
            ]);
        return $this->fetch();
    }

    public function getQuesByTid(Request $request)
    {
        $uid      = session('userid');
        if($uid == null){
           return $this->error('请先登录','Index/login');
        }
        $user     = $this->user->get($uid);
        $userinfo = $user->userinfo;
        $topic_id = $request->param('topicid');
        //dump($str);
        $quesList = $this->question->getQuLiByTid($topic_id);
        foreach($quesList as $key => $question){
            $hotanswer = $question->answer()->find();
            $quesList[$key]['hotanswer'] = $hotanswer;
            if($hotanswer == null){
                $quesList[$key]['person'] = 0;
                $quesList[$key]['personinfo'] = 0;
            }else{
                $quesList[$key]['person'] = $hotanswer->user;
                $quesList[$key]['personinfo'] = $hotanswer->user->userinfo;
            }
            
        }



        $this->assign([
            'user'    =>$user,
            'userinfo'=>$userinfo,
            'quesList' => $quesList,

            ]);
        return $this->fetch('Index/search');
    }
    public function getIndex(Request $request)
    {
        $num = $request->param('num');
        $quesList = $this->question->getQuesList($num);
        if($quesList == null){
            echo json_encode(0);
        }else{
            foreach($quesList as $key=>$question){//给quesList添加回答者的nickname
                $quesList[$key]['user'] = $question->user;
                $quesList[$key]['topic'] = $question->topic;
                $quesList[$key]['userinfo'] = $question->user->userinfo;
                

            }
            $this->assign([
                'quesList'=>$quesList,
                'sql'=>$this->question->getLastSql(),
                'num'=>$num,
                ]);
            return $this->fetch();
        }       
    }
    public function inbox()
    {
        $uid = session('userid');
        $user = $this->user->get($uid);
        $userinfo = $user->userinfo;
        $messages = $this->privatemessage->getPrivMessageByUid($uid);//未读私信
        if($messages){
            $page = $messages->render();
            foreach($messages as $key => $message){
                $sender = $this->user->get($message['senduserid']);
                $messages[$key]['sender'] = $this->user->get($message['senduserid']);
                $messages[$key]['senderinfo'] = $sender->userinfo;
                $messages[$key]['sender'] = $this->user->get($message['senduserid']);
            }
        }
        $this->assign(['messages'=>$messages,'user'=>$user,'userinfo'=>$userinfo,'page'=>$page]);
        return $this->fetch();
    }
    public function messageInfo(Request $request)
    {
        $uid = session('userid');
        $user = $this->user->get($uid);
        $userinfo = $user->userinfo;
        $senduserid = $request->param('senduserid');
        $senduser = $this->user->get($senduserid);
        $senduserinfo = $senduser->userinfo;
        $look = $this->privatemessage->lookedMessage($uid,$senduserid);
        $messages = $this->privatemessage->getPrivMessageBySid($senduserid,$uid); 
        if($messages){
            $page = $messages->render();
            foreach($messages as $key => $message){
                $sender = $this->user->get($message['senduserid']);
                $messages[$key]['sender'] = $this->user->get($message['senduserid']);
                $messages[$key]['senderinfo'] = $sender->userinfo;
                $messages[$key]['sender'] = $this->user->get($message['senduserid']);
            }
        }
        $this->assign(['messages'=>$messages,'user'=>$user,'userinfo'=>$userinfo,'senduser'=>$senduser,'senduserinfo'=>$senduserinfo,'page'=>$page]);
        return $this->fetch();
    }
    public function addPrivMessage(Request $request)
    {
        $takeuserid = $request->param('takeuserid');
        $senduserid = session('userid');
        $privmesscontent = $request->param('privmesscontent');
        dump($senduserid);
        $re = $this->privatemessage->addMessage($takeuserid,$senduserid,$privmesscontent);
        if($re){
            $this->redirect("Index/messageInfo",array('senduserid'=>$takeuserid));
        }else{
            $this->error('发送失败');
        }

    }

    public function checkMessage(Request $request)
    {
        $uid = session('userid');
        $agreemessages = $this->agreeoppose->notLook($uid);
        $privmessnum = $this->privatemessage->getPrivMessCount($uid);
        $commentmessages = $this->comment->notlook($uid);
        $invitemessages = $this->inviter->notlook($uid);
        if($commentmessages){
            foreach($commentmessages as $key=>$commentmessage){
                $senduser = $this->user->get($commentmessage['senduserid']);
                $quesid = $commentmessage['quesid'];
                $commentmessages[$key]['question'] = $this->question->get($quesid);
                $commentmessages[$key]['senduser'] = $senduser;
                $commentmessages[$key]['senduserinfo'] = $senduser->userinfo;
            }
        }
        
        if($agreemessages){
            foreach($agreemessages as $key=>$agreemessage){
                $agreerid = $agreemessage['agreerid'];
                $agreemessages[$key]['agreer'] = $this->user->get($agreerid);
                $quesid = $agreemessage['quesid'];
                $agreemessages[$key]['question'] = $this->question->get($quesid);
                
            }
            
        }

        if($invitemessages){
            foreach($invitemessages as $key=>$invitemessage){
                $user = $this->user->get($invitemessage['userid']);
                $quesid = $invitemessage['quesid'];
                $invitemessages[$key]['question'] = $this->question->get($quesid);
                $invitemessages[$key]['user'] = $user;
                $invitemessages[$key]['userinfo'] = $user->userinfo;
            }
        }
            
        // if($agreemessages && $commentmessages){
            $this->assign([
                    'agreemessages'=>$agreemessages,
                    'commentmessages'=>$commentmessages,
                    'invitemessages'=>$invitemessages,
                    ]);
            echo json_encode(['code'=>1,'privmessnum'=>$privmessnum,'agreeinfo'=>$this->fetch('getmessage')]);
        /*}else{
            echo json_encode(['code'=>0]);
        }*/
        
    }
    public function upagreeMessage(Request $request)
    {
        $bid = $request->param('bid');
        $agree = $this->agreeoppose->get($bid);
        $agree->islook = 1;
        if($agree->save()){
            return 1;
        }else{
            return 0;
        }
    }
    public function upcommentMessage(Request $request)
    {
        $commentid = $request->param('commentid');
        $comment = $this->comment->get($commentid);
        $comment->islook = 1;
        if($comment->save()){
            return 1;
        }else{
            return 0;
        }
    }
    public function upinviteMessage(Request $request)
    {
        $bid = $request->param('bid');
        $inviter = $this->inviter->get($bid);
        $inviter->islook = 1;
        if($inviter->save()){
            return 1;
        }else{
            return 0;
        }
    }
	public function addUser(Request $request)
	{
		//$ch = $this->user->checkUser()
		//dump($request->param());
		//法一:
		$re = $this->user->add($request->param());		
		//法二:
		//$this->user->data($data);
		// $data['nickname'] = $request->param('nickname');
		// $data['phone'] = $request->param('phone');
		// $data['password'] = $request->param('password');
		//$re = $this->user->save($data);
		if($re){
			$this->user->userinfo()->save(['user_id'=>$re]);
			
			$this->redirect("Index/index");
		}else{
			$this->error('注册失败');
		}

	}
	public function checkLogin(Request $request)
	{
		//dump($request->param());
		$re = $this->user->login($request->param());
		if($re){
			$this->redirect('Index/index');
		}else{
			$this->error('登录失败');
		}
	}
	public function checkNickName(Request $request)
	{
		$re = $this->user->checkname($request->param('nickname'));
	}
	public function checkPhone(Request $request)
	{
		$re = $this->user->checkPhone($request->param('phone'));
	}
	public function sendYzm(Request $request)
	{
		$re = $this->user->sendYzm($request->param('phone'));
	}
	public function checkYzm(Request $request)
	{
		$re = $this->user->checkYzm($request->param('yzm'));
	}

	//发现
    public function explore()
    {
        $pushQuestion = $this->question->pushQueSec();
        $firstquName  = $pushQuestion[0]['quesname'];
        $firstid      = $pushQuestion[0]['quesid'];
        $resultQ      = $this->queAnswUser($firstid);
        $hotQuestion  = $this->question->hotQueSec();
        foreach ($hotQuestion as $k => $v){
            $qid = $v->quesid;
            $hotQuestion[$k]['resultQ'] = $this->queAnswUser($qid);
        }




        $uid      = session('userid');
        if($uid == null){
           return $this->error('请先登录','Index/login');
        }
    	$user     = $this->user->get($uid);
    	$userinfo = $user->userinfo;


        $this->assign([
        	'firstid'      => $firstid,
            'pushQuestion' => $pushQuestion,
            'firstAnswer'  => $resultQ['firstAnswer'],
            'firstquName'  => $firstquName,
            'answerName'   => $resultQ['answerName'],
            'authgrapg'    => $resultQ['authgrapg'],
            'photo'        => $resultQ['photo'],
            'firstUserid'  => $resultQ['firstUserid'],
            'hotQuestion'  => $hotQuestion,
            'user'		   => $user,
    		'userinfo'     => $userinfo,
        ]);
        return $this->fetch();
    }

    public function queAnswUser($qid)
    {
        $array = [];
        $queAns = $this->question->get($qid);
        $ans = $queAns->answer; //一对多查询回答
        //获取第一条回答内容与回答者id
        if ($ans){
            $firstAnswer = $ans[0]->answercontent;
            $firstUserid = $ans[0]->userid;
            $array['firstAnswer'] = $firstAnswer;
            //通过回答者id查询回答者信息
            $userInfo    = $this->user->get($firstUserid);
            $answerName  = $userInfo->nickname;
            $array['answerName'] = $answerName;

            //一对一查询回答者基本信息(一句话介绍自己 、头像等信息)
            $authgrapg   = $userInfo->userinfo->autograph;
            $photo       = $userInfo->userinfo->photo;

            $array['firstUserid'] = $firstUserid;
            $array['authgrapg'] = $authgrapg;
            $array['photo'] = $photo;
            return $array;
        }else{ 
        	$array['firstUserid'] = '';
            $array['firstAnswer'] = '';
            $array['answerName']  = '';
            $array['authgrapg']   = '';
            $array['photo']       = '';
            return $array;
        }
    }

    public function checkShouji(Request $request)
    {
        $appkey = '0c8dc693c21e6541';//你的appkey
        $shouji = $request->param('phonenum');
        $url = "http://api.jisuapi.com/shouji/query?appkey=$appkey&shouji=$shouji";
        $result = $this->curlOpen($url);
         
        $jsonarr = json_decode($result, true);
        //exit(var_dump($jsonarr));
         
        if($jsonarr['status'] != 0)
        {
            echo json_encode(['code'=>0,'info'=>$jsonarr['msg']]);
            
        }else{

         
        $result = $jsonarr['result'];
        $re =  $result['province'].' '.$result['city'].' '.$result['company'].' '.$result['cardtype'];
        echo json_encode(['code'=>1,'info'=>$re]);
        
        }
    }

    public function joke(Request $request)
    {
        $appkey = '0c8dc693c21e6541';//你的appkey
        $pagenum = 1;
        $pagesize = 1;
        $sort = 'rand';//addtime/rand
        $url = "http://api.jisuapi.com/xiaohua/text?pagenum=$pagenum&pagesize=$pagesize&sort=$sort&appkey=$appkey";
        $result = $this->curlOpen($url);
        $jsonarr = json_decode($result, true);
        //exit(var_dump($jsonarr));
        if($jsonarr['status'] != 0)
        {
            echo json_encode(['code'=>0,'info'=>$jsonarr['msg']]);
        }else{
            $result = $jsonarr['result'];
            //echo $result['total'].' '.$result['pagesize'].' '.$result['pagenum'].'<br>';
            foreach($result['list'] as $val)
            {
                echo json_encode(['code'=>1,'info'=>$val['content']]);
            }
        }
        
    }

    public function talk(Request $request)
    {
        $appkey = '0c8dc693c21e6541';//你的appkey
        $question = $request->param('question');//问题(utf8)

        $url = "http://api.jisuapi.com/iqa/query?appkey=$appkey&question=$question";
         
        $result = $this->curlOpen($url);
        $jsonarr = json_decode($result, true);
        //var_dump($jsonarr);
        if($jsonarr['status'] != 0)
        {
            echo json_encode(['code'=>0,'info'=>$jsonarr['msg']]);
        }else{
            $result = $jsonarr['result'];
            //echo $result['type'].' '.$result['content'] . '<br>'
            echo json_encode(['code'=>1,'info'=>$result['content']]) ;
            
        }
                                                  
    }

    public function curlOpen($url, $config = array())
    {
        $arr = array('post' => false,'referer' => $url,'cookie' => '', 'useragent' => 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0; SLCC1; .NET CLR 2.0.50727; .NET CLR 3.0.04506; customie8)', 'timeout' => 20, 'return' => true, 'proxy' => '', 'userpwd' => '', 'nobody' => false,'header'=>array(),'gzip'=>true,'ssl'=>false,'isupfile'=>false);
        $arr = array_merge($arr, $config);
        $ch = curl_init();
         
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, $arr['return']);
        curl_setopt($ch, CURLOPT_NOBODY, $arr['nobody']); 
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, $arr['useragent']);
        curl_setopt($ch, CURLOPT_REFERER, $arr['referer']);
        curl_setopt($ch, CURLOPT_TIMEOUT, $arr['timeout']);
        //curl_setopt($ch, CURLOPT_HEADER, true);//获取header
        if($arr['gzip']) curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
        if($arr['ssl'])
        {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        if(!empty($arr['cookie']))
        {
            curl_setopt($ch, CURLOPT_COOKIEJAR, $arr['cookie']);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $arr['cookie']);
        }
         
        if(!empty($arr['proxy']))
        {
            //curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP); 
            curl_setopt ($ch, CURLOPT_PROXY, $arr['proxy']);
            if(!empty($arr['userpwd']))
            {           
                curl_setopt($ch,CURLOPT_PROXYUSERPWD,$arr['userpwd']);
            }       
        }   
         
        //ip比较特殊，用键值表示
        if(!empty($arr['header']['ip']))
        {
            array_push($arr['header'],'X-FORWARDED-FOR:'.$arr['header']['ip'],'CLIENT-IP:'.$arr['header']['ip']);
            unset($arr['header']['ip']);
        }  
        $arr['header'] = array_filter($arr['header']);
         
        if(!empty($arr['header']))
        {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $arr['header']);
        }
     
        if ($arr['post'] != false)
        {
            curl_setopt($ch, CURLOPT_POST, true);
            if(is_array($arr['post']) && $arr['isupfile'] === false)
            {
                $post = http_build_query($arr['post']);           
            }
            else
            {
                $post = $arr['post'];
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }   
        $result = curl_exec($ch);
        //var_dump(curl_getinfo($ch));
        curl_close($ch);
     
        return $result;
    }
}