<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class roles extends Model
{
    use HasFactory;
    protected $fillable = ['name'];
    public const IS_ADMIN = 1;
    public const IS_HR = 2;
    public const IS_USER = 3; 
}
