<?php

namespace Ximdex\StructuredData\Models;

use Ximdex\StructuredData\Core\Model;

class Schema extends Model
{
    const THING_TYPE = 'Thing';
    
    public $hidden = ['created_at', 'updated_at', 'pivot', 'mainProperties'];
    
    public $fillable = ['name', 'comment', 'version_id'];
    
    /**
     * Retrieve only the properties specified for this schema
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function mainProperties()
    {
        return $this->hasMany(PropertySchema::class);
    }
    
    /**
     * Retrieve the parent schemas for the current one (only one level)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function inheritedSchemas()
    {
        return $this->belongsToMany(static::class, (new HereditableSchema())->getTable(), null, 'parent_schema_id')
            ->withPivot('priority')
            ->orderBy('priority');
    }
    
    /**
     * Retrieve the properties for the current schema and its parents
     * Use low level unique property name
     * 
     * @param array $schemas
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function properties(array & $schemas = [])
    {
        // Load specific properties for this schema
        $properties = $this->mainProperties;
        foreach ($this->inheritedSchemas as $schema) {
            if (in_array($schema->id, $schemas)) {
                
                // This schema was processed already
                continue;
            }
            $schemas[] = $schema->id;
            
            // Merge the parent schema properties ordered by order field
            $properties = $properties->merge($schema->properties($schemas)->sortBy('order'));
        }
        return $properties->unique('name');
    }
}
