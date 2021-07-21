<?php

use App\Http\Controllers\Api\CalendarLeaveController;
use App\Http\Controllers\Api\ChucVuController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\LichChamCongController;
use App\Http\Controllers\Api\LuongController;
use App\Http\Controllers\Api\PhongBanController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PrizefineController;
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
Route::post('forget_password', [UserController::class, 'forget_password'])->name('forget_password');
Route::post('/login', [AuthController::class, 'login']);
Route::group(['middleware' => 'auth'], function () {
    Route::group(['prefix' => 'auth'], function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/user-profile', [AuthController::class, 'userProfile']);
    });

    Route::group(['prefix' => 'dashboard'], function () {
        Route::get('/', [DashboardController::class, 'index'])->name('getdata');
        Route::get('/show_calendar', [DashboardController::class, 'show_lich'])->name('show_lich');
        Route::get('/get_user_late_early', [DashboardController::class, 'get_user_late_early'])->name('get_user_late_early');
    });
    Route::group(['prefix' => 'user/'], function () {
        Route::get('/', [UserController::class, 'getAll'])->name('getAllUser');
        Route::get('getdetail/{id}', [UserController::class, 'getUser'])->name('getUser');
        Route::get('getlist', [UserController::class, 'getlist'])->name('getListChucVuPhongBan');
        Route::post('update/{id}', [UserController::class, 'update'])->name('update');
        Route::post('create', [UserController::class, 'addSaveUser'])->name('addUser');
        Route::post('delete/{id}', [UserController::class, 'delete'])->name('delete');
        Route::post('changepassword', [UserController::class, 'changepassword'])->name('changepassword');
        Route::get('getListUser', [UserController::class, 'ListUsers'])->name('getListUser');
        Route::get('listAll', [UserController::class, 'ListAll'])->name('ListAll');
    });
    Route::group(['prefix' => 'phongban'], function () {
        Route::get('/', [PhongBanController::class, 'getAll'])->name('getAll');
        Route::get('getdetail/{id}', [PhongBanController::class, 'getphongban'])->name('getphongban');
        Route::post('create', [PhongBanController::class, 'addSave'])->name('addSave');
        Route::post('update/{id}', [PhongBanController::class, 'update'])->name('update');
        Route::post('delete/{id}', [PhongBanController::class, 'delete'])->name('delete');
    });
    Route::group(['prefix' => 'chucvu'], function () {
        Route::get('/', [ChucVuController::class, 'getAll'])->name('getAll');
        Route::get('getdetail/{id}', [ChucVuController::class, 'getchucvu'])->name('getchucvu');
        Route::post('create', [ChucVuController::class, 'addSave'])->name('addSave');
        Route::post('update/{id}', [ChucVuController::class, 'update'])->name('update');
        Route::post('delete/{id}', [ChucVuController::class, 'delete'])->name('delete');
    });
    Route::group(['prefix' => 'lichchamcong'], function () {
        Route::get('/', [LichChamCongController::class, 'getAll'])->name('getAll');
        Route::get('getdetail/{id}', [LichChamCongController::class, 'getdetail'])->name('getdetail');
        Route::post('update/{id}', [LichChamCongController::class, 'update'])->name('update');
        Route::post('create', [LichChamCongController::class, 'create'])->name('create');
        Route::post('update_OT', [LichChamCongController::class, 'update_OT'])->name('update_OT');
        Route::get('/getListByUser', [LichChamCongController::class, 'getListByUser'])->name('getListByUser');
        Route::post('diemdanh', [LichChamCongController::class, 'diemdanh'])->name('diemdanh');
        // Route::post('total_gross_salary', [LichChamCongController::class, 'total_gross_salary'])->name('total_gross_salary');
    });
    Route::group(['prefix' => 'luong'], function () {
        Route::get('/', [LuongController::class, 'getAll'])->name('getAll');
        Route::get('getdetail/{id}', [LuongController::class, 'getdetail'])->name('getdetail');
        Route::post('tinhluong', [LuongController::class, 'tinhluong'])->name('tinhluong');
        Route::get('getSalaryByUser', [LuongController::class, 'getSalaryByUser'])->name('getSalaryByUser');
        Route::post('tra_luong', [LuongController::class, 'tra_luong'])->name('tra_luong');
    });
    Route::group(['prefix' => 'lichxinnghi'], function () {
        Route::get('/', [CalendarLeaveController::class, 'getAll'])->name('getAll');
        Route::get('user_leave', [CalendarLeaveController::class, 'get_lich_nghi'])->name('get_user_leave');
        Route::post('comfig/{id}', [CalendarLeaveController::class, 'comfig'])->name('comfig');
        Route::get('getdetail/{id}', [LuongController::class, 'getdetail'])->name('getdetail');
        Route::post('create', [CalendarLeaveController::class, 'create'])->name('create');
        // Route::post('update_day', [CalendarLeaveController::class, 'update_day'])->name('update_day');
        Route::get('total_day', [CalendarLeaveController::class, 'get_company_leave'])->name('get_company_leave');
    });
    Route::group(['prefix' => 'prize_fine_money'], function () {
        Route::get('/', [PrizefineController::class, 'index'])->name('getAll');
        Route::post('create', [PrizefineController::class, 'create'])->name('create');
        Route::post('update/{id}', [PrizefineController::class, 'update'])->name('update');
        Route::post('delete/{id}', [PrizefineController::class, 'delete'])->name('delete');
    });
});
