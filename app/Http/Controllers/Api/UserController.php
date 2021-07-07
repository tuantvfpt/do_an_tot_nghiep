<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Calendar_leave;
use App\Models\chucvu;
use App\Models\company_mode;
use App\Models\LichChamCong;
use App\Models\phongban;
use App\Models\Prize_user;
use App\Models\TongThuNhap;
use App\Models\User;
use App\Models\userInfo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    public function __construct(User $users)
    {
        $this->users = $users;
    }

    public function getAll(Request $request)
    {
        if (Gate::allows('view')) {
            $users = $this->users
                ->select('users.id', 'user_info.full_name', 'users.email', 'department.name as name_department', 'position.name as name_position', 'user_info.avatar', 'users.user_account')
                ->leftJoin('department', 'department.id', '=', 'users.department_id')
                ->leftJoin('position', 'position.id', '=', 'users.position_id')
                ->leftJoin('user_info', 'user_info.user_id', '=', 'users.id')
                ->where('user_info.deleted_at', null);
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
            $users = $users->paginate(($request->limit != null) ? $request->limit : 5);
        } elseif (!Gate::allows('view')) {
            return response()->json([
                'status' => false,
                'message' => 'Bạn không được phép',
            ], 403);
        }
        return response()->json([
            'status' => true,
            'message' => 'Lấy danh sách user thành công',
            'data' => $users->items(),
            'meta' => [
                'total'      => $users->total(),
                'perPage'    => $users->perPage(),
                'currentPage' => $users->currentPage()
            ]
        ], 200);
    }
    public function getUser($id, Request $request)
    {
        if (Gate::allows('view/id')) {
            $users = User::find($id);
            if ($users) {
                $users->load('userinfo', 'phongban_userinfo', 'chucvu_userinfo');
            }
        } elseif (!Gate::allows('view/id')) {
            return response()->json([
                'status' => false,
                'message' => 'Bạn không được phép',
            ], 403);
        }
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
        if (Gate::allows('view')) {
            $phongban = phongban::all();
            $chucvu = chucvu::all();
        } elseif (!Gate::allows('view')) {
            return response()->json([
                'status' => false,
                'message' => 'Bạn không được phép',
            ], 403);
        }
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
        if (Gate::allows('create')) {
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
        } elseif (!Gate::allows('create')) {
            return response()->json([
                'status' => false,
                'message' => 'Bạn không được phép',
            ], 403);
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
        if (Gate::allows('update')) {
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
            } elseif (!Gate::allows('update')) {
                return response()->json([
                    'status' => false,
                    'message' => 'Bạn không được phép',
                ], 403);
            }
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
        if (Gate::allows('delete')) {
            $userinfo = userInfo::where('user_id', $id);
            $calendar_for_leave = Calendar_leave::where('user_id', $id);
            $company_mode = company_mode::where('user_id', $id);
            $total_salary = TongThuNhap::where('user_id', $id);
            $time_keep_calendar = LichChamCong::where('user_id', $id);
            $prize_fine_user = Prize_user::where('user_id', $id);
            $user = User::find($id);
            if ($userinfo && $user) {
                $userinfo->delete();
                $user->delete();
            }
        } elseif (!Gate::allows('delete')) {
            return response()->json([
                'status' => false,
                'message' => 'Bạn không được phép',
            ], 403);
        }
        return $user && $userinfo ?
            response()->json([
                'status' => true,
                'message' => 'Xóa thành công',
            ], 200) :
            response()->json([
                'status' => false,
                'message' => 'xóa thất bại',
            ], 404);
    }

    public function ChangePassword(Request $request)
    {
        $current_password = $request->current_password;
        $new_password = $request->new_password;
        $comfig_password = $request->comfig_password;
        if ((Hash::check($current_password, Auth::user()->password))) {
            if (($comfig_password == $new_password)) {
                $user = User::find(Auth::user()->id);
                $user->password = Hash::make($new_password);
                $user->save();
                $status = true;
                $messages = "Thay đổi mật khẩu thành công";
            } else {
                $status = false;
                $messages = "Mật khẩu không trùng nhau";
            }
        } else {
            $status = false;
            $messages = "Nhập mật khẩu cũ không chính xác";
        }
        return response()->json([
            'status' => $status,
            'message' => $messages,
        ]);
    }
}
