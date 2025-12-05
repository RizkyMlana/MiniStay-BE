<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


/**
 * @OA\Schema(
 *   schema="Admin",
 *   type="object",
 *   required={"name","password"},
 *   @OA\Property(property="id", type="integer"),
 *   @OA\Property(property="name", type="string"),
 *   @OA\Property(property="password", type="string"),
 *   @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
 *   @OA\Property(property="updated_at", type="string", format="date-time", nullable=true),
 * )
 */
class Admin extends Authenticatable
{

    use HasFactory, HasApiTokens, Notifiable;

    protected $table = 'admins';
    
    protected $fillable = ['name', 'password'];
    protected $hidden = ['password'];

    public function scan() {
        return $this->hasMany(BookingScan::class);
    }
    public function chats(){
        return $this->hasMany(Chat::class);
    }
}
