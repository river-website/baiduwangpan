<?php namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;

use App\model\hotfile;
use App\model\hotsearch;
use App\model\hotuser;
use App\model\share_user;
use App\model\share_file;
use App\model\suffix;
use App\model\website;

class baseController extends Controller{
    private $webSiteInfo = null;
    private $suffixToType = null;
    private $typeToSuffix = null;
    private $hotSearchList = null;
    private $hotFileList = null;
    private $hotUserList = null;

    protected function getWebSiteInfo(){
        // 网站基本信息
        if(empty($this->webSiteInfo)) {
            $this->webSiteInfo = $this->getRedisCache('webSiteInfo', function () {
                $webSite = new website();
                $webSiteInfo = $webSite->find(1)->toArray();
                $share_file = new share_file();
                $webSiteInfo['fileCount'] = $share_file->count();
                $webSiteInfo['fileNewCount'] = rand(10000, 1000000);
                $share_user = new share_user();
                $webSiteInfo['userCount'] = $share_user->count();
                $webSiteInfo['userNewCount'] = rand(100, 10000);
                return $webSiteInfo;
            });
        }
        return $this->webSiteInfo;
    }

    protected function getSuffixToType(){
        if(empty($this->suffixToType)){
            // 后缀信息,类型信息
            $this->suffixToType = $this->getRedisCache('suffixToType',function (){
                $suffix = new suffix();
                $suffixData = $suffix->getSuffixTpesDict()->toArray();
                return array_reduce($suffixData,function($ret,$value){
                    $ret[$value['suffix']] = $value['typeName'];
                    return $ret;
                });
            });
        }
        return $this->suffixToType;
    }
    protected function getTypeToSuffix(){
        if(empty($this->typeToSuffix)){
            // 后缀信息,类型信息
            $this->typeToSuffix = $this->getRedisCache('typeToSuffix',function (){
                $suffix = new suffix();
                $suffixData = $suffix->getSuffixTpesDict()->toArray();
                return array_reduce($suffixData,function($ret,$value){
                    $ret[$value['suffix']] = $value['typeName'];
                    return $ret;
                });
            });
        }
        return $this->typeToSuffix;
    }
    protected function getHotFile(){
        if(empty($this->hotFileList)) {
            // 热门文件
            $this->hotFileList = $this->getRedisCache('hotFileList', function () {
                $hotFile = new hotfile();
                $hotFileList = $hotFile->getDateHot(date('Ymd', time()), 20)->toArray();
                return array_map(function ($file) {
                    $file['fileUrl'] = $this->toFileUrl($file);
                    return $file;
                }, $hotFileList);
            });
        }
        return $this->hotFileList;
    }
    protected function getHotUser(){
        if(empty($this->hotUserList)){
            // 热门用户
            $this->hotUserList = $this->getRedisCache('hotUserList',function (){
                $hotUser = new hotuser();
                $hotUserList = $hotUser->getDateHot( date('Ymd',time()),20)->toArray();
                return array_map(function($user){
                    $user['userUrl'] = $this->toUserUrl($user);
                    return $user;
                },$hotUserList);
            });
        }
        return $this->hotUserList;
    }
    protected function getHotSearch(){
        if(empty($this->hotSearchList)){
            // 热门搜索
            $this->hotSearchList = $this->getRedisCache('hotSearchList',function (){
                $hotSearch = new hotsearch();
                $hotSearchList = $hotSearch->getDateHot( date('Ymd',time()),20)->toArray();
                return array_map(function($search){
                    $search['searchUrl'] = $this->toSearchUrl($search);
                    return $search;
                },$hotSearchList);
            });
        }
        return $this->hotSearchList;
    }
    protected function getUserByID($id){
        // 热门搜索
        return $this->getRedisCache("share_user:$id",function ()use($id){
            $share_user = new share_user();
            $ret = $share_user->find($id)->toArray();
            if(!empty($ret['uk'])){
                $share_file = new share_file();
                $ret['fileCount'] = $share_file->where('uk',$ret['uk'])->count();
            }
            return $ret;
        });
    }
    protected function toFileUrl($file){
        $webSiteInfo = $this->getWebSiteInfo();
        return $webSiteInfo['webSite'].str_replace('$id',$file['id'],$webSiteInfo['fileSite']);
    }
    protected function toUserUrl($user,$condition = null){
        $webSiteInfo = $this->webSiteInfo;
        if(!isset($condition['type']))$condition['type'] = 'ALL';
        if(!isset($condition['suffix']))$condition['suffix'] = 'ALL';
        if(!isset($condition['searchWord']))$condition['searchWord'] = 'ALL';
        if(!isset($condition['page']))$condition['page'] = 1;
        return $webSiteInfo['webSite'].str_replace(
                array('$id','$type','$suffix','$searchWord','$page'),
                array($user['id'],$condition['type'],$condition['suffix'],$condition['searchWord'],$condition['page']),
                $webSiteInfo['userSite']);
    }
    protected function toSearchUrl($condition = null){
        $webSiteInfo = $this->webSiteInfo;
        if(!isset($condition['type']))$condition['type'] = 'ALL';
        if(!isset($condition['suffix']))$condition['suffix'] = 'ALL';
        if(!isset($condition['searchWord']))$condition['searchWord'] = 'ALL';
        if(!isset($condition['page']))$condition['page'] = 1;
        return $webSiteInfo['webSite'].str_replace(
                array('$type','$suffix','$searchWord','$page'),
                array($condition['type'],$condition['suffix'],$condition['searchWord'],$condition['page']),
                $webSiteInfo['searchSite']);
    }
    protected function getRedisCache($key,$func,$timeOut = 600){
        if(!empty($key)&&!empty($func)){
            $data = getRedis($key);
            if(empty($data)){
                $data = call_user_func($func);
                setRedis($key,$data,$timeOut);
            }
            return $data;
        }
    }
}
