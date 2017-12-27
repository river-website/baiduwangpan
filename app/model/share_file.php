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
        if(empty($params['page'])) $params['page'] = 1;
        if(empty($params['limit'])) $params['limit'] = 20;
        if(empty($params['orderField'])) $params['order'] = 'id';
        if(empty($params['orderBy'])) $params['orderBy'] = 'orderBy';

        $offset = ($params['page']-1)*$params['limit'];
        $select = $this;
        if(!empty($params['suffix']) && count($params['suffix'])>1)
            $select = $select->whereIn('suffix',$params['suffix']);
        if(!empty($params['uk']))
            $select = $select->where('uk',$params['uk']);
        if(!empty($params['fileName']))
            $select = $select->where('filename','like',"%".$params['fileName']."%");
        $ret['data'] = $select->$params['orderBy']($params['orderField'])->offset($offset)->limit($params['limit'])->get()->toArray();
        $ret['totle'] = $select->count();
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