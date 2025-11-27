<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;

    protected $table = 'admins';
    
    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password'];

    public function scan() {
        return $this->hasMany(BookingScan::class);
    }
    public function chats(){
        return $this->hasMany(Chat::class);
    }
}
