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

class UserController extends Controller
{
    public function __construct(User $users)
    {
        $this->users = $users;
    }

    public function getAll(Request $request)
    {
        $users = $this->users
            ->select('user_info.full_name', 'users.email', 'department.name as name_department', 'position.name as name_position', 'user_info.avatar', 'users.user_account')
            ->leftJoin('department', 'department.id', '=', 'users.department_id')
            ->leftJoin('position', 'position.id', '=', 'users.position_id')
            ->leftJoin('user_info', 'user_info.user_id', '=', 'users.id');
        dd($users->get());
        // $users->load('userinfo', 'phongban_userinfo', 'chucvu_userinfo');
        if (!empty($request->keyword)) {
            $users =  $users->Where(function ($query) use ($request) {
                $query->Orwhere('user_info.full_name', 'like', "%" . $request->keyword . "%")
                    ->Orwhere('email', 'like', "%" . $request->keyword . "%");
            });
        }
        if (!empty($request->chucvu)) {
            $users =  $users->where('department_id', $request->chucvu);
        }
        if (!empty($request->phongban)) {
            $users = $users->where('position_id', $request->phongban);
        }
        $users = $users->paginate(($request->limit != null) ? $request->limit : 2);
        return response()->json([
            'status' => true,
            'message' => 'Lấy danh sách user thành công',
            'data' => $users
        ])->setStatusCode(200);
    }
    public function getUser($id, Request $request)
    {
        $users = User::find($id);
        $users->load('userinfo', 'phongban_userinfo', 'chucvu_userinfo');
        return $users ?
            response()->json([
                'status' => true,
                'message' => 'Lấy thông tin user thành công',
                'data' => $users
            ], 200) :
            response()->json([
                'status' => false,
                'message' => 'Không tìm thấy user',
            ], 404);
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
            ], 404);
    }
    public function addSaveUser(Request $request)
    {

        $users = new User();
        $users->user_account = $request->user_account;
        $users->email = $request->email;
        $users->position_id = $request->position_id;
        $users->department_id = $request->department_id;
        $users->password = Hash::make($request->password);
        $users->save();
        if ($users->id) {
            $userinfo = new userInfo();
            $userinfo->user_id = $users->id;
            $userinfo->full_name = $request->full_name;
            $userinfo->phone = $request->phone;
            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                $newname = rand() . '.' . $file->getClientOriginalExtension();
                $file->move("images", $newname);
                $userinfo->avatar = "images/" . $newname;
            }
            $userinfo->basic_salary = $request->basic_salary;
            $userinfo->code_QR = $users->user_account . $users->id;
            $userinfo->date_of_join = Carbon::now('Asia/Ho_Chi_Minh');
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
            ], 404);
    }

    public function update($id, Request $request)
    {
        $users = User::find($id);
        if (isset($users)) {
            $userinfo = userInfo::where('user_id', $users->id)->first();
            $userinfo->full_name = $request->full_name;
            $userinfo->phone = $request->phone;
            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                $newname = rand() . '.' . $file->getClientOriginalExtension();
                $file->move("images", $newname);
                $userinfo->avatar = "images/" . $newname;
            }
            $userinfo->save();
            $userinfo->load('getuser');
        } else {
            return response()->json([
                'status' => true,
                'message' => 'Không tồn tại nhân viên này',
            ], 404);
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
            ], 404);
    }

    public function delete($id)
    {
        $userinfo = userInfo::where('user_id', $id)->delete();
        $user = User::find($id)->delete();
        return $user && $userinfo ?
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
