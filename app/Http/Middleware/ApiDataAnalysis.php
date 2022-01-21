<?php

namespace App\Http\Middleware;

use App\Http\Traits\ApiResponse;
use App\Services\ApiDataAnalysis\ApiDataAnalysisService;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Closure;

class ApiDataAnalysis extends Middleware
{
    use ApiResponse;

    public function handle($request, Closure $next)
    {
        $input = $request->all();
        //禁止传递Authorization头
        if ($request->header('Authorization')) return $this->error(__('messages.IllegalOperation'));
        //检查参数是否只包含data、kbsid和file
        if (!$this->checkInput($input)) return $this->error(__('messages.IllegalOperation'));
        $data = $input['data'];
        $kbsid = $input['kbsid'];
        //对data参数进行解码
        $result = ApiDataAnalysisService::getData($data, $kbsid);
        if ($result->getCode() == '001') {
            return $this->error($result->getMessage());
        }
        $_data = $result->getData();
        //将data参数解码后的数组的元素全部添加到request中
        foreach ($_data as $key => $val) {
            $request->offsetSet($key, $val);
        }
        //删除request中的data、kbsid和sign三个参数
        $request->offsetUnset('data');
        $request->offsetUnset('kbsid');
        $request->offsetUnset('sign');
        return $next($request);
    }

    //检查参数是否只包含data、kbsid和file
    private function checkInput($input)
    {
        unset($input['data']);
        unset($input['kbsid']);
        unset($input['file']);
        if (count($input) == 0) {
            return true;
        } else {
            return false;
        }
    }
}
