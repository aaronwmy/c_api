<?php

namespace App\Services\File;

use App\Common\Constant;
use Illuminate\Support\Facades\Storage;

class FileService
{
    public static $uploadFile = 1;
    public static $uploadImage = 2;

    //上传文件
    public static function upload(\Illuminate\Http\UploadedFile $file, $type)
    {
        $fileExt = strtolower($file->getClientOriginalExtension());
        $realPath = $file->getRealPath();
        if ($type == self::$uploadFile) $filePrefix = '/' . Constant::UPLOAD_FILE;
        elseif ($type == self::$uploadImage) $filePrefix = '/' . Constant::UPLOAD_IMAGE;
        $filePath = $filePrefix . '/' . date('Y') . date('m') . '/' . date('d') . '/' . date("YmdHis") . '_' . rand(10000, 99999) . '.' . $fileExt;
        Storage::disk()->put($filePath, file_get_contents($realPath));
        return $filePath;
    }
}
