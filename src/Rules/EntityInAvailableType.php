<?php

namespace Ximdex\StructuredData\Rules;

use Ximdex\StructuredData\Models\Entity;
use Ximdex\StructuredData\Models\Schema;

class EntityInAvailableType extends InAvailableTypeRule
{   
    /**
     * Check if the given value is supported in the property available type
     * 
     * {@inheritDoc}
     * @see \Ximdex\StructuredData\Rules\InAvailableTypeRule::passes()
     */
    public function passes($attribute, $value)
    {
        if (parent::passes($attribute, $value) === false) {
            return false;
        }
        $value = $this->value;
        if ($this->availableType->type != Schema::THING_TYPE) {
            
            // Type only support an entity
            return $this->supportMultiValidation;
        }
        if (! is_array($value)) {
            $value = [$value];
        }
        foreach ($value as $id) {
            if (! is_numeric($id)) {
                return false;
            }
            $entity = Entity::find($id);
            if (! $entity) {
                return false;
            }
            if ($entity->schema_id != $this->availableType->schema_id) {
                
                // The schema for the given entity is different for this available type
                return false;
            }
        }
        return true;
    }

    /**
     * {@inheritDoc}
     * @see \Ximdex\StructuredData\Rules\InAvailableTypeRule::message()
     */
    public function message()
    {
        return "The :attribute must be a type @{$this->availableType->schemaName} for {$this->availableType->propertySchema->name} property";
    }
}
