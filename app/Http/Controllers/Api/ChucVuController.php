<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\chucvu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ChucVuController extends Controller
{
    public function getAll()
    {
        if (Gate::allows('view')) {
            $chucvu = chucvu::all();
            $response =  response()->json([
                'status' => true,
                'message' => 'Lấy danh sách chức vụ thành công',
                'data' => $chucvu
            ])->setStatusCode(200);
        } else {
            $response =  response()->json([
                'status' => false,
                'message' => 'Bạn không được phép',
            ], 403);
        }
        return $response;
    }
    public function getchucvu($id, Request $request)
    {
        if (Gate::allows('view/id')) {
            $chucvu = chucvu::find($id);
            if ($chucvu) {
                $chucvu->load('chucvu');
            }
            $response = $chucvu ?
                response()->json([
                    'status' => true,
                    'message' => 'Lấy thông tin chức vụ thành công',
                    'data' => $chucvu
                ], 200) :
                response()->json([
                    'status' => false,
                    'message' => 'Không tìm thấy chức vụ',
                ], 404);
        } else {
            $response = response()->json([
                'status' => false,
                'message' => 'Bạn không được phép',
            ], 403);
        }
        return $response;
    }
    public function addSave(Request $request)
    {
        if (Gate::allows('create')) {
            $chucvu = new chucvu();
            $chucvu->name = $request->name;
            $chucvu->save();
            $response =   $chucvu ?
                response()->json([
                    'status' => true,
                    'message' => 'Thêm chức vụ thành công',
                    'data' => $chucvu
                ], 200) :
                response()->json([
                    'status' => false,
                    'message' => 'thêm chúc vụ thất bại',
                ], 404);
        } else {
            $response =  response()->json([
                'status' => false,
                'message' => 'Bạn không được phép',
            ], 403);
        }
        return $response;
    }

    public function update($id, Request $request)
    {
        if (Gate::allows('update')) {
            $chucvu = chucvu::find($id);
            if ($chucvu) {
                $chucvu->name = $request->name;
                $chucvu->save();
            }
            $response =   $chucvu ?
                response()->json([
                    'status' => true,
                    'message' => 'Sửa chức vụ thành công',
                    'data' => $chucvu
                ], 200) :
                response()->json([
                    'status' => false,
                    'message' => 'Sửa chức vụ thất bại',
                ], 404);
        } else {
            $response =  response()->json([
                'status' => false,
                'message' => 'Bạn không được phép',
            ], 403);
        }
        return $response;
    }

    public function delete($id, Request $request)
    {
        if (Gate::allows('delete')) {
            $chucvu = chucvu::find($id);
            if ($chucvu) {
                $chucvu->delete();
            }
            $response =  $chucvu ?
                response()->json([
                    'status' => true,
                    'message' => 'Xóa thành công',
                ], 200) :
                response()->json([
                    'status' => false,
                    'message' => 'Xóa thất bại',
                ], 404);
        } else {
            $response =  response()->json([
                'status' => false,
                'message' => 'Bạn không được phép',
            ], 403);
        }
        return $response;
    }
}
