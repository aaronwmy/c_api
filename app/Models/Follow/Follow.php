<?php

namespace App\Models\Follow;

use App\Models\BaseModel;

class Follow extends BaseModel
{
    //检查两个用户之间是否存在关注
    protected function checkIfFollowed($user_id, $to_user_id)
    {
        return Follow::where('user_id', $user_id)->where('to_user_id', $to_user_id)->count() > 0 ? true : false;
    }
}
