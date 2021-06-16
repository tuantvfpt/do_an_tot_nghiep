<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\phongban;
use Illuminate\Http\Request;

class PhongBanController extends Controller
{
    public function getAll()
    {
        $phongban = phongban::all();
        return response()->json([
            'status' => true,
            'message' => 'Lấy danh sách phòng ban thành công',
            'data' => $phongban
        ])->setStatusCode(200);
    }
    public function getphongban($id, Request $request)
    {
        $phongban = phongban::find($id);
        if ($phongban) {
            $phongban->load('phongban_userinfo');
        }
        return $phongban ?
            response()->json([
                'status' => true,
                'message' => 'Lấy thông tin phòng ban thành công',
                'data' => $phongban
            ], 200) :
            response()->json([
                'status' => false,
                'message' => 'Không tìm thấy chức vụ',
            ], 404);
    }
    public function addSave(Request $request)
    {

        $phongban = new phongban();
        $phongban->ten_phong_ban = $request->ten_phong_ban;
        $phongban->save();
        return  $phongban ?
            response()->json([
                'status' => true,
                'message' => 'Thêm phòng ban thành công',
                'data' => $phongban
            ], 200) :
            response()->json([
                'status' => false,
                'message' => 'thêm chúc vụ thất bại',
            ], 404);
    }
    public function update($id, Request $request)
    {

        $phongban = phongban::find($id);
        if ($phongban) {
            $phongban->ten_phong_ban = $request->ten_phong_ban;
            $phongban->save();
        }
        return  $phongban ?
            response()->json([
                'status' => true,
                'message' => 'Sửa phòng ban thành công',
                'data' => $phongban
            ], 200) :
            response()->json([
                'status' => false,
                'message' => 'Sửa phòng ban thất bại',
            ], 404);
    }

    public function delete($id, Request $request)
    {

        $phongban = phongban::destroy($id);
        return $phongban ?
            response()->json([
                'status' => true,
                'message' => 'Xóa thành công',
            ], 200) :
            response()->json([
                'status' => false,
                'message' => 'Xóa thất bại',
            ], 404);
    }
}
