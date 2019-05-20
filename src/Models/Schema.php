<?php

namespace Ximdex\StructuredData\Models;

use Ximdex\StructuredData\Core\Model;

class Schema extends Model
{
    public function properties()
    {
        return $this->belongsToMany(Property::class, (new PropertySchema())->getTable());
    }
    
    public function availableTypes()
    {
        return $this->hasMany(AvailableType::class);
    }
    
    public function inheritedSchemas()
    {
        return $this->belongsToMany(self::class, (new HereditableSchema())->getTable());
    }
}
