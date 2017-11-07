<?php
namespace app\admin\controller;

use app\admin\model\Topic as TopicModel;
use app\admin\model\Topicclass;
use think\Request;
use think\Image;

class Topic extends Auth
{
    protected $is_login = ['*'];
    protected $topic;
    protected $topicClass;
    public function _initialize()
    {
        parent::_initialize();
        $this->topic = new TopicModel();
        $this->topicClass = new Topicclass();
    }

    //话题列表
    public function topicList(Request $request)
    {
        $topicStr = $request->param('str');
        $topicInfo = $this->topic->selectTopic($topicStr);
        foreach ($topicInfo as $key=>$value){
            $topicid = $value->topicid;
            $topicInfo[$key]['topicclass'] = $this->topicInfo($topicid);
        }
        $page = $topicInfo->render();
        $this->assign([
            'page' => $page,
            'topcInfo' => $topicInfo
        ]);
        return $this->fetch();
    }

    //一对多相对查询话题所属类别
    public function topicInfo($topicid)
    {
        $topicInfo = $this->topic->get($topicid);
        $topicClass = $topicInfo->topicclass->topicclassname;
        return $topicClass;
    }

    //添加话题
    public function addTopic()
    {
        //查询话题类别表
        $tpClassInfo = $this->topicClass->selectTopicClass();
        $this->assign([
           'tpclassInfo' => $tpClassInfo
        ]);
        return $this->fetch();
    }

    public function insertTopic(Request $request)
    {
        $topicname = $request->param('topicname');
        $topicClass = $request->param('topicclaass');
        $topicDescribe = $request->param('topicdescribe');

        //上传话题logo
        $file = request()->file('image');
        if (empty($file)){
            $topicPhoto = '';
        }else{
            $info = $file->validate(['size' => 2000000, 'ext' => 'jpg,png,gif'])
                ->move(ROOT_PATH . 'public' . DS . 'topiclogoup');
            if ($info) {
                //输出文件名
                $imagename = $info->getFilename();
                //缩放
            $image = Image::open(request()->file('image'));
//                $image = Image::open($file);
                $image->thumb(75, 75)->save('topiclogo\\' . $imagename);
                $imagePath = '\\topiclogo\\' . $imagename;
                $topicPhoto = $imagePath;

                /*$uid = session('userid');
                $result = $this->userinfo->upPic($imagePath, $uid);
                if ($result){
                    return $imagePath;
                }else{
                    return '上传失败';
                }*/
            } else {
                //$errorInfo = ['code'=>0, 'info' => $file->getError()];
                return $file->getError();
            }
        }


        $topicData = [
            'topicclass_id' => $topicClass,
            'topicname'     => $topicname,
            'topicdescribe' => $topicDescribe,
            'photo'         => $topicPhoto
        ];
        $result = $this->topic->insTopic($topicData);
        if ($result){
            $this->success('添加话题成功','topicList');
        }
    }

    public function isInList(Request $request)
    {
        $topicname = $request->param('topicname');
        $isinList = $this->topic->isInTopic($topicname);
        if ($isinList){
            $info = ['code'=>1, 'info'=>'该话题已经存在！'];
            echo json_encode($info);
        }else{
            $info = ['code'=>0, 'info'=>'该话题可以添加'];
            echo json_encode($info);
        }
    }

    public function isInClasslist(Request $request)
    {
        $tcname = $request->param('tcname');
        $isinList = $this->topicClass->isInTopicclass($tcname);
        if ($isinList){
            $this->error('该类别已经存在','addTopic');
            /*$info = ['code'=>1, 'info'=>'该类别已经存在！'];
            echo json_encode($info);*/
        }else{
            $tpoicClassname = $tcname;
            /*$info = ['code'=>3, 'info'=>'该类别可以添加'];
            echo json_encode($info);*/
        }
        $topicClassdes = $request->param('tcdescribe');

        $tcData=[
            'topicclassname' => $tpoicClassname,
            'topicclassdescribe' => $topicClassdes
        ];

        $result = $this->topicClass->addTopicClass($tcData);
        if ($result){
            $this->success('类别添加成功', 'addTopic');
        }else{
            $this->error('类别添加失败', 'addTopic');
        }
    }

}