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

// class NeededPropertiesRule implements Rule
// {
//     private $schema;
    
//     private $property;
    
//     public function __construct(?int $schema = null)
//     {
//         $this->schema = Schema::findOrFail($schema);
//     }
    
//     /**
//      * Check if the properties are all the needed for the given schema (minimun cardinality is 1)
//      * 
//      * {@inheritDoc}
//      * @see \Illuminate\Contracts\Validation\Rule::passes()
//      */
//     public function passes($attribute, $value)
//     {
//         foreach ($this->schema->properties() as $property) {
//             if ($property->min_cardinality == 1 and ! isset($value[$property->label])) {
//                 $this->property = $property->label;
//                 return false;
//             }
//         }
//         return true;
//     }

//     /**
//      * {@inheritDoc}
//      * @see \Illuminate\Contracts\Validation\Rule::message()
//      */
//     public function message()
//     {
//         return "Some properties (like {$this->property}) are needed for given schema @{$this->schema->label}";
//     }
// }
