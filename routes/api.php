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


//系统管理
Route::group([
    'prefix' => 'system',
    'middleware' => ['auth.jwt']
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
        $router->match(['get', 'post'], 'allotRole', 'UserController@allotRole');
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
        $router->get('getMenuInfo/{id}', 'MenuController@getMenuInfo');
        $router->get('getLevelOptions', 'MenuController@getLevelOptions');
        $router->post('create', 'MenuController@create');
        $router->patch('edit', 'MenuController@edit');
        $router->delete('destroy/{id}', 'MenuController@destroy');
    });
});


