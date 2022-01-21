<?php

namespace App\Services\Region;

class RegionCode
{
    //从地区编码算出地区级别
    public static function getRegionLevelFromCode($regionCode)
    {
        if (substr($regionCode, 2, 4) == '0000') {
            return 1;
        } elseif (substr($regionCode, 4, 2) == '00') {
            return 2;
        } else {
            return 3;
        }
    }

    //获得地区编码的查询条件
    public static function getSelectWhereFromCode($where, $column, $regionCode)
    {
        $level = RegionCode::getRegionLevelFromCode($regionCode);
        if ($level == 3) {
            array_push($where, [$column, $regionCode]);
        } else {
            array_push($where, [$column, '>=', $regionCode]);
            if ($level == 1) {
                array_push($where, [$column, '<', $regionCode + 10000]);
            } elseif ($level == 2) {
                array_push($where, [$column, '<', $regionCode + 100]);
            }
        }
        return $where;
    }

    //去掉地区数据的下标
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
