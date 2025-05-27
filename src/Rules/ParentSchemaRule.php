<?php

namespace Ximdex\StructuredData\Rules;

use Illuminate\Contracts\Validation\Rule;
use Ximdex\StructuredData\Models\Schema;

class ParentSchemaRule implements Rule
{
    private $id;
    
    public function __construct(int $id = null)
    {
        $this->id = $id;
    }
    
    /**
     * Check if parent schemas contain the current schemaId
     * 
     * {@inheritDoc}
     * @see \Illuminate\Contracts\Validation\Rule::passes()
     */
    public function passes($attribute, $value)
    {
        if (! $this->id) {
            return true;
        }
        foreach ($value as $data) {
            if ($data['id'] == $this->id) {
                return false;
            }
            $schema = Schema::find($data['id']);
            if ($schema->schemas->isEmpty()) {
                continue;
            }
            if ($this->passes($attribute, $schema->schemas->toArray()) === false) {
                return false;
            }
        }
        return true;
    }

    /**
     * {@inheritDoc}
     * @see \Illuminate\Contracts\Validation\Rule::message()
     */
    public function message()
    {
        return 'The :attribute cannot be associated as its own parent';
    }
}
