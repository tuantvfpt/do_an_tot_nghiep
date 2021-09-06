<?php

namespace App\Console\Commands;

use App\Models\BangThue;
use App\Models\Prize_user;
use App\Models\TongThuNhap;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class update_salary_every_month extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:update_salary_every_month';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
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
            $mucluong = "11000000";
            $phanluongtinhthue = $total_gross_salary - $mucluong;
            if (($checkthue5->taxable_income) > $phanluongtinhthue) {
                $tongluong = $phanluongtinhthue - (($phanluongtinhthue * $checkthue5->tax_percentage));
            } elseif (($checkthue5->taxable_income) < $phanluongtinhthue && $phanluongtinhthue <=  ($checkthue10->taxable_income)) {
                $tongluong =  $phanluongtinhthue - (($phanluongtinhthue * $checkthue10->tax_percentage) / 100 - 250000);
            } elseif (($checkthue10->taxable_income) < $phanluongtinhthue && $phanluongtinhthue <=  ($checkthue15->taxable_income)) {
                $tongluong =  $phanluongtinhthue - (($phanluongtinhthue * $checkthue15->tax_percentage) / 100 - 750000);
            } elseif (($checkthue15->taxable_income) < $phanluongtinhthue && $phanluongtinhthue <=  ($checkthue20->taxable_income)) {
                $tongluong =  $phanluongtinhthue - (($phanluongtinhthue * $checkthue20->tax_percentage) / 100 - 1650000);
            } elseif (($checkthue20->taxable_income) < $phanluongtinhthue && $phanluongtinhthue <=  ($checkthue25->taxable_income)) {
                $tongluong =  $phanluongtinhthue - (($phanluongtinhthue * $checkthue25->tax_percentage) / 100 - 3250000);
            } elseif (($checkthue25->taxable_income) < $phanluongtinhthue && $phanluongtinhthue <=  ($checkthue30->taxable_income)) {
                $tongluong =  $phanluongtinhthue - (($phanluongtinhthue * $checkthue30->tax_percentage) / 100 - 5850000);
            } elseif (($checkthue35->taxable_income) < $phanluongtinhthue) {
                $tongluong =  $phanluongtinhthue - (($phanluongtinhthue * $checkthue35->tax_percentage) / 100 - 9850000);
            }
            if (isset($tongluong)) {
                $luong_net = TongThuNhap::find($item->id);
                if ($luong_net) {
                    $luong_net->total_net_salary = $tongluong + $mucluong;
                    $luong_net->save();
                }
            }
        }
    }
}
