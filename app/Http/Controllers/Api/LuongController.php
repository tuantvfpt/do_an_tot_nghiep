<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BangThue;
use App\Models\Calendar_leave;
use App\Models\LichChamCong;
use App\Models\Prize_user;
use App\Models\TongThuNhap;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class LuongController extends Controller
{
    public function getAll(Request $request)
    {

        if (Gate::allows('view')) {
            $luong = TongThuNhap::select('total_salary.*', 'user_info.full_name')
                ->Join('users', 'total_salary.user_id', '=', 'users.id')
                ->join('user_info', 'users.id', '=', 'user_info.user_id')
                ->where('total_salary.deleted_at', null);
            if (!empty($request->keyword)) {
                $luong =  $luong->Where(function ($query) use ($request) {
                    $query->where('user_info.full_name', 'like', "%" . $request->keyword . "%");
                });
            }
            if (!empty($request->date)) {
                $luong =  $luong->whereMonth('total_salary.date', date('m', strtotime($request->date)));
            }
            $luong = $luong->paginate(($request->limit != null) ? $request->limit : 10);
            $response =  response()->json([
                'status' => true,
                'message' => 'Lấy danh sách lương thành công thành công',
                'data' => $luong->items(),
                'meta' => [
                    'total'      => $luong->total(),
                    'perPage'    => $luong->perPage(),
                    'currentPage' => $luong->currentPage()
                ]
            ])->setStatusCode(200);
        } else {
            $response = response()->json([
                'status' => true,
                'message' => 'Không có quyền truy cập',
            ]);
        }
        return $response;
    }
    public function getdetail($id)
    {
        $today = Carbon::now()->toDateString();
        $check = TongThuNhap::where('user_id', Auth::user()->id)
            ->where('id', $id)->first();
        if (Gate::allows('view/id') || $check) {
            $luong = TongThuNhap::find($id);
            if ($luong) {

                $tong_ngay_lam = LichChamCong::selectRaw('count(user_id) as total_day_to_work')
                    ->where('user_id', $luong->user_id)
                    ->whereMonth('date_of_work', date('m', strtotime($luong->date)))
                    ->whereYear('date_of_work', date('Y', strtotime($luong->date)))
                    ->first();
                $tong_ngay_xin_nghi = Calendar_leave::selectRaw('Sum(number_day_leave) as total_day_leave,Sum(number_mode_leave) as total_day_mode')
                    ->where('user_id', $luong->user_id)
                    ->whereMonth('date', date('m', strtotime($luong->date)))
                    ->whereYear('date', date('Y', strtotime($luong->date)))
                    ->first();
                $get_fine_money = Prize_user::selectRaw('SUM(prize_fine.fine_money) as total_money_fine')
                    ->join('prize_fine', 'prize_fine.id', '=', 'prize_fine_user.prize_fine_id')
                    ->where('user_id', $luong->user_id)
                    ->whereMonth('date', date('m', strtotime($luong->date)))
                    ->whereYear('date', date('Y', strtotime($luong->date)))
                    ->where('prize_money', null)
                    ->first();
                $get_pize_money = Prize_user::selectRaw('SUM(prize_fine.prize_money) as total_money_prize')
                    ->join('prize_fine', 'prize_fine.id', '=', 'prize_fine_user.prize_fine_id')
                    ->where('user_id', $luong->user_id)
                    ->whereMonth('date', date('m', strtotime($luong->date)))
                    ->whereYear('date', date('Y', strtotime($luong->date)))
                    ->where('fine_money', null)
                    ->first();
            }
            $response = $luong ? response()->json([
                'status' => true,
                'message' => 'Lấy chi tiết lương thành công',
                'data' => $luong, $tong_ngay_lam, $tong_ngay_xin_nghi, $get_fine_money, $get_pize_money
            ])->setStatusCode(200) : response()->json([
                'status' => false,
                'message' => 'Lấy chi tiết lương thấy bại',
            ])->setStatusCode(404);
        } elseif ($check == null) {
            $response = response()->json([
                'status' => false,
                'message' => "Bạn không có quyền truy cập",
            ])->setStatusCode(404);
        }
        return  $response;
    }
    public function getSalaryByUser(Request $request)
    {
        $luong = TongThuNhap::select('total_salary.*', 'user_info.full_name')
            ->Join('users', 'total_salary.user_id', '=', 'users.id')
            ->join('user_info', 'users.id', '=', 'user_info.user_id')
            ->where('total_salary.deleted_at', null)
            ->where('total_salary.user_id', Auth::user()->id);
        if (!empty($request->keyword)) {
            $luong =  $luong->Where(function ($query) use ($request) {
                $query->where('user_info.full_name', 'like', "%" . $request->keyword . "%");
            });
        }
        if (!empty($request->date)) {
            $luong =  $luong->whereMonth('total_salary.date', date('m', strtotime($request->date)));
        }
        $luong = $luong->paginate(($request->limit != null) ? $request->limit : 10);
        return response()->json([
            'status' => true,
            'message' => 'Lấy danh sách lương thành công thành công',
            'data' => $luong->items(),
            'meta' => [
                'total'      => $luong->total(),
                'perPage'    => $luong->perPage(),
                'currentPage' => $luong->currentPage()
            ]
        ])->setStatusCode(200);
    }
    public function tra_luong($id)
    {
        $luong = LichChamCong::find($id);
        if ($luong) {
            $luong->status = 1;
        }
        $luong->save();
    }
    public function luong()
    {
        $user = User::select('users.*', 'user_info.basic_salary')
            ->join('user_info', 'users.id', '=', 'user_info.user_id')->get();
        foreach ($user as $user) {
            // $checkluong = userInfo::where('user_id', $user->id)->first();
            $startmonth = Carbon::now()->startOfMonth()->toDateString();
            $endmonth = Carbon::now()->endOfMonth()->toDateString();
            $tongtime = LichChamCong::where('user_id', $user->id)
                ->where('date_of_work', '>=', $startmonth)
                ->where('date_of_work', '<=', $endmonth)
                ->get();
            $muoihaigio = "12:00:00";
            $muoibagio = "13:00:00";
            $muoibaygio = "17:00:00";
            $tongtimecheckin = 0;
            foreach ($tongtime as $item) {
                if ($item->check_ot == 0) {
                    if (strtotime($muoihaigio) - strtotime($item->time_of_check_in) < 0) {
                        $x = 0;
                    } elseif (strtotime($item->time_of_check_out) - strtotime($muoihaigio) < 0) {
                        $x = strtotime($item->time_of_check_out) - strtotime($item->time_of_check_in);
                    } else {
                        $x = strtotime($muoihaigio) - strtotime($item->time_of_check_in);
                    }
                    if (strtotime($item->time_of_check_out) - strtotime($muoibagio) < 0) {
                        $b = 0;
                    } elseif (strtotime($item->time_of_check_in) - strtotime($muoibagio) > 0) {
                        $b = strtotime($item->time_of_check_out) - strtotime($item->time_of_check_in);
                    } else {
                        if (strtotime($item->time_of_check_out) - strtotime($muoibaygio) < 0) {
                            $b = strtotime($item->time_of_check_out) - strtotime($muoibagio);
                        } else {
                            $b = strtotime($muoibaygio) - strtotime($muoibagio);
                        }
                    }
                } else {
                    if (strtotime($muoihaigio) - strtotime($item->time_of_check_in) < 0) {
                        $x = 0;
                    } elseif (strtotime($item->time_of_check_out) - strtotime($muoihaigio) < 0) {
                        $x = strtotime($item->time_of_check_out) - strtotime($item->time_of_check_in);
                    } else {
                        $x = strtotime($muoihaigio) - strtotime($item->time_of_check_in);
                    }
                    if (strtotime($item->time_of_check_out) - strtotime($muoibagio) < 0) {
                        $b = 0;
                    } elseif (strtotime($item->time_of_check_in) - strtotime($muoibagio) > 0) {
                        $b = strtotime($item->time_of_check_out) - strtotime($item->time_of_check_in);
                    } elseif (strtotime($item->time_of_check_out) - strtotime($muoibagio) > 0 && (strtotime($item->time_of_check_out) <= (strtotime($muoibaygio)))) {
                        $b = strtotime($item->time_of_check_out) - strtotime($muoibagio);
                    } elseif (strtotime($item->time_of_check_out) - strtotime($muoibaygio) > 0) {
                        $c = (strtotime($item->time_of_check_out) - strtotime($muoibaygio)) * 2;
                        $b = strtotime($muoibaygio) - strtotime($muoibagio) + $c;
                    }
                }
                $tongtimecheckin += ($x + $b);
            }
            $tongtimelam = $tongtimecheckin / 3600;
            $luongcoban = $user->basic_salary;
            $timecodinh = 8;
            if (($tongtimelam - $timecodinh * 22) > 0) {
                $tongluong = ($luongcoban + ($tongtimelam - ($timecodinh * 22) * ($luongcoban / (22 * 8))));
            } elseif (($tongtimelam - $timecodinh * 22) == 0) {
                $tongluong = $luongcoban;
            } else {
                $tongluong = ($luongcoban - (($timecodinh * 22) - $tongtimelam) * ($luongcoban / (22 * 8)));
            }
            $formatluong = $tongluong;
            if (isset($formatluong)) {
                $checktongluong = TongThuNhap::where('user_id', $user->id)
                    ->where('date', '>=', $startmonth)
                    ->where('date', '<=', $endmonth)
                    ->first();
                if ($checktongluong) {
                    $luong = TongThuNhap::find($checktongluong->id);
                    $luong->total_gross_salary = $formatluong;
                    $luong->date = Carbon::now()->toDateString();
                    $luong->save();
                } else {
                    $luong = new TongThuNhap();
                    $luong->user_id = $user->id;
                    $luong->total_gross_salary = $formatluong;
                    $luong->total_net_salary = 0;
                    $luong->status = "0";
                    $luong->date = Carbon::now();
                    $luong->save();
                }
            }
        }
    }

    public function tinhluong()
    {
        $startmonth = Carbon::now()->startOfMonth()->toDateString();
        $endmonth = Carbon::now()->endOfMonth()->toDateString();
        $getlist = TongThuNhap::wherebetween('date', [$startmonth, $endmonth])->get();
        $checkthue5 = BangThue::where('tax_percentage', 5)->first();
        $checkthue10 = BangThue::where('tax_percentage', 10)->first();
        $checkthue15 = BangThue::where('tax_percentage', 15)->first();
        $checkthue20 = BangThue::where('tax_percentage', 20)->first();
        $checkthue25 = BangThue::where('tax_percentage', 25)->first();
        $checkthue30 = BangThue::where('tax_percentage', 30)->first();
        $checkthue35 = BangThue::where('tax_percentage', 35)->first();
        foreach ($getlist as $item) {
            $total_gross_salary = $item->total_gross_salary + $item->total_salary_leave;
            $prize = Prize_user::select(
                DB::raw('prize_money,fine_money')
            )
                ->join('prize_fine', 'prize_fine.id', '=', 'prize_fine_user.prize_fine_id')
                ->where('user_id', $item->user_id)->wherebetween('date', [$startmonth, $endmonth])
                ->get();
            if ($prize) {
                foreach ($prize as $prize) {
                    if ($prize->prize_money != null || $prize->prize_money > 0) {
                        $total_gross_salary += $prize->prize_money;
                    } else {
                        $total_gross_salary =  $total_gross_salary - $prize->fine_money;
                    }
                }
            }

            if (($checkthue5->taxable_income) < $total_gross_salary && $total_gross_salary <=  ($checkthue10->taxable_income)) {
                $tongluong = $total_gross_salary - (($total_gross_salary * $checkthue5->tax_percentage) / 100);
            } elseif (($checkthue10->taxable_income) < $total_gross_salary && $total_gross_salary <=  ($checkthue15->taxable_income)) {
                $tongluong =  $total_gross_salary - (($total_gross_salary * $checkthue10->tax_percentage) / 100 - 250000);
            } elseif (($checkthue15->taxable_income) < $total_gross_salary && $total_gross_salary <=  ($checkthue20->taxable_income)) {
                $tongluong =  $total_gross_salary - (($total_gross_salary * $checkthue15->tax_percentage) / 100 - 750000);
            } elseif (($checkthue20->taxable_income) < $total_gross_salary && $total_gross_salary <=  ($checkthue25->taxable_income)) {
                $tongluong =  $total_gross_salary - (($total_gross_salary * $checkthue20->tax_percentage) / 100 - 1650000);
            } elseif (($checkthue25->taxable_income) < $total_gross_salary && $total_gross_salary <=  ($checkthue30->taxable_income)) {
                $tongluong =  $total_gross_salary - (($total_gross_salary * $checkthue25->tax_percentage) / 100 - 3250000);
            } elseif (($checkthue25->taxable_income) < $total_gross_salary && $total_gross_salary <=  ($checkthue30->taxable_income)) {
                $tongluong =  $total_gross_salary - (($total_gross_salary * $checkthue30->tax_percentage) / 100 - 5850000);
            } elseif (($checkthue35->taxable_income) < $total_gross_salary) {
                $tongluong =  $total_gross_salary - (($total_gross_salary * $checkthue35->tax_percentage) / 100 - 9850000);
            } else {
                $tongluong = $total_gross_salary;
            }
            if (isset($tongluong)) {
                $luong_net = TongThuNhap::find($item->id);
                if ($luong_net) {
                    $luong_net->total_net_salary = $tongluong;
                    $luong_net->save();
                }
            }
        }
    }
    // public function getSalary6Month(){
    //     $currentDateTime = Carbon::now()->toDateString();
    //     $newDateTime = Carbon::now()->subMonths(6)->toDateString();
    //     $list6month  = TongThuNhap::select(
    //         DB::raw('user_id')
    //     )
    //     ->join('users', 'users.id', '=', 'total_salary.user_id')
    //     ->where('user_id','=', 1)->wherebetween('date', [ $newDateTime, $currentDateTime])
    //     ->sum('total_salary.total_net_salary');
    //     dd($list6month);
    // }
    // public function getSalary1year(){
    //     $currentDateTime = Carbon::now()->startOfYear()->toDateString();
    //     $newDateTime = Carbon::now()->endOfYear()->toDateString();
    //     $list6month  = TongThuNhap::select(
    //         DB::raw('user_id')
    //     )
    //     ->join('users', 'users.id', '=', 'total_salary.user_id')
    //     ->where('user_id','=', 1)->wherebetween('date', [ $currentDateTime, $newDateTime ])
    //     ->sum('total_salary.total_net_salary');
    //     dd($list6month);
    // }

}
