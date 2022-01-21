<?php

namespace App\Http\Controllers\Admin\Course;

use App\Http\Controllers\BaseController;
use App\Models\Course\CourseChapter;
use Illuminate\Http\Request;

class CourseChapterController extends BaseController
{
    //查询章节
    public function getChapters(Request $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $request->validate([
            'course_id' => [
                'required'
            ]
        ]);
        //查询课程章节的数据
        $list = CourseChapter::leftJoin('course_lives', 'course_chapters.id', 'course_lives.course_chapter_id')->where(
            'course_id',
            $input['course_id']
        )->select('course_chapters.*', 'course_lives.begin_time', 'course_lives.end_time')->getStructureOfChaptersDisplayedOnTheInterface();
        //返回改变了数据结构之后的数据
        return $this->success($list);
    }
}
