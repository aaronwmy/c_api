<?php

namespace App\Http\Controllers\Api\Recruitment;

use App\Http\Controllers\BaseController;
use App\Models\Recruitment\RecruitmentPositionType;
use Illuminate\Http\Request;

class RecruitmentPositionTypeController extends BaseController
{
    //查询招聘职位类型数据
    public function getList(Request $request)
    {
        //获得参数
        $input = $request->all();
        //查询招聘职位类型数据
        $list = RecruitmentPositionType::orderBy('level', 'asc')->orderBy('fid', 'asc')->get()->toArray();
        $data = [];
        $secondData = [];
        for ($i = 0; $i < count($list); $i++) {
            if ($list[$i]['level'] == 1) {
                $data[$list[$i]['id']] = $list[$i];
            } elseif ($list[$i]['level'] == 2) {
                $secondData[$list[$i]['id']] = $list[$i];
            } elseif ($list[$i]['level'] == 3) {
                $secondData[$list[$i]['fid']]['child'][$list[$i]['id']] = $list[$i];
            }
        }
        foreach ($secondData as $key => $value) {
            $data[$secondData[$key]['fid']]['child'][$key] = $secondData[$key];
        }
        if (isset($input['remove_subscript']) && $input['remove_subscript'] == 1) {
            $data = RecruitmentPositionType::removeSubscript($data);
        }
        return $this->success($data);
    }
}
