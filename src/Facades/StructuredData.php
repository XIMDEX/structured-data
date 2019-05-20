<?php

namespace Ximdex\StructuredData\Facades;

use Illuminate\Support\Facades\Facade;

class StructuredData extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'structureddata';
    }
}
