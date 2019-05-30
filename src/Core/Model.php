<?php

namespace Ximdex\StructuredData\Core;

use Illuminate\Database\Eloquent\Model as BaseModel;

class Model extends BaseModel
{
    protected static $prefix = '';

    public function __construct(array $attributes = [])
    {
        self::modelPrefix();
        parent::__construct($attributes);
    }
    
    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable()
    {
        if ($this->table) {
            return $this->table;
        }
        return self::$prefix . parent::getTable();
    }
    
    protected static function modelPrefix()
    {
        if (! self::$prefix) {
            self::$prefix = config('structureddata.module.name', '');
            if (! empty(self::$prefix)) {
                self::$prefix .= '_';
            }
        }
    }
}

