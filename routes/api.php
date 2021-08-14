<?php

use App\Http\Controllers\Api\CalendarLeaveController;
use App\Http\Controllers\Api\ChucVuController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\LichChamCongController;
use App\Http\Controllers\Api\LuongController;
use App\Http\Controllers\Api\PhongBanController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LichTangCaController;
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
Route::get('forget_password', [UserController::class, 'forget_password'])->name('forget_password');
Route::post('/login', [AuthController::class, 'login']);
Route::group(['middleware' => 'auth'], function () {
    Route::group(['prefix' => 'auth'], function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/user-profile', [AuthController::class, 'userProfile']);
    });

    Route::group(['prefix' => 'dashboard'], function () {
        Route::get('list_comfig', [DashboardController::class, 'list_comfig'])->name('list_comfig');
        Route::post('comfig/{id}', [DashboardController::class, 'comfig'])->name('comfig');
        Route::get('show_calendar', [DashboardController::class, 'show_lich'])->name('show_lich');
        Route::get('luong_theo_thang', [DashboardController::class, 'luong_theo_thang'])->name('luong_theo_thang');
        Route::get('get_user_late_early', [DashboardController::class, 'get_user_late_early'])->name('get_user_late_early');
        Route::get('total_leave_have_gross_by_user', [DashboardController::class, 'total_leave_have_gross_by_user'])->name('total_leave_have_gross_by_user');
        Route::get('total_day_off_by_user', [DashboardController::class, 'total_day_off_by_user'])->name('total_day_off_by_user');
        Route::get('total_salary_by_user', [DashboardController::class, 'total_salary_by_user'])->name('total_salary_by_user');
        Route::get('total_user', [DashboardController::class, 'total_user'])->name('total_user');
        Route::get('total_phong_ban', [DashboardController::class, 'total_phong_ban'])->name('total_phong_ban');
        Route::get('total_user_in_phong_ban', [DashboardController::class, 'total_user_in_phong_ban'])->name('total_user_in_phong_ban');
        Route::get('total_user_work', [DashboardController::class, 'total_user_work'])->name('total_user_work');
        Route::get('total_user_off', [DashboardController::class, 'total_user_off'])->name('total_user_off');
        Route::get('total_work_by_user', [DashboardController::class, 'total_work_by_user'])->name('total_work_by_user');
    });
    Route::group(['prefix' => 'user/'], function () {
        Route::get('/', [UserController::class, 'getAll'])->name('getAllUser');
        Route::get('getdetail/{id}', [UserController::class, 'getUser'])->name('getUser');
        Route::post('update/{id}', [UserController::class, 'update'])->name('update');
        Route::post('create', [UserController::class, 'addSaveUser'])->name('addUser');
        Route::delete('delete/{id}', [UserController::class, 'delete'])->name('delete');
        Route::post('changepassword', [UserController::class, 'changepassword'])->name('changepassword');
        Route::get('getListUser', [UserController::class, 'ListUsers'])->name('getListUser');
        Route::get('listAll', [UserController::class, 'ListAll'])->name('ListAll');
        Route::get('my_info', [UserController::class, 'get_user_current'])->name('get_user_current');
    });
    Route::group(['prefix' => 'phongban'], function () {
        Route::get('/', [PhongBanController::class, 'getAll'])->name('getAll');
        Route::get('getdetail/{id}', [PhongBanController::class, 'getphongban'])->name('getphongban');
        Route::post('create', [PhongBanController::class, 'addSave'])->name('addSave');
        Route::post('update/{id}', [PhongBanController::class, 'update'])->name('update');
        Route::delete('delete/{id}', [PhongBanController::class, 'delete'])->name('delete');
    });
    Route::group(['prefix' => 'chucvu'], function () {
        Route::get('/', [ChucVuController::class, 'getAll'])->name('getAll');
        Route::get('getdetail/{id}', [ChucVuController::class, 'getchucvu'])->name('getchucvu');
        Route::post('create', [ChucVuController::class, 'addSave'])->name('addSave');
        Route::post('update/{id}', [ChucVuController::class, 'update'])->name('update');
        Route::delete('delete/{id}', [ChucVuController::class, 'delete'])->name('delete');
    });
    Route::group(['prefix' => 'lichchamcong'], function () {
        Route::get('/', [LichChamCongController::class, 'getAll'])->name('getAll');
        Route::get('getdetail/{id}', [LichChamCongController::class, 'getdetail'])->name('getdetail');
        Route::post('update/{id}', [LichChamCongController::class, 'update'])->name('update');
        Route::post('create', [LichChamCongController::class, 'create'])->name('create');
        Route::post('update_OT', [LichChamCongController::class, 'update_OT'])->name('update_OT');
        Route::get('/getListByUser', [LichChamCongController::class, 'getListByUser'])->name('getListByUser');
        Route::post('diemdanh', [LichChamCongController::class, 'diemdanh'])->name('diemdanh');
        Route::post('update_status', [LichChamCongController::class, 'update_status'])->name('update_status');
        Route::get('/getListOt', [LichChamCongController::class, 'list_OT'])->name('getListOt');
        Route::get('/BieuDoLichDiLam', [LichChamCongController::class, 'BieuDoLichDiLam'])->name('BieuDoLichDiLam');
        // Route::post('total_gross_salary', [LichChamCongController::class, 'total_gross_salary'])->name('total_gross_salary');
    });
    Route::group(['prefix' => 'luong'], function () {
        Route::get('/', [LuongController::class, 'getAll'])->name('getAll');
        Route::get('getdetail/{id}', [LuongController::class, 'getdetail'])->name('getdetail');
        Route::post('luong', [LuongController::class, 'luong'])->name('luong');
        Route::get('getSalaryByUser', [LuongController::class, 'getSalaryByUser'])->name('getSalaryByUser');
        Route::post('tra_luong/{id}', [LuongController::class, 'tra_luong'])->name('tra_luong');
    });
    Route::group(['prefix' => 'tangca'], function () {
        Route::get('danh_sach_tang_ca_by_user', [LichTangCaController::class, 'danh_sach_tang_ca_by_user'])->name('danh_sach_tang_ca_by_user');
        Route::get('danh_sach_tang_ca_by_leader', [LichTangCaController::class, 'danh_sach_tang_ca_by_leader'])->name('danh_sach_tang_ca_by_leader');
        Route::post('xac_nhan_tang_ca/{id}', [LichTangCaController::class, 'xac_nhan_tang_ca'])->name('xac_nhan_tang_ca');
        Route::post('addTangCa', [LichTangCaController::class, 'addTangCa'])->name('addTangCa');
        Route::delete('delete/{id}', [LichTangCaController::class, 'delete'])->name('delete');
        Route::delete('destroy/{id}', [LichTangCaController::class, 'destroy'])->name('destroy');
        Route::get('getAllDelete', [LichTangCaController::class, 'getAllDelete'])->name('getAllDelete');
        Route::post('khoi_phuc/{id}', [LichTangCaController::class, 'khoi_phuc'])->name('khoi_phuc');
    });
    Route::group(['prefix' => 'lichxinnghi'], function () {
        Route::get('/', [CalendarLeaveController::class, 'getAll'])->name('getAll');
        Route::get('getAllDelete', [CalendarLeaveController::class, 'getAllDelete'])->name('getAllDelete');
        Route::get('getdetail/{id}', [CalendarLeaveController::class, 'getdetail'])->name('getdetail');
        Route::post('create', [CalendarLeaveController::class, 'create'])->name('create');
        Route::delete('delete/{id}', [CalendarLeaveController::class, 'delete'])->name('delete');
        Route::delete('destroy/{id}', [CalendarLeaveController::class, 'destroy'])->name('destroy');
        Route::post('khoi_phuc/{id}', [CalendarLeaveController::class, 'khoi_phuc'])->name('khoi_phuc');
        Route::get('total_day', [CalendarLeaveController::class, 'get_company_leave'])->name('get_company_leave');
        Route::post('update_leave/{id}', [CalendarLeaveController::class, 'update_calenda'])->name('update_calenda');
        Route::get('/getAllByUser', [CalendarLeaveController::class, 'getAllByUser'])->name('getAllByUser');
    });
    Route::group(['prefix' => 'prize_fine_money'], function () {
        Route::get('/', [PrizefineController::class, 'index'])->name('getAll');
        Route::get('getdetail/{id}', [PrizefineController::class, 'getdetail'])->name('getdetail_prize');
        Route::post('create', [PrizefineController::class, 'create'])->name('create');
        Route::post('update/{id}', [PrizefineController::class, 'update'])->name('update');
        Route::delete('delete/{id}', [PrizefineController::class, 'delete'])->name('delete');
        Route::delete('destroy/{id}', [PrizefineController::class, 'destroy'])->name('destroy');
        Route::get('getAllDelete', [PrizefineController::class, 'getAllDelete'])->name('getAllDelete');
        Route::post('khoi_phuc/{id}', [PrizefineController::class, 'khoi_phuc'])->name('khoi_phuc');
    });
});
