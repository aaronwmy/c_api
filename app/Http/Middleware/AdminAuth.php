<?php

namespace App\Http\Middleware;

use App\Common\Constant;
use App\Http\Traits\ApiResponse;
use App\Models\AdminUser\AdminUser;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class AdminAuth extends Middleware
{
    use ApiResponse;

    public function handle($request, Closure $next)
    {
        $userModel = Auth::guard('admin')->user();
        if (!$userModel) {
            return $this->error(__('messages.noAuthority'), Constant::NO_EFFECTIVE_TOKEN_HTTP_STATUS);
        }
        $userModel['permissions'] = $userModel->getPermissionsViaRoles();
        AdminUser::setCurrentUserCache($userModel->toArray());
        return $next($request);
    }
}
