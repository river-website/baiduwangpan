<?php namespace App\Http\Controllers;
/**
 * Created by PhpStorm.
 * User: win10
 * Date: 2018/1/2
 * Time: 18:20
 */
class crawlController extends baseController {
    private $rules = array();
    public function __construct(){
        $this->initRules();
    }
    //开始采集任务
    public function start(){
        ezBack(array($this,'crawl_sopanpan_file'));
//        ezBack(array($this,'crawl_baiduyun_user'));
    }
    //采集规则
    private function initRules(){
        $this->rules['http://www.baiduyunpan.com/file/%id%.html'] = array(
            'name'=>array('.resource-h2','text'),
            'yunUserID'=>array('.x-right-li-a','href',null,function($contents){
                $a = explode('-',$contents);
                $a = explode('/',$a[0]);
                if(empty($a[2]))return '';
                return $a[2];
            }),
            'size'=>array('.x-right-li:eq(2) span','text'),
            'suffix'=>array('.x-right-li:eq(4) span','text'),
            'url'=>array('.main-xzfx-a:eq(0)','href',null,function($contents){
                $a = explode('url=',$contents);
                if(empty($a[1]))return '';
                return urldecode($a[1]);
            })
        );
        $this->rules['http://www.baiduyunpan.com/user/%id%-0-0.html'] = array(
            'name'=>array('.main-right-h2','text',null,function($contents){
                return str_replace('的百度云资源','',$contents);
            }),
            'imgUrl'=>array('.main-left-a img','src')
        );
        $this->rules['http://pan.baidu.com/share/link?shareid=%shareid%&uk=%uk%&fid=%fid%'] = array(
            'fileName'=>array('head title','text',null,function($contents){
                return str_replace('_免费高速下载|百度网盘-分享无限制','',$contents);
            }),
            'shareTime'=>array('share-file-info span','text')
        );
        $this->rules['https://wangpan007.com/redirect/file?id=%id%'] = array(
            'url'=>array('#tip_msg p:eq(1)','text')
        );
        $this->rules['https://wangpan007.com/share/file/%id%'] = array(
            'fileName'=>array('.title-box h1','text'),
            'suffix'=>array('.info-basic li:eq(0) span','text',null,function($con){
                $con = ltrim($con,'(');
                return rtrim($con,')');
            }),
            'size'=>array('.info-basic li:eq(1)','text',null,function($con){
                return str_replace('文件大小：','',$con);
            }),
            'shareTime'=>array('.info-basic li:eq(2)','text',null,function($con){
                return str_replace('分享时间：','',$con);
            })
        );
        $this->rules['https://www.sopanpan.com/file/%id%.html'] = array(
            'fileName'=>array('.wbreak','text'),
            'url'=>array('#down','href'),
            'uk'=>array('.usrif .r a','href',null,function($con){
                $con = str_replace('/user/','',$con);
                return str_replace('-0.html','',$con);
            })
        );
        $this->rules['http://pan.baidu.com/share/home?uk=%id%'] = array(
            'userName'=>array('.homepagelink','text'),
//			'userInfo'=>array('.personal-info','text'),
            'imgUrl'=>array('.pic-frm-pic','src')
        );
    }
    // 后台任务
    public function back($backName){
        ezBack(array($this,$backName));
    }
    //后台采集任务
    public function backCrawlTask($taskName){
        ezBack(array($this,'crawl'),$taskName);
    }
    //采集
    public function crawl($funcName){
        ezServer()->logFile = ezServer()->logDir.'/crawl-$date.log';
        $count = 10000;
        while(--$count>=0){
            if($this->$funcName())break;
        }
    }
    //百度云用户
    public function baiduyun_user(){
        ezDebugLog("baiduyun_user start");
        $baseUrl = 'http://pan.baidu.com/share/home?uk=%id%';
        $rule = $this->rules[$baseUrl];

        $share_user = $this->getModel('share_user');
        $crawl_list = $share_user->where(array("userName is null"))->order(array('id'),'asc')->limit(1000)->select(array('id','uk'));
        ezServer()->addErrorIgnorePath(E_NOTICE,ezSYSPATH.'/library/');
        $phpQuery = new QueryList();
        foreach ($crawl_list as $crawl_id){
            $url = str_replace('%id%',$crawl_id['uk'],$baseUrl);
            ezDebugLog($url);
            $phpQuery->html = $url;
            $data = $phpQuery->setQuery($rule)->data;
            if(count($data) != 1 || count($data[0]) != 2){
                continue;
            }
            $data = $data[0];
            ezLog('baiduyun_user id is: '.$crawl_id['id'].' uk is '.$crawl_id['uk']);
            $share_user->update($data,array('id='.$crawl_id['id']));
        }
        ezLog("baiduyun_user end");
        if(count($crawl_list)>0)return false;
        return true;
    }
    // sopan 文件
    public function sopanpan_file(){
        ezLog("sopanpan_file start");
        $baseUrl = 'https://www.sopanpan.com/file/%id%.html';
        $rule = $this->rules[$baseUrl];

        $ids = $this->updateLast($baseUrl,1000);
        ezServer()->addErrorIgnorePath(E_NOTICE,ezSYSPATH.'/library/');
        ezServer()->addErrorIgnorePath(E_WARNING,ezSYSPATH.'/library/');
        $phpQuery = new QueryList();
        while(true) {
            for ($i = $ids['start']; $i < $ids['end']; $i++) {
                $url = str_replace('%id%', $i, $baseUrl);
                ezDebugLog($url);
                $phpQuery->html = $url;
                $data = $phpQuery->setQuery($rule)->data;
                if (count($data) != 1 || count($data[0]) != 3) {
                    continue;
                }
                $data = $data[0];
                $pos = strripos($data['fileName'], '.');
                if ($pos === false || $pos === 0) {
                } else {
                    $suffix = strtolower(substr($data['fileName'], $pos));
                    if (!ctype_alnum($suffix)) {
                    } else $data['suffix'] = $suffix;
                }
                ezlog("sopanpan_file id is: $i");
                $crawlData[] = $data;
            }
            if (!empty($crawlData) && count($crawlData) > 0) {
                $share_file = $this->getModel('share_file');
                $share_file->insertList($crawlData);
                ezLog("sopanpan_file count=" . count($crawlData));
                break;
            }else{
                ezLog('sleep 30min');
                sleep(1800);
            }
        }
        ezlog("sopanpan_file end");
        return false;
    }
    // wangpan007 文件
    public function wangpan007_file(){
        ezlog("wangpan007_file start");
        $baseUrl = 'https://wangpan007.com/share/file/%id%';
        $rule = $this->rules[$baseUrl];

        $share_file = $this->getModel('share_file');
        $crawl_list = $share_file->where(array("fileName is null"))->order(array('id'),'asc')->limit(1000)->select(array('id','wangpan007_id'));
        ezServer()->addErrorIgnorePath(E_NOTICE,ezSYSPATH.'/library/');
        $phpQuery = new QueryList();
        foreach ($crawl_list as $crawl_id){
            $url = str_replace('%id%',$crawl_id['wangpan007_id'],$baseUrl);
            ezDebugLog($url);
            $phpQuery->html = $url;
            $data = $phpQuery->setQuery($rule)->data;
            if(count($data) != 1 || count($data[0]) != 4){
                continue;
            }
            $data = $data[0];
            ezLog('wangpan007_file id is: '.$crawl_id['id'].' wanpan007 id is '.$crawl_id['wangpan007_id']);
            $share_file->update($data,array('id='.$crawl_id['id']));
        }
        ezLog("wangpan007_file end");
        if(count($crawl_list)>0)return false;
        return true;
    }
    // wangpan007 真实文件地址
    public function wangpan007_redirect(){
        ezLog("wangpan007_redirect start");
        $baseUrl = 'https://wangpan007.com/redirect/file?id=%id%';
        $rule = $this->rules[$baseUrl];

        $ids = $this->updateLast($baseUrl,1000);
        ezServer()->addErrorIgnorePath(E_NOTICE,ezSYSPATH.'/library/');
        $phpQuery = new QueryList();
        for ($i=$ids['start'];$i<$ids['end'];$i++){
            $url = str_replace('%id%',$i,$baseUrl);
            ezDebugLog($url);
            $phpQuery->html = $url;
            $data = $phpQuery->setQuery($rule)->data;
            if(count($data) != 1 || count($data[0]) != 1){
                continue;
            }
            $data = $data[0];
            $data['url'] = str_replace('amp;','',$data['url']);
            $temp1 =explode('?',$data['url']);
            if(count($temp1) != 2)continue;
            parse_str($temp1[1],$temp2);
            if(!empty($temp2['shareid']))$data['shareid'] = $temp2['shareid'];
            if(!empty($temp2['uk']))$data['uk'] = $temp2['uk'];
            if(!empty($temp2['fid']))$data['fid'] = $temp2['fid'];
            $data['wangpan007_id'] = $i;
            ezLog("wangpan007_redirect id is: $i");
            $crawlData[] = $data;
        }
        if(!empty($crawlData)&&count($crawlData)>0) {
            $share_file = $this->getModel('share_file');
            $share_file->insertList($crawlData);
            ezLog("crawl_wangpan007 count=".count($crawlData));
        }
        ezLog("wangpan007_redirect end");
        return false;
    }
    // 循环采集 file 一直执行
    public function crawl_sopanpan_file(){
        ezServer()->logFile = ezServer()->logDir.'/crawl-file-$date.log';
        ezLog('start crawl_baiduyunpan_file');
        while(true){
            $this->sopanpan_file();
        }
    }
    public function crawl_baiduyun_user(){
        ezServer()->logFile = ezServer()->logDir.'/crawl-user-$date.log';
        ezLog('start crawl_baiduyun_user');
        while(true){
            ezLog('crawl_baiduyun_user date:'.date('Y-m-d'));
            $this->share_user_get();
            $this->share_user_update();
            sleep(86400);
        }
    }

