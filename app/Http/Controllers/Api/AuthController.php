<?php

namespace App\Http\Controllers\Api;

use App\Models\User;

use App\Http\Controllers\Controller;
use App\Response\ResponseCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    /**
     * 登录
     *
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|exists:users|min:2|max:32',
            'password' => 'required|min:6|max:32',
        ]);

        if ($validator->fails()) {
            return ResponseCode::json(4001);
        }

        $user = User::where('username', $request->username)->first();
        if(!Hash::check($request->password, $user->password)) {
            return ResponseCode::json(5001);
        }

        if (!$user->role) {
            return ResponseCode::json(4007);
        }

        if (!$token = Auth::login($user)) {
            return ResponseCode::json(5002);
        }

        $data['user_id']      = strval($user->id);
        $data['access_token'] = $token;
        $data['expires_in']   = strval(time() + 86400);

        return ResponseCode::json(0, '登录成功', $data);
    }

    /**
     * 用户登出
     * 
     */
    public function logout()
    {
        Auth::invalidate(true);

        return ResponseCode::json(0, '登出成功');
    }

    /**
     * 更新用户Token
     *
     */
    public function refresh()
    {
        if (!$token = Auth::refresh(true, true)) {
            return ResponseCode::json(5002);
        }

        $data['access_token'] = $token;
        $data['expires_in']   = strval(time() + 86400);

        return ResponseCode::json(0, '刷新Token成功', $data);
    }
}