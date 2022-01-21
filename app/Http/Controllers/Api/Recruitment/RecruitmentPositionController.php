<?php

namespace App\Http\Controllers\Api\Recruitment;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Recruitment\Extend\RecruitmentPositionForManagePosition;
use App\Models\Collection\Collection;
use App\Models\Recruitment\RecruitmentPosition;
use App\Models\Recruitment\RecruitmentPositionRequest;
use App\Models\User\Company;
use App\Models\User\User;
use App\Services\DatabaseResponse\DatabaseResponseService;
use App\Services\Region\RegionCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RecruitmentPositionController extends BaseController
{
    //增加招聘职位
    public function addPosition(\App\Http\Requests\Recruitment\RecruitmentPosition $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $request->validate([
            'position_name' => [
                'required'
            ],
            'position_type_id' => [
                'required'
            ],
            'min_salary' => [
                'required'
            ],
            'max_salary' => [
                'required'
            ],
            'description' => [
                'required'
            ],
            'education_id' => [
                'required'
            ],
            'experience_id' => [
                'required'
            ],
            'region_code' => [
                'required'
            ],
            'address' => [
                'required'
            ],
            'mobile' => [
                'required'
            ]
        ]);
        //开启数据库事务
        DB::beginTransaction();
        try {
            //增加招聘职位
            RecruitmentPosition::create([
                'user_id' => User::getCurrentUserCache('id'),
                'position_name' => $input['position_name'],
                'position_type_id' => $input['position_type_id'],
                'min_salary' => $input['min_salary'],
                'max_salary' => $input['max_salary'],
                'benefits' => isset($input['benefits']) ? $input['benefits'] : '',
                'description' => $input['description'],
                'education_id' => $input['education_id'],
                'experience_id' => $input['experience_id'],
                'region_code' => $input['region_code'],
                'address' => $input['address'],
                'mobile' => $input['mobile']
            ]);
            //更新公司的职位数量
            Company::updatePositionCount(User::getCurrentUserCache('id'));
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

    //修改招聘职位
    public function editPosition(RecruitmentPositionForManagePosition $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $request->validate([
            'id' => [
                'required'
            ],
            'position_name' => [
                'required'
            ],
            'position_type_id' => [
                'required'
            ],
            'min_salary' => [
                'required'
            ],
            'max_salary' => [
                'required'
            ],
            'description' => [
                'required'
            ],
            'education_id' => [
                'required'
            ],
            'experience_id' => [
                'required'
            ],
            'region_code' => [
                'required'
            ],
            'address' => [
                'required'
            ],
            'mobile' => [
                'required'
            ]
        ]);
        try {
            //修改招聘职位
            RecruitmentPosition::where('id', $input['id'])->update([
                'position_name' => $input['position_name'],
                'position_type_id' => $input['position_type_id'],
                'min_salary' => $input['min_salary'],
                'max_salary' => $input['max_salary'],
                'benefits' => isset($input['benefits']) ? $input['benefits'] : '',
                'description' => $input['description'],
                'education_id' => $input['education_id'],
                'experience_id' => $input['experience_id'],
                'region_code' => $input['region_code'],
                'address' => $input['address'],
                'mobile' => $input['mobile']
            ]);
        } catch (\Exception $e) {
            //返回数据库错误
            return $this->error(__('messages.DatabaseError'));
        }
        //返回成功
        return $this->success();
    }

    //查询招聘职位
    public function getList(Request $request)
    {
        //获得参数
        $input = $request->all();
        //设置默认分页大小
        $pageSize = isset($input['page_size']) ? $input['page_size'] : 12;
        //设置查询条件
        $where = [
            ['recruitment_positions.user_id', User::getCurrentUserCache('id')]
        ];
        if (isset($input['status'])) {
            array_push($where, ['recruitment_positions.status', $input['status']]);
        }
        if (isset($input['id'])) {
            array_push($where, ['recruitment_positions.id', $input['id']]);
        }
        if (isset($input['position_name'])) {
            array_push($where, ['recruitment_positions.position_name', 'like', '%' . $input['position_name'] . '%']);
        }
        //查询招聘职位数据
        $list = RecruitmentPosition::leftJoin(
            'recruitment_experiences',
            'recruitment_positions.experience_id',
            'recruitment_experiences.id'
        )->leftJoin(
            'recruitment_education',
            'recruitment_positions.education_id',
            'recruitment_education.id'
        )->leftJoin(
            'regions',
            'recruitment_positions.region_code',
            'regions.region_code'
        )->leftJoin(
            'recruitment_position_types',
            'recruitment_positions.position_type_id',
            'recruitment_position_types.id'
        )->where($where)->select(
            'recruitment_positions.*',
            'recruitment_experiences.experience_name',
            'recruitment_education.education_name',
            'regions.region_fullname',
            'recruitment_position_types.type_name'
        )->orderBy('recruitment_positions.id', 'desc')->paginate($pageSize);
        //返回招聘职位数据
        return $this->success($list);
    }

    //修改招聘职位状态
    public function editPositionStatus(RecruitmentPositionForManagePosition $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $request->validate([
            'id' => [
                'required'
            ],
            'status' => [
                'required'
            ]
        ]);
        //开启数据库事务
        DB::beginTransaction();
        try {
            //修改招聘职位状态
            RecruitmentPosition::where('id', $input['id'])->update([
                'status' => $input['status']
            ]);
            //更新公司的职位数量
            Company::updatePositionCount(User::getCurrentUserCache('id'));
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

    //删除招聘职位
    public function deletePosition(RecruitmentPositionForManagePosition $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $request->validate([
            'id' => [
                'required'
            ]
        ]);
        //开启数据库事务
        DB::beginTransaction();
        try {
            //删除招聘职位
            RecruitmentPosition::where('id', $input['id'])->delete();
            //更新公司的职位数量
            Company::updatePositionCount(User::getCurrentUserCache('id'));
            //删除操作者可能存在的职位收藏
            Collection::where(
                'user_id',
                User::getCurrentUserCache('id')
            )->where('type', Collection::POSITION)->where('other_id', $input['id'])->delete();
            //更新所有收藏了该职位的用户的收藏职位的数量
            User::updateCollectionCountOfAllCollectorsOfIt($input['id'], Collection::POSITION);
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

    //查询公共的招聘职位
    public function getPublicList(Request $request)
    {
        //获得参数
        $input = $request->all();
        //设置默认分页大小
        $pageSize = isset($input['page_size']) ? $input['page_size'] : 12;
        //设置查询条件
        $where = [
            ['recruitment_positions.status', RecruitmentPosition::ON_THE_SHELF]
        ];
        if (isset($input['id'])) {
            array_push($where, ['recruitment_positions.id', $input['id']]);
        }
        if (isset($input['experience_id'])) {
            array_push($where, ['recruitment_positions.experience_id', $input['experience_id']]);
        }
        if (isset($input['education_id'])) {
            array_push($where, ['recruitment_positions.education_id', $input['education_id']]);
        }
        if (isset($input['region_code'])) {
            $where = RegionCode::getSelectWhereFromCode($where, 'recruitment_positions.region_code', $input['region_code']);
        }
        if (isset($input['min_salary'])) {
            array_push($where, ['recruitment_positions.max_salary', '>=', $input['min_salary']]);
        }
        if (isset($input['max_salary'])) {
            array_push($where, ['recruitment_positions.min_salary', '<=', $input['max_salary']]);
        }
        if (isset($input['user_id'])) {
            array_push($where, ['recruitment_positions.user_id', $input['user_id']]);
        }
        if (isset($input['benefits'])) {
            $benefits = explode(',', $input['benefits']);
            for ($i = 0; $i < count($benefits); $i++) {
                $benefit = $benefits[$i];
                $where[] = [function ($query) use ($benefit) {
                    $query->Where(
                        'benefits',
                        $benefit
                    )->orWhere(
                        'benefits',
                        'like',
                        $benefit . ',%'
                    )->orWhere(
                        'benefits',
                        'like',
                        '%,' . $benefit
                    )->orWhere(
                        'benefits',
                        'like',
                        '%,' . $benefit . ',%'
                    );
                }];
            }
        }
        if (isset($input['keyword'])) {
            $where[] = [function ($query) use ($input) {
                $query->Where(
                    'recruitment_positions.position_name',
                    'like',
                    '%' . $input['keyword'] . '%'
                )->orWhere(
                    'recruitment_positions.description',
                    'like',
                    '%' . $input['keyword'] . '%'
                )->orWhere(
                    'recruitment_positions.address',
                    'like',
                    '%' . $input['keyword'] . '%'
                );
            }];
        }
        //查询招聘职位数据
        $list = RecruitmentPosition::leftJoin(
            'recruitment_experiences',
            'recruitment_positions.experience_id',
            'recruitment_experiences.id'
        )->leftJoin(
            'recruitment_education',
            'recruitment_positions.education_id',
            'recruitment_education.id'
        )->leftJoin(
            'companies',
            'recruitment_positions.user_id',
            'companies.user_id'
        )->leftJoin(
            'regions',
            'recruitment_positions.region_code',
            'regions.region_code'
        )->leftJoin(
            'users',
            'recruitment_positions.user_id',
            'users.id'
        )->leftJoin(
            'recruitment_position_types',
            'recruitment_positions.position_type_id',
            'recruitment_position_types.id'
        )->where($where)->select(
            'recruitment_positions.*',
            'recruitment_experiences.experience_name',
            'recruitment_education.education_name',
            'companies.company_name',
            'companies.address as company_address',
            'companies.introduction',
            'companies.company_cover',
            'regions.region_fullname',
            'users.nickname',
            'users.portrait',
            'recruitment_position_types.type_name'
        )->orderBy('recruitment_positions.updated_at', 'desc')->paginate($pageSize);
        //查询职位是否被操作者申请过
        if (isset($input['check_if_already_requested']) && $input['check_if_already_requested'] == 1) {
            DatabaseResponseService::preSetAlreadyRequested('id');
        }
        //查询职位是否被操作者收藏过
        if (isset($input['check_if_already_collected']) && $input['check_if_already_collected'] == 1) {
            DatabaseResponseService::preSetAlreadyCollected(Collection::POSITION, 'id');
        }
        DatabaseResponseService::exec($list);
        //返回招聘职位数据
        return $this->success($list);
    }

    //查询收藏的招聘职位
    public function getCollectionList(Request $request)
    {
        //获得参数
        $input = $request->all();
        //设置默认分页大小
        $pageSize = isset($input['page_size']) ? $input['page_size'] : 12;
        //设置查询条件
        $where = [
            ['collections.user_id', User::getCurrentUserCache('id')],
            ['collections.type', Collection::POSITION]
        ];
        //查询招聘职位数据
        $list = Collection::join(
            'recruitment_positions',
            'collections.other_id',
            'recruitment_positions.id'
        )->leftJoin(
            'recruitment_experiences',
            'recruitment_positions.experience_id',
            'recruitment_experiences.id'
        )->leftJoin(
            'recruitment_education',
            'recruitment_positions.education_id',
            'recruitment_education.id'
        )->leftJoin(
            'companies',
            'recruitment_positions.user_id',
            'companies.user_id'
        )->leftJoin(
            'regions',
            'recruitment_positions.region_code',
            'regions.region_code'
        )->leftJoin(
            'users',
            'recruitment_positions.user_id',
            'users.id'
        )->leftJoin(
            'recruitment_position_types',
            'recruitment_positions.position_type_id',
            'recruitment_position_types.id'
        )->where($where)->select(
            'recruitment_positions.*',
            'recruitment_experiences.experience_name',
            'recruitment_education.education_name',
            'companies.company_name',
            'companies.address as company_address',
            'regions.region_fullname',
            'users.nickname',
            'users.portrait',
            'recruitment_position_types.type_name'
        )->orderBy('collections.created_at', 'desc')->paginate($pageSize);
        //返回招聘职位数据
        return $this->success($list);
    }
}
