<?php

namespace App\Http\Middleware;

use Closure;
use App\Response\ResponseCode;
use App\Models\Menu;
use Illuminate\Support\Facades\Auth;

class PermissionAuth
{
    /**
     * 处理传入的请求
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return $next($request);
        $user = Auth::user();
        //当前路径
        $currentPath = ltrim($request->path(), '/api/');
        $currentPath = rtrim($currentPath, '/');

        if (strpos($currentPath, 'destroy')) {
            $currentPath = str_replace(strrchr($currentPath, '/'), '', $currentPath);
        }

        //用户授权路由
        if($user->role->menus->count() == 0) return ResponseCode::json(4003);
        $routes = $user->role->menus()->pluck('url')->toArray();
        //无权限访问路由
        if(!in_array($currentPath, $routes)) {
            return ResponseCode::json(4003);
        }
        

        return $next($request);
    }
}