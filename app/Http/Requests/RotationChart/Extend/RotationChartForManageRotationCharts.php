<?php

namespace App\Http\Requests\RotationChart\Extend;

use App\Http\Requests\RotationChart\RotationChart;
use App\Models\User\User;

class RotationChartForManageRotationCharts extends RotationChart
{
    public function rules()
    {
        return array_merge(parent::rules(), [
            'id' => [
                'exists:rotation_charts,id'
            ]
        ]);
    }
}
