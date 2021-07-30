<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Prize;
use App\Models\Prize_user;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PrizefineController extends Controller
{
    public function __construct(Prize $Prize)
    {
        $this->Prize = $Prize;
    }
    public function index(Request $request)
    {
        $prize_fine_money = $this->Prize->select('prize_fine.*', 'users.user_account',)
            ->Join('prize_fine_user', 'prize_fine_user.prize_fine_id', '=', 'prize_fine.id')
            ->Join('users', 'users.id', '=', 'prize_fine_user.user_id')
            ->where('prize_fine.deleted_at', null);
        if (Gate::allows('view')) {
            if (!empty($request->keyword)) {
                $prize_fine_money =  $prize_fine_money->Where(function ($query) use ($request) {
                    $query->Orwhere('prize_fine.name', 'like', "%" . $request->keyword . "%")
                        ->Orwhere('users.user_account', 'like', "%" . $request->keyword . "%");
                });
            }
        } else {
            $prize_fine_money->where('prize_fine_user.user_id', Auth::user()->id);
            if (!empty($request->keyword)) {
                $prize_fine_money =  $prize_fine_money->Where(function ($query) use ($request) {
                    $query->Orwhere('prize_fine.name', 'like', "%" . $request->keyword . "%")
                        ->Orwhere('users.user_account', 'like', "%" . $request->keyword . "%");
                });
            }
        }
        $prize_fine_money = $prize_fine_money->paginate(($request->limit != null) ? $request->limit : 10);
        return $prize_fine_money ?
            response()->json([
                'status' => true,
                'message' => 'lấy prize_fine_money thành công',
                'data' => $prize_fine_money->items(),
                'meta' => [
                    'total'      => $prize_fine_money->total(),
                    'perPage'    => $prize_fine_money->perPage(),
                    'currentPage' => $prize_fine_money->currentPage()
                ]
            ], 200) :
            response()->json([
                'status' => false,
                'message' => 'lấy prize_fine_money thất bại',
            ], 404);
    }
    public function getDetail($id)
    {
        $getdetail = Prize::select('prize_fine.*', 'users.user_account',)
            ->Join('prize_fine_user', 'prize_fine_user.prize_fine_id', '=', 'prize_fine.id')
            ->Join('users', 'users.id', '=', 'prize_fine_user.user_id')
            ->where('prize_fine.id', $id)
            ->first();
        return response()->json([
            'status' => false,
            'message' => 'lấy chi tiết thành công',
            'data' => $getdetail
        ], 200);
    }
    public function create(Request $request)
    {

        if (Gate::allows('create')) {
            $validator = Validator::make(
                $request->all(),
                ['name' => 'required'],

            );
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()
                ], 400);
            }
            $prize_fine_money = new Prize;
            if ($request->thuong) {
                $prize_fine_money->name = $request->name;
                $prize_fine_money->prize_money = $request->prize_fine_money;
            } elseif ($request->phat) {
                $prize_fine_money->name = $request->name;
                $prize_fine_money->fine_money = $request->prize_fine_money;
            }
            $prize_fine_money->save();
            if ($prize_fine_money->id) {
                $prize_fine_money_user = new Prize_user();
                $prize_fine_money_user->prize_fine_id = $prize_fine_money->id;
                $prize_fine_money_user->user_id = $request->user_id;
                $prize_fine_money_user->date = Carbon::now()->toDateString();
                $prize_fine_money_user->save();
            }
            $response =  $prize_fine_money ?
                response()->json([
                    'status' => true,
                    'message' => 'Thêm prize_fine_money thành công',
                    'data' => $prize_fine_money
                ], 200) :
                response()->json([
                    'status' => false,
                    'message' => 'Sửa prize_fine_money thất bại',
                ], 404);
        } else {
            $response =  response()->json([
                'status' => false,
                'message' => 'Bạn không được phép',
            ], 403);
        }
        return $response;
    }
    public function update($id, Request $request)
    {

        if (Gate::allows('update')) {
            $validator = Validator::make(
                $request->all(),
                ['name' => 'required'],

            );
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()
                ], 400);
            }
            $prize_fine_money = Prize::find($id);
            if ($prize_fine_money) {
                $prize_fine_money->name = $request->name;
                if ($request->thuong) {
                    $prize_fine_money->prize_money = $request->prize_fine_money;
                    $prize_fine_money->fine_money = 0;
                }
                if ($request->phat) {
                    $prize_fine_money->prize_money = 0;
                    $prize_fine_money->fine_money = $request->prize_fine_money;
                }
                $prize_fine_money->save();
                $prize_fine_money_user = Prize_user::where('prize_fine_id', $id)->first();
                if ($prize_fine_money_user) {
                    $prize_fine_money_user = Prize_user::find($prize_fine_money_user->id);
                    $prize_fine_money_user->user_id = $request->user_id;
                    $prize_fine_money_user->date = Carbon::now()->toDateString();
                    $prize_fine_money_user->save();
                }
            }
            $response = $prize_fine_money ?
                response()->json([
                    'status' => true,
                    'message' => 'cập nhật prize_fine_money thành công',
                    'data' => $prize_fine_money
                ], 200) :
                response()->json([
                    'status' => false,
                    'message' => 'cập nhật prize_fine_money thất bại',
                ], 404);
        } else {
            $response = response()->json([
                'status' => false,
                'message' => 'Bạn không được phép',
            ], 403);
        }
        return $response;
    }
    public function delete($id)
    {
        if (Gate::allows('delete')) {
            $prize_fine_money_user = Prize_user::where('prize_fine_id', $id);
            $prize_fine_money = Prize::find($id);
            if ($prize_fine_money) {
                $prize_fine_money->delete();
                $prize_fine_money_user->delete();
            }
            $response =  $prize_fine_money_user || $prize_fine_money ?
                response()->json([
                    'status' => true,
                    'message' => 'xóa prize_fine_money thành công',
                ], 200) :
                response()->json([
                    'status' => false,
                    'message' => 'xóa prize_fine_money thất bại',
                ], 404);
        } else {
            $response =  response()->json([
                'status' => false,
                'message' => 'Bạn không được phép',
            ], 403);
        }
        return $response;
    }
}
