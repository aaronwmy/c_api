<?php

namespace App\Common;

class Constant
{
    const MAIN_DB = 'main';
    const LOG_DB = 'log';
    const SUCCESS_STATUS_OF_API_RESPONSE = 'ok';
    const ERROR_STATUS_OF_API_RESPONSE = 'err';
    const EFFECTIVE_MINUTES_OF_SMS = 10;
    const TENCENT_CLOUD_SMS_VERIFICATION_CODE_TEMPLATE_ID = '705446';
    const AES_KEY_LENGTH = 16;
    const NO_EFFECTIVE_TOKEN_HTTP_STATUS = 481;
    const NO_EFFECTIVE_REFRESH_TOKEN_HTTP_STATUS = 482;
    const TEACHER_AUTHORITY_REQUIRED = 491;
    const ONLY_USER = 492;
    const COMPANY_AUTHORITY_REQUIRED = 493;
    const ONLY_TEACHER = 494;
    const HTTP_OK = 200;
    const STORAGE = 'storage';
    const UPLOAD_IMAGE = 'upload_image';
    const UPLOAD_FILE = 'upload_file';
    const DEFAULT_ADMIN_USER_PASSWORD = '123456789';
    const WORKERMAN_API_UID_PREFIX = 'api_';
    const WORKERMAN_ADMIN_UID_PREFIX = 'admin_';
}

?>
