<?php

namespace Ximdex\StructuredData\Rules;

use Illuminate\Contracts\Validation\Rule;
use Ximdex\StructuredData\Models\Property;
use Ximdex\StructuredData\Models\PropertySchema;

class SchemaPropertyDuplication implements Rule
{
    private $schema;
    
    private $property;
    
    public function __construct(int $schema = null, int $property = null, string $name = null)
    {
        $this->schema = $schema;
        if (! $property) {
            if ($prop = Property::where('name', $name)->first()) {
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
    public function passes($attribute, $value)
    {
        if (! $this->property) {
            return true;
        }
        if (PropertySchema::where('schema_id', $this->schema)->where('property_id', $this->property)->first()) {
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
        return 'This property already exists for given schema';
    }
}
