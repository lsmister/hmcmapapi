<?php

namespace App\Http\Controllers\Api\Group\Mrt;

use App\Models\Mrt\MrtRoomType;
use App\Models\Mrt\MrtSubRoom;

use App\Libs\CtripStaticApi;

use App\Http\Controllers\Controller;
use App\Response\ResponseCode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Batch;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Arr;

class RoomTypeController extends Controller
{

    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    public function lists(Request $request)
    {
        $model = MrtRoomType::query();
        
        if($request->filled('hotel_code')) {
            $model = $model->where('hotel_code', $request->get('hotel_code'));
        }

        if($request->filled('room_type_code')) {
            $model = $model->where('room_type_code', $request->get('room_type_code'));
        }

        if($request->filled('ctrip_room_code')) {
            $model = $model->where('ctrip_hotel_code', $request->get('ctrip_room_code'));
        }

        $list = $model->paginate($request->limit);

        return ResponseCode::json(0, '获取列表成功', $list);
    }
    
    
    public function create(Request $request)
    {
        

    }

    public function edit(Request $request)
    {
        // dump($request->all());
        $row = MrtRoomType::where('id', $request->id)->first()->toArray();
        $diff = array_diff_assoc($request->all(), $row);
        
        if (empty($diff)) {
            return ResponseCode::json(0, '更新成功');
        }
        
        if(MrtRoomType::where('id', $request->id)->update($diff)) {
           return ResponseCode::json(0, '更新成功');
        }

        return ResponseCode::json(5003);
    }

    public function destroy($id)
    {

        if(MrtRoomType::destroy($id)){
            return ResponseCode::json(0, '删除成功');
        }

        return ResponseCode::json(5003);
        
    }


    public function lookSubRooms($id)
    {
        $rtype = MrtRoomType::findOrFail($id);

        $subRooms = MrtSubRoom::where('hotel_code', $rtype->hotel_code)
                        ->where('room_type_code', $rtype->room_type_code)
                        ->get();

        return ResponseCode::json(0, '获取列表成功', $subRooms);
    }


    public function editSubRoom(Request $request)
    {
        $c = new CtripStaticApi();

        $a = $c->mappingInfoSearch(true);
        dd($a);

        $data = Arr::where($request->all(), function ($value) {
            return $value !== null;
        });

        MrtSubRoom::where('id', $data['id'])->update(Arr::except($data, ['id']));

        return ResponseCode::json(0, '更新成功');
    }


}
