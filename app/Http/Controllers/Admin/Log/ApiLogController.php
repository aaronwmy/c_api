<?php

namespace App\Http\Controllers\Admin\Log;

use App\Http\Controllers\BaseController;
use App\Models\Log\ApiLog;
use App\Models\User\User;
use Illuminate\Http\Request;

class ApiLogController extends BaseController
{
    //查询公司信息
    public function getList(Request $request)
    {
        //获得参数
        $input = $request->all();
        //设置默认分页大小
        $pageSize = isset($input['page_size']) ? $input['page_size'] : 10;
        //设置查询条件
        $where = [];
        if (isset($input['begin_time'])) {
            array_push($where, ['created_at', '>=', $input['begin_time']]);
        }
        if (isset($input['end_time'])) {
            array_push($where, ['created_at', '<=', $input['end_time']]);
        }
        if (isset($input['min_response_time'])) {
            array_push($where, ['response_time', '>=', $input['min_response_time']]);
        }
        if (isset($input['max_response_time'])) {
            array_push($where, ['response_time', '<=', $input['max_response_time']]);
        }
        if (isset($input['ip'])) {
            array_push($where, ['client_ip', 'like', '%' . $input['ip'] . '%']);
        }
        if (isset($input['http_status_code'])) {
            array_push($where, ['http_status_code', $input['http_status_code']]);
        }
        if (isset($input['method'])) {
            array_push($where, ['method', $input['method']]);
        }
        if (isset($input['type'])) {
            array_push($where, ['type', $input['type']]);
        }
        if (isset($input['user_id'])) {
            array_push($where, ['user_id', $input['user_id']]);
        }
        //查询公司的数据
        $list = ApiLog::where($where)->orderBy('id', 'desc')->paginate($pageSize);
        $list->getCollection()->transform(function ($info, $key) {
            $info['nickname'] = '';
            if ($info['user_id'] > 0) {
                $userInfo = User::where('id', $info['user_id'])->first();
                $info['nickname'] = $userInfo['nickname'];
            }
            return $info;
        });
        //返回公司的数据
        return $this->success($list);
    }
}
