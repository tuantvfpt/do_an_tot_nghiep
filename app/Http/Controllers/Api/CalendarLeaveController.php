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
        $month = strtotime(date("Y-m-d", strtotime($today)) . " -6 month");
        $month = strftime("%Y-%m-%d", $month);
        $year = strtotime(date("Y-m-d", strtotime($today)) . " -10 year");
        $year = strftime("%Y-%m-%d", $year);
        $tamth = strtotime(date("Y-m-d", strtotime($today)) . " - 8 month");
        $muoith = strtotime(date("Y-m-d", strtotime($today)) . " - 10 month");
        $muoihaith = strtotime(date("Y-m-d", strtotime($today)) . " - 12 month");
        $tamth = strftime("%Y-%m-%d", $tamth);
        $muoith = strftime("%Y-%m-%d", $muoith);
        $muoihaith = strftime("%Y-%m-%d", $muoihaith);
        $user = DB::table('users')
            ->join('user_info', 'users.id', '=', 'user_info.user_id')
            ->whereBetween('date_of_join', [$year, $month])
            ->get();
        foreach ($user as $user) {
            $sixth = strtotime(date("Y-m-d", strtotime($user->date_of_join)) . " - 6 month");
            $seventh = strtotime(date("Y-m-d", strtotime($user->date_of_join)) . " - 8 month");
            $nigth = strtotime(date("Y-m-d", strtotime($user->date_of_join)) . " - 10 month");
            $tweth = strtotime(date("Y-m-d", strtotime($user->date_of_join)) . " - 12 month");
            $sixth = strftime("%Y-%m-%d", $sixth);
            $seventh = strftime("%Y-%m-%d", $seventh);
            $nigth = strftime("%Y-%m-%d", $nigth);
            $tweth = strftime("%Y-%m-%d", $tweth);
            $mode_day = company_mode::where('user_id', $user->id)->whereBetween('date', [$startyear, $endyear])->first();
            // if ($mode_day) {
            //     $mode_day = company_mode::find($mode_day->id);
            //     if (strtotime($month) >= strtotime($sixth)) {
            //         $mode_day->date = Carbon::now();
            //         $mode_day->total_day = 3;
            //     } elseif (strtotime($tamth) >= strtotime($seventh) && strtotime($tamth) < strtotime($month)) {
            //         $mode_day->date = Carbon::now();
            //         $mode_day->total_day = 4;
            //     } elseif (strtotime($muoith) >= strtotime($nigth) && strtotime($nigth) < strtotime($tamth)) {
            //         $mode_day->total_day = 5;
            //         $mode_day->date = Carbon::now();
            //     } elseif (strtotime($muoihaith) > strtotime($tweth) && strtotime($muoihaith) < strtotime($nigth)) {
            //         $mode_day->total_day = 6;
            //         $mode_day->date = Carbon::now();
            //     } else {
            //         $mode_day->total_day = 0;
            //         $mode_day->date = Carbon::now();
            //     }
            //     $mode_day->save();
            // } else {
            $mode_day = new company_mode();
            if (strtotime($month) >= strtotime($sixth) && strtotime($sixth) < strtotime($tamth)) {
                $mode_day->user_id = $user->id;
                $mode_day->date = Carbon::now();
                $mode_day->total_day = 3;
            } else {
                $mode_day->user_id = $user->id;
                $mode_day->total_day = 0;
                $mode_day->date = Carbon::now();
            }

            if (strtotime($tamth) >= strtotime($seventh)) {
                $mode_day->user_id = $user->id;
                $mode_day->date = Carbon::now();
                $mode_day->total_day = 4;
            }
            if (strtotime($muoith) >= strtotime($nigth)) {

                $mode_day->user_id = $user->id;
                $mode_day->total_day = 5;
                $mode_day->date = Carbon::now();
            }
            if (strtotime($muoihaith) > strtotime($tweth)) {

                $mode_day->user_id = $user->id;
                $mode_day->total_day = 6;
                $mode_day->date = Carbon::now();
            }
            $mode_day->save();
            // }
        }
    }
    public function create(Request $request)
    {

        $user_id = 1;
        $user_off = new Calendar_leave();
        if ($request->mode_leave == 1) {
            $mode = company_mode::where('user_id', $user_id);
            if ($mode->total_day - $mode->total_day_off > 0) {
                $mode_user = company_mode::find($mode->id);
                $mode_user->total_day_off = $request->so_ngay_nghi;
                $mode_user->date = Carbon::now();
                $mode_user->save();
            }
        }
        $user_off->time_start = $request->time_start;
        $user_off->time_end = $request->time_end;
        $user_off->note = $request->note;
        $user_off->time_start = $request->time_start;
    }
}
