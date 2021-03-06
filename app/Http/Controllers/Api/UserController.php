<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\Calendar_leave;
use App\Models\company_mode;
use App\Models\LichChamCong;
use App\Models\lichTangCa;
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
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    protected $validate_user = [
        'user_account' => 'required|unique:users,user_account,',
        'email' => 'required|unique:users,email',
        'full_name' => 'required',
        'position_id' => 'required',
        'department_id' => 'required',
    ];
    public function __construct(User $users)
    {
        $this->users = $users;
    }

    public function getAll(Request $request)
    {
        $users = $this->users
            ->select('users.id', 'user_info.full_name', 'users.email', 'department.name as name_department', 'position.name as name_position', 'user_info.avatar', 'users.user_account')
            ->leftJoin('department', 'department.id', '=', 'users.department_id')
            ->leftJoin('position', 'position.id', '=', 'users.position_id')
            ->leftJoin('user_info', 'user_info.user_id', '=', 'users.id')
            ->orderby('id', 'ASC');
        if (!empty($request->keyword)) {
            $users =  $users->Where(function ($query) use ($request) {
                $query->Orwhere('user_info.full_name', 'like', "%" . $request->keyword . "%")
                    ->Orwhere('email', 'like', "%" . $request->keyword . "%");
            });
        }
        if (!empty($request->chucvu)) {
            $users =  $users->where('position_id', $request->chucvu);
        }
        if (!empty($request->phongban)) {
            $users = $users->where('department_id', $request->phongban);
        }
        $users = $users->paginate(($request->limit != null) ? $request->limit : 8);
        return  response()->json([
            'status' => true,
            'message' => 'L???y danh s??ch user th??nh c??ng',
            'data' => $users->items(),
            'meta' => [
                'total'      => $users->total(),
                'perPage'    => $users->perPage(),
                'currentPage' => $users->currentPage()
            ]
        ], 200);
    }
    public function getUser($id)
    {
        $check = User::where('id', $id)
            ->where('id', Auth::user()->id)->first();
        if (Gate::allows('view/id')) {
            $users = User::find($id);
            if ($users) {
                $users->load('userinfo', 'phongban_userinfo', 'chucvu_userinfo');
            }
            $response =  $users ?
                response()->json([
                    'status' => true,
                    'message' => 'L???y th??ng tin user th??nh c??ng',
                    'data' => $users
                ], 200) :
                response()->json([
                    'status' => false,
                    'message' => 'Kh??ng t??m th???y user',
                ], 404);
        } else {
            if ($check) {
                $users = User::find($check->id);
                $users->load('userinfo', 'phongban_userinfo', 'chucvu_userinfo');
            }
            $response = response()->json([
                'status' => true,
                'message' => 'L???y th??ng tin user th??nh c??ng',
                'data' => $users
            ], 200);
        }
        return $response;
    }

    public function addSaveUser(Request $request)
    {
        $validator = Validator::make($request->all(), $this->validate_user);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ], 400);
        }
        if (Gate::allows('create')) {
            $users = new User();
            $users->user_account = $request->user_account;
            $users->email = $request->email;
            $users->position_id = $request->position_id;
            $users->department_id = $request->department_id;
            $users->password = Hash::make($request->user_account);
            if ($request->role_id) {
                $users->role_id = $request->role_id;
            } else {
                $users->role_id = 3;
            }
            $users->save();
            if ($users->id) {
                $userinfo = new userInfo();
                $userinfo->user_id = $users->id;
                $userinfo->full_name = $request->full_name;
                $userinfo->phone = $request->phone;
                $userinfo->sex = $request->sex;
                $userinfo->address = $request->address;
                $userinfo->id_card = $request->id_card;
                $userinfo->phone = $request->phone;
                $userinfo->marital_status = $request->marital_status;
                if ($request->hasFile('avatar')) {
                    $file = $request->file('avatar');
                    $newname = rand() . '.' . $file->getClientOriginalExtension();
                    $file->move("images", $newname);
                    $userinfo->avatar = "http://127.0.0.1:8000/" . "images/" . $newname;
                }
                $userinfo->basic_salary = $request->basic_salary;
                $userinfo->code_QR = $users->user_account . $users->id;
                $userinfo->date_of_join = Carbon::now('Asia/Ho_Chi_Minh');
                $userinfo->save();
            }
            $to_name =  $request->full_name;
            $to_email = $request->email;
            $data = array('name' => 'Dear' . $to_name, 'body' => 'C??ng ty r???t c???m ??n b???n ???? gia nh???p c??ng ty.
            Mong b???n v?? c??ng ty c?? th??? h???p t??c t???t ????? ph??t tri???n c??ng ty ??i l??n.' . '????y l?? email v?? m???t kh???u c???a b???n ?????
            truy c???p v??o Web c???a c??ng ty.' . 'Email:' . $to_email . 'M???t kh???u:' . $request->user_account);
            Mail::send('emails.mail', $data, function ($message) use ($to_name, $to_email) {
                $message->to($to_email, $to_name)->subject('C??ng ty Hr');
                $message->from('tuantong.datus@gmail.com');
            });
            $response =  $users ?
                response()->json([
                    'status' => true,
                    'message' => 'Th??m user th??nh c??ng',
                    'data' => $users, $userinfo
                ], 200) :
                response()->json([
                    'status' => false,
                    'message' => 'S???a user th???t b???i',
                ], 404);
        } else {
            $response =  response()->json([
                'status' => false,
                'message' => 'B???n kh??ng ???????c ph??p',
            ], 403);
        }
        return $response;
    }

    public function update($id, Request $request)
    {
        if (Gate::allows('update')) {
            $validator = Validator::make(
                $request->all(),
                [
                    'user_account' =>
                    [
                        'required',
                        Rule::unique('users')->ignore($request->id),
                    ],
                    'email' => Rule::unique('users')->ignore($request->id),

                    'email' => 'required',
                    'full_name' => 'required',
                    'position_id' => 'required',
                    'department_id' => 'required',
                ]
            );
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()
                ], 400);
            }
            $users = User::find($id);
            $users->position_id = $request->position_id;
            $users->department_id = $request->department_id;
            $users->role_id = $request->role_id;
            $users->save();
            if ($users) {
                $userinfo = userInfo::where('user_id', $users->id)->first();
                $userinfo = userInfo::find($userinfo->id);
                $userinfo->basic_salary = $request->basic_salary;
                $userinfo->full_name = $request->full_name;
                $userinfo->sex = $request->sex;
                $userinfo->address = $request->address;
                $userinfo->id_card = $request->id_card;
                $userinfo->phone = $request->phone;
                $userinfo->marital_status = $request->marital_status;
                if ($request->hasFile('avatar')) {
                    $file = $request->file('avatar');
                    $newname = rand() . '.' . $file->getClientOriginalExtension();
                    $file->move("images", $newname);
                    $userinfo->avatar = "http://127.0.0.1:8000/" . "images/" . $newname;
                }
                $userinfo->save();
            }
            $response =   $users || $userinfo  ?
                response()->json([
                    'status' => true,
                    'message' => 'S???a user th??nh c??ng',
                    'data' => $userinfo, $users
                ], 200) :
                response()->json([
                    'status' => false,
                    'message' => 'S???a user th???t b???i',
                ], 404);
        } else {
            $response =  response()->json([
                'status' => true,
                'message' => 'Kh??ng c?? quy???n truy c???p',
            ], 403);
        }
        return $response;
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
                $calendar_for_leave->delete();
                $company_mode->delete();
                $total_salary->delete();
                $time_keep_calendar->delete();
                $prize_fine_user->delete();
                $user->delete();
            }
            $response = $user && $userinfo ? response()->json([
                'status' => true,
                'message' => 'X??a th??nh c??ng',
            ], 200) : response()->json([
                'status' => false,
                'message' => 'x??a th???t b???i',
            ], 404);
        } else {
            $response =  response()->json([
                'status' => false,
                'message' => 'B???n kh??ng ???????c ph??p',
            ], 403);
        }
        return $response;
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
                $messages = "Thay ?????i m???t kh???u th??nh c??ng";
            } else {
                $status = false;
                $messages = "M???t kh???u kh??ng tr??ng nhau";
            }
        } else {
            $status = false;
            $messages = "Nh???p m???t kh???u c?? kh??ng ch??nh x??c";
        }
        return response()->json([
            'status' => $status,
            'message' => $messages,
        ]);
    }
    public function ListUsers()
    {
        $today = Carbon::now()->toDateString();
        $check = User::where('id', Auth::user()->id)->where('role_id', 4)->first();
        if ($check) {
            $list = User::select('time_keep_calendar.*', 'users.*')
                ->Join('time_keep_calendar', 'users.id', '=', 'time_keep_calendar.user_id')
                ->where('department_id', $check->department_id)
                ->where('date_of_work', $today)
                ->where('time_keep_calendar.check_ot', 0)
                ->groupby('time_keep_calendar.user_id')
                ->get();
            $list->load('userinfo');
            return response()->json([
                'status' => true,
                'message' => 'L???y d??? li???u th??nh c??ng',
                'data' => $list
            ]);
        }
    }
    // public function ListUsers_by_hour()
    // {
    //     $today = Carbon::now()->toDateString();
    //     $check = User::where('id', Auth::user()->id)->where('role_id', 4)->first();
    //     if ($check) {
    //         $list = User::select('time_keep_calendar.*', 'users.id')
    //             ->leftjoin('time_keep_calendar', 'users.id', '=', 'time_keep_calendar.user_id')
    //             ->leftjoin('lich_tang_ca', 'time_keep_calendar.id', '=', 'lich_tang_ca.lich_cham_cong_id')
    //             ->where('department_id', $check->department_id)
    //             ->where('date_of_work', $today)
    //             ->where('time_keep_calendar.check_ot', 0)
    //             ->where('date', null)
    //             ->get();
    //         $list->load('userinfo');
    //         return response()->json([
    //             'status' => true,
    //             'message' => 'L???y d??? li???u th??nh c??ng',
    //             'data' => $list
    //         ]);
    //     }
    // }
    public function ListAll()
    {
        $list = User::all();
        $list->load('userinfo');
        return response()->json([
            'status' => true,
            'message' => 'L???y d??? li???u th??nh c??ng',
            'data' => $list
        ]);
    }
    public function forget_password(Request $request)
    {
        $email = $request->email;
        $check = User::where('email', $email)->orWhere('user_account', $email)->first();
        if ($check) {
            $to_name = $check->user_account;
            $to_email = $check->email;
            $random = Str::random(10);
            $password = strtolower($random);
            $user = User::find($check->id);
            $user->password = Hash::make($password);
            $user->save();
            $data = array('name' => $to_name, 'body' => 'M???t kh???u m???i c???a b???n l??:' . $password . ' .Ghi nh??? nh?? n??o c?? v??ng :)');
            Mail::send('emails.mail', $data, function ($message) use ($to_name, $to_email) {
                $message->to($to_email, $to_name)->subject('M?? x??c nh???n qu??n m???t kh???u');
                $message->from('tuantong.datus@gmail.com');
            });
            $response = response()->json([
                'status' => true,
                'message' => 'L???y d??? li???u th??nh c??ng',
            ]);
        } else {
            $response = response()->json([
                'status' => false,
                'message' => 'Kh??ng t??m th???y email',
            ]);
        }
        return $response;
    }
    public function get_user_current()
    {
        $user_current = User::where('id', Auth::user()->id)->first();
        $user_current->load('userinfo');
        return response()->json([
            'status' => true,
            'message' => 'l???y th??ng tin th??nh c??ng',
            'data' => $user_current
        ], 202);
    }
}
