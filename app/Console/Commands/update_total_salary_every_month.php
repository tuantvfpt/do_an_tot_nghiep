<?php

namespace App\Console\Commands;

use App\Models\LichChamCong;
use App\Models\lichTangCa;
use App\Models\TongThuNhap;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class update_total_salary_every_month extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:update_total_salary_every_month';

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
            $tangca = lichTangCa::select('lich_tang_ca.*', 'time_keep_calendar.time_of_check_out')
                ->join('time_keep_calendar', 'time_keep_calendar.id', '=', 'lich_tang_ca.lich_cham_cong_id')
                ->where('lich_tang_ca.user_id', $user->id)
                ->where('date', '>=', $startmonth)
                ->where('date', '<=', $endmonth)
                ->get();
            $tamgio = "8:00:00";
            $muoihaigio = "12:00:00";
            $muoibagio = "13:00:00";
            $muoibaygio = "17:00:00";
            $tongtimecheckin = 0;
            $totaltimetangca = 0;
            foreach ($tongtime as $item) {
                if ($item->check_ot == 0) {
                    //check_in
                    if (strtotime($muoihaigio) - strtotime($item->time_of_check_in) < 0) {
                        $x = 0;
                    } elseif (strtotime($item->time_of_check_out) - strtotime($muoihaigio) < 0) {
                        if (strtotime($item->time_of_check_in) - strtotime($tamgio) < 0) {
                            $x = strtotime($item->time_of_check_out) - strtotime($tamgio);
                        } else {
                            $x = strtotime($item->time_of_check_out) - strtotime($item->time_of_check_in);
                        }
                    } else {
                        $x = strtotime($muoihaigio) - strtotime($item->time_of_check_in);
                        if (strtotime($item->time_of_check_in) - strtotime($tamgio) < 0) {
                            $x = strtotime($muoihaigio) - strtotime($tamgio);
                        } else {
                            $x = strtotime($muoihaigio) - strtotime($item->time_of_check_in);
                        }
                    }
                    // check_out
                    if (strtotime($item->time_of_check_out) - strtotime($muoibagio) < 0) {
                        $b = 0;
                    } elseif (strtotime($item->time_of_check_in) - strtotime($muoibagio) > 0) {
                        if (strtotime($item->time_of_check_out) - strtotime($muoibaygio) < 0) {
                            $b = strtotime($item->time_of_check_out) - strtotime($item->time_of_check_in);
                        } else {
                            $b = strtotime($muoibaygio) - strtotime($item->time_of_check_in);
                        }
                    } else {
                        if (strtotime($item->time_of_check_out) - strtotime($muoibaygio) < 0) {
                            $b = strtotime($item->time_of_check_out) - strtotime($muoibagio);
                        } else {
                            $b = strtotime($muoibaygio) - strtotime($muoibagio);
                        }
                    }
                } else {
                    //check_in
                    if (strtotime($muoihaigio) - strtotime($item->time_of_check_in) < 0) {
                        $x = 0;
                    } elseif (strtotime($item->time_of_check_out) - strtotime($muoihaigio) < 0) {
                        if (strtotime($item->time_of_check_in) - strtotime($tamgio) < 0) {
                            $x = strtotime($item->time_of_check_out) - strtotime($tamgio);
                        } else {
                            $x = strtotime($item->time_of_check_out) - strtotime($item->time_of_check_in);
                        }
                    } else {
                        $x = strtotime($muoihaigio) - strtotime($item->time_of_check_in);
                        if (strtotime($item->time_of_check_in) - strtotime($tamgio) < 0) {
                            $x = strtotime($muoihaigio) - strtotime($tamgio);
                        } else {
                            $x = strtotime($muoihaigio) - strtotime($item->time_of_check_in);
                        }
                    }
                    // check_out
                    if (strtotime($item->time_of_check_out) - strtotime($muoibagio) < 0) {
                        $b = 0;
                    } elseif (strtotime($item->time_of_check_in) - strtotime($muoibagio) > 0) {
                        $b = strtotime($item->time_of_check_out) - strtotime($item->time_of_check_in);
                    } elseif (strtotime($item->time_of_check_out) - strtotime($muoibagio) > 0) {
                        $b = strtotime($item->time_of_check_out) - strtotime($muoibagio);
                    } elseif (strtotime($item->time_of_check_out) - strtotime($muoibaygio) > 0) {
                        $c = (strtotime($item->time_of_check_out) - strtotime($muoibaygio)) * 2;
                        $b = strtotime($muoibaygio) - strtotime($muoibagio) + $c;
                    }
                }

                $tongtimecheckin += ($x + $b);
            }
            foreach ($tangca as $tangca) {
                if (strtotime($tangca->time_tang_ca) - strtotime($tangca->time_of_check_out) > 0) {
                    $tangca = ((strtotime($tangca->time_of_check_out) - strtotime($muoibaygio)) * 2);
                } else {
                    $tangca = ((strtotime($tangca->time_tang_ca) - strtotime($muoibaygio)) * 2);
                }
                $totaltimetangca += $tangca;
            }
            $tongtimelam = $tongtimecheckin / 3600 + $totaltimetangca / 3600;
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
}
