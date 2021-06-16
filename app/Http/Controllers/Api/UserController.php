<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Role;

use App\Http\Controllers\Controller;
use App\Response\ResponseCode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Arr;

class UserController extends Controller
{

    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    public function lists(Request $request)
    {
        $model = User::query();
        
        if($request->filled('id')) {
            $model = $model->where('id', $request->get('id'));
        }
        if($request->filled('username')) {
            $model = $model->where('username', 'like', '%'.$request->get('username').'%');
        }
        if($request->filled('name')) {
            $model = $model->where('name', 'like', '%'.$request->get('name').'%');
        }

        $list = $model->paginate($request->limit);

        return ResponseCode::json(0, '获取用户列表成功', $list);
    }
    
    public function getInfo()
    {
        $data['name'] = $this->user->name;
        $data['avatar'] = $this->user->avatar;
        $data['role'] = $this->user->role->slug;

        return ResponseCode::json(0, '获取用户信息成功', $data);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:32',
            'username' => 'required|string|max:32|unique:users',
            'password' => 'required|string|max:32'
        ]);

        if ($validator->fails()) {
            return ResponseCode::json(4001);
        }

        $row = $validator->validated();
        $row['password'] = Hash::make($row['password']);
        $row['avatar'] = getenv('APP_URL').'/upload/images/1-avatar.jpeg';
        $row['role_id'] = 2; //初始角色id
        if($user = User::create($row)) {
            return ResponseCode::json(0, '添加成功', $user);
        }

        return ResponseCode::json(5003);

    }

    public function edit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:users',
            'name' => 'required|string|max:32',
            'username' => 'required|string|max:32',
            'password' => 'sometimes|string|max:32'
        ]);

        if ($validator->fails()) {
            return ResponseCode::json(4001);
        }

        $row = $validator->validated();
        if(isset($row['password'])) {
            $row['password'] = Hash::make($row['password']);
        }

        $row = Arr::except($row, ['id']);
        if(User::where('id', $request->id)->update($row)) {
            return ResponseCode::json(0, '更新成功');
        }

        return ResponseCode::json(5003);
    }

    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'oldPassword' => 'required|string|max:32',
            'newPassword' => 'required|string|max:32'
        ]);

        if ($validator->fails()) {
            return ResponseCode::json(4001);
        }

        $row = $validator->validated();
        if (!Hash::check($row['oldPassword'], $this->user->password)) {
            return ResponseCode::json(4006);
        }

        $this->user->password = Hash::make($row['newPassword']);
        if($this->user->save()) {
            return ResponseCode::json(0, '修改成功');
        }

        return ResponseCode::json(5003);
    }

    public function destroy($id)
    {
        //超级管理员不能删除
        if($id == 1) {
            return ResponseCode::json(5004);
        }

        if(User::destroy($id)){
            return ResponseCode::json(0, '删除成功');
        }

        return ResponseCode::json(5003);
        
    }

    //分配角色
    public function allotRole(Request $request)
    {

        $user = User::find($request->user_id);
        if (!$user) {
            return ResponseCode::json(4004);
        }

        if ($request->isMethod('get')) {

            $data['all_roles'] = Role::select('id', 'name')->where('slug', '!=', 'supper_admin')->get();
            $data['user_role_id'] = $user->role ? $user->role->id : 0;

            return ResponseCode::json(0, '获取角色列表成功', $data);
        }

        $n = Role::where('id', $request->role_id)->count();
        if($n < 1) {
            return ResponseCode::json(4004);
        }

        $user->role_id = $request->role_id;
        if ($user->save()) {
            return ResponseCode::json(0, '分配成功', $user);
        }
        
        return ResponseCode::json(5003);
    }
}
