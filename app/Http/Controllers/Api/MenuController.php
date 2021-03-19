<?php

namespace App\Http\Controllers\Api;

use App\Models\Menu;

use App\Http\Controllers\Controller;
use App\Response\ResponseCode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class MenuController extends Controller
{

    protected $user;

    const banDelMenuIds = [
        1, 2, 3, 4
    ];

    public function __construct()
    {
        $this->user = Auth::user();
    }
    
    public function lists(Request $request)
    {
        $this->clearCache();
        $model = Menu::query();
        
        if($request->filled('id')) {
            $model = $model->where('id', $request->get('id'));
        }
        if($request->filled('title')) {
            $model = $model->where('title', 'like', '%'.$request->get('title').'%');
        }
        if($request->filled('permission_type')) {
            $model = $model->where('permission_type', $request->get('permission_type'));
        }

        $list = $model->paginate($request->limit);

        return ResponseCode::json(0, '获取菜单列表成功', $list);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'parent_id' => 'required|numeric',
            'title' => 'required|string',
            'icon' => 'sometimes|nullable|string',
            'order' => 'required|numeric',
            'url' => 'nullable|string|max:191',
            'component' => 'nullable|string|max:191',
            'permission_type' => 'required|string'
        ]);

        if ($validator->fails()) {
            return ResponseCode::json(4001);
        }

        $data = array_filter($validator->validated());
        $row = Menu::create($data);
        if($row) {
            $this->clearCache();
            return ResponseCode::json(0, '添加成功', $row);
        }

        return ResponseCode::json(5003);

    }

    public function edit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:menus',
            'parent_id' => 'required|numeric',
            'title' => 'required|string',
            'icon' => 'sometimes|nullable|string',
            'order' => 'required|numeric',
            'url' => 'nullable|string|max:191',
            'component' => 'nullable|string|max:191',
            'permission_type' => 'required|string'
        ]);

        if ($validator->fails()) {
            return ResponseCode::json(4001);
        }

        $row = Arr::except($validator->validated(), ['id']);
        if(Menu::where('id', $request->id)->update($row)) {
            $this->clearCache();
            return ResponseCode::json(0, '更新成功');
        }

        return ResponseCode::json(5003);
    }

    public function destroy($id)
    {
        if(in_array($id, self::banDelMenuIds)) {
            return ResponseCode::json(5005);
        }

        $row = Menu::find($id);
        if($row) {
            $row->delete();
            $row->roles()->detach();
            $this->clearCache();
            return ResponseCode::json(0, '删除成功');
        }

        $this->clearCache();
        return ResponseCode::json(5003);
    }

    //获取顶级菜单
    public function getTopLevel()
    {
        $list = Menu::where('parent_id', 0)
                    ->where('permission_type', 'page')
                    ->select('id', 'parent_id', 'title')
                    ->get();

        return ResponseCode::json(0, '获取菜单列表成功', $list);
    }

    //select菜单
    public function getSelectOptions()
    {
        $list = Cache::rememberForever('laravel_system_menu_select_options', function () {
            $menus = Menu::where('permission_type', 'page')->orderBy('order')->get()->toArray();
            $list = $this->categorySelectOption($menus, 0);
            array_unshift($list, ['id' => 0, 'label' => '顶级']);
            return $list;
        });

        return ResponseCode::json(0, '获取select菜单成功', $list);
    }

    //生成无限级select选项
    public function categorySelectOption($menus, $parent_id, $level = 0)
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
                $info[$key]['id'] = $val['id'];
                $info[$key]['label'] = $val['title'];
                $children = $this->categorySelectOption($menus, $val['id'], $level + 1);
                if (!empty($children)) {
                    $info[$key]['children'] = $children;
                }
            }
        }

        return $info;
    }

    //资源改变时重新加载缓存
    public function clearCache()
    {
        Cache::forget('laravel_system_menu_select_options');
        Cache::forget('laravel_system_role_category_menus');
        Cache::forget('laravel_system_role_category_ids');
    }
}
