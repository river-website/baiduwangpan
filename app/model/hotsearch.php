<?php
/**
 * Created by PhpStorm.
 * User: win10
 * Date: 2017/12/20
 * Time: 14:17
 */
namespace App\model;

use Illuminate\Database\Eloquent\Model;

class hotsearch extends Model
{
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'hotSearch';

    public function getDateHot($date,$limit){
        return $this->where('date','=',$date)->groupBy('searchWord')->orderByDesc('count(searchWord)')->limit($limit)->get('searchWord');
    }
}