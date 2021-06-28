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
        $selected_year = isset($request->year) ? $request->year : null;
        $selected_yearBetween = isset($request->yearBetween) ? $request->yearBetween : null;
        // $current_year = date("Y");
        $now = Carbon::now()->toDateString();
        $get_salary =
            TongThuNhap::select(
                DB::raw(' DISTINCT total_salary.user_id as user_id'),
                DB::raw('SUM(total_salary.total_gross_salary) as tongluong'),
                DB::raw('users.user_account as name')
            )
            ->join('users', 'total_salary.user_id', '=', 'users.id')
            // ->whereBetween('date', [$thangtruoc, $now])
            ->groupBy('user_id', 'name');
        // get lương cho hr và admin
        // if (Auth::user()->role == 0 || Auth::user()->role = 1) {

        if (isset($selected_year)) {
            // get theo year
            $get_salary->whereYear('date', '<', $selected_year + 1);
        }

        if (isset($selected_yearBetween)) {
            $get_salary->whereBetween('date', [$request->yearBetween, $now]);
        }
        // chỉ nhân viên xem được thông kê lương của nhân viên đó
        // } else {
        if (isset($selected_year)) {
            // get theo year
            $get_salary->whereYear('date', '<', $selected_year + 1);
            // ->where('id', Auth::user()->id);
        }
        if (isset($selected_yearBetween)) {
            $get_salary->whereBetween('date', [$request->yearBetween, $now]);
            // ->where('id', Auth::user()->id);;
        }
        // }
        $data_salary = $get_salary->get();
        $dataBetween_salary = $get_salary->get();
        // get nhân viên theo năm
        $get_user = User::selectRaw('count(id) as so_luong_user');
        if (isset($selected_year)) {
            // get theo year
            $get_user->whereYear('date', '<', $selected_year + 1)
                ->where('id', Auth::user()->id);
        }
        $data_user = $get_user->get();
        // get nhân viên theo phòng ban
        $data_user_in_position = User::selectRaw('count(id) as total_user,position_id')
            ->groupBy('position_id')
            ->get();
        // get user đi làm muộn và sớm
        $mocgio = "8:15:00";
        $get_user_di_lam = User::selectRaw('DISTINCT time_keep_calendar.user_id as user_id,users.id,count(time_keep_calendar.user_id) as 
        tong_ngay,users.user_account')
            ->join('time_keep_calendar', 'time_keep_calendar.user_id', '=', 'users.id')
            ->with([
                'userinfo'
            ])
            ->groupBy('user_id');
        //data đi làm sớm
        $data_user_di_som = $get_user_di_lam->where('time_of_check_in', '<', $mocgio)->get();
        $data_user_di_muon = $get_user_di_lam->where('time_of_check_in', '>', $mocgio)->get();

        //data đi làm muộn


        //lay cac ngay trong thang
        $id = $request->id;
        $arrDay = [];
        $motnh = date('m');
        $year = date('Y');
        for ($day = 1; $day <= 31; $day++) {
            $time = mktime(12, 0, 0, $motnh, $day, $year);
            if (date('m', $time) == $motnh) {
                $arrDay[] = date('w-Y-m-d', $time);
            }
        }
        $chunhat = 0;
        $thubay = 1;
        $get_lich_lam = User::select('date_of_work', 'users.user_account')
            ->join('time_keep_calendar', 'time_keep_calendar.user_id', '=', 'users.id')
            ->whereMonth('date_of_work', date('m'))
            ->whereYear('date_of_work', date('Y'))
            ->where('user_id', 1)
            ->get()->toArray();
        $arr_lich_lam = [];
        $arrX = [];
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
            }
            $arr_lich_lam[] = $lich_lam;
        }
        return $arr_lich_lam || $data_user_di_muon || $data_user_di_som || $data_user || $data_salary || $dataBetween_salary || $data_user_in_position ?
            response()->json([
                'status' => true,
                'message' => 'Lấy thông tin dashboard thành công',
                'data' => $arr_lich_lam, $data_user_di_muon, $data_user_di_som, $data_user, $data_salary, $dataBetween_salary, $data_user_in_position
            ], 200) :
            response()->json([
                'status' => false,
                'message' => 'Không tìm thấy data',
            ], 404);
    }
}
