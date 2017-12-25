<?php namespace App\Http\Controllers;

use App\hotuser;
use App\share_file;
use App\share_user;

class share_userController extends BaseController {

    /**
     * Show the profile for the given user.
     *
     * @param  int  $userID
     * @param  int  $condition
     * @return Response
     */
    public function get(){
        // 参数获取
        $params = $_GET;
        // 参数过滤
        $ret = filter($params,array('id','page'));
        if($ret['ret'] != 0)output_json($ret);
        // 初始话参数
        $limit = 20;
        $userID = $params['id'];
        $page = $params['page'];
        // 业务逻辑
        $userInfo = $this->getRedisCache("share_user:$userID",function ()use($userID){
            $share_user = new share_user();
            return $share_user->find($userID);
        });
        if(empty($userInfo))output_json(error());

        $typesList = $this->getRedisCache('typeToSuffix');
        $suffixList = $this->getRedisCache('suffixToType');
        $share_file = new share_file();
        $userFiles = $share_file->limit($limit,($page-1)*$limit)->select(['id','fileName','suffix','size','shareTime']);
        $userInfo['count']=0;

        if(count($userFiles)>0) {
            foreach ($userFiles as &$vaule) {
                $vaule['typeName'] = empty($suffixList[$vaule['suffix']]) ? '未知' : $suffixList[$vaule['suffix']];
                $vaule['fileUrl'] = $this->toFileUrl($vaule);
            }
            $userInfo['count'] = $share_file->count();
        }
        $data['userID'] = $userID;
        $data['date'] = date('Ymd',time());
        $hotUser = new hotuser();
        $hotUser->insert($data);

        output_json(success(array('userfiles'=>$userFiles,'userinfo'=>$userInfo)));
//        $pages = $this->pages($userInfo['count'],$limit,$page);
//        if($pages['pre']){$condition['page']=$pages['pre']; $pages['pre'] = $this->toUserUrl($userInfo,$condition);}
//        if($pages['next']){$condition['page']=$pages['next']; $pages['next'] = $this->toUserUrl($userInfo,$condition);}
//        if($pages['first']){$condition['page']=$pages['first'];$pages['first'] = array('page'=>$pages['first'],'url'=>$this->toUserUrl($userInfo,$condition));}
//        if($pages['last']){$condition['page']=$pages['last'];$pages['last'] = array('page'=>$pages['last'],'url'=>$this->toUserUrl($userInfo,$condition));}
//        foreach ($pages['cur'] as &$value) {
//            $condition['page']=$value;
//            $value = array('page' => $value, 'active' => ($value == $page) ? true : false, 'url' => $this->toUserUrl($userInfo,$condition));
//        }

//        $this->assign('pages',$pages);
//        $this->assign('userInfo',$userInfo);
//        $this->assign('userFiles',$userFiles);
//        $this->display('share_user');
    }

}