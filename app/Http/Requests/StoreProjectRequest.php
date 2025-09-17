<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['string', 'required', 'max:255'],
            'description' => ['string', 'nullable'],
            'start_date' => ['date', 'nullable'],
            'end_date' => ['date', 'nullable'],
            'rate' => ['nullable'],
            'image' => ['image', 'nullable', 'mimes:png,jpg,jpeg', 'max:2048']
        ];
    }
}
