<?php

namespace Ximdex\StructuredData\Models;

use Ximdex\StructuredData\Core\Model;

class AvailableType extends Model
{
    const SIMPLE_TYPES = ['Thing', 'Boolean', 'Date', 'DateTime', 'Number', 'Text', 'Time'];
    
    public $hidden = ['created_at', 'updated_at', 'property_schema_id', 'schema'];
    
    public $appends = ['schema_name'];
    
    public $fillable = ['schema_id', 'property_schema_id', 'type'];
    
    public static $except = ['property_schema_id'];
    
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
    
    /**
     * {@inheritDoc}
     * @see \Ximdex\StructuredData\Core\Model::update()
     */
    public function update(array $attributes = [], array $options = [])
    {
        if ($this->type != Schema::THING_TYPE) {
            $this->schema_id = null;
        }
        parent::update($attributes, $options); 
    }
}
