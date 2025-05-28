<?php

namespace Ximdex\StructuredData\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MaxCardinalityRule extends InAvailableTypeRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Delegate to parent validation
        parent::validate($attribute, $value, $fail);

        // Your own validation logic
        $maxCardinality = $this->availableType?->propertySchema->max_cardinality ?? 0;

        // Use $this->value (or $value depending on your context)
        $valuesToCheck = $this->value;

        if ($maxCardinality > 0 && is_countable($valuesToCheck) && count($valuesToCheck) > $maxCardinality) {
            $fail("The number of values for {$attribute} cannot be greater than {$maxCardinality}.");
        }
    }
}

// class MaxCardinalityRule extends InAvailableTypeRule
// {
//     /**
//      * Check if the count of values for the actual property is not greater than maximun allowed in the given schema
//      * 
//      * {@inheritDoc}
//      * @see \Ximdex\StructuredData\Rules\InAvailableTypeRule::passes()
//      */
//     public function passes($attribute, $value)
//     {
//         if (parent::passes($attribute, $value) === false) {
//             return false;
//         }
//         $value = $this->value;
//         $maxCardinality = $this->availableType->propertySchema->max_cardinality;
//         if ($maxCardinality > 0 && count($this->value) > $maxCardinality) {
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
//             return parent::message();
//         }
//         return "The number of values for :attribute cannot be greater than {$this->availableType->propertySchema->max_cardinality}";
//     }
// }
