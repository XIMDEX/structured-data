<?php

namespace Ximdex\StructuredData\Rules;

class MaxCardinalityRule extends InAvailableTypeRule
{
    /**
     * Check if the count of values for the actual property is not greater than maximun allowed in the given schema
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
        $maxCardinality = $this->availableType->propertySchema->max_cardinality;
        if ($maxCardinality > 0 && count($this->value) > $maxCardinality) {
            return false;
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
        return "The number of values for :attribute cannot be greater than {$this->availableType->propertySchema->max_cardinality}";
    }
}
