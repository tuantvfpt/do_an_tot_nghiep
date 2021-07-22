<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\phongban;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class PhongBanController extends Controller
{
    public function __construct(phongban $phongban)
    {
        $this->phongban = $phongban;
    }
    protected $validate_phongban = [
        'name' => 'required',
    ];
    public function getAll(Request $request)
    {
        if (Gate::allows('view')) {
            $phongban = $this->phongban;
            if (!empty($request->keyword)) {
                $phongban =  $phongban->Where(function ($query) use ($request) {
                    $query->where('name', 'like', "%" . $request->keyword . "%");
                });
            }
            $phongban = $phongban->paginate(($request->limit != null) ? $request->limit : 10);
            $response =  response()->json([
                'status' => true,
                'message' => 'Lấy danh sách phòng ban thành công',
                'data' => $phongban->items(),
                'meta' => [
                    'total'      => $phongban->total(),
                    'perPage'    => $phongban->perPage(),
                    'currentPage' => $phongban->currentPage()
                ]
            ], 200);
        } else {
            $response =  response()->json([
                'status' => false,
                'message' => 'Bạn không được phép',
            ], 403);
        }
        return $response;
    }
    public function getphongban($id)
    {
        if (Gate::allows('view/id')) {
            $phongban = phongban::find($id);
            if ($phongban) {
                $phongban->load('phongban');
            }
            $response =  response()->json([
                'status' => true,
                'message' => 'Lấy thông tin phòng ban thành công',
                'data' => $phongban
            ], 200);
        } else {
            $response =  response()->json([
                'status' => false,
                'message' => 'Bạn không được phép',
            ], 403);
        }
        return $response;
    }
    public function addSave(Request $request)
    {

        if (Gate::allows('create')) {
            $validator = Validator::make(
                $request->all(),
                $this->validate_phongban
            );
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()
                ], 400);
            }
            $phongban = new phongban();
            $phongban->name = $request->name;
            $phongban->save();
            $response =  $phongban ? response()->json([
                'status' => true,
                'message' => 'Thêm phòng ban thành công',
                'data' => $phongban
            ], 200) : response()->json([
                'status' => false,
                'message' => 'Thêm phòng ban không thành công',
            ], 404);;
        } else {
            $response =   response()->json([
                'status' => false,
                'message' => 'Bạn không được phép',
            ], 403);
        }
        return $response;
    }

    public function update($id, Request $request)
    {

        if (Gate::allows('update')) {
            $validator = Validator::make(
                $request->all(),
                $this->validate_phongban
            );
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()
                ], 400);
            }
            $phongban = phongban::find($id);
            if ($phongban) {
                $phongban->name = $request->name;
                $phongban->save();
            }
            $response = $phongban ?
                response()->json([
                    'status' => true,
                    'message' => 'Sửa phòng ban thành công',
                    'data' => $phongban
                ], 200) :
                response()->json([
                    'status' => false,
                    'message' => 'Sửa phòng ban không thành công',
                ], 404);
        } else {
            $response = response()->json([
                'status' => false,
                'message' => 'Bạn không được phép',
            ], 403);
        }
        return $response;
    }

    public function delete($id)
    {
        if (Gate::allows('delete')) {
            $phongban = phongban::find($id);
            if ($phongban) {
                $phongban->delete();
            }
            $response =  $phongban ? response()->json([
                'status' => true,
                'message' => 'Xóa thành công',
            ], 200) : response()->json([
                'status' => false,
                'message' => 'Xóa thất bại'
            ], 403);
        } elseif (!Gate::allows('delete')) {
            $response = response()->json([
                'status' => false,
                'message' => 'Bạn không được phép',
            ], 403);
        }
        return $response;
    }
}
