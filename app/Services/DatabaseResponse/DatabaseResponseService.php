<?php

namespace App\Services\DatabaseResponse;

use App\Models\Collection\Collection;
use App\Models\Course\Course;
use App\Models\Course\CourseChapter;
use App\Models\Follow\Follow;
use App\Models\Recruitment\RecruitmentPositionRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DatabaseResponseService
{
    const LIST = 1;
    const INFO = 2;

    public function __call($method, $parameters)
    {
        return $this->{$method}(...$parameters);
    }

    public static function __callStatic($method, $parameters)
    {
        return (new static)->{$method}(...$parameters);
    }

    //设置“是否被操作者关注过”的预处理数据
    private static $setIsFollowedData = [];
    //设置“是否被操作者购买过”的预处理数据
    private static $setIsPurchasedData = [];
    //设置“是否被操作者收藏过”的预处理数据
    private static $setAlreadyCollectedData = [];
    //设置“是否课程已经开始”的预处理数据
    private static $setAlreadyStartedData = [];
    //设置“是否已经被操作者申请过”的预处理数据
    private static $setAlreadyRequestedData = [];

    //操作者发布的职位的id的数组
    private static $positionIds = [];

    //预处理设置“是否被操作者关注过”
    private function preSetIsFollowed($toUserIdKey)
    {
        $userModel = Auth::guard('api')->user();
        DatabaseResponseService::$setIsFollowedData = [empty($userModel) ? 0 : $userModel['id'], $toUserIdKey];
        return $this;
    }

    //预处理设置“是否被操作者购买过”
    private function preSetIsPurchased($courseIdKey)
    {
        $userModel = Auth::guard('api')->user();
        DatabaseResponseService::$setIsPurchasedData = [empty($userModel) ? 0 : $userModel['id'], $courseIdKey];
        return $this;
    }

    //预处理设置“是否被操作者收藏过”
    private function preSetAlreadyCollected($type, $otherIdKey)
    {
        $userModel = Auth::guard('api')->user();
        DatabaseResponseService::$setAlreadyCollectedData = [empty($userModel) ? 0 : $userModel['id'], $type, $otherIdKey];
        return $this;
    }

    //预处理设置“是否课程已经开始”
    private function preSetAlreadyStarted($courseTypeKey, $courseIdKey)
    {
        $userModel = Auth::guard('api')->user();
        DatabaseResponseService::$setAlreadyStartedData = [$userModel, $courseTypeKey, $courseIdKey];
        return $this;
    }

    //预处理设置“职位是否已经被操作者申请过”
    private function preSetAlreadyRequested($positionIdKey)
    {
        $userModel = Auth::guard('api')->user();
        DatabaseResponseService::$setAlreadyRequestedData = [$userModel, $positionIdKey];
        return $this;
    }

    //执行代码
    private function exec(&$data, $type = self::LIST)
    {
        if (!empty(DatabaseResponseService::$setAlreadyRequestedData)) {
            if (DatabaseResponseService::$setAlreadyRequestedData[0]) {
                self::$positionIds = RecruitmentPositionRequest::where(
                    'user_id',
                    DatabaseResponseService::$setAlreadyRequestedData[0]['id']
                )->first([DB::raw('group_concat(distinct position_id) as position_ids')])['position_ids'];
                self::$positionIds = empty(self::$positionIds) ? [] : explode(',', self::$positionIds);
            }
        }
        if ($type == 1) {
            $data->getCollection()->transform(function ($info, $key) {
                $this->setInfo($info);
                return $info;
            });
        } elseif ($type == 2) {
            $this->setInfo($data);
        }
    }

    //设置单行数据
    private function setInfo(&$info)
    {
        if (!empty(DatabaseResponseService::$setIsFollowedData)) {
            $info['is_followed'] = (DatabaseResponseService::$setIsFollowedData[0] == 0) ? 0 : (Follow::checkIfFollowed(
                DatabaseResponseService::$setIsFollowedData[0],
                $info[DatabaseResponseService::$setIsFollowedData[1]]
            ) ? 1 : 0);
        }
        if (!empty(DatabaseResponseService::$setIsPurchasedData)) {
            $info['is_purchased'] = (DatabaseResponseService::$setIsPurchasedData[0] == 0) ? 0 : (Course::courseIsPurchasedByUser(
                $info[DatabaseResponseService::$setIsPurchasedData[1]],
                DatabaseResponseService::$setIsPurchasedData[0]
            ) ? 1 : 0);
        }
        if (!empty(DatabaseResponseService::$setAlreadyCollectedData)) {
            $info['already_collected'] = (DatabaseResponseService::$setAlreadyCollectedData[0] == 0) ? 0 : (Collection::checkIfCollected(
                DatabaseResponseService::$setAlreadyCollectedData[0],
                DatabaseResponseService::$setAlreadyCollectedData[1],
                $info[DatabaseResponseService::$setAlreadyCollectedData[2]]
            ) ? 1 : 0);
        }
        if (!empty(DatabaseResponseService::$setAlreadyStartedData)) {
            $info['already_started'] = (empty(DatabaseResponseService::$setAlreadyStartedData[0]) || $info[DatabaseResponseService::$setAlreadyStartedData[1]] == Course::VIDEO) ? 0 : (CourseChapter::leftJoin(
                'course_lives',
                'course_chapters.id',
                'course_lives.course_chapter_id'
            )->where('course_chapters.course_id', $info[DatabaseResponseService::$setAlreadyStartedData[2]])->where([
                ['course_lives.begin_time', '<=', date('Y-m-d H:i:s')]
            ])->count() > 0 ? 1 : 0);
        }
        if (!empty(DatabaseResponseService::$setAlreadyRequestedData)) {
            $info['already_requested'] = empty(DatabaseResponseService::$setAlreadyRequestedData[0]) ? 0 : (in_array($info[DatabaseResponseService::$setAlreadyRequestedData[1]], self::$positionIds) ? 1 : 0);
        }
    }
}
