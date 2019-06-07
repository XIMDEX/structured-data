<?php

namespace Ximdex\StructuredData\Requests;

use Illuminate\Support\Facades\Request;
use Ximdex\StructuredData\Models\Schema;
use Ximdex\StructuredData\Rules\ValueInAvailableType;
use Ximdex\StructuredData\Rules\EntityInAvailableType;

class EntityRequest extends ApiRequest
{   
    /**
     * {@inheritDoc}
     * @see \Ximdex\StructuredData\Requests\ApiRequest::rules()
     */
    public function rules(): array
    {
        parent::rules();
        $method = Request::method();
        switch ($method) {
            
            // show
            case 'GET':
                $this->addRule('uid', 'boolean');
                break;
                
            // store | update
            case 'POST':
                $this->addRule('schema_id', 'required');
                $this->addRule('properties', 'required');
                $this->addRule('properties.*.type', 'required');
                $this->addRule('properties.*.values', 'required');
            case 'PUT':
            case 'PATCH':
                $this->addRule('schema_id', 'numeric');
                $this->addRule('schema_id', 'gte:1');
                $this->addRule('properties', 'array');
                $this->addRule('properties.*.type', 'numeric');
                $this->addRule('properties.*.type', 'gte:1');
                $this->addRule('properties.*.values', 'array');
                $this->addRule('*', 'bail');
                $this->addRule('schema_id', 'exists:' . (new Schema)->getTable() . ',id');
                $this->addRule('properties.*', new ValueInAvailableType());
                $this->addRule('properties.*', new EntityInAvailableType());
        }
        return $this->validations;
    }
}
