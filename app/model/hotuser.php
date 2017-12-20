<?php
/**
 * Created by PhpStorm.
 * User: win10
 * Date: 2017/12/20
 * Time: 14:17
 */
namespace App;

use Illuminate\Database\Eloquent\Model;

class hotuser extends Model
{
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'hotuser';
    public function getDateHot($date,$limit){
        return $this->join('share_user','share_file.id=hotFile.fileID')->where('date','=',$date)->groupBy('userID')->orderByDesc('count(userID)')->limit($limit)->get(['share_user.id','userName','imgUrl']);
    }
}