<?php

namespace App\Http\Controllers\Api\Course;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Course\Extend\CourseForManageCourses;
use App\Models\Collection\Collection;
use App\Models\Course\Course;
use App\Models\Course\CourseChapter;
use App\Models\User\User;
use App\Rules\Course\CourseCanBeDeleted;
use App\Rules\String\IsBase64;
use App\Services\DatabaseResponse\DatabaseResponseService;
use App\Services\Region\RegionCode;
use App\Services\String\StringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseController extends BaseController
{
    //创建课程
    public function addCourse(CourseForManageCourses $request)
    {
        //将课程简介参数中的特殊符号转化成加号，还原成base64编码
        $request['introduction'] = str_replace(' ', '+', str_replace('%2B', '+', htmlspecialchars_decode($request->input('introduction'))));
        //验证参数
        $request->validate([
            'course_name' => [
                'required'
            ],
            'price' => [
                'required'
            ],
            'course_cover' => [
                'required'
            ],
            'introduction' => [
                'required',
                new IsBase64()
            ],
            'type' => [
                'required'
            ],
            'region_code' => [
                'required'
            ]
        ]);
        //获得参数
        $input = $request->all();
        //开启数据库事务
        DB::beginTransaction();
        try {
            //添加课程
            $courseInfo = Course::create([
                'user_id' => User::getCurrentUserCache('id'),
                'course_name' => $input['course_name'],
                'price' => $input['price'],
                'course_cover' => $input['course_cover'],
                'introduction' => htmlspecialchars(clean(base64_decode($input['introduction']))),
                'type' => $input['type'],
                'region_code' => $input['region_code']
            ]);
            //更新操作者的已审核的课程数量
            User::updateApprovedCourseCount(User::getCurrentUserCache('id'));
            //提交数据库事务
            DB::commit();
        } catch (\Exception $e) {
            //数据库事务回滚
            DB::rollback();
            //返回数据库错误
            return $this->error(__('messages.DatabaseError'));
        }
        //返回刚刚创建的课程数据
        return $this->success($courseInfo);
    }

    //修改课程
    public function editCourse(CourseForManageCourses $request)
    {
        //将课程简介参数中的特殊符号转化成加号，还原成base64编码
        $request['introduction'] = str_replace(' ', '+', str_replace('%2B', '+', htmlspecialchars_decode($request->input('introduction'))));
        //验证参数
        $request->validate([
            'id' => [
                'required'
            ],
            'course_name' => [
                'required'
            ],
            'price' => [
                'required'
            ],
            'course_cover' => [
                'required'
            ],
            'introduction' => [
                'required',
                new IsBase64()
            ],
            'region_code' => [
                'required'
            ]
        ]);
        //获得参数
        $input = $request->all();
        //开启数据库事务
        DB::beginTransaction();
        try {
            //修改课程
            Course::where('id', $input['id'])->update([
                'course_name' => $input['course_name'],
                'price' => $input['price'],
                'course_cover' => $input['course_cover'],
                'introduction' => htmlspecialchars(clean(base64_decode($input['introduction']))),
                'region_code' => $input['region_code'],
                'status' => Course::UNDER_REVIEW
            ]);
            //更新操作者的已审核的课程数量
            User::updateApprovedCourseCount(User::getCurrentUserCache('id'));
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

    //查询课程
    public function getList(Request $request)
    {
        //获得参数
        $input = $request->all();
        //设置默认分页大小
        $pageSize = isset($input['page_size']) ? $input['page_size'] : 12;
        //设置查询条件
        $where = [
            ['courses.user_id', User::getCurrentUserCache('id')]
        ];
        if (isset($input['status'])) {
            array_push($where, ['courses.status', $input['status']]);
        }
        if (isset($input['type'])) {
            array_push($where, ['courses.type', $input['type']]);
        }
        if (isset($input['course_name'])) {
            array_push($where, ['courses.course_name', 'like', '%' . $input['course_name'] . '%']);
        }
        if (isset($input['region_code'])) {
            $where = RegionCode::getSelectWhereFromCode($where, 'courses.region_code', $input['region_code']);
        }
        if (isset($input['id'])) {
            array_push($where, ['courses.id', $input['id']]);
        }
        //设置排序条件
        $orderArr = ['courses.id' => 'desc'];
        //查询课程数据
        $list = Course::leftJoin(
            'users',
            'courses.user_id',
            'users.id'
        )->leftJoin(
            'regions',
            'courses.region_code',
            'regions.region_code'
        )->where($where)->select('courses.*', 'users.nickname', 'users.portrait', 'regions.region_fullname')->ordersBy($orderArr)->paginate($pageSize);
        $list->getCollection()->transform(function ($info, $key) {
            $info['is_purchased'] = Course::courseIsPurchased($info['id']) ? 1 : 0;
            return $info;
        });
        //返回课程数据
        return $this->success($list);
    }

    //查询公共课程
    public function getPublicList(Request $request)
    {
        //获得参数
        $input = $request->all();
        //设置默认分页大小
        $pageSize = isset($input['page_size']) ? $input['page_size'] : 12;
        //设置查询条件
        $where = [
            ['status', Course::APPROVED]
        ];
        if (isset($input['type'])) {
            array_push($where, ['courses.type', $input['type']]);
        }
        if (isset($input['course_name'])) {
            array_push($where, ['course_name', 'like', '%' . $input['course_name'] . '%']);
        }
        if (isset($input['region_code'])) {
            $where = RegionCode::getSelectWhereFromCode($where, 'courses.region_code', $input['region_code']);
        }
        if (isset($input['id'])) {
            array_push($where, ['courses.id', $input['id']]);
        }
        if (isset($input['user_id'])) {
            array_push($where, ['courses.user_id', $input['user_id']]);
        }
        if (isset($input['is_free']) && $input['is_free'] == 1) {
            array_push($where, ['courses.price', 0]);
        }
        //设置排序条件
        $orderArr = ['courses.id' => 'desc'];
        if (isset($input['order'])) {
            if ($input['order'] == 'newest') {
                $orderArr = ['courses.updated_at' => 'desc'];
            } elseif ($input['order'] == 'most_expensive') {
                $orderArr = ['courses.price' => 'desc'];
            } elseif ($input['order'] == 'cheapest') {
                $orderArr = ['courses.price' => 'asc'];
            } elseif ($input['order'] == 'recommend') {
                $orderArr = ['courses.study_people_number' => 'desc'];
            }
        }
        //查询课程数据
        $list = Course::leftJoin(
            'users',
            'courses.user_id',
            'users.id'
        )->where($where)->select('courses.*', 'users.nickname', 'users.portrait', 'users.type as user_type', 'users.follow_user_count')->ordersBy($orderArr)->paginate($pageSize);
        //检查是否购买过该课程
        DatabaseResponseService::preSetIsPurchased('id');
        //检查是否关注过该教师
        if (isset($input['check_if_followed']) && $input['check_if_followed'] == 1) {
            DatabaseResponseService::preSetIsFollowed('user_id');
        }
        //检查是否收藏过该课程
        if (isset($input['check_if_already_collected']) && $input['check_if_already_collected'] == 1) {
            DatabaseResponseService::preSetAlreadyCollected(Collection::COURSE, 'id');
        }
        //检查是否课程已经开始
        if (isset($input['check_if_already_started']) && $input['check_if_already_started'] == 1) {
            DatabaseResponseService::preSetAlreadyStarted('type', 'id');
        }
        DatabaseResponseService::exec($list);
        //返回课程数据
        return $this->success($list);
    }

    //删除课程
    public function deleteCourse(CourseForManageCourses $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $request->validate([
            'id' => [
                'required',
                new CourseCanBeDeleted()
            ]
        ]);
        //开启数据库事务
        DB::beginTransaction();
        try {
            //删除课程
            Course::where('id', $input['id'])->delete();
            //更新操作者的已审核的课程数量
            User::updateApprovedCourseCount(User::getCurrentUserCache('id'));
            //删除操作者可能存在的课程收藏
            Collection::where(
                'user_id',
                User::getCurrentUserCache('id')
            )->where('type', Collection::COURSE)->where('other_id', $input['id'])->delete();
            //更新所有收藏了该课程的用户的收藏课程的数量
            User::updateCollectionCountOfAllCollectorsOfIt($input['id'], Collection::COURSE);
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

    //查询收藏的课程
    public function getCollectionList(Request $request)
    {
        //获得参数
        $input = $request->all();
        //设置默认分页大小
        $pageSize = isset($input['page_size']) ? $input['page_size'] : 12;
        //设置查询条件
        $where = [
            ['collections.user_id', User::getCurrentUserCache('id')],
            ['collections.type', Collection::COURSE]
        ];
        //查询课程数据
        $list = Collection::join(
            'courses',
            'collections.other_id',
            'courses.id'
        )->leftJoin(
            'users',
            'courses.user_id',
            'users.id'
        )->where($where)->select(
            'courses.*',
            'users.nickname',
            'users.portrait',
            'collections.id as collection_id'
        )->orderBy('collections.created_at', 'desc')->paginate($pageSize);
        //返回课程数据
        return $this->success($list);
    }
}
