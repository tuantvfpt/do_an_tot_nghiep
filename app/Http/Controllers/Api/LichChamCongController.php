<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LichChamCong;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LichChamCongController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function addSave(Request $request)
    {
        $today = Carbon::now()->format('Y-m-d');
        $checkQR = User::select(
            DB::raw('user_info.ma_QR as ma_QR,users.id as userId')
        )
            ->join('user_info', 'user_info.user_id', '=', 'users.id')
            // ->join('lich_cham_cong', 'lich_cham_cong.user_id', '=', 'users.id')
            ->Where('ma_QR', $request->ma_QR)
            // ->where('date', '>=', $today)
            // ->where('date', '<=', $today)
            ->first();
        if ($checkQR) {
            $check_out = LichChamCong::where('user_id', $checkQR->userId)
                ->where('date', '>=', $today)
                ->where('date', '<=', $today)
                ->first();
            if ($check_out) {
                $check_out->check_out = Carbon::now()->format('H:i:m');
                $check_out->status = '1';
                $check_out->save();
                return  $check_out ?
                    response()->json([
                        'status' => true,
                        'message' => 'checkout thành công',
                        'data' => $check_out
                    ], 200) :
                    response()->json([
                        'status' => false,
                        'message' => 'checkout thất bại',
                    ], 404);
            } else {
                $lich_cham_cong = new LichChamCong();
                $lich_cham_cong->user_id = $checkQR->userId;
                $lich_cham_cong->check_in = Carbon::now()->format('H:i:m');
                $lich_cham_cong->check_out = Carbon::now()->format('H:i:m');
                $lich_cham_cong->date = Carbon::now()->format('Y-m-d');
                $lich_cham_cong->status = '0';
                $lich_cham_cong->save();
                return  $lich_cham_cong ?
                    response()->json([
                        'status' => true,
                        'message' => 'Điểm danh thành công',
                        'data' => $lich_cham_cong
                    ], 200) :
                    response()->json([
                        'status' => false,
                        'message' => 'Điểm danh thất bại',
                    ], 404);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Mã QR không tồn tại',
            ]);
        }
    }

}
