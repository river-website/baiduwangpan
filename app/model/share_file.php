<?php
/**
 * Created by PhpStorm.
 * User: win10
 * Date: 2017/12/20
 * Time: 14:16
 */
namespace App;

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
}