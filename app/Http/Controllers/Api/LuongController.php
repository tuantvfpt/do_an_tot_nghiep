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
        $luong = TongThuNhap::select('total_salary.*', 'user_info.full_name')
            ->Join('users', 'total_salary.user_id', '=', 'users.id')
            ->join('user_info', 'users.id', '=', 'user_info.user_id')
            ->where('total_salary.deleted_at', null);
        if (!Gate::allows('view')) {
            $luong->where('total_salary.user_id', Auth::user()->id);
            if (!empty($request->keyword)) {
                $luong =  $luong->Where(function ($query) use ($request) {
                    $query->where('user_info.full_name', 'like', "%" . $request->keyword . "%");
                });
            }
            if (!empty($request->date)) {
                $luong =  $luong->where('date', $request->date);
            }
        }
        $luong = $luong->paginate(($request->limit != null) ? $request->limit : 5);
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


    // public function tinhluong()
    // {
    //     $startmonth = Carbon::now()->startOfMonth()->toDateString();
    //     $endmonth = Carbon::now()->endOfMonth()->toDateString();
    //     $getlist = TongThuNhap::wherebetween('date', [$startmonth, $endmonth])->get();
    //     $checkthue5 = BangThue::where('Tax_percentage', 5)->first();
    //     $checkthue10 = BangThue::where('Tax_percentage', 10)->first();
    //     $checkthue15 = BangThue::where('Tax_percentage', 15)->first();
    //     $checkthue20 = BangThue::where('Tax_percentage', 20)->first();
    //     $checkthue25 = BangThue::where('Tax_percentage', 25)->first();
    //     $checkthue30 = BangThue::where('Tax_percentage', 30)->first();
    //     $checkthue35 = BangThue::where('Tax_percentage', 35)->first();
    //     foreach ($getlist as $item) {
    //         $total_gross_salary = $item->total_gross_salary;
    //         $prize = Prize_user::select(
    //             DB::raw('prize_money,fine_money')
    //         )
    //             ->join('prize_fine', 'prize_fine.id', '=', 'prize_fine_user.prize_fine_id')
    //             ->where('user_id', 1)->wherebetween('date', [$startmonth, $endmonth])
    //             ->get();
    //         if ($prize) {
    //             foreach ($prize as $prize) {
    //                 if ($prize->prize_money != null || $prize->prize_money > 0) {
    //                     $total_gross_salary += $prize->prize_money;
    //                 } else {
    //                     $total_gross_salary =  $total_gross_salary - $prize->fine_money;
    //                 }
    //             }
    //         }
    //         if (($checkthue5->Taxable_income) < $total_gross_salary && $total_gross_salary <=  ($checkthue10->Taxable_income)) {
    //             $tongluong = $total_gross_salary - (($total_gross_salary * $checkthue5->Tax_percentage) / 100);
    //         } elseif (($checkthue10->Taxable_income) < $total_gross_salary && $total_gross_salary <=  ($checkthue15->Taxable_income)) {
    //             $tongluong =  $total_gross_salary - (($total_gross_salary * $checkthue10->Tax_percentage) / 100 - 250000);
    //         } elseif (($checkthue15->Taxable_income) < $total_gross_salary && $total_gross_salary <=  ($checkthue20->Taxable_income)) {
    //             $tongluong =  $total_gross_salary - (($total_gross_salary * $checkthue15->Tax_percentage) / 100 - 750000);
    //         } elseif (($checkthue20->Taxable_income) < $total_gross_salary && $total_gross_salary <=  ($checkthue25->Taxable_income)) {
    //             $tongluong =  $total_gross_salary - (($total_gross_salary * $checkthue20->Tax_percentage) / 100 - 1650000);
    //         } elseif (($checkthue25->Taxable_income) < $total_gross_salary && $total_gross_salary <=  ($checkthue30->Taxable_income)) {
    //             $tongluong =  $total_gross_salary - (($total_gross_salary * $checkthue25->Tax_percentage) / 100 - 3250000);
    //         } elseif (($checkthue25->Taxable_income) < $total_gross_salary && $total_gross_salary <=  ($checkthue30->Taxable_income)) {
    //             $tongluong =  $total_gross_salary - (($total_gross_salary * $checkthue30->Tax_percentage) / 100 - 5850000);
    //         } elseif (($checkthue35->Taxable_income) < $total_gross_salary) {
    //             $tongluong =  $total_gross_salary - (($total_gross_salary * $checkthue35->Tax_percentage) / 100 - 9850000);
    //         } else {
    //             $tongluong = $total_gross_salary;
    //         }
    //         if (isset($tongluong)) {
    //             $luong_net = TongThuNhap::find($item->id);
    //             if ($luong_net) {
    //                 $luong_net->total_net_salary = $tongluong;
    //                 $luong_net->save();
    //                 dd($luong_net);
    //             }
    //         }
    //     }
    // }
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
