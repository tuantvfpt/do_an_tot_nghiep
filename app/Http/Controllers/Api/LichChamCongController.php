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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class LichChamCongController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected $validate = [
        'time_of_check_in' => 'required',
        'time_of_check_out' => 'required',
        'date_of_work' => 'required',
        'user_id' => 'required',
    ];
    public function getAll(Request $request)
    {
        $lich_cham_cong = LichChamCong::select('time_keep_calendar.*', 'user_info.full_name')
            ->Join('users', 'time_keep_calendar.user_id', '=', 'users.id')
            ->join('user_info', 'users.id', '=', 'user_info.user_id')
            ->where('time_keep_calendar.deleted_at', null);
        if (Gate::allows('view')) {
            if (!empty($request->keyword)) {
                $lich_cham_cong =  $lich_cham_cong->Where(function ($query) use ($request) {
                    $query->where('user_info.full_name', 'like', "%" . $request->keyword . "%");
                });
            }
            if (!empty($request->date)) {
                $lich_cham_cong =  $lich_cham_cong->whereMonth('date_of_work', date('m', strtotime($request->date)));
            }
        }
        $lich_cham_cong = $lich_cham_cong->paginate(($request->limit != null) ? $request->limit : 10);
        return  response()->json([
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
        $check = LichChamCong::where('user_id', Auth::user()->id)
            ->where('id', $id)->first();
        if (Gate::allows('view/id') || $check) {
            $lich_cham_cong = LichChamCong::find($id);
            $response = $lich_cham_cong ? response()->json([
                'status' => true,
                'message' => 'Lấy chi tiết lịch châm công thành công',
                'data' => $lich_cham_cong
            ])->setStatusCode(200) : response()->json([
                'status' => false,
                'message' => 'Lấy chi tiết lịch châm công thấy bại',
            ])->setStatusCode(404);
        } elseif ($check == null) {
            $response = response()->json([
                'status' => false,
                'message' => "Bạn không có quyền truy cập",
            ])->setStatusCode(404);
        }
        return $response;
    }
    public function diemdanh(Request $request)
    {
        try {
            DB::beginTransaction();
            if (Gate::allows('diemdanh')) {
                $today = Carbon::now()->format('Y-m-d');
                $checkQR = User::select(
                    DB::raw('user_info.Code_QR as ma_QR,users.id as userId')
                )
                    ->join('user_info', 'user_info.user_id', '=', 'users.id')
                    ->Where('Code_QR', $request->code_QR)
                    ->where('user_info.deleted_at', null)
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
                    $lich_cham_cong->load('get_user_name');
                } else {
                    return  response()->json([
                        'message' => 'Không tồn tại mã QR',
                        'status' => false,
                    ], 404);
                }
                DB::commit();
                $mes = "Check mã QR thành công";
                $status = true;
            } else {
                return  response()->json([
                    'message' => 'Không có quyền điểm danh',
                    'status' => false,
                ], 404);
            }
        } catch (Exception $e) {
            DB::rollBack();
            $mes = $e->getMessage();
            $status = false;
        }
        return  response()->json([
            'message' => $mes,
            'status' => $status,
            'data' => $lich_cham_cong
        ], 200);
    }
    public function create(Request $request)
    {
        if (Gate::allows('create')) {
            $validator = Validator::make(
                $request->all(),
                $this->validate
            );
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()
                ], 400);
            }
            $lich_cham_cong = new LichChamCong();
            $lich_cham_cong->time_of_check_in = $request->time_of_check_in;
            $lich_cham_cong->time_of_check_out = $request->time_of_check_out;
            $lich_cham_cong->date_of_work = $request->date_of_work;
            $lich_cham_cong->user_id = $request->user_id;
            $lich_cham_cong->status = 1;
            $lich_cham_cong->note = "Hr đã thêm ngày công cho bạn";
            $lich_cham_cong->save();
            $response = $lich_cham_cong ? response()->json([
                'status' => true,
                'message' => 'Thêm lịch châm công thành công',
                'data' => $lich_cham_cong
            ])->setStatusCode(200) : response()->json([
                'status' => false,
                'message' => 'Thêm lịch châm công thấy bại',
            ])->setStatusCode(404);
        } else {
            $response = response()->json([
                'status' => false,
                'message' => "Bạn không có quyền truy cập",
            ])->setStatusCode(404);
        }
        return $response;
    }
    public function update($id, Request $request)
    {
        if (Gate::allows('create')) {
            $validator = Validator::make(
                $request->all(),
                $this->validate
            );
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()
                ], 400);
            }
            $lich_cham_cong = LichChamCong::find($id);
            $lich_cham_cong->time_of_check_in = $request->time_of_check_in;
            $lich_cham_cong->time_of_check_out = $request->time_of_check_out;
            $lich_cham_cong->date_of_work = $request->date_of_work;
            $lich_cham_cong->user_id = $request->user_id;
            $lich_cham_cong->status = 1;
            $lich_cham_cong->note = "Hr đã sửa ngày công cho bạn";
            $lich_cham_cong->save();
            $response = $lich_cham_cong ? response()->json([
                'status' => true,
                'message' => 'Sửa lịch châm công thành công',
                'data' => $lich_cham_cong
            ])->setStatusCode(200) : response()->json([
                'status' => false,
                'message' => 'Sửa lịch châm công thấy bại',
            ])->setStatusCode(404);
        } else {
            $response = response()->json([
                'status' => false,
                'message' => "Bạn không có quyền truy cập",
            ])->setStatusCode(404);
        }

        return $response;
    }
    // update ot của nhân viên
    public function update_OT(Request $request)
    {
        if (Gate::allows('leader')) {
            $today = Carbon::now()->toDateString();
            $user_id = $request->id;
            foreach ($user_id as $value => $key) {
                $update_OT = LichChamCong::where('user_id', $key)->where('date_of_work', $today)->first();
                if ($update_OT) {
                    $update = LichChamCong::find($update_OT->id);
                    $update->check_ot = 1;
                    $update->save();
                }
            }
            $response =  response()->json([
                'status' => true,
                'message' => 'Cập nhật OT thành công',
                'data' => $update,
            ], 200);
        } else {
            $response = response()->json([
                'status' => false,
                'message' => "Không có quyền truy cập",
            ], 404);
        }
        return $response;
    }
    public function getListByUser(Request $request)
    {
        $lich_cham_cong = LichChamCong::select('time_keep_calendar.*', 'user_info.full_name')
            ->Join('users', 'time_keep_calendar.user_id', '=', 'users.id')
            ->join('user_info', 'users.id', '=', 'user_info.user_id')
            ->where('time_keep_calendar.deleted_at', null)
            ->where('time_keep_calendar.user_id', Auth::user()->id);
        if (!empty($request->keyword)) {
            $lich_cham_cong =  $lich_cham_cong->Where(function ($query) use ($request) {
                $query->where('user_info.full_name', 'like', "%" . $request->keyword . "%");
            });
        }
        if (!empty($request->date)) {
            $lich_cham_cong =  $lich_cham_cong->whereMonth('date_of_work', date('m', strtotime($request->date)));
        }
        $lich_cham_cong = $lich_cham_cong->paginate(($request->limit != null) ? $request->limit : 10);
        return  response()->json([
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
}
