<?php

namespace App\Models\Collection;

use App\Models\BaseModel;

class Collection extends BaseModel
{
    const COURSE = 1;
    const POSITION = 2;
    const RESUME = 3;

    //检查用户是否收藏了某个事物
    protected function checkIfCollected($user_id, $type, $other_id)
    {
        return Collection::where('user_id', $user_id)->where('type', $type)->where('other_id', $other_id)->count() > 0;
    }
}
