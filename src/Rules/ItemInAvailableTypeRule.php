<?php

namespace Ximdex\StructuredData\Rules;

use Ximdex\StructuredData\Models\Item;
use Ximdex\StructuredData\Models\Schema;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ItemInAvailableTypeRule extends InAvailableTypeRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // If no availableType, delegate validation to parent
        if (! $this->availableType) {
            parent::validate($attribute, $value, $fail);
            return;
        }

        // Call parent validation first
        parent::validate($attribute, $value, $fail);

        // Additional validation specific to this rule
        $valueToCheck = $this->value;

        if ($this->availableType->type != Schema::THING_TYPE) {
            if (! $this->supportMultiValidation) {
                $fail("The {$attribute} fails multi-validation support check.");
            }
            return; // stop further validation
        }

        foreach ($valueToCheck as $id) {
            if (is_array($id)) {
                $id = $id['values'];
            }

            if (! is_numeric($id)) {
                $fail("The {$attribute} must be numeric.");
                continue;
            }

            $item = Item::find($id);
            if (! $item) {
                $fail("The item with ID {$id} does not exist.");
                continue;
            }

            if (! $item->schema->extends($this->availableType->schema)) {
                $fail("The {$attribute} value must be or extends a type @{$this->availableType->schema_label} for "
                    . "{$this->availableType->propertySchema->label} property.");
            }
        }
    }
}