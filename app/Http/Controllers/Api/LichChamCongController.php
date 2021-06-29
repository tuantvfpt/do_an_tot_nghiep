<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LichChamCong;
use App\Models\TongThuNhap;
use App\Models\User;
use App\Models\userInfo;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LichChamCongController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAll(Request $request)
    {
        $lich_cham_cong = LichChamCong::select('time_keep_calendar.*', 'user_info.full_name')
            ->Join('users', 'time_keep_calendar.user_id', '=', 'users.id')
            ->join('user_info', 'users.id', '=', 'user_info.user_id')
            ->where('time_keep_calendar.deleted_at', null);
        if (!empty($request->keyword)) {
            $lich_cham_cong =  $lich_cham_cong->Where(function ($query) use ($request) {
                $query->where('user_info.full_name', 'like', "%" . $request->keyword . "%");
            });
        }
        if (!empty($request->date)) {
            $lich_cham_cong =  $lich_cham_cong->where('date', $request->date);
        }
        $lich_cham_cong = $lich_cham_cong->paginate(($request->limit != null) ? $request->limit : 5);
        return response()->json([
            'status' => true,
            'message' => 'Lấy danh sách chấm công thành công',
            'data' => $lich_cham_cong->items(),
            'meta' => [
                'total'      => $lich_cham_cong->total(),
                'perPage'    => $lich_cham_cong->perPage(),
                'currentPage' => $lich_cham_cong->currentPage()
            ]
        ])->setStatusCode(200);
    }
    public function getdetail($id)
    {
        $lich_cham_cong = LichChamCong::find($id);
        return $lich_cham_cong ? response()->json([
            'status' => true,
            'message' => 'Lấy chi tiết chấm công thành công',
            'data' => $lich_cham_cong
        ])->setStatusCode(200) : response()->json([
            'status' => false,
            'message' => 'Lấy chi tiết chấm công thấy bại',
        ])->setStatusCode(404);
    }
    public function diemdanh(Request $request)
    {
        try {
            DB::beginTransaction();
            $today = Carbon::now()->format('Y-m-d');
            $checkQR = User::select(
                DB::raw('user_info.Code_QR as ma_QR,users.id as userId')
            )
                ->join('user_info', 'user_info.user_id', '=', 'users.id')
                ->Where('Code_QR', $request->code_QR)
                ->first();
            if ($checkQR) {
                $check_out = LichChamCong::where('user_id', $checkQR->userId)
                    ->where('date_of_work', '>=', $today)
                    ->where('date_of_work', '<=', $today)
                    ->first();
                if ($check_out) {
                    $lich_cham_cong = LichChamCong::find($check_out->id);
                    $lich_cham_cong->time_of_check_out = Carbon::now()->format('H:i:m');
                    $lich_cham_cong->status = '1';
                    $lich_cham_cong->save();
                } else {
                    $lich_cham_cong = new LichChamCong();
                    $lich_cham_cong->user_id = $checkQR->userId;
                    $lich_cham_cong->time_of_check_in = Carbon::now()->format('H:i:m');
                    $lich_cham_cong->time_of_check_out = Carbon::now()->format('H:i:m');
                    $lich_cham_cong->date_of_work = Carbon::now()->format('Y-m-d');
                    $lich_cham_cong->status = '0';
                    $lich_cham_cong->save();
                }
                if (isset($lich_cham_cong->time_of_check_out)) {
                    $checkluong = userInfo::where('user_id', $lich_cham_cong->user_id)->first();
                    $startmonth = Carbon::now()->startOfMonth()->toDateString();
                    $endmonth = Carbon::now()->endOfMonth()->toDateString();
                    $tongtime = LichChamCong::where('user_id', $lich_cham_cong->user_id)
                        ->where('date_of_work', '>=', $startmonth)
                        ->where('date_of_work', '<=', $endmonth)
                        ->get();
                    $muoihaigio = "12:00:00";
                    $muoibagio = "13:00:00";
                    $tongtimecheckin = 0;
                    foreach ($tongtime as $item) {
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
                            $b = strtotime($item->time_of_check_out) - strtotime($muoibagio);
                        }
                        $tongtimecheckin += ($x + $b);
                    }
                    $tongtimelam = $tongtimecheckin / 3600;
                    $luongcoban = $checkluong->basic_salary;
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
                        $checktongluong = TongThuNhap::where('user_id', $lich_cham_cong->user_id)
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
                            $luong->user_id = $lich_cham_cong->user_id;
                            $luong->total_gross_salary = $formatluong;
                            $luong->total_net_salary = 0;
                            $luong->status = "0";
                            $luong->date = Carbon::now();
                            $luong->save();
                        }
                    }
                }
            } else {
                return  response()->json([
                    'message' => 'Không tồn tại mã QR',
                    'status' => false
                ], 404);
            }
            DB::commit();
            $mes = "Check mã QR thành công";
            $status = true;
        } catch (Exception $e) {
            DB::rollBack();
            $mes = $e->getMessage();
            $status = false;
        }

        return  response()->json([
            'message' => $mes,
            'status' => $status
        ], 200);
    }
    public function show_lich(Request $request)
    {
        $id = $request->id;
        $arrDay = [];
        if ($request->date_time) {
            $motnh = date('m', strtotime($request->date_time));
            $year = date('Y', strtotime($request->date_time));
        } else {
            $motnh = date('6');
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
            ->where(function ($query) use ($id) {
                if ($id) {
                    $query->where('user_id', $id);
                } else {
                    $query->where('user_id', 3);
                }
            })
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
            // || $data_user_di_muon || $data_user_di_som || $data_user || $data_salary || $dataBetween_salary || $data_user_in_position
            ?
            response()->json([
                'status' => true,
                'message' => 'Lấy thông tin dashboard thành công',
                'data' => $arr_lich_lam,
                // $data_user_di_muon, $data_user_di_som, $data_user, $data_salary, $dataBetween_salary, $data_user_in_position
            ], 200) :
            response()->json([
                'status' => false,
                'message' => $error,
            ], 404);
    }
}
