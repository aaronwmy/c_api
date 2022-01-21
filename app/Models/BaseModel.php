<?php

namespace App\Models;

use App\Common\Constant;
use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    protected $connection = Constant::MAIN_DB;
    protected $guarded = [];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }

    public function scopeOrdersBy($query, $orderArr)
    {
        foreach ($orderArr as $key => $value) {
            $query->orderBy($key, $value);
        }
        return $query;
    }
}
