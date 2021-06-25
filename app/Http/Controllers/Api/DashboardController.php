<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TongThuNhap;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{

    public function index()
    {
        $thangtruoc = Carbon::now()->subMonth(6)->toDateString();
        $now = Carbon::now()->toDateString();
        $getdata =
            TongThuNhap::select(
                DB::raw(' DISTINCT total_salary.user_id as user_id'),
                DB::raw('SUM(total_salary.total_gross_salary) as tongluong'),
                DB::raw('users.user_account as name')
            )
            ->join('users', 'total_salary.user_id', '=', 'users.id')
            ->whereBetween('date', [$thangtruoc, $now])
            ->groupBy('user_id', 'name')
            ->get();
        dd($getdata);
    }
    
}
