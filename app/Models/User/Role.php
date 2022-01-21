<?php

namespace App\Models\User;

use Spatie\Permission\Models\Role as BaseRole;

class Role extends BaseRole
{
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }
}
