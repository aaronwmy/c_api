<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\BaseController;
use App\Http\Requests\User\CompanyData;
use App\Models\User\Company;
use App\Models\User\School;
use App\Models\User\Teacher;
use App\Models\User\User;
use App\Services\DatabaseResponse\DatabaseResponseService;
use App\Services\Region\RegionCode;
use Illuminate\Http\Request;

class CompanyController extends BaseController
{
    //获得公司信息
    public function me()
    {
        return $this->success(
            Company::leftJoin(
                'users',
                'companies.user_id',
                'users.id'
            )->leftJoin(
                'regions',
                'companies.region_code',
                'regions.region_code'
            )->where(
                'companies.user_id',
                User::getCurrentUserCache('id')
            )->select('companies.*', 'users.nickname', 'users.portrait', 'regions.region_fullname')->first()
        );
    }

    //增加或修改公司信息
    public function addOrEditCompany(CompanyData $request)
    {
        //获得参数
        $input = $request->all();
        $request->validate([
            'company_name' => [
                'required',
                'unique:companies,company_name,' . User::getCurrentUserCache('id') . ',user_id'
            ],
            'business_license_code' => [
                'required'
            ],
            'business_license_pic' => [
                'required'
            ],
            'fullname' => [
                'required',
                'max:10'
            ],
            'id_card_number' => [
                'required'
            ],
            'address' => [
                'required'
            ],
            'introduction' => [
                'required'
            ],
            'company_cover' => [
                'required'
            ],
            'region_code' => [
                'required'
            ]
        ]);
        if (School::where('user_id', User::getCurrentUserCache('id'))->count() > 0
            || Teacher::where('user_id', User::getCurrentUserCache('id'))->count() > 0
        ) {
            return $this->error(__('messages.YouHaveSubmittedSchoolOrTeacherInformationAndCannotSubmitCompanyInformation'));
        }
        try {
            Company::updateOrCreate(['user_id' => User::getCurrentUserCache('id')], [
                'company_name' => $input['company_name'],
                'business_license_code' => $input['business_license_code'],
                'business_license_pic' => $input['business_license_pic'],
                'fullname' => $input['fullname'],
                'id_card_number' => $input['id_card_number'],
                'address' => $input['address'],
                'introduction' => $input['introduction'],
                'company_cover' => $input['company_cover'],
                'region_code' => $input['region_code'],
                'status' => Company::UNDER_REVIEW
            ]);
        } catch (\Exception $e) {
            return $this->error(__('messages.DatabaseError'));
        }
        return $this->success();
    }

    //获得公共的公司数据
    public function getPublicList(Request $request)
    {
        //获得参数
        $input = $request->all();
        //设置默认分页大小
        $pageSize = isset($input['page_size']) ? $input['page_size'] : 10;
        //设置查询条件
        $where = [
            ['companies.status', Company::APPROVED]
        ];
        if (isset($input['region_code'])) {
            $where = RegionCode::getSelectWhereFromCode($where, 'companies.region_code', $input['region_code']);
        }
        if (isset($input['company_name'])) {
            array_push($where, ['companies.company_name', 'like', '%' . $input['company_name'] . '%']);
        }
        if (isset($input['user_id'])) {
            array_push($where, ['companies.user_id', $input['user_id']]);
        }
        //设置排序条件
        $orderArr = ['companies.created_at' => 'desc'];
        if (isset($input['order'])) {
            if ($input['order'] == 'newest') {
                $orderArr = ['companies.updated_at' => 'desc'];
            }
        }
        //查询公司数据
        $list = Company::leftJoin(
            'users',
            'companies.user_id',
            'users.id'
        )->where($where)->ordersBy($orderArr)->select(
            'companies.*',
            'users.portrait',
            'users.nickname',
            'users.follow_user_count',
            'users.approved_course_count'
        )->paginate($pageSize);
        //检查是否关注过该公司
        if (isset($input['check_if_followed']) && $input['check_if_followed'] == 1) {
            DatabaseResponseService::preSetIsFollowed('user_id')->exec($list);
        }
        //返回公司数据
        return $this->success($list);
    }
}
