<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LichChamCong;
use App\Models\lichTangCa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class LichTangCaController extends Controller
{
    public function addTangCa(Request $request)
    {
        if (Gate::allows('leader')) {
            $today = Carbon::now()->toDateString();
            foreach ($request->all() as $key) {
                $update = new lichTangCa();
                $update->name_leader = Auth::user()->user_account;
                $update->user_id = $key;
                $update->date = $today;
                $update->time_tang_ca = $request->time_tang_ca;
                $update->note = $request->note;
                $update->status = 0;
                $response =  response()->json([
                    'status' => true,
                    'message' => 'Cập nhật OT thành công',
                    'data' => $update,
                ], 200);
            }
        } else {
            $response = response()->json([
                'status' => false,
                'message' => "Không có quyền truy cập",
            ], 404);
        }
        return $response;
    }
    public function danh_sach_tang_ca_by_user()
    {
        $list = lichTangCa::where('user_id', Auth::user()->id)->orderby('id', 'DESC')->get();
        return response()->json([
            'status' => true,
            'message' => 'Danh sach tăng ca',
            'data' => $list,
        ], 200);
    }
    public function xac_nhan_tang_ca($id, Request $request)
    {
        $tangca = lichTangCa::find($id);
        $check = LichChamCong::where('user_id', Auth::user()->id)
            ->whereDate('date_of_work', $tangca->date)
            ->first();
        if ($tangca && $check) {
            if ($request->comfirm == 'yes') {
                $tangca->status = 1;
                $tangca->lich_cham_cong_id = $check->id;
            } else {
                $tangca->status = 2;
            }
            $tangca->save();
            $response =  response()->json([
                'status' => false,
                'message' => 'Cập nhật trạng thái thành công',
                'data' => $tangca
            ], 200);
        } else {
            $response =  response()->json([
                'status' => false,
                'message' => 'Không tồn tại',
            ], 404);
        }
        return $response;
    }
}
