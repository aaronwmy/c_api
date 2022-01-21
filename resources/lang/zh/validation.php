<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'The :attribute must be accepted.',
    'active_url' => 'The :attribute is not a valid URL.',
    'after' => '":attribute"必须晚于":date".',
    'after_or_equal' => 'The :attribute must be a date after or equal to :date.',
    'alpha' => 'The :attribute may only contain letters.',
    'alpha_dash' => 'The :attribute may only contain letters, numbers, dashes and underscores.',
    'alpha_num' => 'The :attribute may only contain letters and numbers.',
    'array' => '":attribute"必须是数组.',
    'before' => 'The :attribute must be a date before :date.',
    'before_or_equal' => 'The :attribute must be a date before or equal to :date.',
    'between' => [
        'numeric' => 'The :attribute must be between :min and :max.',
        'file' => 'The :attribute must be between :min and :max kilobytes.',
        'string' => 'The :attribute must be between :min and :max characters.',
        'array' => 'The :attribute must have between :min and :max items.',
    ],
    'boolean' => 'The :attribute field must be true or false.',
    'confirmed' => 'The :attribute confirmation does not match.',
    'date' => '":attribute"不是有效的日期格式.',
    'date_equals' => 'The :attribute must be a date equal to :date.',
    'date_format' => '":attribute"的日期格式应该是":format".',
    'different' => 'The :attribute and :other must be different.',
    'digits' => 'The :attribute must be :digits digits.',
    'digits_between' => 'The :attribute must be between :min and :max digits.',
    'dimensions' => 'The :attribute has invalid image dimensions.',
    'distinct' => 'The :attribute field has a duplicate value.',
    'email' => 'The :attribute must be a valid email address.',
    'ends_with' => 'The :attribute must end with one of the following: :values.',
    'exists' => '":attribute"不存在.',
    'file' => '":attribute"必须是一个文件.',
    'filled' => 'The :attribute field must have a value.',
    'gt' => [
        'numeric' => 'The :attribute must be greater than :value.',
        'file' => 'The :attribute must be greater than :value kilobytes.',
        'string' => 'The :attribute must be greater than :value characters.',
        'array' => 'The :attribute must have more than :value items.',
    ],
    'gte' => [
        'numeric' => '":attribute"必须大于或等于":value".',
        'file' => 'The :attribute must be greater than or equal :value kilobytes.',
        'string' => 'The :attribute must be greater than or equal :value characters.',
        'array' => 'The :attribute must have :value items or more.',
    ],
    'image' => '":attribute"必须是图片文件.',
    'in' => '":attribute"的值不正确.',
    'in_array' => 'The :attribute field does not exist in :other.',
    'integer' => '":attribute"必须是整数.',
    'ip' => 'The :attribute must be a valid IP address.',
    'ipv4' => 'The :attribute must be a valid IPv4 address.',
    'ipv6' => 'The :attribute must be a valid IPv6 address.',
    'json' => 'The :attribute must be a valid JSON string.',
    'lt' => [
        'numeric' => 'The :attribute must be less than :value.',
        'file' => 'The :attribute must be less than :value kilobytes.',
        'string' => 'The :attribute must be less than :value characters.',
        'array' => 'The :attribute must have less than :value items.',
    ],
    'lte' => [
        'numeric' => 'The :attribute must be less than or equal :value.',
        'file' => 'The :attribute must be less than or equal :value kilobytes.',
        'string' => 'The :attribute must be less than or equal :value characters.',
        'array' => 'The :attribute must not have more than :value items.',
    ],
    'max' => [
        'numeric' => 'The :attribute may not be greater than :max.',
        'file' => 'The :attribute may not be greater than :max kilobytes.',
        'string' => '":attribute"最大长度为:max字符.',
        'array' => 'The :attribute may not have more than :max items.',
    ],
    'mimes' => 'The :attribute must be a file of type: :values.',
    'mimetypes' => 'The :attribute must be a file of type: :values.',
    'min' => [
        'numeric' => '":attribute"不能小于:min.',
        'file' => 'The :attribute must be at least :min kilobytes.',
        'string' => 'The :attribute must be at least :min characters.',
        'array' => 'The :attribute must have at least :min items.',
    ],
    'not_in' => 'The selected :attribute is invalid.',
    'not_regex' => 'The :attribute format is invalid.',
    'numeric' => 'The :attribute must be a number.',
    'password' => 'The password is incorrect.',
    'present' => 'The :attribute field must be present.',
    'regex' => '":attribute"的格式不正确.',
    'required' => '":attribute"为必填.',
    'required_if' => '当":other"等于":value"时,":attribute"是必填的.',
    'required_unless' => 'The :attribute field is required unless :other is in :values.',
    'required_with' => 'The :attribute field is required when :values is present.',
    'required_with_all' => 'The :attribute field is required when :values are present.',
    'required_without' => '当":values"不存在时，":attribute"为必填.',
    'required_without_all' => 'The :attribute field is required when none of :values are present.',
    'same' => 'The :attribute and :other must match.',
    'size' => [
        'numeric' => 'The :attribute must be :size.',
        'file' => 'The :attribute must be :size kilobytes.',
        'string' => 'The :attribute must be :size characters.',
        'array' => 'The :attribute must contain :size items.',
    ],
    'starts_with' => 'The :attribute must start with one of the following: :values.',
    'string' => 'The :attribute must be a string.',
    'timezone' => 'The :attribute must be a valid zone.',
    'unique' => '":attribute"已存在.',
    'uploaded' => 'The :attribute failed to upload.',
    'url' => 'The :attribute format is invalid.',
    'uuid' => 'The :attribute must be a valid UUID.',
    'notEarlierThanCurrentTime' => ':attribute不能早于当前时间',
    'notEarlierThanValue1' => ':attribute不能早于:value1',
    'notLaterThanCurrentTime' => ':attribute不能晚于当前时间',
    'notLaterThanValue1' => ':attribute不能晚于:value1',
    'notLessThanValue1' => ':attribute不能小于:value1',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'mobile' => '手机号',
        'verification_code' => '验证码',
        'portrait' => '头像',
        'nickname' => '昵称',
        'sex' => '性别',
        'file' => '文件',
        'id_card_number' => '身份证',
        'id_card_front_pic' => '身份证正面照',
        'id_card_back_pic' => '身份证背面照',
        'username' => '用户名',
        'password' => '密码',
        'type' => '类型',
        'video_name' => '视频名称',
        'course_id' => '课程id',
        'fid' => '父id',
        'sort' => '排序',
        'user_id' => '用户id',
        'section_id' => '课节id',
        'file_id' => '文件id',
        'father_region_code' => '父级地区编码',
        'region_code' => '地区编码',
        'fullname' => '姓名',
        'bank_id' => '银行id',
        'branch_address' => '银行地址',
        'bank_card_number' => '银行卡号',
        'begin_time' => '开始时间',
        'end_time' => '结束时间',
        'introduction' => '简介',
        'order_number' => '订单号',
        'position_name' => '职位名称',
        'min_salary' => '最小薪资',
        'max_salary' => '最大薪资',
        'description' => '描述',
        'benefits' => '福利',
        'education_id' => '学历id',
        'experience_id' => '经验id',
        'region_code' => '地区编码',
        'address' => '地址',
        'recruitment_position_id' => '招聘职位id',
        'recruitment_position_ids' => '招聘职位id',
        'email' => '电子邮件',
        'birth_year_month' => '出生年月',
        'first_job_year_month' => '参加工作年月',
        'self_evaluation' => '自我评价',
        'resume_id' => '简历id',
        'company_name' => '公司名称',
        'departure_year_month' => '离职年月',
        'entry_year_month' => '入职年月',
        'job_content' => '工作内容',
        'position_type_id' => '职位类型id',
        'admission_year_month' => '入学年月',
        'graduation_year_month' => '毕业年月',
        'school_name' => '学校名称',
        'major' => '专业',
        'shield_keyword' => '屏蔽关键字',
        'is_shield_resume' => '是否隐藏简历',
        'other_id' => '其他id',
        'to_user_id' => '用户id',
        'param1' => '参数1',
        'param2' => '参数2',
        'collection_ids' => '收藏id',
        'school_user_id' => '学校用户id',
        'bank_logo' => '银行logo',
        'name' => '名称',
        'role_name' => '角色名称',
        'permission_name' => '权限名称',
        'role_id' => '角色id',
        'role_ids' => '角色id',
        'permission_id' => '权限id',
        'permission_ids' => '权限id',
        'old_password' => '旧密码',
        'comment_replied_id' => '被回复的评论id',
    ],

];
