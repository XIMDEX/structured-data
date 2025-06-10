<?php

namespace Ximdex\StructuredData\Rules;

// use Illuminate\Contracts\Validation\Rule;
use Ximdex\StructuredData\Models\Schema;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ParentSchemaRule implements ValidationRule
{
    private ?int $id;

    public function __construct(?int $id = null)
    {
        $this->id = $id;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->id) {
            // No id means no validation needed
            return;
        }

        if ($this->hasParentId($value)) {
            $fail("The {$attribute} cannot be associated as its own parent.");
        }
    }

    private function hasParentId(array $parents): bool
    {
        foreach ($parents as $data) {
            if ($data['id'] == $this->id) {
                return true;
            }

            $schema = Schema::find($data['id']);
            if (! $schema || $schema->schemas->isEmpty()) {
                continue;
            }

            if ($this->hasParentId($schema->schemas->toArray())) {
                return true;
            }
        }

        return false;
    }
}