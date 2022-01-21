<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\BaseController;
use App\Models\Course\Course;
use App\Models\Course\CourseChapter;
use App\Models\CourseOrder\CourseOrder;
use App\Models\User\User;
use App\Models\User\UserStudyCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserStudyCourseController extends BaseController
{
    public function recordStudy(Request $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $request->validate([
            'course_id' => [
                'required',
                'exists:course_orders,course_id,status,' . CourseOrder::PAID . ',user_id,' . User::getCurrentUserCache('id')
            ],
            'section_id' => [
                'required',
                'exists:course_chapters,id,course_id,' . $request->input('course_id', 0) . ',fid,!0'
            ]
        ]);
        //如果操作者没有学习过这个课节，则增加操作者在这个课节的学习记录
        if (UserStudyCourse::where(
                'user_id', User::getCurrentUserCache('id')
            )->where(
                'course_id', $input['course_id']
            )->where(
                'section_id', $input['section_id']
            )->count() == 0) {
            //开启数据库事务
            DB::beginTransaction();
            try {
                //增加用户的学习记录
                UserStudyCourse::create([
                    'user_id' => User::getCurrentUserCache('id'),
                    'course_id' => $input['course_id'],
                    'section_id' => $input['section_id']
                ]);
                //更新课程的学习人数
                Course::updateStudyPeopleNumber($input['course_id']);
                //更新课节的学习人数
                CourseChapter::updateStudyPeopleNumber($input['course_id'], $input['section_id']);
                //提交数据库事务
                DB::commit();
            } catch (\Exception $e) {
                //数据库事务回滚
                DB::rollback();
                //返回数据库错误
                return $this->error(__('messages.DatabaseError'));
            }
        }
        //返回成功
        return $this->success();
    }
}
