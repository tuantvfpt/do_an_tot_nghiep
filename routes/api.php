<?php

use App\Http\Controllers\Api\CalendarLeaveController;
use App\Http\Controllers\Api\ChucVuController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\LichChamCong;
use App\Http\Controllers\Api\LichChamCongController;
use App\Http\Controllers\Api\LuongController;
use App\Http\Controllers\Api\PhongBanController;
use App\Http\Controllers\Api\UserController;
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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });
Route::group(['prefix' => 'dashboard'], function () {
    Route::get('/', [DashboardController::class, 'index'])->name('getdata');
});
Route::group(['prefix' => 'user'], function () {
    Route::get('/', [UserController::class, 'getAll'])->name('getAllUser');
    Route::get('/{id}', [UserController::class, 'getUser'])->name('getUser');
    Route::post('getlist', [UserController::class, 'getlist'])->name('getListChucVuPhongBan');
    Route::post('update/{id}', [UserController::class, 'update'])->name('update');
    Route::post('create', [UserController::class, 'addSaveUser'])->name('addUser');
    Route::post('delete/{id}', [UserController::class, 'delete'])->name('delete');
});
Route::group(['prefix' => 'phongban'], function () {
    Route::get('/', [PhongBanController::class, 'getAll'])->name('getAll');
    Route::get('/{id}', [PhongBanController::class, 'getphongban'])->name('getphongban');
    Route::post('create', [PhongBanController::class, 'addSave'])->name('addSave');
    Route::post('update/{id}', [PhongBanController::class, 'update'])->name('update');
    Route::post('delete/{id}', [PhongBanController::class, 'delete'])->name('delete');
});
Route::group(['prefix' => 'chucvu'], function () {
    Route::get('/', [ChucVuController::class, 'getAll'])->name('getAll');
    Route::get('/{id}', [ChucVuController::class, 'getchucvu'])->name('getchucvu');
    Route::post('create', [ChucVuController::class, 'addSave'])->name('addSave');
    Route::post('update/{id}', [ChucVuController::class, 'update'])->name('update');
    Route::post('delete/{id}', [ChucVuController::class, 'delete'])->name('delete');
});
Route::group(['prefix' => 'lichchamcong'], function () {
    Route::get('/', [LichChamCongController::class, 'getAll'])->name('getAll');
    Route::get('/{id}', [LichChamCongController::class, 'getdetail'])->name('getdetail');
    Route::post('diemdanh', [LichChamCongController::class, 'diemdanh'])->name('diemdanh');
    Route::post('update/{id}', [LichChamCongController::class, 'update'])->name('update');
});
Route::group(['prefix' => 'luong'], function () {
    Route::get('/', [LuongController::class, 'getAll'])->name('getAll');
    Route::get('6month', [LuongController::class, 'getSalary6Month'])->name('getSalary6Month');
    Route::get('1year', [LuongController::class, 'getSalary1year'])->name('getSalary1year');
    Route::get('/{id}', [LuongController::class, 'getdetail'])->name('getdetail');
    Route::post('tinhluong', [LuongController::class, 'tinhluong'])->name('tinhluong');

});
Route::group(['prefix' => 'lichxinnghi'], function () {
    Route::get('/', [LuongController::class, 'getAll'])->name('getAll');
    // Route::get('/{id}', [LuongController::class, 'getdetail'])->name('getdetail');
    Route::post('create', [CalendarLeaveController::class, 'create'])->name('create');
    Route::post('update_day', [CalendarLeaveController::class, 'update_day'])->name('update_day');
});
