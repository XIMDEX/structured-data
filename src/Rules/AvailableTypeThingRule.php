<?php

namespace Ximdex\StructuredData\Rules;

// use Illuminate\Contracts\Validation\Rule;
use Ximdex\StructuredData\Models\Schema;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AvailableTypeThingRule implements ValidationRule
{
    private ?int $schema;

    public function __construct(?int $schema = null)
    {
        $this->schema = $schema;
    }

    // Update from Rule passes-message to ValidationRule validate
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->schema && $value !== Schema::THING_TYPE) {
            $fail("The {$attribute} must be " . Schema::THING_TYPE . " when a schema is present.");
        }
    }
}
