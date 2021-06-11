<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class userInfo extends Model
{
    use HasFactory;
    protected $table = 'user_info';
    public function getuser()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
