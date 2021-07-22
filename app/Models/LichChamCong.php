<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LichChamCong extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'Time_keep_Calendar';
    public function get_user_name()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
