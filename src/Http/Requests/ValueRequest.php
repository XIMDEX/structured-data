<?php

namespace Ximdex\StructuredData\Http\Requests;

use Ximdex\StructuredData\Models\AvailableType;
use Ximdex\StructuredData\Models\Item;
use Ximdex\StructuredData\Rules\ValueInAvailableTypeRule;
use Ximdex\StructuredData\Rules\ItemInAvailableTypeRule;

class ValueRequest extends ApiRequest
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
                $this->addRule('available_type_id', 'required');
                $this->addRule('available_type_id', 'numeric');
                $this->addRule('available_type_id', 'gte:1');
                $this->addRule('available_type_id', 'exists:' . (new AvailableType)->getTable() . ',id');
                $this->addRule('item_id', 'required');
                $this->addRule('item_id', 'numeric');
                $this->addRule('item_id', 'gte:1');
                $this->addRule('item_id', 'exists:' . (new Item)->getTable() . ',id');
                $this->addRule('value', 'required_without:ref_item_id');
                $this->addRule('ref_item_id', 'required_without:value');
            case 'PUT':
            case 'PATCH':
                $this->addRule('ref_item_id', 'numeric');
                $this->addRule('ref_item_id', 'gte:1');
                $this->addRule('ref_item_id', 'exists:' . (new Item)->getTable() . ',id');
                $this->addRule('position', 'numeric');
                $this->addRule('position', 'gte:1');
                $this->addRule('*', 'bail');
                $this->addRule('value', new ValueInAvailableTypeRule($this->get('available_type_id')));
                $this->addRule('ref_item_id', new ItemInAvailableTypeRule($this->get('available_type_id')));
            default:
                break;
        }
        return $this->validations;
    }
}
