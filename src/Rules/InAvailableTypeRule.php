<?php

namespace Ximdex\StructuredData\Rules;

use Illuminate\Contracts\Validation\Rule;
use Ximdex\StructuredData\Models\AvailableType;

abstract class InAvailableTypeRule implements Rule
{
    protected $availableType;
    
    protected $value;
    
    protected $supportMultiValidation = false;
    
    public function __construct(?int $availableTypeId = null)
    {
        if ($availableTypeId) {
            $this->availableType = AvailableType::findOrFail($availableTypeId);
        } else {
            $this->supportMultiValidation = true;
        }
    }
    
    /**
     * Load the property available type in given parameters
     *
     * {@inheritDoc}
     * @see \Illuminate\Contracts\Validation\Rule::passes()
     */
    public function passes($attribute, $value)
    {
        if (isset($value['type']) and isset($value['values'])) {
            if (! $this->availableType or $this->availableType->id != $value['type']) {
                $this->availableType = AvailableType::find($value['type']);
                if (! $this->availableType) {
                    return false;
                }
            }
            $this->value = $value['values'];
        } elseif (! $this->availableType) {
            return false;
        } else {
            $this->value = $value;
        }
        if (! is_array($this->value)) {
            $this->value = [$value];
        }
        return true;
    }
    
    /**
     * {@inheritDoc}
     * @see \Illuminate\Contracts\Validation\Rule::message()
     */
    public function message()
    {
        return "The :attribute must be a valid type for property";
    }
}
