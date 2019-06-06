<?php

namespace Ximdex\StructuredData\Requests;

use Illuminate\Support\Facades\Request;
use Ximdex\StructuredData\Models\AvailableType;
use Ximdex\StructuredData\Models\Entity;
use Ximdex\StructuredData\Rules\ValueInAvailableType;
use Ximdex\StructuredData\Rules\EntityInAvailableType;

class ValueRequest extends ApiRequest
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
                $this->addRule('available_type_id', 'required');
                $this->addRule('available_type_id', 'numeric');
                $this->addRule('available_type_id', 'gte:1');
                $this->addRule('available_type_id', 'exists:' . (new AvailableType)->getTable() . ',id');
                $this->addRule('entity_id', 'required');
                $this->addRule('entity_id', 'numeric');
                $this->addRule('entity_id', 'gte:1');
                $this->addRule('entity_id', 'exists:' . (new Entity)->getTable() . ',id');
                $this->addRule('value', 'required_without:ref_entity_id');
                $this->addRule('ref_entity_id', 'required_without:value');
            case 'PUT':
            case 'PATCH':
                $this->addRule('ref_entity_id', 'numeric');
                $this->addRule('ref_entity_id', 'gte:1');
                $this->addRule('ref_entity_id', 'exists:' . (new Entity)->getTable() . ',id');
                $this->addRule('position', 'numeric');
                $this->addRule('position', 'gte:1');
                $this->addRule('*', 'bail');
                $this->addRule('value', new ValueInAvailableType($this->get('available_type_id')));
                $this->addRule('ref_entity_id', new EntityInAvailableType($this->get('available_type_id')));
        }
        return $this->validations;
    }
}
