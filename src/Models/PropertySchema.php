<?php

namespace Ximdex\StructuredData\Models;

use Illuminate\Support\Str;
use Ximdex\StructuredData\Core\Model;

class PropertySchema extends Model
{
    public $hidden = ['created_at', 'updated_at', 'property', 'property_id'];
    
    public $appends = ['name', 'comment'];
    
    public $fillable = ['name', 'min_cardinality', 'max_cardinality', 'default_value', 'order', 'schema_id', 'property_id'];
    
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
    
    public function availableTypes()
    {
        return $this->hasMany(AvailableType::class);
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
