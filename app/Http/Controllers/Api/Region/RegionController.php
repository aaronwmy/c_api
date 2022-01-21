<?php

namespace App\Http\Controllers\Api\Region;

use App\Http\Controllers\BaseController;
use App\Models\Region\Region;
use App\Services\Region\RegionCode;
use Illuminate\Http\Request;

class RegionController extends BaseController
{
    //获得地区数据
    public function getList(Request $request)
    {
        //获得参数
        $input = $request->all();
        //查询地区数据
        $list = Region::orderBy('region_code', 'asc')->get()->toArray();
        $data = [];
        for ($i = 0; $i < count($list); $i++) {
            if ($list[$i]['level'] == 1) {
                $data['a' . $list[$i]['region_code']] = $list[$i];
            } elseif ($list[$i]['level'] == 2) {
                $data['a' . substr($list[$i]['region_code'], 0, 2) . '0000']['child']['a' . $list[$i]['region_code']] = $list[$i];
            } elseif ($list[$i]['level'] == 3) {
                $data['a' . substr($list[$i]['region_code'], 0, 2) . '0000']['child']['a' . substr($list[$i]['region_code'], 0, 4) . '00']['child']['a' . $list[$i]['region_code']] = $list[$i];
            }
        }
        if (isset($input['remove_subscript']) && $input['remove_subscript'] == 1) {
            $data = RegionCode::removeSubscript($data);
        }
        return $this->success($data);
    }

    //获得城市数据
    public function getCityList()
    {
        //查询热门城市
        $hotCity = Region::where('is_city', Region::IS_CITY)->where('is_hot', Region::IS_HOT)->orderBy('pinyin_initials', 'asc')->get()->toArray();
        //查询国内城市数据
        $cityArr = Region::where('is_city', Region::IS_CITY)->orderBy('pinyin_initials', 'asc')->get()->toArray();
        $cityData = [];
        $pinyinInitials = '';
        $cityList = [];
        for ($i = 0; $i < count($cityArr); $i++) {
            if ($pinyinInitials != trim($cityArr[$i]['pinyin_initials'])) {
                if ($pinyinInitials != '') {
                    $cityData[] = ['letter' => $pinyinInitials, 'city_list' => $cityList];
                    $cityList = [];
                }
                $pinyinInitials = trim($cityArr[$i]['pinyin_initials']);
            }
            $cityList[] = $cityArr[$i];
        }
        $cityData[] = ['letter' => $pinyinInitials, 'city_list' => $cityList];
        return $this->success([
            'hot_city' => $hotCity,
            'city' => $cityData
        ]);
    }
}
