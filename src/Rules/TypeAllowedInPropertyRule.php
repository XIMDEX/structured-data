<?php

namespace Ximdex\StructuredData\Rules;

use Illuminate\Contracts\Validation\Rule;
use Ximdex\StructuredData\Models\AvailableType;

class TypeAllowedInPropertyRule implements Rule
{
    private $availableType;
    
    /**
     * Check if the given type is allowed in the related property
     * 
     * {@inheritDoc}
     * @see \Ximdex\StructuredData\Rules\InAvailableTypeRule::passes()
     */
    public function passes($attribute, $value)
    {
        $this->availableType = AvailableType::findOrFail($value);
        $data = explode('.', $attribute);
        if ($data[1] != $this->availableType->propertySchema->name) {
            return false;
        }
        return true;
    }
    
    /**
     * {@inheritDoc}
     * @see \Ximdex\StructuredData\Rules\InAvailableTypeRule::message()
     */
    public function message()
    {
        return "The :attribute is not an allowed type (used for {$this->availableType->propertySchema->name}"
            . " in @{$this->availableType->propertySchema->schema_name})";
    }
}
