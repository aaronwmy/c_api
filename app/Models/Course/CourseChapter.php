<?php

namespace App\Models\Course;

use App\Models\BaseModel;
use App\Models\User\User;
use App\Models\User\UserStudyCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseChapter extends BaseModel
{
    protected function getMaxSort($course_id, $fid)
    {
        $sort = CourseChapter::where('course_id', $course_id)->where('fid', $fid)->max('sort');
        return empty($sort) ? 0 : $sort;
    }

    //查询出呈现在界面上的树状结构数据
    public function scopeGetStructureOfChaptersDisplayedOnTheInterface($query)
    {
        $list = $query->orderBy('fid', 'asc')->orderBy('sort', 'asc')->get()->toArray();
        $data = [];
        $n = 0;
        for ($i = 0; $i < count($list); $i++) {
            if ($list[$i]['fid'] == 0) {
                $data[] = $list[$i];
            } else {
                $n = $i;
                break;
            }
        }
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['child'] = [];
            for ($k = $n; $k < count($list); $k++) {
                if ($data[$i]['id'] == $list[$k]['fid']) {
                    $data[$i]['child'][] = $list[$k];
                }
            }
        }
        return $data;
    }

    //更新课节的学习人数
    protected function updateStudyPeopleNumber($course_id, $section_id)
    {
        CourseChapter::where('id', $section_id)->update([
            'study_people_number' => UserStudyCourse::where('course_id', $course_id)->where('section_id', $section_id)->count(DB::raw('distinct user_id'))
        ]);
    }
}
