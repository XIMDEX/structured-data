<?php

namespace Ximdex\StructuredData\Requests;

use Illuminate\Support\Facades\Request;
use Ximdex\StructuredData\Models\Schema;
use Ximdex\StructuredData\Rules\ValidPropertyRule;
use Ximdex\StructuredData\Rules\ValueInAvailableTypeRule;
use Ximdex\StructuredData\Rules\EntityInAvailableTypeRule;
use Ximdex\StructuredData\Rules\NeededPropertiesRule;
use Ximdex\StructuredData\Rules\MinCardinalityRule;
use Ximdex\StructuredData\Rules\MaxCardinalityRule;

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
                $this->addRule('properties.*', new ValidPropertyRule($this->get('schema_id')));
                $this->addRule('properties', new NeededPropertiesRule($this->get('schema_id')));
                $this->addRule('*', 'bail');
                $this->addRule('properties.*', new ValueInAvailableTypeRule());
                $this->addRule('properties.*', new EntityInAvailableTypeRule());
                $this->addRule('*', 'bail');
                $this->addRule('properties.*', new MinCardinalityRule());
                $this->addRule('properties.*', new MaxCardinalityRule());
        }
        return $this->validations;
    }
}
