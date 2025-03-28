<?php

namespace Ximdex\StructuredData\Rules;

// use Illuminate\Contracts\Validation\Rule;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Ximdex\StructuredData\Models\Schema;
use Illuminate\Support\Facades\Validator;

class ParentSchemaRule implements ValidationRule
{
    private $id;
    
    public function __construct(?int $id = null)
    {
        $this->id = $id;
    }
    
    /**
     * Check if parent schemas contain the current schemaId
     * 
     * {@inheritDoc}
     * @see \Illuminate\Contracts\Validation\Rule::passes()
     */
    // public function passes($attribute, $value)
    // {
    //     if (! $this->id) {
    //         return true;
    //     }
    //     foreach ($value as $data) {
    //         if ($data['id'] == $this->id) {
    //             return false;
    //         }
    //         $schema = Schema::find($data['id']);
    //         if ($schema->schemas->isEmpty()) {
    //             continue;
    //         }
    //         if ($this->passes($attribute, $schema->schemas->toArray()) === false) {
    //             return false;
    //         }
    //     }
    //     return true;
    // }

    /**
     * {@inheritDoc}
     * @see \Illuminate\Contracts\Validation\Rule::message()
     */
    // public function message()
    // {
    //     return 'The :attribute cannot be associated as its own parent';
    // }


    /**
     * Fix deprecated \Illuminate\Contracts\Validation\Rule
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void{
        if (! $this->id) {
            return;
        }
        foreach ($value as $data) {
            if ($data['id'] == $this->id) {
                $fail('The :attribute cannot be associated as its own parent');
                return;
            }
            $schema = Schema::find($data['id']);
            if ($schema->schemas->isEmpty()) {
                continue;
            }

            $data = ['attribute' => $schema->schemas->toArray()];
            $validator = Validator::make($data, [
                'attribute' => [new ParentSchemaRule()]
            ]);

            if ($validator->fails()) {
                $fail('The :attribute cannot be associated as its own parent');
                return;
            }
        }
    }
}
