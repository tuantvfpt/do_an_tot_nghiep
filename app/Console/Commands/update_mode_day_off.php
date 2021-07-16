<?php

namespace App\Console\Commands;

use App\Models\company_mode;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class update_mode_day_off extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:update_mode_day_off';

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
        $startyear = Carbon::now()->startOfYear()->toDateString();
        $endyear = Carbon::now()->endOfYear()->toDateString();
        $today = date('Y-m-d');
        $user = DB::table('users')
            ->select('*')
            ->rightjoin('user_info', 'users.id', '=', 'user_info.user_id')
            ->where('users.deleted_at', null)
            ->get();
        foreach ($user as $user) {
            // tính thời gian làm việc của nhân viên được bao nhiêu tháng
            $dateDiff = date_diff(date_create($user->date_of_join), date_create($today));
            $x = $dateDiff->m;
            $i = $dateDiff->y;
            $mode_day = new company_mode();
            //kiểm tra điều kiện
            $check = company_mode::where('user_id', $user->user_id)->whereBetween('date', [$startyear, $endyear])->first();
            if ($check) {
                $mode_day = company_mode::find($check->id);
            }
            if ($i < 1) {
                if ($x >= 1 && $x < 2 && $i < 1) {
                    $mode_day->user_id = $user->user_id;
                    $mode_day->date = Carbon::now()->todateString();
                    $mode_day->total_day = 1;
                } elseif ($x >= 2 && $x < 3 && $i < 1) {
                    $mode_day->user_id = $user->user_id;
                    $mode_day->date = Carbon::now()->todateString();
                    $mode_day->total_day = 2;
                } elseif ($x >= 3 && $x < 4 && $i < 1) {
                    $mode_day->user_id = $user->user_id;
                    $mode_day->date = Carbon::now()->todateString();
                    $mode_day->total_day = 3;
                } elseif ($x >= 4 && $x < 5 && $i < 1) {
                    $mode_day->user_id = $user->user_id;
                    $mode_day->total_day = 4;
                    $mode_day->date = Carbon::now()->todateString();
                } elseif ($x >= 5 && $x < 6 && $i < 1) {
                    $mode_day->user_id = $user->user_id;
                    $mode_day->total_day = 5;
                    $mode_day->date = Carbon::now()->todateString();
                } elseif ($x >= 6 && $x < 7 && $i < 1) {
                    $mode_day->user_id = $user->user_id;
                    $mode_day->total_day = 6;
                    $mode_day->date = Carbon::now()->todateString();
                } elseif ($x >= 7 && $x < 8 && $i < 1) {
                    $mode_day->user_id = $user->user_id;
                    $mode_day->total_day = 7;
                    $mode_day->date = Carbon::now()->todateString();
                } elseif ($x >= 8 && $x < 9 && $i < 1) {
                    $mode_day->user_id = $user->user_id;
                    $mode_day->total_day = 8;
                    $mode_day->date = Carbon::now()->todateString();
                } elseif ($x >= 9 && $x < 10 && $i < 1) {
                    $mode_day->user_id = $user->user_id;
                    $mode_day->total_day = 9;
                    $mode_day->date = Carbon::now()->todateString();
                } elseif ($x >= 10 && $x < 11 && $i < 1) {
                    $mode_day->user_id = $user->user_id;
                    $mode_day->total_day = 10;
                    $mode_day->date = Carbon::now()->todateString();
                } elseif ($x >= 11 && $x < 12 && $i < 1) {
                    $mode_day->user_id = $user->user_id;
                    $mode_day->total_day = 11;
                    $mode_day->date = Carbon::now()->todateString();
                } elseif ($x >= 12) {
                    $mode_day->user_id = $user->user_id;
                    $mode_day->total_day = 12;
                    $mode_day->date = Carbon::now()->todateString();
                } else {
                    $mode_day->user_id = $user->user_id;
                    $mode_day->total_day = 0;
                    $mode_day->date = Carbon::now()->todateString();
                }
            } else {
                $dateDiff = date_diff(date_create($startyear), date_create($today));
                $x = $dateDiff->m;
                if ($x >= 1 && $x < 2) {
                    $mode_day->user_id = $user->user_id;
                    $mode_day->date = Carbon::now()->todateString();
                    $mode_day->total_day = 2;
                } elseif ($x >= 2 && $x < 3) {
                    $mode_day->user_id = $user->user_id;
                    $mode_day->date = Carbon::now()->todateString();
                    $mode_day->total_day = 3;
                } elseif ($x >= 3 && $x < 4) {
                    $mode_day->user_id = $user->user_id;
                    $mode_day->date = Carbon::now()->todateString();
                    $mode_day->total_day = 4;
                } elseif ($x >= 4 && $x < 5) {
                    $mode_day->user_id = $user->user_id;
                    $mode_day->total_day = 5;
                    $mode_day->date = Carbon::now()->todateString();
                } elseif ($x >= 5 && $x < 6) {
                    $mode_day->user_id = $user->user_id;
                    $mode_day->total_day = 6;
                    $mode_day->date = Carbon::now()->todateString();
                } elseif ($x >= 6 && $x < 7) {
                    $mode_day->user_id = $user->user_id;
                    $mode_day->total_day = 7;
                    $mode_day->date = Carbon::now()->todateString();
                } elseif ($x >= 7 && $x < 8) {
                    $mode_day->user_id = $user->user_id;
                    $mode_day->total_day = 8;
                    $mode_day->date = Carbon::now()->todateString();
                } elseif ($x >= 8 && $x < 9) {
                    $mode_day->user_id = $user->user_id;
                    $mode_day->total_day = 9;
                    $mode_day->date = Carbon::now()->todateString();
                } elseif ($x >= 9 && $x < 10) {
                    $mode_day->user_id = $user->user_id;
                    $mode_day->total_day = 10;
                    $mode_day->date = Carbon::now()->todateString();
                } elseif ($x >= 10 && $x < 11) {
                    $mode_day->user_id = $user->user_id;
                    $mode_day->total_day = 11;
                    $mode_day->date = Carbon::now()->todateString();
                } elseif ($x >= 11 && $x < 12) {
                    $mode_day->user_id = $user->user_id;
                    $mode_day->total_day = 12;
                    $mode_day->date = Carbon::now()->todateString();
                } else {
                    $mode_day->user_id = $user->user_id;
                    $mode_day->total_day = 1;
                    $mode_day->date = Carbon::now()->todateString();
                }
            }
            $mode_day->save();
        }
    }
}
