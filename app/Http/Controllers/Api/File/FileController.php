<?php

namespace App\Http\Controllers\Api\File;

use App\Http\Controllers\BaseController;
use App\Http\Requests\File\File;
use App\Services\File\FileService;

class FileController extends BaseController
{
    //上传文件
    public function uploadFile(File $request)
    {
        $request->validate([
            'file' => [
                'file'
            ]
        ]);
        $file = $request->file('file');
        if (!in_array($file->getClientOriginalExtension(), ['doc', 'docx'])) {
            return $this->error(__('messages.fileExtensionNameIsIncorrect'));
        }
        $filePath = FileService::upload($file, FileService::$uploadFile);
        return $this->success([
            'file' => $filePath
        ]);
    }

    //上传图片
    public function uploadImage(File $request)
    {
        $request->validate([
            'file' => [
                'image'
            ]
        ]);
        $file = $request->file('file');
        $filePath = FileService::upload($file, FileService::$uploadImage);
        return $this->success([
            'file' => $filePath
        ]);
    }
}
