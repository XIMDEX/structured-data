<?php

namespace Ximdex\StructuredData\Models;

use Ximdex\StructuredData\Core\Model;

class Property extends Model
{
    public $fillable = ['name', 'comment', 'version_id'];
    
    public function schemaProperties()
    {
        return $this->hasMany(PropertySchema::class);
    }
}