    // baiduyunpan 文件
    public function baiduyunpan_file(){
        ezLog("baiduyunpan_file start");
        $baseUrl = 'http://www.baiduyunpan.com/file/%id%.html';
        $rule = $this->rules[$baseUrl];

        $ids = $this->updateLast($baseUrl,1000);
        ezServer()->addErrorIgnorePath(E_NOTICE,ezSYSPATH.'/library/');
        ezServer()->addErrorIgnorePath(E_WARNING,ezSYSPATH.'/library/');
        $phpQuery = new QueryList();
        while(true) {
            for ($i = $ids['start']; $i < $ids['end']; $i++) {
                $url = str_replace('%id%', $i, $baseUrl);
                $phpQuery->html = $url;
                $data = $phpQuery->setQuery($rule)->data;
                if (count($data) != 1 || count($data[0]) != 5) {
                    continue;
                }
                $data = $data[0];
                $pos = strripos($data['fileName'], '.');
                if ($pos === false || $pos === 0) {
                } else {
                    $suffix = strtolower(substr($data['fileName'], $pos));
                    if (!ctype_alnum($suffix)) {
                    } else $data['suffix'] = $suffix;
                }
                ezLog("baiduyunpan_file id is: $i");
                $crawlData[] = $data;
            }
            if (!empty($crawlData) && count($crawlData) > 0) {
                $yunUrl = $this->getModel('yunUrl');
                $yunUrl->insertList($crawlData);
                ezLog("baiduyunpan_file count=" . count($crawlData));
                break;
            }else{
                ezLog('sleep 30min');
                sleep(1800);
            }
        }
        ezLog("baiduyunpan_file end");
        return false;
    }
    // 文件名中获取 后缀
    public function getSuffix($fileName){
        $pos = strrpos($fileName,'.');
        if($pos != false && $pos != 0){
            return substr($fileName,$pos);
        }else{
            return '/';
        }
    }
    // 修复suffix表 后缀 .txt -> txt
    public function repairData_suffix(){
        $suffix = $this->getModel('suffix');
        $suffix_list = $suffix->select(array('id', 'suffix'));
        foreach ($suffix_list as $value) {
            $id = $value['id'];
            $value = strtolower($value['suffix']);
            $new = str_replace('.', '', $value);
            $suffix->update(array('suffix' => $new), array("id=$id"));
        }
    }
    // 修复share_file表 文件 后缀修复  .txt -> txt  中文,null,'',t.txt  break
    public function repairData_share_file_suffix()
    {
        $offset = 0;
        $limit = 10000;
        $share_file = $this->getModel('share_file');
        while(true){
            $suffix_list = $share_file->limit($limit,$offset)->select('id,fileName');
            if(count($suffix_list)==0)break;
            foreach ($suffix_list as $value){
                $fileName = $value['fileName'];
                $pos = strripos($fileName,'.');
                if($pos === false || $pos === 0)continue;
                $suffix = strtolower(substr($fileName,$pos));
                if(!ctype_alnum($suffix))continue;
                $id = $value['id'];
                $share_file->update(array('suffix'=>$suffix),array("id=$id"));
            }
        }
    }
    // 将新的baidu user插入
    public function share_user_get(){
        // 从file中找user，将新的user插入
        $share_user = $this->getModel('share_user');
        $share_file = $this->getModel('share_file');
        $all_uk_list = $share_file->group(array('uk'))->select(array('uk'));
        $old_uk_list = $share_user->select(array('uk'));
        foreach ($old_uk_list as $old_uk)
            $uk_list[$old_uk['uk']] = $old_uk['uk'];
        foreach ($all_uk_list as $uk)
            if(!isset($uk_list[$uk['uk']]))
                $new_uk_list[] = $uk;
        if(!empty($new_uk_list)) {
            $share_user->insertList($new_uk_list);
            ezLog('new user count='.count($new_uk_list));
        }else ezLog('new user count=0');

    }
    // 从baidu 更新user表
    public function share_user_update(){
        // 获取user中空数据，从baidu更新数据
        $offset = 0;
        $limit = 1000;
        ezServer()->addErrorIgnorePath(E_NOTICE,ezSYSPATH.'/library/');
        ezServer()->addErrorIgnorePath(E_WARNING,ezSYSPATH.'/library/');
        $baseUrl = 'http://pan.baidu.com/share/home?uk=%id%';
        $rule = $this->rules[$baseUrl];
        $phpQuery = new QueryList();
        $share_user = $this->getModel('share_user');
        while(true){
            $uk_list = $share_user->limit($limit,$offset)->order(array('id'))->select();
            ezLog("offset is $offset");
            $offset += $limit;
            if(count($uk_list)==0)break;
            foreach ($uk_list as $value){
                if(empty($value['userName'])||empty($value['imgUrl'])){
                    $url = str_replace('%id%',$value['uk'],$baseUrl);
                    ezDebugLog($url);
                    $phpQuery->html = $url;
                    $data = $phpQuery->setQuery($rule)->data;
                    if(count($data) != 1 || count($data[0]) != 2){
                        continue;
                    }
                    $data = $data[0];
                    ezLog('baiduyun_user id is: '.$value['id'].' uk is '.$value['uk']);
                    $share_user->update($data,array('id='.$value['id']));
                }
            }
        }
    }
    // 爬虫使用更新最后一次的id
    public function updateLast($www,$count){
        $crawlLast = $this->getModel('crawlLast');
        $last = $crawlLast->where(array("web='$www'"))->select();
        $last = $last[0];
        $start = $last['last'] + 1;
        $end = $start + $count;
        $last['last'] = $last['last'] + $count;
        $crawlLast->update($last,array('id='.$last['id']));
        return array('start'=>$start,'end'=>$end);
    }

