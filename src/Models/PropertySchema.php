<?php

namespace Ximdex\StructuredData\Models;

use Illuminate\Support\Str;
use Ximdex\StructuredData\Core\Model;

class PropertySchema extends Model
{
    public $hidden = ['created_at', 'updated_at', 'property', 'property_id', 'schema', 'availableTypes', 'version_id', 'schema_id', 'version'
        , 'id'];
    
    public $appends = ['name', 'comment', 'schema_name', 'version_tag', 'types'];
    
    public $fillable = ['name', 'min_cardinality', 'max_cardinality', 'default_value', 'order', 'schema_id', 'property_id', 'version_id'];
    
    public static $except = ['schema_id', 'property_id'];
    
    public function getNameAttribute(): string
    {
        if ($this->property) {
            return Str::camel($this->property->name);
        }
        return '';
    }
    
    public function getCommentAttribute(): ?string
    {
        if ($this->property) {
            return $this->property->comment;
        }
        return null;
    }
    
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
    
    public function getTypesAttribute() : array
    {
        return $this->availableTypes->toArray();
    }
    
    public function setNameAttribute(string $name): void
    {
        if (! $this->property) {
            $this->property = new Property();
        }
        $this->property->name = Str::snake($name);
    }
    
    public function setCommentAttribute(string $comment = null): void
    {
        if (! $this->property) {
            $this->property = new Property();
        }
        $this->property->comment = $comment;
    }
    
    public function property()
    {
        return $this->belongsTo(Property::class);
    }
    
    public function schema()
    {
        return $this->belongsTo(Schema::class);
    }
    
    public function availableTypes()
    {
        $result = $this->hasMany(AvailableType::class);
        if (Version::getLatest()) {
            
            // Get the latest version of property available types with custom user ones
            $result->where(function ($query) {
                $query->whereRaw('version_id IS NULL OR version_id = ?', Version::getLatest());
            });
        }
        return $result;
    }
    
    public function version()
    {
        return $this->belongsTo(Version::class);
    }
    
    /**
     * {@inheritDoc}
     * @see \Illuminate\Database\Eloquent\Model::save()
     */
    public function save(array $options = [])
    {
        if (! $this->property_id and $this->name) {
            
            // Load property id for given property name, or create a new one
            $property = Property::firstOrCreate(['name' => $this->name], ['comment' => $this->comment]);
            $this->property_id = $property->id;
        }
        unset($this->property);
        return parent::save($options);
    }
}
