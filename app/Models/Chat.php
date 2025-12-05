<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *   schema="Chat",
 *   type="object",
 *   required={"message","sender"},
 *   @OA\Property(property="id", type="integer"),
 *   @OA\Property(property="user_id", type="integer", nullable=true),
 *   @OA\Property(property="admin_id", type="integer", nullable=true),
 *   @OA\Property(property="message", type="string"),
 *   @OA\Property(property="sender", type="string", enum={"user","admin"}),
 *   @OA\Property(property="is_seen", type="boolean"),
 *   @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
 *   @OA\Property(property="updated_at", type="string", format="date-time", nullable=true),
 * )
 */

class Chat extends Model
{


    protected $fillable = ['user_id', 'admin_id', 'message', 'sender', 'is_seen'];

    public function user(){
        return $this->belongsTo(User::class);
    }
    public function admin(){
        return $this->belongsTo(Admin::class);
    }
}
