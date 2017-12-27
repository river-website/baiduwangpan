<?php
/**
 * Created by PhpStorm.
 * User: win10
 * Date: 2017/12/20
 * Time: 14:16
 */
namespace App\model;

use Illuminate\Database\Eloquent\Model;

class share_file extends Model
{
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'share_file';

    public function getNew($limit){
        return $this->orderByDesc('id')->limit($limit)->get();
    }
    public function getUkFile($uk,$limit,$page){
        return $this->where('uk',$uk)->paginate($limit);
    }
    public function search($params){
        $limit = 20;
        $offset = ($params['page']-1)*$limit;
        if(!empty($params['suffix']) && count($params['suffix'])>1)
            $this->whereIn('suffix',$params['suffix']);
        if(!empty($params['uk']))
            $this->where('uk',$params['uk']);
        if(!empty($params['fileName']))
            $this->where('filename','like',"%".$params['fileName']."%");
        $ret['data'] = $this->limit($limit)->toSql();
        output_json(success($ret));
        $ret['totle'] = $this->count();
        return $ret;
    }
    public function getPreByID($id,$filter='*'){
        $ret = $this
            ->where('id','<',$id)
            ->orderByDesc('id')
            ->limit(1)
            ->select($filter)
            ->first();
        if(!empty($ret))return $ret->toArray();
    }
    public function getNextByID($id,$filter='*'){
        $ret = $this
            ->where('id','>',$id)
            ->orderBy('id')
            ->limit(1)
            ->select($filter)
            ->first();
        if(!empty($ret))return $ret->toArray();
    }
}