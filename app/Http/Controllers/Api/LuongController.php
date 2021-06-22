<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BangThue;
use App\Models\Prize_user;
use App\Models\TongThuNhap;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LuongController extends Controller
{
    public function getAll()
    {
        $luong = TongThuNhap::all();
        return response()->json([
            'status' => true,
            'message' => 'Lấy danh sách lương thành công thành công',
            'data' => $luong
        ])->setStatusCode(200);
    }
    public function getdetail($id)
    {
        $luong = TongThuNhap::find($id);
        return $luong ? response()->json([
            'status' => true,
            'message' => 'Lấy chi tiết lương thành công',
            'data' => $luong
        ])->setStatusCode(200) : response()->json([
            'status' => false,
            'message' => 'Lấy chi tiết lương thấy bại',
        ])->setStatusCode(404);
    }
    public function tinhluong()
    {
        $startmonth = Carbon::now()->startOfMonth()->toDateString();
        $endmonth = Carbon::now()->endOfMonth()->toDateString();
        $getlist = TongThuNhap::wherebetween('date', [$startmonth, $endmonth])->get();
        $checkthue5 = BangThue::where('Tax_percentage', 5)->first();
        $checkthue10 = BangThue::where('Tax_percentage', 10)->first();
        $checkthue15 = BangThue::where('Tax_percentage', 15)->first();
        $checkthue20 = BangThue::where('Tax_percentage', 20)->first();
        $checkthue25 = BangThue::where('Tax_percentage', 25)->first();
        $checkthue30 = BangThue::where('Tax_percentage', 30)->first();
        $checkthue35 = BangThue::where('Tax_percentage', 35)->first();
        foreach ($getlist as $item) {
            $total_gross_salary = $item->total_gross_salary;
            $prize = Prize_user::select(
                DB::raw('prize_money')
            )
                ->join('prize', 'prize.id', '=', 'prize_user.prize_id')
                ->where('user_id', $item->user_id)->wherebetween('date', [$startmonth, $endmonth])
                ->get();

            if ($prize) {
                foreach ($prize as $prize) {
                    $total_gross_salary += $prize->prize_money;
                }
            }
            if (($checkthue5->Taxable_income) < $total_gross_salary && $total_gross_salary <=  ($checkthue10->Taxable_income)) {
                $tongluong = $total_gross_salary - (($total_gross_salary * $checkthue5->Tax_percentage) / 100);
            } elseif (($checkthue10->Taxable_income) < $total_gross_salary && $total_gross_salary <=  ($checkthue15->Taxable_income)) {
                $tongluong =  $total_gross_salary - (($total_gross_salary * $checkthue10->Tax_percentage) / 100 - 250000);
            } elseif (($checkthue15->Taxable_income) < $total_gross_salary && $total_gross_salary <=  ($checkthue20->Taxable_income)) {
                $tongluong =  $total_gross_salary - (($total_gross_salary * $checkthue15->Tax_percentage) / 100 - 750000);
            } elseif (($checkthue20->Taxable_income) < $total_gross_salary && $total_gross_salary <=  ($checkthue25->Taxable_income)) {
                $tongluong =  $total_gross_salary - (($total_gross_salary * $checkthue20->Tax_percentage) / 100 - 1650000);
            } elseif (($checkthue25->Taxable_income) < $total_gross_salary && $total_gross_salary <=  ($checkthue30->Taxable_income)) {
                $tongluong =  $total_gross_salary - (($total_gross_salary * $checkthue25->Tax_percentage) / 100 - 3250000);
            } elseif (($checkthue25->Taxable_income) < $total_gross_salary && $total_gross_salary <=  ($checkthue30->Taxable_income)) {
                $tongluong =  $total_gross_salary - (($total_gross_salary * $checkthue30->Tax_percentage) / 100 - 5850000);
            } elseif (($checkthue35->Taxable_income) < $total_gross_salary) {
                $tongluong =  $total_gross_salary - (($total_gross_salary * $checkthue35->Tax_percentage) / 100 - 9850000);
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
}
