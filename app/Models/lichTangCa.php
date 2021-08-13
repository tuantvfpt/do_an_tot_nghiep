<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class lichTangCa extends Model
{
    use HasFactory;
    protected $table = 'lich_tang_ca';
    use SoftDeletes;
}
