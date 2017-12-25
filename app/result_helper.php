<?php
/**
 * Created by PhpStorm.
 * User: win10
 * Date: 2017/12/25
 * Time: 18:08
 */
if (!function_exists('output_json')) {
    function output_json($data)
    {
        echo json_encode($data);
        exit();
    }
}

if (!function_exists('error')) {
    function error($options = -1, $data = array())
    {
        return array(
            'ret' => -1,
            'msg' =>  '未知错误',
            'data' => $data
        );
    }
}

if (!function_exists('success')) {
    function success($options = array()) {
        if(isset($options['ret']) && isset($options['data'])) {
            return $options;
        } else {
            return array(
                'ret' => 0,
                'msg' => '成功',
                'data' => $options
            );
        }
    }
}
if (!function_exists('filter')) {
    function filter(&$param, $requiredArray = array(), $optionalArray = array()) {
        $requiredLostArray = array();
        $notNeedArray = array();

        foreach($requiredArray as $required) {
            if(!isset($param[$required]) || $param[$required] === '') {
                array_push($requiredLostArray, $required);
            }
        }

        foreach($param as $key => $val) {
            if(!in_array($key, $requiredArray) && !in_array($key, $optionalArray)) {
                array_push($notNeedArray, $key);
                unset($param[$key]);
            }
        }

        if(count($requiredLostArray) == 0) {
            return success();
        } else {
            return error(2, array(
                'requiredLost' => $requiredLostArray,
                'notNeed' => $notNeedArray
            ));
        }
    }
}
