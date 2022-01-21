<?php

namespace App\Http\Controllers\Api\Course;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Course\Extend\CourseChapterForDeleteChapters;
use App\Http\Requests\Course\Extend\CourseChapterForManageChapters;
use App\Http\Requests\Course\Extend\CourseChapterForManageChaptersAndSections;
use App\Models\Course\Course;
use App\Models\Course\CourseChapter;
use App\Rules\Course\CourseExists;
use App\Rules\Course\SortsParamVerificationOfCourseChapter;
use App\Services\Cache\TempAttributesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseChapterController extends BaseController
{
    //创建章
    public function addChapter(CourseChapterForManageChaptersAndSections $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $request->validate([
            'course_id' => [
                'required'
            ],
            'chapter_name' => [
                'required'
            ]
        ]);
        //获得章的排序值
        $sort = isset($input['sort']) ? $input['sort'] : (CourseChapter::getMaxSort($input['course_id'], 0) + 1);
        //开启数据库事务
        DB::beginTransaction();
        try {
            //添加课程章
            CourseChapter::create([
                'course_id' => $input['course_id'],
                'chapter_name' => $input['chapter_name'],
                'sort' => $sort
            ]);
            //将课程设置为未审核状态
            Course::where('id', $input['course_id'])->update([
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

    //修改章
    public function editChapter(CourseChapterForManageChapters $request)
    {
        //获得参数
        $input = $request->all();
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
        //获得要更改的课程章的数据
        $chapterInfo = $request->getDataOperator()->getChapterInfo();
        //开启数据库事务
        DB::beginTransaction();
        try {
            //修改课程章
            CourseChapter::where('id', $input['id'])->update([
                'chapter_name' => $input['chapter_name'],
                'sort' => $input['sort']
            ]);
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

    //删除章
    public function deleteChapter(CourseChapterForDeleteChapters $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $request->validate([
            'id' => [
                'required'
            ]
        ]);
        //获得要更改的课程章的数据
        $chapterInfo = $request->getDataOperator()->getChapterInfo();
        //开启数据库事务
        DB::beginTransaction();
        try {
            //删除课程章
            CourseChapter::where('id', $input['id'])->delete();
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

    //查询章节
    public function getChapters(Request $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $dataOperator = new TempAttributesService(['courseInfo']);
        $request->validate([
            'course_id' => [
                'required',
                new CourseExists($dataOperator, CourseExists::PRIVATE)
            ]
        ]);
        //获得操作者拥有的课程数据
        $courseInfo = $dataOperator->getCourseInfo();
        //查询课程章节的数据
        $CourseChapter = new CourseChapter();
        if ($courseInfo['type'] == Course::LIVE) {
            $CourseChapter = $CourseChapter->leftJoin('course_lives', 'course_chapters.id', 'course_lives.course_chapter_id')->select('course_chapters.*', 'course_lives.begin_time', 'course_lives.end_time');
        }
        $list = $CourseChapter->where(
            'course_id',
            $input['course_id']
        )->getStructureOfChaptersDisplayedOnTheInterface();
        //返回改变了数据结构之后的数据
        return $this->success($list);
    }

    //查询公共章节
    public function getPublicChapters(Request $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $dataOperator = new TempAttributesService(['courseInfo']);
        $request->validate([
            'course_id' => [
                'required',
                new CourseExists($dataOperator, CourseExists::PUBLIC)
            ]
        ]);
        //获得操作者拥有的课程数据
        $courseInfo = $dataOperator->getCourseInfo();
        //查询课程章节的数据
        $CourseChapter = new CourseChapter();
        if ($courseInfo['type'] == Course::LIVE) {
            $CourseChapter = $CourseChapter->leftJoin('course_lives', 'course_chapters.id', 'course_lives.course_chapter_id')->select('course_chapters.*', 'course_lives.begin_time', 'course_lives.end_time');
        }
        $list = $CourseChapter->where(
            'course_id',
            $input['course_id']
        )->getStructureOfChaptersDisplayedOnTheInterface();
        //返回改变了数据结构之后的数据
        return $this->success($list);
    }

    //批量更改章的排序
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
                new SortsParamVerificationOfCourseChapter($input['course_id'], $dataOperator)
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
}
