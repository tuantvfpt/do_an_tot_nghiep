<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    public function userinfo()
    {
        return $this->hasOne(userInfo::class, 'user_id');
    }
    public function chucvu_userinfo()
    {
        return $this->belongsTo(chucvu::class, 'position_id');
    }
    public function phongban_userinfo()
    {
        return $this->belongsTo(phongban::class, 'position_id');
    }
    public function user_calendar()
    {
        return $this->hasMany(LichChamCong::class, 'user_id');
    }
}
