<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\chucvu;
use App\Models\phongban;
use App\Models\User;
use App\Models\userInfo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Whoops\Run;

class UserController extends Controller
{
    public function getAll()
    {
        $users = User::all();
        $users->load('userinfo');
        return response()->json([
            'status' => true,
            'message' => 'Lấy danh sách user thành công',
            'data' => $users
        ])->setStatusCode(200);
    }
    public function getUser($id, Request $request)
    {
        $users = User::find($id);
        $users->load('userinfo');
        return $users ?
            response()->json([
                'status' => true,
                'message' => 'Lấy thông tin user thành công',
                'data' => $users
            ], 200) :
            response()->json([
                'status' => false,
                'message' => 'Không tìm thấy user',
            ], 200);
    }
    public function getlist()
    {
        $phongban = phongban::all();
        $chucvu = chucvu::all();
        return $phongban && $chucvu ?
            response()->json([
                'status' => true,
                'message' => 'Lấy dữ liệu thành công',
                'data' => $phongban, $chucvu
            ], 200) :
            response()->json([
                'status' => false,
                'message' => 'Lấy dữ liệu thất bại',
            ], 200);
    }
    public function addSaveUser(Request $request)
    {

        $users = new User();
        $users->user_account = $request->user_account;
        $users->email = $request->email;
        $users->password = Hash::make($request->password);
        $users->save();
        if ($users->id) {
            $userinfo = new userInfo();
            $userinfo->user_id = $users->id;
            $userinfo->name = $request->name;
            $userinfo->chuc_vu_id = $request->chuc_vu_id;
            $userinfo->phong_ban_id = $request->phong_ban_id;
            $userinfo->phone = $request->phone;
            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                $newname = rand() . '.' . $file->getClientOriginalExtension();
                $file->move("images", $newname);
                $userinfo->avatar = "images/" . $newname;
            }
            $userinfo->luong_co_ban = $request->luong_co_ban;
            $userinfo->ma_QR = $users->user_account . $users->id;
            $userinfo->ngay_gia_nhap = Carbon::now('Asia/Ho_Chi_Minh');
            $userinfo->save();
        }
        return $userinfo && $users ?
            response()->json([
                'status' => true,
                'message' => 'Thêm user thành công',
                'data' => $users, $userinfo
            ], 200) :
            response()->json([
                'status' => false,
                'message' => 'Sửa user thất bại',
            ], 200);
    }

    public function update($id, Request $request)
    {

        $userinfo = userInfo::find($id);
        if ($userinfo) {
            $userinfo->name = $request->name;
            $userinfo->chuc_vu_id = $request->chuc_vu_id;
            $userinfo->chuc_vu_id = $request->phong_ban_id;
            $userinfo->phone = $request->phone;
            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                $newname = rand() . '.' . $file->getClientOriginalExtension();
                $file->move("images", $newname);
                $userinfo->avatar = "images/" . $newname;
            }
            $userinfo->luong_co_ban = $request->luong_co_ban;
            $userinfo->save();
            $userinfo->load('getuser');
        }
        return  $userinfo ?
            response()->json([
                'status' => true,
                'message' => 'Sửa user thành công',
                'data' => $userinfo
            ], 200) :
            response()->json([
                'status' => false,
                'message' => 'Sửa user thất bại',
            ], 200);
    }

    public function delete($id, Request $request)
    {
        $userinfo = userInfo::where('user_id', $id)->delete();
        $user = User::destroy($id);
        return $user && $userinfo ?
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
