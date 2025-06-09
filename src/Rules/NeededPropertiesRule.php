<?php

namespace Ximdex\StructuredData\Rules;

// use Illuminate\Contracts\Validation\Rule;
use Ximdex\StructuredData\Models\Schema;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NeededPropertiesRule implements ValidationRule
{
    private $schema;
    private $property;

    public function __construct(?int $schema = null)
    {
        $this->schema = Schema::findOrFail($schema);
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        foreach ($this->schema->properties() as $property) {
            if ($property->min_cardinality == 1 && ! isset($value[$property->label])) {
                $this->property = $property->label;
                $fail("Some properties (like {$this->property}) are needed for given schema @{$this->schema->label}");
                // Stop checking after first failure (optional)
                return;
            }
        }
    }
}