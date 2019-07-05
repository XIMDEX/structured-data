<?php

namespace Ximdex\StructuredData\Requests;

use Ximdex\StructuredData\Models\Schema;
use Ximdex\StructuredData\Rules\ParentSchemaRule;

class SchemaRequest extends ApiRequest
{   
    /**
     * {@inheritDoc}
     * @see \Ximdex\StructuredData\Requests\ApiRequest::rules()
     */
    public function rules(): array
    {
        parent::rules();
        if ($this->schema) {
            $id = $this->schema->id;
        } else {
            $id = null;
        }
        switch ($this->method) {
            case 'POST':
            case 'PUT':
                $this->addRule('label', 'required');
            case 'PATCH':
                $this->addRule('label', 'max:50');
                $this->addRule('comment', 'nullable');
                $this->addRule('schemas', 'array');
                $this->addRule('schemas.*.id', 'Numeric');
                $this->addRule('schemas.*.id', 'gte:1');
                $this->addRule('schemas.*.priority', 'Numeric');
                $this->addRule('schemas.*.priority', 'gte:1');
                $this->addRule('*', 'bail');
                $this->addRule('label', 'unique:' . (new Schema)->getTable() . ',label,' . $id);
                $this->addRule('schemas.*.id', 'exists:' . (New Schema)->getTable() . ',id');
                $this->addRule('schemas', new ParentSchemaRule($id));
            default:
                break;
        }
        return $this->validations;
    }
}
