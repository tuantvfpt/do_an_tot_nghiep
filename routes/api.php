<?php

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
    Route::get('getAll', [UserController::class, 'getAll'])->name('getAllUser.index');
    Route::get('getUser/{id}', [UserController::class, 'getUser'])->name('getUser.index');
    Route::post('update', [UserController::class, 'update'])->name('update.index');
    Route::post('delete/{id}', [UserController::class, 'delete'])->name('delete.index');
});
Route::group(['prefix' => 'chucvu'], function () {
    Route::get('getAll', [UserController::class, 'getAll'])->name('getAllUser.index');
    Route::get('getUser/{id}', [UserController::class, 'getUser'])->name('getUser.index');
    Route::post('update/{id}', [UserController::class, 'update'])->name('update.index');
    Route::post('delete/{id}', [UserController::class, 'delete'])->name('delete.index');
});
