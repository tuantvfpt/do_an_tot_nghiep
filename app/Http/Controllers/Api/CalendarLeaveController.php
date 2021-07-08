<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Calendar_leave;
use App\Models\company_mode;
use App\Models\LichChamCong;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CalendarLeaveController extends Controller
{
    //
    public function getAll(Request $request)
    {
        $lich_nghi = Calendar_leave::select('calendar_for_leave.*', 'user_info.full_name')
            ->Join('users', 'calendar_for_leave.user_id', '=', 'users.id')
            ->join('user_info', 'users.id', '=', 'user_info.user_id')
            ->where('calendar_for_leave.deleted_at', null)
            ->where('calendar_for_leave.status', 1);
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
        $lich_nghi = $lich_nghi->paginate(($request->limit != null) ? $request->limit : 5);
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
    public function comfig($id, Request $request)
    {
        if (Gate::allows('confirmLeave')) {
            // $check = Calendar_leave::where('id', $id)->first();
            if (isset($request->yes)) {
                $lich_xin_nghi = Calendar_leave::find($id);
                $lich_xin_nghi->status = 1;
                $lich_xin_nghi->save();
                $mess = "Đồng ý cho nghỉ";
            } else {
                $lich_xin_nghi = Calendar_leave::find($id)->delete();
                $mess = "Không đồng ý cho nghỉ";
            }
            $response = response()->json([
                'status' => true,
                'message' => $mess,
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
    public function create(Request $request)
    {
        $today = Carbon::now()->toDateString();
        $user_id = Auth::user()->id;
        $check = Calendar_leave::where('date', $today)->where('user_id', $user_id)->first();
        $user_off = new Calendar_leave();
        if ($check) {
            $user_off = Calendar_leave::find($check->id);
        }
        $dateDiff = date_diff(date_create($request->time_start), date_create($request->time_end));
        $x = $dateDiff->d;
        $user_off->user_id = $user_id;
        $user_off->time_start = $request->time_start;
        $user_off->time_end = $request->time_end;
        $user_off->note = $request->note;
        $user_off->date = Carbon::now()->toDateString();
        $user_off->status = 0;
        $user_off->mode_leave = $request->mode_leave;
        $user_off->save();
        if ($request->mode_leave == 1) {
            $mode = company_mode::where('user_id', $user_id)->first();
            if (($mode->total_day - $x >= 0 && ($mode->total_day_off + $x <= $mode->total_day || $mode->total_day_off + $request->number_day <= $mode->total_day))) {
                $mode_user = company_mode::find($mode->id);
                if ($request->number_day && $request->number_day <= $x) {
                    $mode_user->total_day_off += $request->number_day;
                } else {
                    $mode_user->total_day_off += $x;
                }
                $mode_user->date = Carbon::now();
                $mode_user->save();
            }
        }
        return $user_off ? response()->json([
            'status' => true,
            'message' => "Đăng kí ngày nghỉ thành công",
            'data' => $user_off
        ])->setStatusCode(200) : response()->json([
            'status' => false,
            'message' => 'Đăng kí không thành công',
        ], 404);
    }
    //lấy tất cả dữ liệu đi lầm với nghỉ trong 1 tháng theo lich

}
