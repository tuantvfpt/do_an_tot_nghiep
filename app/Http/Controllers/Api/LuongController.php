<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BangThue;
use App\Models\TongThuNhap;
use Carbon\Carbon;
use Illuminate\Http\Request;

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
        $checkthue5 = BangThue::where('tax', 5)->first();
        $checkthue10 = BangThue::where('tax', 10)->first();
        $checkthue15 = BangThue::where('tax', 15)->first();
        $checkthue20 = BangThue::where('tax', 20)->first();
        $checkthue25 = BangThue::where('tax', 25)->first();
        $checkthue30 = BangThue::where('tax', 30)->first();
        $checkthue35 = BangThue::where('tax', 35)->first();
        $x = 750000;
        foreach ($getlist as $item) {
            $total_gross_salary = $item->total_gross_salary;
            if (($checkthue5->total_income) < $total_gross_salary && $total_gross_salary <=  ($checkthue10->toltal_income)) {
                $tongluong = $total_gross_salary - ($total_gross_salary * $checkthue5->tax);
            } elseif (($checkthue10->total_income) < $total_gross_salary && $total_gross_salary <  ($checkthue15->toltal_income)) {
                $tongluong = ($total_gross_salary * $checkthue15->tax);
                dd($tongluong);
            } else {
                $tongluong = $total_gross_salary;
            }
        }
    }
}
