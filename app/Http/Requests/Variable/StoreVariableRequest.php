<?php

namespace App\Http\Requests\Variable;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreVariableRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'key'   => ['required', 'string', 'max:255', 'regex:/^[A-Z0-9_]+$/'],
            'value' => ['required', 'string', 'max:10000'],
        ];
    }
}
