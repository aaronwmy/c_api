<?php

namespace App\Models\User;

use App\Common\Constant;
use App\Models\Collection\Collection;
use App\Models\Course\Course;
use App\Models\Follow\Follow;
use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends AuthUser implements JWTSubject
{
    const USER_TYPE = 1;
    const TEACHER_TYPE = 2;
    const SCHOOL_TYPE = 3;
    const COMPANY_TYPE = 4;

    protected $connection = Constant::MAIN_DB;
    protected $guarded = [];
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

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }

    //更新收藏了某物的所有用户的收藏数量
    protected function updateCollectionCountOfAllCollectorsOfIt($other_id, $type)
    {
        $list = Collection::where('type', $type)->where('other_id', $other_id)->get(['user_id']);
        foreach ($list as $info) {
            $this::updateCollectionCount($info['user_id'], $type);
        }
    }

    //更新用户的收藏数量
    protected function updateCollectionCount($user_id, $type)
    {
        if ($type == Collection::COURSE) {
            $count = Collection::where(
                'collections.user_id',
                $user_id
            )->join('courses', 'collections.other_id', 'courses.id')->where('collections.type', $type)->count();
            User::where('id', $user_id)->update(['collection_course_count' => $count]);
        } elseif ($type == Collection::POSITION) {
            $count = Collection::where(
                'collections.user_id',
                $user_id
            )->join('recruitment_positions', 'collections.other_id', 'recruitment_positions.id')->where('collections.type', $type)->count();
            User::where('id', $user_id)->update(['collection_position_count' => $count]);
        } elseif ($type == Collection::RESUME) {
            $count = Collection::where(
                'collections.user_id',
                $user_id
            )->join('recruitment_resumes', 'collections.other_id', 'recruitment_resumes.id')->where('collections.type', $type)->count();
            User::where('id', $user_id)->update(['collection_resume_count' => $count]);
        }
        User::where('id', $user_id)->update(['collection_count' => DB::raw('collection_course_count+collection_position_count+collection_resume_count')]);
    }

    //更新用户的关注数量
    protected function updateUserFollowCount($user_id)
    {
        User::where('id', $user_id)->update(['user_follow_count' => Follow::where('user_id', $user_id)->count()]);
    }

    //更新用户的粉丝数量
    protected function updateFollowUserCount($user_id)
    {
        User::where('id', $user_id)->update(['follow_user_count' => Follow::where('to_user_id', $user_id)->count()]);
    }

    //更新已审核的课程数量
    protected function updateApprovedCourseCount($user_id)
    {
        User::where('id', $user_id)->update([
            'approved_course_count' => Course::where('user_id', $user_id)->where('status', Course::APPROVED)->count()
        ]);
    }
}
