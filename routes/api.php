<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['namespace' => 'Api'], function () {
    Route::get('getDcKey', 'EncryptionAndDecryptionController@getDcKey');
});

Route::group(['middleware' => ['api_data_analysis', 'xss']], function () {
    Route::group(['namespace' => 'Api\User'], function () {
        Route::post('SMS', 'MobileController@sendSMS');
        Route::post('user', 'LoginAndRegistrationController@register');
        Route::post('token', 'LoginAndRegistrationController@login');
        Route::put('token', 'LoginAndRegistrationController@refreshToken');
        Route::get('public_school', 'SchoolController@getPublicList');
        Route::get('public_company', 'CompanyController@getPublicList');
        Route::get('public_teacher', 'TeacherController@getPublicList');
        Route::group(['middleware' => ['api.auth']], function () {
            Route::get('user', 'UserController@me');
            Route::put('user', 'UserController@edit');
            Route::delete('token', 'LoginAndRegistrationController@logout');
            Route::post('teacher', 'TeacherController@addOrEditTeacher')->middleware('only_user');
            Route::get('teacher', 'TeacherController@me');
            Route::post('company', 'CompanyController@addOrEditCompany')->middleware('only_user');
            Route::get('company', 'CompanyController@me');
            Route::post('school', 'SchoolController@addOrEditSchool')->middleware('only_user');
            Route::get('school', 'SchoolController@me');
            Route::get('user_money_record', 'UserMoneyRecordController@getList');
            Route::get('user_bank', 'UserBankController@getList');
            Route::post('user_bank', 'UserBankController@addUserBank');
            Route::delete('user_bank', 'UserBankController@deleteUserBank');
            Route::post('user_withdrawal', 'UserWithdrawalController@addUserWithdrawal');
            Route::get('user_i_follow', 'UserController@getUserIFollowList');
            Route::get('user_follow_me', 'UserController@getUserFollowMeList');
            Route::post('record_study', 'UserStudyCourseController@recordStudy');
            Route::post('apply_school', 'TeacherApplySchoolController@applySchool')->middleware('only_teacher');
        });
    });
    Route::group(['namespace' => 'Api\File'], function () {
        Route::group(['middleware' => ['api.auth']], function () {
            Route::post('file', 'FileController@uploadFile');
            Route::post('image', 'FileController@uploadImage');
        });
    });
    Route::group(['namespace' => 'Api\TencentCloudOnDemand'], function () {
        Route::group(['middleware' => ['api.auth']], function () {
            Route::get('tencent_cloud_on_demand/upload_sign', 'SignController@getUploadSign');
            Route::get('tencent_cloud_on_demand/play_video_sign', 'SignController@getPlayVideoSign');
        });
    });
    Route::group(['namespace' => 'Api\TencentCloudIM'], function () {
        Route::group(['middleware' => ['api.auth']], function () {
            Route::get('tencent_cloud_im/user_sig', 'UserSigController@getUserSig');
        });
    });
    Route::group(['namespace' => 'Api\Course'], function () {
        Route::get('public_course', 'CourseController@getPublicList');
        Route::get('public_course_chapter', 'CourseChapterController@getPublicChapters');
        Route::get('public_course_section', 'CourseSectionController@getPublicSections');
        Route::group(['middleware' => ['api.auth']], function () {
            Route::get('public_course_video', 'CourseVideoController@getPublicVideo');
            Route::get('course_live_pull_urls', 'CourseLiveController@getPullUrls');
            Route::get('course_collection', 'CourseController@getCollectionList');
            Route::post('course_comment', 'CourseCommentController@addComment');
            Route::get('course_comment', 'CourseCommentController@getComment');
            Route::group(['middleware' => ['teacher_jurisdiction']], function () {
                Route::get('course', 'CourseController@getList');
                Route::post('course', 'CourseController@addCourse');
                Route::put('course', 'CourseController@editCourse');
                Route::delete('course', 'CourseController@deleteCourse');
                Route::post('course_video', 'CourseVideoController@addVideo');
                Route::get('course_video', 'CourseVideoController@getList');
                Route::put('course_video', 'CourseVideoController@editVideo');
                Route::delete('course_video', 'CourseVideoController@deleteVideo');
                Route::delete('course_videos', 'CourseVideoController@deleteVideos');
                Route::get('course_chapter', 'CourseChapterController@getChapters');
                Route::post('course_chapter', 'CourseChapterController@addChapter');
                Route::put('course_chapter', 'CourseChapterController@editChapter');
                Route::delete('course_chapter', 'CourseChapterController@deleteChapter');
                Route::put('course_chapter_sort', 'CourseChapterController@editSorts');
                Route::post('course_section', 'CourseSectionController@addSection');
                Route::put('course_section', 'CourseSectionController@editSection');
                Route::delete('course_section', 'CourseSectionController@deleteSection');
                Route::put('course_section_sort', 'CourseSectionController@editSorts');
                Route::get('course_live_push_urls', 'CourseLiveController@getPushUrls');
            });
        });
    });
    Route::group(['namespace' => 'Api\CourseOrder'], function () {
        Route::group(['middleware' => ['api.auth']], function () {
            Route::get('course_order', 'OrderController@getList');
            Route::post('course_order', 'OrderController@createOrder');
            Route::delete('course_order', 'OrderController@cancelOrder');
            Route::post('course_order/alipay', 'Pay\AlipayController@pay');
            Route::post('course_order/union_pay', 'Pay\UnionPayController@pay');
            Route::group(['middleware' => ['teacher_jurisdiction']], function () {
                Route::get('teacher_course_order', 'OrderController@getTeacherOrderList');
            });
        });
    });
    Route::group(['namespace' => 'Api\SiteMail'], function () {
        Route::group(['middleware' => ['api.auth']], function () {
            Route::get('site_system_mail', 'SiteMailController@getSystemMail');
            Route::post('user_read_site_mail', 'SiteMailController@readMail');
        });
    });
    Route::group(['namespace' => 'Api\Region'], function () {
        Route::get('region', 'RegionController@getList');
        Route::get('city', 'RegionController@getCityList');
    });
    Route::group(['namespace' => 'Api\Bank'], function () {
        Route::group(['middleware' => ['api.auth']], function () {
            Route::get('bank', 'BankController@getList');
        });
    });
    Route::group(['namespace' => 'Api\Version'], function () {
        Route::get('version', 'VersionController@getVersion');
    });
    Route::group(['namespace' => 'Api\Recruitment'], function () {
        Route::get('public_recruitment_position', 'RecruitmentPositionController@getPublicList');
        Route::group(['middleware' => ['api.auth']], function () {
            Route::group(['middleware' => ['company_jurisdiction']], function () {
                Route::delete('recruitment_position', 'RecruitmentPositionController@deletePosition');
                Route::post('recruitment_position', 'RecruitmentPositionController@addPosition');
                Route::put('recruitment_position', 'RecruitmentPositionController@editPosition');
                Route::get('recruitment_position', 'RecruitmentPositionController@getList');
                Route::put('recruitment_position_status', 'RecruitmentPositionController@editPositionStatus');
                Route::get('recruitment_resumes_received', 'RecruitmentResumeController@getResumeListReceived');
                Route::get('public_recruitment_resume', 'RecruitmentResumeController@getPublicResumeInfo');
                Route::get('public_recruitment_resumes', 'RecruitmentResumeController@getPublicResumeList');
                Route::get('recruitment_resume_collection', 'RecruitmentResumeController@getResumeCollectionList');
                Route::delete('recruitment_resume_received', 'RecruitmentResumeController@deleteResumeReceived');
            });
            Route::get('recruitment_position_request', 'RecruitmentPositionRequestController@getPositionRequest');
            Route::post('recruitment_position_requests', 'RecruitmentPositionRequestController@addPositionRequests');
            Route::post('recruitment_resume_personal_information', 'RecruitmentResumeController@addOrEditResumePersonalInformation');
            Route::put('recruitment_resume_self_evaluation', 'RecruitmentResumeController@editResumeSelfEvaluation');
            Route::post('recruitment_resume_expected_job', 'RecruitmentResumeExpectedJobController@addResumeExpectedJob');
            Route::put('recruitment_resume_expected_job', 'RecruitmentResumeExpectedJobController@editResumeExpectedJob');
            Route::delete('recruitment_resume_expected_job', 'RecruitmentResumeExpectedJobController@deleteResumeExpectedJob');
            Route::get('recruitment_resume_expected_job', 'RecruitmentResumeExpectedJobController@getResumeExpectedJob');
            Route::post('recruitment_resume_job_experience', 'RecruitmentResumeJobExperienceController@addResumeJobExperience');
            Route::put('recruitment_resume_job_experience', 'RecruitmentResumeJobExperienceController@editResumeJobExperience');
            Route::delete('recruitment_resume_job_experience', 'RecruitmentResumeJobExperienceController@deleteResumeJobExperience');
            Route::get('recruitment_resume_job_experience', 'RecruitmentResumeJobExperienceController@getResumeJobExperience');
            Route::post('recruitment_resume_educational_experience', 'RecruitmentResumeEducationalExperienceController@addResumeEducationalExperience');
            Route::put('recruitment_resume_educational_experience', 'RecruitmentResumeEducationalExperienceController@editResumeEducationalExperience');
            Route::delete('recruitment_resume_educational_experience', 'RecruitmentResumeEducationalExperienceController@deleteResumeEducationalExperience');
            Route::get('recruitment_resume_educational_experience', 'RecruitmentResumeEducationalExperienceController@getResumeEducationalExperience');
            Route::get('recruitment_resume', 'RecruitmentResumeController@getResumeInfo');
            Route::post('recruitment_resume_shield_for_companies', 'RecruitmentResumeShieldController@addShield');
            Route::delete('recruitment_resume_shield_for_companies', 'RecruitmentResumeShieldController@deleteShield');
            Route::get('recruitment_position_collection', 'RecruitmentPositionController@getCollectionList');
        });
        Route::get('recruitment_benefits', 'RecruitmentBenefitsController@getList');
        Route::get('recruitment_education', 'RecruitmentEducationController@getList');
        Route::get('recruitment_experience', 'RecruitmentExperienceController@getList');
        Route::get('recruitment_position_type', 'RecruitmentPositionTypeController@getList');
        Route::get('recruitment_working_years', 'RecruitmentWorkingYearsController@getList');
    });
    Route::group(['namespace' => 'Api\Collection'], function () {
        Route::group(['middleware' => ['api.auth']], function () {
            Route::post('collection', 'CollectionController@addCollection');
            Route::delete('collection', 'CollectionController@deleteCollection');
            Route::delete('collections', 'CollectionController@deleteCollections');
        });
    });
    Route::group(['namespace' => 'Api\Follow'], function () {
        Route::group(['middleware' => ['api.auth']], function () {
            Route::post('follow', 'FollowController@addFollow');
            Route::delete('follow', 'FollowController@deleteFollow');
        });
    });

    Route::group(['namespace' => 'Api\RotationChart'], function () {
        Route::get('rotation_chart', 'RotationChartController@getList');
    });
});

Route::group(['namespace' => 'Api\TencentCloudOnDemand'], function () {
    Route::post('tencent_cloud_on_demand/callback', 'CallBackController@callback');
});

Route::group(['namespace' => 'Api\CourseOrder'], function () {
    Route::post('course_order/alipay_app_callback', 'Pay\AlipayController@appCallback');
    Route::post('course_order/alipay_pc_callback', 'Pay\AlipayController@pcCallback');
    Route::post('course_order/union_pay_app_callback', 'Pay\UnionPayController@appCallback');
    Route::post('course_order/union_pay_pc_callback', 'Pay\UnionPayController@pcCallback');
});
