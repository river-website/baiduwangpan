<?php namespace App\Http\Controllers;
/**
 * Created by PhpStorm.
 * User: win10
 * Date: 2017/12/28
 * Time: 10:54
 */
class suffixController extends baseController
{
    public function search()
    {
        // 参数获取
        $params = $_GET;
        // 参数过滤
        $ret = filter($params,array('typeName'));
        if($ret['ret'] != 0)output_json($ret);
        $typeToSuffix = $this->getTypeToSuffix();
        if(empty($typeToSuffix[$params['typeName']]))output_json(success());
        output_json(success($typeToSuffix[$params['typeName']]));
    }
}