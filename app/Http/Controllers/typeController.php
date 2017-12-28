<?php namespace App\Http\Controllers;
/**
 * Created by PhpStorm.
 * User: win10
 * Date: 2017/12/28
 * Time: 10:54
 */
class typeController extends baseController
{
    public function get()
    {
        output_json(success(array_values($this->getTypeToSuffix())));
    }
}