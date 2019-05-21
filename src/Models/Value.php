<?php

namespace Ximdex\StructuredData\src\Models;

use Ximdex\StructuredData\Core\Model;
use Ximdex\StructuredData\Models\AvailableType;
use Ximdex\StructuredData\Models\Entity;

class Value extends Model
{
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
