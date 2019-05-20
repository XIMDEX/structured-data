<?php

namespace Ximdex\StructuredData\Models;

use Ximdex\StructuredData\Core\Model;

class PropertySchema extends Model
{
    public function availableTypes()
    {
        return $this->hasMany(AvailableType::class);
    }
}
