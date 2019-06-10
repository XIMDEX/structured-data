<?php

namespace Ximdex\StructuredData\Rules;

use Illuminate\Contracts\Validation\Rule;
use Ximdex\StructuredData\Models\Schema;

class NeededPropertiesRule implements Rule
{
    private $schema;
    
    private $property;
    
    public function __construct(int $schema = null)
    {
        $this->schema = Schema::findOrFail($schema);
    }
    
    /**
     * Check if the properties are all the needed for the given schema (minimun cardinality is 1)
     * 
     * {@inheritDoc}
     * @see \Illuminate\Contracts\Validation\Rule::passes()
     */
    public function passes($attribute, $value)
    {
        foreach ($this->schema->properties() as $property) {
            if ($property->min_cardinality == 1 and ! isset($value[$property->name])) {
                $this->property = $property->name;
                return false;
            }
        }
        return true;
    }

    /**
     * {@inheritDoc}
     * @see \Illuminate\Contracts\Validation\Rule::message()
     */
    public function message()
    {
        return "Some properties (like {$this->property}) are needed for given schema @{$this->schema->name}";
    }
}
