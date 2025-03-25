<?php

namespace Ximdex\StructuredData\Rules;

use Illuminate\Contracts\Validation\Rule;
use Ximdex\StructuredData\Models\Schema;

class AvailableTypeThingRule implements Rule
{
    private $schema;
    
    public function __construct(?int $schema = null)
    {
        $this->schema = $schema;
    }
    
    /**
     * Check if the type field is Thing when a schema id was given
     * 
     * {@inheritDoc}
     * @see \Illuminate\Contracts\Validation\Rule::passes()
     */
    public function passes($attribute, $value)
    {
        if ($this->schema and $value != Schema::THING_TYPE) {
            return false;
        }
        return true;
    }

    /**
     * {@inheritDoc}
     * @see \Illuminate\Contracts\Validation\Rule::message()
     */
    public function message()
    {
        return 'The :attribute must be ' . Schema::THING_TYPE . ' when a schema is present';
    }
}
