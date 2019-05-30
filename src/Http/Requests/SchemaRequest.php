<?php

namespace Ximdex\StructuredData\Requests;

use Ximdex\StructuredData\Models\Schema;
use Illuminate\Support\Facades\Request;

class SchemaRequest extends ApiRequest
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
            case 'PUT':
            case 'PATH':
                $this->addRule('name', 'alpha');
                $this->addRule('name', 'max:50');
                $this->addRule('name', 'unique:' . (new Schema)->getTable());
                break;
        }
        return $this->validations;
    }
}
