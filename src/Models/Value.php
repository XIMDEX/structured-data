<?php

namespace Ximdex\StructuredData\Models;

use Ximdex\StructuredData\Core\Model;

class Value extends Model
{
    public $casts = ['value' => 'type'];
    
    public $fillable = ['available_type_id', 'entity_id', 'ref_entity_id', 'value', 'position'];
    
    public $hidden = ['created_at', 'updated_at'];
    
    public static $except = ['available_type_id', 'entity_id'];
    
    protected function getCastType($key)
    {
        if ($key == 'value' and $this->type != Schema::THING_TYPE) {
            if ($this->type == 'Number') {
                return (int) $this->value;
            }
            return $this->type;
        }
        return parent::getCastType($key);
    }
    
    public function entity()
    {
        return $this->belongsTo(Entity::class);
    }
    
    public function referenceEntity()
    {
        return $this->belongsTo(Entity::class, 'ref_entity_id');
    }
    
    public function availableType()
    {
        return $this->belongsTo(AvailableType::class);
    }
}
