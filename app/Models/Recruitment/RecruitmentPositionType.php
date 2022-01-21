<?php

namespace App\Models\Recruitment;

use App\Models\BaseModel;

class RecruitmentPositionType extends BaseModel
{
    //去掉招聘职位数据的下标
    public static function removeSubscript($data)
    {
        foreach ($data as $c_key => $city) {
            foreach ($data[$c_key]['child'] as $d_key => $district) {
                if (isset($data[$c_key]['child'][$d_key]['child'])) $data[$c_key]['child'][$d_key]['child'] = array_values($data[$c_key]['child'][$d_key]['child']);

            }
            $data[$c_key]['child'] = array_values($data[$c_key]['child']);
        }
        return array_values($data);
    }
}
