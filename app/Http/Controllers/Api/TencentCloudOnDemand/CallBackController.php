<?php

namespace App\Http\Controllers\Api\TencentCloudOnDemand;

use App\Http\Controllers\BaseController;
use App\Models\Course\CourseVideo;
use App\Models\User\Teacher;
use App\Services\EncryptionAndDecryption\AES;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CallBackController extends BaseController
{

    //腾讯云点播回调
    public function callback(Request $request)
    {
        //获得参数
        $input = $request->all();
        Log::debug($input);
        if (isset($input['EventType'])) {
            if ($input['EventType'] == 'NewFileUpload') {
                $fileId = $input['FileUploadEvent']['FileId'];
                $url = $input['FileUploadEvent']['MediaBasicInfo']['MediaUrl'];
                $Aes = new AES(env('TENCENT_CLOUD_AES_KEY'), 'cbc', 'base64', env('TENCENT_CLOUD_AES_IV'));
                $sourceContext = @$Aes->decrypt($input['FileUploadEvent']['MediaBasicInfo']['SourceInfo']['SourceContext']);
                if (substr($sourceContext, 0, 8) == 'teacher_') {
                    $user_id = str_replace('teacher_', '', $sourceContext);
                    try {
                        Teacher::where('user_id', $user_id)->update([
                            'tencent_cloud_on_demand_file_id' => $fileId
                        ]);
                    } catch (\Exception $e) {
                        return $this->error(__('messages.DatabaseError'));
                    }
                } elseif (substr($sourceContext, 0, 6) == 'video_') {
                    $video_id = str_replace('video_', '', $sourceContext);
                    try {
                        CourseVideo::where('id', $video_id)->update([
                            'tencent_cloud_on_demand_file_id' => $fileId
                        ]);
                    } catch (\Exception $e) {
                        return $this->error(__('messages.DatabaseError'));
                    }
                }
            } elseif ($input['EventType'] == 'ProcedureStateChanged') {
                if ($input['ProcedureStateChangeEvent']['Status'] == 'FINISH') {
                    $fileId = $input['ProcedureStateChangeEvent']['FileId'];
                    for ($i = 0; $i < count($input['ProcedureStateChangeEvent']['MediaProcessResultSet']); $i++) {
                        if ($input['ProcedureStateChangeEvent']['MediaProcessResultSet'][$i]['Type'] == 'CoverBySnapshot') {
                            if ($input['ProcedureStateChangeEvent']['MediaProcessResultSet'][$i]['CoverBySnapshotTask']['Status'] == 'SUCCESS') {
                                $coverUrl = $input['ProcedureStateChangeEvent']['MediaProcessResultSet'][$i]['CoverBySnapshotTask']['Output']['CoverUrl'];
                                Teacher::where('tencent_cloud_on_demand_file_id', $fileId)->update([
                                    'tencent_cloud_on_demand_cover_url' => $coverUrl
                                ]);
                                CourseVideo::where('tencent_cloud_on_demand_file_id', $fileId)->update([
                                    'tencent_cloud_on_demand_cover_url' => $coverUrl
                                ]);
                            }
                        }
                    }
                }
            }
        }
        return $this->success();
    }
}
