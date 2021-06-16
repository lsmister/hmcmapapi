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
    
    public function getMenuInfo($id) {
        $menu = Menu::select('id','parent_id','title','order','component','icon')->find($id);

        if ($menu) {
            return ResponseCode::json(0, '获取成功', $menu);
        }

        return ResponseCode::json(5003);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'parent_id' => 'required|numeric',
            'title' => 'required|string',
            'icon' => 'sometimes|nullable|string',
            'order' => 'required|numeric',
            'component' => 'nullable|string|max:191',
        ]);

        if ($validator->fails()) {
            return ResponseCode::json(4001);
        }

        $data = array_filter($validator->validated());
        $row = Menu::create($data);
        if($row) {
            $this->clearCache();
            return $this->getLevelOptions();
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
            'component' => 'nullable|string|max:191',
        ]);

        if ($validator->fails()) {
            return ResponseCode::json(4001);
        }

        $row = Arr::except($validator->validated(), ['id']);
        if(Menu::where('id', $request->id)->update($row)) {
            $this->clearCache();
            return $this->getLevelOptions();
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
            // $row->roles()->detach();
            $this->clearCache();
            return ResponseCode::json(0, '删除成功');
        }

        return ResponseCode::json(5003);
    }


    //资源改变时重新加载缓存
    public function clearCache()
    {
        Cache::forget('laravel_system_menu_category_menus');
        Cache::forget('laravel_system_menu_category_ids');
    }


    //菜单列表
    public function getLevelOptions()
    {
        // $this->clearCache();
        $data['categoryMenus'] = Cache::rememberForever('laravel_system_menu_category_menus', function () {
            $menus = Menu::get()->toArray();
            $list = $this->categoryMenu($menus, 0);
            array_unshift($list, ['id' => 0, 'label' => '顶级']);
            return $list;
        });

        $data['categoryIds'] = Cache::rememberForever('laravel_system_menu_category_ids', function () {
            return Menu::pluck('id');
        });

        return ResponseCode::json(0, '获取菜单成功', $data);
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
                $info[$key]['id'] = $val['id'];
                $info[$key]['label'] = $val['title'];
                $info[$key]['children'] = $this->categoryMenu($menus, $val['id']);
                if(empty($info[$key]['children'])) {
                    unset($info[$key]['children']);
                }
            }
        }

        return $info;
    }

}
