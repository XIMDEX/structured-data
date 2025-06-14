<?php

namespace Ximdex\StructuredData\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Request;

class ApiRequest extends FormRequest
{
    protected $validations = [
        'label' => [
            'string',
            'min:3',
            'max:255',
            'alpha'
        ]
    ];
    
    public ?string $method;
    
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
        $this->method = Request::method();
        return $this->validations;
    }
    
    protected function addRule(string $key, $rule): void
    {
        $this->validations[$key][] = $rule;
    }
    
    protected function removeRule(string $key): void
    {
        unset($this->validations[$key]);
    }
}
