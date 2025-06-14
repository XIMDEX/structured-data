<?php

namespace Ximdex\StructuredData\Http\Requests;

use Illuminate\Validation\Rule;
use Ximdex\StructuredData\Models\Schema;
use Ximdex\StructuredData\Models\Value;
use Ximdex\StructuredData\Rules\ValidPropertyRule;
use Ximdex\StructuredData\Rules\ValueInAvailableTypeRule;
use Ximdex\StructuredData\Rules\ItemInAvailableTypeRule;
use Ximdex\StructuredData\Rules\NeededPropertiesRule;
use Ximdex\StructuredData\Models\AvailableType;
use Ximdex\StructuredData\Rules\TypeAllowedInPropertyRule;

class ItemRequest extends ApiRequest
{   
    /**
     * {@inheritDoc}
     * @see \Ximdex\StructuredData\Requests\ApiRequest::rules()
     */
    public function rules(): array
    {
        parent::rules();
        if ($this->item and $this->item->schema_id) {
            $schemaId = $this->item->schema_id;
        } else {
            $schemaId = $this->get('schema_id');
        }
        switch ($this->method) {
                
            // store | update
            case 'POST':
                $this->addRule('schema_id', 'required');
                $this->addRule('properties', 'required');
                $this->addRule('properties.*.type', 'required');
                $this->addRule('properties.*.values', 'required');
            case 'PUT':
                if ($schemaId) {
                    $this->addRule('properties', new NeededPropertiesRule($schemaId));
                }
            case 'PATCH':
                $this->addRule('schema_id', 'numeric');
                $this->addRule('schema_id', 'gte:1');
                $this->addRule('delete', 'array');
                $this->addRule('properties', 'array');
                $this->addRule('properties.*.type', 'numeric');
                $this->addRule('properties.*.type', 'gte:1');
                $this->addRule('properties.*.values', 'array');
                $this->addRule('properties.*.values.*.id', 'numeric');
                $this->addRule('properties.*.values.*.id', 'gte:1');
                $this->addRule('properties.*.values.*.id', 'required_with:properties.*.values.*.value');
                $this->addRule('properties.*.values.*.value', 'required_with:properties.*.values.*.id');
                $this->addRule('properties.*.values.*.id', 'gte:1');
                $this->addRule('properties.*.values.delete', 'boolean');
                $this->addRule('*', 'bail');
                $this->addRule('schema_id', 'exists:' . (new Schema)->getTable() . ',id');
                if ($this->item and $this->item->id) {
                    
                    // The value must be present in the Values table and be associated to the item to update
                    $this->addRule('delete', Rule::exists((new Value)->getTable(), 'id')->where(function ($query) {
                        $query->where('item_id', $this->item->id);
                    }));
                }
                $this->addRule('properties.*.type', 'exists:' . (new AvailableType)->getTable() . ',id');
                $this->addRule('properties.*.values.*.id', 'exists:' . (new Value)->getTable() . ',id');
                $this->addRule('*', 'bail');
                if ($schemaId) {
                    $this->addRule('properties.*', new ValidPropertyRule($schemaId));
                }
                $this->addRule('*', 'bail');
                $this->addRule('properties.*.type', new TypeAllowedInPropertyRule());
                $this->addRule('*', 'bail');
                $this->addRule('properties.*', new ValueInAvailableTypeRule());
                $this->addRule('properties.*', new ItemInAvailableTypeRule());
            default:
                break;
        }
        return $this->validations;
    }
}
