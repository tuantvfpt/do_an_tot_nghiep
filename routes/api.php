<?php

use App\Http\Controllers\Api\ChucVuController;
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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::group(['prefix' => 'user'], function () {
    Route::get('getAll', [UserController::class, 'getAll'])->name('getAllUser');
    Route::get('getUser/{id}', [UserController::class, 'getUser'])->name('getUser');
    Route::post('getlist', [UserController::class, 'getlist'])->name('getlist');
    Route::post('update/{id}', [UserController::class, 'update'])->name('update');
    Route::post('createUser', [UserController::class, 'addUser'])->name('addUser');
    Route::post('delete/{id}', [UserController::class, 'delete'])->name('delete');
});
Route::group(['prefix' => 'phongban'], function () {
    Route::get('getAll', [PhongBanController::class, 'getAll'])->name('getAll');
    Route::get('getphongban/{id}', [PhongBanController::class, 'getphongban'])->name('getphongban');
    Route::post('createphongban', [PhongBanController::class, 'addSave'])->name('addSave');
    Route::post('update/{id}', [PhongBanController::class, 'update'])->name('update');
    Route::post('delete/{id}', [PhongBanController::class, 'delete'])->name('delete');
});
Route::group(['prefix' => 'chucvu'], function () {
    Route::get('getAll', [ChucVuController::class, 'getAll'])->name('getAll');
    Route::get('getchucvu/{id}', [ChucVuController::class, 'getchucvu'])->name('getchucvu');
    Route::post('createchucvu', [ChucVuController::class, 'addSave'])->name('addSave');
    Route::post('update/{id}', [ChucVuController::class, 'update'])->name('update');
    Route::post('delete/{id}', [ChucVuController::class, 'delete'])->name('delete');
});
