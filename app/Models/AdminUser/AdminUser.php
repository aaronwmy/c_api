<?php

namespace App\Models\AdminUser;

use App\Common\Constant;
use Illuminate\Foundation\Auth\User as AuthUser;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class AdminUser extends AuthUser implements JWTSubject
{
    use HasRoles;

    protected $connection = Constant::MAIN_DB;
    protected $guarded = [];
    protected $hidden = ['password'];
    private static $currentUserCache = null;

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public static function getCurrentUserCache($key = null)
    {
        if (!is_null($key) && array_key_exists($key, self::$currentUserCache)) {
            return self::$currentUserCache[$key];
        }
        return self::$currentUserCache;
    }

    public static function setCurrentUserCache($data, $isForce = false)
    {
        if (self::$currentUserCache === null || $isForce) {
            self::$currentUserCache = $data;
        }
    }

    public static function getPassword()
    {
        return AdminUser::where('id', AdminUser::getCurrentUserCache('id'))->first(['password'])->makeVisible('password')['password'];
    }

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }
}