    public function wangpan007_file_sign($id){
        ezServerLog("wangpan007_file start");
        $baseUrl = 'https://wangpan007.com/share/file/%id%';
        $rule = $this->rules[$baseUrl];

        $share_file = $this->getModel('share_file');
        $crawl_list = $share_file->where(array('id='.$id))->select(array('id','wangpan007_id'));
        ezGLOBALS::addErrorIgnorePath(E_NOTICE,ezSYSPATH.'/library/');
        $phpQuery = new QueryList();
        foreach ($crawl_list as $crawl_id){
            $url = str_replace('%id%',$crawl_id['wangpan007_id'],$baseUrl);
            ezDebugLog($url);
            $phpQuery->html = $url;
            $data = $phpQuery->setQuery($rule)->data;
            if(count($data) != 1 || count($data[0]) != 4){
                continue;
            }
            $data = $data[0];
            ezServerLog('crawl id is: '.$crawl_id['id'].' wanpan007 id is '.$crawl_id['wangpan007_id']);
            $share_file->update($data,array('id='.$crawl_id['id']));
        }
        if(count($crawl_list)>0)return false;
        return true;
    }
    public function sopanpan_file_sign($id){
        ezServerLog("sopanpan_file start");
        $baseUrl = 'http://www.sopanpan.com/file/%id%.html';
        $rule = $this->rules[$baseUrl];

        ezGLOBALS::addErrorIgnorePath(E_NOTICE,ezSYSPATH.'/library/');
        $phpQuery = new QueryList();
        $url = str_replace('%id%',$id,$baseUrl);
        ezDebugLog($url);
        $phpQuery->html = $url;
        $data = $phpQuery->setQuery($rule)->data;
        if(count($data) != 1 || count($data[0]) != 3){
            return;
        }
        $data = $data[0];
        $temp1 =explode('?',$data['url']);
        if(count($temp1) == 2){
            parse_str($temp1[1],$temp2);
            if(!empty($temp2['shareid']))$data['shareid'] = $temp2['shareid'];
            if(!empty($temp2['fid']))$data['fid'] = $temp2['fid'];
        }
        ezServerLog("crawl id is: $id");
        $crawlData[] = $data;
        var_dump($crawlData);
        if(!empty($crawlData)&&count($crawlData)>0) {
            $share_file = $this->getModel('share_file');
            $share_file->insertList($crawlData);
            ezServerLog("sopanpan_file crawl count=".count($crawlData));
        }
    }
    public function baiduyun_file_sign($id){
        $baseUrl = 'http://pan.baidu.com/share/link?shareid=%shareid%&uk=%uk%&fid=%fid%';
        $rule = $this->rules[$baseUrl];

        $share_file = $this->getModel('share_file');
        $url = $share_file->where(array("id=$id"))->select(array('url'));
        $url = $url[0]['url'];
        ezGLOBALS::addErrorIgnorePath(E_NOTICE,ezSYSPATH.'/library/');
        $phpQuery = new QueryList();
//		$url = str_replace('%id%',$id,$baseUrl);
        echo($url);
        $phpQuery->html = $url;
        $data = $phpQuery->setQuery($rule)->data;
        if(count($data) != 1 || count($data[0]) != 3){
            return;
        }
        $data = $data[0];
        ezServerLog("crawl id is: $id");
        $crawlData[] = $data;
        var_dump($crawlData);
        if(!empty($crawlData)&&count($crawlData)>0) {
//			$share_file->insertList($crawlData);
            ezServerLog("sopanpan_file crawl count=".count($crawlData));
        }
    }
}