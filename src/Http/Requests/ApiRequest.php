<?php

namespace Ximdex\StructuredData\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Request;

class ApiRequest extends FormRequest
{
    protected $validations = [
        'name' => [
            'string',
            'min:3',
            'max:255'
        ]
    ];
    
    /**
     * Determine if the user is authorized to make this request
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }
    
    /**
     * Get the validation rules that apply to the request
     *
     * @return array
     */
    public function rules(): array
    {
        $method = Request::method();
        switch ($method) {
            
            // store | update
            case 'POST':
            case 'PUT':
            case 'PATH':
                $this->addRule('name', 'required');
                break;
        }
        return $this->validations;
    }
    
    protected function addRule(string $key, string $rule): void
    {
        if (isset($this->validations[$key])) {
            $this->validations[$key][] = $rule;
        } else {
            $this->validations[$key] = $rule;
        }
    }
}
