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

        if (Gate::allows('view')) {
            $lich_nghi = Calendar_leave::select('calendar_for_leave.*', 'user_info.full_name')
                ->Join('users', 'calendar_for_leave.user_id', '=', 'users.id')
                ->join('user_info', 'users.id', '=', 'user_info.user_id')
                ->where('calendar_for_leave.deleted_at', null);
            if (!empty($request->keyword)) {
                $lich_nghi =  $lich_nghi->Where(function ($query) use ($request) {
                    $query->where('user_info.full_name', 'like', "%" . $request->keyword . "%");
                });
            }
            if (!empty($request->date)) {
                $lich_nghi =  $lich_nghi->whereMonth('calendar_for_leave.date', date('m', strtotime($request->date)));
            }
            $lich_nghi = $lich_nghi->paginate(($request->limit != null) ? $request->limit : 10);
            $response = response()->json([
                'status' => true,
                'message' => 'Lấy danh sách nghỉ thành công',
                'data' => $lich_nghi->items(),
                'meta' => [
                    'total'      => $lich_nghi->total(),
                    'perPage'    => $lich_nghi->perPage(),
                    'currentPage' => $lich_nghi->currentPage()
                ]
            ])->setStatusCode(200);
        } else {
            $response = response()->json([
                'status' => false,
                'message' => 'Không có quyền truy cập',
            ], 404);
        }
        return  $response;
    }
    public function GetAllByUser(Request $request)
    {

        $lich_nghi = Calendar_leave::select('calendar_for_leave.*', 'user_info.full_name')
            ->Join('users', 'calendar_for_leave.user_id', '=', 'users.id')
            ->join('user_info', 'users.id', '=', 'user_info.user_id')
            ->where('calendar_for_leave.deleted_at', null)
            ->where('calendar_for_leave.user_id', Auth::user()->id);
        if (!empty($request->keyword)) {
            $lich_nghi =  $lich_nghi->Where(function ($query) use ($request) {
                $query->where('user_info.full_name', 'like', "%" . $request->keyword . "%");
            });
        }
        if (!empty($request->date)) {
            $lich_nghi =  $lich_nghi->whereMonth('calendar_for_leave.date', date('m', strtotime($request->date)));
        }
        $lich_nghi = $lich_nghi->paginate(($request->limit != null) ? $request->limit : 10);
        return response()->json([
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
        if ($calendar) {
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
        }

        $mode = company_mode::where('user_id', Auth::user()->id)->whereYear('date', $today)->first();
        if ($mode->total_day - $request->number_day >= 0 && ($mode->total_day_off + $request->number_day <= $mode->total_day)) {
            $calendar->save();
            $response = response()->json([
                'status' => true,
                'message' => "Bạn đã sửa lịch nghỉ thành công",
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
        if ($check && $check->status == 0) {
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
    public function getdetail($id)
    {
        $detail = Calendar_leave::find($id);
        return  response()->json([
            'status' => true,
            'message' => "Lấy chi tiết lịch xin nghỉ thành công",
            'data' => $detail
        ])->setStatusCode(200);
    }
}
