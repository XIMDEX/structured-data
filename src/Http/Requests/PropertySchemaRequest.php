<?php

namespace Ximdex\StructuredData\Requests;

use Illuminate\Validation\Rule;
use Ximdex\StructuredData\Models\Schema;
use Ximdex\StructuredData\Models\Property;
use Ximdex\StructuredData\Rules\SchemaPropertyDuplicationRule;
use Ximdex\StructuredData\Models\AvailableType;

class PropertySchemaRequest extends ApiRequest
{   
    /**
     * {@inheritDoc}
     * @see \Ximdex\StructuredData\Requests\ApiRequest::rules()
     */
    public function rules(): array
    {
        parent::rules();
        switch ($this->method) {
            
            // store | update
            case 'POST':
                $this->addRule('label', 'required_without:property_id');
                $this->addRule('property_id', 'required_without:label');
                $this->addRule('schema_id', 'required');
                $this->addRule('types', 'required');
            case 'PUT':
            case 'PATCH':
                $this->addRule('label', 'max:50');
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
                $this->addRule('max_cardinality', 'gte:0');
                if ($this->get('max_cardinality')) {
                    $this->addRule('min_cardinality', 'lte:' . $this->get('max_cardinality'));
                    if ($this->get('min_cardinality')) {
                        $this->addRule('max_cardinality', 'gte:' . $this->get('min_cardinality'));
                    }
                }
                $this->addRule('default_value', 'max:5000');
                $this->addRule('*', 'bail');
                $this->addRule('schema_id', new SchemaPropertyDuplicationRule(
                    $this->get('schema_id'), 
                    $this->get('property_id'),
                    $this->get('label')
                ));
                $this->addRule('types', 'array');
                $this->addRule('types.*.type', 'required');
                $this->addRule('types.*.type', Rule::in(AvailableType::SIMPLE_TYPES));
                $this->addRule('types.*.schema_id', 'required_if:types.*.type,' . Schema::THING_TYPE);
                $this->addRule('types.*.schema_id', 'numeric');
                $this->addRule('types.*.schema_id', 'gte:1');
                $this->addRule('*', 'bail');
                $this->addRule('types.*.schema_id', 'exists:' . (new Schema)->getTable() . ',id');
            default:
                break;
        }
        return $this->validations;
    }
}
