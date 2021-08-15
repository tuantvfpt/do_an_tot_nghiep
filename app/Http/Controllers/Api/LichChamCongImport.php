<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LichChamCong;
use App\Models\User;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;

class LichChamCongImport extends Controller
{
    public function index()
    {
        return view('lichchamcong.import');
    }
    public function import(Request $request)
    {
        if ($_FILES["file_tb"]["name"] != '') {
            $allowed_extension = array('xls', 'xlsx');
            $file_array = explode(".", $_FILES['file_tb']['name']);
            $file_extension = end($file_array);
            if (in_array($file_extension, $allowed_extension)) {
                $reader = IOFactory::createReader('Xlsx');
                $spreadsheet = $reader->load($_FILES['file_tb']['tmp_name']);
                $data = $spreadsheet->getActiveSheet()->toArray();
                unset($data[0]);
            }
            foreach ($data as $item) {
                $time = strtotime($item['4']);
                $newdate = date('Y-d-m', $time);
                $check = User::where('user_account', $item['1'])->first();
                $check_lich = LichChamCong::where('date_of_work', $newdate)->where('user_id', $check->id)->first();
                if (isset($check) && empty($check_lich)) {
                    $lich_cham_cong = new LichChamCong();
                    $lich_cham_cong->user_id = $check->id;
                    $lich_cham_cong->time_of_check_in = $item['2'];
                    $lich_cham_cong->time_of_check_out = $item['3'];
                    $lich_cham_cong->note = 'Hr đã thêm ngày công cho bạn';
                    $lich_cham_cong->date_of_work = $newdate;
                    if ($item['5'] == 'Có') {
                        $lich_cham_cong->check_ot = 1;
                    } else {
                        $lich_cham_cong->check_ot = 0;
                    }
                    $lich_cham_cong->status = 1;
                    $lich_cham_cong->save();
                    $response = response()->json([
                        'status' => true,
                        'message' => 'Improt dữ liệu thành công',
                        'data' => $lich_cham_cong
                    ]);
                } else {
                    $response = response()->json([
                        'status' => false,
                        'message' => 'Improt dữ liệu thất bại',
                    ]);
                }
            }
            return $response;
        }
    }
}
