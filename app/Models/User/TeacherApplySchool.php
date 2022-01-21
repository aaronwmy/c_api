<?php

namespace App\Models\User;

use App\Models\BaseModel;

class TeacherApplySchool extends BaseModel
{
    const REJECTED = -1;
    const UNDER_REVIEW = 0;
    const APPROVED = 1;
}
