<?php

namespace Ximdex\StructuredData\Models;

use Ximdex\StructuredData\Core\Model;

class Property extends Model
{
    const SIMPLE_TYPES = ['Thing', 'Boolean', 'Date', 'DateTime', 'Number', 'Text', 'Time'];
    
    public $fillable = ['name'];
}
