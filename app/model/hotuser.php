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

class hotuser extends Model
{
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'hotUser';
    public function getDateHot($date,$limit){
        return $this
            ->join('share_user','share_user.id','=','hotUser.userID')
            ->where('date',$date)
            ->groupBy('userID')
            ->orderByDesc('clicks')
            ->limit($limit)
            ->select(DB::raw('count(userID) as clicks,share_user.id,share_user.userName,share_user.imgUrl'))
            ->get()->toArray();
    }
}