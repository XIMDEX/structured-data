<?php

namespace Ximdex\StructuredData\Rules;

use Illuminate\Contracts\Validation\Rule;
use Ximdex\StructuredData\Models\AvailableType;
use Ximdex\StructuredData\Models\Entity;
use Ximdex\StructuredData\Models\Schema;

class EntityInAvailableType implements Rule
{
    private $availableType;
    
    public function __construct(int $availableTypeId)
    {
        $this->availableType = AvailableType::find($availableTypeId);
    }
    
    /**
     * Check if the given value is supported in the property available type 
     * 
     * {@inheritDoc}
     * @see \Illuminate\Contracts\Validation\Rule::passes()
     */
    public function passes($attribute, $value)
    {
        if ($this->availableType->type != Schema::THING_TYPE) {
            
            // Type only support an entity
            return false;
        }
        $entity = Entity::findOrFail($value);
        if ($entity->schema_id != $this->availableType->schema_id) {
            
            // The schema for the given entity is different for this available type
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
        return "The :attribute must be a type @{$this->availableType->schemaName} for {$this->availableType->propertySchema->name} property";
    }
}
