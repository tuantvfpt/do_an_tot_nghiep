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
        if(Gate::allows('view')){
        $chucvu = chucvu::all();
    }elseif(!Gate::allows('view')){
        return response()->json([
            'status' => false,
            'message' => 'Bạn không được phép',
        ],403);
    }
        return response()->json([
            'status' => true,
            'message' => 'Lấy danh sách chức vụ thành công',
            'data' => $chucvu
        ])->setStatusCode(200);
     
    }
    public function getchucvu($id, Request $request)
    {
        if(Gate::allows('view/id')){
        $chucvu = chucvu::find($id);
        if ($chucvu) {
            $chucvu->load('chucvu_userinfo');
        }
        }elseif(!Gate::allows('view/id')){
        return response()->json([
            'status' => false,
            'message' => 'Bạn không được phép',
        ],403);
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
            ], 404);
        
    }
    public function addSave(Request $request)
    {
        if(Gate::allows('create')){
        $chucvu = new chucvu();
        $chucvu->name = $request->name;
        $chucvu->save();
        }elseif(!Gate::allows('create')){
        return response()->json([
            'status' => false,
            'message' => 'Bạn không được phép',
        ],403);
        }
        return  $chucvu ?
            response()->json([
                'status' => true,
                'message' => 'Thêm chức vụ thành công',
                'data' => $chucvu
            ], 200) :
            response()->json([
                'status' => false,
                'message' => 'thêm chúc vụ thất bại',
            ], 404);
     
    }

    public function update($id, Request $request)
    {
        if(Gate::allows('update')){
        $chucvu = chucvu::find($id);
        if ($chucvu) {
            $chucvu->name = $request->name;
            $chucvu->save();
        }
        }elseif(!Gate::allows('update')){
            return response()->json([
                'status' => false,
                'message' => 'Bạn không được phép',
            ],403);
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
            ], 404);
       
    }

    public function delete($id, Request $request)
    {
        if(Gate::allows('delete')){
        $chucvu = chucvu::destroy($id);
        }elseif(!Gate::allows('delete')){
            return response()->json([
                'status' => false,
                'message' => 'Bạn không được phép',
            ],403);
        }
        return $chucvu ?
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
