<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class phongban extends Model
{
    use HasFactory;
    protected $table = 'phong_ban';
    public function phongban_userinfo()
    {
        return $this->hasMany(userInfo::class, 'chuc_vu_id');
    }
}
