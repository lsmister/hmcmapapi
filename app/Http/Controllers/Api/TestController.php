<?php

namespace App\Http\Controllers\Api;

use App\Models\Role;
use App\Models\Menu;

use App\Libs\CtripStaticApi;

use App\Http\Controllers\Controller;
use App\Response\ResponseCode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class TestController extends Controller
{
    protected $user;

    protected $ctripApi;

    public function __construct()
    {
        $this->user = Auth::user();
        $this->ctripApi = new CtripStaticApi();
    }
    
    public function hotelPush()
    {
        $hotelInfo['code'] = 'HB50532';
        $hotelInfo['en_name'] = 'Clarion Inn Page - Lake Powell';
        $hotelInfo['cn_name'] = '佩奇-鲍威尔湖克拉丽奥酒店';
        $hotelInfo['currency'] = 'RMB';
        $hotelInfo['latitude'] = '36.91526';
        $hotelInfo['longitude'] = '-111.45471';
        $hotelInfo['country_name'] = 'United States (the)';
        $hotelInfo['province_name'] = 'Arizona';
        $hotelInfo['address'] = '751 SOUTH NAVAJO DRIVE';
        $hotelInfo['phone_type'] = 'Phone';
        $hotelInfo['phone_country_code'] = '66';
        $hotelInfo['phone_area_code'] = '86';
        $hotelInfo['phone_main_code'] = '9000';

        return $this->ctripApi->hotelStaticPush($hotelInfo);
    }


    public function basicRoomPush()
    {
        
    }

    public function hotelinfo()
    {
        $hotelids = [
            '71919724','71919660'
        ];

        return $this->ctripApi->hotelInfoSearch($hotelids);
    }


    public function mappingInfoSearch()
    {
        $hotelids = [
            '71919724','71919660'
        ];

        return $this->ctripApi->mappingInfoSearch(false, $hotelids);
    }

}
