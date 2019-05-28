<?php

namespace Ximdex\StructuredData\Models;

use Ximdex\StructuredData\Core\Model;

class AvailableType extends Model
{
    public $hidden = ['created_at', 'updated_at', 'property_schema_id', 'schema'];
    
    public $appends = ['schema_name'];
    
    public function getSchemaNameAttribute() : ?string
    {
        if ($this->schema_id) {
            return $this->schema->name;
        }
        return null;
    }
    
    public function schema()
    {
        return $this->belongsTo(Schema::class);
    }
    
    public function propertySchema()
    {
        return $this->belongsTo(PropertySchema::class);
    }
}
