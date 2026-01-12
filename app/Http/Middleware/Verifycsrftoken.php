<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Verifycsrftoken extends Middleware
{
    protected $except = [
        'api/auth/admin/login',
    ];
}
