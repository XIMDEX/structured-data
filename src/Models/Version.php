<?php

namespace Ximdex\StructuredData\Models;

use Ximdex\StructuredData\Core\Model;

class Version extends Model
{
    public $hidden = ['updated_at'];
    
    public $fillable = ['name'];
}
