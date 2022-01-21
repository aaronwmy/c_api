<?php

namespace App\Http\Controllers\Admin\Course;

use App\Http\Controllers\BaseController;
use App\Models\Course\CourseVideo;
use Illuminate\Http\Request;

class CourseVideoController extends BaseController
{
    //查询视频
    public function getList(Request $request)
    {
        //获得参数
        $input = $request->all();
        //设置默认分页大小
        $pageSize = isset($input['page_size']) ? $input['page_size'] : 12;
        //设置查询条件
        $where = [];
        if (isset($input['video_name'])) {
            array_push($where, ['video_name', 'like', '%' . $input['video_name'] . '%']);
        }
        if (isset($input['id'])) {
            array_push($where, ['id', $input['id']]);
        }
        if (isset($input['begin_time'])) {
            array_push($where, ['created_at', '>=', $input['begin_time']]);
        }
        if (isset($input['end_time'])) {
            array_push($where, ['created_at', '<=', $input['end_time']]);
        }
        //查询课程视频数据
        $list = CourseVideo::where($where)->paginate($pageSize);
        //返回课程视频数据
        return $this->success($list);
    }
}
