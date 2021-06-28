<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Calendar_leave;
use App\Models\company_mode;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CalendarLeaveController extends Controller
{
    //
    public function get_mode()
    {
    }
    public function update_day()
    {
        $startyear = Carbon::now()->startOfYear()->toDateString();
        $endyear = Carbon::now()->endOfYear()->toDateString();
        $today = date('Y-m-d');
        $user = DB::table('users')
            ->select('*')
            ->rightjoin('user_info', 'users.id', '=', 'user_info.user_id')
            ->get();
        foreach ($user as $user) {
            $dateDiff = date_diff(date_create($user->date_of_join), date_create($today));
            $x = $dateDiff->m;
            $mode_day = company_mode::where('user_id', $user->user_id)->whereBetween('date', [$startyear, $endyear])->first();
            if ($mode_day) {
                $mode_day = company_mode::find($mode_day->id);
                if ($x >= 6 && $x < 8) {
                    $mode_day->user_id = $user->user_id;
                    $mode_day->date = Carbon::now()->todateString();
                    $mode_day->total_day = 3;
                } elseif ($x >= 8 && $x < 10) {
                    $mode_day->user_id = $user->user_id;
                    $mode_day->date = Carbon::now()->todateString();
                    $mode_day->total_day = 4;
                } elseif ($x >= 10 && $x < 12) {
                    $mode_day->user_id = $user->user_id;
                    $mode_day->total_day = 5;
                    $mode_day->date = Carbon::now()->todateString();
                } elseif ($x >= 12) {
                    $mode_day->user_id = $user->user_id;
                    $mode_day->total_day = 6;
                    $mode_day->date = Carbon::now()->todateString();
                } else {
                    $mode_day->user_id = $user->user_id;
                    $mode_day->total_day = 0;
                    $mode_day->date = Carbon::now()->todateString();
                }
                $mode_day->save();
            } else {
                $mode_day = new company_mode();
                if ($x >= 6 && $x < 8) {
                    $mode_day->user_id = $user->user_id;
                    $mode_day->date = Carbon::now()->todateString();
                    $mode_day->total_day = 3;
                } elseif ($x >= 8 && $x < 10) {
                    $mode_day->user_id = $user->user_id;
                    $mode_day->date = Carbon::now()->todateString();
                    $mode_day->total_day = 4;
                } elseif ($x >= 10 && $x < 12) {
                    $mode_day->user_id = $user->user_id;
                    $mode_day->total_day = 5;
                    $mode_day->date = Carbon::now()->todateString();
                } elseif ($x >= 12) {
                    $mode_day->user_id = $user->user_id;
                    $mode_day->total_day = 6;
                    $mode_day->date = Carbon::now()->todateString();
                } else {
                    $mode_day->user_id = $user->user_id;
                    $mode_day->total_day = 0;
                    $mode_day->date = Carbon::now()->toDateString();
                }
                $mode_day->save();
            }
        }
    }
    public function create(Request $request)
    {
        $today = Carbon::now()->toDateString();
        $user_id = $request->user_id;
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
            if (($mode->total_day - $x >= 0)) {
                $mode_user = company_mode::find($mode->id);
                $mode_user->total_day_off += $x;
                $mode_user->date = Carbon::now();
                $mode_user->save();
            }
        }
    }
}
