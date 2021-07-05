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
        if(Gate::allows('view')){
        $phongban = phongban::all();
    }elseif(!Gate::allows('view')){
        return response()->json([
            'status' => false,
            'message' => 'Bạn không được phép',
            
        ], 403);
    }
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
            
        } elseif (!Gate::allows('view/id')) {
            response()->json([
                'status' => false,
                'message' => 'Bạn không được phép',
            ], 403);
        }
        return  response()->json([
            'status' => true,
            'message' => 'Lấy thông tin phòng ban thành công',
            'data' => $phongban
        ], 200);
    }
    public function addSave(Request $request)
    {
        if (Gate::allows('create')) {
            $phongban = new phongban();
            $phongban->name = $request->name;
            $phongban->save();
        } elseif (!Gate::allows('create')) {
            return  response()->json([
                'status' => false,
                'message' => 'Bạn không được phép',
            ], 403);
        }
            return $phongban ? response()->json([
                'status' => true,
                'message' => 'Thêm phòng ban thành công',
                'data' => $phongban
            ], 200) : response()->json([
                'status' => false,
                'message' => 'Thêm phòng ban không thành công',
            ], 404);;
      
    }

    public function update($id, Request $request)
    {
        if (Gate::allows('update')) {
            $phongban = phongban::find($id);
            if ($phongban) {
                $phongban->name = $request->name;
                $phongban->save();
            }
        } elseif (!Gate::allows('create')) {
            return response()->json([
                'status' => false,
                'message' => 'Bạn không được phép',
            ], 403);
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
       
    }

    public function delete($id, Request $request)
    {
        if (Gate::allows('delete')) {
            $phongban = phongban::destroy($id);
        } elseif (!Gate::allows('delete')) {
            return response()->json([
                'status' => false,
                'message' => 'Bạn không được phép',
            ], 403);
        }
            return  $phongban ? response()->json([
                'status' => true,
                'message' => 'Xóa thành công',
            ], 200) : response()->json([
                'status' => false,
                'message' => 'Xóa thất bại'
            ], 403);
        
    }
}
