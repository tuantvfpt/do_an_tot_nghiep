<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class phongban extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'department';
    public function phongban()
    {
        return $this->hasMany(User::class, 'department_id');
    }
}
