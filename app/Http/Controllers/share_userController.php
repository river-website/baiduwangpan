<?php namespace App\Http\Controllers;

use App\model\hotuser;
use App\model\share_file;
use App\model\share_user;

class share_userController extends baseController {

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
        $ret = filter($params,array('id'));
        if($ret['ret'] != 0)output_json($ret);
        // 初始化参数
        $userID = $params['id'];
        // 业务逻辑
        $userInfo = $this->getUserByID($userID);
        $data['userID'] = $userID;
        $data['date'] = date('Ymd',time());
        $hotUser = new hotuser();
        $hotUser->insert($data);

        output_json(success($userInfo));
    }

    public function files(){
        // 参数获取
        $params = $_GET;
        // 参数过滤
        $ret = filter($params,array('id','page'));
        if($ret['ret'] != 0)output_json($ret);
        // 初始化参数
        $limit = 20;
        $userID = $params['id'];
        $page = $params['page'];

        $share_file = new share_file();
        $userFiles = $share_file->limit($limit,($page-1)*$limit)->select(['id','fileName','suffix','size','shareTime']);

        if(count($userFiles)>0) {
            $suffixList = $this->getSuffixToType();
            foreach ($userFiles as &$vaule) {
                $vaule['typeName'] = empty($suffixList[$vaule['suffix']]) ? '未知' : $suffixList[$vaule['suffix']];
                $vaule['fileUrl'] = $this->toFileUrl($vaule);
            }
        }
        output_json(success($userFiles));
    }
}