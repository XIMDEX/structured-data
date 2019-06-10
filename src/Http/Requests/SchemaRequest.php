<?php

namespace Ximdex\StructuredData\Requests;

use Ximdex\StructuredData\Models\Schema;
use Illuminate\Support\Facades\Request;
use Ximdex\StructuredData\Rules\SchemaInheritationRule;

class SchemaRequest extends ApiRequest
{   
    /**
     * {@inheritDoc}
     * @see \Ximdex\StructuredData\Requests\ApiRequest::rules()
     */
    public function rules(): array
    {
        if ($this->schema) {
            $id = $this->schema->id;
        } else {
            $id = null;
        }
        parent::rules();
        $method = Request::method();
        switch ($method) {
            
            // store | update
            case 'POST':
            case 'PUT':
                $this->addRule('name', 'required');
            case 'PATCH':
                $this->addRule('name', 'max:50');
                $this->addRule('name', 'unique:' . (new Schema)->getTable() . ',name,' . $id);
                $this->addRule('comment', 'nullable');
                $this->addRule('inherited_schemas', 'array');
                $this->addRule('inherited_schemas.*.id', 'Numeric');
                $this->addRule('inherited_schemas.*.id', 'gte:1');
                $this->addRule('inherited_schemas.*.priority', 'Numeric');
                $this->addRule('inherited_schemas.*.priority', 'gte:1');
                $this->addRule('*', 'bail');
                $this->addRule('inherited_schemas.*.id', 'exists:' . (New Schema)->getTable() . ',id');
                $this->addRule('inherited_schemas', new SchemaInheritationRule($id));
        }
        return $this->validations;
    }
}
