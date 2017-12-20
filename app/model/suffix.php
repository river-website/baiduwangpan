<?php
/**
 * Created by PhpStorm.
 * User: win10
 * Date: 2017/12/20
 * Time: 15:40
 */
namespace App;

use Illuminate\Database\Eloquent\Model;

class suffix extends Model
{
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'suffix';

    public function getSuffixTpesDict(){
        return $this->join('types','types.id','=','suffix.typeID')->get(['types.name as typeName','suffix']);
    }
}