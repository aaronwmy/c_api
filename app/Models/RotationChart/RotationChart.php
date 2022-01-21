<?php

namespace App\Models\RotationChart;

use App\Models\BaseModel;

class RotationChart extends BaseModel
{
    protected function getMaxSort($place)
    {
        $sort = RotationChart::where('place', $place)->max('sort');
        return empty($sort) ? 0 : $sort;
    }
}
