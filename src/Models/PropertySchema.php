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
    
    /**
     * {@inheritDoc}
     * @see \Illuminate\Database\Eloquent\Model::save()
     */
    public function save(array $options = [])
    {
        if (! $this->property_id and $this->name) {
            
            // load property id for given property name
            $property = Property::where('name', $this->name)->first();
            if (! $property) {
                
                // Create a new property
                $property = new Property();
                $property->name = $this->name;
                $property->save();
            }
            $this->property_id = $property->id;
        }
        return parent::save($options);
    }
}
