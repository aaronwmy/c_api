<?php

namespace App\Http\Middleware;

use App\Common\Constant;
use App\Http\Traits\ApiResponse;
use App\Models\User\User;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class ApiAuth extends Middleware
{
    use ApiResponse;

    public function handle($request, Closure $next)
    {
        $userModel = Auth::guard('api')->user();
        if (!$userModel) {
            return $this->error(__('messages.noAuthority'), Constant::NO_EFFECTIVE_TOKEN_HTTP_STATUS);
        }
        User::setCurrentUserCache($userModel->toArray());
        return $next($request);
    }
}
