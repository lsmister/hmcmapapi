<?php

namespace App\Http\Controllers\Api\Group\Mrt;

use App\Models\HotelGroup;
use App\Models\Mrt\MrtHotel;

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
        $model = MrtHotel::query();
        
        if($request->filled('name')) {
            $model = $model->where('cn_name', 'like', '%'.$request->get('name').'%')
                ->orWhere('en_name', 'like', '%'.$request->get('name').'%');
        }

        if($request->filled('code')) {
            $model = $model->where('code', $request->get('code'));
        }

        if($request->filled('ctrip_hotel_code')) {
            $model = $model->where('ctrip_hotel_code', $request->get('ctrip_hotel_code'));
        }

        $list = $model->paginate($request->limit);

        return ResponseCode::json(0, '获取列表成功', $list);
    }
    
    
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:10|unique:mrt_hotels',
            'en_name' => 'required|string|max:32',
            'cn_name' => 'required|string|max:32',
            'longitude' => 'required|string|max:30',
            'latitude' => 'required|string|max:30',
            'country' => 'required|string|max:30',
            'address' => 'required|string|max:191',
            'phone' => 'required|string|max:50',
            'cur' => 'required|string|max:10',
            'address_visible' => 'required|string|in:true,false'
        ]);

        if ($validator->fails()) {
            return ResponseCode::json(4001);
        }

        $row = $validator->validated();
        if($user = MrtHotel::create($row)) {
            return ResponseCode::json(0, '添加成功', $user);
        }

        return ResponseCode::json(5003);

    }

    public function edit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:mrt_hotels',
            'code' => 'required|string|max:10',
            'en_name' => 'required|string|max:32',
            'cn_name' => 'required|string|max:32',
            'longitude' => 'required|string|max:30',
            'latitude' => 'required|string|max:30',
            'country' => 'required|string|max:30',
            'address' => 'required|string|max:191',
            'phone' => 'required|string|max:50',
            'cur' => 'required|string|max:10',
            'address_visible' => 'required|string|in:true,false'
        ]);

        if ($validator->fails()) {
            return ResponseCode::json(4001);
        }
        
        if(MrtHotel::where('id', '!=', $request->id)->where('code', $request->code)->exists()) {
            return ResponseCode::json(4005);
        }

        $row = $validator->validated();
        $row = Arr::except($row, ['id', 'code']);
        if(MrtHotel::where('id', $request->id)->update($row)) {
            return ResponseCode::json(0, '更新成功');
        }

        return ResponseCode::json(5003);
    }

    public function destroy($id)
    {

        if(MrtHotel::destroy($id)){
            return ResponseCode::json(0, '删除成功');
        }

        return ResponseCode::json(5003);
        
    }


    public function changeStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:mrt_hotels',
            'status' => 'required|numeric|in:0,1'
        ]);

        if ($validator->fails()) {
            return ResponseCode::json(4001);
        }

        $row = $validator->validated();
        if (MrtHotel::where('id', $row['id'])->update(['status' => $row['status']])) {
            return ResponseCode::json(0, '操作成功');
        }

        return ResponseCode::json(5003);
    }

}
