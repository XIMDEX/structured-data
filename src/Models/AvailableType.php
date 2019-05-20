<?php

namespace Ximdex\StructuredData\Models;

use Ximdex\StructuredData\Core\Model;

class AvailableType extends Model
{
    public function schema()
    {
        return $this->belongsTo(Schema::class);
    }
}
