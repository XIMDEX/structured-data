<?php

namespace Ximdex\StructuredData\Requests;

use Illuminate\Support\Facades\Request;
use Ximdex\StructuredData\Models\Schema;
use Ximdex\StructuredData\Models\Property;
use Ximdex\StructuredData\Rules\SchemaPropertyDuplication;

class PropertySchemaRequest extends ApiRequest
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
            
            // store | update
            case 'POST':
                $this->addRule('name', 'required_without:property_id');
                $this->addRule('property_id', 'required_without:name');
                $this->addRule('schema_id', 'required');
            case 'PUT':
            case 'PATCH':
                $this->addRule('name', 'max:50');
                $this->addRule('order', 'numeric');
                $this->addRule('order', 'gte:1');
                $this->addRule('schema_id', 'numeric');
                $this->addRule('schema_id', 'gte:1');
                $this->addRule('schema_id', 'exists:' . (new Schema)->getTable() . ',id');
                $this->addRule('property_id', 'numeric');
                $this->addRule('property_id', 'gte:1');
                $this->addRule('property_id', 'exists:' . (new Property)->getTable() . ',id');
                $this->addRule('min_cardinality', 'numeric');
                $this->addRule('min_cardinality', 'gte:0');
                $this->addRule('max_cardinality', 'numeric');
                $this->addRule('max_cardinality', 'nullable');
                $this->addRule('max_cardinality', 'gte:1');
                $this->addRule('default_value', 'max:5000');
                $this->addRule('*', 'bail');
                $this->addRule('schema_id', new SchemaPropertyDuplication(
                    $this->get('schema_id'), 
                    $this->get('property_id'),
                    $this->get('name')
                ));
        }
        return $this->validations;
    }
}
