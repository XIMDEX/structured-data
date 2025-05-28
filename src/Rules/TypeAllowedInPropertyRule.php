<?php

namespace Ximdex\StructuredData\Rules;

// use Illuminate\Contracts\Validation\Rule;
use Ximdex\StructuredData\Models\AvailableType;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TypeAllowedInPropertyRule implements ValidationRule
{
    private ?AvailableType $availableType = null;

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $this->availableType = AvailableType::find($value);

        if (! $this->availableType) {
            $fail("The {$attribute} is not an allowed type.");
            return;
        }

        $data = explode('.', $attribute);
        if (!isset($data[1]) || $data[1] !== $this->availableType->propertySchema->label) {
            $fail("The {$attribute} is not an allowed type (used for {$this->availableType->propertySchema->label}"
                . " in @{$this->availableType->propertySchema->schema_label}).");
        }
    }
}

// class TypeAllowedInPropertyRule implements Rule
// {
//     private $availableType;
    
//     /**
//      * Check if the given type is allowed in the related property
//      * 
//      * {@inheritDoc}
//      * @see \Ximdex\StructuredData\Rules\InAvailableTypeRule::passes()
//      */
//     public function passes($attribute, $value)
//     {
//         $this->availableType = AvailableType::find($value);
//         if (! $this->availableType) {
//             return false;
//         }
//         $data = explode('.', $attribute);
//         if ($data[1] != $this->availableType->propertySchema->label) {
//             return false;
//         }
//         return true;
//     }
    
//     /**
//      * {@inheritDoc}
//      * @see \Ximdex\StructuredData\Rules\InAvailableTypeRule::message()
//      */
//     public function message()
//     {
//         if (! $this->availableType) {
//             return null;
//         }
//         return "The :attribute is not an allowed type (used for {$this->availableType->propertySchema->label}"
//             . " in @{$this->availableType->propertySchema->schema_label})";
//     }
// }
