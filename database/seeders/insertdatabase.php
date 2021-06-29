<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\userInfo;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class insertdatabase extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('tax')->insert([
            [
                'Tax_bracket' => 'Bậc I',
                'Taxable_income' => 5000000,
                'Tax_percentage' => 5
            ],
            [
                'Tax_bracket' => 'Bậc II',
                'Taxable_income' => 10000000,
                'Tax_percentage' => 10
            ],
            [
                'Tax_bracket' => 'Bậc III',
                'Taxable_income' => 18000000,
                'Tax_percentage' => 15
            ],
            [
                'Tax_bracket' => 'Bậc IV',
                'Taxable_income' => 32000000,
                'Tax_percentage' => 20
            ],
            [
                'Tax_bracket' => 'Bậc V',
                'Taxable_income' => 52000000,
                'Tax_percentage' => 25
            ],
            [
                'Tax_bracket' => 'Bậc VI',
                'Taxable_income' => 80000000,
                'Tax_percentage' => 30
            ],
            [
                'Tax_bracket' => 'Bậc VII',
                'Taxable_income' => 80000000,
                'Tax_percentage' => 35
            ],

        ]);
        DB::table('role')->insert([
            [
                'name' => 'Admin',

            ],
            [
                'name' => 'Hr',

            ],
            [
                'name' => 'Nhân Viên',

            ],
        ]);
        DB::table('department')->insert([
            [
                'name' => 'Tester',

            ],
            [
                'name' => 'Developer',

            ],
            [
                'name' => 'PHP_BackEnd',

            ],
            [
                'name' => 'FontEnd',

            ],
        ]);
        DB::table('position')->insert([
            [
                'name' => 'Phòng Test',

            ],
            [
                'name' => 'Phòng FontEnd',

            ],
            [
                'name' => 'Phòng BackEnd',

            ],
            [
                'name' => 'Phòng giám đốc',

            ],
            [
                'name' => 'Phòng hành chính',

            ],
        ]);
        $user_lst = [
            [
                'user_account' => 'Admin',
                'email' => 'tuantvph09673@fpt.edu.vn',
                'position_id' => '1',
                'department_id' => '1',
                'password' => Hash::make('123456'),
                'role_id' => 1
            ],
            [
                'user_account' => 'Hr',
                'email' => 'tuantong.datus@gmail.com',
                'position_id' => '2',
                'department_id' => '2',
                'password' => Hash::make('123456'),
                'role_id' => 2
            ],
            [
                'user_account' => 'VanTuan',
                'email' => 'tuantong32.datus@gmail.com',
                'position_id' => '3',
                'department_id' => '3',
                'password' => Hash::make('123456'),
                'role_id' => 3
            ]
        ];

        foreach ($user_lst as $item) {
            $model = new User();
            $model->fill($item);
            $model->save();
            if ($model->id) {
                $user_info = new userInfo();
                $user_info->user_id = $model->id;
                $user_info->full_name = "Văn Tuấn" . $model->id;
                $user_info->date_of_join = Carbon::now();
                $user_info->basic_salary = 8000000;
                $user_info->code_QR = $model->user_account . $model->id;
                $user_info->save();
            }
        }
    }
}
