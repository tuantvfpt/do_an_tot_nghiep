<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class chucvu extends Model
{
    use HasFactory;
    protected $table = 'position';
    public function chucvu_userinfo()
    {
        return $this->hasMany(userInfo::class, 'position_id');
    }
}
