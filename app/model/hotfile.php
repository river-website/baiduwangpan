<?php
/**
 * Created by PhpStorm.
 * User: win10
 * Date: 2017/12/20
 * Time: 14:17
 */
namespace App;

use Illuminate\Database\Eloquent\Model;

class hotfile extends Model
{
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'hotfile';
    public function getDateHot($date,$limit){
        return $this->join('share_file','share_file.id=hotFile.fileID')->where('date','=',$date)->groupBy('fileID')->orderByDesc('count(fileID)')->limit($limit)->get(['share_file.id','fileName']);
    }
}