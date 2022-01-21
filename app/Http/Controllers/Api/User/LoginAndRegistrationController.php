<?php


namespace App\Http\Controllers\Api\User;

use App\Common\Constant;
use App\Http\Controllers\BaseController;
use App\Http\Requests\User\LoginAndRegistration;
use App\Models\User\User;
use Illuminate\Support\Facades\Auth;

class LoginAndRegistrationController extends BaseController
{
    //注册
    public function register(LoginAndRegistration $request)
    {
        //获得参数
        $input = $request->all();
        $request->validate([
            'mobile' => [
                'unique:users,mobile'
            ]
        ]);
        try {
            $userInfo = User::create([
                'mobile' => $input['mobile']
            ]);
        } catch (\Exception $e) {
            return $this->error(__('messages.DatabaseError'));
        }

        $token = Auth::guard('api')->login($userInfo);

        return $this->success([
            'token' => $token,
            'user' => $userInfo
        ]);
    }

    //登录
    public function login(LoginAndRegistration $request)
    {
        //获得参数
        $input = $request->all();
        $userInfo = User::where('mobile', $input['mobile'])->first();
        $request->validate([
            'mobile' => function ($attribute, $value, $fail) use ($userInfo) {
                if (empty($userInfo)) {
                    return $fail(__('validation.exists'));
                }
            }
        ]);

        $token = Auth::guard('api')->login($userInfo);

        return $this->success([
            'token' => $token,
            'user' => $userInfo
        ]);
    }

    //注销
    public function logout()
    {
        Auth::guard('api')->logout();
        return $this->success();
    }

    //刷新令牌
    public function refreshToken()
    {
        try {
            $token = auth('api')->refresh();
        } catch (\Exception $e) {
            return $this->error('', Constant::NO_EFFECTIVE_REFRESH_TOKEN_HTTP_STATUS);
        }
        return $this->success(
            [
                'token' => $token
            ]
        );
    }

}
