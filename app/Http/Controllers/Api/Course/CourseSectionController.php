<?php

namespace App\Http\Controllers\Api\Course;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Course\Extend\CourseChapterForManageChaptersAndSections;
use App\Http\Requests\Course\Extend\CourseChapterForManageSections;
use App\Models\Course\Course;
use App\Models\Course\CourseChapter;
use App\Models\Course\CourseLive;
use App\Models\User\User;
use App\Rules\Course\SortsParamVerificationOfCourseSection;
use App\Services\Cache\TempAttributesService;
use App\Services\Region\RegionCode;
use App\Services\TencentCloudLive\UrlService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseSectionController extends BaseController
{
    //创建课节
    public function addSection(CourseChapterForManageSections $request)
    {
        //获得参数
        $input = $request->all();
        //获得要添加的课节的上级课程章的数据
        $fatherChapterInfo = $request->getDataOperator()->getChapterInfo();
        //验证参数
        $request->validate([
            'fid' => [
                'required'
            ],
            'chapter_name' => [
                'required'
            ]
        ]);
        //如果是直播，则需要判断直播参数
        if ($fatherChapterInfo['course_type'] == Course::LIVE) {
            $this->checkLiveParams($request, $fatherChapterInfo['course_id']);
        } else {
            //验证参数
            $request->validate([
                'video_id' => [
                    'required',
                    'exists:course_videos,id,user_id,' . User::getCurrentUserCache('id')
                ]
            ]);
        }
        //获得课节的排序值
        $sort = isset($input['sort']) ? $input['sort'] : (CourseChapter::getMaxSort($fatherChapterInfo['course_id'], $input['fid']) + 1);
        //开启数据库事务
        DB::beginTransaction();
        try {
            //添加课节
            $sectionInfo = CourseChapter::create([
                'fid' => $input['fid'],
                'course_id' => $fatherChapterInfo['course_id'],
                'chapter_name' => $input['chapter_name'],
                'video_id' => ($fatherChapterInfo['course_type'] == Course::VIDEO) ? $input['video_id'] : 0,
                'sort' => $sort
            ]);
            //如果课程是直播，需要增加课程直播数据
            if ($fatherChapterInfo['course_type'] == Course::LIVE) {
                //增加课程直播
                $streamStr = md5(uniqid() . $sectionInfo->id);
                CourseLive::create([
                    'course_chapter_id' => $sectionInfo->id,
                    'begin_time' => $input['begin_time'],
                    'end_time' => $input['end_time'],
                    'tencent_cloud_live_push_url' => UrlService::getPushUrl($streamStr, $input['end_time']),
                    'tencent_cloud_live_pull_url' => UrlService::getPullUrl($streamStr)
                ]);
            }
            //将课程设置为未审核状态
            Course::where('id', $fatherChapterInfo['course_id'])->update([
                'status' => Course::UNDER_REVIEW
            ]);
            //提交数据库事务
            DB::commit();
        } catch (\Exception $e) {
            //数据库事务回滚
            DB::rollback();
            //返回数据库错误
            return $this->error(__('messages.DatabaseError'));
        }
        //返回成功
        return $this->success();
    }

    //修改课节
    public function editSection(CourseChapterForManageSections $request)
    {
        //获得参数
        $input = $request->all();
        //获得要修改的课节的数据
        $chapterInfo = $request->getDataOperator()->getSectionInfo();
        //验证参数
        $request->validate([
            'id' => [
                'required'
            ],
            'chapter_name' => [
                'required'
            ],
            'sort' => [
                'required'
            ]
        ]);
        //如果是直播，则需要判断直播参数
        if ($chapterInfo['course_type'] == Course::LIVE) {
            $this->checkLiveParams($request, $chapterInfo['course_id'], $input['id']);
        } else {
            //验证参数
            $request->validate([
                'video_id' => [
                    'required',
                    'exists:course_videos,id,user_id,' . User::getCurrentUserCache('id')
                ]
            ]);
        }
        //开启数据库事务
        DB::beginTransaction();
        try {
            //修改课节
            CourseChapter::where('id', $input['id'])->update([
                'chapter_name' => $input['chapter_name'],
                'video_id' => ($chapterInfo['course_type'] == Course::VIDEO) ? $input['video_id'] : 0,
                'sort' => $input['sort']
            ]);
            //如果课程是直播，需要修改课程直播数据
            if ($chapterInfo['course_type'] == Course::LIVE) {
                //修改课程直播
                $streamStr = md5(uniqid() . $input['id']);
                CourseLive::where('course_chapter_id', $input['id'])->update([
                    'begin_time' => $input['begin_time'],
                    'end_time' => $input['end_time'],
                    'tencent_cloud_live_push_url' => UrlService::getPushUrl($streamStr, $input['end_time']),
                    'tencent_cloud_live_pull_url' => UrlService::getPullUrl($streamStr)
                ]);
            }
            //将课程设置为未审核状态
            Course::where('id', $chapterInfo['course_id'])->update([
                'status' => Course::UNDER_REVIEW
            ]);
            //提交数据库事务
            DB::commit();
        } catch (\Exception $e) {
            //数据库事务回滚
            DB::rollback();
            //返回数据库错误
            return $this->error(__('messages.DatabaseError'));
        }
        //返回成功
        return $this->success();
    }

    //删除课节
    public function deleteSection(CourseChapterForManageSections $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $request->validate([
            'id' => [
                'required'
            ]
        ]);
        //获得要删除的课节的数据
        $chapterInfo = $request->getDataOperator()->getSectionInfo();
        //开启数据库事务
        DB::beginTransaction();
        try {
            //删除课节
            CourseChapter::where('id', $input['id'])->delete();
            //如果课程是直播，需要删除课程直播数据
            if ($chapterInfo['course_type'] == Course::LIVE) {
                //删除课程直播
                CourseLive::where('course_chapter_id', $input['id'])->delete();
            }
            //将课程设置为未审核状态
            Course::where('id', $chapterInfo['course_id'])->update([
                'status' => Course::UNDER_REVIEW
            ]);
            //提交数据库事务
            DB::commit();
        } catch (\Exception $e) {
            //数据库事务回滚
            DB::rollback();
            //返回数据库错误
            return $this->error(__('messages.DatabaseError'));
        }
        //返回成功
        return $this->success();
    }

    //批量更改课节的排序
    public function editSorts(CourseChapterForManageChaptersAndSections $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $dataOperator = new TempAttributesService(['sorts']);
        $request->validate([
            'course_id' => [
                'required'
            ],
            'sorts' => [
                'required',
                new SortsParamVerificationOfCourseSection($input['course_id'], $dataOperator)
            ]
        ]);
        $sorts = $dataOperator->getSorts();
        foreach ($sorts as $key => $value) {
            CourseChapter::where('id', $key)->update([
                'sort' => $value
            ]);
        }
        return $this->success();
    }

    //查询公共课节
    public function getPublicSections(Request $request)
    {
        //获得参数
        $input = $request->all();
        //设置默认分页大小
        $pageSize = isset($input['page_size']) ? $input['page_size'] : 10;
        //设置查询条件
        $where = [
            ['courses.status', Course::APPROVED],
            ['course_chapters.fid', '>', 0]
        ];
        if (isset($input['region_code'])) {
            $where = RegionCode::getSelectWhereFromCode($where, 'courses.region_code', $input['region_code']);
        }
        //设置排序条件
        $orderArr = ['course_chapters.study_people_number' => 'desc'];
        $list = CourseChapter::leftJoin(
            'courses',
            'course_chapters.course_id',
            'courses.id'
        )->leftJoin(
            'users',
            'courses.user_id',
            'users.id'
        )->where($where)->select(
            'course_chapters.*',
            'courses.course_name',
            'courses.course_cover',
            'courses.price',
            'courses.type',
            'courses.user_id',
            'users.portrait',
            'users.nickname'
        )->ordersBy($orderArr)->paginate($pageSize);
        return $this->success($list);
    }

    //检查直播参数
    private function checkLiveParams($request, $course_id, $course_chapter_id = 0)
    {
        //验证参数
        $request->validate([
            'begin_time' => [
                'required',
                'date_format:Y-m-d H:i:s',
                function ($attribute, $value, $fail) {
                    if (strtotime($value) < time()) {
                        return $fail(__('validation.notEarlierThanCurrentTime'));
                    }
                }
            ],
            'end_time' => [
                'required',
                'date_format:Y-m-d H:i:s',
                function ($attribute, $value, $fail) use ($course_id, $request, $course_chapter_id) {
                    $begin_time = $request->input('begin_time', '');
                    if (strtotime($begin_time) >= strtotime($value)) {
                        return $fail(__('validation.notEarlierThanValue1', [
                            'value1' => __('validation.attributes.begin_time')
                        ]));
                    }
                    if (CourseChapter::leftJoin(
                            'course_lives',
                            'course_chapters.id',
                            'course_lives.course_chapter_id'
                        )->where(
                            'course_chapters.course_id',
                            $course_id
                        )->where(function ($query) use ($begin_time, $value, $course_chapter_id) {
                            $query->whereBetween('course_lives.begin_time', [$begin_time, $value])->orWhereBetween('course_lives.end_time', [$begin_time, $value]);
                        })->where('course_lives.course_chapter_id', '!=', $course_chapter_id)->count() > 0) {
                        return $fail(__('messages.liveBroadcastAlreadyExistsInTimeRange'));
                    }
                }
            ]
        ]);
    }
}
