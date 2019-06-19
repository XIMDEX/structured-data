<?php

namespace Ximdex\StructuredData\Models;

use Ximdex\StructuredData\Core\Model;

class AvailableType extends Model
{
    const THING_TYPE = Schema::THING_TYPE;
    
    const BOOLEAN_TYPE = 'Boolean';
    
    const DATE_TYPE = 'Date';
    
    const DATETIME_TYPE = 'DateTime';
    
    const NUMBER_TYPE = 'Number';
    
    const TEXT_TYPE = 'Text';
    
    const TIME_TYPE = 'Time';
    
    const SIMPLE_TYPES = [
        self::THING_TYPE,
        self::BOOLEAN_TYPE,
        self::DATE_TYPE,
        self::DATETIME_TYPE,
        self::NUMBER_TYPE,
        self::TEXT_TYPE,
        self::TIME_TYPE
    ];
    
    public $hidden = ['created_at', 'updated_at', 'property_schema_id', 'schema', 'version_id', 'version'];
    
    public $appends = ['schema_name', 'version_tag'];
    
    public $fillable = ['schema_id', 'property_schema_id', 'type', 'version_id'];
    
    public static $except = ['property_schema_id'];
    
    public function getSchemaNameAttribute() : ?string
    {
        if ($this->schema_id) {
            return $this->schema->name;
        }
        return null;
    }
    
    public function getVersionTagAttribute(): ?string
    {
        if ($this->version) {
            return $this->version->tag;
        }
        return null;
    }
    
    public function version()
    {
        return $this->belongsTo(Version::class);
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
