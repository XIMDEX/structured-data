<?php

namespace Ximdex\StructuredData\Rules;

// use Illuminate\Contracts\Validation\Rule;
use Ximdex\StructuredData\Models\Property;
use Ximdex\StructuredData\Models\Schema;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidPropertyRule implements ValidationRule
{
    private $schema;
    private ?string $property = null;

    public function __construct(?int $schema = null)
    {
        $this->schema = Schema::findOrFail($schema);
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $data = explode('.', $attribute);
        if (!isset($data[1])) {
            $fail("Property is missing in attribute format.");
            return;
        }

        $this->property = $data[1];

        $prop = Property::where('label', $this->property)->first();
        if (! $prop) {
            $fail("Property {$this->property} is not valid for given schema @{$this->schema->label}.");
            return;
        }

        $res = $this->schema->properties()->where('property_id', $prop->id);
        if (! $res->count()) {
            // The property does not appear in the schema or inherited schemas
            $fail("Property {$this->property} is not valid for given schema @{$this->schema->label}.");
            return;
        }
    }
}