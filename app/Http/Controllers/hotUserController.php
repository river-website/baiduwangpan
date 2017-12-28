<?php namespace App\Http\Controllers;
/**
 * Created by PhpStorm.
 * User: win10
 * Date: 2017/12/28
 * Time: 10:54
 */
class hotUserController extends baseController
{
    public function getHot()
    {
        output_json(success($this->getHotUser()));
    }
}