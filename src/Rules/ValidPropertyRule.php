<?php

namespace Ximdex\StructuredData\Rules;

use Illuminate\Contracts\Validation\Rule;
use Ximdex\StructuredData\Models\Property;
use Ximdex\StructuredData\Models\Schema;

class ValidPropertyRule implements Rule
{
    private $schema;
    
    private $property;
    
    public function __construct(int $schema = null)
    {
        $this->schema = Schema::findOrFail($schema);
    }
    
    /**
     * If the given property is not in use in the schema, do not passes the rule
     * 
     * {@inheritDoc}
     * @see \Illuminate\Contracts\Validation\Rule::passes()
     */
    public function passes($attribute, $value)
    {
        $data = explode('.', $attribute);
        if (! isset($data[1])) {
            return false;
        }
        $this->property = $data[1];
        $property = Property::where('label', $this->property)->first();
        if (! $property) {
            return false;
        }
        $res = $this->schema->properties()->where('property_id', $property->id);
        if (! $res->count()) {
            
            // The property does not appear in the schema or inherited schemas
            return false;
        }
        return true;
    }

    /**
     * {@inheritDoc}
     * @see \Illuminate\Contracts\Validation\Rule::message()
     */
    public function message()
    {
        return "Property {$this->property} is nod valid for given schema @{$this->schema->label}";
    }
}
