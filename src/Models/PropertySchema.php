<?php

namespace Ximdex\StructuredData\Models;

use Ximdex\StructuredData\Core\Model;

class PropertySchema extends Model
{
    public $hidden = ['created_at', 'updated_at', 'property', 'property_id'];
    
    public $appends = ['name'];
    
    public function getNameAttribute() : string
    {
        return $this->property->name;
    }
    
    public function property()
    {
        return $this->belongsTo(Property::class);
    }
    
    public function availableTypes()
    {
        return $this->hasMany(AvailableType::class);
    }
}
