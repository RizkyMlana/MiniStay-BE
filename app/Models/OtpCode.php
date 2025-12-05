<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *   schema="OtpCode",
 *   type="object",
 *   required={"phone","code","expires_at"},
 *   @OA\Property(property="id", type="integer"),
 *   @OA\Property(property="phone", type="string"),
 *   @OA\Property(property="code", type="string"),
 *   @OA\Property(property="expires_at", type="string", format="date-time"),
 *   @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
 *   @OA\Property(property="updated_at", type="string", format="date-time", nullable=true),
 * )
 */

class OtpCode extends Model
{

    protected $fillable = ['phone', 'code', 'expires_at'];

    protected $casts = ['expires_at' => 'datetime'];
}
