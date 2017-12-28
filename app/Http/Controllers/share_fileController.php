<?php namespace App\Http\Controllers;
use App\model\hotfile;
use App\model\hotsearch;
use App\model\share_file;

/**
 * Created by PhpStorm.
 * User: win10
 * Date: 2017/12/20
 * Time: 18:03
 */


class share_fileController extends baseController{
    public function get(){
        // 参数获取
        $params = $_GET;
        // 参数过滤
        $ret = filter($params,array('id'));
        if($ret['ret'] != 0)output_json($ret);
        // 初始化参数
        $fileID = $params['id'];
        // 业务逻辑
        $share_file = new share_file();
        $fileInfo = $share_file->find($fileID)->toArray();
        if(empty($fileInfo) || count($fileInfo) == 0)output_json(error());
        $fileInfo['fileUrl'] = $this->toFileUrl($fileInfo);
        $suffixList = $this->getSuffixToType();
        $fileInfo['typeName'] = isset($suffixList[$fileInfo['suffix']])?$suffixList[$fileInfo['suffix']]:'未知';


        $preFile = $share_file->getPreByID($fileID,['id','fileName']);
        if(empty($preFile)||count($preFile) == 0)$preFile = array('id'=>null,'fileName'=>null,'fileUrl'=>null);
        else{
            $preFile['fileUrl'] = $this->toFileUrl($preFile);
        }
        $fileInfo['pre'] = $preFile;
        $nextFile = $share_file->getNextByID($fileID,['id','fileName']);
        if(empty($nextFile)||count($nextFile) == 0)$nextFile = array('id'=>null,'fileName'=>null,'fileUrl'=>null);
        else{
            $nextFile['fileUrl'] = $this->toFileUrl($nextFile);
        }
        $fileInfo['next'] = $nextFile;

        $data['date'] = date('Ymd',time());
        $data['fileID'] = $fileID;
        $hotFile = new hotfile();
        $hotFile->insert($data);
        output_json(success($fileInfo));
    }
    public function search(){
        // 参数获取
        $params = $_GET;
        // 参数过滤
        $ret = filter($params,[],['typeName','suffix','fileName','page','uk','limit','orderBy','page','orderField']);
        if($ret['ret'] != 0)output_json($ret);
        // 初始化参数

        // 业务逻辑
        if(!empty($params['suffix'])) $params['suffix'] = [$params['suffix']];
        elseif (!empty($params['typeName'])){
            $typeList = $this->getTypeToSuffix();
            if(empty($typeList[$params['typeName']]))output_json(success());
            $params['suffix'] = $typeList[$params['typeName']];
        }
        $share_file = new share_file();
        $files = $share_file->search($params);
        $suffixList = $this->getSuffixToType();
        foreach ($files['data'] as &$vaule) {
            $vaule['typeName'] = empty($suffixList[$vaule['suffix']]) ? '未知' : $suffixList[$vaule['suffix']];
            $vaule['fileUrl'] = $this->toFileUrl($vaule);
        }
        if(count($files)>0 && !empty($params['fileName'])){
            $data['date'] = date('Ymd',time());
            $data['searchWord'] = $params['fileName'];
            $hotSearch = new hotsearch();
            $hotSearch->insert($data);
        }
        output_json(success($files));
    }
}