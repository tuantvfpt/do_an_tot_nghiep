<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Calendar_leave;
use App\Models\company_mode;
use App\Models\LichChamCong;
use App\Models\TongThuNhap;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class CalendarLeaveController extends Controller
{
    //
    protected $validate = [
        'time_start' => 'required',
        'time_end' => 'required',
        'note' => 'required',
    ];
    public function getAll(Request $request)
    {
        $lich_nghi = Calendar_leave::select('calendar_for_leave.*', 'user_info.full_name')
            ->Join('users', 'calendar_for_leave.user_id', '=', 'users.id')
            ->join('user_info', 'users.id', '=', 'user_info.user_id')
            ->where('calendar_for_leave.deleted_at', null);
        if (Gate::allows('view')) {
            if (!empty($request->keyword)) {
                $lich_nghi =  $lich_nghi->Where(function ($query) use ($request) {
                    $query->where('user_info.full_name', 'like', "%" . $request->keyword . "%");
                });
            }
            if (!empty($request->date)) {
                $lich_nghi =  $lich_nghi->where('date', $request->date);
            }
        } else {
            $lich_nghi->where('calendar_for_leave.user_id', Auth::user()->id);
        }
        $lich_nghi = $lich_nghi->paginate(($request->limit != null) ? $request->limit : 10);
        return  response()->json([
            'status' => true,
            'message' => 'Lấy danh sách nghỉ thành công',
            'data' => $lich_nghi->items(),
            'meta' => [
                'total'      => $lich_nghi->total(),
                'perPage'    => $lich_nghi->perPage(),
                'currentPage' => $lich_nghi->currentPage()
            ]
        ])->setStatusCode(200);
    }
    public function get_lich_nghi()
    {
        if (Gate::allows('view')) {
            $lich_xin_nghi = Calendar_leave::select('calendar_for_leave.*', 'user_info.full_name')
                ->Join('users', 'calendar_for_leave.user_id', '=', 'users.id')
                ->join('user_info', 'users.id', '=', 'user_info.user_id')
                ->where('calendar_for_leave.deleted_at', null)
                ->where('status', 0)
                ->where('date', Carbon::now()->toDateString())
                ->get();
            $response = response()->json([
                'status' => true,
                'message' => 'Lấy dữ liệu thành công',
                'data' => $lich_xin_nghi
            ])->setStatusCode(200);
        } else {
            $response = response()->json([
                'status' => false,
                'message' => 'Không được phép thực hiện',
            ])->setStatusCode(404);
        }
        return $response;
    }
    public function update_calenda($id, Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            $this->validate
        );
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ], 400);
        }
        $today = Carbon::now()->toDateString();
        $dateDiff = date_diff(date_create($request->time_start), date_create($request->time_end));
        $x = $dateDiff->d;
        $calendar = Calendar_leave::find($id);
        $calendar->user_id = Auth::user()->id;
        $calendar->time_start = $request->time_start;
        $calendar->time_end = $request->time_end;
        $calendar->note = $request->note;
        $calendar->status = 0;
        $calendar->mode_leave = $request->mode_leave;
        $calendar->number_day_leave = $x;
        if ($request->mode_leave) {
            $calendar->number_mode_leave = $request->number_day;
        } else {
            $calendar->number_mode_leave = 0;
        }
        $mode = company_mode::where('user_id', Auth::user()->id)->whereYear('date', $today)->first();
        if ($mode->total_day - $request->number_day >= 0 && ($mode->total_day_off + $request->number_day <= $mode->total_day)) {
            $calendar->save();
            $response = response()->json([
                'status' => true,
                'message' => "Bạn đã đăng kí lịch nghỉ thành công",
                'data' => $calendar
            ])->setStatusCode(200);
        } else {
            $response = response()->json([
                'status' => true,
                'message' => "Đã sảy ra lỗi khi nhập ngày nghỉ phép",
                'data' => $calendar
            ])->setStatusCode(404);
        }
        return $response;
    }
    public function comfig($id, Request $request)
    {
        if (Gate::allows('confirmLeave')) {
            // $check = Calendar_leave::where('id', $id)->first();
            $today = Carbon::now()->toDateString();
            if (isset($request->yes)) {
                $lich_xin_nghi = Calendar_leave::find($id);
                $lich_xin_nghi->status = 1;
                $lich_xin_nghi->save();
                $mode = company_mode::where('user_id', $lich_xin_nghi->user_id)->whereYear('date', $today)->first();
                if ($mode->total_day - $lich_xin_nghi->number_mode_leave >= 0 && ($mode->total_day_off + $lich_xin_nghi->number_mode_leave <= $mode->total_day)) {
                    $mode_user = company_mode::find($mode->id);
                    $mode_user->total_day_off += $lich_xin_nghi->number_mode_leave;
                    $mode_user->date = Carbon::now();
                    $mode_user->save();
                }
                $user = User::where('id', $lich_xin_nghi->user_id)->first();
                $to_name = $user->user_account;
                $to_email = $user->email;
                $data = array('name' => 'Hello' . $to_name, 'body' => 'Công ty đã nhận được đơn xin nghỉ của bạn và 
                công ty chấp nhận đơn xin nghỉ của bạn.');
                Mail::send('emails.mail', $data, function ($message) use ($to_name, $to_email) {
                    $message->to($to_email, $to_name)->subject('V.v nghỉ phép');
                    $message->from('tuantong.datus@gmail.com');
                });
                $mess = "Đồng ý cho nghỉ";
            } else {
                $lich_xin_nghi = Calendar_leave::find($id);
                if ($lich_xin_nghi) {
                    if ($lich_xin_nghi->mode_leave == 1) {
                        $mode_user = company_mode::where('user_id', $lich_xin_nghi->user_id)->first();
                        $mode_user = company_mode::find($mode_user->id);
                        $mode_user->total_day_off = $mode_user->total_day_off - $lich_xin_nghi->number_mode_leave;
                        $mode_user->save();
                    }
                    $user = User::where('id', $lich_xin_nghi->user_id)->first();
                    $to_name = $user->user_account;
                    $to_email = $user->email;
                    $data = array('name' => $to_name, 'body' => 'Công ty đã nhận được đơn xin nghỉ của bạn và 
                công ty không chấp nhận đơn xin nghỉ của bạn.Mong bạn thu xếp công việc và đi làm đúng giờ nhé');
                    Mail::send('emails.mail', $data, function ($message) use ($to_name, $to_email) {
                        $message->to($to_email, $to_name)->subject('V.v nghỉ phép');
                        $message->from('tuantong.datus@gmail.com');
                    });
                    $lich_xin_nghi->delete();
                }
                $mess = "Không cho phép nghỉ";
            }
            $response = response()->json([
                'status' => true,
                'message' => $mess,
            ])->setStatusCode(200);
        } else {
            $response = response()->json([
                'status' => false,
                'message' => 'Không được phép thực hiện',
            ])->setStatusCode(404);
        }
        return $response;
    }
    public function create(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            $this->validate
        );
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ], 400);
        }
        $today = Carbon::now()->toDateString();
        $user_id = Auth::user()->id;
        $check = Calendar_leave::where('date', $today)->where('user_id', $user_id)->first();
        $dateDiff = date_diff(date_create($request->time_start), date_create($request->time_end));
        $x = $dateDiff->d;
        $user_off = new Calendar_leave();
        if ($check) {
            $user_off = Calendar_leave::find($check->id);
        }
        $user_off->user_id = $user_id;
        $user_off->time_start = $request->time_start;
        $user_off->time_end = $request->time_end;
        $user_off->note = $request->note;
        $user_off->date = Carbon::now()->toDateString();
        $user_off->status = 0;
        $user_off->mode_leave = $request->mode_leave;
        $user_off->number_day_leave = $x;
        if ($request->mode_leave) {
            $user_off->number_mode_leave = $request->number_day;
        } else {
            $user_off->number_mode_leave = 0;
        }
        $mode = company_mode::where('user_id', Auth::user()->id)->whereYear('date', $today)->first();
        if ($mode->total_day - $request->number_day >= 0 && ($mode->total_day_off + $request->number_day <= $mode->total_day)) {
            $user_off->save();
            $response = response()->json([
                'status' => true,
                'message' => "Bạn đã đăng kí lịch nghỉ thành công",
                'data' => $user_off
            ])->setStatusCode(200);
        } else {
            $response = response()->json([
                'status' => true,
                'message' => "Đã sảy ra lỗi khi nhập ngày nghỉ phép",
                'data' => $user_off
            ])->setStatusCode(404);
        }
        return $response;
    }
    public function get_company_leave()
    {
        $today = Carbon::now()->toDateString();
        $get_company_leave = company_mode::where('user_id', Auth::user()->id)->WhereYear('date', date('Y', strtotime($today)))->first();
        return  response()->json([
            'status' => true,
            'message' => "Lấy tổng số ngày nghỉ nhân viên thành công",
            'data' => $get_company_leave
        ])->setStatusCode(200);
    }
}
