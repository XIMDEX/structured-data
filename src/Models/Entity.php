<?php

namespace Ximdex\StructuredData\Models;

use Ximdex\StructuredData\Core\Model;
use Ximdex\StructuredData\src\Models\Value;

class Entity extends Model
{   
    public function schema()
    {
        return $this->belongsTo(Schema::class);
    }
   
    public function nodes()
    {
        return $this->belongsToMany(Node::class, (new EntityNode)->getTable());
    }
    
    public function values()
    {
        return $this->hasMany(Value::class);
    }
}
