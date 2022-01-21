<?php

namespace App\Http\Controllers\Api\Recruitment;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Recruitment\Extend\RecruitmentResumeForManageResume;
use App\Http\Requests\Recruitment\RecruitmentResume as RecruitmentResumeRequest;
use App\Models\Collection\Collection;
use App\Models\Recruitment\RecruitmentPositionRequest;
use App\Models\Recruitment\RecruitmentResume;
use App\Models\Recruitment\RecruitmentResumeEducationalExperience;
use App\Models\Recruitment\RecruitmentResumeExpectedJob;
use App\Models\Recruitment\RecruitmentResumeJobExperience;
use App\Models\User\User;
use App\Rules\Recruitment\RecruitmentResumeReceivedCanBeDeleted;
use App\Services\Cache\TempAttributesService;
use App\Services\DatabaseResponse\DatabaseResponseService;
use App\Services\Region\RegionCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecruitmentResumeController extends BaseController
{
    //增加或修改招聘简历的个人信息
    public function addOrEditResumePersonalInformation(RecruitmentResumeRequest $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $request->validate([
            'portrait' => [
                'required'
            ],
            'fullname' => [
                'required'
            ],
            'sex' => [
                'required'
            ],
            'birth_year_month' => [
                'required'
            ],
            'first_job_year_month' => [
                'required'
            ],
            'education_id' => [
                'required'
            ],
            'region_code' => [
                'required'
            ],
            'mobile' => [
                'required'
            ],
            'email' => [
                'required'
            ]
        ]);
        try {
            //增加或修改招聘简历的个人信息
            $info = RecruitmentResume::updateOrCreate(['user_id' => User::getCurrentUserCache('id')],
                [
                    'portrait' => $input['portrait'],
                    'fullname' => $input['fullname'],
                    'sex' => $input['sex'],
                    'birth_year_month' => $input['birth_year_month'],
                    'first_job_year_month' => $input['first_job_year_month'],
                    'education_id' => $input['education_id'],
                    'region_code' => $input['region_code'],
                    'mobile' => $input['mobile'],
                    'email' => $input['email']
                ]
            );
        } catch (\Exception $e) {
            //返回数据库错误
            return $this->error(__('messages.DatabaseError'));
        }
        //返回成功
        return $this->success($info);
    }

    //修改招聘简历的自我评价
    public function editResumeSelfEvaluation(RecruitmentResumeForManageResume $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $request->validate([
            'id' => [
                'required'
            ],
            'self_evaluation' => [
                'required'
            ]
        ]);
        try {
            //修改招聘简历的自我评价
            RecruitmentResume::where('id', $input['id'])->update(
                [
                    'self_evaluation' => $input['self_evaluation']
                ]
            );
        } catch (\Exception $e) {
            //返回数据库错误
            return $this->error(__('messages.DatabaseError'));
        }
        //返回成功
        return $this->success();
    }

    //查询单个简历的数据
    public function getResumeInfo(Request $request)
    {
        //获得参数
        $input = $request->all();
        $resumeInfo = RecruitmentResume::leftJoin(
            'regions',
            'recruitment_resumes.region_code',
            'regions.region_code'
        )->leftJoin(
            'recruitment_education',
            'recruitment_resumes.education_id',
            'recruitment_education.id'
        )->leftJoin(
            'recruitment_resume_expected_jobs',
            'recruitment_resumes.important_expected_job_id',
            'recruitment_resume_expected_jobs.id'
        )->leftJoin(
            'recruitment_position_types',
            'recruitment_resume_expected_jobs.position_type_id',
            'recruitment_position_types.id'
        )->where(
            'recruitment_resumes.user_id',
            User::getCurrentUserCache('id')
        )->select(
            'recruitment_resumes.*',
            'regions.region_fullname',
            'recruitment_education.education_name',
            'recruitment_position_types.type_name'
        )->first();
        if (!isset($input['only_personal_information']) || $input['only_personal_information'] != 1) {
            if (!empty($resumeInfo)) {
                $resumeInfo['expected_job'] = RecruitmentResumeExpectedJob::leftJoin(
                    'regions',
                    'recruitment_resume_expected_jobs.region_code',
                    'regions.region_code'
                )->leftJoin(
                    'recruitment_position_types',
                    'recruitment_resume_expected_jobs.position_type_id',
                    'recruitment_position_types.id'
                )->where(
                    'recruitment_resume_expected_jobs.resume_id',
                    $resumeInfo['id']
                )->select(
                    'recruitment_resume_expected_jobs.*',
                    'recruitment_position_types.type_name',
                    'regions.region_fullname'
                )->orderBy('recruitment_resume_expected_jobs.id', 'asc')->get();
                $resumeInfo['educational_experience'] = RecruitmentResumeEducationalExperience::leftJoin(
                    'recruitment_education',
                    'recruitment_resume_educational_experiences.education_id',
                    'recruitment_education.id'
                )->where(
                    'recruitment_resume_educational_experiences.resume_id',
                    $resumeInfo['id']
                )->select('recruitment_resume_educational_experiences.*', 'recruitment_education.education_name')->get();
                $resumeInfo['job_experience'] = RecruitmentResumeJobExperience::leftJoin(
                    'recruitment_position_types',
                    'recruitment_resume_job_experiences.position_type_id',
                    'recruitment_position_types.id'
                )->where(
                    'recruitment_resume_job_experiences.resume_id',
                    $resumeInfo['id']
                )->select('recruitment_resume_job_experiences.*', 'recruitment_position_types.type_name')->get();
            }
        }
        //编辑简历的返回值
        $resumeInfo = RecruitmentResume::infoModifyReturnValue($resumeInfo, false);
        //返回简历的数据
        return $this->success($resumeInfo);
    }

    //查询单个公共的简历的数据
    public function getPublicResumeInfo(Request $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $request->validate([
            'id' => [
                'required',
                'exists:recruitment_resumes,id'
            ]
        ]);
        $resumeInfo = RecruitmentResume::leftJoin(
            'regions',
            'recruitment_resumes.region_code',
            'regions.region_code'
        )->leftJoin(
            'recruitment_education',
            'recruitment_resumes.education_id',
            'recruitment_education.id'
        )->leftJoin(
            'recruitment_resume_expected_jobs',
            'recruitment_resumes.important_expected_job_id',
            'recruitment_resume_expected_jobs.id'
        )->leftJoin(
            'recruitment_position_types',
            'recruitment_resume_expected_jobs.position_type_id',
            'recruitment_position_types.id'
        )->where(
            'recruitment_resumes.id',
            $input['id']
        )->select(
            'recruitment_resumes.*',
            'regions.region_fullname',
            'recruitment_education.education_name',
            'recruitment_position_types.type_name'
        )->first();
        if (!empty($resumeInfo)) {
            $resumeInfo['expected_job'] = RecruitmentResumeExpectedJob::leftJoin(
                'regions',
                'recruitment_resume_expected_jobs.region_code',
                'regions.region_code'
            )->leftJoin(
                'recruitment_position_types',
                'recruitment_resume_expected_jobs.position_type_id',
                'recruitment_position_types.id'
            )->where(
                'recruitment_resume_expected_jobs.resume_id',
                $resumeInfo['id']
            )->select(
                'recruitment_resume_expected_jobs.*',
                'recruitment_position_types.type_name',
                'regions.region_fullname'
            )->orderBy('recruitment_resume_expected_jobs.id', 'asc')->get();
            $resumeInfo['educational_experience'] = RecruitmentResumeEducationalExperience::leftJoin(
                'recruitment_education',
                'recruitment_resume_educational_experiences.education_id',
                'recruitment_education.id'
            )->where(
                'recruitment_resume_educational_experiences.resume_id',
                $resumeInfo['id']
            )->select('recruitment_resume_educational_experiences.*', 'recruitment_education.education_name')->get();
            $resumeInfo['job_experience'] = RecruitmentResumeJobExperience::leftJoin(
                'recruitment_position_types',
                'recruitment_resume_job_experiences.position_type_id',
                'recruitment_position_types.id'
            )->where(
                'recruitment_resume_job_experiences.resume_id',
                $resumeInfo['id']
            )->select('recruitment_resume_job_experiences.*', 'recruitment_position_types.type_name')->get();
        }
        //编辑简历的返回值
        $resumeInfo = RecruitmentResume::infoModifyReturnValue($resumeInfo);
        //查询简历是否被操作者收藏过
        if (isset($input['check_if_already_collected']) && $input['check_if_already_collected'] == 1) {
            DatabaseResponseService::preSetAlreadyCollected(Collection::RESUME, 'id')->exec($resumeInfo, DatabaseResponseService::INFO);
        }
        //返回简历的数据
        return $this->success($resumeInfo);
    }

    //查询公共的简历的数据
    public function getPublicResumeList(Request $request)
    {
        //获得参数
        $input = $request->all();
        //设置默认分页大小
        $pageSize = isset($input['page_size']) ? $input['page_size'] : 12;
        //设置查询条件
        $where = [];
        if (isset($input['region_code'])) {
            $where = RegionCode::getSelectWhereFromCode($where, 'recruitment_resumes.region_code', $input['region_code']);
        }
        if (isset($input['sex'])) {
            array_push($where, ['recruitment_resumes.sex', $input['sex']]);
        }
        if (isset($input['education_id'])) {
            array_push($where, ['recruitment_resumes.education_id', $input['education_id']]);
        }
        if (isset($input['min_working_years'])) {
            array_push($where, [
                'recruitment_resumes.first_job_year_month',
                '<=',
                (int)date('Ym', time() - $input['min_working_years'] * 365 * 24 * 60 * 60)
            ]);
        }
        if (isset($input['max_working_years'])) {
            array_push($where, [
                'recruitment_resumes.first_job_year_month',
                '>=',
                (int)date('Ym', time() - $input['max_working_years'] * 365 * 24 * 60 * 60)
            ]);
        }
        if (isset($input['keyword'])) {
            $where[] = [function ($query) use ($input) {
                $query->Where(
                    'recruitment_resumes.fullname',
                    'like',
                    '%' . $input['keyword'] . '%'
                )->orWhere(
                    'recruitment_resumes.self_evaluation',
                    'like',
                    '%' . $input['keyword'] . '%'
                );
            }];
        }
        //查询公共的简历的数据
        $list = RecruitmentResume::leftJoin(
            'recruitment_resume_expected_jobs',
            'recruitment_resumes.important_expected_job_id',
            'recruitment_resume_expected_jobs.id'
        )->leftJoin(
            'recruitment_position_types',
            'recruitment_resume_expected_jobs.position_type_id',
            'recruitment_position_types.id'
        )->leftJoin(
            'regions',
            'recruitment_resumes.region_code',
            'regions.region_code'
        )->leftJoin(
            'recruitment_education',
            'recruitment_resumes.education_id',
            'recruitment_education.id'
        )->whereNotIn(
            'recruitment_resumes.user_id',
            RecruitmentResume::getShieldResumeUserIds()
        )->where($where)->select(
            'recruitment_resumes.*',
            'recruitment_position_types.type_name',
            'recruitment_education.education_name',
            'regions.region_fullname'
        )->orderBy('recruitment_resumes.updated_at', 'desc')->paginate($pageSize);
        //编辑简历的返回值
        $list = RecruitmentResume::listModifyReturnValue($list);
        //返回公共的简历的数据
        return $this->success($list);
    }

    //查询收到的简历的数据
    public function getResumeListReceived(Request $request)
    {
        //获得参数
        $input = $request->all();
        //设置默认分页大小
        $pageSize = isset($input['page_size']) ? $input['page_size'] : 12;
        //设置查询条件
        $where = [
            ['recruitment_positions.user_id', User::getCurrentUserCache('id')],
            ['recruitment_position_requests.status', RecruitmentPositionRequest::DELIVERED]
        ];
        if (isset($input['region_code'])) {
            $where = RegionCode::getSelectWhereFromCode($where, 'recruitment_resumes.region_code', $input['region_code']);
        }
        if (isset($input['position_id'])) {
            array_push($where, ['recruitment_position_requests.position_id', $input['position_id']]);
        }
        if (isset($input['sex'])) {
            array_push($where, ['recruitment_resumes.sex', $input['sex']]);
        }
        if (isset($input['education_id'])) {
            array_push($where, ['recruitment_resumes.education_id', $input['education_id']]);
        }
        if (isset($input['min_working_years'])) {
            array_push($where, [
                'recruitment_resumes.first_job_year_month',
                '<=',
                (int)date('Ym', time() - $input['min_working_years'] * 365 * 24 * 60 * 60)
            ]);
        }
        if (isset($input['max_working_years'])) {
            array_push($where, [
                'recruitment_resumes.first_job_year_month',
                '>=',
                (int)date('Ym', time() - $input['max_working_years'] * 365 * 24 * 60 * 60)
            ]);
        }
        if (isset($input['keyword'])) {
            $where[] = [function ($query) use ($input) {
                $query->Where(
                    'recruitment_resumes.fullname',
                    'like',
                    '%' . $input['keyword'] . '%'
                )->orWhere(
                    'recruitment_resumes.self_evaluation',
                    'like',
                    '%' . $input['keyword'] . '%'
                );
            }];
        }
        $list = RecruitmentPositionRequest::leftJoin(
            'recruitment_positions',
            'recruitment_position_requests.position_id',
            'recruitment_positions.id'
        )->leftJoin(
            'recruitment_resumes',
            'recruitment_position_requests.resume_id',
            'recruitment_resumes.id'
        )->leftJoin(
            'regions',
            'recruitment_resumes.region_code',
            'regions.region_code'
        )->leftJoin(
            'recruitment_education',
            'recruitment_resumes.education_id',
            'recruitment_education.id'
        )->leftJoin(
            'recruitment_resume_expected_jobs',
            'recruitment_resumes.important_expected_job_id',
            'recruitment_resume_expected_jobs.id'
        )->leftJoin(
            'recruitment_position_types',
            'recruitment_resume_expected_jobs.position_type_id',
            'recruitment_position_types.id'
        )->where($where)->select(
            'recruitment_resumes.*',
            'recruitment_positions.position_name',
            'recruitment_education.education_name',
            'regions.region_fullname',
            'recruitment_position_types.type_name'
        )->orderBy('recruitment_resumes.updated_at', 'desc')->paginate($pageSize);
        //编辑简历的返回值
        $list = RecruitmentResume::listModifyReturnValue($list);
        //返回收到的简历的数据
        return $this->success($list);
    }

    //删除收到的简历
    public function deleteResumeReceived(Request $request)
    {
        //验证参数
        $dataOperator = new TempAttributesService(['resumeInfo']);
        $request->validate([
            'resume_id' => [
                'required',
                new RecruitmentResumeReceivedCanBeDeleted($dataOperator)
            ]
        ]);
        //获得收到的简历
        $resumeInfo = $dataOperator->getResumeInfo();
        try {
            //修改申请的招聘职位的状态为不关注
            RecruitmentPositionRequest::where(
                'id',
                $resumeInfo['id']
            )->update(['status' => RecruitmentPositionRequest::NOT_BEING_NOTICED]);
        } catch (\Exception $e) {
            //返回数据库错误
            return $this->error(__('messages.DatabaseError'));
        }
        //返回成功
        return $this->success();
    }

    //查询收藏的招聘简历
    public function getResumeCollectionList(Request $request)
    {
        //获得参数
        $input = $request->all();
        //设置默认分页大小
        $pageSize = isset($input['page_size']) ? $input['page_size'] : 12;
        //设置查询条件
        $where = [
            ['collections.user_id', User::getCurrentUserCache('id')],
            ['collections.type', Collection::RESUME]
        ];
        if (isset($input['region_code'])) {
            $where = RegionCode::getSelectWhereFromCode($where, 'recruitment_resumes.region_code', $input['region_code']);
        }
        if (isset($input['sex'])) {
            array_push($where, ['recruitment_resumes.sex', $input['sex']]);
        }
        if (isset($input['education_id'])) {
            array_push($where, ['recruitment_resumes.education_id', $input['education_id']]);
        }
        if (isset($input['min_working_years'])) {
            array_push($where, [
                'recruitment_resumes.first_job_year_month',
                '<=',
                (int)date('Ym', time() - $input['min_working_years'] * 365 * 24 * 60 * 60)
            ]);
        }
        if (isset($input['max_working_years'])) {
            array_push($where, [
                'recruitment_resumes.first_job_year_month',
                '>=',
                (int)date('Ym', time() - $input['max_working_years'] * 365 * 24 * 60 * 60)
            ]);
        }
        if (isset($input['position_type_id'])) {
            array_push($where, ['recruitment_resume_expected_jobs.position_type_id', $input['position_type_id']]);
        }
        if (isset($input['keyword'])) {
            $where[] = [function ($query) use ($input) {
                $query->Where(
                    'recruitment_resumes.fullname',
                    'like',
                    '%' . $input['keyword'] . '%'
                )->orWhere(
                    'recruitment_resumes.self_evaluation',
                    'like',
                    '%' . $input['keyword'] . '%'
                );
            }];
        }
        $list = Collection::leftJoin(
            'recruitment_resumes',
            'collections.other_id',
            'recruitment_resumes.id'
        )->leftJoin(
            'recruitment_resume_expected_jobs',
            'recruitment_resumes.important_expected_job_id',
            'recruitment_resume_expected_jobs.id'
        )->leftJoin(
            'recruitment_position_types',
            'recruitment_resume_expected_jobs.position_type_id',
            'recruitment_position_types.id'
        )->leftJoin(
            'regions',
            'recruitment_resumes.region_code',
            'regions.region_code'
        )->leftJoin(
            'recruitment_education',
            'recruitment_resumes.education_id',
            'recruitment_education.id'
        )->where($where)->select(
            'recruitment_resumes.*',
            'recruitment_position_types.type_name',
            'recruitment_education.education_name',
            'regions.region_fullname'
        )->orderBy('collections.created_at', 'desc')->paginate($pageSize);
        //编辑简历的返回值
        $list = RecruitmentResume::listModifyReturnValue($list);
        //返回收藏的招聘简历的数据
        return $this->success($list);
    }
}
