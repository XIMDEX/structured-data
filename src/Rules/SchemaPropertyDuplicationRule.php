<?php

namespace Ximdex\StructuredData\Rules;

// use Illuminate\Contracts\Validation\Rule;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Ximdex\StructuredData\Models\Property;
use Ximdex\StructuredData\Models\PropertySchema;

class SchemaPropertyDuplicationRule implements ValidationRule
{
    private $schema;
    
    private $property;
    
    public function __construct(?int $schema = null, ?int $property = null, ?string $label = null)
    {
        $this->schema = $schema;
        if (! $property) {
            if ($prop = Property::where('label', $label)->first()) {
                $this->property = $prop->id;
            }
        } else {
            $this->property = $property;
        }
    }
    
    /**
     * If the given property is already in the schema, do not passes the rule
     * 
     * {@inheritDoc}
     * @see \Illuminate\Contracts\Validation\Rule::passes()
     */
    // public function passes($attribute, $value)
    // {
    //     if (! $this->property) {
    //         return true;
    //     }
    //     if (PropertySchema::where('schema_id', $this->schema)->where('property_id', $this->property)->first()) {
    //         return false;
    //     }
    //     return true;
    // }

    /**
     * {@inheritDoc}
     * @see \Illuminate\Contracts\Validation\Rule::message()
     */
    // public function message()
    // {
    //     return 'This property already exists for given schema';
    // }


    /**
     * Fix deprecated \Illuminate\Contracts\Validation\Rule
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void{
        if (! $this->property) {
            return;
        }
        if (PropertySchema::where('schema_id', $this->schema)->where('property_id', $this->property)->first()) {
            $fail('This property already exists for given schema');
        }
    }
}
