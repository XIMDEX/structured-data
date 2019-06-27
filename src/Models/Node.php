<?php

namespace Ximdex\StructuredData\Models;

use Ximdex\StructuredData\Core\Model;

class Node extends Model
{
    public function entities()
    {
        return $this->belongsToMany(Item::class, (new ItemNode)->getTable());
    }
}
