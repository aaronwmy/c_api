<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\BaseController;
use App\Http\Requests\User\SchoolData;
use App\Models\User\Company;
use App\Models\User\School;
use App\Models\User\Teacher;
use App\Models\User\User;
use App\Services\DatabaseResponse\DatabaseResponseService;
use App\Services\Region\RegionCode;
use Illuminate\Http\Request;

class SchoolController extends BaseController
{
    //获得学校信息
    public function me()
    {
        return $this->success(
            School::leftJoin(
                'users',
                'schools.user_id',
                'users.id'
            )->leftJoin(
                'regions',
                'schools.region_code',
                'regions.region_code'
            )->where(
                'schools.user_id',
                User::getCurrentUserCache('id')
            )->select('schools.*', 'users.nickname', 'users.portrait', 'regions.region_fullname')->first()
        );
    }

    //增加或修改学校信息
    public function addOrEditSchool(SchoolData $request)
    {
        //获得参数
        $input = $request->all();
        $request->validate([
            'school_name' => [
                'required',
                'unique:schools,school_name,' . User::getCurrentUserCache('id') . ',user_id'
            ],
            'enterprise_legal_person_certificate_code' => [
                'required'
            ],
            'enterprise_legal_person_certificate_pic' => [
                'required'
            ],
            'fullname' => [
                'required',
                'max:10'
            ],
            'address' => [
                'required'
            ],
            'introduction' => [
                'required'
            ],
            'school_cover' => [
                'required'
            ],
            'region_code' => [
                'required'
            ]
        ]);
        if (Company::where('user_id', User::getCurrentUserCache('id'))->count() > 0
            || Teacher::where('user_id', User::getCurrentUserCache('id'))->count() > 0
        ) {
            return $this->error(__('messages.YouHaveSubmittedTeacherOrCompanyInformationAndCannotSubmitSchoolInformation'));
        }
        try {
            School::updateOrCreate(['user_id' => User::getCurrentUserCache('id')], [
                'school_name' => $input['school_name'],
                'enterprise_legal_person_certificate_code' => $input['enterprise_legal_person_certificate_code'],
                'enterprise_legal_person_certificate_pic' => $input['enterprise_legal_person_certificate_pic'],
                'fullname' => $input['fullname'],
                'address' => $input['address'],
                'introduction' => $input['introduction'],
                'school_cover' => $input['school_cover'],
                'region_code' => $input['region_code'],
                'status' => School::UNDER_REVIEW
            ]);
        } catch (\Exception $e) {
            return $this->error(__('messages.DatabaseError'));
        }
        return $this->success();
    }

    //获得公共的学校数据
    public function getPublicList(Request $request)
    {
        //获得参数
        $input = $request->all();
        //设置默认分页大小
        $pageSize = isset($input['page_size']) ? $input['page_size'] : 10;
        //设置查询条件
        $where = [
            ['schools.status', School::APPROVED]
        ];
        if (isset($input['school_name'])) {
            array_push($where, ['schools.school_name', 'like', '%' . $input['school_name'] . '%']);
        }
        if (isset($input['region_code'])) {
            $where = RegionCode::getSelectWhereFromCode($where, 'schools.region_code', $input['region_code']);
        }
        if (isset($input['user_id'])) {
            array_push($where, ['schools.user_id', $input['user_id']]);
        }
        //设置排序条件
        $orderArr = ['schools.created_at' => 'desc'];
        if (isset($input['order'])) {
            if ($input['order'] == 'newest') {
                $orderArr = ['schools.updated_at' => 'desc'];
            }
        }
        //查询学校数据
        $list = School::leftJoin(
            'users',
            'schools.user_id',
            'users.id'
        )->where($where)->ordersBy($orderArr)->select(
            'schools.*',
            'users.portrait',
            'users.nickname',
            'users.follow_user_count',
            'users.approved_course_count'
        )->paginate($pageSize);
        //检查是否关注过该学校
        if (isset($input['check_if_followed']) && $input['check_if_followed'] == 1) {
            DatabaseResponseService::preSetIsFollowed('user_id')->exec($list);
        }
        //返回学校数据
        return $this->success($list);
    }
}
