<?php
/**
 * Created by PhpStorm.
 * User: win10
 * Date: 2018/1/3
 * Time: 11:47
 */
namespace App\model;

class mongoBase{
    protected $collectionName = '';
    protected $collection = null;
    protected $conf = 'default';
    public function __construct()
    {
        $conf = config('database.mongo.'.$this->conf);
        $host = $conf['host'];
        $port = $conf['port'];
        $dataBase = $conf['database'];
        $m = new \MongoClient("mongodb://$host:$port");
        $db = $m->selectDB($dataBase);
        $this->collection =  $db->selectCollection($this->collectionName);
    }
    public function __call($method,$arg){
        call_user_func_array([$this->collection,$method],$arg);
    }
}