<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LichChamCong;
use App\Models\lichTangCa;
use App\Models\thong_bao;
use App\Models\User;
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
            $dataArR = [];
            foreach ($request->listUsers2 as $value) {
                $a = [
                    'id' => $value['id']
                ];
                array_push($dataArR, $a);
            }
            foreach ($dataArR as $value) {
                $update = new lichTangCa();
                $update->name_leader = Auth::user()->user_account;
                $update->user_id = $value['id'];
                $update->date = $today;
                $update->time_tang_ca = $request->time_tang_ca;
                $update->note = $request->note;
                $update->status = 0;
                $update->save();
                if ($update) {
                    $thong_bao = new thong_bao();
                    $thong_bao->action_id = $update->id;
                    $thong_bao->type = 3;
                    $thong_bao->date_notyfi = Carbon::now()->toDateString();
                    $thong_bao->save();
                }
            }
            $response =  response()->json([
                'status' => true,
                'message' => 'Thêm danh sách OT thành công',
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
    public function danh_sach_tang_ca_by_leader(Request $request)
    {
        $list_OT = lichTangCa::select('lich_tang_ca.*', 'user_info.full_name')
            ->Join('users', 'lich_tang_ca.user_id', '=', 'users.id')
            ->join('user_info', 'users.id', '=', 'user_info.user_id');
        if (Gate::allows('view')) {
            if (!empty($request->keyword)) {
                $list_OT =  $list_OT->Where(function ($query) use ($request) {
                    $query->where('user_info.full_name', 'like', "%" . $request->keyword . "%");
                });
            }
            if (!empty($request->date)) {
                $list_OT =  $list_OT->whereMonth('lich_tang_ca.date', date('m', strtotime($request->date)));
            }
        } elseif (Gate::allows('leader')) {
            $check = User::where('id', Auth::user()->id)->first();
            $list_OT =  $list_OT->where('users.department_id', $check->department_id);
        } else {
            $list_OT =  $list_OT->where('lich_tang_ca.user_id', Auth::user()->id);
        }
        $list_OT = $list_OT->paginate(($request->limit != null) ? $request->limit : 10);
        return  response()->json([
            'status' => true,
            'message' => 'Lấy danh sách chấm công thành công',
            'data' => $list_OT
                ->items(),
            'meta' => [
                'total'      => $list_OT->total(),
                'perPage'    => $list_OT->perPage(),
                'currentPage' => $list_OT->currentPage()
            ]
        ])->setStatusCode(200);
    }
    public function getAllDelete(Request $request)
    {
        $list_OT = lichTangCa::select('lich_tang_ca.*', 'user_info.full_name')
            ->Join('users', 'lich_tang_ca.user_id', '=', 'users.id')
            ->whereNotNull('lich_tang_ca.deleted_at')
            ->withTrashed()
            ->join('user_info', 'users.id', '=', 'user_info.user_id');
        if (Gate::allows('view')) {
            if (!empty($request->keyword)) {
                $list_OT =  $list_OT->Where(function ($query) use ($request) {
                    $query->where('user_info.full_name', 'like', "%" . $request->keyword . "%");
                });
            }
            if (!empty($request->date)) {
                $list_OT =  $list_OT->whereMonth('lich_tang_ca.date', date('m', strtotime($request->date)));
            }
        } elseif (Gate::allows('leader')) {
            $check = User::where('id', Auth::user()->id)->first();
            $list_OT =  $list_OT->where('users.department_id', $check->department_id);
        }
        $list_OT = $list_OT->paginate(($request->limit != null) ? $request->limit : 10);
        return  response()->json([
            'status' => true,
            'message' => 'Lấy danh sách chấm công thành công',
            'data' => $list_OT->items(),
            'meta' => [
                'total'      => $list_OT->total(),
                'perPage'    => $list_OT->perPage(),
                'currentPage' => $list_OT->currentPage()
            ]
        ])->setStatusCode(200);
    }
    public function danh_sach_tang_ca_by_user()
    {
        $today = Carbon::now()->toDateString();
        $list = lichTangCa::where('user_id', Auth::user()->id)->orderby('id', 'DESC')->where('date', $today)->where('status', 0)->get();
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

        if ($tangca && $request->comfirm == 'yes') {
            $tangca->status = 1;
            $tangca->lich_cham_cong_id = $check->id;
            $response =  response()->json([
                'status' => true,
                'message' => 'Cập nhật trạng thái thành công',
                'data' => $tangca
            ], 200);
        } else {
            $tangca->status = 2;
            $response =  response()->json([
                'status' => true,
                'message' => 'không đông ý tăng ca thành công',
                'data' => $tangca
            ], 200);
        }
        $tangca->save();
        return  $response;
    }
    public function delete($id)
    {
        $lich_tang_ca = lichTangCa::find($id);
        if ($lich_tang_ca) {
            $lich_tang_ca->delete();
        }
        return  $lich_tang_ca ? response()->json([
            'status' => true,
            'message' => 'Xóa thành công',
        ], 200) : response()->json([
            'status' => false,
            'message' => 'Xóa thất bại'
        ], 403);
    }
    public function khoi_phuc($id)
    {
        $khoi_phuc = lichTangCa::withTrashed()->find($id);
        if ($khoi_phuc) {
            $khoi_phuc->restore();
        }
        return  $khoi_phuc ? response()->json([
            'status' => true,
            'message' => 'Khôi phục thành công',
        ], 200) : response()->json([
            'status' => false,
            'message' => 'Khôi phục thất bại'
        ], 403);
    }
    public function destroy($id)
    {
        $lich_tang_ca = lichTangCa::withTrashed()->find($id);
        if ($lich_tang_ca) {
            $lich_tang_ca->forceDelete();
        }
        return  $lich_tang_ca ? response()->json([
            'status' => true,
            'message' => 'Xóa thành công',
        ], 200) : response()->json([
            'status' => false,
            'message' => 'Xóa thất bại'
        ], 403);
    }
    public function TrashAll(Request $request)
    {
        $ArrID = $request->all();
        foreach ($ArrID as $value) {
            $lich_tang_ca = lichTangCa::find($value);
            if ($lich_tang_ca) {
                $lich_tang_ca->delete();
            }
        }
        return response()->json([
            'status' => true,
            'message' => 'Bỏ vào thùng rác',
        ], 200);
    }
    public function DestroyAll(Request $request)
    {
        $ArrID = $request->all();
        foreach ($ArrID as $value) {
            $lich_tang_ca = lichTangCa::withTrashed()->find($value);
            if ($lich_tang_ca) {
                $lich_tang_ca->forceDelete();
            }
        }
        return response()->json([
            'status' => true,
            'message' => 'Xóa thành công',
        ], 200);
    }
    public function khoi_phuc_all(Request $request)
    {
        $ArrID = $request->all();
        foreach ($ArrID as $value) {
            $khoi_phuc = lichTangCa::withTrashed()->find($value);
            if ($khoi_phuc) {
                $khoi_phuc->restore();
            }
        }
        return response()->json([
            'status' => true,
            'message' => 'Khôi phục thành công',
        ], 200);
    }
}
