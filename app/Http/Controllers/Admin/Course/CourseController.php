<?php

namespace App\Http\Controllers\Admin\Course;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Course\Course as CourseRequest;
use App\Http\Requests\Course\Extend\CourseForApprovedCourses;
use App\Models\Course\Course;
use App\Models\SiteMail\SiteMail;
use App\Models\User\User;
use Illuminate\Support\Facades\DB;

class CourseController extends BaseController
{
    //查询课程
    public function getList(CourseRequest $request)
    {
        //获得参数
        $input = $request->all();
        //设置默认分页大小
        $pageSize = isset($input['page_size']) ? $input['page_size'] : 12;
        //设置查询条件
        $where = [];
        if (isset($input['status'])) {
            array_push($where, ['status', $input['status']]);
        }
        if (isset($input['type'])) {
            array_push($where, ['type', $input['type']]);
        }
        if (isset($input['course_name'])) {
            array_push($where, ['course_name', 'like', '%' . $input['course_name'] . '%']);
        }
        if (isset($input['begin_time'])) {
            array_push($where, ['created_at', '>=', $input['begin_time']]);
        }
        if (isset($input['end_time'])) {
            array_push($where, ['created_at', '<=', $input['end_time']]);
        }
        if (isset($input['id'])) {
            array_push($where, ['id', $input['id']]);
        }
        //查询课程数据
        $list = Course::where($where)->orderBy('id', 'desc')->paginate($pageSize);
        //返回课程数据
        return $this->success($list);
    }

    //拒绝课程
    public function rejectCourse(CourseForApprovedCourses $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $request->validate([
            'course_id' => [
                'required'
            ],
            'reasons_for_rejection' => [
                'required'
            ]
        ]);
        //开启数据库事务
        DB::beginTransaction();
        try {
            //将课程状态改成已拒绝，并修改课程被拒绝的原因。
            Course::where('id', $input['course_id'])->update([
                'status' => Course::REJECTED,
                'reasons_for_rejection' => $input['reasons_for_rejection']
            ]);
            //给课程的所有者发送系统消息
            $courseInfo = $request->getDataOperator()->getCourseInfo();
            SiteMail::sendSystemMessageToUser(
                $courseInfo['user_id'],
                __('messages.rejectCourseMsgTitle'),
                __('messages.rejectCourseMsgContent',
                    [
                        'value1' => $courseInfo['course_name'],
                        'value2' => $input['reasons_for_rejection']
                    ]
                )
            );
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

    //批准课程
    public function approvedCourse(CourseForApprovedCourses $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $request->validate([
            'course_id' => [
                'required'
            ]
        ]);
        //开启数据库事务
        DB::beginTransaction();
        try {
            //将课程状态改成已审核
            Course::where('id', $input['course_id'])->update([
                'status' => Course::APPROVED
            ]);
            //更新课程主人的已审核的课程数量
            $courseInfo = $request->getDataOperator()->getCourseInfo();
            User::updateApprovedCourseCount($courseInfo['user_id']);
            //给课程的所有者发送系统消息
            SiteMail::sendSystemMessageToUser(
                $courseInfo['user_id'],
                __('messages.approvedCourseMsgTitle'),
                __('messages.approvedCourseMsgContent',
                    [
                        'value1' => $courseInfo['course_name']
                    ]
                )
            );
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
}
