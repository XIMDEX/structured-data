<?php

namespace Ximdex\StructuredData\Requests;

use Illuminate\Support\Facades\Request;
use Illuminate\Validation\Rule;
use Ximdex\StructuredData\Models\Schema;
use Ximdex\StructuredData\Models\PropertySchema;
use Ximdex\StructuredData\Models\AvailableType;
use Ximdex\StructuredData\Rules\AvailableTypeThingRule;

class AvailableTypeRequest extends ApiRequest
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
                $this->addRule('property_schema_id', 'required');
                $this->addRule('property_schema_id', 'numeric');
                $this->addRule('property_schema_id', 'gte:1');
                $this->addRule('property_schema_id', 'exists:' . (new PropertySchema)->getTable() . ',id');
            case 'PUT':
            case 'PATCH':
                $this->addRule('schema_id', 'required_if:type,==,' . Schema::THING_TYPE);
                $this->addRule('schema_id', 'numeric');
                $this->addRule('schema_id', 'gte:1');
                $this->addRule('type', 'required');
                $this->addRule('*', 'bail');
                $this->addRule('schema_id', 'exists:' . (new Schema)->getTable() . ',id');
                $this->addRule('type', Rule::in(AvailableType::TYPES));
                $this->addRule('type', new AvailableTypeThingRule($this->get('schema_id')));
        }
        return $this->validations;
    }
}