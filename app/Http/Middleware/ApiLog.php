<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class ApiLog
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $now_time = (int)(microtime(true) * 1000);
        $response = $next($request);
        if (substr($request->path(), 0, 4) == 'api/') {
            $type = 'api';
            $userModel = Auth::guard('api')->user();
        } elseif (substr($request->path(), 0, 6) == 'admin/') {
            $type = 'admin';
            $userModel = Auth::guard('admin')->user();
        }
        \App\Models\Log\ApiLog::create([
            'type' => $type,
            'user_id' => empty($userModel) ? 0 : $userModel['id'],
            'url' => $request->url(),
            'method' => $request->method(),
            'client_ip' => $request->ip(),
            'request_header' => json_encode($request->header()),
            'params' => json_encode($request->all(), JSON_UNESCAPED_UNICODE),
            'response_time' => (int)(microtime(true) * 1000) - $now_time,
            'http_status_code' => $response->getStatusCode(),
            'result' => mb_substr($response->getContent(), 0, 10000, 'UTF-8')
        ]);
        return $response;
    }
}
