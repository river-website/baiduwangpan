<?php namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;

class share_userController extends BaseController {

    /**
     * Show the profile for the given user.
     *
     * @param  int  $userID
     * @param  int  $condition
     * @return Response
     */
    public function index($userID=null,$condition=null){
        $limit = 20;
        $condition = explode('-', $condition);
        if(count($condition)!=4){$this->reHome();return;}
        $typeName = urldecode($condition[0]);
        $suffix = $condition[1];
        $word 	= urldecode($condition[2]);
        $page 	= $condition[3];
        if(empty($typeName)||empty($suffix)||empty($word)||!is_numeric($page) || $page<=0){$this->reHome();return;}

        if(empty($userID)||!is_numeric($userID)){$this->reHome();return;}
        $userInfo = $this->getRedisCache("share_user:$userID",function ($userID){
            // 查询数据库
            // 返回数据
            return;
        });
        if(empty($userInfo)){$this->reHome();return;}

        $typesList = $this->getRedisCache('typesList');
        $suffixList = ezServer()->getCache('suffixList');
        $share_file = $this->getModel('share_file');
        if($typeName !='ALL'){
            if(empty($typesList[$typeName])){$this->reHome();return;}
            $suffixs = $typesList[$typeName];
            if($suffix != 'ALL' && empty($suffixs[$suffix])){$this->reHome();return;}
        }else if($suffix != 'ALL') {
            if (empty($suffixList[$suffix])) {$this->reHome();return;}
        }
        if(!empty($suffixs))
            $share_file->where_in('suffix',array_keys($suffixs));
        if($suffix != 'ALL')
            $share_file->where(array("suffix=$suffix"));
        if($word != 'ALL'){
            $insertdata['searchWord'] = $word;
            $insertdata['date'] = date('Ymd',time());
            $hotSearch = $this->getModel('hotSearch');
            $hotSearch->insert($insertdata);
            $share_file->where(array('match(fileName) against("'.$word.'")'));
        }
        $sql = $share_file->where(array("uk=".$userInfo['uk']))->sql;
        $userFiles = $share_file->limit($limit,($page-1)*$limit)->select(array('id','fileName','suffix','size','shareTime'));
        $userInfo['count']=0;
        $condition['type'] = $condition[0];
        $condition['suffix'] = $condition[1];
        $condition['searchWord'] = $condition[2];
        $condition['page'] = $condition[3];
        if(count($userFiles)>0) {
            foreach ($userFiles as &$vaule) {
                $vaule['typeName'] = empty($suffixList[$vaule['suffix']]) ? '未知' : $suffixList[$vaule['suffix']];
                $vaule['fileUrl'] = $this->toUserUrl($vaule,$condition);
            }
            $share_file->sql = $sql;
            $count = $share_file->select('count(id) as count');
            if(empty($count) || count($count)!=1)
                $userInfo['count'] = 0;
            else $userInfo['count'] = $count[0]['count'];
        }
        $data['userID'] = $userID;
        $data['date'] = date('Ymd',time());
        $hotUser = $this->getModel('hotUser');
        $hotUser->insert($data);

        $pages = $this->pages($userInfo['count'],$limit,$page);
        if($pages['pre']){$condition['page']=$pages['pre']; $pages['pre'] = $this->toUserUrl($userInfo,$condition);}
        if($pages['next']){$condition['page']=$pages['next']; $pages['next'] = $this->toUserUrl($userInfo,$condition);}
        if($pages['first']){$condition['page']=$pages['first'];$pages['first'] = array('page'=>$pages['first'],'url'=>$this->toUserUrl($userInfo,$condition));}
        if($pages['last']){$condition['page']=$pages['last'];$pages['last'] = array('page'=>$pages['last'],'url'=>$this->toUserUrl($userInfo,$condition));}
        foreach ($pages['cur'] as &$value) {
            $condition['page']=$value;
            $value = array('page' => $value, 'active' => ($value == $page) ? true : false, 'url' => $this->toUserUrl($userInfo,$condition));
        }

        $this->assign('pages',$pages);
        $this->assign('userInfo',$userInfo);
        $this->assign('userFiles',$userFiles);
        $this->display('share_user');
    }

}