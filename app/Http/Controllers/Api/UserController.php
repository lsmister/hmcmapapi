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
        $data['roles'] = $this->user->roles->pluck('slug');

        return ResponseCode::json(0, '获取用户信息成功', $data);
    }

    public function accessRoutes()
    {
        $roles = $this->user->roles;
        if($roles->count() == 0) {
            return ResponseCode::json(0, '未分配角色');
        }

        if($role->menus->count() == 0) {
            return ResponseCode::json(0, '未分配权限');
        }

        $menus = $role->menus->where('permission_type', 'page')->toArray();
        $routes = $this->categoryMenu($menus, 0);

        return ResponseCode::json(0, '获取授权路由列表成功', $routes);
    }

    public function categoryMenu($menus, $parent_id)
    {
        $data = [];
        foreach($menus as $v) {
            if($v['parent_id'] == $parent_id) {
                $data[] = $v;
            }
        }

        if(count($data) == 0) {
            return [];
        }else {
            foreach($data as $key => $val) {
                $info[$key]['path'] = $val['url'];
                $info[$key]['component'] = $val['component'];

                $str = ltrim($val['url'], '/');
                if(strpos($str, '/')) {
                    $arr = explode('/', $str);
                    $name = '';
                    foreach($arr as $a) {
                        $name .= ucfirst($a);
                    }
                }else {
                    $name = ucfirst($str);
                }
                $info[$key]['name'] = $name;
                $info[$key]['meta']['title'] = $val['title'];
                $info[$key]['meta']['icon'] = $val['icon'];
                $info[$key]['children'] = $this->categoryMenu($menus, $val['id']);
                if(empty($info[$key]['children'])) {
                    unset($info[$key]['children']);
                }
            }
        }

        return $info;
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

        //移除角色
        $this->user->roles()->detach();

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
            $data['user_role_ids'] = $user->roles->pluck('id');

            return ResponseCode::json(0, '获取角色列表成功', $data);
        }

        $n = Role::whereIn('id', $request->role_ids)->count();
        if(count($request->role_ids) != $n) {
            return ResponseCode::json(4004);
        }

        $user->roles()->sync($request->role_ids);
        
        return ResponseCode::json(0, '分配成功', $user);

        return ResponseCode::json(5003);
    }
}
