<?php
/**
 * Created by PhpStorm.
 * User: win10
 * Date: 2017/12/20
 * Time: 14:17
 */
namespace App\model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class hotsearch extends Model
{
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'hotSearch';

    public function getDateHot($date,$limit){
        return $this
            ->where('date',$date)
            ->groupBy('searchWord')
            ->orderByDesc('clicks')
            ->limit($limit)
            ->select(DB::raw('searchWord,count(searchWord) as clicks'))
            ->get()->toArray();
    }
}