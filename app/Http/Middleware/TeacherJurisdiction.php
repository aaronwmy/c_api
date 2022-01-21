<?php

namespace App\Http\Middleware;

use App\Common\Constant;
use App\Http\Traits\ApiResponse;
use App\Models\User\User;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Closure;

class TeacherJurisdiction extends Middleware
{
    use ApiResponse;

    public function handle($request, Closure $next)
    {
        if (User::getCurrentUserCache('type') == User::USER_TYPE) {
            return $this->error(__('messages.noAuthority'), Constant::TEACHER_AUTHORITY_REQUIRED);
        }
        return $next($request);
    }
}
