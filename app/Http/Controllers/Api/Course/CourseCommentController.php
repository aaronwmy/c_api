<?php

namespace App\Http\Controllers\Api\Course;

use App\Http\Controllers\BaseController;
use App\Models\Course\CourseComment;
use App\Models\User\User;
use App\Http\Requests\Course\CourseComment as CourseCommentRequest;
use Illuminate\Http\Request;

class CourseCommentController extends BaseController
{
    //增加评论
    public function addComment(CourseCommentRequest $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $request->validate([
            'section_id' => [
                'required_without:comment_replied_id'
            ],
            'comment_replied_id' => [
                'required_without:section_id'
            ],
            'content' => [
                'required'
            ]
        ]);
        if (isset($input['comment_replied_id'])) {
            //获得被回复的评论数据
            $commentInfo = $request->getDataOperator()->getCommentInfo();
            $courseId = $commentInfo['course_id'];
            $sectionId = $commentInfo['section_id'];
            $toUserId = $commentInfo['user_id'];
            $commentRepliedId = $commentInfo['id'];
            $mainCommentRepliedId = $commentInfo['main_comment_replied_id'] > 0 ? $commentInfo['main_comment_replied_id'] : $commentInfo['id'];
        } else {
            //获得课节对应的课程数据
            $courseInfo = $request->getDataOperator()->getCourseInfo();
            $courseId = $courseInfo['id'];
            $sectionId = $input['section_id'];
            $toUserId = 0;
            $commentRepliedId = 0;
            $mainCommentRepliedId = 0;
        }
        try {
            //增加评论
            CourseComment::create([
                'course_id' => $courseId,
                'section_id' => $sectionId,
                'user_id' => User::getCurrentUserCache('id'),
                'to_user_id' => $toUserId,
                'content' => $input['content'],
                'comment_replied_id' => $commentRepliedId,
                'main_comment_replied_id' => $mainCommentRepliedId
            ]);
        } catch (\Exception $e) {
            //返回数据库错误
            return $this->error(__('messages.DatabaseError'));
        }
        //返回成功
        return $this->success();
    }

    //查询评论
    public function getComment(Request $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $request->validate([
            'section_id' => [
                'required'
            ]
        ]);
        //设置默认分页大小
        $pageSize = isset($input['page_size']) ? $input['page_size'] : 12;
        //设置查询条件
        $where = [
            'main_comment_replied_id' => 0
        ];
        if (isset($input['section_id'])) {
            array_push($where, ['section_id', $input['section_id']]);
        }
        //查询评论数据
        $list = CourseComment::leftJoin(
            'users',
            'course_comments.user_id',
            'users.id'
        )->where($where)->select(
            'course_comments.*',
            'users.nickname',
            'users.portrait'
        )->orderby('course_comments.id', 'desc')->paginate($pageSize);
        $list->getCollection()->transform(function ($info, $key) {
            $info['comments_reply'] = CourseComment::leftJoin(
                'users',
                'course_comments.user_id',
                'users.id'
            )->leftJoin(
                'users as to_users',
                'course_comments.to_user_id',
                'to_users.id'
            )->where('main_comment_replied_id', $info['id'])->select(
                'course_comments.*',
                'users.nickname',
                'users.portrait',
                'to_users.nickname as to_user_nickname'
            )->orderby('course_comments.id', 'desc')->get();
            return $info;
        });
        //返回评论数据
        return $this->success($list);
    }
}
