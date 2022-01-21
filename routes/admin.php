<?php

use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Api'], function () {
    Route::get('getDcKey', 'EncryptionAndDecryptionController@getDcKey');
});

Route::group(['middleware' => ['api_data_analysis', 'xss']], function () {
    Route::group(['namespace' => 'Admin\AdminUser'], function () {
        Route::post('token', 'LoginAndRegistrationController@login');
        Route::put('token', 'LoginAndRegistrationController@refreshToken');
        Route::group(['middleware' => ['admin.auth']], function () {
            Route::get('me', 'AdminUserController@getMyInfo');
            Route::delete('token', 'LoginAndRegistrationController@logout');
            Route::put('password', 'AdminUserController@resetPassword');
            Route::group(['middleware' => ['auth:admin', 'permission:admin_user_manageable']], function () {
                Route::get('admin_user', 'AdminUserController@getList');
                Route::post('admin_user', 'AdminUserController@addAdminUser');
                Route::put('admin_user', 'AdminUserController@editAdminUser');
                Route::put('admin_user_status', 'AdminUserController@editStatus');
                Route::get('role', 'RoleController@getList');
                Route::post('role', 'RoleController@addRole');
                Route::put('role', 'RoleController@editRole');
                Route::delete('role', 'RoleController@deleteRole');
                Route::get('permission', 'PermissionController@getList');
                Route::put('admin_user_roles', 'AdminUserController@AdminUserResetRoles');
                Route::put('role_permissions', 'RoleController@roleResetPermissions');
            });
        });
    });
    Route::group(['namespace' => 'Admin\User'], function () {
        Route::group(['middleware' => ['admin.auth']], function () {
            Route::group(['middleware' => ['auth:admin', 'permission:user_readable']], function () {
                Route::get('user', 'UserController@getUserList');
            });
            Route::group(['middleware' => ['auth:admin', 'permission:teacher_readable']], function () {
                Route::get('teacher', 'TeacherController@getTeacherList');
            });
            Route::group(['middleware' => ['auth:admin', 'permission:teacher_writable']], function () {
                Route::post('teacher_status', 'TeacherController@approvedTeacher');
                Route::put('teacher_status', 'TeacherController@rejectTeacher');
            });
            Route::group(['middleware' => ['auth:admin', 'permission:company_readable']], function () {
                Route::get('company', 'CompanyController@getCompanyList');
            });
            Route::group(['middleware' => ['auth:admin', 'permission:company_writable']], function () {
                Route::post('company_status', 'CompanyController@approvedCompany');
                Route::put('company_status', 'CompanyController@rejectCompany');
            });
            Route::group(['middleware' => ['auth:admin', 'permission:school_readable']], function () {
                Route::get('school', 'SchoolController@getSchoolList');
            });
            Route::group(['middleware' => ['auth:admin', 'permission:school_writable']], function () {
                Route::post('school_status', 'SchoolController@approvedSchool');
                Route::put('school_status', 'SchoolController@rejectSchool');
            });
            Route::group(['middleware' => ['auth:admin', 'permission:user_money_record_readable']], function () {
                Route::get('user_money_record', 'UserMoneyRecordController@getList');
            });
            Route::group(['middleware' => ['auth:admin', 'permission:user_withdrawals_readable']], function () {
                Route::get('user_withdrawal', 'UserWithdrawalController@getList');
            });
            Route::group(['middleware' => ['auth:admin', 'permission:user_withdrawals_writable']], function () {
                Route::put('user_withdrawal', 'UserWithdrawalController@completeWithdrawal');
            });
            Route::get('teacher_apply_school', 'TeacherApplySchoolController@getApplySchoolList');
        });
    });
    Route::group(['namespace' => 'Admin\Course'], function () {
        Route::group(['middleware' => ['admin.auth']], function () {
            Route::group(['middleware' => ['auth:admin', 'permission:course_readable']], function () {
                Route::get('course', 'CourseController@getList');
                Route::get('course_chapter', 'CourseChapterController@getChapters');
                Route::get('course_video', 'CourseVideoController@getList');
            });
            Route::group(['middleware' => ['auth:admin', 'permission:course_writable']], function () {
                Route::post('course_status', 'CourseController@approvedCourse');
                Route::put('course_status', 'CourseController@rejectCourse');
            });
        });
    });
    Route::group(['namespace' => 'Admin\CourseOrder'], function () {
        Route::group(['middleware' => ['admin.auth']], function () {
            Route::group(['middleware' => ['auth:admin', 'permission:course_order_readable']], function () {
                Route::get('course_order', 'OrderController@getList');
            });
        });
    });
    Route::group(['namespace' => 'Admin\Bank'], function () {
        Route::group(['middleware' => ['admin.auth']], function () {
            Route::group(['middleware' => ['auth:admin', 'permission:bank_manageable']], function () {
                Route::get('bank', 'BankController@getList');
                Route::post('bank', 'BankController@addBank');
                Route::put('bank', 'BankController@editBank');
                Route::delete('bank', 'BankController@deleteBank');
            });
        });
    });
    Route::group(['namespace' => 'Api\File'], function () {
        Route::group(['middleware' => ['admin.auth']], function () {
            Route::post('file', 'FileController@uploadFile');
            Route::post('image', 'FileController@uploadImage');
        });
    });
    Route::group(['namespace' => 'Api\TencentCloudOnDemand'], function () {
        Route::group(['middleware' => ['admin.auth']], function () {
            Route::get('tencent_cloud_on_demand/play_video_sign', 'SignController@getPlayVideoSign');
        });
    });
    Route::group(['namespace' => 'Admin\RotationChart'], function () {
        Route::group(['middleware' => ['admin.auth']], function () {
            Route::group(['middleware' => ['auth:admin', 'permission:rotation_chart_manageable']], function () {
                Route::get('rotation_chart', 'RotationChartController@getList');
                Route::post('rotation_chart', 'RotationChartController@addRotationChart');
                Route::put('rotation_chart', 'RotationChartController@editRotationChart');
                Route::delete('rotation_chart', 'RotationChartController@deleteRotationChart');
            });
        });
    });
    Route::group(['namespace' => 'Admin\Log'], function () {
        Route::group(['middleware' => ['admin.auth']], function () {
            Route::group(['middleware' => ['auth:admin', 'permission:log_manageable']], function () {
                Route::get('api_log', 'ApiLogController@getList');
            });
        });
    });
});
