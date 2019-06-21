<?php

namespace Ximdex\StructuredData\Rules;

use Ximdex\StructuredData\Models\Entity;
use Ximdex\StructuredData\Models\Schema;

class EntityInAvailableTypeRule extends InAvailableTypeRule
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
        if ($this->availableType->schema_id == Schema::THING_TYPE) {
            
            // If the schema type is Thing every schema is valid for entity value
            return true;
        }
        foreach ($value as $id) {
            
            // If value contains the type, get only the value given
            if (is_array($id)) {
                $id = $id['value'];
            }
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
        if (! $this->availableType) {
            return parent::message();
        }
        return "The :attribute value must be a type @{$this->availableType->schemaName} for {$this->availableType->propertySchema->name} property";
    }
}
