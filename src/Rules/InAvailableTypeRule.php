<?php

namespace Ximdex\StructuredData\Rules;

// use Illuminate\Contracts\Validation\Rule;
use Ximdex\StructuredData\Models\AvailableType;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

abstract class InAvailableTypeRule implements ValidationRule
{
    protected $availableType;
    
    protected $value;
    
    protected $supportMultiValidation = false;

    public function __construct(?int $availableTypeId = null)
    {
        if ($availableTypeId) {
            $this->availableType = AvailableType::findOrFail($availableTypeId);
        } else {
            $this->supportMultiValidation = true;
        }
    }

    // Update from Rule passes-message to ValidationRule validate
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (isset($value['type']) and isset($value['values'])) {
            if (! $this->availableType or $this->availableType->id != $value['type']) {
                $this->availableType = AvailableType::find($value['type']);
                if (! $this->availableType) {
                    $fail("The {$attribute} must be a valid type for property");
                }
            }
            $this->value = $value['values'];
        } elseif (! $this->availableType) {
            $fail("The {$attribute} must be a valid type for property");
        } else {
            $this->value = $value;
        }
        if (! is_array($this->value)) {
            $this->value = [$value];
        }
    }

}