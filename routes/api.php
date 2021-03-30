<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

//鉴权
Route::group([
    'prefix' => 'auth'
], function ($router) {
    $router->post('login', 'AuthController@login');
    //$router->post('logout', 'AuthController@logout');
    //$router->patch('refresh', 'AuthController@refresh');
});


//携程酒店集团
Route::group([
    'prefix' => 'hotelGroup'
], function ($router) {
    $router->get('lists', 'HotelGroupController@lists');
    $router->post('create', 'HotelGroupController@create');
    $router->patch('edit', 'HotelGroupController@edit');
    $router->delete('destroy/{id}', 'HotelGroupController@destroy');
});


//携程酒店集团
Route::group([
    'prefix' => 'group',
    'namespace' => 'Group'
], function ($router) {
    
    //mrt
    $router->group([
        'prefix' => 'mrt',
        'namespace' => 'Mrt'
    ], function ($router) {

        $router->group([
            'prefix' => 'hotel'
        ], function ($router) {
            $router->get('lists', 'HotelController@lists');
            $router->post('create', 'HotelController@create');
            $router->patch('edit', 'HotelController@edit');
            $router->delete('destroy/{id}', 'HotelController@destroy');
            $router->patch('changeStatus', 'HotelController@changeStatus');
            $router->get('updMappingStatus', 'HotelController@updMappingStatus');
        });

        $router->group([
            'prefix' => 'roomType'
        ], function ($router) {
            $router->get('lists', 'RoomTypeController@lists');
            $router->patch('edit', 'RoomTypeController@edit');
            $router->delete('destroy/{id}', 'RoomTypeController@destroy');
            $router->get('lookSubRooms/{id}', 'RoomTypeController@lookSubRooms');
            $router->patch('editSubRoom', 'RoomTypeController@editSubRoom');
        });
    });

});



//系统管理
Route::group([
    'prefix' => 'system',
    'middleware' => ['auth.jwt', 'permission']
], function ($router) {
    //用户管理
    $router->group([
        'prefix' => 'user'
    ], function ($router) {
        $router->get('lists', 'UserController@lists');
        $router->post('create', 'UserController@create');
        $router->patch('edit', 'UserController@edit');
        $router->delete('destroy/{id}', 'UserController@destroy');
        $router->get('getInfo', 'UserController@getInfo');
        $router->get('accessRoutes', 'UserController@accessRoutes');
        $router->get('allotRole', 'UserController@allotRole');
        $router->patch('allotRole', 'UserController@allotRole');
        $router->patch('updatePassword', 'UserController@updatePassword');
    });

    //角色管理
    $router->group([
        'prefix' => 'role'
    ], function ($router) {
        $router->get('lists', 'RoleController@lists');
        $router->post('create', 'RoleController@create');
        $router->patch('edit', 'RoleController@edit');
        $router->delete('destroy/{id}', 'RoleController@destroy');
        $router->get('allotPermission', 'RoleController@allotPermission');
        $router->patch('allotPermission', 'RoleController@allotPermission');
    });

    //菜单管理
    $router->group([
        'prefix' => 'menu'
    ], function ($router) {
        $router->get('lists', 'MenuController@lists');
        $router->get('getSelectOptions', 'MenuController@getSelectOptions');
        $router->get('getTopLevel', 'MenuController@getTopLevel');
        $router->post('create', 'MenuController@create');
        $router->patch('edit', 'MenuController@edit');
        $router->delete('destroy/{id}', 'MenuController@destroy');
    });
});

