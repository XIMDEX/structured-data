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