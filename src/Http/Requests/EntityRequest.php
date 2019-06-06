<?php

namespace Ximdex\StructuredData\Requests;

use Illuminate\Support\Facades\Request;
use Ximdex\StructuredData\Models\Schema;

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
            case 'PUT':
            case 'PATCH':
                $this->addRule('schema_id', 'numeric');
                $this->addRule('schema_id', 'gte:1');
                $this->addRule('schema_id', 'exists:' . (new Schema)->getTable() . ',id');
        }
        return $this->validations;
    }
}
