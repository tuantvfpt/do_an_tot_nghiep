<?php

namespace Database\Seeders;

use App\Models\TongThuNhap;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class Fakedata extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $user = User::all();
        // foreach ($user as $user) {
        //     // $luong = TongThuNhap::find('user_id',$user->id);
        //     $item = [
        //         'total_gross_salary' => "10000000",
        //         'total_net_salary' => "9000000",
        //         'status' => 1,
        //         'user_id' => $user->id,
        //         'date' => "2021-07-30",
        //         'updated_at' => Carbon::now(),
        //         'created_at' => Carbon::now(),
        //     ];
        //     DB::table('total_salary')->insert($item);
        // }
        // $user = User::all();

        // foreach ($user as $user) {
        //     $item = [
        //         'time_of_check_in' => '08:00:00',
        //         'time_of_check_out' => '17:00:00',
        //         'status' => 1,
        //         'user_id' => $user->id,
        //         'date_of_work' => "2021-08-0",
        //         'updated_at' => Carbon::now(),
        //         'created_at' => Carbon::now(),
        //     ];
        //     DB::table('time_keep_calendar')->insert($item);
        // }
        for ($i = 1; $i < 30; $i++) {
            $item = [
                'time_of_check_in' => "8:00:00",
                'time_of_check_out' => "17:00:00",
                'date_of_work' => "2021-08-28",
                'status' => 1,
                'check_ot' => 0,
                'user_id' => $i,
            ];
            DB::table('time_keep_calendar')->insert($item);
        }
        // for ($i = 1; $i <= 50; $i++) {
        //     $item = [
        //         'total_gross_salary' => "2700000",
        //         'total_net_salary' => "2700000",
        //         'status' => 1,
        //         'user_id' => $i,
        //         'date' => "2021-01-30",
        //         'updated_at' => Carbon::now(),
        //         'created_at' => Carbon::now(),
        //     ];
        //     DB::table('total_salary')->insert($item);
        // }

        // for ($i = 6; $i < 51; $i++) {
        //     $item = [
        //         'user_account' => "NhanVien$i",
        //         'role_id' => "3",
        //         'position_id' => 6,
        //         'department_id' => rand(1, 3),
        //         'email' => "Nhanvien$i@gmail.com",
        //         'password' => Hash::make('123456')
        //     ];
        //     DB::table('users')->insert($item);
        // }
        // for ($i = 6; $i < 51; $i++) {
        //     $item = [
        //         'user_id' => "$i",
        //         'full_name' => "Nguyễn Thị Nhân Viên $i",
        //         'phone' => "038234xxxx",
        //         'date_of_join' => "2021-07-15",
        //         'basic_salary' => rand(5000000, 100000000),
        //         'code_QR' => "Nhanvien$i",
        //     ];
        //     DB::table('user_info')->insert($item);
        // }
    }
}
