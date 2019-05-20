<?php

namespace Ximdex\StructuredData\Models;

use Ximdex\StructuredData\Core\Model;

class Node extends Model
{
    public function entities()
    {
        return $this->belongsToMany(Entity::class, (new EntityNode)->getTable());
    }
}
