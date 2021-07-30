<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Calendar_leave;
use App\Models\company_mode;
use App\Models\LichChamCong;
use App\Models\phongban;
use App\Models\TongThuNhap;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;

class DashboardController extends Controller
{
    // public function index(Request $request)
    // {
    //     //get lương
    //     $now = Carbon::now()->toDateString();
    //     $selected_year = isset($request->year) ? $request->year : null;
    //     // $selected_yearBetween = isset($request->yearBetween) ? $request->yearBetween : null;
    //     // $current_year = date("Y");
    //     $get_salary =
    //         TongThuNhap::select(
    //             DB::raw(' DISTINCT total_salary.user_id as user_id'),
    //             DB::raw('SUM(total_salary.total_net_salary) as tongluong'),
    //             DB::raw('users.user_account as name')
    //         )
    //         ->join('users', 'total_salary.user_id', '=', 'users.id')
    //         // ->whereBetween('date', [$thangtruoc, $now])
    //         ->groupBy('user_id', 'name');

    //     if (Auth::user()->role_id == 1 || Auth::user()->role_id == 2) {
    //         // get lương cho hr và admin
    //         if (isset($selected_year)) {
    //             // get theo year
    //             $get_salary->whereYear('date', '<', $selected_year + 1);
    //         }

    //         // if (isset($selected_yearBetween)) {
    //         //     $get_salary->whereBetween('date', [$request->yearBetween, $now]);
    //         // }
    //         // chỉ nhân viên xem được thông kê lương của nhân viên đó
    //     } else {
    //         $get_salary->where('user_id', Auth::user()->id);
    //         if (isset($selected_year)) {
    //             // get theo year
    //             $get_salary->whereYear('date', '<', $selected_year + 1)
    //                 ->where('user_id', Auth::user()->id);
    //         }
    //         // if (isset($selected_yearBetween)) {
    //         //     $get_salary->whereBetween('date', [$selected_yearBetween, $now])
    //         //         ->where('user_id', Auth::user()->id);
    //         //     dd($get_salary->get());
    //         // }
    //     }
    //     // get nhân viên theo năm
    //     $get_user = User::selectRaw('count(id) as so_luong_user');
    //     $data_user = $get_user->get();
    //     // get nhân viên theo phòng ban
    //     $data_user_in_department = User::selectRaw('count(id) as total_user,department_id')
    //         ->groupBy('department_id')
    //         ->get();
    //     $data_salary = $get_salary->get();
    //     return $data_salary || $data_user || $data_user_in_department
    //         ?
    //         response()->json([
    //             'status' => true,
    //             'message' => 'Lấy thông tin thành công',
    //             'data' => $data_salary, $data_user, $data_user_in_department
    //         ], 200) :
    //         response()->json([
    //             'status' => false,
    //             'message' => 'lấy thông tin không thành công'
    //         ], 404);
    // }
    public function total_user_team_work_leave()
    {
        $today = Carbon::now()->toDateString();
        $total_user = User::selectRaw('count(id) as so_luong_user')->get();
        $total_department = phongban::selectRaw('count(id) as so_luong_phong_ban')->get();
        $total_user_work = LichChamCong::selectRaw('count(id) as nhan_vien_di_lam')
            ->where('date_of_work', $today)->get();
        $total_user_off = Calendar_leave::selectRaw('count(id) as nhan_vien_nghi_lam')
            ->where('date', $today)->where('status', 1)->get();
        $total_user_in_department = User::selectRaw('count(id) as total_user,department_id')
            ->groupBy('department_id')
            ->get();
        $total_user_in_department->load('phongban_userinfo');
        return response()->json([
            'status' => true,
            'message' => 'Lấy thông tin thành công',
            'data' => $total_user, $total_department, $total_user_work, $total_user_off, $total_user_in_department
        ], 200);
    }
    public function get_user_late_early()
    {
        // get user đi làm muộn và sớm
        $mocgio = "8:15:00";
        $data_user_di_lam_som = User::selectRaw('DISTINCT time_keep_calendar.user_id as user_id,users.id,count(time_keep_calendar.user_id) as 
        tong_ngay_di_som,users.user_account')
            ->join('time_keep_calendar', 'time_keep_calendar.user_id', '=', 'users.id')
            ->with([
                'userinfo'
            ])->where('time_keep_calendar.deleted_at', null)
            ->where('time_of_check_in', '<=', $mocgio)
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
            $motnh = date('m');
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
            ->where('user_id', $id)
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
    public function comfig($id, Request $request)
    {
        if (Gate::allows('confirmLeave')) {
            // $check = Calendar_leave::where('id', $id)->first();
            $startmonth = Carbon::now()->startOfMonth()->toDateString();
            $endmonth = Carbon::now()->endOfMonth()->toDateString();
            $today = Carbon::now()->toDateString();
            if (isset($request->yes)) {
                $lich_xin_nghi = Calendar_leave::find($id);
                if ($lich_xin_nghi) {

                    $lich_xin_nghi->status = 1;
                    $lich_xin_nghi->save();
                    $mode = company_mode::where('user_id', $lich_xin_nghi->user_id)->whereYear('date', $today)->first();
                    if ($mode->total_day - $lich_xin_nghi->number_mode_leave >= 0 && ($mode->total_day_off + $lich_xin_nghi->number_mode_leave <= $mode->total_day)) {
                        $mode_user = company_mode::find($mode->id);
                        $mode_user->total_day_off += $lich_xin_nghi->number_mode_leave;
                        $mode_user->date = Carbon::now();
                        $mode_user->save();
                    }
                    $user = User::where('users.id', $lich_xin_nghi->user_id)
                        ->join('user_info', 'users.id', '=', 'user_info.user_id')->first();
                    $check = TongThuNhap::where('user_id', $lich_xin_nghi->user_id)
                        ->wherebetween('date', [$startmonth, $endmonth])->first();
                    $luongcoban = $user->basic_salary;
                    $salaryOneDay = $luongcoban / 22;
                    $totalSalary_leave = $salaryOneDay * ($lich_xin_nghi->number_mode_leave);
                    $total_salary_leave = TongThuNhap::find($check->id);
                    $total_salary_leave->total_salary_leave = $totalSalary_leave;
                    $total_salary_leave->save();
                    $to_name = $user->user_account;
                    $to_email = $user->email;
                    $data = array('name' => 'Hello' . $to_name, 'body' => 'Công ty đã nhận được đơn xin nghỉ của bạn và 
                công ty chấp nhận đơn xin nghỉ của bạn.');
                    Mail::send('emails.mail', $data, function ($message) use ($to_name, $to_email) {
                        $message->to($to_email, $to_name)->subject('V.v nghỉ phép');
                        $message->from('tuantong.datus@gmail.com');
                    });
                    $mess = "Đồng ý cho nghỉ";
                }
            } else {
                $lich_xin_nghi = Calendar_leave::find($id);
                if ($lich_xin_nghi) {
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
                    $mess = "Không cho phép nghỉ";
                }
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
    public function list_comfig()
    {
        if (Gate::allows('view')) {
            $lich_xin_nghi = Calendar_leave::select('calendar_for_leave.*', 'user_info.full_name', 'user_info.avatar')
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
    public function list_user_leave()
    {
        $lich_xin_nghi = Calendar_leave::select('calendar_for_leave.*', 'user_info.full_name', 'user_info.avatar')
            ->Join('users', 'calendar_for_leave.user_id', '=', 'users.id')
            ->join('user_info', 'users.id', '=', 'user_info.user_id')
            ->where('calendar_for_leave.deleted_at', null)
            ->where('status', 1)
            ->where('date', Carbon::now()->toDateString())
            ->get();
    }
    public function luong_theo_thang(Request $request)
    {
        $arrMonth = [];
        if ($request->date_time) {
            $motnh = date('m');
            $year = date('Y', strtotime($request->date_time));
            // $day = date('d', strtotime($request->date_time));
        } else {
            $motnh = date('m');
            $year = date('Y');
            $day = date('d');
            $to_day = Carbon::now()->toDateString();
        }
        for ($motnh = 1; $motnh <= 12; $motnh++) {
            $time = mktime(12, 0, 0, $motnh, 1, $year);
            if (date('Y', $time) == $year) {
                $arrMonth[] = date('Y-m-d', $time);
            }
        }
        $get_thu_nhap = DB::table('total_salary')
            ->selectRaw('department_id,date,department.name,Sum(total_net_salary) as total_net_salary')
            ->Join('users', 'total_salary.user_id', '=', 'users.id')
            ->Join('department', 'users.department_id', '=', 'department.id')
            ->whereYear('total_salary.date', $year)
            ->where('department_id', 1)
            ->groupBy('department_id')
            ->first();
        $tong = [];
        $Arrseries = [];
        $Arrseries['name'] = $get_thu_nhap->name;
        foreach ($arrMonth as $day) {
            $x = date('m', strtotime($get_thu_nhap->date));
            $y = date('m', strtotime($day));
            $chuyenthang = explode('-', $day, 3);
            if ($x == $y) {
                $series['series'] = [
                    'luong' => $get_thu_nhap->total_net_salary,
                    'date' => 'Tháng ' . $chuyenthang[1]
                ];
            } else {
                $series['series'] = [
                    'luong' => 0,
                    'date' => 'Tháng ' . $chuyenthang[1]
                ];
            }
            $Arrseries['series'][] = $series['series'];
        }
        array_push($tong, $Arrseries);
        $get_phong_ban_2 = DB::table('total_salary')
            ->selectRaw('department_id,date,department.name,Sum(total_net_salary) as total_net_salary')
            ->Join('users', 'total_salary.user_id', '=', 'users.id')
            ->Join('department', 'users.department_id', '=', 'department.id')
            ->whereYear('total_salary.date', $year)
            ->where('department_id', 2)
            ->groupBy('department_id')
            ->first();
        $ArrService_2 = [];
        $ArrService_2['name'] = $get_phong_ban_2->name;
        foreach ($arrMonth as $day) {

            $x = date('m', strtotime($get_phong_ban_2->date));
            $y = date('m', strtotime($day));
            $chuyenthang = explode('-', $day, 3);
            if ($x == $y) {
                $series['series'] = [
                    'luong' => $get_phong_ban_2->total_net_salary,
                    'date' => 'Tháng ' . $chuyenthang[1]
                ];
            } else {
                $series['series'] = [
                    'luong' => 0,
                    'date' => 'Tháng ' . $chuyenthang[1]
                ];
            }
            $ArrService_2['series'][] = $series['series'];
        }
        array_push($tong, $ArrService_2);

        $get_phong_ban_3 = DB::table('total_salary')
            ->selectRaw('department_id,date,department.name,Sum(total_net_salary) as total_net_salary')
            ->Join('users', 'total_salary.user_id', '=', 'users.id')
            ->Join('department', 'users.department_id', '=', 'department.id')
            ->whereYear('total_salary.date', $year)
            ->where('department_id', 3)
            ->groupBy('department_id')
            ->first();
        $ArrService_3 = [];
        $ArrService_3['name'] = $get_phong_ban_3->name;
        foreach ($arrMonth as $day) {

            $x = date('m', strtotime($get_phong_ban_3->date));
            $y = date('m', strtotime($day));
            $chuyenthang = explode('-', $day, 3);
            if ($x == $y) {
                $series['series'] = [
                    'luong' => $get_phong_ban_3->total_net_salary,
                    'date' => 'Tháng ' . $chuyenthang[1]
                ];
            } else {
                $series['series'] = [
                    'luong' => 0,
                    'date' => 'Tháng ' . $chuyenthang[1]
                ];
            }
            $ArrService_3['series'][] = $series['series'];
        }
        array_push($tong, $ArrService_3);
        return response()->json([
            'status' => true,
            'message' => 'lấy thành công',
            'data' => $tong
        ]);
    }
}
