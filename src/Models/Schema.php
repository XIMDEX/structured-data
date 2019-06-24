<?php

namespace Ximdex\StructuredData\Models;

use Ximdex\StructuredData\Core\Model;

class Schema extends Model
{
    const THING_TYPE = 'Thing';
    
    public $hidden = ['created_at', 'updated_at', 'pivot', 'mainProperties', 'version'];
    
    public $fillable = ['name', 'comment', 'version_id'];
    
    public $appends = ['version_tag'];
    
    public function getVersionTagAttribute(): ?string
    {
        if ($this->version) {
            return $this->version->tag;
        }
        return null;
    }
    
    /**
     * Return the version for this schema
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function version()
    {
        return $this->belongsTo(Version::class);
    }
    
    /**
     * Retrieve only the properties specified for this schema
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function mainProperties()
    {
        $result = $this->hasMany(PropertySchema::class);
        if (Version::getLatest()) {
            
            // Get the latest version of schema properties with custom user ones
            $result->where(function ($query) {
                $query->whereRaw('version_id IS NULL OR version_id = ?', Version::getLatest()); 
            });
        }
        return $result;
    }
    
    /**
     * Retrieve the parent schemas for the current one (only one level)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function inheritedSchemas()
    {
        $result = $this->belongsToMany(static::class, (new HereditableSchema())->getTable(), null, 'parent_schema_id');
        if (Version::getLatest()) {
            
            // Get the latest version of inherited schemas with custom user ones
            $result->where(function ($query) {
                $table = (new HereditableSchema())->getTable();
                $query->whereRaw($table . '.version_id IS NULL OR ' . $table . '.version_id = ?', Version::getLatest());
            });
        }
        return $result->withPivot('priority')->orderBy('priority');
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
    
    /**
     * Check if this schema has an inheritance relation with the schema given
     * 
     * @param Schema $schema
     * @return bool
     */
    public function extends(Schema $schema): bool
    {
        if ($this->id == $schema->id) {
            return true;
        }
        foreach ($this->inheritedSchemas as $inheritedSchema) {
            if ($inheritedSchema->id == $schema->id) {
                return true;
            }
            return $inheritedSchema->extends($schema);
        }
        return false;
    }
}
