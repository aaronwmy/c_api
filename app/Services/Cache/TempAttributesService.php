<?php

namespace App\Services\Cache;

class TempAttributesService
{
    private $dataWarehouse = [];
    private $attributeNames;

    public function __construct($attributeNames)
    {
        $this->attributeNames = $attributeNames;
    }

    public function __call($method, $parameters)
    {
        if (in_array(lcfirst(substr($method, 3)), $this->attributeNames)) {
            if (substr($method, 0, 3) == 'get') {
                return $this->dataWarehouse[lcfirst(substr($method, 3))];
            } elseif (substr($method, 0, 3) == 'set') {
                $this->dataWarehouse[lcfirst(substr($method, 3))] = $parameters[0];
            }
        }
    }
}
