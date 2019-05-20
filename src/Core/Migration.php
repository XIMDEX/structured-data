<?php

namespace Ximdex\StructuredData\Core;

use Illuminate\Database\Migrations\Migration as BaseMigration;

class Migration extends BaseMigration
{
    protected $baseName = null;
    
    public function __construct()
    {
        $this->baseName = config('structureddata.module.name', '');
        if (! empty($this->baseName)) {
            $this->baseName .= '_';
        }
    }
}
