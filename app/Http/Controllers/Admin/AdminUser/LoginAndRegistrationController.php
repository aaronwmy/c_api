<?php

namespace App\Http\Controllers\Admin\AdminUser;

use App\Common\Constant;
use App\Http\Controllers\BaseController;
use App\Http\Requests\AdminUser\LoginAndRegistration;
use App\Models\AdminUser\AdminUser;
use App\Rules\Admin\AdminPasswordIsRight;
use App\Rules\Admin\AdminUsernameExists;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class LoginAndRegistrationController extends BaseController
{
    //登录
    public function login(LoginAndRegistration $request)
    {
        //获得参数
        $input = $request->all();
        $userInfo = AdminUser::where('username', $input['username'])->first();
        $request->validate([
            'username' => [
                new AdminUsernameExists($userInfo),
            ],
            'password' => [
                new AdminPasswordIsRight($userInfo['password'])
            ]
        ]);

        $token = Auth::guard('admin')->login($userInfo);

        return $this->success([
            'token' => $token
        ]);
    }

    //注销
    public function logout()
    {
        Auth::guard('admin')->logout();
        return $this->success();
    }

    //刷新令牌
    public function refreshToken()
    {
        try {
            $token = auth('admin')->refresh();
        } catch (TokenExpiredException $e) {
            return $this->error('', Constant::NO_EFFECTIVE_REFRESH_TOKEN_HTTP_STATUS);
        }
        return $this->success(
            [
                'token' => $token
            ]
        );
    }

}
