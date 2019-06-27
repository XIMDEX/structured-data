<?php

namespace Ximdex\StructuredData\Core;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Support\Arr;

class Model extends BaseModel
{
    protected static $prefix = '';
    
    public static $except = [];

    public function __construct(array $attributes = [])
    {
        self::modelPrefix();
        parent::__construct($attributes);
    }
    
    public static function create(array $attributes = [])
    {
        return static::query()->create($attributes);
    }
    
    /**
     * {@inheritDoc}
     * @see \Illuminate\Database\Eloquent\Model::update()
     */
    public function update(array $attributes = [], array $options = [])
    {
        $attributes = Arr::except($attributes, static::$except);
        return parent::update($attributes, $options);
    }
    
    /**
     * {@inheritDoc}
     * @see \Illuminate\Database\Eloquent\Model::getTable()
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
