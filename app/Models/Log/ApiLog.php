<?php

namespace App\Models\Log;

use App\Common\Constant;
use App\Models\BaseModel;

class ApiLog extends BaseModel
{
    protected $connection =Constant::LOG_DB;
}
