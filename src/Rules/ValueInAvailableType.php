<?php

namespace Ximdex\StructuredData\Rules;

use Ximdex\StructuredData\Models\Schema;
use Illuminate\Support\Carbon;

class ValueInAvailableType extends InAvailableTypeRule
{
    const TIME_FORMAT = 'h:i:s';
    
    CONST DATE_FORMAT = 'd-m-Y';
    
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
        if ($this->availableType->type == Schema::THING_TYPE) {
            
            // Type only support an entity
            return $this->supportMultiValidation;
        }
        switch ($this->availableType->type) {
            
            // Check if value is a valid boolean type
            case 'Boolean':
                if (! in_array($value, ['true', 'false', '1', '0'])) {
                    return false;
                }
                break;

            // Check if value is a valid number type
            case 'Number':
                if (! is_numeric($value)) {
                    return false;
                }
                break;
                
            // Check if value is a valid date type
            case 'Date':
                $format = self::DATE_FORMAT;
            
            // Check if value is a valid time type
            case 'Time':
                $format = self::TIME_FORMAT;
            
            // Check if value is a valid date time type
            case 'DateTime':
                $format = self::DATE_FORMAT . ' ' . self::TIME_FORMAT;
                
            // Check the format for date | time
            case 'Date':
            case 'Time':
            case 'DateTime':
                try {
                    $value = Carbon::createFromFormat($format, $value);
                    if ($value === false) {
                        return false;
                    }
                } catch (\Exception $e) {
                    return false;
                }
                break;
        }
        return true;
    }
    
    /**
     * {@inheritDoc}
     * @see \Ximdex\StructuredData\Rules\InAvailableTypeRule::message()
     */
    public function message()
    {
        return "The :attribute must be a valid type ({$this->availableType->type}) for {$this->availableType->propertySchema->name} property";
    }
}
