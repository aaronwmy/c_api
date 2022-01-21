<?php

namespace App\Http\Controllers\Api\Course;

use App\Http\Controllers\BaseController;
use App\Models\Course\Course;
use App\Models\Course\CourseLive;
use App\Models\CourseOrder\CourseOrder;
use App\Models\User\User;
use Illuminate\Http\Request;

class CourseLiveController extends BaseController
{
    //查询直播推流码
    public function getPushUrls()
    {
        //查询操作者所有的已审核的直播课程下面的正在直播或即将直播的推流码
        $list = CourseLive::leftJoin(
            'course_chapters',
            'course_lives.course_chapter_id',
            'course_chapters.id'
        )->leftJoin('courses', 'course_chapters.course_id', 'courses.id')->where(
            'courses.user_id', User::getCurrentUserCache('id')
        )->where(
            'courses.status', Course::APPROVED
        )->where('courses.type', Course::LIVE)->where([
            ['course_lives.begin_time', '<=', date('Y-m-d H:i:s', time() + 60 * 10)],
            ['course_lives.end_time', '>=', date('Y-m-d H:i:s')]
        ])->orderBy('course_lives.begin_time', 'asc')->select(
            'course_lives.tencent_cloud_live_push_url',
            'course_lives.begin_time',
            'course_lives.end_time',
            'course_chapters.course_id',
            'course_chapters.chapter_name',
            'courses.course_name',
            'course_chapters.id as section_id'
        )->get();
        $list->each(function ($info, $key) {
            $info['is_started'] = (strtotime($info['begin_time']) <= time()) ? 1 : 0;
            return $info;
        });
        return $this->success($list);
    }

    //查询直播播流码
    public function getPullUrls(Request $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $request->validate([
            'course_id' => [
                'required'
            ]
        ]);
        //查询某个直播课程下的操作者已购买的正在直播或即将直播的播流码
        $list = CourseLive::leftJoin(
            'course_chapters',
            'course_lives.course_chapter_id',
            'course_chapters.id'
        )->leftJoin(
            'courses',
            'course_chapters.course_id',
            'courses.id'
        )->leftJoin(
            'course_orders',
            'courses.id',
            'course_orders.course_id'
        )->where(
            'course_chapters.course_id', $input['course_id']
        )->where(
            'courses.type', Course::LIVE
        )->where(
            'course_orders.user_id', User::getCurrentUserCache('id')
        )->where(
            'course_orders.status',
            CourseOrder::PAID
        )->where([
            ['course_lives.begin_time', '<=', date('Y-m-d H:i:s', time() + 60 * 10)],
            ['course_lives.end_time', '>=', date('Y-m-d H:i:s')]
        ])->orderBy('course_lives.begin_time', 'asc')->select(
            'course_lives.tencent_cloud_live_pull_url',
            'course_lives.begin_time',
            'course_lives.end_time',
            'course_chapters.course_id',
            'course_chapters.chapter_name',
            'courses.course_name',
            'course_chapters.id as section_id'
        )->get();
        $list->each(function ($info, $key) {
            $info['is_started'] = (strtotime($info['begin_time']) <= time()) ? 1 : 0;
            return $info;
        });
        return $this->success($list);
    }
}
