<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\phongban;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PhongBanController extends Controller
{
    public function getAll()
    {
        // $this->authorize('view');
        $phongban = phongban::all();
        return response()->json([
            'status' => true,
            'message' => 'Lấy danh sách phòng ban thành công',
            'data' => $phongban
        ], 200);
    }
    public function getphongban($id, Request $request)
    {
        if (Gate::allows('view/id')) {
            $phongban = phongban::find($id);
            if ($phongban) {
                $phongban->load('phongban_userinfo');
            }
            return  response()->json([
                'status' => true,
                'message' => 'Lấy thông tin phòng ban thành công',
                'data' => $phongban
            ], 200);
        } elseif (!Gate::allows('view/id')) {
            response()->json([
                'status' => false,
                'message' => 'Lấy thất bại',
            ], 403);
        }
    }
    public function addSave(Request $request)
    {
        if (Gate::allows('create')) {
            $phongban = new phongban();
            $phongban->name = $request->name;
            $phongban->save();
            return $phongban ? response()->json([
                'status' => true,
                'message' => 'Thêm phòng ban thành công',
                'data' => $phongban
            ], 200) : response()->json([
                'status' => false,
                'message' => 'Thêm phòng ban không thành công',
            ], 404);;
        } elseif (!Gate::allows('create')) {
            return  response()->json([
                'status' => false,
                'message' => 'Bạn không được phép',

            ], 403);
        }
    }

    public function update($id, Request $request)
    {
        if (Gate::allows('update')) {
            $phongban = phongban::find($id);
            if ($phongban) {
                $phongban->name = $request->name;
                $phongban->save();
            }
            return $phongban ?
                response()->json([
                    'status' => true,
                    'message' => 'Sửa phòng ban thành công',
                    'data' => $phongban
                ], 200) :
                response()->json([
                    'status' => false,
                    'message' => 'Sửa phòng ban không thành công',
                ], 404);
        } elseif (!Gate::allows('create')) {
            return response()->json([
                'status' => false,
                'message' => 'Bạn không có quyền này'
            ], 403);
        }
    }

    public function delete($id, Request $request)
    {
        if (Gate::allows('delete')) {

            $phongban = phongban::destroy($id);
            return response()->json([
                'status' => true,
                'message' => 'Xóa thành công',
            ], 200);
        } elseif (!Gate::allows('delete')) {
            return response()->json([
                'status' => false,
                'message' => 'Xóa thất bại',
            ], 403);
        }
    }
}
