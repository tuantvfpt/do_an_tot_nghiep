<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Calendar_leave;
use App\Models\company_mode;
use App\Models\LichChamCong;
use App\Models\TongThuNhap;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;

class CalendarLeaveController extends Controller
{
    //
    public function getAll(Request $request)
    {
        $lich_nghi = Calendar_leave::select('calendar_for_leave.*', 'user_info.full_name')
            ->Join('users', 'calendar_for_leave.user_id', '=', 'users.id')
            ->join('user_info', 'users.id', '=', 'user_info.user_id')
            ->where('calendar_for_leave.deleted_at', null)
            ->where('calendar_for_leave.status', 1);
        if (Gate::allows('view')) {
            if (!empty($request->keyword)) {
                $lich_nghi =  $lich_nghi->Where(function ($query) use ($request) {
                    $query->where('user_info.full_name', 'like', "%" . $request->keyword . "%");
                });
            }
            if (!empty($request->date)) {
                $lich_nghi =  $lich_nghi->where('date', $request->date);
            }
        } else {
            $lich_nghi->where('calendar_for_leave.user_id', Auth::user()->id);
        }
        $lich_nghi = $lich_nghi->paginate(($request->limit != null) ? $request->limit : 5);
        return  response()->json([
            'status' => true,
            'message' => 'Lấy danh sách nghỉ thành công',
            'data' => $lich_nghi->items(),
            'meta' => [
                'total'      => $lich_nghi->total(),
                'perPage'    => $lich_nghi->perPage(),
                'currentPage' => $lich_nghi->currentPage()
            ]
        ])->setStatusCode(200);
    }
    public function get_lich_nghi()
    {
        if (Gate::allows('view')) {
            $lich_xin_nghi = Calendar_leave::select('calendar_for_leave.*', 'user_info.full_name')
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
    public function comfig($id, Request $request)
    {
        if (Gate::allows('confirmLeave')) {
            // $check = Calendar_leave::where('id', $id)->first();
            if (isset($request->yes)) {
                $lich_xin_nghi = Calendar_leave::find($id);
                $lich_xin_nghi->status = 1;
                $lich_xin_nghi->save();
                $user = User::where('id', $lich_xin_nghi->user_id)->first();
                $to_name = $user->user_account;
                $to_email = $user->email;
                $data = array('name' => 'Hello' . $to_name, 'body' => 'Công ty đã nhận được đơn xin nghỉ của bạn và 
                công ty chấp nhận đơn xin nghỉ của bạn.');
                Mail::send('emails.mail', $data, function ($message) use ($to_name, $to_email) {
                    $message->to($to_email, $to_name)->subject('V.v nghỉ phép');
                    $message->from('tuantong.datus@gmail.com');
                });
                $mess = "Đồng ý cho nghỉ";
            } else {
                $lich_xin_nghi = Calendar_leave::find($id);
                if ($lich_xin_nghi) {
                    if ($lich_xin_nghi->mode_leave == 1) {
                        $mode_user = company_mode::where('user_id', $lich_xin_nghi->user_id)->first();
                        $mode_user = company_mode::find($mode_user->id);
                        $mode_user->total_day_off = $mode_user->total_day_off - $lich_xin_nghi->number_mode_leave;
                        $mode_user->save();
                    }
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
                }
                $mess = "Không cho phép nghỉ";
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
    public function create(Request $request)
    {
        $today = Carbon::now()->toDateString();
        $user_id = Auth::user()->id;
        $check = Calendar_leave::where('date', $today)->where('user_id', $user_id)->first();
        $dateDiff = date_diff(date_create($request->time_start), date_create($request->time_end));
        $x = $dateDiff->d;
        $user_off = new Calendar_leave();
        if ($check) {
            $user_off = Calendar_leave::find($check->id);
        }
        $user_off->user_id = $user_id;
        $user_off->time_start = $request->time_start;
        $user_off->time_end = $request->time_end;
        $user_off->note = $request->note;
        $user_off->date = Carbon::now()->toDateString();
        $user_off->status = 0;
        $user_off->mode_leave = $request->mode_leave;
        $user_off->number_day_leave = $x;
        if ($request->mode_leave) {
            $user_off->number_mode_leave = $request->number_day;
        } else {
            $user_off->number_mode_leave = 0;
        }
        // Update bảng chế độ nghỉ của công ty
        if ($request->mode_leave == 1) {
            $mode = company_mode::where('user_id', $user_id)->first();
            if ($request->number_day) {
                if ($mode->total_day - $request->number_day >= 0 && ($mode->total_day_off + $request->number_day <= $mode->total_day)) {
                    $mode_user = company_mode::find($mode->id);
                    $mode_user->total_day_off += $request->number_day;
                    $mode_user->date = Carbon::now();
                    $mode_user->save();
                    $user_off->save();
                    $response = response()->json([
                        'status' => true,
                        'message' => "Bạn đã đăng kí lịch nghỉ thành công",
                        'data' => $user_off
                    ])->setStatusCode(200);
                } else {
                    $response = response()->json([
                        'status' => false,
                        'message' => "Bạn đã thực hiên lỗi vui lòng thử lại"
                    ])->setStatusCode(404);
                }
            }
        } else {
            $response = response()->json([
                'status' => true,
                'message' => "Bạn đã đăng kí lịch nghỉ thành công",
                'data' => $user_off
            ])->setStatusCode(200);
        }
        return $response;
    }
    //lấy tất cả dữ liệu đi lầm với nghỉ trong 1 tháng theo lich
    // public function nghiphep()
    // {
    //     $startyear = Carbon::now()->startOfYear()->toDateString();
    //     $endyear = Carbon::now()->endOfYear()->toDateString();
    //     $today = date('Y-m-d');
    //     $user = DB::table('users')
    //         ->select('*')
    //         ->rightjoin('user_info', 'users.id', '=', 'user_info.user_id')
    //         ->where('users.deleted_at', null)
    //         ->get();
    //     foreach ($user as $user) {
    //         // tính thời gian làm việc của nhân viên được bao nhiêu tháng
    //         $dateDiff = date_diff(date_create($user->date_of_join), date_create($today));
    //         $x = $dateDiff->m;
    //         $i = $dateDiff->y;
    //         $mode_day = new company_mode();
    //         //kiểm tra điều kiện
    //         $check = company_mode::where('user_id', $user->user_id)->whereBetween('date', [$startyear, $endyear])->first();
    //         if ($check) {
    //             $mode_day = company_mode::find($check->id);
    //         }
    //         if ($i < 1) {
    //             if ($x >= 1 && $x < 2 && $i < 1) {
    //                 $mode_day->user_id = $user->user_id;
    //                 $mode_day->date = Carbon::now()->todateString();
    //                 $mode_day->total_day = 1;
    //             } elseif ($x >= 2 && $x < 3 && $i < 1) {
    //                 $mode_day->user_id = $user->user_id;
    //                 $mode_day->date = Carbon::now()->todateString();
    //                 $mode_day->total_day = 2;
    //             } elseif ($x >= 3 && $x < 4 && $i < 1) {
    //                 $mode_day->user_id = $user->user_id;
    //                 $mode_day->date = Carbon::now()->todateString();
    //                 $mode_day->total_day = 3;
    //             } elseif ($x >= 4 && $x < 5 && $i < 1) {
    //                 $mode_day->user_id = $user->user_id;
    //                 $mode_day->total_day = 4;
    //                 $mode_day->date = Carbon::now()->todateString();
    //             } elseif ($x >= 5 && $x < 6 && $i < 1) {
    //                 $mode_day->user_id = $user->user_id;
    //                 $mode_day->total_day = 5;
    //                 $mode_day->date = Carbon::now()->todateString();
    //             } elseif ($x >= 6 && $x < 7 && $i < 1) {
    //                 $mode_day->user_id = $user->user_id;
    //                 $mode_day->total_day = 6;
    //                 $mode_day->date = Carbon::now()->todateString();
    //             } elseif ($x >= 7 && $x < 8 && $i < 1) {
    //                 $mode_day->user_id = $user->user_id;
    //                 $mode_day->total_day = 7;
    //                 $mode_day->date = Carbon::now()->todateString();
    //             } elseif ($x >= 8 && $x < 9 && $i < 1) {
    //                 $mode_day->user_id = $user->user_id;
    //                 $mode_day->total_day = 8;
    //                 $mode_day->date = Carbon::now()->todateString();
    //             } elseif ($x >= 9 && $x < 10 && $i < 1) {
    //                 $mode_day->user_id = $user->user_id;
    //                 $mode_day->total_day = 9;
    //                 $mode_day->date = Carbon::now()->todateString();
    //             } elseif ($x >= 10 && $x < 11 && $i < 1) {
    //                 $mode_day->user_id = $user->user_id;
    //                 $mode_day->total_day = 10;
    //                 $mode_day->date = Carbon::now()->todateString();
    //             } elseif ($x >= 11 && $x < 12 && $i < 1) {
    //                 $mode_day->user_id = $user->user_id;
    //                 $mode_day->total_day = 11;
    //                 $mode_day->date = Carbon::now()->todateString();
    //             } elseif ($x >= 12) {
    //                 $mode_day->user_id = $user->user_id;
    //                 $mode_day->total_day = 12;
    //                 $mode_day->date = Carbon::now()->todateString();
    //             } else {
    //                 $mode_day->user_id = $user->user_id;
    //                 $mode_day->total_day = 0;
    //                 $mode_day->date = Carbon::now()->todateString();
    //             }
    //         } else {
    //             $dateDiff = date_diff(date_create($startyear), date_create($today));
    //             $x = $dateDiff->m;
    //             if ($x >= 1 && $x < 2) {
    //                 $mode_day->user_id = $user->user_id;
    //                 $mode_day->date = Carbon::now()->todateString();
    //                 $mode_day->total_day = 2;
    //             } elseif ($x >= 2 && $x < 3) {
    //                 $mode_day->user_id = $user->user_id;
    //                 $mode_day->date = Carbon::now()->todateString();
    //                 $mode_day->total_day = 3;
    //             } elseif ($x >= 3 && $x < 4) {
    //                 $mode_day->user_id = $user->user_id;
    //                 $mode_day->date = Carbon::now()->todateString();
    //                 $mode_day->total_day = 4;
    //             } elseif ($x >= 4 && $x < 5) {
    //                 $mode_day->user_id = $user->user_id;
    //                 $mode_day->total_day = 5;
    //                 $mode_day->date = Carbon::now()->todateString();
    //             } elseif ($x >= 5 && $x < 6) {
    //                 $mode_day->user_id = $user->user_id;
    //                 $mode_day->total_day = 6;
    //                 $mode_day->date = Carbon::now()->todateString();
    //             } elseif ($x >= 6 && $x < 7) {
    //                 $mode_day->user_id = $user->user_id;
    //                 $mode_day->total_day = 7;
    //                 $mode_day->date = Carbon::now()->todateString();
    //             } elseif ($x >= 7 && $x < 8) {
    //                 $mode_day->user_id = $user->user_id;
    //                 $mode_day->total_day = 8;
    //                 $mode_day->date = Carbon::now()->todateString();
    //             } elseif ($x >= 8 && $x < 9) {
    //                 $mode_day->user_id = $user->user_id;
    //                 $mode_day->total_day = 9;
    //                 $mode_day->date = Carbon::now()->todateString();
    //             } elseif ($x >= 9 && $x < 10) {
    //                 $mode_day->user_id = $user->user_id;
    //                 $mode_day->total_day = 10;
    //                 $mode_day->date = Carbon::now()->todateString();
    //             } elseif ($x >= 10 && $x < 11) {
    //                 $mode_day->user_id = $user->user_id;
    //                 $mode_day->total_day = 11;
    //                 $mode_day->date = Carbon::now()->todateString();
    //             } elseif ($x >= 11 && $x < 12) {
    //                 $mode_day->user_id = $user->user_id;
    //                 $mode_day->total_day = 12;
    //                 $mode_day->date = Carbon::now()->todateString();
    //             } else {
    //                 $mode_day->user_id = $user->user_id;
    //                 $mode_day->total_day = 1;
    //                 $mode_day->date = Carbon::now()->todateString();
    //             }
    //         }
    //         $mode_day->save();
    //     }
    // }
    //update tiền lương
    // public function tienluong()
    // {
    //     $user = User::select('users.*', 'user_info.basic_salary')
    //         ->join('user_info', 'users.id', '=', 'user_info.user_id')->get();
    //     foreach ($user as $user) {
    //         // $checkluong = userInfo::where('user_id', $user->id)->first();
    //         $startmonth = Carbon::now()->startOfMonth()->toDateString();
    //         $endmonth = Carbon::now()->endOfMonth()->toDateString();
    //         $tongtime = LichChamCong::where('user_id', $user->id)
    //             ->where('date_of_work', '>=', $startmonth)
    //             ->where('date_of_work', '<=', $endmonth)
    //             ->get();
    //         $muoihaigio = "12:00:00";
    //         $muoibagio = "13:00:00";
    //         $muoibaygio = "17:00:00";
    //         $tongtimecheckin = 0;
    //         foreach ($tongtime as $item) {
    //             if ($item->check_ot == 0) {
    //                 if (strtotime($muoihaigio) - strtotime($item->time_of_check_in) < 0) {
    //                     $x = 0;
    //                 } elseif (strtotime($item->time_of_check_out) - strtotime($muoihaigio) < 0) {
    //                     $x = strtotime($item->time_of_check_out) - strtotime($item->time_of_check_in);
    //                 } else {
    //                     $x = strtotime($muoihaigio) - strtotime($item->time_of_check_in);
    //                 }
    //                 if (strtotime($item->time_of_check_out) - strtotime($muoibagio) < 0) {
    //                     $b = 0;
    //                 } elseif (strtotime($item->time_of_check_in) - strtotime($muoibagio) > 0) {
    //                     $b = strtotime($item->time_of_check_out) - strtotime($item->time_of_check_in);
    //                 } else {
    //                     if (strtotime($item->time_of_check_out) - strtotime($muoibaygio) < 0) {
    //                         $b = strtotime($item->time_of_check_out) - strtotime($muoibagio);
    //                     } else {
    //                         $b = strtotime($muoibaygio) - strtotime($muoibagio);
    //                     }
    //                 }
    //             } else {
    //                 if (strtotime($muoihaigio) - strtotime($item->time_of_check_in) < 0) {
    //                     $x = 0;
    //                 } elseif (strtotime($item->time_of_check_out) - strtotime($muoihaigio) < 0) {
    //                     $x = strtotime($item->time_of_check_out) - strtotime($item->time_of_check_in);
    //                 } else {
    //                     $x = strtotime($muoihaigio) - strtotime($item->time_of_check_in);
    //                 }
    //                 if (strtotime($item->time_of_check_out) - strtotime($muoibagio) < 0) {
    //                     $b = 0;
    //                 } elseif (strtotime($item->time_of_check_in) - strtotime($muoibagio) > 0) {
    //                     $b = strtotime($item->time_of_check_out) - strtotime($item->time_of_check_in);
    //                 } elseif (strtotime($item->time_of_check_out) - strtotime($muoibagio) > 0 && (strtotime($item->time_of_check_out) <= (strtotime($muoibaygio)))) {
    //                     $b = strtotime($item->time_of_check_out) - strtotime($muoibagio);
    //                 } elseif (strtotime($item->time_of_check_out) - strtotime($muoibaygio) > 0) {
    //                     $c = (strtotime($item->time_of_check_out) - strtotime($muoibaygio)) * 2;
    //                     $b = strtotime($muoibaygio) - strtotime($muoibagio) + $c;
    //                 }
    //             }
    //             $tongtimecheckin += ($x + $b);
    //         }
    //         $tongtimelam = $tongtimecheckin / 3600;
    //         $luongcoban = $user->basic_salary;
    //         $timecodinh = 8;
    //         if (($tongtimelam - $timecodinh * 22) > 0) {
    //             $tongluong = ($luongcoban + ($tongtimelam - ($timecodinh * 22) * ($luongcoban / (22 * 8))));
    //         } elseif (($tongtimelam - $timecodinh * 22) == 0) {
    //             $tongluong = $luongcoban;
    //         } else {
    //             $tongluong = ($luongcoban - (($timecodinh * 22) - $tongtimelam) * ($luongcoban / (22 * 8)));
    //         }
    //         $formatluong = $tongluong;
    //         if (isset($formatluong)) {
    //             $checktongluong = TongThuNhap::where('user_id', $user->id)
    //                 ->where('date', '>=', $startmonth)
    //                 ->where('date', '<=', $endmonth)
    //                 ->first();
    //             if ($checktongluong) {
    //                 $luong = TongThuNhap::find($checktongluong->id);
    //                 $luong->total_gross_salary = $formatluong;
    //                 $luong->date = Carbon::now()->toDateString();
    //                 $luong->save();
    //             } else {
    //                 $luong = new TongThuNhap();
    //                 $luong->user_id = $user->id;
    //                 $luong->total_gross_salary = $formatluong;
    //                 $luong->total_net_salary = 0;
    //                 $luong->status = "0";
    //                 $luong->date = Carbon::now();
    //                 $luong->save();
    //             }
    //         }
    //     }
    // }

   
}
