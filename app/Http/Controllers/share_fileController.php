<?php namespace App\Http\Controllers;
use App\model\hotfile;
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


        $preFile = $share_file
            ->where('id','<',$fileID)
            ->orderByDesc('id')
            ->limit(1)
            ->get('id,fileName');
        if(empty($preFile)||count($preFile) == 0)$preFile = array('id'=>null,'fileName'=>null,'fileUrl'=>null);
        else{
            $preFile['fileUrl'] = $this->toFileUrl($preFile);
        }
        $fileInfo['pre'] = $preFile;
        $nextFile = $share_file
            ->where('id','>',$fileID)
            ->order('id')
            ->limit(1)
            ->get('id,fileName');
        if(empty($nextFile)||count($nextFile) == 0)$nextFile = array('id'=>null,'fileName'=>null,'fileUrl'=>null);
        else{
            $nextFile['fileUrl'] = $this->toFileUrl($nextFile);
        }
        $fileInfo['next'] = $nextFile;

//        $data['date'] = date('Ymd',time());
//        $data['fileID'] = $fileID;
//        $hotFile = new hotfile();
//        $hotFile->insert($data);
        output_json(success($fileInfo));
    }
    public function search(){
        // 参数获取
        $params = $_GET;
        // 参数过滤
        $ret = filter($params,[],['typeName','suffix','fileName','page','uk']);
        if($ret['ret'] != 0)output_json($ret);
        // 初始化参数
        if(empty($params['page'])) $params['page'] = 1;
        // 业务逻辑
        if(!empty($params['typeName'])){
            $typeList = $this->getTypeToSuffix();
            if(empty($typeList[$params['typeName']]))output_json(error());
            if(!empty($params['suffix'])){
                if(!in_array($params['suffix'],$typeList[$params['typeName']]))output_json(error());
                $params['suffix'] = [$params['suffix']];
            }else
                $params['suffix'] = $typeList[$params['typeName']];
        }
        $share_file = new share_file();
        $files = $share_file->search($params)->toArray();
        $suffixList = $this->getSuffixToType();
        foreach ($files['data'] as &$vaule) {
            $vaule['typeName'] = empty($suffixList[$vaule['suffix']]) ? '未知' : $suffixList[$vaule['suffix']];
            $vaule['fileUrl'] = $this->toFileUrl($vaule);
        }
        output_json(success($files));
    }

}