<?php

namespace App\Http\Controllers\Api\Group\Mrt;

use App\Models\HotelGroup;
use App\Models\Role;

use App\Http\Controllers\Controller;
use App\Response\ResponseCode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Arr;

class HotelController extends Controller
{

    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    public function lists(Request $request)
    {
        $model = HotelGroup::query();
        
        if($request->filled('code')) {
            $model = $model->where('code', $request->get('code'));
        }

        $list = $model->paginate($request->limit);

        return ResponseCode::json(0, '获取列表成功', $list);
    }
    
    
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:16|unique:hotel_groups',
            'en_name' => 'required|string|max:32',
            'cn_name' => 'required|string|max:32'
        ]);

        if ($validator->fails()) {
            return ResponseCode::json(4001);
        }

        $row = $validator->validated();
        if($user = HotelGroup::create($row)) {
            return ResponseCode::json(0, '添加成功', $user);
        }

        return ResponseCode::json(5003);

    }

    public function edit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:hotel_groups',
            'code' => 'required|string|max:16',
            'en_name' => 'required|string|max:32',
            'cn_name' => 'required|string|max:32'
        ]);

        if ($validator->fails()) {
            return ResponseCode::json(4001);
        }

        
        if(HotelGroup::where('id', '!=', $request->id)->where('code', $request->code)->exists()) {
            return ResponseCode::json(4005);
        }

        $row = $validator->validated();
        $row = Arr::except($row, ['id']);
        if(HotelGroup::where('id', $request->id)->update($row)) {
            return ResponseCode::json(0, '更新成功');
        }

        return ResponseCode::json(5003);
    }

    public function destroy($id)
    {

        if(HotelGroup::destroy($id)){
            return ResponseCode::json(0, '删除成功');
        }

        return ResponseCode::json(5003);
        
    }

}
