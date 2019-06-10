<?php

namespace Ximdex\StructuredData\Rules;

class MinCardinalityRule extends InAvailableTypeRule
{
    /**
     * {@inheritDoc}
     * @see \Ximdex\StructuredData\Rules\InAvailableTypeRule::passes()
     */
    public function passes($attribute, $value)
    {
        if (parent::passes($attribute, $value) === false) {
            return false;
        }
        $value = $this->value;
        $minCardinality = $this->availableType->propertySchema->min_cardinality;
        if ($minCardinality > 0 and count($this->value) < $minCardinality) {
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
        return "The number of values for :attribute must be greater or equal than {$this->availableType->propertySchema->min_cardinality}";
    }
}
