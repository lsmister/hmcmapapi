<?php

namespace App\Response;

class ResponseCode
{
    protected static $codes = [
        0 => '成功',
        
        4001 => '参数错误',
        4002 => '无效认证',
        4003 => '无权限访问',
        4004 => '记录不存在',
        4005 => '记录已存在',
        4006 => '原密码不正确',
        4007 => '用户角色不存在',

        5001 => '用户名或密码错误',
        5002 => '系统错误，无法生成令牌',
        5003 => '操作失败',
        5004 => '此用户不可删除',
        5005 => '此菜单不可删除',
        5006 => '此角色不可删除',

        6000 => '未知错误'
    ];

    public static function json($code, $msg = '', $data = [])
    {
        if(empty($msg)) {
            $msg = self::$codes[$code];
        }

        if(empty($data)) {
            return response()->json(['code' => $code, 'message' => $msg]);
        }else {
            return response()->json(['code' => $code, 'message' => $msg, 'data' => $data]);
        }

    }

}
