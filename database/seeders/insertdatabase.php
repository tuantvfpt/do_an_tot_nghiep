<?php

namespace Database\Seeders;

use App\Models\Prize;
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
        
        DB::table('tax')->insert([
            [
                'tax_bracket' => 'Bậc I',
                'taxable_income' => 5000000,
                'tax_percentage' => 5
            ],
            [
                'tax_bracket' => 'Bậc II',
                'taxable_income' => 10000000,
                'tax_percentage' => 10
            ],
            [
                'tax_bracket' => 'Bậc III',
                'taxable_income' => 18000000,
                'tax_percentage' => 15
            ],
            [
                'tax_bracket' => 'Bậc IV',
                'taxable_income' => 32000000,
                'tax_percentage' => 20
            ],
            [
                'tax_bracket' => 'Bậc V',
                'taxable_income' => 52000000,
                'tax_percentage' => 25
            ],
            [
                'tax_bracket' => 'Bậc VI',
                'taxable_income' => 80000000,
                'tax_percentage' => 30
            ],
            [
                'tax_bracket' => 'Bậc VII',
                'taxable_income' => 80000000,
                'tax_percentage' => 35
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
                'name' => 'Employee',

            ],
            [
                'name' => 'Leader'
            ],
            [
                'name' => 'diemdanh'
            ]
        ]);
        DB::table('position')->insert([
            [
                'name' => 'Giám Đốc',

            ],
            [
                'name' => 'Leader',

            ],
            [
                'name' => 'Kế Toán',

            ],
            [
                'name' => 'Developer',

            ],

            [
                'name' => 'Tester',

            ],
            [
                'name' => 'Employee',

            ],
        ]);
        DB::table('department')->insert([
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
                'name' => 'Phòng Nhân Sư',

            ],
        ]);
        $user_lst = [
            [
                'user_account' => 'Admin',
                'email' => 'tuantvph09673@fpt.edu.vn',
                'position_id' => '1',
                'department_id' => '3',
                'password' => Hash::make('123456'),
                'role_id' => 1
            ],
            [
                'user_account' => 'KeToan',
                'email' => 'tuantong.datus@gmail.com',
                'position_id' => '3',
                'department_id' => '4',
                'password' => Hash::make('123456'),
                'role_id' => 2
            ],
            [
                'user_account' => 'Leader_Test',
                'email' => 'tuantong32.datus@gmail.com',
                'position_id' => '2',
                'department_id' => '1',
                'password' => Hash::make('123456'),
                'role_id' => 4
            ],
            [
                'user_account' => 'Leader_FontEnd',
                'email' => 'tuantong31.datus@gmail.com',
                'position_id' => '2',
                'department_id' => '2',
                'password' => Hash::make('123456'),
                'role_id' => 4
            ],
            [
                'user_account' => 'Leader_BackEnd',
                'email' => 'tuantong30.datus@gmail.com',
                'position_id' => '2',
                'department_id' => '3',
                'password' => Hash::make('123456'),
                'role_id' => 4
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
