<?php

namespace App\Http\Controllers\Api;

use App\Models\Role;
use App\Models\Menu;

use App\Http\Controllers\Controller;
use App\Response\ResponseCode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class RoleController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }
    
    public function lists(Request $request)
    {
        $model = Role::query();

        if($request->filled('id')) {
            $model = $model->where('id', $request->get('id'));
        }
        if($request->filled('slug')) {
            $model = $model->where('slug', 'like', '%'.$request->get('slug').'%');
        }
        
        $list = $model->paginate($request->limit);

        return ResponseCode::json(0, '获取角色列表成功', $list);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'slug' => 'required|string|max:32|unique:roles',
            'name' => 'required|string|max:32'
        ]);

        if ($validator->fails()) {
            return ResponseCode::json(4001);
        }

        $row = Role::create($validator->validated());
        if($row) {
            return ResponseCode::json(0, '添加成功', $row);
        }

        $this->clearCache();
        return ResponseCode::json(5003);

    }

    public function edit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:menus',
            'slug' => 'required|string|max:32',
            'name' => 'required|string|max:32'
        ]);

        if ($validator->fails()) {
            return ResponseCode::json(4001);
        }

        if(Role::where('id', '!=', $request->id)->where('slug', $request->slug)->exists()) {
            return ResponseCode::json(4005);
        }

        $row = Arr::except($validator->validated(), ['id']);
        if(Role::where('id', $request->id)->update($row)) {
            return ResponseCode::json(0, '更新成功');
        }

        $this->clearCache();
        return ResponseCode::json(5003);
    }

    public function destroy($id)
    {
        if($id == 1) {
            return ResponseCode::json(5006);
        }

        $row = Role::find($id);
        if($row) {
            $row->delete();
            $row->menus()->detach();

            return ResponseCode::json(0, '删除成功');
        }

        $this->clearCache();
        return ResponseCode::json(5003);
    }

    //分配权限
    public function allotPermission(Request $request)
    {
        // $this->clearCache();
        $role = Role::findOrFail($request->role_id);

        if ($request->isMethod('get')) {
            $data['useMenus'] = $role->menus->pluck('id');
            $data['categoryMenus'] = Cache::rememberForever('laravel_system_role_category_menus', function () {
                $menus = Menu::get()->toArray();
                return (new Menu)->categoryMenu($menus, 0);
            });
            $data['categoryIds'] = Cache::rememberForever('laravel_system_role_category_ids', function () {
                return Menu::pluck('id');
            });

            return ResponseCode::json(0, '获取菜单列表成功', $data);
        }


        $role->menus()->sync($request->permission_ids);

        return ResponseCode::json(0, '分配成功');

    }


    //资源改变时重新加载缓存
    public function clearCache()
    {
        Cache::forget('laravel_system_role_category_menus');
        Cache::forget('laravel_system_role_category_ids');
    }
    
}
