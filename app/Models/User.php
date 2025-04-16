<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, SoftDeletes; // Tambahkan SoftDeletes

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $dates = ['deleted_at']; // Ini opsional di versi Laravel terbaru, tapi aman untuk kompatibilitas

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
