<?php

namespace App\Http\Middleware;

use App\Http\Traits\ApiResponse;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Closure;

class XSS extends Middleware
{
    use ApiResponse;

    public function handle($request, Closure $next)
    {
        $input = $request->all();
        foreach ($input as $k => $v) {
            $input[$k] = htmlspecialchars($v);
        }
        $request->merge($input);
        return $next($request);
    }
}
