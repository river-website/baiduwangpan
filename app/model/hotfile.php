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

class hotfile extends Model
{
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'hotFile';
    public function getDateHot($date,$limit){
        return $this
            ->join('share_file','share_file.id','=','hotFile.fileID')
            ->where('date',$date)
            ->groupBy('fileID')
            ->orderByDesc('clicks')
            ->limit($limit)
            ->select(DB::raw('count(fileID) as clicks,share_file.id,share_file.fileName'))
            ->get()->toArray();
    }
}