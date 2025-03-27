<?php

namespace Ximdex\StructuredData\Rules;

use Ximdex\StructuredData\Models\Item;
use Ximdex\StructuredData\Models\Schema;
use Closure;
use Illuminate\Support\Facades\Validator;

class ItemInAvailableTypeRule extends InAvailableTypeRule
{   
    /**
     * Check if the given value is supported in the property available type
     * 
     * {@inheritDoc}
     * @see \Ximdex\StructuredData\Rules\InAvailableTypeRule::passes()
     */
    // public function passes($attribute, $value)
    // {
    //     if (parent::passes($attribute, $value) === false) {
    //         return false;
    //     }
    //     $value = $this->value;
    //     if ($this->availableType->type != Schema::THING_TYPE) {
            
    //         // Type only support an item
    //         return $this->supportMultiValidation;
    //     }
    //     foreach ($value as $id) {
            
    //         // If value contains the type, get only the value given
    //         if (is_array($id)) {
    //             $id = $id['values'];
    //         }
    //         if (! is_numeric($id)) {
    //             return false;
    //         }
    //         $item = Item::find($id);
    //         if (! $item) {
    //             return false;
    //         }
    //         if (! $item->schema->extends($this->availableType->schema)) {

    //             // The schemas for the given item are not supported for this available type
    //             return false;
    //         }
    //     }
    //     return true;
    // }

    /**
     * {@inheritDoc}
     * @see \Ximdex\StructuredData\Rules\InAvailableTypeRule::message()
     */
    // public function message()
    // {
    //     if (! $this->availableType) {
    //         return parent::message();
    //     }
    //     return "The :attribute value must be or extends a type @{$this->availableType->schema_label} for "
    //         . "{$this->availableType->propertySchema->label} property";
    // }


    /**
     * Fix deprecated \Illuminate\Contracts\Validation\Rule
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void{
        $data = ['attribute' => $value];
        $validator = Validator::make($data, [
            'attribute' => [new InAvailableTypeRule]
        ]);

        if ($validator->fails()) {
            $fail('The :attribute value must be or extends a type @{$this->availableType->schema_label} for '
            . '{$this->availableType->propertySchema->label} property');
            return;
        }
        $value = $this->value;
        if ($this->availableType->type != Schema::THING_TYPE) {
            // Type only support an item
            if(!$this->supportMultiValidation){
                $fail('The :attribute value must be or extends a type @{$this->availableType->schema_label} for '
                . '{$this->availableType->propertySchema->label} property');
                return;
            }
        }
        foreach ($value as $id) {
            // If value contains the type, get only the value given
            if (is_array($id)) {
                $id = $id['values'];
            }
            if (! is_numeric($id)) {
                $fail('The :attribute value must be or extends a type @{$this->availableType->schema_label} for '
                . '{$this->availableType->propertySchema->label} property');
                return;
            }
            $item = Item::find($id);
            if (! $item) {
                $fail('The :attribute value must be or extends a type @{$this->availableType->schema_label} for '
                . '{$this->availableType->propertySchema->label} property');
                return;
            }
            if (! $item->schema->extends($this->availableType->schema)) {
                // The schemas for the given item are not supported for this available type
                $fail('The :attribute value must be or extends a type @{$this->availableType->schema_label} for '
                . '{$this->availableType->propertySchema->label} property');
            return;
            }
        }
    }
}
