<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Calendar_leave;
use App\Models\LichChamCong;
use App\Models\TongThuNhap;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        //get lương
        $now = Carbon::now()->toDateString();
        $selected_year = isset($request->year) ? $request->year : null;
        // $selected_yearBetween = isset($request->yearBetween) ? $request->yearBetween : null;
        // $current_year = date("Y");
        $get_salary =
            TongThuNhap::select(
                DB::raw(' DISTINCT total_salary.user_id as user_id'),
                DB::raw('SUM(total_salary.total_net_salary) as tongluong'),
                DB::raw('users.user_account as name')
            )
            ->join('users', 'total_salary.user_id', '=', 'users.id')
            // ->whereBetween('date', [$thangtruoc, $now])
            ->groupBy('user_id', 'name');

        if (Auth::user()->role_id == 1 || Auth::user()->role_id == 2) {
            // get lương cho hr và admin
            if (isset($selected_year)) {
                // get theo year
                $get_salary->whereYear('date', '<', $selected_year + 1);
            }

            // if (isset($selected_yearBetween)) {
            //     $get_salary->whereBetween('date', [$request->yearBetween, $now]);
            // }
            // chỉ nhân viên xem được thông kê lương của nhân viên đó
        } else {
            $get_salary->where('user_id', Auth::user()->id);
            if (isset($selected_year)) {
                // get theo year
                $get_salary->whereYear('date', '<', $selected_year + 1)
                    ->where('user_id', Auth::user()->id);
            }
            // if (isset($selected_yearBetween)) {
            //     $get_salary->whereBetween('date', [$selected_yearBetween, $now])
            //         ->where('user_id', Auth::user()->id);
            //     dd($get_salary->get());
            // }
        }
        // get nhân viên theo năm
        $get_user = User::selectRaw('count(id) as so_luong_user');
        $data_user = $get_user->get();
        // get nhân viên theo phòng ban
        $data_user_in_position = User::selectRaw('count(id) as total_user,position_id')
            ->groupBy('position_id')
            ->get();
        $data_salary = $get_salary->get();
        return $data_salary || $data_user || $data_user_in_position
            ?
            response()->json([
                'status' => true,
                'message' => 'Lấy thông tin thành công',
                'data' => $data_salary, $data_user, $data_user_in_position
            ], 200) :
            response()->json([
                'status' => false,
                'message' => 'lấy thông tin không thành công'
            ], 404);
    }

    public function get_user_late_early(Request $request)
    {
        // get user đi làm muộn và sớm
        $mocgio = "8:15:00";
        $data_user_di_lam_som = User::selectRaw('DISTINCT time_keep_calendar.user_id as user_id,users.id,count(time_keep_calendar.user_id) as 
        tong_ngay_di_som,users.user_account')
            ->join('time_keep_calendar', 'time_keep_calendar.user_id', '=', 'users.id')
            ->with([
                'userinfo'
            ])->where('time_keep_calendar.deleted_at', null)
            ->where('time_of_check_in', '<', $mocgio)
            ->groupBy('user_id', 'users.id', 'users.user_account')->get();
        //data đi làm sớm
        // data đi làm muộn
        $data_user_di_lam_muon = User::selectRaw('DISTINCT time_keep_calendar.user_id as user_id,users.id,count(time_keep_calendar.user_id) as 
        tong_ngay_di_muon,users.user_account')
            ->join('time_keep_calendar', 'time_keep_calendar.user_id', '=', 'users.id')
            ->with([
                'userinfo'
            ])->where('time_keep_calendar.deleted_at', null)
            ->where('time_of_check_in', '>', $mocgio)
            ->groupBy('user_id', 'users.id', 'users.user_account')->get();
        // lay cac ngay trong thang
        return $data_user_di_lam_muon || $data_user_di_lam_som
            ?
            response()->json([
                'status' => true,
                'message' => 'Lấy thông tin thành công',
                'data' => $data_user_di_lam_muon, $data_user_di_lam_som
            ], 200) :
            response()->json([
                'status' => false,
                'message' => 'lấy thông tin không thành công'
            ], 404);
    }
    public function show_lich(Request $request)
    {
        $id = Auth::user()->id;
        $arrDay = [];
        if ($request->date_time) {
            $motnh = date('m', strtotime($request->date_time));
            $year = date('Y', strtotime($request->date_time));
        } else {
            $motnh = date('6');
            $year = date('Y');
        }
        for ($day = 1; $day <= 31; $day++) {
            $time = mktime(12, 0, 0, $motnh, $day, $year);
            if (date('m', $time) == $motnh) {
                $arrDay[] = date('w-Y-m-d', $time);
            }
        }
        $chunhat = 0;
        $thubay = 1;
        $get_lich_lam = LichChamCong::select('date_of_work', 'users.user_account as name')
            ->join('users', 'time_keep_calendar.user_id', '=', 'users.id')
            ->whereMonth('date_of_work', $motnh)
            ->whereYear('date_of_work', $year)
            ->where(function ($query) use ($id) {
                if ($id) {
                    $query->where('user_id', $id);
                } else {
                    $query->where('user_id', 3);
                }
            })
            // ->where('user_id', $id)
            // ->orwhere('user_id', Auth::user()->id)
            ->get()->toArray();
        $arr_lich_lam["total_di_lam"] = [];
        $arrX = [];
        if (count($get_lich_lam) > 0) {
            foreach ($arrDay as $day) {
                $arrthu = explode('-', $day, 2);

                foreach ($get_lich_lam as $key => $value) {
                    if ($value['date_of_work'] == $arrthu[1]) {
                        $lich_lam = 'đi làm';
                        break;
                    } elseif ($arrthu[0] == $chunhat || $arrthu[0] == $thubay) {
                        $lich_lam = null;
                    } else {
                        $lich_lam = 'vắng';
                    }
                    $arr_lich_lam['user'] = $value['name'];
                }
                $arr_lich_lam["total_di_lam"][] = $lich_lam;
                $arr_lich_lam["ngay"][] = $arrthu[1];
            }
        } else {
            $error = "Không có dữ liệu";
        }
        return $arr_lich_lam
            ?
            response()->json([
                'status' => true,
                'message' => 'Lấy thông tin lịch đi làm thành công',
                'data' => $arr_lich_lam,
            ], 200) :
            response()->json([
                'status' => false,
                'message' => $error,
            ], 404);
    }
}
