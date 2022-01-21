<?php

namespace App\Http\Controllers\Api\Course;

use App\Http\Controllers\BaseController;
use App\Models\Course\Course;
use App\Models\Course\CourseChapter;
use App\Models\Course\CourseVideo;
use App\Models\CourseOrder\CourseOrder;
use App\Models\User\User;
use App\Rules\Course\CourseVideoCanBeDeleted;
use App\Rules\Course\PublicVideoCanBeViewed;
use App\Services\Cache\TempAttributesService;
use App\Services\TencentCloudOnDemand\VideoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseVideoController extends BaseController
{
    //增加视频
    public function addVideo(Request $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $request->validate([
            'video_name' => [
                'required',
                'unique:course_videos,video_name,0,id,user_id,' . User::getCurrentUserCache('id')//添加的课程视频的名称不能和操作者所拥有的其他课程视频名称重复
            ]
        ]);
        try {
            //增加课程视频
            $info = CourseVideo::create([
                'user_id' => User::getCurrentUserCache('id'),
                'video_name' => $input['video_name']
            ]);
        } catch (\Exception $e) {
            //返回数据库错误
            return $this->error(__('messages.DatabaseError'));
        }
        //返回添加的课程视频数据
        return $this->success($info);
    }

    //修改视频
    public function editVideo(Request $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $request->validate([
            'id' => [
                'required',
                'exists:course_videos,id,user_id,' . User::getCurrentUserCache('id')//只能修改操作者自己的课程视频
            ],
            'video_name' => [
                'required',
                'unique:course_videos,video_name,' . $input['id'] . ',id,user_id,' . User::getCurrentUserCache('id')//修改的课程视频名称不能和操作者所拥有的其他课程视频名称重复
            ]
        ]);
        try {
            //修改课程视频
            CourseVideo::where('id', $input['id'])->update([
                'video_name' => $input['video_name']
            ]);
        } catch (\Exception $e) {
            //返回数据库错误
            return $this->error(__('messages.DatabaseError'));
        }
        //返回成功
        return $this->success();
    }

    //查询视频
    public function getList(Request $request)
    {
        //获得参数
        $input = $request->all();
        //设置默认分页大小
        $pageSize = isset($input['page_size']) ? $input['page_size'] : 12;
        //设置查询条件
        $where = [
            ['user_id', User::getCurrentUserCache('id')],
            ['tencent_cloud_on_demand_file_id', '!=', '']
        ];
        if (isset($input['video_name'])) {
            array_push($where, ['video_name', 'like', '%' . $input['video_name'] . '%']);
        }
        if (isset($input['id'])) {
            array_push($where, ['id', $input['id']]);
        }
        //查询课程视频数据
        $list = CourseVideo::where($where)->paginate($pageSize);
        //返回课程视频数据
        return $this->success($list);
    }

    //查询公共课程视频
    public function getPublicVideo(Request $request)
    {
        //验证参数
        $request->validate([
            'section_id' => [
                'required'
            ]
        ]);
        //验证参数
        $dataOperator = new TempAttributesService(['sectionInfo']);
        $request->validate([
            'section_id' => [
                new PublicVideoCanBeViewed($dataOperator)
            ]
        ]);
        $sectionInfo = $dataOperator->getSectionInfo();
        $videoInfo = CourseVideo::where('id', $sectionInfo['video_id'])->first();
        return $this->success($videoInfo);
    }

    //删除视频
    public function deleteVideo(Request $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $dataOperator = new TempAttributesService(['videoInfo']);
        $request->validate([
            'id' => [
                'required',
                new CourseVideoCanBeDeleted($dataOperator)
            ]
        ]);
        $videoInfo = $dataOperator->getVideoInfo();
        //开启数据库事务
        DB::beginTransaction();
        try {
            //删除课程视频
            CourseVideo::where('id', $input['id'])->delete();
            //远程删除腾讯云视频
            if (!empty($videoInfo['tencent_cloud_on_demand_file_id'])) VideoService::deleteVideo($videoInfo['tencent_cloud_on_demand_file_id']);
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

    //删除多个视频
    public function deleteVideos(Request $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $request->validate([
            'ids' => [
                'required'
            ]
        ]);
        $ids = array_values(array_unique(explode(',', $input['ids'])));
        $msg = '';
        for ($i = 0; $i < count($ids); $i++) {
            //查询操作者拥有的课程视频数据
            $videoInfo = CourseVideo::where('id', $ids[$i])->where('user_id', User::getCurrentUserCache('id'))->first();
            //只能删除操作者自己的课程视频
            if (empty($videoInfo)) {
                continue;
            }
            //如果视频正在被使用，则不能删除
            if (CourseChapter::join('courses', 'course_chapters.course_id', 'courses.id')->where('video_id', $ids[$i])->where('courses.type', Course::VIDEO)->count() > 0) {
                $msg = $msg . (empty($msg) ? '' : "\r\n") . '视频“' . $videoInfo['video_name'] . '”正在被使用，不能删除。';
                continue;
            }
            //开启数据库事务
            DB::beginTransaction();
            try {
                //删除课程视频
                CourseVideo::where('id', $ids[$i])->delete();
                //远程删除腾讯云视频
                if (!empty($videoInfo['tencent_cloud_on_demand_file_id'])) VideoService::deleteVideo($videoInfo['tencent_cloud_on_demand_file_id']);
                //提交数据库事务
                DB::commit();
            } catch (\Exception $e) {
                //数据库事务回滚
                DB::rollback();
                //提示删除视频的时候，出现的错误
                $msg = $msg . (empty($msg) ? '' : "\r\n") . '视频“' . $videoInfo['video_name'] . '”删除的过程中出现了异常错误，删除失败。';
            }
        }
        //返回成功
        return $this->success($msg);
    }
}
