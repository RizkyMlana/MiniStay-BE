<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password'];

    public function scan() {
        return $this->hasMany(BookingScan::class);
    }
    public function chats(){
        return $this->hasMany(Chat::class);
    }
}
