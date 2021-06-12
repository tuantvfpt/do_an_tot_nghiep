<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\chucvu;
use Illuminate\Http\Request;

class ChucVuController extends Controller
{
    public function getAll()
    {
        $chucvu = chucvu::all();
        return response()->json([
            'status' => true,
            'message' => 'Lấy danh sách chức vụ thành công',
            'data' => $chucvu
        ])->setStatusCode(200);
    }
    public function getchucvu($id, Request $request)
    {
        $chucvu = chucvu::find($id);
        if ($chucvu) {
            $chucvu->load('chucvu_userinfo');
        }
        return $chucvu ?
            response()->json([
                'status' => true,
                'message' => 'Lấy thông tin chức vụ thành công',
                'data' => $chucvu
            ], 200) :
            response()->json([
                'status' => false,
                'message' => 'Không tìm thấy chức vụ',
            ], 200);
    }
    public function addSave(Request $request)
    {

        $chucvu = new chucvu();
        $chucvu->ten_chuc_vu = $request->ten_chuc_vu;
        dd($chucvu);
        $chucvu->save();
        return  $chucvu ?
            response()->json([
                'status' => true,
                'message' => 'Thêm chức vụ thành công',
                'data' => $chucvu
            ], 200) :
            response()->json([
                'status' => false,
                'message' => 'thêm chúc vụ thất bại',
            ], 200);
    }

    public function update($id, Request $request)
    {

        $chucvu = chucvu::find($id);
        if ($chucvu) {
            $chucvu->ten_chuc_vu = $request->ten_chuc_vu;
            $chucvu->save();
        }
        return  $chucvu ?
            response()->json([
                'status' => true,
                'message' => 'Sửa chức vụ thành công',
                'data' => $chucvu
            ], 200) :
            response()->json([
                'status' => false,
                'message' => 'Sửa chức vụ thất bại',
            ], 200);
    }

    public function delete($id, Request $request)
    {

        $chucvu = chucvu::destroy($id);
        return $chucvu ?
            response()->json([
                'status' => true,
                'message' => 'Xóa thành công',
            ], 200) :
            response()->json([
                'status' => false,
                'message' => 'Xóa thất bại',
            ], 200);
    }
}
