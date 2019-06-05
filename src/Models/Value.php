<?php

namespace Ximdex\StructuredData\src\Models;

use Ximdex\StructuredData\Core\Model;
use Ximdex\StructuredData\Models\AvailableType;
use Ximdex\StructuredData\Models\Entity;
use Ximdex\StructuredData\Models\Schema;
// use Ximdex\StructuredData\Models\Schema;

class Value extends Model
{
    public $casts = ['value' => 'type'];
    
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
